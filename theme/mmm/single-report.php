<?php
/**
 * A single field report — /media/{slug}/.
 *
 * One template renders every report; everything that varies comes from the
 * report CPT (mmm_reports()).
 *
 * Ground truth:
 *   E:\MMM02\out\media\bls-rescue-1122\index.html
 *   E:\MMM02\out\media\gaza-food-parcels-and-hot-meals\index.html
 *   E:\MMM02\out\media\one-million-patients\index.html
 *   E:\MMM02\out\media\surgical-camp-central-jail\index.html
 *   E:\MMM02\out\media\taunsa-flood-response\index.html
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

$slug   = (string) get_query_var( 'post_name' );
$report = $slug ? mmm_report_by_slug( $slug ) : null;

if ( ! $report ) {
	$queried = get_queried_object();
	if ( $queried instanceof WP_Post ) {
		$report = mmm_report_by_slug( $queried->post_name );
	}
}

if ( ! $report ) {
	get_header();
	echo '<div class="shell-wide py-32"><h1 class="font-display text-[2rem] font-black text-[#0A1020]">' . esc_html__( 'Report not found', 'mmm' ) . '</h1></div>';
	get_footer();
	return;
}

// The original <head> preloads the logo (header.php) plus this report's image.
mmm_hero_preload( $report['image'] );

// Related: every other report, in menu order, the first three.
$related = array();
foreach ( mmm_reports() as $other ) {
	if ( $other['slug'] === $report['slug'] ) {
		continue;
	}
	$related[] = $other;
	if ( count( $related ) >= 3 ) {
		break;
	}
}

// The third meta cell is labelled "Period" for an open-ended date, "Year" otherwise.
$date_label = ( 'Ongoing' === $report['date'] )
	? mmm_copy( 'report', 'meta_period_label', 'Period' )
	: mmm_copy( 'report', 'meta_year_label', 'Year' );

get_header();
?>

<header class="relative isolate overflow-hidden border-b border-slate-200/80 bg-gradient-to-b from-[#F0F6FC] via-[#F8FAFC] to-white pt-32 pb-16 md:pt-36 md:pb-20">
<div class="absolute inset-0 -z-10">
<img src="<?php echo mmm_attr( $report['image'] ); ?>" alt="<?php echo mmm_attr( $report['title'] ); ?>" class="h-full w-full object-cover opacity-20"/>
</div>
<div class="shell">
<nav aria-label="Breadcrumb">
<ol class="flex flex-wrap items-center gap-2 text-[0.88rem] font-semibold text-[#6B7280]">
<li class="flex items-center gap-2"><a class="transition-colors hover:text-[#075BD6]" href="/"><?php echo mmm_text( mmm_copy( 'report', 'crumb_home', 'Home' ) ); ?></a></li>
<li class="flex items-center gap-2"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 text-[#1F2937]" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg><a class="transition-colors hover:text-[#075BD6]" href="/media/"><?php echo mmm_text( mmm_copy( 'report', 'crumb_media', 'Media' ) ); ?></a></li>
<li class="flex items-center gap-2"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 text-[#1F2937]" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg><a class="transition-colors hover:text-[#075BD6]" href="/media/<?php echo mmm_attr( $report['slug'] ); ?>/"><?php echo mmm_text( $report['title'] ); ?></a></li>
</ol>
</nav>
<h1 class="mt-5 max-w-3xl font-display text-[clamp(2.15rem,3.8vw,3.15rem)] leading-[1.08] font-black text-[#0A1020] tracking-tight"><?php echo mmm_text( $report['title'] ); ?></h1>
<p class="mt-4 max-w-2xl text-[1.05rem] leading-relaxed text-[#4B5563] sm:text-[1.14rem]"><?php echo mmm_text( $report['excerpt'] ); ?></p>
</div>
</header>

<section class="relative z-10 -mt-14">
<div class="shell-wide">
<dl class="grid overflow-hidden rounded-2xl bg-white shadow-[0_28px_60px_-28px_rgba(10,23,48,0.45)] sm:grid-cols-3 sm:divide-x sm:divide-line">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  p-7">
<dt class="text-[0.88rem] text-slate-muted"><?php echo mmm_text( mmm_copy( 'report', 'meta_report_type_label', 'Report type' ) ); ?></dt>
<dd class="mt-2.5 font-display text-[1.15rem] font-extrabold text-navy-800"><?php echo mmm_text( $report['kind'] ); ?></dd>
</div>
<div data-visible="false" style="--reveal-delay:80ms" class="reveal  p-7">
<dt class="text-[0.88rem] text-slate-muted"><?php echo mmm_text( mmm_copy( 'report', 'meta_location_label', 'Location' ) ); ?></dt>
<dd class="mt-2.5 font-display text-[1.15rem] font-extrabold text-navy-800"><?php echo mmm_text( $report['place'] ); ?></dd>
</div>
<div data-visible="false" style="--reveal-delay:160ms" class="reveal  p-7">
<dt class="text-[0.88rem] text-slate-muted"><?php echo mmm_text( $date_label ); ?></dt>
<dd class="mt-2.5 font-display text-[1.15rem] font-extrabold text-navy-800"><?php echo mmm_text( $report['date'] ); ?></dd>
</div>
</dl>
</div>
</section>

<section class="py-20 lg:py-28">
<div class="shell-wide grid gap-14 lg:grid-cols-12">
<div class="lg:col-span-8">
<div class="rich mt-8 text-[1.06rem]">
<?php foreach ( array_values( (array) $report['body'] ) as $i => $paragraph ) : ?>
<p data-visible="false" style="--reveal-delay:<?php echo (int) ( $i * 60 ); ?>ms" class="reveal  mb-5"><?php echo mmm_text( $paragraph ); ?></p>
<?php endforeach; ?>
</div>
<div data-visible="false" style="--reveal-delay:120ms" class="reveal  ">
<div class="mt-10 flex flex-wrap gap-2 sm:gap-4">
<a class="group inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 xs:gap-2 rounded-full bg-[#046BD2] px-2.5 py-2.5 xs:px-3 xs:py-3 sm:px-7 sm:py-3.5 font-display text-[0.72rem] xs:text-[0.76rem] sm:text-[0.95rem] font-bold text-white min-h-[38px] shadow-sm transition-all duration-250 hover:-translate-y-0.5 hover:bg-[#e53935]" href="/donate/">
<span class="whitespace-nowrap"><?php echo mmm_text( mmm_copy( 'report', 'cta_support', 'Support this work' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</a>
<a class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 xs:gap-2 rounded-full border-2 border-slate-300 px-2.5 py-2.5 xs:px-3 xs:py-3 sm:px-7 sm:py-3.5 font-display text-[0.72rem] xs:text-[0.76rem] sm:text-[0.95rem] font-bold text-[#003475] min-h-[38px] transition-all duration-250 hover:border-[#e53935] hover:bg-[#e53935] hover:text-white" href="/media/">
<span class="whitespace-nowrap"><?php echo mmm_text( mmm_copy( 'report', 'cta_all_reports', 'All field reports' ) ); ?></span>
</a>
</div>
</div>
</div>
<div class="lg:col-span-4 lg:col-start-9">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal reveal-right ">
<div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-soft">
<img src="<?php echo mmm_attr( $report['image'] ); ?>" alt="<?php echo mmm_attr( $report['title'] ); ?>" loading="lazy" class="aspect-[4/3] w-full object-cover"/>
<div class="p-6">
<p class="font-display text-[0.95rem] font-bold text-[#003475]"><?php echo mmm_text( $report['kind'] ); ?></p>
<p class="mt-1.5 text-[0.9rem] text-[#4B5563]"><?php echo mmm_text( $report['place'] ); ?></p>
</div>
</div>
</div>
</div>
</div>
</section>

<section class="bg-surface py-20 lg:py-24">
<div class="shell-wide">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  ">
<div class="flex flex-wrap items-center gap-3 sm:gap-5">
<h2 class="font-display text-[1.5rem] font-extrabold text-[#003475]"><?php echo mmm_text( mmm_copy( 'report', 'related_heading', 'More field reports' ) ); ?></h2>
<span class="h-px flex-1 bg-slate-200" aria-hidden="true"></span>
<a class="group inline-flex items-center gap-1.5 xs:gap-2 font-display text-[0.85rem] xs:text-[0.9rem] font-bold text-[#046BD2] shrink-0 min-h-[36px]" href="/media/">
<span class="link-underline"><?php echo mmm_text( mmm_copy( 'report', 'related_see_all', 'See all' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</a>
</div>
</div>
<div class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
<?php foreach ( $related as $i => $other ) : ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $i * 70 ); ?>ms" class="reveal  ">
<a class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-[#DCE2EA] transition-all duration-500 hover:-translate-y-1.5 hover:shadow-lift hover:ring-[#075BD6]/50" href="/media/<?php echo mmm_attr( $other['slug'] ); ?>/">
<div class="aspect-[16/9] overflow-hidden">
<img src="<?php echo mmm_attr( $other['image'] ); ?>" alt="" aria-hidden="true" loading="lazy" class="h-full w-full object-cover transition-transform duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105"/>
</div>
<div class="flex flex-1 flex-col p-6">
<h3 class="font-display text-[1.15rem] leading-tight font-black text-[#0A1020] transition-colors group-hover:text-[#075BD6]"><?php echo mmm_text( $other['title'] ); ?></h3>
<p class="mt-3 flex-1 text-[0.95rem] leading-relaxed text-[#4B5563]"><?php echo mmm_text( $other['excerpt'] ); ?></p>
<span class="mt-4 inline-flex items-center gap-1.5 font-display text-[0.85rem] font-bold text-[#075BD6] group-hover:text-[#0649B8]">
<span><?php echo mmm_text( mmm_copy( 'report', 'related_read_cta', 'Read the report' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3.5 w-3.5 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</span>
</div>
</a>
</div>
<?php endforeach; ?>
</div>
</div>
</section>
<?php // React's component boundary markers, reproduced verbatim from the build. ?>
<!--$-->
<!--/$-->
<?php
get_footer();
