<?php
/**
 * Add missing gallery tabs and synchronize the academy/conference collection.
 *
 * Existing image edits and unrelated tabs are preserved. The academy
 * collection is refreshed from seed-data.json and missing source rows are
 * added without re-seeding the rest of the site.
 *
 * Run: docker compose run --rm wpcli eval-file /setup/add-gallery-groups.php
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = mmm_load_seed_data();

if ( empty( $data['galleryGroups'] ) || empty( $data['gallery'] ) ) {
	echo "seed-data.json has no gallery or galleryGroups.\n";
	return;
}

$academy_key   = 'academy-conferences';
$academy_group = null;
foreach ( $data['galleryGroups'] as $group ) {
	if ( $academy_key === ( $group['key'] ?? '' ) ) {
		$academy_group = $group;
		break;
	}
}

if ( null === $academy_group ) {
	echo "seed-data.json has no academy-conferences tab.\n";
	return;
}

$academy_photos = array_values(
	array_filter(
		$data['gallery'],
		static function ( $photo ) use ( $academy_key ) {
			return $academy_key === ( $photo['group'] ?? '' );
		}
	)
);
$gallery_group_updates = array(
	'pillar-a-camp-05' => 'disaster',
	'pillar-a-camp-06' => 'disaster',
	'pillar-a-camp-02' => 'disaster',
	'pillar-a-camp-16' => 'disaster',
	'pillar-a-camp-03' => 'medical',
	'pillar-a-camp-09' => 'medical',
	'pillar-a-camp-10' => 'medical',
	'pillar-a-camp-15' => 'medical',
	'pillar-a-camp-14' => 'medical',
	'pillar-a-camp-13' => 'medical',
	'pillar-a-camp-12' => 'medical',
	'pillar-a-camp-11' => 'medical',
);
$target_photos = array();
foreach ( $data['gallery'] as $photo ) {
	$name = pathinfo( $photo['src'] ?? '', PATHINFO_FILENAME );
	if ( isset( $gallery_group_updates[ $name ] ) ) {
		$photo['group']          = $gallery_group_updates[ $name ];
		$target_photos[ $name ] = $photo;
	}
}
if ( count( $target_photos ) !== count( $gallery_group_updates ) ) {
	echo "One or more requested gallery photos are missing from seed-data.json.\n";
	return;
}
$removed_photos = array(
	'/media/ration/ration-01.jpg',
	'/media/ration/ration-15.jpg',
	'/media/field/hero-humanitarian-relief.jpg',
	'/media/ration/pillar-a-qurbani-01.jpg',
	'/media/ration/pillar-a-qurbani-02.jpg',
	'/media/ration/pillar-a-qurbani-03.jpg',
	'/media/ration/pillar-a-qurbani-04.jpg',
	'/media/ration/pillar-a-qurbani-05.jpg',
	'/media/ration/pillar-a-qurbani-06.jpg',
	'/media/ration/pillar-a-qurbani-07.jpg',
	'/media/ration/pillar-a-qurbani-08.jpg',
	'/media/ration/pillar-a-qurbani-09.jpg',
	'/media/ration/pillar-a-qurbani-10.jpg',
);
$gallery = mmm_gallery();
$before_removal = count( $gallery );
$gallery        = array_values(
	array_filter(
		$gallery,
		static function ( $photo ) use ( $removed_photos ) {
			return ! in_array( $photo['src'] ?? '', $removed_photos, true );
		}
	)
);
$removed = $before_removal - count( $gallery );
$indexes = array();
foreach ( $gallery as $index => $photo ) {
	if ( isset( $photo['src'] ) ) {
		$indexes[ $photo['src'] ] = $index;
	}
}

$added = 0;
foreach ( $academy_photos as $photo ) {
	$src = $photo['src'] ?? '';
	if ( '' === $src ) {
		continue;
	}
	if ( isset( $indexes[ $src ] ) ) {
		$gallery[ $indexes[ $src ] ]['group'] = $academy_key;
	} else {
		$gallery[]       = $photo;
		$indexes[ $src ] = count( $gallery ) - 1;
		$added++;
	}
}

$moved = 0;
foreach ( $gallery as &$photo ) {
	$name = pathinfo( $photo['src'] ?? '', PATHINFO_FILENAME );
	if ( isset( $target_photos[ $name ] ) ) {
		$photo['group'] = $target_photos[ $name ]['group'];
		$moved++;
	}
}
unset( $photo );

$groups = mmm_gallery_groups();
$found  = false;
foreach ( $groups as &$group ) {
	if ( $academy_key === ( $group['key'] ?? '' ) ) {
		$group = $academy_group;
		$found = true;
		break;
	}
}
unset( $group );
if ( ! $found ) {
	$groups[] = $academy_group;
}

update_option( 'mmm_gallery', $gallery );
update_option( 'mmm_gallery_groups', $groups );

printf(
	"Updated gallery: removed %d duplicate photos; reassigned %d photos; academy/conference collection has %d photos (%d added); %d tabs total.\n",
	$removed,
	$moved,
	count( $academy_photos ),
	$added,
	count( $groups )
);
