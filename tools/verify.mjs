/**
 * Compare the live WordPress site against the original static build.
 *
 * For every route in the original build this fetches the page from the
 * running site, extracts <main> from both documents and compares them on
 * three axes, which together capture everything a visitor can perceive:
 *
 *   1. the sequence of element tags
 *   2. the exact class string on every element
 *   3. the rendered text
 *
 * Whitespace is normalised (browsers collapse it), so indentation and
 * newlines never produce a false failure. Anything else does.
 *
 * Usage:
 *   node tools/verify.mjs                 # every route
 *   node tools/verify.mjs about donate    # only these routes
 *   node tools/verify.mjs --full          # also list attribute differences
 *
 * Exit code is 0 when every checked route matches, 1 otherwise.
 */

import fs from "node:fs";
import path from "node:path";

const SOURCE = process.env.MMM_SOURCE_ROOT || "E:/MMM02";
const BASE = process.env.MMM_BASE_URL || "http://localhost:8080";
const outDir = path.join(SOURCE, "out");

const args = process.argv.slice(2);
const full = args.includes("--full");
const only = args.filter((a) => !a.startsWith("--"));

/* ------------------------------------------------------------------ *
 * Routes
 * ------------------------------------------------------------------ */

/**
 * The routes the original build produced, as URL paths.
 *
 * Walked recursively: the build nests fifteen programme and report pages
 * under their section, and each is its own route served by a single
 * template, so skipping them would grade less than two thirds of the site.
 */
function routes() {
	const list = ["/"];

	const walk = (relDir) => {
		for (const entry of fs.readdirSync(path.join(outDir, relDir), { withFileTypes: true })) {
			if (!entry.isDirectory()) continue;
			const rel = relDir ? `${relDir}/${entry.name}` : entry.name;
			if (fs.existsSync(path.join(outDir, rel, "index.html"))) list.push(`/${rel}/`);
			walk(rel);
		}
	};
	walk("");

	return list.sort((a, b) => a.localeCompare(b));
}

/**
 * Which original build file backs a route.
 *
 * Media and what-we-do hold both an index and its children, which the
 * WordPress side resolves through the post types rather than a page.
 */
function originFile(route) {
	if (route === "/") return path.join(outDir, "index.html");
	const rel = route.replace(/^\/|\/$/g, "");
	const index = path.join(outDir, rel, "index.html");
	if (fs.existsSync(index)) return index;
	return null;
}

/* ------------------------------------------------------------------ *
 * HTML parsing
 * ------------------------------------------------------------------ */

const SELF_CLOSING = new Set([
	"img", "br", "hr", "input", "meta", "link", "source", "use", "path",
	"rect", "circle", "ellipse", "line", "polygon", "polyline", "stop", "col", "area", "base", "embed", "param", "track", "wbr",
]);

/**
 * Decode HTML entities so two spellings of the same character compare equal.
 *
 * The build escapes an apostrophe as &#x27; while PHP's esc_html() writes
 * &#039;. A browser renders both as ' — comparing the raw bytes would call
 * that a difference when nothing a visitor can see has changed. &amp; has to
 * be decoded last, or "&amp;#x27;" would become "'" and then be misread.
 */
const NAMED_ENTITIES = {
	amp: "&", lt: "<", gt: ">", quot: '"', apos: "'", nbsp: " ",
	lsquo: "‘", rsquo: "’", ldquo: "“", rdquo: "”",
	mdash: "—", ndash: "–", hellip: "…", copy: "©", reg: "®",
	euro: "€", pound: "£", deg: "°", middot: "·", bull: "•",
};

const decodeEntities = (text) =>
	text
		.replace(/&#x([0-9a-f]+);/gi, (_, hex) => String.fromCodePoint(parseInt(hex, 16)))
		.replace(/&#(\d+);/g, (_, dec) => String.fromCodePoint(Number(dec)))
		.replace(/&([a-zA-Z]+);/g, (m, name) => NAMED_ENTITIES[name.toLowerCase()] ?? m)
		.replace(/&amp;/g, "&");

/**
 * Index just past the element starting at `start`, counting nested uses of
 * the same tag so the whole subtree is skipped.
 */
function skipElement(html, start, name) {
	const open = new RegExp(`<${name}\\b`, "g");
	const close = new RegExp(`</${name}\\s*>`, "g");
	open.lastIndex = start;
	close.lastIndex = start;
	let depth = 1;
	let end = start;
	let o = open.exec(html);
	let c = close.exec(html);

	while (depth > 0) {
		if (!c) return html.length;

		if (o && o.index < c.index) {
			// An opening tag comes first, so count it. The closing tag has
			// not been consumed and has to stay available for the next round:
			// running both regexes side by side used to eat it here, which
			// made the depth drift downwards and the skip swallow everything
			// after the hidden element — including perfectly good markup.
			depth++;
			open.lastIndex = o.index + 1;
			o = open.exec(html);
			continue;
		}

		// A closing tag comes first, so count it. The opening tag is left in
		// `o`, still uncounted, and is compared again on the next round — the
		// two cursors are only advanced when their own tag is the one taken.
		depth--;
		// Past the whole `</name>` tag, not just past its `<`.
		end = c.index + c[0].length;
		close.lastIndex = end;
		c = close.exec(html);
	}

	return end;
}

/**
 * Turn markup into a flat token list so two documents can be lined up.
 *
 * Each tag becomes { kind: "tag", name, attrs } and each run of text
 * becomes { kind: "text", value }. Comments and React's hydration payload
 * are dropped — they render nothing.
 *
 * Subtrees marked `hidden` are dropped too: nothing inside them is
 * perceptible, and the port legitimately renders all 250 gallery photographs
 * with the ones past the fold hidden, where the build only rendered 24.
 * This stays a fair check because the two sides are treated alike — if the
 * port hid something the build displays, the build's copy survives and the
 * mismatch is still reported.
 */
function tokenize(html) {
	const tokens = [];
	const re = /<!--[\s\S]*?-->|<\/?([a-zA-Z][a-zA-Z0-9-]*)((?:"[^"]*"|'[^']*'|[^"'>])*)>/g;
	let last = 0;
	let m;

	const pushText = (raw) => {
		const value = decodeEntities(raw).replace(/\s+/g, " ").trim();
		if (value) tokens.push({ kind: "text", value });
	};

	while ((m = re.exec(html)) !== null) {
		pushText(html.slice(last, m.index));
		last = re.lastIndex;

		if (m[0].startsWith("<!--")) continue;

		const name = m[1].toLowerCase();
		if (name === "script" || name === "style") {
			// Skip the whole element, payload or not.
			const close = html.indexOf(`</${name}`, re.lastIndex);
			if (close === -1) { re.lastIndex = html.length; continue; }
			const gt = html.indexOf(">", close);
			re.lastIndex = gt === -1 ? html.length : gt + 1;
			continue;
		}

		const closing = m[0][1] === "/";
		const rawAttrs = m[2] || "";
		const selfClosing = m[0].endsWith("/>") || SELF_CLOSING.has(name);

		if (closing) {
			tokens.push({ kind: "close", name });
			continue;
		}

		const attrs = parseAttrs(rawAttrs);

		// Skip a hidden subtree whole, from just after its opening tag.
		// `last` must jump too, or the skipped markup would be re-emitted
		// as a text run on the next pass.
		if (!selfClosing && attrs.some((a) => a.name === "hidden")) {
			re.lastIndex = skipElement(html, re.lastIndex, name);
			last = re.lastIndex;
			continue;
		}

		tokens.push({ kind: "open", name, attrs });
		if (!selfClosing) tokens.push({ kind: "close", name });
	}

	pushText(html.slice(last));
	return tokens;
}

/** Attribute name -> normalised value, in source order. */
function parseAttrs(raw) {
	const attrs = [];
	const re = /([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*(?:=\s*("([^"]*)"|'([^']*)'|[^\s]+))?/g;
	let m;
	while ((m = re.exec(raw)) !== null) {
		const value = m[3] ?? m[4] ?? (m[2] !== undefined ? m[2] : "");
		attrs.push({ name: m[1].toLowerCase(), value: String(value).replace(/\s+/g, " ").trim() });
	}
	return attrs;
}

const attr = (token, name) => token.attrs?.find((a) => a.name === name)?.value ?? null;

/** The <main>…</main> payload of a document, or null if absent. */
function extractMain(html, opener = /<main\b[^>]*>/) {
	const start = html.search(opener);
	if (start === -1) return null;
	const end = html.indexOf("</main>", start);
	return html.slice(start, end === -1 ? html.length : end + 7);
}

/** Strip the React hydration payload, which carries no rendered markup. */
const stripPayload = (html) =>
	html.replace(/<script>self\.__next_f\.push[\s\S]*?<\/script>/g, "");

/* ------------------------------------------------------------------ *
 * Comparison
 * ------------------------------------------------------------------ */

/** Line the two token lists up and report the first divergences. */
function compare(original, mine) {
	const diffs = [];
	const max = Math.max(original.length, mine.length);

	for (let i = 0; i < max && diffs.length < 40; i++) {
		const a = original[i];
		const b = mine[i];

		if (!a) { diffs.push({ at: i, what: "extra in port", b }); continue; }
		if (!b) { diffs.push({ at: i, what: "missing in port", a }); continue; }

		if (a.kind !== b.kind) {
			diffs.push({ at: i, what: `token kind ${a.kind} vs ${b.kind}`, a, b });
			continue;
		}
		if (a.name !== b.name) {
			diffs.push({ at: i, what: `tag <${a.name}> vs <${b.name}>`, a, b });
			continue;
		}
		if (a.kind === "text") {
			if (a.value !== b.value) diffs.push({ at: i, what: "text", a, b });
			continue;
		}
		if (a.kind !== "open") continue;

		const ca = attr(a, "class");
		const cb = attr(b, "class");
		if (ca !== cb) diffs.push({ at: i, what: "class", a, b });

		if (full) {
			const names = new Set([...a.attrs.map((x) => x.name), ...b.attrs.map((x) => x.name)]);
			for (const n of names) {
				if (n === "class") continue;
				const va = attr(a, n);
				const vb = attr(b, n);
				if (va !== vb && !(va === null && vb === null)) {
					diffs.push({ at: i, what: `@${n}`, a, b });
				}
			}
		}
	}
	return diffs;
}

/** A one-line rendering of a token, for the report. */
function describe(t) {
	if (!t) return "(none)";
	if (t.kind === "text") return `text "${t.value.slice(0, 70)}${t.value.length > 70 ? "…" : ""}"`;
	if (t.kind === "close") return `</${t.name}>`;
	const c = attr(t, "class");
	return `<${t.name}${c ? ` class="${c.slice(0, 120)}${c.length > 120 ? "…" : ""}"` : ""}>`;
}

/** Where in the document a token sits, so a failure is findable. */
function context(tokens, index) {
	const parts = [];
	for (let i = Math.max(0, index - 12); i < index; i++) {
		if (tokens[i]?.kind === "text") parts.push(tokens[i].value.slice(0, 40));
		else if (tokens[i]?.kind === "open") parts.push(`<${tokens[i].name}>`);
	}
	return parts.join(" ").slice(-160);
}

/* ------------------------------------------------------------------ *
 * Run
 * ------------------------------------------------------------------ */

const wanted = only.length
	? routes().filter((r) => only.some((o) => r.includes(o)))
	: routes();

const results = [];

for (const route of wanted) {
	const file = originFile(route);
	if (!file) {
		results.push({ route, status: "SKIP", note: "no build file" });
		continue;
	}

	const originalHtml = stripPayload(fs.readFileSync(file, "utf8"));
	const originalMain = extractMain(originalHtml);
	if (!originalMain) {
		results.push({ route, status: "SKIP", note: "no <main> in build" });
		continue;
	}

	let live;
	try {
		// The 404 page is the one route whose live response is not a 200.
		const expected404 = route === "/404/";
		const res = await fetch(BASE + route, { redirect: "manual" });
		const body = await res.text();
		const statusOk = res.status === 200 || (expected404 && res.status === 404);
		if (!statusOk) {
			results.push({ route, status: "FAIL", note: `HTTP ${res.status}` });
			continue;
		}
		live = body;
	} catch (err) {
		results.push({ route, status: "FAIL", note: `fetch: ${err.message}` });
		continue;
	}

	const phpError = live.match(/(Fatal error|Parse error|Warning:|Notice:)[^<]*/);
	if (phpError) {
		results.push({ route, status: "FAIL", note: `PHP: ${phpError[0].slice(0, 160)}` });
		continue;
	}

	const liveMain = extractMain(live, /<main\b[^>]*>/);
	if (!liveMain) {
		results.push({ route, status: "FAIL", note: "port has no <main>" });
		continue;
	}

	const a = tokenize(originalMain);
	const b = tokenize(liveMain);
	const diffs = compare(a, b);

	results.push({
		route,
		status: diffs.length ? "FAIL" : "PASS",
		diffs,
		tokens: `${a.length} vs ${b.length}`,
	});
}

/* ---------------------------- report ---------------------------- */

const pad = (s, n) => String(s).padEnd(n);const width = Math.max(...results.map((r) => r.route.length), 8);

console.log(`Comparing ${results.length} route(s) against ${path.relative(process.cwd(), outDir)}\n`);
console.log(`  ${pad("ROUTE", width)}  ${pad("STATUS", 7)}  DETAIL`);
console.log(`  ${"-".repeat(width)}  ${"-".repeat(7)}  ${"-".repeat(50)}`);

for (const r of results) {
	const detail = r.status === "PASS"
		? `${r.tokens} tokens, identical`
		: (r.note || `${r.diffs.length} difference(s), ${r.tokens} tokens`);
	console.log(`  ${pad(r.route, width)}  ${pad(r.status, 7)}  ${detail}`);
}

const failed = results.filter((r) => r.status === "FAIL");

for (const r of failed) {
	if (!r.diffs?.length) continue;
	console.log(`\n${"=".repeat(78)}\n${r.route}\n${"=".repeat(78)}`);
	const originalTokens = tokenize(extractMain(stripPayload(fs.readFileSync(originFile(r.route), "utf8"))));
	let lastShown = -99;

	for (const d of r.diffs.slice(0, 12)) {
		if (d.at - lastShown > 1 && lastShown !== -99) {
			console.log(`   … ${d.at - lastShown - 1} matching token(s) skipped`);
		}
		lastShown = d.at;
		console.log(`  #${d.at}  ${d.what}`);
		if (d.what !== "extra in port" && d.what !== "missing in port") {
			console.log(`        build : ${describe(d.a)}`);
			console.log(`        port  : ${describe(d.b)}`);
		} else {
			console.log(`        ${describe(d.a ?? d.b)}`);
		}
		const before = context(originalTokens, d.at);
		if (before) console.log(`        before: …${before}`);
	}
	if (r.diffs.length > 12) console.log(`  … and ${r.diffs.length - 12} more`);
}

const passed = results.length - failed.length;
console.log(`\n${passed}/${results.length} route(s) identical`);
process.exit(failed.length ? 1 : 0);
