<?php
/**
 * Report on the seeded content and media library.
 *
 * Run with: docker compose run --rm wpcli eval-file /setup/status.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$line = static function ( $label, $value ) {
	printf( "%-22s %s\n", $label, $value );
};

echo "---- CONTENT ----\n";
$line( 'seed version', (string) get_option( 'mmm_seed_version', '(not seeded)' ) );
$line( 'theme', wp_get_theme()->get( 'Name' ) );
$line( 'site url', home_url( '/' ) );
$line( 'org email', mmm_org_value( 'email' ) );
$line( 'org name', mmm_org_value( 'name' ) );
$line( 'legal name', mmm_org_value( 'legalName' ) );
$line( 'bank iban', mmm_bank( 'iban' ) );
$line( 'nav items', count( mmm_nav() ) );
$line( 'impact stats', count( mmm_impact_stats() ) );
$line( 'giving tiers', count( mmm_giving_tiers() ) );
$line( 'allocation', count( mmm_allocation() ) );
$line( 'means', count( mmm_means() ) );
$line( 'regions', count( mmm_regions() ) );
$line( 'gallery groups', count( mmm_gallery() ) );
$line( 'gallery tabs', count( mmm_gallery_groups() ) );
$line( 'search entries', count( mmm_search_index() ) );
$line( 'programmes', count( mmm_programs() ) );
$line( 'field reports', count( mmm_reports() ) );

echo "\n---- PROGRAMMES ----\n";
foreach ( mmm_programs() as $p ) {
	printf( "  %-26s %-8s %-9s body=%d gallery=%d highlights=%d\n",
		$p['slug'], $p['region'], $p['accent'],
		count( $p['body'] ), count( $p['gallery'] ), count( $p['highlights'] ) );
}

echo "\n---- REPORTS ----\n";
foreach ( mmm_reports() as $r ) {
	printf( "  %-38s %-20s %s\n", $r['slug'], $r['kind'], $r['date'] );
}

echo "\n---- MEDIA LIBRARY ----\n";
$attachments = get_posts(
	array(
		'post_type'      => 'attachment',
		'posts_per_page' => -1,
		'post_status'    => 'inherit',
		'fields'         => 'ids',
	)
);
$line( 'attachments', count( $attachments ) );
$expected = 0;
$root     = ABSPATH . 'wp-content/uploads/media';
if ( is_dir( $root ) ) {
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $f ) {
		if ( $f->isFile() ) {
			$expected++;
		}
	}
}
$line( 'files on disk', $expected );

// /media/... is served by a rewrite rule, not a symlink — a symlink would
// make Apache claim /media as a directory and 403 the Media page.
$htaccess = file_exists( ABSPATH . '.htaccess' ) ? (string) file_get_contents( ABSPATH . '.htaccess' ) : '';
$line( 'media rewrite', false !== strpos( $htaccess, 'wp-content/uploads/media/' ) ? 'present' : 'MISSING' );

echo "\n---- PAGES ----\n";
$pages = get_posts( array( 'post_type' => 'page', 'posts_per_page' => -1, 'post_status' => 'any' ) );
$line( 'wp pages', count( $pages ) );
foreach ( $pages as $p ) {
	printf( "  %s (%s)\n", $p->post_name, $p->post_status );
}
echo "----------------\n";
