<?php
/**
 * The Disaster response page — route /disaster-response/.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The original <head> preloaded the hero photograph for this route.
mmm_hero_preload( '/media/field/hero-disaster-response.jpg' );

get_header();

$mmm_impact  = mmm_impact_stats();
$mmm_patients = $mmm_impact[0] ?? array();

$mmm_phases = array(
	array(
		'n'      => '01',
		'kicker' => 'First 24 hours',
		'title'  => 'Assessment, not announcement',
		'body'   => 'Two people go in before anything is loaded. They confirm which roads hold, where displaced families have actually gathered and what the district health office still has. Nothing is procured until that report comes back.',
	),
	array(
		'n'      => '02',
		'kicker' => 'Day 2 to day 7',
		'title'  => 'Clinic and clean water',
		'body'   => 'A medical team deploys with a standing drug list built for what floods produce, gastroenteritis, skin infection, snakebite, respiratory illness and untreated chronic disease. Clean water goes in alongside it, because the outbreak follows the water, not the injury.',
	),
	array(
		'n'      => '03',
		'kicker' => 'Week 2 onward',
		'title'  => 'Food that lasts a month',
		'body'   => 'Ration distribution is run through local volunteers who know which households have lost everything. Bulk purchase before the local market price climbs is the difference between feeding one family and three.',
	),
	array(
		'n'      => '04',
		'kicker' => 'The months after',
		'title'  => 'The part nobody photographs',
		'body'   => 'Camps continue after the water recedes, when the coverage has moved on and the illness has not. This is the phase most appeals never fund, and the phase that decides whether people recover.',
	),
);

$mmm_founded   = (int) mmm_org_value( 'founded' );
$mmm_pat_val   = (int) ( $mmm_patients['value'] ?? 0 );
$mmm_pat_sfx   = (string) ( $mmm_patients['suffix'] ?? '' );
$mmm_records   = array_slice( mmm_reports(), 0, 2 );
?>

<header class="relative isolate overflow-hidden border-b border-slate-200/80 bg-gradient-to-b from-[#F0F6FC] via-[#F8FAFC] to-white pt-32 pb-16 md:pt-36 md:pb-20">
<div class="absolute inset-0 -z-10"><img src="/media/field/hero-disaster-response.jpg" alt="<?php echo esc_attr( mmm_copy( 'disaster-response', 'hero_title', 'We move in the first week, and we were still there in the third month' ) ); ?>" class="h-full w-full object-cover opacity-20"/></div>
<div class="shell">
<nav aria-label="Breadcrumb">
<ol class="flex flex-wrap items-center gap-2 text-[0.88rem] font-semibold text-[#6B7280]">
<li class="flex items-center gap-2"><a class="transition-colors hover:text-[#075BD6]" href="/">Home</a></li>
<li class="flex items-center gap-2"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 text-[#1F2937]" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg><a class="transition-colors hover:text-[#075BD6]" href="/disaster-response/"><?php echo esc_html( mmm_copy( 'disaster-response', 'breadcrumb_label', 'Disaster response' ) ); ?></a></li>
</ol>
</nav>
<h1 class="mt-5 max-w-3xl font-display text-[clamp(2.15rem,3.8vw,3.15rem)] leading-[1.08] font-black text-[#0A1020] tracking-tight"><?php echo esc_html( mmm_copy( 'disaster-response', 'hero_title', 'We move in the first week, and we were still there in the third month' ) ); ?></h1>
<p class="mt-4 max-w-2xl text-[1.05rem] leading-relaxed text-[#4B5563] sm:text-[1.14rem]"><?php echo esc_html( mmm_copy( 'disaster-response', 'hero_lead', 'Flood response in Pakistan is not one operation. It is four, run in sequence, and most organisations only fund the first two.' ) ); ?></p>
</div>
</header>

<section class="relative z-10 -mt-14">
<div class="shell-wide">
<dl class="grid overflow-hidden rounded-2xl bg-white shadow-[0_28px_60px_-28px_rgba(10,23,48,0.45)] sm:grid-cols-3 sm:divide-x sm:divide-line">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  p-7">
<dt class="sr-only"><?php echo esc_html( mmm_copy( 'disaster-response', 'stat_founded_label', 'Founded in' ) ); ?></dt>
<dd>
<span class="block font-display text-[1.3rem] leading-none font-black tracking-tight text-[#0A1020] sm:text-[1.85rem]">
<span class=""<?php echo mmm_count_attrs( $mmm_founded, '', 0 ); ?>>0</span>
</span>
<span class="mt-1 block font-display text-[0.78rem] font-bold text-[#0A1020] leading-tight sm:text-[0.88rem]"><?php echo esc_html( mmm_copy( 'disaster-response', 'stat_founded_label', 'Founded in' ) ); ?></span>
<span class="mt-1 block text-[0.72rem] text-[#6B7280]"><?php echo esc_html( mmm_org_value( 'name' ) ); ?></span>
</dd>
</div>
<div data-visible="false" style="--reveal-delay:80ms" class="reveal  p-7">
<dt class="sr-only"><?php echo esc_html( mmm_copy( 'disaster-response', 'stat_patients_label', 'Patients seen' ) ); ?></dt>
<dd>
<span class="block font-display text-[1.3rem] leading-none font-black tracking-tight text-[#0A1020] sm:text-[1.85rem]">
<span class=""<?php echo mmm_count_attrs( $mmm_pat_val, $mmm_pat_sfx, 0 ); ?>>0<?php if ( '' !== $mmm_pat_sfx ) : ?><!-- --><?php echo esc_html( $mmm_pat_sfx ); ?><?php endif; ?></span>
</span>
<span class="mt-1 block font-display text-[0.78rem] font-bold text-[#0A1020] leading-tight sm:text-[0.88rem]"><?php echo esc_html( mmm_copy( 'disaster-response', 'stat_patients_label', 'Patients seen' ) ); ?></span>
<span class="mt-1 block text-[0.72rem] text-[#6B7280]"><?php echo esc_html( $mmm_patients['detail'] ?? '' ); ?></span>
</dd>
</div>
<div data-visible="false" style="--reveal-delay:160ms" class="reveal  p-7">
<dt class="sr-only"><?php echo esc_html( mmm_copy( 'disaster-response', 'stat_coverage_label', 'Coverage' ) ); ?></dt>
<dd>
<span class="block font-display text-[1.3rem] leading-none font-black tracking-tight text-[#0A1020] sm:text-[1.85rem]"><?php echo esc_html( mmm_copy( 'disaster-response', 'stat_coverage_value', 'All Over Pakistan' ) ); ?></span>
<span class="mt-1 block font-display text-[0.78rem] font-bold text-[#0A1020] leading-tight sm:text-[0.88rem]"><?php echo esc_html( mmm_copy( 'disaster-response', 'stat_coverage_label', 'Coverage' ) ); ?></span>
<span class="mt-1 block text-[0.72rem] text-[#6B7280]"><?php echo esc_html( mmm_copy( 'disaster-response', 'stat_coverage_detail', 'All over Pakistan' ) ); ?></span>
</dd>
</div>
</dl>
</div>
</section>

<section class="py-24 lg:py-28">
<div class="shell-wide">
<div class="">
<div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7"><h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo esc_html( mmm_copy( 'disaster-response', 'deployment_heading', 'How a deployment actually runs' ) ); ?></h2></div>
<div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5"><p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo esc_html( mmm_copy( 'disaster-response', 'deployment_lead', 'Written down because improvising each time costs days, and in a flood response days are measured in cases of cholera.' ) ); ?></p></div>
</div>
</div>
<ol class="mt-14 grid gap-6 lg:grid-cols-2">
<?php foreach ( $mmm_phases as $mmm_i => $mmm_phase ) : ?>
<li data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 80 ); ?>ms" class="reveal  ">
<div class="relative h-full overflow-hidden rounded-2xl bg-surface p-8">
<span class="absolute -top-4 right-5 font-display text-[5.5rem] leading-none font-extrabold text-navy-100/70 select-none" aria-hidden="true"><?php echo esc_html( $mmm_phase['n'] ); ?></span>
<p class="relative font-display text-[1.05rem] font-bold text-[#EF3B19]"><?php echo esc_html( mmm_copy( 'disaster-response', 'phase_' . ( $mmm_i + 1 ) . '_kicker', $mmm_phase['kicker'] ) ); ?></p>
<h3 class="relative mt-2.5 font-display text-[1.4rem] sm:text-[1.5rem] font-black text-[#0A1020]"><?php echo esc_html( mmm_copy( 'disaster-response', 'phase_' . ( $mmm_i + 1 ) . '_title', $mmm_phase['title'] ) ); ?></h3>
<p class="relative mt-3 text-[1.02rem] leading-relaxed text-[#4B5563]"><?php echo esc_html( mmm_copy( 'disaster-response', 'phase_' . ( $mmm_i + 1 ) . '_body', $mmm_phase['body'] ) ); ?></p>
</div>
</li>
<?php endforeach; ?>
</ol>
</div>
</section>

<section class="border-y border-slate-200/80 bg-gradient-to-b from-[#F0F7FF] via-[#F8FAFC] to-white py-24 lg:py-28">
<div class="shell-wide">
<div class="">
<div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7"><h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo esc_html( mmm_copy( 'disaster-response', 'record_heading', 'The record from the last flood' ) ); ?></h2></div>
<div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5"><p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo esc_html( mmm_copy( 'disaster-response', 'record_lead', 'Places, not percentages. These are the districts our teams worked in and what they did there.' ) ); ?></p></div>
</div>
</div>
<div class="mt-14 grid gap-6 md:grid-cols-2">
<?php foreach ( $mmm_records as $mmm_i => $mmm_record ) : ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 80 ); ?>ms" class="reveal  ">
<article class="group h-full overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-soft transition-all duration-400 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lift">
<div class="aspect-[16/9] overflow-hidden">
<img src="<?php echo esc_url( $mmm_record['image'] ); ?>" alt="" aria-hidden="true" loading="lazy" class="h-full w-full object-cover transition-transform duration-[900ms] group-hover:scale-105"/>
</div>
<div class="p-7">
<h3 class="font-display text-[1.2rem] leading-snug font-extrabold text-[#003475] transition-colors group-hover:text-[#046BD2]"><?php echo esc_html( $mmm_record['title'] ); ?></h3>
<p class="mt-3 text-[0.93rem] leading-relaxed text-[#334155]"><?php echo esc_html( $mmm_record['excerpt'] ); ?></p>
<p class="mt-4 text-[0.9rem] font-semibold text-[#046BD2]"><?php echo esc_html( $mmm_record['place'] ); ?></p>
</div>
</article>
</div>
<?php endforeach; ?>
</div>
<div data-visible="false" style="--reveal-delay:100ms" class="reveal  ">
<div class="mt-12 flex flex-wrap gap-3 sm:gap-4">
<a class="group inline-flex items-center justify-center gap-1 rounded-full px-1.5 py-2.5 xs:px-2 xs:py-3 sm:px-7 sm:py-3.5 min-h-[38px] max-w-full font-display text-[0.5625rem] xs:text-[0.6rem] sm:text-[0.95rem] font-bold tracking-tight transition-all duration-300 hover:-translate-y-0.5 active:scale-98 bg-[#046BD2] text-white hover:bg-[#e53935] hover:border-[#e53935] shadow-[0_10px_24px_-8px_rgba(4,107,210,0.4)] hover:shadow-[0_10px_24px_-8px_rgba(229,57,53,0.4)] flex-1 sm:flex-none" href="/donate/">
<span class="whitespace-nowrap"><?php echo esc_html( mmm_copy( 'disaster-response', 'cta_primary', 'Fund the next deployment' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</a>
<a class="group inline-flex items-center justify-center gap-1 rounded-full px-1.5 py-2.5 xs:px-2 xs:py-3 sm:px-7 sm:py-3.5 min-h-[38px] max-w-full font-display text-[0.5625rem] xs:text-[0.6rem] sm:text-[0.95rem] font-bold tracking-tight transition-all duration-300 hover:-translate-y-0.5 active:scale-98 border-2 border-slate-300 text-[#334155] hover:border-[#e53935] hover:bg-[#e53935] hover:text-white flex-1 sm:flex-none" href="/get-involved/">
<span class="whitespace-nowrap"><?php echo esc_html( mmm_copy( 'disaster-response', 'cta_secondary', 'Join a response team' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</a>
</div>
</div>
</div>
</section>
<!--$-->
<!--/$-->
<?php
get_footer();
