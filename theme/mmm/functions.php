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

/**
 * Home page: tabbed social feeds (Smash Balloon free plugins).
 *
 * One tab per platform, in the same order as the "Follow Muslim Medical Mission
 * in the Field" cards. A tab only appears once its plugin is active and a feed has been
 * created in it, and nothing is printed until at least one feed exists.
 */
function mmm_social_feed_tabs() {
	global $wpdb;

	// Tab order: Facebook, YouTube, Instagram, X. "table" is where each plugin stores the feeds created in its "All Feeds" screen.
	$tabs = array(
		'facebook'  => array(
			'label'     => 'Facebook',
			'shortcode' => 'custom-facebook-feed',
			'table'     => 'cff_feeds',
		),
		'youtube'   => array(
			'label'     => 'YouTube',
			'shortcode' => 'youtube-feed',
			'table'     => 'sby_feeds',
		),
		'instagram' => array(
			'label'     => 'Instagram',
			'shortcode' => 'instagram-feed',
			'table'     => 'sbi_feeds',
		),
		'x'         => array(
			'label'     => 'X',
			'shortcode' => 'custom-twitter-feeds',
			'table'     => 'ctf_feeds',
		),
	);

	// Keep a tab only when its plugin is active and a feed exists; show that plugin's first feed.
	foreach ( $tabs as $key => $tab ) {
		$feed_id = 0;
		if ( shortcode_exists( $tab['shortcode'] ) ) {
			$table = $wpdb->prefix . $tab['table'];
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) === $table ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$feed_id = (int) $wpdb->get_var( "SELECT id FROM `{$table}` ORDER BY id ASC LIMIT 1" );
			}
		}
		if ( $feed_id > 0 ) {
			$tabs[ $key ]['feed'] = $feed_id;
		} else {
			unset( $tabs[ $key ] );
		}
	}

	if ( empty( $tabs ) ) {
		return;
	}

	$first = key( $tabs );

	echo '<div class="mmm-feed-tabs" data-mmm-feed-tabs><div class="mmm-feed-tabs__list" role="tablist" aria-label="Social media feeds">';
	foreach ( $tabs as $key => $tab ) {
		printf(
			'<button type="button" role="tab" class="mmm-feed-tabs__tab" id="mmm-feed-tab-%1$s" aria-controls="mmm-feed-panel-%1$s" aria-selected="%2$s" tabindex="%3$s">%4$s</button>',
			esc_attr( $key ),
			$key === $first ? 'true' : 'false',
			$key === $first ? '0' : '-1',
			esc_html( $tab['label'] )
		);
	}
	echo '</div>';
	foreach ( $tabs as $key => $tab ) {
		printf(
			'<div role="tabpanel" class="mmm-feed-tabs__panel" id="mmm-feed-panel-%1$s" aria-labelledby="mmm-feed-tab-%1$s"%2$s>',
			esc_attr( $key ),
			$key === $first ? '' : ' hidden'
		);
		echo do_shortcode( sprintf( '[%s feed=%d]', $tab['shortcode'], $tab['feed'] ) );
		echo '</div>';
	}
	echo '</div>';
	?>
<style>
.mmm-feed-tabs{margin-top:2rem;width:100%;min-width:0}
.mmm-feed-tabs__list{display:flex;gap:.5rem;overflow-x:auto;border-bottom:1px solid #DCE2EA;padding-bottom:.75rem}
.mmm-feed-tabs__tab{flex:0 0 auto;border:1px solid #DCE2EA;border-radius:9999px;background:#F5F7FA;color:#334155;font-weight:700;font-size:.92rem;line-height:1;padding:.7rem 1.15rem;cursor:pointer;transition:background-color .2s,color .2s,border-color .2s}
.mmm-feed-tabs__tab:hover{border-color:#075BD6;color:#075BD6;background:#fff}
.mmm-feed-tabs__tab[aria-selected="true"]{background:#075BD6;border-color:#075BD6;color:#fff}
.mmm-feed-tabs__tab:focus-visible{outline:2px solid #075BD6;outline-offset:2px}
.mmm-feed-tabs__panel{margin-top:1.5rem;min-width:0}
.mmm-feed-tabs__panel[hidden]{display:none}
</style>
<script>
(function(){
	var root=document.querySelector('[data-mmm-feed-tabs]');
	if(!root){return;}
	var tabs=[].slice.call(root.querySelectorAll('[role="tab"]'));
	function select(tab,focus){
		tabs.forEach(function(t){
			var on=t===tab;
			t.setAttribute('aria-selected',on?'true':'false');
			t.tabIndex=on?0:-1;
			var panel=document.getElementById(t.getAttribute('aria-controls'));
			if(panel){panel.hidden=!on;}
		});
		if(focus){tab.focus();}
		// Feeds measure themselves while visible, so let the newly shown one lay out again.
		window.dispatchEvent(new Event('resize'));
	}
	tabs.forEach(function(tab,i){
		tab.addEventListener('click',function(){select(tab,false);});
		tab.addEventListener('keydown',function(e){
			var next=null;
			if(e.key==='ArrowRight'){next=tabs[(i+1)%tabs.length];}
			else if(e.key==='ArrowLeft'){next=tabs[(i-1+tabs.length)%tabs.length];}
			else if(e.key==='Home'){next=tabs[0];}
			else if(e.key==='End'){next=tabs[tabs.length-1];}
			if(next){e.preventDefault();select(next,true);}
		});
	});
})();
</script>
	<?php
}
