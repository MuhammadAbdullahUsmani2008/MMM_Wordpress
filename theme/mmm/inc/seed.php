<?php
/**
 * One-time content import.
 *
 * Reads inc/seed-data.json — extracted verbatim from the original site's
 * TypeScript sources — and writes it into WordPress as options and posts.
 * After seeding, the database is the single source of truth: editing in
 * wp-admin changes the site, and this file is never re-run unless the
 * version below is bumped.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Bump to force a re-seed. */
define( 'MMM_SEED_VERSION', '1.0.1' );

/**
 * Path to the extracted dataset.
 *
 * @return string
 */
function mmm_seed_file() {
	return MMM_THEME_DIR . '/inc/seed-data.json';
}

/**
 * Load the extracted dataset.
 *
 * @return array|null
 */
function mmm_load_seed_data() {
	$file = mmm_seed_file();
	if ( ! file_exists( $file ) ) {
		return null;
	}
	$data = json_decode( file_get_contents( $file ), true );
	return is_array( $data ) ? $data : null;
}

/**
 * Flatten the org object into a single option array.
 *
 * @param array $org Raw org data.
 * @return array
 */
function mmm_flatten_org( $org ) {
	$flat = array();
	foreach ( $org as $key => $value ) {
		if ( is_array( $value ) && array_is_list( $value ) ) {
			continue; // handled separately (address is associative, social is a list)
		}
		if ( is_array( $value ) ) {
			foreach ( $value as $sub => $subval ) {
				if ( ! is_array( $subval ) ) {
					$flat[ $key . '_' . $sub ] = $subval;
				}
			}
			continue;
		}
		$flat[ $key ] = $value;
	}
	return $flat;
}

/**
 * Run the import.
 *
 * Safe to call on every load; it exits early once seeded.
 *
 * @return bool Whether seeding happened on this call.
 */
function mmm_seed() {
	if ( get_option( 'mmm_seed_version' ) === MMM_SEED_VERSION ) {
		return false;
	}
	$data = mmm_load_seed_data();
	if ( null === $data ) {
		return false;
	}

	// ---- Organisation ----------------------------------------------------
	if ( ! empty( $data['org'] ) ) {
		update_option( 'mmm_org', mmm_flatten_org( $data['org'] ) );
		// Address is kept as its own option so templates can lay it out.
		if ( ! empty( $data['org']['address'] ) ) {
			update_option( 'mmm_org_address', $data['org']['address'] );
		}
		if ( ! empty( $data['org']['bank'] ) ) {
			update_option( 'mmm_org_bank', $data['org']['bank'] );
		}
	}
	if ( ! empty( $data['org']['social'] ) ) {
		update_option( 'mmm_social', $data['org']['social'] );
	}

	// ---- Repeatable option lists ----------------------------------------
	// Explicit map: getoption names are snake_case, not lowercased camel.
	$option_map = array(
		'nav'         => 'mmm_nav',
		'impactStats' => 'mmm_impact_stats',
		'givingTiers' => 'mmm_giving_tiers',
		'allocation'  => 'mmm_allocation',
		'means'       => 'mmm_means',
		'regions'     => 'mmm_regions',
		'gallery'     => 'mmm_gallery',
		// The filter tabs. Separate from the 250-row image list so a tab can
		// be renamed without touching every image.
		'galleryGroups' => 'mmm_gallery_groups',
		'searchIndex' => 'mmm_search_index',
	);
	foreach ( $option_map as $key => $option ) {
		if ( isset( $data[ $key ] ) ) {
			update_option( $option, $data[ $key ] );
		}
	}

	// ---- Programmes ------------------------------------------------------
	if ( ! empty( $data['programs'] ) ) {
		foreach ( $data['programs'] as $i => $p ) {
			mmm_seed_program( $p, $i );
		}
	}

	// ---- Field reports ---------------------------------------------------
	if ( ! empty( $data['reports'] ) ) {
		foreach ( $data['reports'] as $i => $r ) {
			mmm_seed_report( $r, $i );
		}
	}

	update_option( 'mmm_seed_version', MMM_SEED_VERSION );
	clean_post_cache( 0 );
	return true;
}

/**
 * Create or update a single programme.
 *
 * @param array $p Programme data.
 * @param int   $order Menu position.
 * @return int Post id.
 */
function mmm_seed_program( $p, $order ) {
	$existing = get_page_by_path( $p['slug'], OBJECT, 'program' );
	$fields   = array(
		'post_type'   => 'program',
		'post_name'   => $p['slug'],
		'post_title'  => $p['title'],
		'post_status' => 'publish',
		'menu_order'  => (int) $order,
		'post_content'=> implode( "\n\n", array_map( 'wp_kses_post', (array) ( $p['body'] ?? array() ) ) ),
	);
	$id = $existing ? $existing->ID : wp_insert_post( $fields, true );
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	if ( $existing ) {
		wp_update_post( $fields + array( 'ID' => $id ) );
	}
	update_post_meta( $id, '_mmm_summary', $p['summary'] ?? '' );
	update_post_meta( $id, '_mmm_subtitle', $p['subtitle'] ?? '' );
	update_post_meta( $id, '_mmm_region', $p['region'] ?? '' );
	update_post_meta( $id, '_mmm_accent', $p['accent'] ?? 'blue' );
	update_post_meta( $id, '_mmm_image', $p['image'] ?? '' );
	update_post_meta(
		$id,
		'_mmm_gallery',
		implode( "\n", (array) ( $p['gallery'] ?? array() ) )
	);
	update_post_meta(
		$id,
		'_mmm_highlights',
		implode( "\n", array_map(
			static function ( $h ) {
				return $h['label'] . ' = ' . $h['value'];
			},
			(array) ( $p['highlights'] ?? array() )
		) )
	);
	return (int) $id;
}

/**
 * Create or update a single field report.
 *
 * @param array $r Report data.
 * @param int   $order Menu position.
 * @return int Post id.
 */
function mmm_seed_report( $r, $order ) {
	$existing = get_page_by_path( $r['slug'], OBJECT, 'report' );
	$date     = (string) ( $r['date'] ?? '' );
	// WP needs a real date; the display value is kept in meta because the
	// source data includes non-numeric values such as "Ongoing".
	$post_date = preg_match( '/^\d{4}$/', $date )
		? sprintf( '%s-01-01 00:00:00', $date )
		: current_time( 'mysql' );

	$fields = array(
		'post_type'    => 'report',
		'post_name'    => $r['slug'],
		'post_title'   => $r['title'],
		'post_status'  => 'publish',
		'menu_order'   => (int) $order,
		'post_date'    => $post_date,
		'post_content' => implode( "\n\n", array_map( 'wp_kses_post', (array) ( $r['body'] ?? array() ) ) ),
		'post_excerpt' => $r['excerpt'] ?? '',
	);
	$id = $existing ? $existing->ID : wp_insert_post( $fields, true );
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	if ( $existing ) {
		wp_update_post( $fields + array( 'ID' => $id ) );
	}
	update_post_meta( $id, '_mmm_place', $r['place'] ?? '' );
	update_post_meta( $id, '_mmm_kind', $r['kind'] ?? '' );
	update_post_meta( $id, '_mmm_date', $r['date'] ?? '' );
	update_post_meta( $id, '_mmm_image', $r['image'] ?? '' );
	update_post_meta( $id, '_mmm_excerpt', $r['excerpt'] ?? '' );
	return (int) $id;
}

/**
 * Trigger the seed after themes activate and on ordinary loads while pending.
 */
function mmm_maybe_seed() {
	mmm_seed();
}
add_action( 'after_switch_theme', 'mmm_maybe_seed' );
add_action( 'init', 'mmm_maybe_seed', 5 );
