/**
 * Build theme/mmm/footer.php from the original static build.
 *
 * The footer is a single large block of markup. Rather than transcribe it by
 * hand (and risk changing the design), this reads the byte-exact HTML out of
 * the build and substitutes the organisation fields with PHP, so the values
 * are editable in wp-admin while the markup stays untouched.
 *
 * Run: node tools/extract-chrome.mjs
 */
import fs from "node:fs";
import path from "node:path";

const SOURCE = process.env.MMM_SOURCE_ROOT || "E:/MMM02";
const OUT = path.join(import.meta.dirname, "..", "theme", "mmm", "footer.php");

const html = fs.readFileSync(path.join(SOURCE, "out", "index.html"), "utf8");

const between = (startRe, endRe) => {
	const s = html.search(startRe);
	if (s < 0) throw new Error("start not found: " + startRe);
	const e = html.indexOf(endRe, s);
	if (e < 0) throw new Error("end not found: " + endRe);
	return html.slice(s, e + endRe.length);
};

const footer = between(/<footer class=/, "</footer>");

// Everything after the footer and before the Next.js runtime scripts: the
// WhatsApp float and the back-to-top control.
const afterFooter = html.slice(html.indexOf("</footer>") + "</footer>".length);
const floatEnd = afterFooter.indexOf('<script type="application/ld+json">');
const floats = floatEnd > 0 ? afterFooter.slice(0, floatEnd).trim() : "";

let out = footer;

/**
 * Replace a literal with a PHP expression, longest-first safe.
 */
const subs = [
	// Order matters: the legal name contains the short name.
	["Muslim Medical Mission Foundation", `<?php echo esc_html( mmm_org_value( 'legalName' ) ); ?>`],
	["Building Number 430, Phase 1", `<?php echo esc_html( mmm_org_value( 'address_line1' ) ); ?>`],
	["Johar Town, Lahore", `<?php echo esc_html( mmm_org_value( 'address_line2' ) ); ?>`],
	["Punjab, Pakistan", `<?php echo esc_html( mmm_org_value( 'address_country' ) ); ?>`],
	["tel:+923214244433", `<?php echo esc_attr( mmm_org_value( 'phoneHref' ) ); ?>`],
	["+92 321 424 4433", `<?php echo esc_html( mmm_org_value( 'phone' ) ); ?>`],
	["mailto:info@mmm.org.pk", `<?php echo esc_attr( 'mailto:' . mmm_org_value( 'email' ) ); ?>`],
	["info@mmm.org.pk", `<?php echo esc_html( mmm_org_value( 'email' ) ); ?>`],
	["United Bank Limited (UBL)", `<?php echo esc_html( mmm_bank( 'bank' ) ); ?>`],
	["PK50UNIL0109000338617189", `<?php echo esc_html( mmm_bank( 'iban' ) ); ?>`],
	["Wisdom, Action, Service for Allah for Right", `<?php echo esc_html( mmm_org_value( 'motto' ) ); ?>`],
	["https://facebook.com/MMMPakOfficial", `<?php echo esc_url( mmm_social_url( 'Facebook' ) ); ?>`],
	["https://instagram.com/MMMPakOfficial", `<?php echo esc_url( mmm_social_url( 'Instagram' ) ); ?>`],
	["https://x.com/MMMPakOfficial", `<?php echo esc_url( mmm_social_url( 'X' ) ); ?>`],
	["https://youtube.com/@mmmpakofficial", `<?php echo esc_url( mmm_social_url( 'YouTube' ) ); ?>`],
	["Muslim Medical Mission", `<?php echo esc_html( mmm_org_value( 'name' ) ); ?>`],
];

for (const [literal, php] of subs) {
	out = out.split(literal).join(php);
}

// Copyright line: the source renders "<!-- -->2026<!-- -->" as a year token.
out = out.replace(
	/(<!-- -->)(\d{4})(<!-- -->)/,
	`$1<?php echo esc_html( gmdate( 'Y' ) ); ?>$3`
);

// Social links are driven by the mmm_social option: wrap each anchor so the
// whole block can be re-rendered if an account is added later.
let floatsPhp = floats
	.split("https://wa.me/923214244433")
	.join(`<?php echo esc_url( mmm_org_value( 'whatsapp' ) ); ?>`);

const prologue = `<?php
/**
 * Document footer: closing </main>, the site footer, the floating controls
 * and the closing tags.
 *
 * Generated from the original build by tools/extract-chrome.mjs — the markup
 * is byte-identical apart from the organisation fields, which now read from
 * WordPress.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>
`;

const epilogue = `
<?php mmm_print_json_ld(); ?>
<?php wp_footer(); ?>
</body>
</html>
`;

const floatsBlock = floatsPhp
	? `\n${floatsPhp}\n`
	: "";

fs.writeFileSync(OUT, prologue + out + floatsBlock + epilogue, "utf8");
console.log("wrote", OUT, fs.statSync(OUT).size, "bytes");
