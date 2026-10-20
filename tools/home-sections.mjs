/**
 * List the home page's top-level <section> blocks.
 *
 * Pairs "<section" with "</section>" by counting, rather than parsing tags:
 * Tailwind arbitrary variants such as [&>*]: put a bare ">" inside a class
 * attribute, which desynchronises any naive tag scanner. The literal string
 * "</section" cannot occur inside an attribute, so counting it is safe.
 *
 * Run: node tools/home-sections.mjs
 */
import fs from "node:fs";

const raw = fs.readFileSync("E:/MMM02/out/index.html", "utf8")
	.replace(/<script>self\.__next_f\.push[\s\S]*?<\/script>/g, "");

const s = raw.indexOf("<main");
const e = raw.indexOf("</main>", s);
const inner = raw.slice(raw.indexOf(">", s) + 1, e);

const blocks = [];
let depth = 0;
let start = -1;
let from = 0;

for (;;) {
	const open = inner.indexOf("<section", from);
	const close = inner.indexOf("</section", from);
	if (open === -1 && close === -1) break;

	if (open !== -1 && (close === -1 || open < close)) {
		if (depth === 0) start = open;
		depth++;
		from = open + 9;
	} else {
		depth--;
		from = close + 10;
		if (depth === 0 && start !== -1) {
			blocks.push(inner.slice(start, from));
			start = -1;
		}
	}
}

const tag = (x) => (x.match(/^<([a-z0-9-]+)/) || [])[1] ?? "?";
const attr = (x, n) => (x.match(new RegExp(`${n}="([^"]*)"`)) || [])[1] ?? "";
const text = (x) =>
	x.replace(/<[^>]+>/g, " ").replace(/&#x27;/g, "'").replace(/&amp;/g, "&").replace(/\s+/g, " ").trim();

console.log(`<main> inner bytes: ${inner.length}\n`);
console.log(`top-level <section> blocks: ${blocks.length}\n`);

blocks.forEach((b, k) => {
	const open = b.slice(0, b.indexOf(">") + 1);
	const headings = [...b.matchAll(/<h([1-3])[^>]*>([\s\S]*?)<\/h\1>/g)]
		.map((m) => text(m[2]))
		.slice(0, 2);

	console.log(`[${k}] ${b.length} bytes   id="${attr(open, "id")}"`);
	console.log(`    class: ${attr(open, "class").slice(0, 120)}`);
	console.log(`    imgs:${(b.match(/<img/g) || []).length}  btns:${(b.match(/<button/g) || []).length}  links:${(b.match(/<a\s/g) || []).length}  reveals:${(b.match(/class="reveal/g) || []).length}`);
	if (headings.length) console.log(`    heading: ${headings.join(" / ").slice(0, 100)}`);
	console.log("");
});

// Anything in <main> that is not inside a section.
let outside = inner;
blocks.forEach((b) => { outside = outside.replace(b, ""); });
console.log(`bytes outside any <section>: ${outside.length}`);
console.log(`  headings: ${[...outside.matchAll(/<h([1-3])[^>]*>([\s\S]*?)<\/h\1>/g)].map((m) => text(m[2])).slice(0, 4).join(" / ").slice(0, 120)}`);
