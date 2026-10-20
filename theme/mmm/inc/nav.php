<?php
/**
 * Navigation helpers: active state and the mega-menu markup.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The request path in the original site's form (always leading and
 * trailing slash, e.g. "/about/").
 *
 * @return string
 */
function mmm_current_path() {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '/';
	$path = (string) $path;
	$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( $home && str_starts_with( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	$path = '/' . ltrim( $path, '/' );
	if ( ! str_ends_with( $path, '/' ) && ! str_contains( basename( $path ), '.' ) ) {
		$path .= '/';
	}
	return $path;
}

/**
 * Whether a navigation entry is the current section.
 *
 * Mirrors the source site exactly: "/" matches only the home page, every
 * other entry matches by path prefix, which is why a programme page
 * (/what-we-do/save-vision/) marks "What We Do" active, while /donate/ and
 * /contact/ — which are not top-level entries — never highlight anything.
 *
 * @param string $href Original href from the nav data.
 * @return bool
 */
function mmm_is_nav_active( $href ) {
	$current = mmm_current_path();
	$href    = (string) $href;
	if ( '' === $href || str_starts_with( $href, '#' ) ) {
		return false;
	}
	if ( '/' === $href ) {
		return '/' === $current;
	}
	if ( ! str_ends_with( $href, '/' ) && ! str_contains( basename( $href ), '.' ) ) {
		$href .= '/';
	}
	return str_starts_with( $current, $href );
}

/**
 * Top-level nav entries, optionally just those that are links.
 *
 * @return array<int,array>
 */
function mmm_nav_items() {
	return mmm_nav();
}

/**
 * Render one mega-menu column (desktop panel).
 *
 * @param array $column Column data with heading and links.
 */
function mmm_render_nav_column( $column ) {
	echo '<div>';
	if ( ! empty( $column['heading'] ) ) {
		echo '<h3 class="border-b border-[#DCE2EA] pb-2 font-display text-[0.80rem] font-extrabold uppercase tracking-wider text-[#0A1020]">' . esc_html( $column['heading'] ) . '</h3>';
	}
	echo '<ul class="mt-2.5 space-y-1">';
	foreach ( (array) ( $column['links'] ?? array() ) as $link ) {
		echo '<li>';
		echo '<a href="' . esc_url( mmm_link( $link['href'] ?? '' ) ) . '" class="group flex items-center justify-between rounded-lg px-2 py-1.5 text-[0.84rem] font-semibold text-[#4B5563] transition-colors hover:bg-[#F3F7FF] hover:text-[#075BD6]">';
		echo '<span class="leading-snug">' . esc_html( $link['label'] ?? '' ) . '</span>';
		echo '<svg viewBox="0 0 24 24" aria-hidden="true" class="h-3 w-3 shrink-0 text-slate-300 opacity-0 transition-all duration-200 group-hover:translate-x-0.5 group-hover:text-[#075BD6] group-hover:opacity-100" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
		echo '</a></li>';
	}
	echo '</ul></div>';
}

/**
 * Render the desktop mega-menu panel.
 *
 * Reproduces DropdownPanel from the source site. The panel is always present
 * in the DOM and toggled with the `hidden` attribute, because React used to
 * insert it on demand; the visible result is the same.
 *
 * @param array $item  Nav item.
 * @param int   $idx   Index in the nav array, used for left/right alignment.
 */
function mmm_render_dropdown_panel( $item, $idx ) {
	$cols      = (array) ( $item['columns'] ?? array() );
	$is_right  = $idx >= 2;
	$is_three  = count( $cols ) >= 3;

	$align = $is_right ? 'right-0' : ( $is_three ? 'left-0 sm:-left-12 xl:left-0' : 'left-0' );
	$width = $is_three ? 'w-[min(690px,calc(100vw-2rem))]' : 'w-[min(480px,calc(100vw-2rem))]';
	$grid  = $is_three ? 'grid-cols-1 sm:grid-cols-3' : 'grid-cols-1 sm:grid-cols-2';

	printf(
		'<div data-mega-panel hidden class="animate-drift-up absolute top-full z-50 mt-2 max-h-[calc(100vh-130px)] overflow-y-auto rounded-2xl border border-[#DCE2EA] bg-white p-5 sm:p-6 shadow-[0_20px_48px_-12px_rgba(10,16,32,0.18)] %s %s">',
		esc_attr( $align ),
		esc_attr( $width )
	);
	printf( '<div class="grid gap-5 sm:gap-6 %s">', esc_attr( $grid ) );
	foreach ( $cols as $col ) {
		mmm_render_nav_column( $col );
	}
	echo '</div>';

	$feature = $item['feature'] ?? null;
	if ( ! empty( $feature ) ) {
		echo '<div class="mt-5 border-t border-[#DCE2EA] pt-4">';
		echo '<a href="' . esc_url( mmm_link( $feature['href'] ?? '' ) ) . '" class="group flex items-center justify-between gap-3 rounded-xl bg-[#F5F7FA] p-3.5 border border-[#DCE2EA]/70 transition-colors hover:bg-[#F3F7FF] hover:border-[#DCE6FB]">';
		echo '<div class="min-w-0 pr-2">';
		echo '<span class="font-display text-[0.78rem] font-extrabold uppercase tracking-wider text-[#075BD6]">Featured Focus</span>';
		echo '<p class="mt-0.5 truncate font-display text-[0.92rem] font-bold text-[#0A1020]">' . esc_html( $feature['title'] ?? '' ) . '</p>';
		echo '</div>';
		echo '<span class="shrink-0 inline-flex items-center gap-1.5 font-display text-[0.82rem] font-bold text-[#075BD6]">';
		echo '<span>' . esc_html( $feature['cta'] ?? '' ) . '</span>';
		echo '<svg viewBox="0 0 24 24" aria-hidden="true" class="h-3 w-3 transition-transform duration-200 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
		echo '</span></a></div>';
	}
	echo '</div>';
}
