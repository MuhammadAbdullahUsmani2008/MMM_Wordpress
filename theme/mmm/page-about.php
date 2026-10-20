<?php
/**
 * The About page — route /about/.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The original <head> preloaded the hero photograph for this route.
mmm_hero_preload( '/media/field/hero-humanitarian-relief.jpg' );

get_header();

$mmm_hero_alt = mmm_copy( 'about', 'hero_title', 'A volunteer network of Muslim healthcare professionals' );

$mmm_pillars = array(
	array(
		'key'  => 'mission',
		'title'=> 'Mission',
		'body' => 'To become dynamic Muslim health care professionals who will pioneer social change by acquiring knowledge and wisdom, and practicing Dawah with acumen and communication that is convincing and effective, both professionally and culturally. To orientate health education with Islamic values and the application of these values in patient care.',
	),
	array(
		'key'  => 'vision',
		'title'=> 'Vision',
		'body' => 'In Surah \'Mohammed\', verse 19, Allah Subhanaho wa ta\'la addresses you like this: "Know therefore that there is no god but Allah and ask forgiveness for the fault and for the men and women who believe: for Allah knows how you move about and how you dwell in your homes." Respected HCP, the time and the manner of our conducting ourselves at home and out, is all material to the judgment of our conduct by \'The Supreme Judgment Authority\' - Allah SWT. Hence, even for nuance in our professional, moral and spiritual progress, we must learn to seek Allah\'s SWT help and guidance so that we can develop the ability to achieve shades of \'Sibghat-Ullah\'.',
	),
);

$mmm_timeline = array(
	array( '2005', 'The Foundation of MMM', 'The October 2005 earthquake in northern Pakistan became the catalyst for bringing Muslim healthcare professionals together as the Muslim Medical Mission, formalizing their commitment to healthcare, education, and humanitarian service.' ),
	array( '2006', 'From Commitment to Action', 'MMM began strengthening its programs for healthcare professionals through training sessions, seminars, and specialist lectures, while continuing free medical and community health initiatives.' ),
	array( '2010', 'Responding to Disaster', 'As major floods affected communities across Pakistan, MMM expanded its humanitarian response, providing medical assistance and relief services to people affected by disaster.' ),
	array( '2015', 'Strengthening Professional Development', 'MMM expanded its focus on developing healthcare professionals through professional education, training, medical knowledge, and Islamic guidance, reinforcing the connection between ethical practice and patient care.' ),
	array( '2022', 'Expanding Humanitarian Outreach', 'MMM continued its disaster-response work in severely affected regions, providing medical camps, essential supplies, and humanitarian assistance to communities in need.' ),
	array( '2024', 'A Growing National Network', 'MMM continued its national programs, bringing healthcare professionals together through medical conferences, educational activities, training, and community healthcare initiatives.' ),
	array( '2025', 'Humanitarian Response at Scale', 'MMM continued its relief operations in Pakistan while expanding humanitarian assistance, including support for flood-affected communities in South Punjab and Balochistan.' ),
	array( '2026', 'Serving Beyond Borders', 'MMM continues its healthcare and humanitarian mission in Pakistan while providing assistance to communities affected by the crisis in Gaza, including food, water, medical services, and other essential relief.' ),
	array( 'Today', 'Wisdom. Action. Service.', 'Today, MMM brings together healthcare, professional development, Islamic values, and humanitarian service with a continuing commitment to serve humanity without discrimination.' ),
);

$mmm_means   = mmm_means();

// The regions shown under "Where we work": copy key, name, description.
$mmm_reach = array(
	array( 'pakistan', 'All Over Pakistan', 'Free medical camps, disaster relief, professional training and healthcare outreach for underserved communities across Pakistan.' ),
	array( 'gaza', 'Gaza', 'Food, water, medical services and other essential relief for communities affected by the crisis in Gaza.' ),
);
?>

<header class="relative isolate overflow-hidden border-b border-slate-200/80 bg-gradient-to-b from-[#F0F6FC] via-[#F8FAFC] to-white pt-32 pb-16 md:pt-36 md:pb-20">
<div class="absolute inset-0 -z-10"><img src="/media/field/hero-humanitarian-relief.jpg" alt="<?php echo esc_attr( $mmm_hero_alt ); ?>" class="h-full w-full object-cover opacity-20"/></div>
<div class="shell">
<nav aria-label="Breadcrumb">
<ol class="flex flex-wrap items-center gap-2 text-[0.88rem] font-semibold text-[#6B7280]">
<li class="flex items-center gap-2"><a class="transition-colors hover:text-[#075BD6]" href="/">Home</a></li>
<li class="flex items-center gap-2"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 text-[#1F2937]" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg><a class="transition-colors hover:text-[#075BD6]" href="/about/"><?php echo esc_html( mmm_copy( 'about', 'breadcrumb_label', 'About us' ) ); ?></a></li>
</ol>
</nav>
<h1 class="mt-5 max-w-3xl font-display text-[clamp(2.15rem,3.8vw,3.15rem)] leading-[1.08] font-black text-[#0A1020] tracking-tight"><?php echo esc_html( mmm_copy( 'about', 'hero_title', 'A volunteer network of Muslim healthcare professionals' ) ); ?></h1>
<p class="mt-4 max-w-2xl text-[1.05rem] leading-relaxed text-[#4B5563] sm:text-[1.14rem]"><?php echo esc_html( mmm_copy( 'about', 'hero_lead', 'No paid fundraising arm, no field office in a capital city. Doctors, paramedics, pharmacists and students who take leave from their own jobs to go where the clinic is not.' ) ); ?></p>
</div>
</header>

<section class="bg-white py-12 sm:py-14 lg:py-16 border-b border-slate-200/80">
<div class="shell-wide grid gap-8 lg:grid-cols-12">
<div class="lg:col-span-7">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  "><h2 class="font-display text-[clamp(2.05rem,3.4vw,2.85rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo esc_html( mmm_copy( 'about', 'who_heading', 'Who we are' ) ); ?></h2></div>
<div class="rich mt-6 text-[1.04rem] sm:text-[1.12rem] leading-relaxed text-[#4B5563]">
<p data-visible="false" style="--reveal-delay:60ms" class="reveal  "><?php echo esc_html( mmm_copy( 'about', 'who_para_1', 'There was a group of Muslim Health Care professionals working on some issues of common interest like free medical camping, free health care clinics, and health awareness seminars with the core mission of promoting a better understanding of Islam and Medical practice within the framework of Islam. Meanwhile, northern areas of Pakistan were struck by a devastating earthquake in October 2005 which became the inciting stimulus for the organization of these Health Care Professionals into the Muslim Medical Mission (MMM).' ) ); ?></p>
<p data-visible="false" style="--reveal-delay:100ms" class="reveal  mt-4"><?php echo esc_html( mmm_copy( 'about', 'who_para_2', 'The organization imparts, educational and research programs relating to the Islamic code of conduct by arranging training sessions, seminars, symposia, and specialist lectures for the health care professionals of all medical fields as well as for the general public. It also offers humanitarian services such as relief operations, welfare clinics, and surgical camps to those who are in need of disaster without any discrimination of race & religion. We believe in evidence-based medical & religious practice.' ) ); ?></p>
</div>
</div>
<div data-visible="false" style="--reveal-delay:0ms" class="reveal reveal-right lg:col-span-4 lg:col-start-9">
<img src="/media/disaster/new-hero-s1.jpeg" alt="Muslim Medical Mission disaster response image" class="w-full rounded-2xl object-cover shadow-md" loading="lazy"/>
<div class="mt-5 rounded-2xl border border-slate-200/80 bg-surface p-6">
<p class="font-quote text-[1.18rem] leading-snug text-[#0A1020] italic"><?php echo esc_html( mmm_org_value( 'motto' ) ); ?></p>
<p class="mt-2.5 text-[0.88rem] text-[#6B7280]"><?php echo esc_html( mmm_copy( 'about', 'motto_note', 'The words the organisation was founded on, and the test applied to every deployment since.' ) ); ?></p>
</div>
</div>
</div>
</section>

<section id="mission" class="border-b border-slate-200/80 bg-[#F0F5FA] py-12 sm:py-14 lg:py-16">
<div class="shell-wide">
<div class="">
<div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7"><h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo esc_html( mmm_copy( 'about', 'mission_heading', 'Mission and vision' ) ); ?></h2></div>
<div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5"><p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo esc_html( mmm_copy( 'about', 'mission_lead', 'Both were written at the start and neither has been rewritten for a funding application since.' ) ); ?></p></div>
</div>
</div>
<div class="mt-8 grid gap-5 lg:grid-cols-2">
<?php foreach ( $mmm_pillars as $mmm_i => $mmm_pillar ) : ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 90 ); ?>ms" class="reveal  ">
<div class="h-full rounded-2xl border border-blue-100 bg-white p-7 shadow-soft">
<h3 class="font-display text-[1.25rem] font-black text-[#075BD6]"><?php echo esc_html( mmm_copy( 'about', $mmm_pillar['key'] . '_title', $mmm_pillar['title'] ) ); ?></h3>
<p class="mt-4 font-quote text-[1.18rem] sm:text-[1.28rem] leading-snug text-[#0A1020] italic"><?php echo esc_html( mmm_copy( 'about', $mmm_pillar['key'] . '_body', $mmm_pillar['body'] ) ); ?></p>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</section>

<section id="means" class="bg-white py-12 sm:py-14 lg:py-16 border-b border-slate-200/80">
<div class="shell-wide">
<div class="">
<div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7"><h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo esc_html( mmm_copy( 'about', 'means_heading', 'Our Means' ) ); ?></h2></div>
<div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5"><p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo esc_html( mmm_copy( 'about', 'means_lead', '– under the guidance of the Quran and Sunnah' ) ); ?></p></div>
</div>
</div>
<div class="mt-8 max-w-4xl">
<div data-visible="false" style="--reveal-delay:80ms" class="reveal  ">
<div class="rounded-2xl border border-blue-100 bg-white p-7 shadow-soft">
<h3 class="font-display text-[1.25rem] font-black text-[#075BD6]"><?php echo esc_html( mmm_copy( 'about', 'means_card_title', 'Means' ) ); ?></h3>
<div class="mt-4">
<?php foreach ( $mmm_means as $mmm_i => $mmm_mean ) : ?>
<p class="<?php echo esc_attr( $mmm_i > 0 ? 'mt-4 ' : '' ); ?>font-quote text-[1.18rem] sm:text-[1.28rem] leading-snug text-[#0A1020] italic"><?php echo esc_html( $mmm_mean ); ?></p>
<?php endforeach; ?>
</div>
</div>
</div>
</div>
</div>
</section>

<section class="bg-[#F0F5FA] py-12 sm:py-14 lg:py-16 border-b border-slate-200/80">
<div class="shell-wide">
<div class="">
<div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7"><h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo esc_html( mmm_copy( 'about', 'timeline_heading', 'How the work grew' ) ); ?></h2></div>
<div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5"><p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo esc_html( mmm_copy( 'about', 'timeline_lead', 'From one camp a month to sustained emergency response in two countries, without ever adding a layer of management.' ) ); ?></p></div>
</div>
</div>
<ol class="mt-8 border-l-2 border-slate-300 pl-6 sm:pl-10">
<?php foreach ( $mmm_timeline as $mmm_i => $mmm_step ) : ?>
<li data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 70 ); ?>ms" class="reveal  relative pb-8 last:pb-0">
<span class="absolute top-1.5 -left-[33px] h-3.5 w-3.5 rounded-full border-4 border-white bg-[#075BD6] sm:-left-[49px]" aria-hidden="true"></span>
<p class="font-display text-[1.45rem] leading-none font-black text-[#075BD6]"><?php echo esc_html( mmm_copy( 'about', 'timeline_' . ( $mmm_i + 1 ) . '_year', $mmm_step[0] ) ); ?></p>
<h3 class="mt-2 font-display text-[1.24rem] font-black text-[#0A1020]"><?php echo esc_html( mmm_copy( 'about', 'timeline_' . ( $mmm_i + 1 ) . '_title', $mmm_step[1] ) ); ?></h3>
<p class="mt-2 max-w-2xl text-[0.96rem] leading-relaxed text-slate-body"><?php echo esc_html( mmm_copy( 'about', 'timeline_' . ( $mmm_i + 1 ) . '_body', $mmm_step[2] ) ); ?></p>
</li>
<?php endforeach; ?>
</ol>
</div>
</section>

<section id="reach" class="bg-white py-12 sm:py-14 lg:py-16 border-b border-slate-200/80">
<div class="shell-wide">
<div class="">
<div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end">
<div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7"><h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo esc_html( mmm_copy( 'about', 'reach_heading', 'Where we work' ) ); ?></h2></div>
<div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5"><p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo esc_html( mmm_copy( 'about', 'reach_lead_regions', 'From remote districts to major cities, we serve communities in need all over Pakistan, and carry the same care to families affected by the crisis in Gaza.' ) ); ?></p></div>
</div>
</div>
<div class="mt-8 grid gap-5 md:grid-cols-2">
<?php foreach ( $mmm_reach as $mmm_i => $mmm_region ) : ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 80 ); ?>ms" class="reveal  ">
<div class="group flex h-full gap-6 rounded-2xl bg-white p-7 ring-1 ring-line transition-all duration-400 hover:-translate-y-1 hover:shadow-lift hover:ring-transparent">
<span class="mt-1.5 h-full w-1 shrink-0 rounded bg-gradient-to-b from-blue-brand to-magenta-brand" aria-hidden="true"></span>
<div>
<h3 class="font-display text-[1.25rem] font-extrabold text-navy-800"><?php echo esc_html( mmm_copy( 'about', 'reach_' . $mmm_region[0] . '_name', $mmm_region[1] ) ); ?></h3>
<p class="mt-2 text-[0.96rem] leading-relaxed text-slate-body"><?php echo esc_html( mmm_copy( 'about', 'reach_' . $mmm_region[0] . '_body', $mmm_region[2] ) ); ?></p>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<div data-visible="false" style="--reveal-delay:100ms" class="reveal  ">
<div class="mt-12 flex flex-wrap gap-3 sm:gap-4">
<a class="group inline-flex items-center justify-center gap-1 rounded-full px-1.5 py-2.5 xs:px-2 xs:py-3 sm:px-7 sm:py-3.5 min-h-[38px] max-w-full font-display text-[0.5625rem] xs:text-[0.6rem] sm:text-[0.95rem] font-bold tracking-tight transition-all duration-300 hover:-translate-y-0.5 active:scale-98 bg-[#046BD2] text-white hover:bg-[#e53935] hover:border-[#e53935] shadow-[0_10px_24px_-8px_rgba(4,107,210,0.4)] hover:shadow-[0_10px_24px_-8px_rgba(229,57,53,0.4)] flex-1 sm:flex-none" href="/get-involved/">
<span class="whitespace-nowrap"><?php echo esc_html( mmm_copy( 'about', 'cta_primary', 'Volunteer with us' ) ); ?></span>
<svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
</a>
<a class="group inline-flex items-center justify-center gap-1 rounded-full px-1.5 py-2.5 xs:px-2 xs:py-3 sm:px-7 sm:py-3.5 min-h-[38px] max-w-full font-display text-[0.5625rem] xs:text-[0.6rem] sm:text-[0.95rem] font-bold tracking-tight transition-all duration-300 hover:-translate-y-0.5 active:scale-98 border-2 border-slate-300 text-[#334155] hover:border-[#e53935] hover:bg-[#e53935] hover:text-white flex-1 sm:flex-none" href="/contact/">
<span class="whitespace-nowrap"><?php echo esc_html( mmm_copy( 'about', 'cta_secondary', 'Contact the team' ) ); ?></span>
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
