<?php
/**
 * Document header: <head>, skip link, masthead and mobile drawer.
 *
 * The markup is reproduced exactly from the original build. Content comes
 * from WordPress: organisation details from the mmm_org option and the
 * navigation from mmm_nav. The mega panels and search panel are present in
 * the DOM but hidden, and are toggled by assets/js/mmm.js — React used to
 * insert them on demand.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mmm_email  = mmm_org_value( 'email' );
$mmm_name   = mmm_org_value( 'name' ) ?: 'Muslim Medical Mission';
$mmm_motto  = mmm_org_value( 'motto' );
$mmm_nav    = mmm_nav();
$mmm_home   = is_front_page();
$mmm_preload = mmm_hero_preload();
?>
<!DOCTYPE html>
<html lang="en" class="__variable_212bf7 __variable_7c654a __variable_170677">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<link rel="preload" as="image" href="/media/brand/circle-logo.png"/>
<?php if ( $mmm_preload ) : ?>
<link rel="preload" as="image" href="<?php echo esc_attr( $mmm_preload ); ?>"/>
<?php endif; ?>
<?php wp_head(); ?>
</head>
<body class="overflow-x-hidden antialiased">
<div hidden=""></div>
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:rounded-full focus:bg-[#003475] focus:px-5 focus:py-3 focus:font-display focus:font-bold focus:text-white shadow-lg">Skip to content</a>
<header class="fixed inset-x-0 top-0 z-50 transition-all duration-200">
<div class="border-b border-[#DCE2EA] bg-[#F5F7FA] text-xs text-[#4B5563] backdrop-blur-sm"><div class="shell-wide flex h-[34px] items-center justify-between text-[0.72rem] sm:text-xs"><div class="flex shrink-0 items-center gap-4 sm:gap-6"><a href="mailto:<?php echo esc_attr( $mmm_email ); ?>" class="hidden items-center gap-1.5 font-medium transition-colors hover:text-[#075BD6] sm:flex"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-3 w-3 text-[#075BD6]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg><span><?php echo esc_html( $mmm_email ); ?></span></a></div><div class="flex shrink-0 items-center gap-3 sm:gap-4"><button type="button" data-search-toggle aria-label="Search programmes and field reports" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full transition-colors cursor-pointer text-[#4B5563] hover:bg-[#E9EEF5] hover:text-[#075BD6]"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="2.2"></circle><path d="M16.5 16.5L21 21" stroke-width="2.2" stroke-linecap="round"></path></svg></button><span class="hidden h-3 w-px bg-[#DCE2EA] md:inline" aria-hidden="true"></span><a class="font-semibold text-[#075BD6] transition-colors hover:text-[#0649B8] hover:underline whitespace-nowrap" href="/get-involved/"><span class="hidden min-[380px]:inline">Become a </span>Volunteer</a></div></div></div>
<div class="border-b border-[#DCE2EA] bg-white/95 shadow-[0_2px_12px_rgba(10,16,32,0.04)] backdrop-blur-md"><div class="shell-wide flex h-[68px] sm:h-[84px] md:h-[88px] items-center justify-between gap-1.5 xs:gap-2 sm:gap-4 xl:gap-6"><a aria-label="<?php echo esc_attr( $mmm_name ); ?>, home" class="group flex min-w-0 shrink items-center gap-2 xs:gap-2.5 sm:gap-3.5 xl:min-w-[271px] transition-transform duration-200" href="/"><img src="/media/brand/circle-logo.png" alt="" aria-hidden="true" class="h-[38px] w-[38px] xs:h-[42px] xs:w-[42px] sm:h-12 sm:w-12 md:h-14 md:w-14 shrink-0 object-contain transition-transform duration-300 group-hover:scale-105"/><div class="flex min-w-0 flex-col justify-center xl:w-[201px] xl:shrink-0"><span class="truncate font-display text-[0.82rem] xs:text-[0.9rem] sm:text-[1.05rem] md:text-[1rem] font-black tracking-tight text-[#0A1020] group-hover:text-[#075BD6] transition-colors leading-[1.08] xl:overflow-visible xl:whitespace-nowrap"><?php echo esc_html( $mmm_name ); ?></span><span class="mt-0.5 hidden font-display text-[0.68rem] sm:text-[0.76rem] md:text-[0.82rem] font-bold tracking-tight text-[#EF3B19] leading-none sm:block"><?php echo esc_html( $mmm_motto ); ?></span></div></a>
<nav class="hidden items-center lg:flex" aria-label="Main Navigation"><ul class="flex items-center gap-1 xl:gap-1.5">
<li><a class="relative whitespace-nowrap rounded-md px-2.5 xl:px-3.5 py-2 font-display text-[0.92rem] xl:text-[0.98rem] font-bold transition-all <?php echo $mmm_home ? 'text-[#075BD6] bg-[#F3F7FF]' : 'text-[#4B5563] hover:bg-[#F5F7FA] hover:text-[#075BD6]'; ?>" href="/">Home</a></li>
<?php foreach ( $mmm_nav as $mmm_idx => $mmm_item ) : ?>
<?php
$mmm_active   = mmm_is_nav_active( $mmm_item['href'] ?? '' );
$mmm_open_cls = $mmm_active && ! $mmm_home ? 'text-[#075BD6] bg-[#F3F7FF]' : 'text-[#4B5563] hover:bg-[#F5F7FA] hover:text-[#075BD6]';
$mmm_ico_cls  = $mmm_active && ! $mmm_home ? 'text-[#075BD6]' : 'text-slate-400 group-hover:text-[#075BD6]';
?>
<li class="relative" data-mega-trigger>
<button type="button" aria-expanded="false" class="group flex items-center gap-1 whitespace-nowrap rounded-md px-2.5 xl:px-3.5 py-2 font-display text-[0.92rem] xl:text-[0.98rem] font-bold transition-all <?php echo esc_attr( $mmm_open_cls ); ?>"><span class="whitespace-nowrap"><?php echo esc_html( $mmm_item['label'] ?? '' ); ?></span><svg viewBox="0 0 24 24" aria-hidden="true" class="h-3 w-3 shrink-0 transition-colors <?php echo esc_attr( $mmm_ico_cls ); ?> transition-transform duration-200 " fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg></button>
<?php mmm_render_dropdown_panel( $mmm_item, $mmm_idx ); ?>
</li>
<?php endforeach; ?>
</ul></nav>
<div class="flex shrink-0 items-center gap-1 xs:gap-1.5 sm:gap-2.5 md:gap-3">
<a class="inline-flex shrink-0 items-center justify-center gap-1 xs:gap-1.5 sm:gap-2 rounded-full bg-[#075BD6] min-h-[36px] sm:min-h-[42px] px-3 py-1.5 xs:px-3.5 xs:py-2 sm:px-5 sm:py-2.5 font-display text-[0.78rem] xs:text-[0.82rem] sm:text-[0.88rem] font-bold text-white shadow-[0_4px_14px_rgba(7,91,214,0.35)] transition-all duration-250 hover:-translate-y-0.5 hover:bg-[#e53935] hover:shadow-[0_6px_18px_rgba(229,57,53,0.4)] active:scale-95 whitespace-nowrap" href="/donate/"><span>Donate Now</span><svg viewBox="0 0 24 24" aria-hidden="true" class="h-3 w-3 xs:h-3.5 xs:w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></a>
<button type="button" data-drawer-open aria-label="Open navigation menu" class="flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-[#334155] transition-colors hover:bg-slate-100 lg:hidden cursor-pointer"><svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"></path></svg></button>
</div>
</div></div>
<?php mmm_render_search_panel(); ?>
</header>
<div class="fixed inset-0 z-[70] lg:hidden pointer-events-none" aria-hidden="true" data-drawer>
<div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity duration-300 opacity-0" data-drawer-backdrop></div>
<div class="absolute inset-y-0 right-0 flex w-[min(380px,88vw)] flex-col bg-white shadow-2xl transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] translate-x-full" data-drawer-sheet>
<div class="flex items-center justify-between border-b border-slate-200/80 px-4 py-3.5 sm:px-5 sm:py-4">
<a class="flex min-w-0 items-center gap-2.5 sm:gap-3" href="/"><img src="/media/brand/circle-logo.png" alt="" aria-hidden="true" class="h-10 w-10 sm:h-11 sm:w-11 shrink-0 object-contain"/><div class="flex min-w-0 flex-col"><span class="truncate font-display text-[1rem] sm:text-[1.08rem] font-black text-[#0A1020] leading-tight"><?php echo esc_html( $mmm_name ); ?></span><span class="mt-0.5 truncate font-display text-[0.64rem] sm:text-[0.66rem] font-bold text-[#EF3B19] leading-none"><?php echo esc_html( $mmm_motto ); ?></span></div></a>
<button type="button" data-drawer-close aria-label="Close menu" class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-slate-200 text-[#334155] transition-colors hover:bg-slate-100 ml-2 cursor-pointer"><svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" aria-hidden="true" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6L6 18"></path></svg></button>
</div>
<div class="border-b border-slate-100 bg-slate-50/70 px-4 py-2.5 sm:px-5 sm:py-3"><button type="button" data-drawer-search class="flex w-full min-h-[44px] items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-medium text-slate-500 shadow-xs transition-colors hover:border-[#075BD6] hover:text-[#0A1020] cursor-pointer whitespace-nowrap"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 text-[#075BD6]" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="2.2"></circle><path d="M16.5 16.5L21 21" stroke-width="2.2" stroke-linecap="round"></path></svg><span class="truncate">Search programmes, reports...</span></button></div>
<nav class="flex-1 overflow-y-auto px-5 py-4" aria-label="Mobile Navigation"><ul class="divide-y divide-slate-100">
<li><a class="block py-3 font-display text-[1rem] font-bold <?php echo $mmm_home ? 'text-[#046BD2]' : 'text-[#334155]'; ?>" href="/">Home</a></li>
<?php foreach ( $mmm_nav as $mmm_item ) : ?>
<li>
<button type="button" aria-expanded="false" data-drawer-section class="flex w-full items-center justify-between py-3 font-display text-[1rem] font-bold <?php echo mmm_is_nav_active( $mmm_item['href'] ?? '' ) ? 'text-[#046BD2]' : 'text-[#334155]'; ?>"><span class=""><?php echo esc_html( $mmm_item['label'] ?? '' ); ?></span><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 text-slate-400 transition-transform duration-200 " fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg></button>
<div class="grid transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] grid-rows-[0fr] opacity-0" data-drawer-panel>
<div class="overflow-hidden">
<?php foreach ( (array) ( $mmm_item['columns'] ?? array() ) as $mmm_col ) : ?>
<div class="mb-3"><p class="font-display text-[0.82rem] font-extrabold uppercase tracking-wider text-[#003475]"><?php echo esc_html( $mmm_col['heading'] ?? '' ); ?></p>
<ul class="mt-1.5 space-y-1 border-l-2 border-slate-100 pl-3">
<?php foreach ( (array) ( $mmm_col['links'] ?? array() ) as $mmm_l ) : ?>
<li><a class="block py-1 text-[0.9rem] font-semibold text-[#334155] hover:text-[#046BD2]" href="<?php echo esc_url( mmm_link( $mmm_l['href'] ?? '' ) ); ?>"><?php echo esc_html( $mmm_l['label'] ?? '' ); ?></a></li>
<?php endforeach; ?>
</ul></div>
<?php endforeach; ?>
</div></div>
</li>
<?php endforeach; ?>
</ul></nav>
<div class="grid gap-2.5 border-t border-slate-100 bg-slate-50 p-5">
<a class="flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-[#046BD2] px-4 py-3 text-center font-display text-sm sm:text-base font-bold text-white shadow-sm transition-all duration-250 hover:bg-[#e53935] whitespace-nowrap active:scale-98" href="/donate/"><span>Donate Now</span><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg></a>
<a class="flex min-h-[44px] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center font-display text-sm sm:text-base font-bold text-[#003475] transition-all duration-250 hover:bg-[#e53935] hover:border-[#e53935] hover:text-white whitespace-nowrap active:scale-98" href="/get-involved/">Become a Volunteer</a>
<div class="pt-2 text-center text-xs text-[#7A7A7A]"><p><?php echo esc_html( mmm_org_value( 'phone' ) ); ?></p><p class="mt-0.5"><?php echo esc_html( $mmm_email ); ?></p></div>
</div>
</div>
</div>
<main id="main">
