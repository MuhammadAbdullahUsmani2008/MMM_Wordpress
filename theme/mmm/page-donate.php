<?php
/**
 * The Donate page — route /donate/.
 *
 * Markup only: the donation planner's cause and amount arithmetic, the
 * WhatsApp hand-off and the copy-to-clipboard bank rows all live in
 * assets/js/mmm.js, which finds this markup through the data-* attributes
 * emitted below. Nothing is ever posted to the server, so the controls carry
 * no name attributes.
 *
 * Ground truth: E:\MMM02\out\donate\index.html
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
mmm_hero_preload( '/media/field/free-medical-camp-medicines.jpg' );

get_header();

$mmm_whatsapp = mmm_org_value( 'whatsapp' );
$mmm_amount   = 9000;

/*
 * Planner causes. The unit economics are part of the widget's own markup
 * (the behaviour layer reads them from data-unit-cost / data-unit-name);
 * the wording is editable copy.
 */
$mmm_causes = array(
	array(
		'key'         => 'camp',
		'label'       => 'Medical camps and clinics',
		'unit_cost'   => 600,
		'unit_name'   => 'patient seen and medicated',
		'unit_plural' => 'patients seen and medicated',
		'note'        => 'Consultation, diagnosis and a full course of medicine at a rural camp.',
	),
	array(
		'key'         => 'ration',
		'label'       => 'Food parcels',
		'unit_cost'   => 12000,
		'unit_name'   => 'family fed for a month',
		'unit_plural' => 'families fed for a month',
		'note'        => 'A month of staple food for a displaced household, built around what can be cooked in a shelter.',
	),
	array(
		'key'         => 'winter',
		'label'       => 'Winter packages',
		'unit_cost'   => 9000,
		'unit_name'   => 'household kept warm',
		'unit_plural' => 'households kept warm',
		'note'        => 'A heavy quilt, blankets and warm clothing sized for the children in the family.',
	),
	array(
		'key'         => 'water',
		'label'       => 'Water for Life',
		'unit_cost'   => 45000,
		'unit_name'   => 'tanker delivered',
		'unit_plural' => 'tankers delivered',
		'note'        => 'Drinking water trucked into a Gaza displacement camp with no functioning mains.',
	),
);

// Amount buttons under "Choose an amount".
$mmm_presets = array( 3000, 9000, 25000, 50000 );

// The card renders the first cause with the default amount, as the build did.
$mmm_active_cause  = $mmm_causes[0];
$mmm_active_label  = mmm_copy( 'donate', 'cause_camp_label', $mmm_active_cause['label'] );
$mmm_active_unit   = mmm_copy( 'donate', 'cause_camp_unit', $mmm_active_cause['unit_name'] );
$mmm_active_plural = mmm_copy( 'donate', 'cause_camp_plural', $mmm_active_cause['unit_plural'] );
$mmm_active_note   = mmm_copy( 'donate', 'cause_camp_note', $mmm_active_cause['note'] );
$mmm_units         = (int) floor( $mmm_amount / $mmm_active_cause['unit_cost'] );
$mmm_active_heading = 'PKR ' . number_format( $mmm_amount ) . ' towards ' . strtolower( $mmm_active_label );
$mmm_planner_message = rawurlencode(
	'Assalamu alaikum. I would like to donate PKR ' . number_format( $mmm_amount )
	. ' towards ' . $mmm_active_label . '. Please confirm the transfer details.'
);

// Copy rows above the bank details list.
$mmm_planner_rows = array(
	array(
		'key'   => 'row_account_title',
		'label' => 'Account title',
		'value' => mmm_bank( 'title' ),
	),
	array(
		'key'   => 'row_iban',
		'label' => 'IBAN',
		'value' => mmm_bank( 'iban' ),
	),
	array(
		'key'   => 'row_account',
		'label' => 'Account number',
		'value' => mmm_bank( 'account' ),
	),
);

// Allocation percentages (shared with the Zakat/about pages).
$mmm_allocation = mmm_allocation();

// Zakat-eligible programme list.
$mmm_zakat = array(
	'food'    => 'Food parcels',
	'winter'  => 'Winter packages',
	'water'   => 'Water for Life',
	'medical' => 'Medical treatment',
);

// "Ways to give" cards; the footer line is organisation data.
$mmm_ways = array(
	array(
		'key'    => 'bank',
		'title'  => 'Bank transfer',
		'body'   => 'The lowest processing cost, so the largest share reaches the district. Details are on this page and can be copied in one tap.',
		'footer' => mmm_bank( 'bank' ),
	),
	array(
		'key'    => 'wallets',
		'title'  => 'JazzCash and EasyPaisa',
		'body'   => 'Send from a mobile wallet in seconds. Useful for smaller regular gifts and for Zakat paid in instalments.',
		'footer' => mmm_org_value( 'mobileWallets' ),
	),
	array(
		'key'    => 'cash',
		'title'  => 'Cash or cheque',
		'body'   => 'Message the team on WhatsApp and a volunteer will arrange collection or confirm where to deliver it.',
		'footer' => mmm_org_value( 'phone' ),
	),
);

// Bank details / contact list at the foot of the page.
$mmm_details = array(
	array(
		'key'   => 'account_title',
		'label' => 'Account title',
		'value' => mmm_bank( 'title' ),
	),
	array(
		'key'   => 'bank',
		'label' => 'Bank',
		'value' => mmm_bank( 'bank' ),
	),
	array(
		'key'   => 'account',
		'label' => 'Account number',
		'value' => mmm_bank( 'account' ),
	),
	array(
		'key'   => 'iban',
		'label' => 'IBAN',
		'value' => mmm_bank( 'iban' ),
	),
	array(
		'key'   => 'branch',
		'label' => 'Branch',
		'value' => mmm_bank( 'branch' ),
	),
	array(
		'key'   => 'wallets',
		'label' => 'Mobile wallets',
		'value' => mmm_org_value( 'mobileWallets' ),
	),
	array(
		'key'   => 'phone',
		'label' => 'Phone',
		'value' => mmm_org_value( 'phone' ),
	),
	array(
		'key'   => 'email',
		'label' => 'Email',
		'value' => mmm_org_value( 'email' ),
	),
);
?>

<header class="relative isolate overflow-hidden border-b border-slate-200/80 bg-gradient-to-b from-[#F0F6FC] via-[#F8FAFC] to-white pt-32 pb-16 md:pt-36 md:pb-20"><div class="absolute inset-0 -z-10"><img src="/media/field/free-medical-camp-medicines.jpg" alt="<?php echo mmm_attr( mmm_copy( 'donate', 'hero_image_alt', 'Give to a specific thing, and we will tell you what it did' ) ); ?>" class="h-full w-full object-cover opacity-20"/></div><div class="shell"><nav aria-label="Breadcrumb"><ol class="flex flex-wrap items-center gap-2 text-[0.88rem] font-semibold text-[#6B7280]"><li class="flex items-center gap-2"><a class="transition-colors hover:text-[#075BD6]" href="/"><?php echo mmm_text( mmm_copy( 'donate', 'crumb_home', 'Home' ) ); ?></a></li><li class="flex items-center gap-2"><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 h-3 w-3 text-[#1F2937]" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg><a class="transition-colors hover:text-[#075BD6]" href="/donate/"><?php echo mmm_text( mmm_copy( 'donate', 'crumb_current', 'Donate' ) ); ?></a></li></ol></nav><h1 class="mt-5 max-w-3xl font-display text-[clamp(2.15rem,3.8vw,3.15rem)] leading-[1.08] font-black text-[#0A1020] tracking-tight"><?php echo mmm_text( mmm_copy( 'donate', 'hero_title', 'Give to a specific thing, and we will tell you what it did' ) ); ?></h1><p class="mt-4 max-w-2xl text-[1.05rem] leading-relaxed text-[#4B5563] sm:text-[1.14rem]"><?php echo mmm_text( mmm_copy( 'donate', 'hero_lead', 'Every programme has a real unit cost. Choose one, choose an amount, and see exactly what it buys before you send anything.' ) ); ?></p></div></header>
<section class="py-20 lg:py-24"><div class="shell-wide"><div class="grid gap-6 lg:grid-cols-12" data-donation-planner data-whatsapp-base="<?php echo mmm_attr( $mmm_whatsapp ); ?>"><div class="rounded-2xl bg-white p-7 ring-1 ring-line lg:col-span-7 lg:p-9"><fieldset><legend class="font-display text-[1.15rem] font-extrabold text-navy-800"><?php echo mmm_text( mmm_copy( 'donate', 'planner_legend_where', 'Choose where it goes' ) ); ?></legend><div class="mt-4 grid gap-2.5 sm:grid-cols-2"><?php foreach ( $mmm_causes as $mmm_cause ) : ?>
<?php
$mmm_label   = mmm_copy( 'donate', 'cause_' . $mmm_cause['key'] . '_label', $mmm_cause['label'] );
$mmm_unit    = mmm_copy( 'donate', 'cause_' . $mmm_cause['key'] . '_unit', $mmm_cause['unit_name'] );
$mmm_plural  = mmm_copy( 'donate', 'cause_' . $mmm_cause['key'] . '_plural', $mmm_cause['unit_plural'] );
$mmm_note    = mmm_copy( 'donate', 'cause_' . $mmm_cause['key'] . '_note', $mmm_cause['note'] );
$mmm_on      = 'camp' === $mmm_cause['key'];
?>
<button type="button" aria-pressed="<?php echo $mmm_on ? 'true' : 'false'; ?>" class="rounded-xl border-2 p-4 text-left transition-all duration-300<?php echo $mmm_on ? ' border-blue-brand bg-blue-soft' : ' border-line hover:border-navy-300'; ?>" data-cause="<?php echo mmm_attr( $mmm_cause['key'] ); ?>" data-label="<?php echo mmm_attr( $mmm_label ); ?>" data-unit-cost="<?php echo (int) $mmm_cause['unit_cost']; ?>" data-unit-name="<?php echo mmm_attr( $mmm_unit ); ?>" data-unit-plural="<?php echo mmm_attr( $mmm_plural ); ?>" data-note="<?php echo mmm_attr( $mmm_note ); ?>"><span class="block font-display text-[0.98rem] font-bold text-navy-800"><?php echo mmm_text( $mmm_label ); ?></span><span class="mt-0.5 block text-[0.82rem] text-slate-muted">PKR <?php echo number_format( (int) $mmm_cause['unit_cost'] ); ?><!-- --> per <!-- --><?php echo mmm_text( $mmm_unit ); ?></span></button><?php endforeach; ?>
</div></fieldset><fieldset class="mt-8"><legend class="font-display text-[1.15rem] font-extrabold text-navy-800"><?php echo mmm_text( mmm_copy( 'donate', 'planner_legend_amount', 'Choose an amount' ) ); ?></legend><div class="mt-4 flex flex-wrap gap-2.5"><?php foreach ( $mmm_presets as $mmm_preset ) : $mmm_preset_on = 9000 === $mmm_preset; ?><button type="button" aria-pressed="<?php echo $mmm_preset_on ? 'true' : 'false'; ?>" class="inline-flex items-center justify-center rounded-full px-4.5 py-2.5 xs:px-6 xs:py-3 font-display text-[0.85rem] xs:text-[0.95rem] font-bold whitespace-nowrap min-h-[44px] transition-all duration-250 <?php echo $mmm_preset_on ? 'bg-[#046BD2] text-white shadow-sm hover:bg-[#e53935]' : 'bg-slate-100 text-[#334155] hover:bg-[#e53935] hover:text-white'; ?>" data-preset-amount="<?php echo (int) $mmm_preset; ?>"><span>PKR <?php echo number_format( (int) $mmm_preset ); ?></span></button><?php endforeach; ?></div><label class="mt-4 block"><span class="sr-only">Custom amount in Pakistani rupees</span><div class="flex items-center gap-3 rounded-xl border-2 border-line px-4 py-3 transition-colors focus-within:border-blue-brand"><span class="font-display text-[0.9rem] font-bold text-slate-muted">PKR</span><input type="number" min="100" step="100" inputMode="numeric" placeholder="<?php echo mmm_attr( mmm_copy( 'donate', 'custom_amount_placeholder', 'Another amount' ) ); ?>" class="w-full bg-transparent font-display text-[1.05rem] font-bold text-navy-800 outline-none" data-custom-amount value=""/></div></label></fieldset></div><div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-[#F0F7FF] to-white p-7 shadow-soft lg:col-span-5 lg:p-9"><p class="font-display text-[0.98rem] font-bold text-[#046BD2]" data-out-heading><?php echo mmm_text( 'PKR ' . number_format( $mmm_amount ) ); ?><!-- --> towards <!-- --><?php echo mmm_text( strtolower( $mmm_active_label ) ); ?></p><p class="mt-6 font-display text-[3.4rem] leading-none font-extrabold text-[#003475]" data-out-units><?php echo number_format( $mmm_units ); ?></p><p class="mt-2 font-display text-[1.1rem] font-bold text-[#003475]" data-out-unit-label><?php echo mmm_text( 1 === $mmm_units ? $mmm_active_unit : $mmm_active_plural ); ?></p><p data-out-note class="mt-4 text-[0.93rem] leading-relaxed text-[#334155]"><?php echo mmm_text( mmm_copy( 'donate', 'active_note', 'Consultation, diagnosis and a full course of medicine at a rural camp.' ) ); ?></p><a data-out-cta href="<?php echo esc_url( $mmm_whatsapp . '?text=' . $mmm_planner_message ); ?>" target="_blank" rel="noreferrer" class="group mt-8 inline-flex w-full items-center justify-center gap-2 xs:gap-2.5 rounded-full bg-[#046BD2] px-5 py-3 xs:px-7 xs:py-4 font-display text-[0.88rem] xs:text-base font-bold text-white whitespace-nowrap min-h-[44px] shadow-[0_8px_20px_rgba(4,107,210,0.3)] transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#003475]"><span><?php echo mmm_text( mmm_copy( 'donate', 'planner_cta', 'Arrange this donation' ) ); ?></span><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg></a><div class="mt-7 space-y-3 border-t border-slate-200 pt-6"><?php foreach ( $mmm_planner_rows as $mmm_row ) : ?>
<div class="flex items-start justify-between gap-4"><div class="min-w-0"><p class="text-[0.85rem] text-slate-500"><?php echo mmm_text( mmm_copy( 'donate', $mmm_row['key'] . '_label', $mmm_row['label'] ) ); ?></p><p class="mt-0.5 text-[0.88rem] font-semibold break-all text-[#0A1020]"><?php echo mmm_text( $mmm_row['value'] ); ?></p></div><button type="button" data-copy-value="<?php echo mmm_attr( $mmm_row['value'] ); ?>" class="inline-flex shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white px-3.5 py-1.5 font-display text-[0.75rem] font-bold text-[#334155] whitespace-nowrap min-h-[38px] transition-all duration-250 hover:bg-[#e53935] hover:border-[#e53935] hover:text-white"><span>Copy</span></button></div><?php endforeach; ?>
</div></div></div></div></section>
<section class="pb-20 lg:pb-24"><div class="shell-wide"><div data-visible="false" style="--reveal-delay:0ms" class="reveal  "><div class="grid gap-8 rounded-2xl bg-surface p-8 lg:grid-cols-12 lg:items-center lg:p-10"><div class="lg:col-span-7"><h2 class="font-display text-[1.85rem] sm:text-[2.15rem] font-black text-[#0A1020]"><?php echo mmm_text( mmm_copy( 'donate', 'zakat_heading', 'Zakat eligible programmes' ) ); ?></h2><p class="mt-3 text-[1.04rem] leading-relaxed text-[#4B5563]"><?php echo mmm_text( mmm_copy( 'donate', 'zakat_body', 'Food parcels, winter packages, clean water and free medical treatment for the poor are all applied to households that meet the criteria for Zakat. Tell us when you send it and your gift is kept in a separate pool and distributed accordingly.' ) ); ?></p><div class="mt-6"><a class="group inline-flex items-center justify-center gap-1 rounded-full px-1.5 py-2.5 xs:px-2 xs:py-3 sm:px-7 sm:py-3.5 min-h-[38px] max-w-full font-display text-[0.5625rem] xs:text-[0.6rem] sm:text-[0.95rem] font-bold tracking-tight transition-all duration-300 hover:-translate-y-0.5 active:scale-98 bg-[#046BD2] text-white hover:bg-[#e53935] hover:border-[#e53935] shadow-[0_10px_24px_-8px_rgba(4,107,210,0.4)] hover:shadow-[0_10px_24px_-8px_rgba(229,57,53,0.4)] " href="/zakat-calculator/"><span class="whitespace-nowrap"><?php echo mmm_text( mmm_copy( 'donate', 'zakat_cta', 'Zakat Calculator' ) ); ?></span><svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 shrink-0 shrink-0 transition-transform duration-300 group-hover:translate-x-1" fill="none"><path d="M4 12h15M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg></a></div></div><ul class="grid gap-3 sm:grid-cols-2 lg:col-span-5"><?php foreach ( $mmm_zakat as $mmm_key => $mmm_item ) : ?>
<li class="flex items-center gap-3 rounded-xl bg-white px-4.5 py-3.5 font-display text-[0.98rem] font-bold text-[#0A1020] shadow-xs"><span class="h-2 w-2 shrink-0 rounded-full bg-[#EF3B19]" aria-hidden="true"></span><?php echo mmm_text( mmm_copy( 'donate', 'zakat_' . $mmm_key, $mmm_item ) ); ?></li><?php endforeach; ?>
</ul></div></div></div></section>
<section id="allocation" class="border-y border-slate-200/80 bg-gradient-to-b from-[#F0F7FF] via-[#F8FAFC] to-white py-24 lg:py-28"><div class="shell-wide"><div class=""><div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end"><div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7"><h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo mmm_text( mmm_copy( 'donate', 'alloc_heading', 'Where the money goes' ) ); ?></h2></div><div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5"><p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo mmm_text( mmm_copy( 'donate', 'alloc_intro', 'The organisation runs on volunteer clinical time and has no paid fundraising arm, which is the only reason these proportions are possible.' ) ); ?></p></div></div></div><div class="mt-14 grid gap-10 lg:grid-cols-12"><div class="lg:col-span-7"><ul class="space-y-7"><?php foreach ( $mmm_allocation as $mmm_i => $mmm_row ) : ?>
<li data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 90 ); ?>ms" class="reveal  "><div class="flex items-baseline justify-between gap-4"><span class="font-display text-[1.08rem] font-black text-[#0A1020]"><?php echo mmm_text( mmm_copy( 'donate', 'alloc_' . $mmm_i . '_label', $mmm_row['label'] ) ); ?></span><span class="font-display text-[1.6rem] font-black text-[#075BD6]"><?php echo (int) $mmm_row['value']; ?><!-- -->%</span></div><div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-gradient-to-r from-[#075BD6] to-[#008DC9] transition-[width] duration-1000 ease-out" style="width:<?php echo (int) $mmm_row['value']; ?>%"></div></div></li><?php endforeach; ?>
</ul></div><div data-visible="false" style="--reveal-delay:100ms" class="reveal  lg:col-span-4 lg:col-start-9"><div class="rounded-2xl border border-blue-100 bg-white p-7 sm:p-8 shadow-soft"><h3 class="font-display text-[1.32rem] font-black text-[#0A1020]"><?php echo mmm_text( mmm_copy( 'donate', 'ask_heading', 'Ask us for the count' ) ); ?></h3><p class="mt-3 text-[0.98rem] leading-relaxed text-[#4B5563]"><?php echo mmm_text( mmm_copy( 'donate', 'ask_body', 'Send your transfer receipt on WhatsApp and a volunteer will tell you which deployment your gift went into and how many people it reached.' ) ); ?></p><a href="<?php echo esc_url( $mmm_whatsapp ); ?>" target="_blank" rel="noreferrer" class="mt-6 inline-flex items-center justify-center rounded-full bg-[#075BD6] px-5 py-2.5 xs:px-6 xs:py-3 font-display text-[0.84rem] xs:text-[0.95rem] font-bold text-white whitespace-nowrap min-h-[44px] shadow-sm transition-all duration-250 hover:-translate-y-0.5 hover:bg-[#e53935]"><span><?php echo mmm_text( mmm_copy( 'donate', 'ask_cta', 'Message the team' ) ); ?></span></a></div></div></div></div></section>
<section class="py-24 lg:py-28"><div class="shell-wide"><div class=""><div class="grid gap-4 md:grid-cols-12 md:gap-8 md:items-end"><div data-visible="false" style="--reveal-delay:0ms" class="reveal  md:col-span-6 lg:col-span-7"><h2 class="font-display text-[clamp(1.95rem,3.4vw,2.75rem)] leading-[1.1] font-black text-[#0A1020]"><?php echo mmm_text( mmm_copy( 'donate', 'ways_heading', 'Ways to give' ) ); ?></h2></div><div data-visible="false" style="--reveal-delay:90ms" class="reveal  md:col-span-6 lg:col-span-5"><p class="text-[1.04rem] leading-relaxed sm:text-[1.12rem] text-[#4B5563]"><?php echo mmm_text( mmm_copy( 'donate', 'ways_intro', 'All of them reach the same account and the same field teams.' ) ); ?></p></div></div></div><div class="mt-14 grid gap-6 md:grid-cols-3"><?php foreach ( $mmm_ways as $mmm_i => $mmm_way ) : ?>
<div data-visible="false" style="--reveal-delay:<?php echo (int) ( $mmm_i * 80 ); ?>ms" class="reveal  "><div class="flex h-full flex-col rounded-2xl bg-white p-7 ring-1 ring-line transition-all duration-400 hover:-translate-y-1 hover:shadow-lift hover:ring-transparent"><h3 class="font-display text-[1.32rem] font-black text-[#0A1020]"><?php echo mmm_text( mmm_copy( 'donate', 'way_' . $mmm_way['key'] . '_title', $mmm_way['title'] ) ); ?></h3><p class="mt-3 flex-1 text-[0.98rem] leading-relaxed text-[#4B5563]"><?php echo mmm_text( mmm_copy( 'donate', 'way_' . $mmm_way['key'] . '_body', $mmm_way['body'] ) ); ?></p><p class="mt-5 border-t border-line pt-4 font-display text-[0.95rem] font-bold text-[#075BD6]"><?php echo mmm_text( $mmm_way['footer'] ); ?></p></div></div><?php endforeach; ?>
</div><div data-visible="false" style="--reveal-delay:100ms" class="reveal  "><dl class="mt-10 grid gap-x-8 gap-y-6 rounded-2xl bg-surface p-8 sm:grid-cols-2 lg:grid-cols-4"><?php foreach ( $mmm_details as $mmm_row ) : ?>
<div><dt class="text-[0.88rem] text-slate-muted"><?php echo mmm_text( mmm_copy( 'donate', $mmm_row['key'] . '_label', $mmm_row['label'] ) ); ?></dt><dd class="mt-1 text-[0.92rem] font-semibold break-all text-navy-800"><?php echo mmm_text( $mmm_row['value'] ); ?></dd></div><?php endforeach; ?>
</dl></div></div></section><!--$--><!--/$-->
<?php
get_footer();
