<?php
/**
 * Document head output.
 *
 * Reproduces the metadata the source site emitted: title template,
 * description, keywords, robots, Open Graph, Twitter card, theme colour
 * and the NGO JSON-LD block.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-route document metadata extracted from the original build.
 *
 * @return array<string,array{title:string,description:string,ogimage:string}>
 */
function mmm_meta_defaults() {
	static $defaults = null;
	if ( null === $defaults ) {
		$file = MMM_THEME_DIR . '/inc/meta-defaults.json';
		$data = file_exists( $file ) ? json_decode( file_get_contents( $file ), true ) : null;
		$defaults = is_array( $data ) ? $data : array();
	}
	return $defaults;
}

/**
 * Which entry of meta-defaults.json describes the current document.
 *
 * Mirrors the original file layout exactly: the home page is "index", a page
 * is its slug, a programme is "what-we-do/{slug}" and a report is
 * "media/{slug}".
 *
 * @return string
 */
function mmm_meta_route() {
	if ( is_404() ) {
		return '404';
	}
	if ( is_front_page() ) {
		return 'index';
	}
	if ( ! is_singular() ) {
		return 'index';
	}
	$post = get_queried_object();
	if ( $post instanceof WP_Post ) {
		if ( 'program' === $post->post_type ) {
			return 'what-we-do/' . $post->post_name;
		}
		if ( 'report' === $post->post_type ) {
			return 'media/' . $post->post_name;
		}
		return $post->post_name;
	}
	return 'index';
}

/**
 * Metadata for the current document, with any admin override applied.
 *
 * @return array{title:string,description:string,ogimage:string}
 */
function mmm_meta_for_current() {
	$route   = mmm_meta_route();
	$defaults = mmm_meta_defaults();
	$meta     = $defaults[ $route ] ?? ( $defaults['index'] ?? array() );
	$meta     = array_merge(
		array(
			'title'       => '',
			'description' => '',
			'ogimage'     => '',
		),
		$meta
	);

	if ( is_singular() ) {
		$stored = get_post_meta( get_queried_object_id(), '_mmm_meta_description', true );
		if ( is_string( $stored ) && '' !== trim( $stored ) ) {
			$meta['description'] = $stored;
		}
		// The programme/report hero image doubles as the social image.
		if ( '' === $meta['ogimage'] || 'program' === get_post_type() || 'report' === get_post_type() ) {
			$image = get_post_meta( get_queried_object_id(), '_mmm_image', true );
			if ( is_string( $image ) && '' !== $image ) {
				$meta['ogimage'] = $image;
			}
		}
	}

	return $meta;
}

/**
 * Site defaults, matching the original metadata exactly.
 *
 * @return array<string,string>
 */
function mmm_head_defaults() {
	return array(
		'description' => 'Muslim Medical Mission is a volunteer network of doctors, paramedics and students running free medical camps, disaster relief, clean water and food distribution across Pakistan and into Gaza.',
		'keywords'    => 'Muslim Medical Mission, MMM Pakistan, free medical camps Pakistan, flood relief Pakistan, Gaza relief, Islamic charity healthcare',
		'ogTitle'     => 'Muslim Medical Mission | Free healthcare and relief in Pakistan and Gaza',
		'ogDesc'      => 'Free medical camps, disaster response, clean water and food relief, delivered by Muslim healthcare professionals.',
		'ogImage'     => '/media/disaster/disaster-11.jpg',
		'twitterSite' => '@MMMPakOfficial',
		'themeColor'  => '#046BD2',
	);
}

/**
 * Title for the current document, following the original template.
 *
 * Home and 404: "Muslim Medical Mission | Free healthcare and relief…"
 * Everything else: "{Document title} | Muslim Medical Mission"
 *
 * @param string $title Value WordPress computed.
 * @return string
 */
function mmm_document_title( $title = '' ) {
	$org = mmm_org_value( 'name' ) ?: 'Muslim Medical Mission';

	if ( is_admin() ) {
		return $title;
	}

	$route = mmm_meta_route();
	if ( 'index' === $route || '404' === $route ) {
		return $org . ' | Free healthcare and relief in Pakistan and Gaza';
	}

	$defaults = mmm_meta_defaults();
	$doc      = $defaults[ $route ]['title'] ?? '';
	if ( '' === $doc ) {
		$doc = is_singular() ? (string) get_the_title() : '';
	}
	return $doc ? $doc . ' | ' . $org : $org;
}
add_filter( 'pre_get_document_title', 'mmm_document_title' );

/**
 * Emit the theme colour before the document title, as the original did.
 */
function mmm_print_head_color() {
	$d = mmm_head_defaults();
	echo '<meta name="theme-color" content="' . esc_attr( $d['themeColor'] ) . '" />' . "\n";
}
add_action( 'wp_head', 'mmm_print_head_color', 0 );

/**
 * Emit the metadata tags after the document title.
 */
function mmm_print_head_meta() {
	$d    = mmm_head_defaults();
	$meta = mmm_meta_for_current();

	$description = $meta['description'] ?: $d['description'];
	$og_title    = mmm_document_title();
	$og_image    = $meta['ogimage'] ?: $d['ogImage'];
	$site_name   = mmm_org_value( 'name' ) ?: 'Muslim Medical Mission';

	echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
	echo '<meta name="keywords" content="' . esc_attr( $d['keywords'] ) . '" />' . "\n";
	echo '<meta name="robots" content="index, follow" />' . "\n";

	echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $d['ogDesc'] ) . '" />' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";
	echo '<meta property="og:image" content="' . esc_url( home_url( $og_image ) ) . '" />' . "\n";
	echo '<meta property="og:type" content="website" />' . "\n";

	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	echo '<meta name="twitter:site" content="' . esc_attr( $d['twitterSite'] ) . '" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $d['ogDesc'] ) . '" />' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( home_url( $og_image ) ) . '" />' . "\n";

	echo '<link rel="icon" href="' . esc_url( home_url( '/icon.png' ) ) . '" />' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( home_url( '/icon.png' ) ) . '" />' . "\n";
	echo '<link rel="canonical" href="' . esc_url( mmm_canonical_url() ) . '" />' . "\n";
}
add_action( 'wp_head', 'mmm_print_head_meta', 20 );

/**
 * Canonical URL for the current document.
 *
 * @return string
 */
function mmm_canonical_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		return get_permalink();
	}
	return home_url( mmm_current_path() );
}

/**
 * Description for the current document, with the admin override applied.
 *
 * @return string
 */
function mmm_meta_description() {
	return mmm_meta_for_current()['description'];
}

/**
 * Hero image to preload for the current page.
 *
 * The source site preloads the logo plus one hero image per page; templates
 * call mmm_hero_preload( '/media/...' ) before get_header().
 *
 * @param string|null $path Path to set, or null to read.
 * @return string|null
 */
function mmm_hero_preload( $path = null ) {
	static $value = '';
	if ( null !== $path ) {
		$value = $path;
		return $value;
	}
	return $value;
}

/**
 * The search overlay panel.
 *
 * React inserted this on demand; here it is always in the DOM and toggled
 * with the `hidden` attribute by mmm.js. Results come from the search index
 * seeded into the mmm_search_index option.
 */
function mmm_render_search_panel() {
	?>
<div data-search-panel hidden class="animate-drift-up border-b border-slate-200 bg-white shadow-xl"><div class="shell-wide py-4 sm:py-6">
<label class="flex items-center gap-2 sm:gap-3 border-b-2 border-slate-200 pb-2.5 sm:pb-3 focus-within:border-[#046BD2]">
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4.5 w-4.5 sm:h-5 sm:w-5 shrink-0 text-[#046BD2]" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="2.2"></circle><path d="M16.5 16.5L21 21" stroke-width="2.2" stroke-linecap="round"></path></svg>
<span class="sr-only">Search this site</span>
<input type="search" data-search-input placeholder="Search programmes, flood camps, Gaza, Zakat..." class="w-full bg-transparent font-display text-[0.95rem] sm:text-[1.1rem] font-bold text-[#333333] outline-none placeholder:font-normal placeholder:text-xs sm:placeholder:text-sm placeholder:text-slate-400" />
<button type="button" data-search-close class="shrink-0 rounded-md px-2.5 py-1 font-display text-[0.8rem] sm:text-[0.85rem] font-bold text-[#7A7A7A] transition-colors hover:bg-slate-100 hover:text-[#333333]">Esc / Close</button>
</label>
<ul data-search-results hidden class="mt-4 grid gap-1 sm:grid-cols-2"></ul>
</div></div>
	<?php
}
/**
 * The NGO structured-data block the source site printed in the layout.
 */
function mmm_print_json_ld() {
	$org    = mmm_org();
	$social = wp_list_pluck( mmm_social(), 'href' );
	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'NGO',
		'name'            => $org['legalName'] ?? 'Muslim Medical Mission Foundation',
		'alternateName'   => $org['short'] ?? 'MMM',
		'url'             => home_url( '/' ),
		'logo'            => home_url( '/media/brand/logo.png' ),
		'slogan'          => $org['motto'] ?? '',
		'email'           => $org['email'] ?? '',
		'telephone'       => $org['phone'] ?? '',
		'address'         => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $org['address_line1'] ?? '',
			'addressLocality' => 'Lahore',
			'addressRegion'   => 'Punjab',
			'addressCountry'  => 'PK',
		),
		'sameAs'          => $social,
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
