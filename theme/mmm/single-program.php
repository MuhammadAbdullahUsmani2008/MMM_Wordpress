<?php
/**
 * A single programme — /what-we-do/{slug}/.
 *
 * One template renders all eleven programmes; everything that varies comes
 * from the program CPT (mmm_program_by_slug()). The programme's body copy,
 * summary, highlights and gallery are database content and deliberately do
 * not appear as mmm_copy() fields.
 *
 * Ground truth (diffed against each other; only data counts differ):
 *   E:\MMM02\out\what-we-do\save-vision\index.html
 *   E:\MMM02\out\what-we-do\gaza-field-clinics\index.html
 *   E:\MMM02\out\what-we-do\flood-medical-camps\index.html
 *   E:\MMM02\out\what-we-do\training\index.html
 *   ... and the remaining seven routes.
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

$slug    = (string) get_query_var( 'post_name' );
$program = $slug ? mmm_program_by_slug( $slug ) : null;

if ( ! $program ) {
	$queried = get_queried_object();
	if ( $queried instanceof WP_Post ) {
		$program = mmm_program_by_slug( $queried->post_name );
	}
}

if ( ! $program ) {
	get_header();
	echo '<div class="shell-wide py-32"><h1 class="font-display text-[2rem] font-black text-[#0A1020]">' . esc_html__( 'Programme not found', 'mmm' ) . '</h1></div>';
	get_footer();
	return;
}

// The original <head> preloads the logo (header.php) plus this programme's image.
if ( $program['image'] ) {
	mmm_hero_preload( $program['image'] );
}

// Related: every other programme, in menu order, the first three.
$mmm_related = array();
foreach ( mmm_programs() as $mmm_other ) {
	if ( $mmm_other['slug'] === $program['slug'] ) {
		continue;
	}
	$mmm_related[] = $mmm_other;
	if ( count( $mmm_related ) >= 3 ) {
		break;
	}
}

// Show at most four photographs, and never exactly three: 4+ shows four,
// three shows two, fewer than three shows what exists. Stored images are untouched.
$mmm_gallery = array_values( (array) $program['gallery'] );
$mmm_gallery = array_slice( $mmm_gallery, 0, count( $mmm_gallery ) >= 4 ? 4 : ( 3 === count( $mmm_gallery ) ? 2 : count( $mmm_gallery ) ) );

// The not-found branch above opens and closes the document itself. This is
// the normal path, and it has to open it too — without this the page emits
// its own markup with no <html>, no masthead and no <main id="main">, which
// leaves footer.php closing a tag that was never opened.
get_header();
?>

<header class="relative isolate overflow-hidden border-b border-slate-200/80 bg-gradient-to-b from-[#F0F6FC] via-[#F8FAFC] to-white pt-32 pb-16 md:pt-36 md:pb-20">
<div class="absolute inset-0 -z-10">
<?php if ( $program['image'] ) : ?>
<img src="<?php echo mmm_attr( $program['image'] ); ?>" alt="<?php echo mmm_attr( $program['title'] ); ?>" class="h-full w-full object-cover opacity-20"/>
<?php else : ?>
<div class="h-full w-full opacity-60" aria-hidden="true" style="background-image: radial-gradient(60% 70% at 85% 20%, rgba(4,107,210,0.12), transparent 70%), radial-gradient(40% 50% at 10% 80%, rgba(243,230,230,0.8), transparent 60%);"></div>
<?php endif; ?>
</div>
<div class="shell">
<nav aria-label="Breadcrumb">
<ol class="flex flex-wrap items-center gap-2 text-[0.88rem] font-semibold text-[#6B7280]">
<li class="flex items-center gap-2"><a class="transition-colors hover:text-[#075BD6]" href="/"><?php echo mmm_text( mmm_copy( 'program', 'crumb_home', 'Home' ) ); ?></a></li>
<li class="flex items-center gap-2"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 text-[#1F2937]" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg><a class="transition-colors hover:text-[#075BD6]" href="/what-we-do/"><?php echo mmm_text( mmm_copy( 'program', 'crumb_section', 'What we do' ) ); ?></a></li>
<li class="flex items-center gap-2"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 text-[#1F2937]" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg><a class="transition-colors hover:text-[#075BD6]" href="/what-we-do/<?php echo mmm_attr( $program['slug'] ); ?>/"><?php echo mmm_text( $program['title'] ); ?></a></li>
</ol>
</nav>
<h1 class="mt-5 max-w-3xl font-display text-[clamp(2.15rem,3.8vw,3.15rem)] leading-[1.08] font-black text-[#0A1020] tracking-tight"><?php echo mmm_text( $program['title'] ); ?></h1>
<?php if ( ! empty( $program['subtitle'] ) ) : ?>
<p class="mt-2 max-w-3xl font-display text-xl font-bold text-[#003475] sm:text-2xl"><?php echo mmm_text( $program['subtitle'] ); ?></p>
<?php endif; ?>
<p class="mt-4 max-w-2xl text-[1.05rem] leading-relaxed text-[#4B5563] sm:text-[1.14rem]"><?php echo mmm_text( $program['summary'] ); ?></p>
</div>
</header>

<?php // Facts strip ?>
<section class="relative z-10 -mt-14">
<div class="shell-wide">
<dl class="grid overflow-hidden rounded-2xl bg-white shadow-[0_28px_60px_-28px_rgba(10,23,48,0.45)] <?php echo ( 1 === count( (array) $program['highlights'] ) ) ? '' : ( 2 === count( (array) $program['highlights'] ) ? 'sm:grid-cols-2' : 'sm:grid-cols-3' ); ?> sm:divide-x sm:divide-line">
<?php foreach ( array_values( (array) $program['highlights'] ) as $mmm_i => $mmm_h ) : ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 80 ); ?>ms" class="reveal  p-7">
<dt class="text-[0.88rem] text-slate-muted"><?php echo mmm_text( $mmm_h['label'] ?? '' ); ?></dt>
<dd class="mt-2.5 font-display text-[1.35rem] font-extrabold text-navy-800"><?php echo mmm_text( $mmm_h['value'] ?? '' ); ?></dd>
</div>
<?php endforeach; ?>
</dl>
</div>
</section>

<?php // Body + gallery rail ?>
<section class="py-20 lg:py-28">
<div class="shell-wide grid gap-14 lg:grid-cols-12">
<div class="<?php echo $mmm_gallery ? 'lg:col-span-7' : 'lg:col-span-8'; ?>">
<div class="rich mt-8 text-[1.06rem]">
<?php foreach ( array_values( (array) $program['body'] ) as $mmm_i => $mmm_paragraph ) : ?>
<?php if ( preg_match( '/^<(?:h[2-6]|p|ul|ol|div|section)\b/i', trim( $mmm_paragraph ) ) ) : ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 60 ); ?>ms" class="reveal  mb-5"><?php echo wp_kses_post( $mmm_paragraph ); ?></div>
<?php else : ?>
<p data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 60 ); ?>ms" class="reveal  mb-5"><?php echo mmm_text( $mmm_paragraph ); ?></p>
<?php endif; ?>
<?php endforeach; ?>
</div>
<div data-visible="false" style="--reveal-delay:120ms" class="reveal  ">
<div class="mt-10 flex flex-wrap gap-2 sm:gap-4">
<a class="group inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 xs:gap-2 rounded-full bg-[#046BD2] px-2.5 py-2.5 xs:px-3 xs:py-3 sm:px-7 sm:py-3.5 font-display text-[0.72rem] xs:text-[0.76rem] sm:text-[0.95rem] font-bold text-white min-h-[38px] shadow-sm transition-all duration-250 hover:-translate-y-0.5 hover:bg-[#e53935]" href="/donate/">
<span class="whitespace-nowrap"><?php echo mmm_text( mmm_copy( 'program', 'cta_fund', 'Fund this programme' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</a>
<a class="inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 xs:gap-2 rounded-full border-2 border-slate-300 px-2.5 py-2.5 xs:px-3 xs:py-3 sm:px-7 sm:py-3.5 font-display text-[0.72rem] xs:text-[0.76rem] sm:text-[0.95rem] font-bold text-[#003475] min-h-[38px] transition-all duration-250 hover:border-[#e53935] hover:bg-[#e53935] hover:text-white" href="/get-involved/">
<span class="whitespace-nowrap"><?php echo mmm_text( mmm_copy( 'program', 'cta_join', 'Join a deployment' ) ); ?></span>
</a>
</div>
</div>
</div>

<?php // Gallery rail, only where real photographs of this work exist ?>
<div class="lg:col-span-4 lg:col-start-9 <?php echo $mmm_gallery ? '' : 'hidden'; ?>">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal reveal-right ">
<h2 class="font-display text-[1.15rem] font-extrabold text-[#003475]"><?php echo mmm_text( mmm_copy( 'program', 'gallery_heading', 'From this programme' ) ); ?></h2>
<span class="mt-3 block h-0.5 w-10 bg-[#046BD2]" aria-hidden="true"></span>
</div>
<div class="mt-6 grid grid-cols-2 gap-3">
<?php foreach ( $mmm_gallery as $mmm_i => $mmm_src ) : ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 70 ); ?>ms" class="reveal  overflow-hidden rounded-xl">
<img src="<?php echo mmm_attr( $mmm_src ); ?>" alt="" aria-hidden="true" loading="lazy" class="aspect-square w-full object-cover transition-transform duration-700 hover:scale-110"/>
</div>
<?php endforeach; ?>
</div>
</div>
</div>
</section>

<?php // Related ?>
<section class="bg-surface py-20 lg:py-24">
<div class="shell-wide">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  ">
<div class="flex flex-wrap items-center gap-3 sm:gap-5">
<h2 class="font-display text-[1.5rem] font-extrabold text-[#003475]"><?php echo mmm_text( mmm_copy( 'program', 'related_heading', 'Other programmes' ) ); ?></h2>
<span class="h-px flex-1 bg-slate-200" aria-hidden="true"></span>
<a class="group inline-flex items-center gap-1.5 xs:gap-2 font-display text-[0.85rem] xs:text-[0.9rem] font-bold text-[#046BD2] shrink-0 min-h-[36px]" href="/what-we-do/">
<span class="link-underline"><?php echo mmm_text( mmm_copy( 'program', 'related_see_all', 'See all' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</a>
</div>
</div>
<div class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
<?php foreach ( $mmm_related as $mmm_i => $mmm_other ) : ?>
<?php $mmm_category = mmm_program_category( $mmm_other['slug'] ); ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 70 ); ?>ms" class="reveal  ">
<div class="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white border border-[#DCE2EA] shadow-[0_4px_16px_rgba(10,16,32,0.05)] transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-2 hover:border-[#075BD6]/50 hover:shadow-[0_20px_40px_-12px_rgba(6,73,184,0.16)]">
<div class="relative overflow-hidden aspect-[16/10] shrink-0">
<img src="<?php echo mmm_attr( $mmm_other['image'] ); ?>" alt="<?php echo mmm_attr( $mmm_other['title'] ); ?>" loading="lazy" class="h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105"/>
<div class="absolute inset-0 bg-gradient-to-t from-[#0A1020]/80 via-transparent to-[#0A1020]/30" aria-hidden="true"></div>
<div class="absolute top-4 left-4">
<span class="inline-flex items-center gap-1.5 rounded-md border border-white/15 bg-[#0A1020]/90 px-2.5 py-1 font-display text-[0.72rem] font-black uppercase tracking-wider text-[#EF3B19] shadow-sm backdrop-blur-xs">
<span class="h-1.5 w-1.5 rounded-full bg-[#EF3B19]"></span><?php echo mmm_text( $mmm_category ); ?></span>
</div>
<span class="absolute bottom-3.5 right-4 max-w-[calc(100%-2rem)] truncate rounded-md bg-white/90 px-2.5 py-0.5 font-display text-[0.76rem] font-bold text-[#0A1020] shadow-xs backdrop-blur-xs"><?php echo mmm_text( $mmm_other['region'] ); ?></span>
</div>
<div class="flex flex-col flex-1 p-5 sm:p-6">
<h3 class="font-display font-black text-[#0A1020] transition-colors duration-200 group-hover:text-[#075BD6] text-[1.22rem] sm:text-[1.32rem] leading-snug"><?php echo mmm_text( $mmm_other['title'] ); ?></h3>
<p class="mt-3 leading-relaxed text-[#4B5563] text-[0.92rem] sm:text-[0.96rem]"><?php echo mmm_text( $mmm_other['summary'] ); ?></p>
<div class="mt-auto pt-6 border-t border-[#DCE2EA]/80 flex flex-wrap items-center justify-between gap-2.5">
<a class="group/supp inline-flex items-center justify-center gap-1.5 rounded-lg border border-[#DCE2EA] bg-[#F5F7FA] px-3 py-2 font-display text-[0.78rem] sm:text-[0.82rem] font-bold text-[#0A1020] whitespace-nowrap min-h-[38px] transition-all duration-250 hover:border-[#e53935] hover:bg-[#e53935] hover:text-white" href="/donate/">
<svg class="h-3.5 w-3.5 shrink-0 text-[#EF3B19] transition-colors duration-250 group-hover/supp:text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
<span><?php echo mmm_text( mmm_copy( 'what-we-do', 'card_support_label', 'Support' ) ); ?></span>
</a>
<a class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#075BD6] px-3.5 py-2 xs:px-4.5 font-display text-[0.78rem] sm:text-[0.85rem] font-bold text-white whitespace-nowrap min-h-[38px] shadow-xs transition-all duration-250 hover:bg-[#e53935] hover:shadow-sm group/btn" href="/what-we-do/<?php echo mmm_attr( $mmm_other['slug'] ); ?>/">
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
<?php // React's component boundary markers, reproduced verbatim from the build. ?>
<!--$-->
<!--/$-->
<?php
get_footer();
