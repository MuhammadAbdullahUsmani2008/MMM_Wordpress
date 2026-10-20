<?php
/**
 * Data attributes the behaviour layer expects.
 *
 * The original site rendered some interactive elements client-side, so their
 * hooks never existed in the server HTML. Where the markup alone does not
 * carry enough information for assets/js/mmm.js, it is added here rather
 * than being spread across the templates.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Count-up hook for a headline statistic.
 *
 * The source rendered these as plain text inside a <span>; the JS needs the
 * target value explicitly.
 *
 * @param int    $value    Final number.
 * @param string $suffix   Anything after the number, e.g. "M+" or "%".
 * @param int    $decimals Digits after the decimal point.
 * @return string Attribute string.
 */
function mmm_count_attrs( $value, $suffix = '', $decimals = 0 ) {
	return sprintf(
		' data-count-to="%s" data-count-suffix="%s" data-count-decimals="%d"',
		esc_attr( (string) $value ),
		esc_attr( $suffix ),
		(int) $decimals
	);
}

/**
 * Reveal wrapper, matching Reveal.tsx exactly.
 *
 * @param string      $direction up|left|right|scale.
 * @param int         $delay     Milliseconds.
 * @param string      $classes   Extra classes for the wrapper element.
 * @param bool        $now       Reveal without waiting for scroll.
 * @return string Opening attributes (class, data-visible, style).
 */
function mmm_reveal_attrs( $direction = 'up', $delay = 0, $classes = '', $now = false ) {
	$map = array(
		'up'    => '',
		'left'  => 'reveal-left',
		'right' => 'reveal-right',
		'scale' => 'reveal-scale',
	);

	$class = 'reveal';
	if ( isset( $map[ $direction ] ) && '' !== $map[ $direction ] ) {
		$class .= ' ' . $map[ $direction ];
	}
	if ( $classes ) {
		$class .= ' ' . $classes;
	}

	$attrs = sprintf(
		'class="%s" data-visible="false" style="--reveal-delay:%dms"',
		esc_attr( trim( $class ) ),
		(int) $delay
	);
	if ( $now ) {
		$attrs .= ' data-reveal-now';
	}
	return $attrs;
}

/**
 * Wrap content in a reveal element.
 *
 * @param string      $content   Inner HTML.
 * @param string      $direction up|left|right|scale.
 * @param int         $delay     Delay in ms.
 * @param string      $tag       Wrapper tag.
 * @param string      $classes   Extra classes.
 * @return string
 */
function mmm_reveal( $content, $direction = 'up', $delay = 0, $tag = 'div', $classes = '' ) {
	$tag = preg_replace( '/[^a-z0-9]/i', '', $tag );
	if ( ! $tag ) {
		$tag = 'div';
	}
	return sprintf(
		'<%1$s %2$s>%3$s</%1$s>',
		$tag,
		mmm_reveal_attrs( $direction, $delay, $classes ),
		$content
	);
}

/**
 * Emit the search index as JSON for the header search panel.
 */
function mmm_print_search_index() {
	$index = mmm_search_index();
	if ( ! $index ) {
		return;
	}
	printf(
		'<script type="application/json" id="mmm-search-index">%s</script>' . "\n",
		wp_json_encode( array_values( $index ) )
	);
}
