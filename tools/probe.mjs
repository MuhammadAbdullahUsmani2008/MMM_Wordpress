/** Quick structural probe of a live route. Run: node tools/probe.mjs <path> */
const path = process.argv[2] || "/what-we-do/training/";
const r = await fetch("http://localhost:8080" + path);
const b = await r.text();

const markers = [
	"<!DOCTYPE", "<html", "<head", "</head>", "<body",
	'id="main"', "</main>", "<footer",
	"themes/mmm/assets/js/mmm.js", "data-mega-panel", "Donate Now",
	'<header class="relative isolate',
];

console.log("status", r.status, "bytes", b.length, "\n");
for (const m of markers) console.log(String(b.includes(m)).padEnd(6), m);

const mainAt = b.search(/<main\b/);
console.log("\n<main index:", mainAt);
console.log("first 400:", JSON.stringify(b.slice(0, 400)));

const closeAt = b.indexOf("</main>");
if (closeAt > -1) {
	console.log("\naround </main>:", JSON.stringify(b.slice(Math.max(0, closeAt - 300), closeAt + 8)));
}
