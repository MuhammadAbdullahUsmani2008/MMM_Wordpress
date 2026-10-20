<?php
/**
 * Create the WordPress pages that back the original site's routes.
 *
 * Run with: docker compose run --rm wpcli eval-file /setup/create-pages.php
 *
 * Each route keeps the original slug so every URL on the site is unchanged:
 *   /                      -> page "Home" (front page)
 *   /about/                -> page "about"
 *   /contact/              -> page "contact"
 *   /donate/               -> page "donate"
 *   /get-involved/         -> page "get-involved"
 *   /media/                -> page "media"
 *   /what-we-do/           -> page "what-we-do"
 *   /disaster-response/    -> page "disaster-response"
 *   /zakat-calculator/     -> page "zakat-calculator"
 *
 * Programmes (/what-we-do/{slug}/) and field reports (/media/{slug}/) are
 * post types, so they are not created here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$routes = array(
	'Home'               => array( 'slug' => 'home', 'template' => 'front-page' ),
	'About'              => array( 'slug' => 'about' ),
	'Contact'            => array( 'slug' => 'contact' ),
	'Donate'             => array( 'slug' => 'donate' ),
	'Get Involved'       => array( 'slug' => 'get-involved' ),
	'Media'              => array( 'slug' => 'media' ),
	'What We Do'         => array( 'slug' => 'what-we-do' ),
	'Disaster Response'  => array( 'slug' => 'disaster-response' ),
	'Zakat Calculator'   => array( 'slug' => 'zakat-calculator' ),
);

$created = 0;
$kept    = 0;

foreach ( $routes as $title => $def ) {
	$slug   = $def['slug'];
	$exists = get_page_by_path( $slug );
	if ( $exists ) {
		$kept++;
		continue;
	}
	$id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_title'  => $title,
			'post_name'   => $slug,
			'post_status' => 'publish',
			'post_content'=> '',
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		echo 'ERROR ' . $slug . ': ' . $id->get_error_message() . "\n";
		continue;
	}
	$created++;
	echo "created page {$slug} (#{$id})\n";
}

// The home route must resolve to the front page.
$home = get_page_by_path( 'home' );
if ( $home ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home->ID );
	echo "front page set to #{$home->ID}\n";
}

// Remove the stock pages WordPress ships with, which have no counterpart
// on the original site.
foreach ( array( 'sample-page' ) as $junk ) {
	$p = get_page_by_path( $junk );
	if ( $p ) {
		wp_delete_post( $p->ID, true );
		echo "removed {$junk}\n";
	}
}
$draft = get_posts(
	array(
		'name'       => 'privacy-policy',
		'post_type'  => 'page',
		'post_status'=> 'any',
		'numberposts'=> 1,
	)
);
if ( $draft ) {
	wp_delete_post( $draft[0]->ID, true );
	echo "removed privacy-policy\n";
}

echo "created={$created} existing={$kept}\n";
