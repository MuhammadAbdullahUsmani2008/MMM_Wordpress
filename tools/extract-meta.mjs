/**
 * Extract each route's document metadata (title, description, og:image)
 * from the original build into tools/meta.json, so head.php can reproduce
 * the exact head WordPress should emit.
 *
 * Run: node tools/extract-meta.mjs
 */
import fs from "node:fs";
import path from "node:path";

const SOURCE = process.env.MMM_SOURCE_ROOT || "E:/MMM02";
const OUT = path.join(import.meta.dirname, "meta.json");

const outDir = path.join(SOURCE, "out");

function walk(dir) {
	const entries = fs.readdirSync(dir, { withFileTypes: true });
	const files = [];
	for (const e of entries) {
		const abs = path.join(dir, e.name);
		if (e.isDirectory()) files.push(...walk(abs));
		else if (e.name === "index.html") files.push(abs);
	}
	return files;
}

const pick = (html, re, group = 1) => {
	const m = html.match(re);
	return m ? decodeEntities(m[group]) : "";
};

const decodeEntities = (s) =>
	s
		.replace(/&#x([0-9a-f]+);/gi, (_, h) => String.fromCharCode(parseInt(h, 16)))
		.replace(/&#(\d+);/g, (_, d) => String.fromCharCode(Number(d)))
		.replace(/&amp;/g, "&")
		.replace(/&quot;/g, '"')
		.replace(/&lt;/g, "<")
		.replace(/&gt;/g, ">")
		.replace(/&nbsp;/g, " ")
		.replace(/&rsquo;/g, "\u2019")
		.replace(/&lsquo;/g, "\u2018")
		.replace(/&ldquo;/g, "\u201c")
		.replace(/&rdquo;/g, "\u201d")
		.replace(/&mdash;/g, "\u2014")
		.replace(/&ndash;/g, "\u2013");

const meta = {};

for (const file of walk(outDir)) {
	const rel = path
		.relative(outDir, file)
		.replace(/\\/g, "/")
		.replace(/\/index\.html$/, "");
	const route = rel === "" ? "index" : rel;

	const html = fs.readFileSync(file, "utf8");
	const title = pick(html, /<title>([^<]*)<\/title>/);
	const description = pick(html, /name="description" content="([^"]*)"/);
	const ogimage = pick(html, /property="og:image" content="([^"]*)"/);

	meta[route] = {
		title: title.replace(/ \| Muslim Medical Mission$/, ""),
		description,
		ogimage: ogimage.replace(/^https:\/\/mmm\.org\.pk/, ""),
	};
}

fs.writeFileSync(OUT, JSON.stringify(meta, null, 2), "utf8");
console.log("extracted", Object.keys(meta).length, "routes ->", OUT);
