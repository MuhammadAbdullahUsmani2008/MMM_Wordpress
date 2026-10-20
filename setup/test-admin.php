<?php
/**
 * Exercise the content settings screen: schema, sanitising, saving, rendering.
 *
 * Run: docker compose run --rm wpcli eval-file /setup/test-admin.php
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$failures = 0;
$check    = static function ( $label, $ok, $detail = '' ) use ( &$failures ) {
	if ( ! $ok ) {
		$failures++;
	}
	printf(
		"  %s %-46s %s\n",
		$ok ? 'ok  ' : 'FAIL',
		$label,
		$detail
	);
};

echo "---- SCHEMA ----\n";
$schema = mmm_admin_schema();
$check( 'sections defined', count( $schema ) >= 10, count( $schema ) . ' sections' );

foreach ( $schema as $id => $spec ) {
	$ok = ! empty( $spec['option'] ) && ! empty( $spec['title'] ) && ! empty( $spec['fields'] );
	if ( ! $ok ) {
		$check( "section $id complete", false, 'missing option/title/fields' );
		continue;
	}
	// Every field needs a label and a type the renderer knows how to print.
	$types = array( 'text', 'textarea', 'url', 'tel', 'email', 'number', 'int', 'select' );
	foreach ( $spec['fields'] as $key => $field ) {
		if ( empty( $field['label'] ) || ! in_array( $field['type'] ?? '', $types, true ) ) {
			$ok = false;
			$check( "section $id field $key", false, 'bad label or type' );
		}
	}
	// A nested child must carry fields of its own, or it renders nothing.
	foreach ( ( $spec['children'] ?? array() ) as $ckey => $child ) {
		if ( empty( $child['fields'] ) ) {
			$ok = false;
			$check( "section $id child $ckey", false, 'no fields' );
		}
	}
	if ( $ok ) {
		$check( "section $id", true, count( $spec['fields'] ) . ' field(s)' . ( isset( $spec['children'] ) ? ' + nested' : '' ) );
	}
}

echo "\n---- SANITISING ----\n";

// A crafted payload: unknown keys must be dropped, types coerced.
$org_raw = array(
	'name'       => "Muslim Medical Mission\n<script>alert(1)</script>",
	'email'      => 'info@mmm.org.pk',
	'founded'    => '2005',
	'evil_option' => 'nope',
);
$clean = mmm_sanitize_section( $org_raw, $schema['org'] );
$check( 'unknown key dropped', ! isset( $clean['evil_option'] ) );
$check( 'markup stripped from text', false === strpos( $clean['name'], '<script>' ), substr( $clean['name'], 0, 30 ) );
$check( 'int coerced', 2005 === $clean['founded'], var_export( $clean['founded'], true ) );

$stats_raw = array(
	array( 'value' => '1,000,000', 'suffix' => '+', 'label' => 'Patients Served', 'detail' => 'x' ),
	array( 'value' => '16', 'suffix' => '', 'label' => 'Conferences', 'detail' => 'y' ),
);
$clean_stats = mmm_sanitize_section( $stats_raw, $schema['impact_stats'] );
$check( 'comma stripped from number', 1000000 == $clean_stats[0]['value'], var_export( $clean_stats[0]['value'], true ) );
$check( 'whole numbers stay numeric', is_int( $clean_stats[0]['value'] ) && is_int( $clean_stats[1]['value'] ) );
$check( 'row count preserved', 2 === count( $clean_stats ) );

// Fields the screen does not display must survive a save. mmm_org carries
// flattened copies of the address and bank, and nav items carry a feature
// panel — deleting those would break the site on the first click of Save.
$with_hidden = mmm_sanitize_section(
	array( 'name' => 'Renamed', 'bank_iban' => 'PK50UNIL0109000338617189' ),
	$schema['org'],
	array( 'name' => 'Muslim Medical Mission', 'bank_iban' => 'PK50UNIL0109000338617189', 'bank_branch' => 'Model Town' )
);
$check( 'managed field updated', 'Renamed' === $with_hidden['name'] );
$check( 'unmanaged field kept', 'PK50UNIL0109000338617189' === $with_hidden['bank_iban'] );
$check( 'absent unmanaged field kept', 'Model Town' === $with_hidden['bank_branch'] );

// A naive submit of every nav field, with no feature panel in the payload.
$stored_nav = array(
	array(
		'label'   => 'About Us',
		'href'    => '/about',
		'feature' => array( 'title' => 'Pre-Conference BLS', 'body' => 'b', 'href' => '/about', 'cta' => 'Read', 'image' => '/media/x.jpg' ),
		'columns' => array( array( 'heading' => 'H', 'links' => array( array( 'label' => 'L', 'href' => '/l' ) ) ) ),
	),
);
$submitted_nav = array(
	array( 'label' => 'About Us', 'href' => '/about', 'blurb' => '', 'columns' => array() ),
);
$clean_nav2 = mmm_sanitize_section( $submitted_nav, $schema['nav'], $stored_nav );
$check( 'feature panel survives a save', 'Pre-Conference BLS' === ( $clean_nav2[0]['feature']['title'] ?? null ) );

// …and it is editable when the form does send it.
$edited_nav = array(
	array(
		'label'   => 'About Us',
		'href'    => '/about',
		'feature' => array( 'title' => 'Edited', 'body' => 'b2', 'href' => '/about', 'cta' => 'Read', 'image' => '/media/y.jpg' ),
		'columns' => array(),
	),
);
$clean_nav3 = mmm_sanitize_section( $edited_nav, $schema['nav'], $stored_nav );
$check( 'feature panel is editable', 'Edited' === ( $clean_nav3[0]['feature']['title'] ?? null ) );
$check( 'feature image kept', '/media/y.jpg' === ( $clean_nav3[0]['feature']['image'] ?? null ) );

// A scalar list arrives as plain strings.
$clean_means = mmm_sanitize_section(
	array( 'First statement.', 'Second statement.' ),
	$schema['means'],
	array( 'First statement.', 'Second statement.', 'Third statement.' )
);
$check( 'scalar rows accepted', 2 === count( $clean_means ) && 'First statement.' === $clean_means[0]['text'] );

// Nested nav: a column with its links must survive a round trip.
$nav_raw = array(
	array(
		'label'  => 'About Us',
		'href'   => '/about',
		'blurb'  => 'Since 2005.',
		'columns' => array(
			array(
				'heading' => 'The organisation',
				'links'   => array(
					array( 'label' => 'Who we are', 'href' => '/about', 'note' => 'Founded in 2005' ),
					array( 'label' => 'Mission', 'href' => '/about#mission', 'note' => '' ),
				),
			),
		),
	),
);
$clean_nav = mmm_sanitize_section( $nav_raw, $schema['nav'] );
$check( 'nav item kept', 'About Us' === $clean_nav[0]['label'] );
$check( 'column kept', 'The organisation' === $clean_nav[0]['columns'][0]['heading'] );
$check( 'nested links kept', 2 === count( $clean_nav[0]['columns'][0]['links'] ) );
$check( 'deepest field kept', 'Founded in 2005' === $clean_nav[0]['columns'][0]['links'][0]['note'] );

// An unknown choice falls back to the default rather than being stored.
$tiers = mmm_sanitize_section(
	array( array( 'amount' => '3,000', 'title' => 't', 'body' => 'b', 'accent' => 'chartreuse' ) ),
	$schema['giving_tiers']
);
$check( 'invalid select falls back', 'blue' === $tiers[0]['accent'], var_export( $tiers[0]['accent'], true ) );

echo "\n---- LIVE DATA SURVIVES A ROUND TRIP ----\n";
// Saving the current values back must not change them. This is the failure
// that would silently wipe a site's content, so it is worth checking directly.
$untouched = true;

/**
 * Sort array keys recursively, so two arrays holding the same content in a
 * different order compare equal. Key order in an associative array carries
 * no meaning, but a whole-JSON comparison would flag it.
 *
 * @param mixed $value Value to normalise.
 * @return mixed
 */
$ksort_deep = static function ( $value ) use ( &$ksort_deep ) {
	if ( ! is_array( $value ) ) {
		return $value;
	}
	$out = array();
	foreach ( $value as $k => $v ) {
		$out[ $k ] = $ksort_deep( $v );
	}
	ksort( $out );
	return $out;
};

foreach ( $schema as $id => $spec ) {
	$current = get_option( $spec['option'] );
	$again   = mmm_sanitize_section( $current, $spec, $current );
	if ( ! empty( $spec['repeatable'] ) ) {
		$again = array_values( array_filter( $again, static function ( $row ) {
			return is_array( $row ) && array_filter( $row, static fn( $v ) => '' !== $v );
		} ) );

		// Mirror the save handler: a scalar list is stored flat.
		if ( ! empty( $spec['scalar'] ) ) {
			$flat  = $spec['scalar'];
			$again = array_map( static fn( $row ) => (string) ( $row[ $flat ] ?? '' ), $again );
		}

		$current = is_array( $current ) ? array_values( $current ) : array();
	}
	// Gallery keys contain a hyphen, nav hrefs a hash — neither may be lost.
	$same = wp_json_encode( $ksort_deep( $current ) ) === wp_json_encode( $ksort_deep( $again ) );
	$check( "$id round trip", $same, $same ? '' : 'CHANGED' );
	if ( ! $same ) {
		$untouched = false;
	}
}
$check( 'every option survives', $untouched );

echo "\n---- RENDER ----\n";
wp_set_current_user( 1 );
ob_start();
$mmm_render_error = null;
try {
	mmm_render_admin_page();
} catch ( Throwable $e ) {
	$mmm_render_error = $e->getMessage();
}
$html = ob_get_clean();

$check( 'renders without exception', null === $mmm_render_error, (string) $mmm_render_error );
$check( 'produces markup', strlen( $html ) > 5000, strlen( $html ) . ' bytes' );
$check( 'has a form', false !== strpos( $html, '<form method="post"' ) );
$check( 'has the nonce field', false !== strpos( $html, 'name="mmm_admin_nonce"' ) );

foreach ( array_keys( $schema ) as $id ) {
	$check( "renders section $id", false !== strpos( $html, "id=\"mmm-$id\"" ) );
}

// Nothing may leak an unescaped user value or a notice.
$check( 'no PHP notice in output', ! preg_match( '/(Warning|Notice|Deprecated|Fatal)/', $html ) );
$check( 'no unescaped script tag', ! preg_match( '/<script>alert/', $html ) );

echo "\n" . ( $failures ? "$failures CHECK(S) FAILED\n" : "ALL CHECKS PASSED\n" );
