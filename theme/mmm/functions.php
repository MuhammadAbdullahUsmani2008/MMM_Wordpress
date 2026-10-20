<?php
/**
 * MMM — Muslim Medical Mission theme bootstrap.
 *
 * The design layer (CSS, fonts, component markup) is reproduced exactly from
 * the original static build. This file only wires up WordPress: assets,
 * content types, routing and the shared data layer.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MMM_VERSION', '1.0.5' );
define( 'MMM_THEME_DIR', get_template_directory() );
define( 'MMM_THEME_URI', get_template_directory_uri() );

/**
 * Path prefix the original site used for photographs, e.g. /media/field/x.jpg.
 * WordPress stores uploads under wp-content/uploads/, so every image reference
 * is resolved through mmm_media() rather than hard coded.
 */
define( 'MMM_MEDIA_PREFIX', '/media/' );
define( 'MMM_UPLOADS_MEDIA', '/wp-content/uploads/media/' );

require_once MMM_THEME_DIR . '/inc/data.php';
require_once MMM_THEME_DIR . '/inc/cpt.php';
require_once MMM_THEME_DIR . '/inc/nav.php';
require_once MMM_THEME_DIR . '/inc/head.php';
require_once MMM_THEME_DIR . '/inc/seed.php';
require_once MMM_THEME_DIR . '/inc/copy.php';
require_once MMM_THEME_DIR . '/inc/interactive.php';
require_once MMM_THEME_DIR . '/inc/admin.php';

/**
 * Theme supports.
 */
function mmm_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'automatic-feed-links' );
	// The original site never shows an admin bar on the front end design,
	// but we keep it for editing convenience and let CSS handle it.
}
add_action( 'after_setup_theme', 'mmm_setup' );

/**
 * Route /media/... to the uploaded files without stealing the page route.
 *
 * The original site served photographs from /media/field/x.jpg. Those files
 * now live in the media library under wp-content/uploads/media/, so the
 * original URLs are mapped here — but only when the file actually exists.
 * Without the existence check Apache would claim /media/ as a directory and
 * the Media page (and every /media/{report}/ permalink) would 403.
 *
 * @param string $rules .htaccess contents WordPress generated.
 * @return string
 */
function mmm_media_rewrite( $rules ) {
	$rule  = 'RewriteCond %{DOCUMENT_ROOT}/wp-content/uploads/media/$1 -f' . "\n";
	$rule .= 'RewriteRule ^media/(.+)$ wp-content/uploads/media/$1 [L]' . "\n\n";
	return $rule . $rules;
}
add_filter( 'mod_rewrite_rules', 'mmm_media_rewrite' );

/**
 * Make sure .htaccess maps /media/... to the uploaded files.
 *
 * WordPress only rewrites .htaccess when it can, and in this container it
 * reports that it cannot — so the rule is written here instead. It is
 * inserted just after WordPress' own index.php short-circuit, which is the
 * last point before WordPress claims every unmatched request.
 *
 * Idempotent: it does nothing if the rule is already present.
 *
 * @param bool $force Rewrite the file even if the rule is present.
 * @return bool Whether the file was changed.
 */
function mmm_ensure_htaccess( $force = false ) {
	$htaccess = ABSPATH . '.htaccess';
	if ( ! file_exists( $htaccess ) ) {
		return false;
	}

	$marker = 'RewriteRule ^media/(.+)$ wp-content/uploads/media/$1 [L]';
	$rules  = file_get_contents( $htaccess );

	if ( ! $force && false !== strpos( (string) $rules, $marker ) ) {
		return false;
	}

	$anchor = 'RewriteRule ^index\.php$ - [L]';
	$insert = 'RewriteCond %{DOCUMENT_ROOT}/wp-content/uploads/media/$1 -f' . "\n"
		. $marker . "\n";

	if ( false !== strpos( (string) $rules, $anchor ) ) {
		$count = 0;
		$rules = str_replace( $anchor, $anchor . "\n" . rtrim( $insert ), $rules, $count );
	} else {
		// No WordPress anchor: place the rule before the closing block.
		$rules = preg_replace( '/\n# END WordPress/', "\n" . rtrim( $insert ) . "\n# END WordPress", $rules, 1 );
	}

	$result = file_put_contents( $htaccess, $rules );
	return false !== $result;
}
add_action( 'after_switch_theme', static function () {
	mmm_ensure_htaccess();
}, 20 );

/**
 * Enqueue the original compiled stylesheet and the behaviour layer.
 *
 * Two stylesheets exist in the source build:
 *   - the webfont/critical sheet
 *   - the compiled utility sheet
 * Both are enqueued in the same order the original document used.
 */
function mmm_enqueue_assets() {
	wp_enqueue_style(
		'mmm-fonts',
		MMM_THEME_URI . '/assets/css/next-415069bf770ed919.css',
		array(),
		MMM_VERSION
	);
	wp_enqueue_style(
		'mmm-utilities',
		MMM_THEME_URI . '/assets/css/next-a602de8c44d77eac.css',
		array( 'mmm-fonts' ),
		MMM_VERSION
	);
	wp_enqueue_style(
		'mmm-theme',
		get_stylesheet_uri(),
		array( 'mmm-utilities' ),
		MMM_VERSION
	);
	wp_enqueue_script(
		'mmm-behaviour',
		MMM_THEME_URI . '/assets/js/mmm.js',
		array(),
		MMM_VERSION,
		true
	);
	wp_localize_script(
		'mmm-behaviour',
		'mmmSettings',
		array(
			'homeUrl'      => esc_url( home_url( '/' ) ),
			'uploadUrl'    => esc_url( wp_upload_dir()['baseurl'] ),
			'mediaPrefix'  => MMM_MEDIA_PREFIX,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'mmm_enqueue_assets' );

/**
 * Drop WordPress' emoji shim.
 *
 * The original build shipped no emoji script, and the shim adds a render
 * blocking script plus requests to s.w.org on every page. Browsers render
 * emoji natively, so nothing visual changes.
 */
function mmm_disable_emoji_shim() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'mmm_disable_emoji_shim' );

/**
 * Resolve an original-site path (/media/...) to its WordPress upload URL.
 *
 * Anything that is not a /media/ path is returned untouched so internal
 * links keep working exactly as they did on the source site.
 *
 * @param string $path Original site-relative path.
 * @return string URL.
 */
function mmm_media( $path ) {
	if ( '' === $path ) {
		return '';
	}
	// Already absolute or protocol-relative.
	if ( preg_match( '#^(https?:)?//#', $path ) || str_starts_with( $path, 'data:' ) ) {
		return $path;
	}
	if ( str_starts_with( $path, MMM_MEDIA_PREFIX ) ) {
		return home_url( MMM_UPLOADS_MEDIA . substr( $path, strlen( MMM_MEDIA_PREFIX ) ) );
	}
	if ( str_starts_with( $path, '/' ) ) {
		return home_url( $path );
	}
	return $path;
}

/**
 * Internal link that keeps the original trailing-slash URLs intact.
 *
 * @param string $path Original path, e.g. /what-we-do/save-vision/.
 * @return string URL.
 */
function mmm_link( $path ) {
	if ( preg_match( '#^(https?:)?//#', $path ) ) {
		return $path;
	}
	return home_url( $path );
}

/**
 * Build the CSS custom property string for an accent name, matching the
 * accent palette used by programmes and giving tiers.
 *
 * @param string $accent One of blue|navy|magenta|cyan.
 * @return array CSS class => true for Tailwind-style conditional classes.
 */
function mmm_accent( $accent ) {
	$map = array(
		'blue'    => array(
			'text'  => 'text-[#075BD6]',
			'bg'    => 'bg-[#075BD6]',
			'border'=> 'border-[#075BD6]',
			'soft'  => 'bg-[#F3F7FF]',
		),
		'navy'    => array(
			'text'  => 'text-[#003475]',
			'bg'    => 'bg-[#003475]',
			'border'=> 'border-[#003475]',
			'soft'  => 'bg-[#F5F7FA]',
		),
		'magenta' => array(
			'text'  => 'text-[#DD127B]',
			'bg'    => 'bg-[#DD127B]',
			'border'=> 'border-[#DD127B]',
			'soft'  => 'bg-[#FDF0F6]',
		),
		'cyan'    => array(
			'text'  => 'text-[#0891B2]',
			'bg'    => 'bg-[#0891B2]',
			'border'=> 'border-[#0891B2]',
			'soft'  => 'bg-[#ECFEFF]',
		),
	);
	return $map[ $accent ] ?? $map['blue'];
}

/**
 * Escape helper that mirrors React's default: text nodes are escaped,
 * attribute values are escaped, URLs are left to esc_url.
 *
 * @param string $text Text.
 * @return string Escaped text.
 */
function mmm_e( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}
