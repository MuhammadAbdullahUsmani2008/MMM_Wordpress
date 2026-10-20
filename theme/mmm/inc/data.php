<?php
/**
 * Content access layer.
 *
 * Every getter reads from WordPress — post types for the collections that
 * need per-item editing, options for the scalar organisation data and the
 * small repeatable lists. Nothing here holds a hard coded copy of the copy;
 * the original values live in the database, seeded once from inc/seed.php.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Organisation details (contact, address, social accounts, bank details).
 *
 * @return array<string,string|array>
 */
function mmm_org() {
	$org = get_option( 'mmm_org', array() );
	return is_array( $org ) ? $org : array();
}

/**
 * Single scalar org value, e.g. mmm_org_value('email').
 *
 * @param string $key Field key.
 * @return string
 */
function mmm_org_value( $key ) {
	$org = mmm_org();
	$val = $org[ $key ] ?? '';
	if ( is_array( $val ) ) {
		return '';
	}
	return (string) $val;
}

/**
 * Social accounts, each { name, href }.
 *
 * @return array<int,array{name:string,href:string}>
 */
function mmm_social() {
	$social = get_option( 'mmm_social', array() );
	return is_array( $social ) ? $social : array();
}

/**
 * Headline impact counters.
 *
 * @return array<int,array{value:int,suffix:string,label:string,detail:string}>
 */
function mmm_impact_stats() {
	$v = get_option( 'mmm_impact_stats', array() );
	return is_array( $v ) ? $v : array();
}

/**
 * Giving tiers shown on the donate page.
 *
 * @return array<int,array{amount:string,title:string,body:string,accent:string}>
 */
function mmm_giving_tiers() {
	$v = get_option( 'mmm_giving_tiers', array() );
	return is_array( $v ) ? $v : array();
}

/**
 * Donation allocation split.
 *
 * @return array<int,array{label:string,value:int}>
 */
function mmm_allocation() {
	$v = get_option( 'mmm_allocation', array() );
	return is_array( $v ) ? $v : array();
}

/**
 * "Our means" statements.
 *
 * @return array<int,string>
 */
function mmm_means() {
	$v = get_option( 'mmm_means', array() );
	return is_array( $v ) ? $v : array();
}

/**
 * Regions where the organisation works.
 *
 * @return array<int,array{name:string,detail:string,focus:string}>
 */
function mmm_regions() {
	$v = get_option( 'mmm_regions', array() );
	return is_array( $v ) ? $v : array();
}

/**
 * Main navigation including mega-menu columns and feature panel.
 *
 * @return array<int,array>
 */
function mmm_nav() {
	$v = get_option( 'mmm_nav', array() );
	return is_array( $v ) ? $v : array();
}

/**
 * Split post content into plain paragraphs.
 *
 * Handles both classic paragraph text and block markup, so editors can use
 * whichever editor the installation provides without changing the output.
 *
 * @param string $content Post content.
 * @return array<int,string>
 */
function mmm_paragraphs( $content ) {
	$content = (string) $content;
	// Strip block comments and their handles, leaving inner markup.
	$content = preg_replace( '/<!--\s*\/?wp:[a-z][a-z0-9_]*(\s+\{.*?\})?\s*-->/s', '', $content );
	$chunks = preg_split( '/\s*\n\s*\n\s*/', trim( $content ) );
	$out    = array();
	foreach ( (array) $chunks as $chunk ) {
		$chunk = trim( $chunk );
		if ( '' !== $chunk ) {
			$out[] = $chunk;
		}
	}
	return $out;
}

/**
 * All programmes, in menu order.
 *
 * @return array<int,array>
 */
function mmm_programs() {
	static $programs = null;
	if ( null !== $programs ) {
		return $programs;
	}
	$q = new WP_Query(
		array(
			'post_type'      => 'program',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		)
	);
	$programs = array();
	foreach ( $q->posts as $p ) {
		$programs[] = mmm_map_program( $p );
	}
	return $programs;
}

/**
 * Map a program post to the shape the templates expect.
 *
 * @param WP_Post $p Post.
 * @return array
 */
function mmm_map_program( $p ) {
	return array(
		'slug'       => $p->post_name,
		'title'      => $p->post_title,
		'region'     => (string) get_post_meta( $p->ID, '_mmm_region', true ),
		'subtitle'   => (string) get_post_meta( $p->ID, '_mmm_subtitle', true ),
		'summary'    => (string) get_post_meta( $p->ID, '_mmm_summary', true ),
		'body'       => mmm_paragraphs( $p->post_content ),
		'image'      => (string) get_post_meta( $p->ID, '_mmm_image', true ),
		'gallery'    => mmm_parse_list( get_post_meta( $p->ID, '_mmm_gallery', true ) ),
		'accent'     => (string) get_post_meta( $p->ID, '_mmm_accent', true ),
		'highlights' => mmm_parse_highlights( get_post_meta( $p->ID, '_mmm_highlights', true ) ),
		'id'         => $p->ID,
	);
}

/**
 * @param string $slug Programme slug.
 * @return array|null
 */
function mmm_program_by_slug( $slug ) {
	foreach ( mmm_programs() as $p ) {
		if ( $p['slug'] === $slug ) {
			return $p;
		}
	}
	return null;
}

/**
 * All field reports.
 *
 * @return array<int,array>
 */
function mmm_reports() {
	static $reports = null;
	if ( null !== $reports ) {
		return $reports;
	}
	$q = new WP_Query(
		array(
			'post_type'      => 'report',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		)
	);
	$reports = array();
	foreach ( $q->posts as $p ) {
		$reports[] = array(
			'slug'    => $p->post_name,
			'title'   => $p->post_title,
			'place'   => (string) get_post_meta( $p->ID, '_mmm_place', true ),
			'kind'    => (string) get_post_meta( $p->ID, '_mmm_kind', true ),
			'excerpt' => (string) get_post_meta( $p->ID, '_mmm_excerpt', true ),
			'body'    => mmm_paragraphs( $p->post_content ),
			'image'   => (string) get_post_meta( $p->ID, '_mmm_image', true ),
			'date'    => (string) get_post_meta( $p->ID, '_mmm_date', true ),
			'id'      => $p->ID,
		);
	}
	return $reports;
}

/**
 * @param string $slug Report slug.
 * @return array|null
 */
function mmm_report_by_slug( $slug ) {
	foreach ( mmm_reports() as $r ) {
		if ( $r['slug'] === $slug ) {
			return $r;
		}
	}
	return null;
}

/**
 * One value from the bank details block.
 *
 * @param string $key title|bank|account|iban|branch.
 * @return string
 */
function mmm_bank( $key ) {
	$bank = get_option( 'mmm_org_bank', array() );
	return is_array( $bank ) && isset( $bank[ $key ] ) ? (string) $bank[ $key ] : '';
}

/**
 * URL for a named social account.
 *
 * @param string $name Facebook|Instagram|X|YouTube.
 * @return string
 */
function mmm_social_url( $name ) {
	foreach ( mmm_social() as $account ) {
		if ( ( $account['name'] ?? '' ) === $name ) {
			return (string) ( $account['href'] ?? '' );
		}
	}
	return '';
}

/**
 * Every gallery photograph, as a flat list.
 *
 * Each row is { src, group, region } — `group` is the key of the filter tab
 * it belongs to, `region` is the caption shown in the lightbox.
 *
 * @return array<int,array{src:string,group:string,region:string}>
 */
function mmm_gallery() {
	$v = get_option( 'mmm_gallery', array() );
	return is_array( $v ) ? $v : array();
}

/**
 * The gallery's filter tabs.
 *
 * The image list is flat; these definitions say what each tab is called and
 * which region it belongs to. Kept apart from mmm_gallery() so a tab can be
 * renamed without touching 250 image rows.
 *
 * @return array<int,array{key:string,label:string,region:string}>
 */
function mmm_gallery_groups() {
	$v = get_option( 'mmm_gallery_groups', array() );
	return is_array( $v ) ? $v : array();
}

/**
 * Label for a gallery group key, with a sensible fallback.
 *
 * @param string $key Group key, e.g. "gaza-water".
 * @return string
 */
function mmm_gallery_group_label( $key ) {
	foreach ( mmm_gallery_groups() as $group ) {
		if ( ( $group['key'] ?? '' ) === $key ) {
			return (string) ( $group['label'] ?? '' );
		}
	}
	return 'Field work';
}

/**
 * Flat search index powering the header search panel.
 *
 * @return array<int,array{title:string,section:string,href:string,terms:string}>
 */
function mmm_search_index() {
	$v = get_option( 'mmm_search_index', array() );
	return is_array( $v ) ? $v : array();
}
