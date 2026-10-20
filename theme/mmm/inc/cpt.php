<?php
/**
 * Content types for the MMM site.
 *
 * Two post types mirror the source site's data collections:
 *   program -> /what-we-do/{slug}/
 *   report  -> /media/{slug}/
 *
 * Body copy lives in post_content so it is edited in the normal editor;
 * structured fields (highlights, gallery, region, accent, place, kind, date)
 * are stored as post meta and edited through the meta boxes below.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the programme post type.
 */
function mmm_register_program_cpt() {
	register_post_type(
		'program',
		array(
			'labels'       => array(
				'name'          => __( 'Programmes', 'mmm' ),
				'singular_name' => __( 'Programme', 'mmm' ),
				'add_new_item'  => __( 'Add New Programme', 'mmm' ),
				'edit_item'     => __( 'Edit Programme', 'mmm' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-heart',
			'menu_position'=> 20,
			'rewrite'      => array(
				'slug'       => 'what-we-do',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'mmm_register_program_cpt' );

/**
 * Register the field report post type.
 */
function mmm_register_report_cpt() {
	register_post_type(
		'report',
		array(
			'labels'       => array(
				'name'          => __( 'Field Reports', 'mmm' ),
				'singular_name' => __( 'Field Report', 'mmm' ),
				'add_new_item'  => __( 'Add New Field Report', 'mmm' ),
				'edit_item'     => __( 'Edit Field Report', 'mmm' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-media-spread',
			'menu_position'=> 21,
			'rewrite'      => array(
				'slug'       => 'media',
				'with_front' => false,
			),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'mmm_register_report_cpt' );

/**
 * Flush rewrites once so /what-we-do/{slug}/ and /media/{slug}/ resolve.
 */
function mmm_after_switch_theme() {
	mmm_register_program_cpt();
	mmm_register_report_cpt();
	// Soft flush: the hard flush would try to rewrite .htaccess, which this
	// install cannot do — mmm_ensure_htaccess() owns that file instead.
	flush_rewrite_rules( false );
}
add_action( 'after_switch_theme', 'mmm_after_switch_theme' );

/**
 * Meta keys used by both content types.
 */
function mmm_meta_keys() {
	return array(
		'program' => array(
			'_mmm_summary'  => array( 'label' => 'Summary', 'type' => 'text' ),
			'_mmm_subtitle' => array( 'label' => 'Subtitle', 'type' => 'text' ),
			'_mmm_region'   => array( 'label' => 'Region', 'type' => 'select', 'options' => array( 'Pakistan', 'Gaza', 'Pakistan and Gaza' ) ),
			'_mmm_accent'   => array( 'label' => 'Accent', 'type' => 'select', 'options' => array( 'blue', 'navy', 'magenta', 'cyan' ) ),
			'_mmm_image'    => array( 'label' => 'Hero image (/media/...)', 'type' => 'text' ),
			'_mmm_gallery'  => array( 'label' => 'Gallery images (one /media/ path per line)', 'type' => 'textarea' ),
			'_mmm_highlights'=> array( 'label' => 'Highlights (Label = Value, one per line)', 'type' => 'textarea' ),
		),
		'report'  => array(
			'_mmm_excerpt'  => array( 'label' => 'Excerpt', 'type' => 'textarea' ),
			'_mmm_place'    => array( 'label' => 'Place', 'type' => 'text' ),
			'_mmm_kind'     => array( 'label' => 'Kind', 'type' => 'text' ),
			'_mmm_date'     => array( 'label' => 'Date (free text)', 'type' => 'text' ),
			'_mmm_image'    => array( 'label' => 'Image (/media/...)', 'type' => 'text' ),
		),
	);
}

/**
 * Render the meta boxes.
 *
 * @param WP_Post $post Current post.
 */
function mmm_render_meta_boxes( $post ) {
	wp_nonce_field( 'mmm_save_meta', 'mmm_meta_nonce' );
	$keys = mmm_meta_keys()[ $post->post_type ] ?? array();
	foreach ( $keys as $key => $def ) {
		$value = get_post_meta( $post->ID, $key, true );
		echo '<p style="margin-bottom:14px"><strong style="display:block;margin-bottom:4px">' . esc_html( $def['label'] ) . '</strong>';
		if ( 'textarea' === $def['type'] ) {
			echo '<textarea name="' . esc_attr( $key ) . '" rows="5" style="width:100%">' . esc_textarea( $value ) . '</textarea>';
		} elseif ( 'select' === $def['type'] ) {
			echo '<select name="' . esc_attr( $key ) . '" style="width:100%;max-width:340px">';
			foreach ( $def['options'] as $opt ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $opt ) );
			}
			echo '</select>';
		} else {
			echo '<input type="text" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" style="width:100%;max-width:640px" />';
		}
		echo '</p>';
	}
}

/**
 * Register and save the meta boxes.
 */
function mmm_register_meta_boxes() {
	foreach ( array( 'program', 'report' ) as $type ) {
		add_meta_box( 'mmm_programme_data', __( 'Programme data', 'mmm' ), 'mmm_render_meta_boxes', $type, 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'mmm_register_meta_boxes' );

/**
 * Persist meta box values.
 *
 * @param int $post_id Post id.
 */
function mmm_save_meta( $post_id ) {
	if ( ! isset( $_POST['mmm_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mmm_meta_nonce'] ), 'mmm_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$type = get_post_type( $post_id );
	$keys = mmm_meta_keys()[ $type ] ?? array();
	foreach ( $keys as $key => $def ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		$clean = 'textarea' === $def['type'] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		update_post_meta( $post_id, $key, $clean );
	}
}
add_action( 'save_post', 'mmm_save_meta' );

/**
 * Parse "Label = Value" lines into the highlights array used by templates.
 *
 * @param string $raw Raw textarea value.
 * @return array<int, array{label:string,value:string}>
 */
function mmm_parse_highlights( $raw ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '=', $line, 2 ) );
		$out[] = array(
			'label' => $parts[0],
			'value' => $parts[1] ?? '',
		);
	}
	return $out;
}

/**
 * Parse a newline separated list of /media/ paths.
 *
 * @param string $raw Raw value.
 * @return array<int,string>
 */
function mmm_parse_list( $raw ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$out[] = $line;
		}
	}
	return $out;
}
