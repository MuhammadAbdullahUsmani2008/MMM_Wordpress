<?php
/**
 * One-shot demo content + configuration setup.
 * Run with: docker compose run --rm wpcli eval-file /setup/setup-content.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$out = [];

/* ------------------------------------------------------------------ *
 * Helper
 * ------------------------------------------------------------------ */
function ensure_page( $title, $content, $status = 'publish' ) {
	$existing = get_page_by_path( sanitize_title( $title ) );
	if ( $existing ) {
		wp_update_post( array(
			'ID'            => $existing->ID,
			'post_content'  => $content,
			'post_status'   => $status,
		) );
		return $existing->ID;
	}
	return wp_insert_post( array(
		'post_type'    => 'page',
		'post_title'   => $title,
		'post_name'    => sanitize_title( $title ),
		'post_content' => $content,
		'post_status'  => $status,
	) );
}

function ensure_post( $title, $content, $category ) {
	$existing = get_page_by_path( sanitize_title( $title ), OBJECT, 'post' );
	$cat      = get_category_by_slug( sanitize_title( $category ) );
	$cat_ids  = $cat ? array( $cat->term_id ) : array();

	$data = array(
		'post_type'     => 'post',
		'post_title'    => $title,
		'post_name'     => sanitize_title( $title ),
		'post_content'  => $content,
		'post_status'   => 'publish',
		'post_category' => $cat_ids,
	);

	if ( $existing ) {
		$data['ID'] = $existing->ID;
		return wp_update_post( $data, true );
	}
	return wp_insert_post( $data, true );
}

/* ------------------------------------------------------------------ *
 * Categories
 * ------------------------------------------------------------------ */
foreach ( array( 'News', 'Tutorials', 'Releases' ) as $cat_name ) {
	if ( ! term_exists( $cat_name, 'category' ) ) {
		wp_insert_term( $cat_name, 'category' );
	}
}
$out[] = 'Categories: News, Tutorials, Releases';

/* ------------------------------------------------------------------ *
 * Pages
 * ------------------------------------------------------------------ */
$home_id = ensure_page(
	'Home',
	'<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Welcome to MMM Demo Site</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This is a locally hosted WordPress installation running in Docker. Use it to experiment with themes, plugins, content and settings without any risk to a production site.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Everything you see here &mdash; pages, posts and settings &mdash; was set up automatically with WP-CLI.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/about/">Learn more</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->'
);

$about_id = ensure_page(
	'About',
	'<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">About Us</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We build things for the web. This demo site exists so you can try out WordPress features in a safe, local environment.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Local development means instant changes, no downtime, and a reset button whenever you need one.</p>
<!-- /wp:paragraph -->'
);

$services_id = ensure_page(
	'Services',
	'<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Services</h1>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Design</h3>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Interface and brand design for web products.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Development</h3>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Custom themes, plugins and integrations.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Support</h3>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Maintenance, updates and performance tuning.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->'
);

$contact_id = ensure_page(
	'Contact',
	'<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Contact</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Drop us a line at <a href="mailto:admin@example.com">admin@example.com</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Install a form plugin such as WPForms or Contact Form 7 to add a working form here.</p>
<!-- /wp:paragraph -->'
);

$blog_id = ensure_page(
	'Blog',
	'<!-- wp:query {"query":{"perPage":10,"postType":"post","inherit":true}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:post-title {"isLink":true} /--><!-- wp:post-excerpt /-->
<!-- /wp:post-template --></div>
<!-- /wp:query -->'
);

$out[] = "Pages created: Home(#$home_id) About(#$about_id) Services(#$services_id) Contact(#$contact_id) Blog(#$blog_id)";

/* ------------------------------------------------------------------ *
 * Posts
 * ------------------------------------------------------------------ */
$posts = array(
	array(
		'Hello World: Your New Local WordPress Site',
		'News',
		'<!-- wp:paragraph --><p>Your local WordPress environment is up and running in Docker. Two containers handle the work: one serves WordPress over Apache, the other runs MariaDB for storage.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Because the database lives in a Docker volume, your content survives container restarts. Run <code>docker compose down -v</code> when you want a completely fresh install.</p><!-- /wp:paragraph -->',
	),
	array(
		'Five Things To Try In Your Demo Site',
		'Tutorials',
		'<!-- wp:paragraph --><p>Here are a few things worth trying now that the site is running:</p><!-- /wp:paragraph --><!-- wp:list {"ordered":true} --><ol class="wp-block-list"><!-- wp:list-item --><li>Switch themes under Appearance &rarr; Themes.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Build a page with the block editor.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Add plugins from the plugin directory.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Change permalinks under Settings &rarr; Permalinks.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Export an XML backup from Tools &rarr; Export.</li><!-- /wp:list-item --></ol><!-- /wp:list -->',
	),
	array(
		'How Local Docker WordPress Works',
		'Tutorials',
		'<!-- wp:paragraph --><p>The WordPress image ships Apache with PHP ready to go and connects to MariaDB over a private Docker network. WP-CLI runs as a separate one-off container, so command-line tasks never disturb the running web container.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Port 8080 is mapped to the host, so <code>http://localhost:8080</code> is your site.</p><!-- /wp:paragraph -->',
	),
	array(
		'Version 7.1 Is Installed',
		'Releases',
		'<!-- wp:paragraph --><p>This demo site runs the current WordPress release, updated automatically during setup so that plugins and themes install without compatibility warnings.</p><!-- /wp:paragraph -->',
	),
);

$created = array();
foreach ( $posts as $p ) {
	$created[] = ensure_post( $p[0], $p[2], $p[1] );
}
$out[] = 'Posts created: ' . implode( ', ', $created );

/* ------------------------------------------------------------------ *
 * Reading / front page settings
 * ------------------------------------------------------------------ */
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', $blog_id );
$out[] = 'Reading: static front page + posts page set';

/* ------------------------------------------------------------------ *
 * General settings
 * ------------------------------------------------------------------ */
update_option( 'blogname', 'MMM Demo Site' );
update_option( 'blogdescription', 'A locally hosted WordPress demo site' );
update_option( 'blog_public', 0 );                 // discourage search engines (local site)
update_option( 'timezone_string', 'UTC' );
update_option( 'date_format', 'F j, Y' );
update_option( 'time_format', 'g:i a' );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'thumbnail_crop', array( 'center' ) );
$out[] = 'General options updated';

/* ------------------------------------------------------------------ *
 * Admin user
 * ------------------------------------------------------------------ */
$admin = get_user_by( 'login', 'admin' );
if ( $admin ) {
	wp_update_user( array(
		'ID'           => $admin->ID,
		'user_email'   => 'admin@example.com',
		'display_name' => 'Admin',
		'first_name'   => 'Site',
		'last_name'    => 'Admin',
		'role'         => 'administrator',
	) );
	$out[] = "Admin user confirmed: admin (#{$admin->ID})";
}

// Apply the admin password from the environment (see .env.example) so no real
// credential ever has to live in this file.
$admin_pass = getenv( 'WORDPRESS_ADMIN_PASSWORD' );
if ( $admin && $admin_pass ) {
	wp_set_password( $admin_pass, $admin->ID );
	clean_user_cache( $admin->ID );
	$out[] = 'Admin password applied from WORDPRESS_ADMIN_PASSWORD';
} elseif ( $admin ) {
	$out[] = 'WORDPRESS_ADMIN_PASSWORD not set - admin password left unchanged';
}

/* ------------------------------------------------------------------ *
 * Misc tidy-up
 * ------------------------------------------------------------------ */
// Remove the default "Uncategorized" assignment on our posts is handled above.
// Delete default sample content if still present.
$default_post = get_page_by_path( 'hello-world', OBJECT, 'post' );
if ( $default_post && ! in_array( $default_post->ID, $created, true ) ) {
	wp_delete_post( $default_post->ID, true );
	$out[] = 'Removed default hello-world post';
}

$sample_page = get_page_by_path( 'sample-page' );
if ( $sample_page ) {
	wp_delete_post( $sample_page->ID, true );
	$out[] = 'Removed default sample-page';
}
$privacy_page = get_option( 'wp_page_for_privacy_policy' );
if ( $privacy_page ) {
	wp_delete_post( (int) $privacy_page, true );
	update_option( 'wp_page_for_privacy_policy', 0 );
	$out[] = 'Removed default privacy policy page';
}

// Deactivate plugins we installed but do not want active by default.
deactivate_plugins( array( 'classic-editor/classic-editor.php' ) );
update_option( 'allowedthemes', get_option( 'allowedthemes' ) );

// Flush rewrite rules after content creation.
flush_rewrite_rules( true );
$out[] = 'Rewrite rules flushed';

/* ------------------------------------------------------------------ *
 * Report
 * ------------------------------------------------------------------ */
echo "---- SETUP REPORT ----\n";
foreach ( $out as $line ) {
	echo '* ' . $line . "\n";
}
echo '* Front page ID: ' . get_option( 'page_on_front' ) . "\n";
echo '* Posts page ID: ' . get_option( 'page_for_posts' ) . "\n";
echo '* Site URL: ' . get_option( 'siteurl' ) . "\n";
echo '---- COMPLETE ----' . "\n";
