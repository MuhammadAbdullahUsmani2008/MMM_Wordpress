/**
 * Dump one top-level <section> of the home page, or all of them.
 *
 * Run: node tools/extract-home-section.mjs [index]
 */
import fs from "node:fs";

const raw = fs.readFileSync("E:/MMM02/out/index.html", "utf8")
	.replace(/<script>self\.__next_f\.push[\s\S]*?<\/script>/g, "");

const s = raw.indexOf("<main");
const e = raw.indexOf("</main>", s);
const inner = raw.slice(raw.indexOf(">", s) + 1, e);

const blocks = [];
let depth = 0, start = -1, from = 0;
for (;;) {
	const open = inner.indexOf("<section", from);
	const close = inner.indexOf("</section", from);
	if (open === -1 && close === -1) break;
	if (open !== -1 && (close === -1 || open < close)) {
		if (depth === 0) start = open;
		depth++; from = open + 9;
	} else {
		depth--; from = close + 10;
		if (depth === 0 && start !== -1) { blocks.push(inner.slice(start, from)); start = -1; }
	}
}

const only = process.argv[2];
if (only !== undefined) {
	const i = Number(only);
	if (!Number.isInteger(i) || !blocks[i]) {
		console.error("no such block: " + only + " (have 0.." + (blocks.length - 1) + ")");
		process.exit(1);
	}
	process.stdout.write(blocks[i]);
} else {
	console.log("blocks: " + blocks.length + ", inner bytes: " + inner.length);
	blocks.forEach((b, k) => console.log("  [" + k + "] " + b.length + " bytes"));
}