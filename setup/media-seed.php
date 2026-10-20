<?php
/**
 * Register the original site's photographs in the WordPress media library.
 *
 * The files are copied into wp-content/uploads/media/ by the shell step that
 * runs before this script, preserving the original folder structure
 * (field/, disaster/, gaza-food/, ...) so the library matches how the
 * photography was organised on the source site.
 *
 * Images are registered without regenerating intermediate sizes: the source
 * site serves the original files, and the templates reference the same
 * originals, so no derivatives are required.
 *
 * Run with: docker compose run --rm wpcli eval-file /setup/media-seed.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$root      = ABSPATH . 'wp-content/uploads/media';
$base_url  = wp_upload_dir()['baseurl'] . '/media';
$progress  = get_option( 'mmm_media_seeded', array() );

if ( ! is_dir( $root ) ) {
	echo "MISSING: $root\n";
	return;
}

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
);

$created = 0;
$skipped = 0;
$errors  = 0;
$files   = 0;

foreach ( $iterator as $file ) {
	if ( ! $file->isFile() ) {
		continue;
	}
	$abs  = $file->getPathname();
	$rel  = ltrim( str_replace( '\\', '/', substr( $abs, strlen( $root ) ) ), '/' );
	$files++;

	// Skip non-attachment files.
	$ext = strtolower( pathinfo( $abs, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'mp4', 'webm', 'pdf' ), true ) ) {
		$skipped++;
		continue;
	}

	if ( isset( $progress[ $rel ] ) ) {
		$skipped++;
		continue;
	}

	$url     = $base_url . '/' . $rel;
	$already = get_posts(
		array(
			'post_type'   => 'attachment',
			'fields'      => 'ids',
			'meta_key'    => '_wp_attached_file',
			'meta_value'  => 'media/' . $rel,
			'numberposts' => 1,
			'post_status' => 'any',
		)
	);
	if ( $already ) {
		$progress[ $rel ] = $already[0];
		update_option( 'mmm_media_seeded', $progress, false );
		$skipped++;
		continue;
	}

	$parent_dir = dirname( $rel );
	$upload_dir = wp_upload_dir();

	// The files already live under wp-content/uploads/media/..., so
	// wp_insert_attachment() derives the correct relative path from the
	// absolute path and no upload_dir filtering is required.
	$title     = sanitize_file_name( pathinfo( $abs, PATHINFO_FILENAME ) );
	$mime      = wp_check_filetype( $abs )['type'] ?: 'application/octet-stream';
	$attach_id = wp_insert_attachment(
		array(
			'post_mime_type' => $mime,
			'post_title'     => $title,
			'post_status'    => 'inherit',
			'guid'           => $url,
		),
		$abs,
		0,
		true
	);

	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		$errors++;
		if ( $errors < 10 ) {
			echo 'ERROR ' . $rel . ': ' . ( is_wp_error( $attach_id ) ? $attach_id->get_error_message() : 'unknown' ) . "\n";
		}
		continue;
	}

	update_post_meta( $attach_id, '_wp_attached_file', 'media/' . $rel );
	update_post_meta( $attach_id, '_mmm_original_path', '/media/' . $rel );
	$progress[ $rel ] = (int) $attach_id;
	$created++;

	if ( 0 === $created % 50 ) {
		update_option( 'mmm_media_seeded', $progress, false );
		echo "progress: $created created\n";
	}
}

update_option( 'mmm_media_seeded', $progress, false );

echo "---- MEDIA REPORT ----\n";
echo "* files scanned: $files\n";
echo "* registered:    $created\n";
echo "* already/skip:  $skipped\n";
echo "* errors:        $errors\n";
echo "* total in lib:  " . count( $progress ) . "\n";
echo "----------------------\n";
