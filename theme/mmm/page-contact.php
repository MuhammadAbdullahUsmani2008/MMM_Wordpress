<?php
/**
 * The Contact page — route /contact/.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The original <head> preloaded the hero photograph for this route.
mmm_hero_preload( '/media/brand/community-gathering.jpg' );

get_header();

$mmm_whatsapp = mmm_org_value( 'whatsapp' );
$mmm_phone     = mmm_org_value( 'phone' );
$mmm_phone_uri = mmm_org_value( 'phoneHref' );
$mmm_email     = mmm_org_value( 'email' );

$mmm_channels = array(
	array(
		'key'      => 'whatsapp',
		'title'    => 'WhatsApp',
		'body'     => 'The fastest route to a coordinator, for volunteering, donations and press.',
		'href'     => $mmm_whatsapp,
		'action'   => $mmm_phone,
		'external' => true,
	),
	array(
		'key'      => 'phone',
		'title'    => 'Phone',
		'body'     => 'Office hours, Monday to Saturday. If nobody answers we are usually on a deployment.',
		'href'     => $mmm_phone_uri,
		'action'   => $mmm_phone,
		'external' => false,
	),
	array(
		'key'      => 'email',
		'title'    => 'Email',
		'body'     => 'For partnerships, supplier offers, documentation requests and media enquiries.',
		'href'     => 'mailto:' . $mmm_email,
		'action'   => $mmm_email,
		'external' => false,
	),
);

$mmm_social = mmm_social();
?>

<header class="relative isolate overflow-hidden border-b border-slate-200/80 bg-gradient-to-b from-[#F0F6FC] via-[#F8FAFC] to-white pt-32 pb-16 md:pt-36 md:pb-20">
<div class="absolute inset-0 -z-10"><img src="/media/brand/community-gathering.jpg" alt="<?php echo esc_attr( mmm_copy( 'contact', 'hero_title', 'Talk to a person, not a ticketing system' ) ); ?>" class="h-full w-full object-cover opacity-20"/></div>
<div class="shell">
<nav aria-label="Breadcrumb">
<ol class="flex flex-wrap items-center gap-2 text-[0.88rem] font-semibold text-[#6B7280]">
<li class="flex items-center gap-2"><a class="transition-colors hover:text-[#075BD6]" href="/">Home</a></li>
<li class="flex items-center gap-2"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 text-[#1F2937]" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg><a class="transition-colors hover:text-[#075BD6]" href="/contact/"><?php echo esc_html( mmm_copy( 'contact', 'breadcrumb_label', 'Contact' ) ); ?></a></li>
</ol>
</nav>
<h1 class="mt-5 max-w-3xl font-display text-[clamp(2.15rem,3.8vw,3.15rem)] leading-[1.08] font-black text-[#0A1020] tracking-tight"><?php echo esc_html( mmm_copy( 'contact', 'hero_title', 'Talk to a person, not a ticketing system' ) ); ?></h1>
<p class="mt-4 max-w-2xl text-[1.05rem] leading-relaxed text-[#4B5563] sm:text-[1.14rem]"><?php echo esc_html( mmm_copy( 'contact', 'hero_lead', 'There is no call centre. The number below reaches a volunteer coordinator who is usually a practising clinician.' ) ); ?></p>
</div>
</header>

<section class="py-24 lg:py-28">
<div class="shell-wide grid gap-14 lg:grid-cols-12">
<div class="lg:col-span-7">
<ul class="space-y-4">
<?php foreach ( $mmm_channels as $mmm_i => $mmm_channel ) : ?>
<li data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 80 ); ?>ms" class="reveal  ">
<a href="<?php echo esc_url( $mmm_channel['href'] ); ?>"<?php echo $mmm_channel['external'] ? ' target="_blank" rel="noreferrer"' : ''; ?> class="group flex flex-wrap items-center justify-between gap-5 rounded-2xl bg-white p-7 ring-1 ring-line transition-all duration-400 hover:-translate-y-1 hover:shadow-lift hover:ring-transparent">
<div class="min-w-0">
<h2 class="font-display text-[1.2rem] font-extrabold text-navy-800"><?php echo esc_html( mmm_copy( 'contact', $mmm_channel['key'] . '_title', $mmm_channel['title'] ) ); ?></h2>
<p class="mt-2 max-w-md text-[0.94rem] leading-relaxed text-slate-body"><?php echo esc_html( mmm_copy( 'contact', $mmm_channel['key'] . '_body', $mmm_channel['body'] ) ); ?></p>
</div>
<span class="inline-flex items-center gap-2 xs:gap-2.5 font-display text-[0.88rem] xs:text-[1rem] font-bold text-blue-brand whitespace-nowrap shrink-0">
<span><?php echo esc_html( $mmm_channel['action'] ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1.5" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</span>
</a>
</li>
<?php endforeach; ?>
</ul>
<div data-visible="false" style="--reveal-delay:100ms" class="reveal  ">
<div class="mt-8 rounded-2xl bg-surface p-7">
<h2 class="font-display text-[1.15rem] font-extrabold text-navy-800"><?php echo esc_html( mmm_copy( 'contact', 'receipt_heading', 'Sending a donation receipt' ) ); ?></h2>
<p class="mt-2.5 text-[0.95rem] leading-relaxed text-slate-body"><?php echo esc_html( mmm_copy( 'contact', 'receipt_body', 'Send the screenshot on WhatsApp with the programme name. A volunteer will confirm it and, once the deployment closes, tell you what it funded and how many people it reached.' ) ); ?></p>
</div>
</div>
</div>
<div data-visible="false" style="--reveal-delay:0ms" class="reveal reveal-right lg:col-span-4 lg:col-start-9">
<div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-soft">
<img src="/media/brand/mmm-lahore-2015.jpg" alt="Muslim Medical Mission volunteers in Lahore" loading="lazy" class="aspect-[4/3] w-full object-cover"/>
<div class="p-7">
<h2 class="font-display text-[1.2rem] font-extrabold text-[#003475]"><?php echo esc_html( mmm_org_value( 'legalName' ) ); ?></h2>
<address class="mt-4 space-y-1 text-[0.95rem] not-italic text-[#334155]">
<p><?php echo esc_html( mmm_org_value( 'address_line1' ) ); ?></p>
<p><?php echo esc_html( mmm_org_value( 'address_line2' ) ); ?></p>
<p><?php echo esc_html( mmm_org_value( 'address_country' ) ); ?></p>
</address>
<div class="mt-6 border-t border-slate-100 pt-5">
<p class="font-display text-[0.95rem] font-bold text-[#003475]"><?php echo esc_html( mmm_copy( 'contact', 'follow_heading', 'Follow the work' ) ); ?></p>
<ul class="mt-3 flex flex-wrap gap-x-5 gap-y-2">
<?php foreach ( $mmm_social as $mmm_account ) : ?>
<li><a href="<?php echo esc_url( $mmm_account['href'] ?? '' ); ?>" target="_blank" rel="noreferrer" class="font-display text-[0.9rem] font-bold text-[#046BD2] transition-colors hover:text-[#003475]"><?php echo esc_html( $mmm_account['name'] ?? '' ); ?></a></li>
<?php endforeach; ?>
</ul>
</div>
</div>
</div>
</div>
</div>
</section>
<!--$-->
<!--/$-->
<?php
get_footer();
