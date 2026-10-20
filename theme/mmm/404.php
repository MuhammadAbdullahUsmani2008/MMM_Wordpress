<?php
/**
 * Not found.
 *
 * Reproduces out/404.html. WordPress sets the 404 status itself; header.php
 * and footer.php provide the chrome (and the document title, which on the
 * original build was simply the site title).
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

mmm_hero_preload( '/media/disaster/disaster-13.jpg' );

get_header();
?>

<section class="relative isolate flex min-h-[72vh] items-center overflow-hidden border-b border-slate-200/80 bg-gradient-to-b from-[#F0F6FC] via-[#F8FAFC] to-white pt-24"><img src="/media/disaster/disaster-13.jpg" alt="" aria-hidden="true" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-15"/><div class="shell-wide py-24"><h1 class="max-w-2xl text-[clamp(2.2rem,5vw,3.6rem)] font-extrabold text-[#003475]"><?php echo esc_html( mmm_copy( '404', 'title', 'This road does not go through' ) ); ?></h1><p class="mt-5 max-w-xl text-[1.05rem] leading-relaxed text-[#334155]"><?php echo esc_html( mmm_copy( '404', 'lead', 'The page you were looking for has moved or never existed. The work carries on either way.' ) ); ?></p><div class="mt-9 flex flex-wrap gap-3 sm:gap-4"><a class="group inline-flex items-center justify-center gap-1 rounded-full px-1.5 py-2.5 xs:px-2 xs:py-3 sm:px-7 sm:py-3.5 min-h-[38px] max-w-full font-display text-[0.5625rem] xs:text-[0.6rem] sm:text-[0.95rem] font-bold tracking-tight transition-all duration-300 hover:-translate-y-0.5 active:scale-98 bg-[#046BD2] text-white hover:bg-[#e53935] hover:border-[#e53935] shadow-[0_10px_24px_-8px_rgba(4,107,210,0.4)] hover:shadow-[0_10px_24px_-8px_rgba(229,57,53,0.4)] flex-1 sm:flex-none" href="/"><span class="whitespace-nowrap"><?php echo esc_html( mmm_copy( '404', 'cta_home', 'Back to the home page' ) ); ?></span><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg></a><a class="group inline-flex items-center justify-center gap-1 rounded-full px-1.5 py-2.5 xs:px-2 xs:py-3 sm:px-7 sm:py-3.5 min-h-[38px] max-w-full font-display text-[0.5625rem] xs:text-[0.6rem] sm:text-[0.95rem] font-bold tracking-tight transition-all duration-300 hover:-translate-y-0.5 active:scale-98 border-2 border-slate-300 text-[#334155] hover:border-[#e53935] hover:bg-[#e53935] hover:text-white flex-1 sm:flex-none" href="/what-we-do/"><span class="whitespace-nowrap"><?php echo esc_html( mmm_copy( '404', 'cta_programs', 'See the programmes' ) ); ?></span><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg></a></div></div></section>
<!--$-->
<!--/$-->
<?php
get_footer();
