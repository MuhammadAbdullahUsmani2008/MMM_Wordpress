/**
 * Print the VISIBLE tag sequence of a document's <main>, collapsing any
 * subtree that carries a "hidden" attribute into a single marker. Used to see
 * exactly where two renderings' visible structure diverges.
 *
 * Usage: node tools/visible-seq.mjs <file> [startMarker]
 */
import fs from "node:fs";

const file = process.argv[2];
const from = process.argv[3] || "Fetching live rates";

let html = fs.readFileSync(file, "utf8")
	.replace(/<script>self\.__next_f\.push[\s\S]*?<\/script>/g, "");

const s = html.indexOf("<main");
const e = html.indexOf("</main>", s);
html = html.slice(html.indexOf(">", s) + 1, e === -1 ? html.length : e);

const start = from ? html.indexOf(from) : 0;
if (start === -1) { console.error("marker not found: " + from); process.exit(1); }
let seg = html.slice(start);

const SELF = new Set(["img","br","hr","input","meta","link","source","use","path","rect","circle","ellipse","line","polygon","polyline","stop","col","area","base","embed","param","track","wbr"]);

const out = [];
let i = 0;
while (i < seg.length) {
	const lt = seg.indexOf("<", i);
	if (lt === -1) break;
	const gt = seg.indexOf(">", lt);
	if (gt === -1) break;
	const tok = seg.slice(lt, gt + 1);
	const name = (tok.match(/^<\/?([a-z][a-z0-9-]*)/i) || [])[1];

	if (!name) { i = gt + 1; continue; }

	// Collide a hidden subtree into one marker and jump past it.
	if (tok[1] !== "/" && /(\s|")hidden(\s|"|$)/.test(tok)) {
		let depth = 0, j = lt;
		for (;;) {
			const n = seg.indexOf("<", j);
			if (n === -1) break;
			const g = seg.indexOf(">", n);
			if (g === -1) break;
			const t2 = seg.slice(n, g + 1);
			const nm = (t2.match(/^<\/?([a-z][a-z0-9-]*)/i) || [])[1];
			if (nm && !SELF.has(nm.toLowerCase()) && !t2.endsWith("/>")) {
				depth += t2[1] === "/" ? -1 : 1;
			}
			j = g + 1;
			if (depth === 0) break;
		}
		out.push("[hidden:" + name + "]");
		i = j;
		continue;
	}

	const cls = (tok.match(/class="([^"]*)"/) || [])[1];
	out.push(tok[1] === "/" ? "</" + name + ">" : "<" + name + (cls ? " ." + cls.split(/\s+/)[0] : "") + ">");
	i = gt + 1;
}

console.log(out.join("\n"));