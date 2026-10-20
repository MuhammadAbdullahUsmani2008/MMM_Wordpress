<?php
/**
 * The Media page — /media/.
 *
 * Field reports, the filterable photo library and the video wall.
 *
 * Ground truth: E:\MMM02\out\media\index.html
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'mmm_text' ) ) {
	/**
	 * Text-node escaping, using the apostrophe entity the original build
	 * emitted (&#x27;) so the rendered markup matches byte for byte.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	function mmm_text( $text ) {
		return str_replace( '&#039;', '&#x27;', esc_html( $text ) );
	}
}

if ( ! function_exists( 'mmm_attr' ) ) {
	/**
	 * Attribute escaping, same apostrophe convention as mmm_text().
	 *
	 * @param string $text Attribute value.
	 * @return string
	 */
	function mmm_attr( $text ) {
		return str_replace( '&#039;', '&#x27;', esc_attr( $text ) );
	}
}

// The original <head> preloads the logo (header.php) plus this hero image.
mmm_hero_preload( '/media/field/national-conference-stage.jpg' );

$reports = mmm_reports();
$photos  = mmm_gallery();

/*
 * Filter tabs. mmm_gallery_groups() holds { key, label, region } per tab in
 * display order; the labels live in the option, so mmm_gallery_group_label()
 * is their single source. The "Everything" tab is fixed UI, not data.
 */
$groups = mmm_gallery_groups();

$total_photos = count( $photos );
$shown_photos  = min( 24, $total_photos );

/*
 * Video wall. The embed list has no WordPress getter (see the porting
 * notes); the titles are editable copy, the embed URLs are locked markup.
 */
$videos = array(
	'https://www.youtube-nocookie.com/embed/yIm-jdfFi7s?si=ko76G6ZmyTUkhPvx' => 'Ramadan Package Distribution, Gaza',
	'https://www.youtube-nocookie.com/embed/Rjw6EIPoLz0?si=khUi6EduOJIXoI4H' => 'Providing Hot meal in Gaza',
	'https://www.youtube-nocookie.com/embed/xlan-cUhZvc?si=nqLtxqarzMZpEnHO'  => 'Providing Clean Water in Gaza',
	'https://www.youtube-nocookie.com/embed/hMtFcTJgjN8?si=1RLPlrVa1dpvHiHR' => 'Fresh Bread Distribution in Gaza',
	'https://www.youtube-nocookie.com/embed/jxjikidC7zQ?si=6G6ct8OO4i0UyEX8' => 'Gaza Relief Campaign',
	'https://www.youtube-nocookie.com/embed/OWHADJImiMU?si=GSwMugt7MP891wxo' => "Hot Bread Continues to Reach Gaza's Tents",
);

get_header();
?>

<header class="relative isolate overflow-hidden border-b border-slate-200/80 bg-gradient-to-b from-[#F0F6FC] via-[#F8FAFC] to-white pt-32 pb-16 md:pt-36 md:pb-20">
<div class="absolute inset-0 -z-10">
<img src="/media/field/national-conference-stage.jpg" alt="<?php echo mmm_attr( mmm_copy( 'media', 'hero_title', 'What the work looks like when nobody is posing for it' ) ); ?>" class="h-full w-full object-cover opacity-20"/>
</div>
<div class="shell">
<nav aria-label="Breadcrumb">
<ol class="flex flex-wrap items-center gap-2 text-[0.88rem] font-semibold text-[#6B7280]">
<li class="flex items-center gap-2"><a class="transition-colors hover:text-[#075BD6]" href="/"><?php echo mmm_text( mmm_copy( 'media', 'crumb_home', 'Home' ) ); ?></a></li>
<li class="flex items-center gap-2"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 text-[#1F2937]" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg><a class="transition-colors hover:text-[#075BD6]" href="/media/"><?php echo mmm_text( mmm_copy( 'media', 'crumb_media', 'Media' ) ); ?></a></li>
</ol>
</nav>
<h1 class="mt-5 max-w-3xl font-display text-[clamp(2.15rem,3.8vw,3.15rem)] leading-[1.08] font-black text-[#0A1020] tracking-tight"><?php echo mmm_text( mmm_copy( 'media', 'hero_title', 'What the work looks like when nobody is posing for it' ) ); ?></h1>
<p class="mt-4 max-w-2xl text-[1.05rem] leading-relaxed text-[#4B5563] sm:text-[1.14rem]"><?php echo mmm_text( mmm_copy( 'media', 'hero_lead', 'Photographs taken by our own teams on deployment, and reports written by the people who were standing in them.' ) ); ?></p>
</div>
</header>

<section class="py-24 lg:py-28">
<div class="shell-wide">
<div class="">
<div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7">
<h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo mmm_text( mmm_copy( 'media', 'reports_heading', 'Field reports' ) ); ?></h2>
</div>
<div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5">
<p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo mmm_text( mmm_copy( 'media', 'reports_intro', 'Each one records a place, a date and a count of what was delivered.' ) ); ?></p>
</div>
</div>
</div>
<div class="mt-14 grid gap-6 md:grid-cols-2">
<?php foreach ( $reports as $i => $report ) : ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $i * 80 ); ?>ms" class="reveal  ">
<a class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-[#DCE2EA] transition-all duration-500 hover:-translate-y-1.5 hover:shadow-lift hover:ring-[#075BD6]/50" href="/media/<?php echo mmm_attr( $report['slug'] ); ?>/">
<div class="aspect-[16/9] overflow-hidden">
<img src="<?php echo mmm_attr( $report['image'] ); ?>" alt="" aria-hidden="true" loading="lazy" class="h-full w-full object-cover transition-transform duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105"/>
</div>
<div class="flex flex-1 flex-col p-7 sm:p-8">
<h3 class="font-display text-[1.35rem] sm:text-[1.48rem] leading-tight font-black text-[#0A1020] transition-colors group-hover:text-[#075BD6]"><?php echo mmm_text( $report['title'] ); ?></h3>
<p class="mt-3 flex-1 text-[1rem] leading-relaxed text-[#4B5563]"><?php echo mmm_text( $report['excerpt'] ); ?></p>
<p class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-[0.88rem] text-[#6B7280]">
<span class="font-bold text-[#EF3B19]"><?php echo mmm_text( $report['kind'] ); ?></span>
<span class="h-3 w-px bg-[#DCE2EA]" aria-hidden="true"></span>
<span><?php echo mmm_text( $report['place'] ); ?></span>
</p>
<span class="mt-5 inline-flex items-center gap-1.5 font-display text-[0.9rem] font-bold text-[#075BD6] group-hover:text-[#0649B8]">
<span><?php echo mmm_text( mmm_copy( 'media', 'read_report_cta', 'Read the complete report' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3.5 w-3.5 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</span>
</div>
</a>
</div>
<?php endforeach; ?>
</div>
</div>
</section>

<section id="gallery" class="bg-surface py-24 lg:py-28">
<div class="shell-wide">
<div class="">
<div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7">
<h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo mmm_text( mmm_copy( 'media', 'gallery_heading', 'Photo library' ) ); ?></h2>
</div>
<div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5">
<p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo mmm_text( mmm_copy( 'media', 'gallery_intro', 'Over a hundred photographs from camps, distributions and emergency deployments. Select one to view it full size.' ) ); ?></p>
</div>
</div>
</div>
<div class="mt-12">
<div data-gallery>
<div class="relative">
<button type="button" aria-label="Scroll categories left" data-gallery-arrow-left class="absolute left-0 top-1/2 z-10 hidden -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-[#334155] shadow-md transition-all duration-300 hover:bg-blue-50 hover:text-[#046BD2] md:flex h-9 w-9 shrink-0 min-h-[36px] min-w-[36px] pointer-events-none opacity-0">
<svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="none" aria-hidden="true">
<path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
</path>
</svg>
</button>
<div data-gallery-tabs class="no-scrollbar -mx-5 flex gap-2 overflow-x-auto px-5 pb-1 md:mx-0 md:px-10">
<button type="button" aria-pressed="true" data-gallery-filter="all" class="inline-flex shrink-0 items-center justify-center rounded-full px-4 py-2 xs:px-5 xs:py-2.5 font-display text-[0.82rem] xs:text-[0.88rem] font-bold whitespace-nowrap min-h-[40px] transition-all duration-300 bg-[#046BD2] text-white shadow-sm"><?php echo mmm_text( mmm_copy( 'media', 'gallery_everything', 'Everything' ) ); ?></button>
<?php foreach ( $groups as $group ) :
	$g     = isset( $group['key'] ) ? (string) $group['key'] : '';
	$label = mmm_gallery_group_label( $g );
	?>
<button type="button" aria-pressed="false" data-gallery-filter="<?php echo mmm_attr( $g ); ?>" class="inline-flex shrink-0 items-center justify-center rounded-full px-4 py-2 xs:px-5 xs:py-2.5 font-display text-[0.82rem] xs:text-[0.88rem] font-bold whitespace-nowrap min-h-[40px] transition-all duration-300 bg-slate-100 text-[#334155] hover:bg-blue-50 hover:text-[#046BD2]"><?php echo mmm_text( $label ); ?></button>
<?php endforeach; ?>
</div>
<button type="button" aria-label="Scroll categories right" data-gallery-arrow-right class="absolute right-0 top-1/2 z-10 hidden -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-[#334155] shadow-md transition-all duration-300 hover:bg-blue-50 hover:text-[#046BD2] md:flex h-9 w-9 shrink-0 min-h-[36px] min-w-[36px] pointer-events-none opacity-0">
<svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="none" aria-hidden="true">
<path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
</path>
</svg>
</button>
</div>
<div data-gallery-grid class="mt-9 columns-1 gap-4 xs:columns-2 md:columns-3 lg:columns-4 [&amp;&gt;*]:mb-4">
<?php foreach ( $photos as $i => $photo ) : ?>
<?php
$src    = isset( $photo['src'] ) ? (string) $photo['src'] : '';
$group  = isset( $photo['group'] ) ? (string) $photo['group'] : '';
$label  = mmm_gallery_group_label( $group );
?>
<button type="button" class="group relative block w-full overflow-hidden rounded-xl break-inside-avoid" aria-label="Open photograph, <?php echo mmm_attr( $label ); ?>" data-gallery-item data-group="<?php echo mmm_attr( $group ); ?>"<?php echo $i >= $shown_photos ? ' hidden' : ''; ?>>
<img src="<?php echo mmm_attr( $src ); ?>" alt="" loading="lazy" class="w-full transition-transform duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]"/>
<span class="absolute inset-0 bg-[#003475]/0 transition-colors duration-400 group-hover:bg-[#003475]/40" aria-hidden="true"></span>
<span class="absolute bottom-3 left-3 translate-y-2 font-display text-[0.78rem] font-bold text-white opacity-0 transition-all duration-400 group-hover:translate-y-0 group-hover:opacity-100" aria-hidden="true"><?php echo mmm_text( $label ); ?></span>
</button>
<?php endforeach; ?>
</div>
<?php if ( $total_photos > $shown_photos ) : ?>
<div class="mt-10 text-center">
<button type="button" data-gallery-more class="inline-flex items-center justify-center rounded-full border-2 border-slate-300 px-6 py-2.5 xs:px-8 xs:py-3.5 font-display text-[0.88rem] xs:text-base font-bold text-[#334155] whitespace-nowrap min-h-[44px] max-w-full transition-colors hover:border-[#046BD2] hover:text-[#046BD2] hover:bg-blue-50/50">
<span><?php echo mmm_text( mmm_copy( 'media', 'gallery_more', 'Show more photographs' ) ); ?></span>
<span data-gallery-count class="ml-2 text-slate-400">(<!-- --><?php echo (int) $shown_photos; ?><!-- --> of <!-- --><?php echo (int) $total_photos; ?><!-- -->)</span>
</button>
</div>
<?php endif; ?>
</div>
</div>
</div>
</section>

<section id="video" class="py-24 lg:py-28">
<div class="shell-wide">
<div class="">
<div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7">
<h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo mmm_text( mmm_copy( 'media', 'video_heading', 'Video' ) ); ?></h2>
</div>
<div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5">
<p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo mmm_text( mmm_copy( 'media', 'video_intro', 'Footage from deployments, published on our YouTube channel.' ) ); ?></p>
<a class="group mt-4 inline-flex items-center gap-1.5 xs:gap-2 font-display text-[0.88rem] xs:text-[0.96rem] font-bold shrink-0 min-h-[38px] text-[#075BD6]" href="<?php echo esc_url( mmm_social_url( 'YouTube' ) ); ?>">
<span class="link-underline"><?php echo mmm_text( mmm_copy( 'media', 'video_channel', 'Open the channel' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</a>
</div>
</div>
</div>
<div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
<?php $video_index = 0; ?>
<?php foreach ( $videos as $video_src => $video_title ) : ?>
<?php $video_title = mmm_copy( 'media', 'video_' . ( $video_index + 1 ) . '_title', $video_title ); ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $video_index * 70 ); ?>ms" class="reveal  ">
<div class="group block overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lift">
<div class="relative aspect-video overflow-hidden">
<iframe src="<?php echo esc_url( $video_src ); ?>" title="<?php echo mmm_attr( $video_title ); ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerPolicy="strict-origin-when-cross-origin" allowFullScreen="" class="absolute inset-0 h-full w-full">
</iframe>
</div>
<p class="p-5 font-display text-[0.98rem] font-bold text-[#003475]"><?php echo mmm_text( $video_title ); ?></p>
</div>
</div>
<?php $video_index++; ?>
<?php endforeach; ?>
</div>
</div>
</section>
<?php // React's component boundary markers, reproduced verbatim from the build. ?>
<!--$-->
<!--/$-->
<?php
get_footer();
