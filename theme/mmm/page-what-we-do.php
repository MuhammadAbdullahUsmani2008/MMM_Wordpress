<?php
/**
 * The What we do index — route /what-we-do/.
 *
 * All eleven programmes, grouped by region (Pakistan, Gaza, across both),
 * rendered as ProgramCard clones. Everything that varies comes from
 * mmm_programs().
 *
 * Ground truth:
 *   E:\MMM02\out\what-we-do\index.html
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

if ( ! function_exists( 'mmm_program_category' ) ) {
	/**
	 * The small badge label shown on each programme card.
	 *
	 * The original build kept this slug => label map inside ProgramCard.tsx
	 * (with "Healthcare Relief" as the fallback for an unknown slug); it was
	 * never part of the programme data, so it is registered here as editable
	 * page copy instead of living in the database.
	 *
	 * @param string $slug Programme slug.
	 * @return string
	 */
	function mmm_program_category( $slug ) {
		$map = array(
			'flood-medical-camps'      => 'Emergency Relief',
			'flood-relief'             => 'Disaster Response',
			'gaza-field-clinics'       => 'Trauma & Lifeline',
			'water-for-life'           => 'Clean Water',
			'food-parcels-gaza'        => 'Nutrition & Food',
			'winter-packages'          => 'Winter Survival',
			'diagnostic-camps'         => 'Diagnostic Care',
			'health-education'         => 'Preventive Health',
			'save-vision'              => 'Eye Surgery',
			'prison-healthcare'        => 'Vulnerable Care',
			'training'                 => 'Paramedic Training',
			'professional-development' => 'Medical Education',
		);
		if ( isset( $map[ $slug ] ) ) {
			return mmm_copy( 'what-we-do', 'category_' . str_replace( '-', '_', $slug ), $map[ $slug ] );
		}
		return mmm_copy( 'what-we-do', 'category_default', 'Healthcare Relief' );
	}
}

// The original <head> preloaded the logo (header.php) plus this hero image.
mmm_hero_preload( '/media/field/hero-humanitarian-relief.jpg' );

$mmm_hero_title = mmm_copy( 'what-we-do', 'hero_title', 'Eleven programmes, run by people who show up in person' );

// Region groups, in the original order; groups with no programmes are skipped.
$mmm_groups = array(
	array( 'region' => 'Pakistan', 'key' => 'pakistan', 'heading' => 'In Pakistan' ),
	array( 'region' => 'Gaza', 'key' => 'gaza', 'heading' => 'In Gaza' ),
	array( 'region' => 'Pakistan and Gaza', 'key' => 'both', 'heading' => 'Across both' ),
);

$mmm_programs = mmm_programs();

get_header();
?>

<header class="relative isolate overflow-hidden border-b border-slate-200/80 bg-gradient-to-b from-[#F0F6FC] via-[#F8FAFC] to-white pt-32 pb-16 md:pt-36 md:pb-20">
<div class="absolute inset-0 -z-10"><img src="/media/field/hero-humanitarian-relief.jpg" alt="<?php echo mmm_attr( $mmm_hero_title ); ?>" class="h-full w-full object-cover opacity-20"/></div>
<div class="shell">
<nav aria-label="Breadcrumb">
<ol class="flex flex-wrap items-center gap-2 text-[0.88rem] font-semibold text-[#6B7280]">
<li class="flex items-center gap-2"><a class="transition-colors hover:text-[#075BD6]" href="/"><?php echo mmm_text( mmm_copy( 'what-we-do', 'crumb_home', 'Home' ) ); ?></a></li>
<li class="flex items-center gap-2"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 text-[#1F2937]" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg><a class="transition-colors hover:text-[#075BD6]" href="/what-we-do/"><?php echo mmm_text( mmm_copy( 'what-we-do', 'crumb_section', 'What we do' ) ); ?></a></li>
</ol>
</nav>
<h1 class="mt-5 max-w-3xl font-display text-[clamp(2.15rem,3.8vw,3.15rem)] leading-[1.08] font-black text-[#0A1020] tracking-tight"><?php echo mmm_text( $mmm_hero_title ); ?></h1>
<p class="mt-4 max-w-2xl text-[1.05rem] leading-relaxed text-[#4B5563] sm:text-[1.14rem]"><?php echo mmm_text( mmm_copy( 'what-we-do', 'hero_lead', 'Each one exists because a team came back from a district and said the thing we were doing was not enough. None of them charge a patient anything.' ) ); ?></p>
</div>
</header>

<div class="py-24 lg:py-28">
<?php foreach ( $mmm_groups as $mmm_gi => $mmm_group ) : ?>
<?php
$mmm_items = array();
foreach ( $mmm_programs as $mmm_p ) {
	if ( ( $mmm_p['region'] ?? '' ) === $mmm_group['region'] ) {
		$mmm_items[] = $mmm_p;
	}
}
if ( ! $mmm_items ) {
	continue;
}
$mmm_count = count( $mmm_items );
?>
<section class="<?php echo $mmm_gi > 0 ? 'mt-20' : ''; ?>">
<div class="shell-wide">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  ">
<div class="flex flex-wrap items-center gap-3 sm:gap-5">
<h2 class="font-display text-[1.85rem] sm:text-[2.15rem] font-black text-[#0A1020]"><?php echo mmm_text( mmm_copy( 'what-we-do', 'group_' . $mmm_group['key'] . '_heading', $mmm_group['heading'] ) ); ?></h2>
<span class="h-px flex-1 bg-slate-200" aria-hidden="true"></span>
<span class="font-display text-[0.85rem] font-bold text-[#6B7280]"><?php echo (int) $mmm_count; ?><!-- --> <!-- --><?php echo mmm_text( mmm_copy( 'what-we-do', 1 === $mmm_count ? 'label_programme' : 'label_programmes', 1 === $mmm_count ? 'programme' : 'programmes' ) ); ?></span>
</div>
</div>
<div class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
<?php foreach ( $mmm_items as $mmm_i => $mmm_p ) : ?>
<?php $mmm_category = mmm_program_category( $mmm_p['slug'] ); ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 70 ); ?>ms" class="reveal  ">
<div class="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white border border-[#DCE2EA] shadow-[0_4px_16px_rgba(10,16,32,0.05)] transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-2 hover:border-[#075BD6]/50 hover:shadow-[0_20px_40px_-12px_rgba(6,73,184,0.16)]">
<div class="relative overflow-hidden aspect-[16/10] shrink-0">
<img src="<?php echo mmm_attr( $mmm_p['image'] ); ?>" alt="<?php echo mmm_attr( $mmm_p['title'] ); ?>" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105"/>
<div class="absolute inset-0 bg-gradient-to-t from-[#0A1020]/80 via-transparent to-[#0A1020]/30" aria-hidden="true"></div>
<div class="absolute top-4 left-4">
<span class="inline-flex items-center gap-1.5 rounded-md border border-white/15 bg-[#0A1020]/90 px-2.5 py-1 font-display text-[0.72rem] font-black uppercase tracking-wider text-[#EF3B19] shadow-sm backdrop-blur-xs">
<span class="h-1.5 w-1.5 rounded-full bg-[#EF3B19]"></span><?php echo mmm_text( $mmm_category ); ?></span>
</div>
<span class="absolute bottom-3.5 right-4 max-w-[calc(100%-2rem)] truncate rounded-md bg-white/90 px-2.5 py-0.5 font-display text-[0.76rem] font-bold text-[#0A1020] shadow-xs backdrop-blur-xs"><?php echo mmm_text( $mmm_p['region'] ); ?></span>
</div>
<div class="flex flex-col flex-1 p-5 sm:p-6">
<h3 class="font-display font-black text-[#0A1020] transition-colors duration-200 group-hover:text-[#075BD6] text-[1.22rem] sm:text-[1.32rem] leading-snug"><?php echo mmm_text( $mmm_p['title'] ); ?></h3>
<p class="mt-3 leading-relaxed text-[#4B5563] text-[0.92rem] sm:text-[0.96rem]"><?php echo mmm_text( $mmm_p['summary'] ); ?></p>
<div class="mt-auto pt-6 border-t border-[#DCE2EA]/80 flex flex-wrap items-center justify-between gap-2.5">
<a class="group/supp inline-flex items-center justify-center gap-1.5 rounded-lg border border-[#DCE2EA] bg-[#F5F7FA] px-3 py-2 font-display text-[0.78rem] sm:text-[0.82rem] font-bold text-[#0A1020] whitespace-nowrap min-h-[38px] transition-all duration-250 hover:border-[#e53935] hover:bg-[#e53935] hover:text-white" href="/donate/">
<svg class="h-3.5 w-3.5 shrink-0 text-[#EF3B19] transition-colors duration-250 group-hover/supp:text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
<span><?php echo mmm_text( mmm_copy( 'what-we-do', 'card_support_label', 'Support' ) ); ?></span>
</a>
<a class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#075BD6] px-3.5 py-2 xs:px-4.5 font-display text-[0.78rem] sm:text-[0.85rem] font-bold text-white whitespace-nowrap min-h-[38px] shadow-xs transition-all duration-250 hover:bg-[#e53935] hover:shadow-sm group/btn" href="/what-we-do/<?php echo mmm_attr( $mmm_p['slug'] ); ?>/">
<span><?php echo mmm_text( mmm_copy( 'what-we-do', 'card_learn_more_label', 'Learn More' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 shrink-0 transition-transform duration-200 group-hover/btn:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</a>
</div>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</section>
<?php endforeach; ?>
</div>
<?php // React's component boundary markers, reproduced verbatim from the build. ?>
<!--$-->
<!--/$-->
<?php
get_footer();
