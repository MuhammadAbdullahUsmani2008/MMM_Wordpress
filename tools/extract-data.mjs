/**
 * Extract the MMM site's content from its TypeScript sources into JSON, so
 * the WordPress theme can be seeded from the real data instead of a hand copy.
 *
 * The source project is read-only from here; nothing is written back to it.
 *
 * Run:  node tools/extract-data.mjs
 * Out:  tools/data.json
 */
import fs from "node:fs";
import path from "node:path";
import { createRequire } from "node:module";

const SOURCE_ROOT = process.env.MMM_SOURCE_ROOT || "E:/MMM02";
const OUT_PATH = path.join(import.meta.dirname, "data.json");

// TypeScript lives in the source project's node_modules; borrow it from there.
const ts = createRequire(path.join(SOURCE_ROOT, "package.json"))("typescript");

const tsCache = new Map();

function loadModule(relPath) {
	const abs = path.join(SOURCE_ROOT, relPath);
	return loadModuleAbs(abs);
}

/** Resolve a bare specifier (e.g. "./site") from within a .ts file. */
function resolveTs(fromAbs, spec) {
	if (!spec.startsWith(".")) return null;
	const base = path.resolve(path.dirname(fromAbs), spec);
	for (const cand of [base, `${base}.ts`, `${base}.tsx`, path.join(base, "index.ts")]) {
		if (fs.existsSync(cand) && fs.statSync(cand).isFile()) return cand;
	}
	return null;
}

function loadModuleAbs(abs) {
	if (tsCache.has(abs)) return tsCache.get(abs).exports;

	const src = fs.readFileSync(abs, "utf8");
	const { outputText } = ts.transpileModule(src, {
		compilerOptions: {
			module: ts.ModuleKind.CommonJS,
			target: ts.ScriptTarget.ES2020,
			esModuleInterop: true,
		},
		fileName: abs,
	});

	const module_ = { exports: {} };
	tsCache.set(abs, module_);

	// Resolve relative imports from the source module's own location, so the
	// script never has to be installed inside the source project.
	const nativeRequire = createRequire(abs);
	const moduleRequire = (spec) => {
		if (spec.startsWith(".")) {
			const resolved = resolveTs(abs, spec);
			if (resolved && resolved.endsWith(".ts")) return loadModuleAbs(resolved);
		}
		return nativeRequire(spec);
	};
	moduleRequire.resolve = nativeRequire.resolve;
	moduleRequire.cache = nativeRequire.cache;

	const fn = new Function("exports", "require", "module", "__filename", "__dirname", outputText);
	fn(module_.exports, moduleRequire, module_, abs, path.dirname(abs));
	return module_.exports;
}

const site = loadModule("src/data/site.ts");
const gallery = loadModule("src/data/gallery.ts");
const search = loadModule("src/data/search.ts");

const data = {
	sourceRoot: SOURCE_ROOT,
	extractedAt: new Date().toISOString(),
	org: site.org,
	nav: site.nav,
	impactStats: site.impactStats,
	programs: site.programs,
	givingTiers: site.givingTiers,
	allocation: site.allocation,
	reports: site.reports,
	means: site.means,
	regions: site.regions,
	gallery: gallery.gallery ?? gallery.default?.gallery ?? gallery,
	// The filter tabs. gallery.ts holds both the tab definitions and the flat
	// image list; the tabs carry the labels, which are content in their own
	// right and must be editable like everything else.
	galleryGroups: gallery.galleryGroups ?? [],
	searchIndex: search.searchIndex ?? search.index ?? search.default ?? search,
};

fs.writeFileSync(OUT_PATH, JSON.stringify(data, null, 2), "utf8");

const len = (v) => (Array.isArray(v) ? v.length : v == null ? 0 : Object.keys(v).length);
console.log("extracted ->", OUT_PATH);
console.log({
	org: len(data.org),
	nav: len(data.nav),
	impactStats: len(data.impactStats),
	programs: len(data.programs),
	givingTiers: len(data.givingTiers),
	allocation: len(data.allocation),
	reports: len(data.reports),
	means: len(data.means),
	regions: len(data.regions),
	gallery: len(data.gallery),
	searchIndex: len(data.searchIndex),
});
