/**
 * Compare the live site's <head> against the original build.
 *
 * `tools/meta.json` records the title, description and og:image the original
 * build served on every route. This fetches each route from the running site
 * and checks the same three fields, so a page whose body is byte-identical
 * but whose browser tab reads wrongly is still caught.
 *
 * The metadata keys are file paths from the build (`index.html`, `about`,
 * `media/one-million-patients`); they are mapped back to URL routes here.
 *
 * Usage:
 *   node tools/verify-head.mjs                 # every route
 *   node tools/verify-head.mjs about donate     # only these
 *
 * Exit code is 0 when every checked route matches, 1 otherwise.
 */
import fs from "node:fs";
import path from "node:path";

const BASE = process.env.MMM_BASE_URL || "http://localhost:8080";
const meta = JSON.parse(fs.readFileSync(path.join(import.meta.dirname, "meta.json"), "utf8"));

const args = process.argv.slice(2);

/** File path in the build -> URL route on the site. */
const toRoute = (file) => {
	if (file === "index.html") return "/";
	return `/${file}/`;
};

/**
 * Read one tag's text and decode its entities.
 *
 * tools/meta.json holds already-decoded text while the live HTML is raw
 * markup, so PHP's esc_attr() spelling of an apostrophe (&#039;) would look
 * like a difference from the build's plain '. Decoding both sides compares
 * what a browser actually displays.
 */
const decodeEntities = (s) =>
	s
		.replace(/&#x([0-9a-f]+);/gi, (_, h) => String.fromCodePoint(parseInt(h, 16)))
		.replace(/&#(\d+);/g, (_, d) => String.fromCodePoint(Number(d)))
		.replace(/&quot;/g, '"')
		.replace(/&lt;/g, "<")
		.replace(/&gt;/g, ">")
		.replace(/&nbsp;/g, " ")
		.replace(/&rsquo;/g, "\u2019")
		.replace(/&lsquo;/g, "\u2018")
		.replace(/&ldquo;/g, "\u201c")
		.replace(/&rdquo;/g, "\u201d")
		.replace(/&mdash;/g, "\u2014")
		.replace(/&ndash;/g, "\u2013")
		.replace(/&amp;/g, "&");

const pick = (html, re) => {
	const m = html.match(re);
	return m ? decodeEntities(m[1].replace(/\s+/g, " ").trim()) : null;
};

/**
 * The document title, with the site-name suffix removed.
 *
 * Every route except the home and 404 pages renders "{Document title} |
 * Muslim Medical Mission" — head.php applies that template itself, and
 * tools/meta.json stores the document part. Comparing the parts is what
 * catches a wrong page name; the suffix is asserted separately, because
 * comparing only the parts would let a missing suffix pass unnoticed.
 */
const SITE_SUFFIX = " | Muslim Medical Mission";
const docTitle = (s) => String(s ?? "").replace(/\s+/g, " ").trim().replace(/\s*\|\s*Muslim Medical Mission$/, "");

/** Routes whose expected title is the site title, so has no suffix. */
const usesSiteTitle = (want) => String(want.title ?? "").startsWith("Muslim Medical Mission |");

const headOf = (html) => {
	const start = html.search(/<head\b[^>]*>/i);
	const end = html.search(/<\/head>/i);
	return start === -1 || end === -1 ? html : html.slice(start, end);
};

/** Absolute URLs differ by host; the path and query are what must match. */
const pathOf = (v) => String(v ?? "").replace(/^https?:\/\/[^/]+/, "");

const wanted = Object.keys(meta)
	.map((file) => ({ file, route: toRoute(file) }))
	.filter((r) => !args.length || args.some((a) => r.route.includes(a)))
	.sort((a, b) => a.route.localeCompare(b.route));

const results = [];

for (const { file, route } of wanted) {
	let live;
	try {
		const res = await fetch(BASE + route, { redirect: "manual" });
		live = await res.text();
		const okStatus = res.status === 200 || (route === "/404/" && res.status === 404);
		if (!okStatus) {
			results.push({ route, problems: [`HTTP ${res.status}`] });
			continue;
		}
	} catch (err) {
		results.push({ route, problems: [`fetch: ${err.message}`] });
		continue;
	}

	const want = meta[file];
	const h = headOf(live);
	const got = {
		title: pick(h, /<title[^>]*>([\s\S]*?)<\/title>/i),
		description: pick(h, /<meta\s+name="description"\s+content="([^"]*)"/i),
		ogimage: pick(h, /<meta\s+property="og:image"\s+content="([^"]*)"/i),
	};

	const problems = [];

	// Title: the document part must match, and the site-name suffix must be
	// present exactly where the original put it.
	if (docTitle(want.title) !== docTitle(got.title)) problems.push("title");
	const wantsSuffix = !usesSiteTitle(want);
	const hasSuffix = String(got.title ?? "").endsWith(SITE_SUFFIX);
	if (wantsSuffix !== hasSuffix) {
		problems.push(wantsSuffix ? "title-suffix (missing)" : "title-suffix (unexpected)");
	}

	for (const key of ["description", "ogimage"]) {
		const a = key === "ogimage" ? pathOf(want[key]) : String(want[key] ?? "");
		const b = key === "ogimage" ? pathOf(got[key]) : String(got[key] ?? "");
		if (a !== b) problems.push(key);
	}
	results.push({ route, problems, got, want });
}

const pad = (s, n) => String(s).padEnd(n);
const width = Math.max(...results.map((r) => r.route.length), 8);

console.log(`Checking <head> on ${results.length} route(s)\n`);
console.log(`  ${pad("ROUTE", width)}  STATUS  FIELDS`);
console.log(`  ${"-".repeat(width)}  ------  ------`);

const failed = [];
for (const r of results) {
	const ok = r.problems.length === 0;
	if (!ok) failed.push(r);
	console.log(
		`  ${pad(r.route, width)}  ${ok ? "PASS" : "FAIL"}   ${ok ? "-" : r.problems.join(", ")}`,
	);
}

for (const r of failed) {
	if (!r.want) continue;
	console.log(`\n${"=".repeat(78)}\n${r.route}\n${"=".repeat(78)}`);
	for (const key of r.problems) {
		if (key.startsWith("title")) {
			console.log(`  ${key}`);
			console.log(`    build : ${JSON.stringify(r.want.title)}`);
			console.log(`    port  : ${JSON.stringify(r.got?.title)}`);
			continue;
		}
		console.log(`  ${key}`);
		console.log(`    build : ${JSON.stringify(r.want[key])}`);
		console.log(`    port  : ${JSON.stringify(r.got?.[key])}`);
	}
}

const passed = results.length - failed.length;
console.log(`\n${passed}/${results.length} route(s) match`);
process.exit(failed.length ? 1 : 0);
