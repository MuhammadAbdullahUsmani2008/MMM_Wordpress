<?php
/**
 * Editable page copy.
 *
 * The design lives in the template files; the words live here. A template
 * asks for a value with mmm_copy( 'about', 'intro', 'Original sentence' ) —
 * the third argument is the default, taken verbatim from the source build,
 * so the site is correct on first render with nothing seeded.
 *
 * Values are stored per page in the mmm_copy_{slug} option and edited
 * through the "Page copy" box on the page edit screen. Because the markup
 * itself is never in the database, editing a sentence can never break the
 * layout.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get (and on first sight, register) one editable string.
 *
 * @param string $slug    Page slug, e.g. "about".
 * @param string $key     Field key, e.g. "intro".
 * @param string $default Text from the original build.
 * @return string
 */
function mmm_copy( $slug, $key, $default = '' ) {
	$option = 'mmm_copy_' . sanitize_key( $slug );
	$all    = get_option( $option );

	if ( ! is_array( $all ) ) {
		$all = array();
	}

	if ( ! array_key_exists( $key, $all ) ) {
		$all[ $key ] = $default;
		// Written once per new key; afterwards get_option() is the only cost.
		update_option( $option, $all, false );
	}

	$value = $all[ $key ];

	// An admin may blank a field to hide it; never fall back to the default.
	return is_string( $value ) ? $value : (string) $value;
}

/**
 * Render an editable block of markup.
 *
 * Long prose that spans several elements is stored as raw HTML so the
 * paragraph breaks and inline emphasis survive editing.
 *
 * @param string $slug    Page slug.
 * @param string $key     Field key.
 * @param string $default Markup from the original build.
 * @return string Markup, already safe.
 */
function mmm_copy_html( $slug, $key, $default = '' ) {
	return wp_kses_post( mmm_copy( $slug, $key, $default ) );
}

/**
 * Every copy field stored for a page, for the edit screen.
 *
 * @param string $slug Page slug.
 * @return array<string,string>
 */
function mmm_copy_all( $slug ) {
	$all = get_option( 'mmm_copy_' . sanitize_key( $slug ) );
	return is_array( $all ) ? $all : array();
}

/**
 * Replace the plain-text copy of a page wholesale (used by the importer).
 *
 * @param string $slug   Page slug.
 * @param array  $values Key => value.
 */
function mmm_copy_set( $slug, $values ) {
	update_option( 'mmm_copy_' . sanitize_key( $slug ), $values, false );
}

/**
 * Register the meta box that exposes the copy on the page edit screen.
 *
 * The same box handles every page: it lists whatever keys the templates for
 * that page have already registered, so new editable fields appear without
 * any extra admin code.
 */
function mmm_register_copy_metabox() {
	add_meta_box(
		'mmm_page_copy',
		__( 'Page copy', 'mmm' ),
		'mmm_render_copy_metabox',
		'page',
		'normal',
		'high'
	);

	foreach ( array( 'page', 'program', 'report' ) as $type ) {
		add_meta_box(
			'mmm_meta_description',
			__( 'Search preview', 'mmm' ),
			'mmm_render_description_metabox',
			$type,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'mmm_register_copy_metabox' );

/**
 * Print the SEO description field.
 *
 * Pre-filled from the value the original build shipped with, so it is
 * correct without ever being touched.
 *
 * @param WP_Post $post The document.
 */
function mmm_render_description_metabox( $post ) {
	wp_nonce_field( 'mmm_save_description', 'mmm_desc_nonce' );

	$stored   = get_post_meta( $post->ID, '_mmm_meta_description', true );
	$route    = 'program' === $post->post_type ? 'what-we-do/' . $post->post_name
		: ( 'report' === $post->post_type ? 'media/' . $post->post_name : $post->post_name );
	$defaults = mmm_meta_defaults();
	$default  = $defaults[ $route ]['description'] ?? '';
	$value    = ( is_string( $stored ) && '' !== $stored ) ? $stored : $default;

	printf(
		'<label for="mmm_meta_description" class="screen-reader-text">%s</label>',
		esc_html__( 'Meta description', 'mmm' )
	);
	printf(
		'<textarea id="mmm_meta_description" name="mmm_meta_description" rows="5" style="width:100%%">%s</textarea>',
		esc_textarea( $value )
	);
	echo '<p class="description">' . esc_html__( 'Shown by search engines and when the page is shared.', 'mmm' ) . '</p>';
}

/**
 * Save the SEO description.
 *
 * @param int $post_id Document id.
 */
function mmm_save_description( $post_id ) {
	if ( ! isset( $_POST['mmm_desc_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mmm_desc_nonce'] ), 'mmm_save_description' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['mmm_meta_description'] ) ) {
		return;
	}
	update_post_meta(
		$post_id,
		'_mmm_meta_description',
		sanitize_textarea_field( wp_unslash( $_POST['mmm_meta_description'] ) )
	);
}
add_action( 'save_post', 'mmm_save_description' );

/**
 * Print the field list.
 *
 * @param WP_Post $post The page.
 */
function mmm_render_copy_metabox( $post ) {
	wp_nonce_field( 'mmm_save_copy', 'mmm_copy_nonce' );

	$slug  = $post->post_name;
	$copy  = mmm_copy_all( $slug );
	$blank = '<p class="description">' . __( 'No editable fields registered for this page yet. Open the site once and they will appear here.', 'mmm' ) . '</p>';

	echo '<p class="description" style="margin-bottom:12px">';
	echo esc_html__( 'Each field corresponds to one passage in the template. The text shown is what visitors currently see.', 'mmm' );
	echo '</p>';

	if ( ! $copy ) {
		echo $blank; // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}

	foreach ( $copy as $key => $value ) {
		$rows = substr_count( $value, "\n" ) + 1;
		$rows = max( 3, min( 18, $rows ) );
		$looks_html = (bool) preg_match( '/<(p|ul|ol|li|h[1-6]|strong|em|span|a|div)[\s>]/', $value );

		printf(
			'<p style="margin-bottom:14px"><strong style="display:block;margin-bottom:4px">%s <code style="font-weight:400;opacity:.6">%s</code></strong>',
			esc_html( $key ),
			esc_html( $looks_html ? 'html' : 'text' )
		);
		printf(
			'<textarea name="mmm_copy[%s]" rows="%d" style="width:100%%;font-family:inherit">%s</textarea>',
			esc_attr( $key ),
			(int) $rows,
			esc_textarea( $value )
		);
		echo '</p>';
	}
}

/**
 * Save edited copy.
 *
 * @param int $post_id Page id.
 */
function mmm_save_copy( $post_id ) {
	if ( ! isset( $_POST['mmm_copy_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mmm_copy_nonce'] ), 'mmm_save_copy' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'page' !== get_post_type( $post_id ) || ! isset( $_POST['mmm_copy'] ) || ! is_array( $_POST['mmm_copy'] ) ) {
		return;
	}

	$slug  = get_post_field( 'post_name', $post_id );
	$old   = mmm_copy_all( $slug );
	$input = wp_unslash( $_POST['mmm_copy'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	foreach ( $input as $key => $value ) {
		$key   = sanitize_key( $key );
		$value = (string) $value;
		// Preserve markup for fields that hold it; plain text otherwise.
		$old[ $key ] = isset( $old[ $key ] ) && (bool) preg_match( '/<[a-z][\s>]/i', $old[ $key ] )
			? wp_kses_post( $value )
			: sanitize_textarea_field( $value );
	}

	update_option( 'mmm_copy_' . sanitize_key( $slug ), $old, false );
}
add_action( 'save_post', 'mmm_save_copy' );
