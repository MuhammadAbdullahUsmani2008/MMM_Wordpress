<?php
/**
 * Show what a settings save would change for a given option.
 *
 * Run: docker compose run --rm wpcli eval-file /setup/debug-admin.php [section]
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Walk two values and collect a line for each difference.
 *
 * @param mixed  $a    Before.
 * @param mixed  $b    After.
 * @param string $path Dotted path for the report.
 * @return array<int,string>
 */
function mmm_debug_diff( $a, $b, $path = '' ) {
	$out = array();

	if ( is_array( $a ) && is_array( $b ) ) {
		$keys = array_unique( array_merge( array_keys( $a ), array_keys( $b ) ) );
		foreach ( $keys as $k ) {
			$here = $path . '[' . $k . ']';
			if ( ! array_key_exists( $k, $a ) ) {
				$out[] = "$here ADDED = " . wp_json_encode( $b[ $k ] );
			} elseif ( ! array_key_exists( $k, $b ) ) {
				$out[] = "$here REMOVED (was " . wp_json_encode( $a[ $k ] ) . ')';
			} else {
				$out = array_merge( $out, mmm_debug_diff( $a[ $k ], $b[ $k ], $here ) );
			}
		}
		return $out;
	}

	if ( wp_json_encode( $a ) !== wp_json_encode( $b ) ) {
		$out[] = "$path " . wp_json_encode( $a ) . ' -> ' . wp_json_encode( $b );
	}
	return $out;
}

$only   = isset( $argv[1] ) ? (string) $argv[1] : '';
$schema = mmm_admin_schema();
$total  = 0;

foreach ( $schema as $id => $spec ) {
	if ( $only && $only !== $id ) {
		continue;
	}

	$current = get_option( $spec['option'] );
	$again   = mmm_sanitize_section( $current, $spec, $current );

	if ( ! empty( $spec['repeatable'] ) ) {
		$again = array_values( array_filter( $again, static function ( $row ) {
			return is_array( $row ) && array_filter( $row, static fn( $v ) => '' !== $v );
		} ) );
		if ( ! empty( $spec['scalar'] ) ) {
			$flat  = $spec['scalar'];
			$again = array_map( static fn( $row ) => (string) ( $row[ $flat ] ?? '' ), $again );
		}
		$current = is_array( $current ) ? array_values( $current ) : array();
	}

	$changes = mmm_debug_diff( $current, $again );
	$total  += count( $changes );

	printf( '== %s (%s) %s', $id, $spec['option'], $changes ? count( $changes ) . " change(s)\n" : "unchanged\n" );
	foreach ( array_slice( $changes, 0, 20 ) as $line ) {
		echo "   $line\n";
	}
	if ( count( $changes ) > 20 ) {
		echo '   … ' . ( count( $changes ) - 20 ) . " more\n";
	}
	echo "\n";
}

printf( "total changes across %s: %d\n", $only ?: 'all sections', $total );
