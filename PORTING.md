# MMM → WordPress porting conventions

The goal is **byte-level fidelity with the original build**, with content
editable in wp-admin and the design locked in the templates.

Read this completely before writing any template.

---

## 1. Ground truth

The original site is a Next.js static export at `E:\MMM02`. It has already
been built; the output is at `E:\MMM02\out\`.

**Every route has an `index.html` you must reproduce:**

| Route | Ground-truth file | Template to write |
|---|---|---|
| `/` | `out/index.html` | `front-page.php` |
| `/about/` | `out/about/index.html` | `page-about.php` |
| `/contact/` | `out/contact/index.html` | `page-contact.php` |
| `/donate/` | `out/donate/index.html` | `page-donate.php` |
| `/get-involved/` | `out/get-involved/index.html` | `page-get-involved.php` |
| `/media/` | `out/media/index.html` | `page-media.php` |
| `/what-we-do/` | `out/what-we-do/index.html` | `page-what-we-do.php` |
| `/disaster-response/` | `out/disaster-response/index.html` | `page-disaster-response.php` |
| `/zakat-calculator/` | `out/zakat-calculator/index.html` | `page-zakat-calculator.php` |
| `/what-we-do/{slug}/` | `out/what-we-do/{slug}/index.html` | `single-program.php` |
| `/media/{slug}/` | `out/media/{slug}/index.html` | `single-report.php` |
| anything else | `out/404.html` | `404.php` |

You only reproduce **`<main>…</main>`**. The `<head>`, masthead, mobile
drawer and `<footer>` are already done — `header.php` opens `<main id="main">`
at the very end, and `footer.php` starts with `</main>`.

### Extracting the ground truth

The build HTML is one long line and ~30% of it is React hydration payload
(`<script>self.__next_f.push(...)</script>`) which you must ignore. To get a
readable reference:

```powershell
$h = Get-Content "E:\MMM02\out\about\index.html" -Raw
$m = $h.Substring($h.IndexOf('<main'), $h.IndexOf('</main>') - $h.IndexOf('<main'))
$m = [regex]::Replace($m, '(?s)<script>self\.__next_f\.push.*?</script>', '')
[System.IO.File]::WriteAllText("C:\Users\pk\AppData\Local\Temp\opencode\mmm\ref-about.html", $m, (New-Object System.Text.UTF8Encoding($false)))
```

The React payload carries no rendered markup — stripping it changes nothing
you need to copy.

---

## 2. Template skeleton

```php
<?php
/**
 * The About page.
 *
 * @package mmm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Optional: image to preload in <head> — see header.php.
mmm_hero_preload( '/media/xxx/hero.jpg' );

get_header();

$copy = ...;   // see section 4
?>

<!-- … exact <main> markup, byte for byte, with PHP substitutions … -->

<?php
get_footer();
```

`get_header()` prints everything through `<main id="main">`.
`get_footer()` prints `</main>`, the site footer, the floating controls, the
search-index JSON, JSON-LD and `</body></html>`.

Do **not** emit `<html>`, `<head>`, `<body>`, `<header>`, `<footer>`,
`<main id="main">` or `</main>` yourself.

---

## 3. Keeping the markup identical

Copy the markup exactly. Specifically:

- Preserve **every class string verbatim**, including arbitrary Tailwind
  values like `sm:max-w-[430px]`. The compiled CSS only contains the classes
  the original used — if you invent a class it will have no style, and if you
  drop one the design changes.
- Preserve attribute order where practical, self-closing style (`/>` vs
  `>`), and the exact SVG paths.
- Preserve `data-visible="false"` and `style="--reveal-delay:120ms"` on reveal
  wrappers, and the class variants `reveal-left` / `reveal-right` /
  `reveal-scale`.
- Keep `aria-*` and `role` attributes.
- Do not "improve", reformat, re-indent or re-attribute anything.

**Whitespace inside tags does not matter** (browsers collapse it), but text
content does.

---

## 4. Making words editable

Design is locked in the template; the *words* come from WordPress through
`mmm_copy()`:

```php
mmm_copy( 'about', 'intro', 'The original sentence from the build.' );
```

- 1st arg: the page slug (`about`, `donate`, …).
- 2nd arg: a stable `snake_case` key.
- 3rd arg: **the original text, verbatim** — this is the default and makes
  the page correct on first render with nothing seeded.

Register a copy field for every genuine piece of prose: headings, paragraphs,
list-item sentences, button labels, stat captions, footnote text.

Do **not** register:

- numbers inside data-driven loops (those come from the options/CPT, below)
- decorative spans, icon labels, `sr-only` text, `aria-label`s
- text inside SVGs

For a run of markup that contains several elements (e.g. a heading plus two
paragraphs), use `mmm_copy_html( $slug, $key, $markup )` — it stores raw HTML
and filters it through `wp_kses_post()`.

Wrap in the normal escaping helpers: `esc_html()` for text,
`esc_attr()` inside attributes, `esc_url()` for links.

---

## 5. Data that lives in WordPress (never hard-code these)

Everything below already exists in the database (seeded from the original
TypeScript). Use the getters — hard-coding them would break editing.

### Organisation
```php
mmm_org_value( 'name' );        // also: legalName, short, motto, founded,
                                // phone, phoneHref, whatsapp, email,
                                // address_line1, address_line2, address_country
mmm_bank( 'iban' );             // title, bank, account, iban, branch
mmm_social();                   // [ [ 'name' => 'Facebook', 'href' => '...' ], ... ]
mmm_social_url( 'YouTube' );
```

### Collections (post types — query with `mmm_programs()` / `mmm_reports()`)
```php
mmm_programs();       // ordered; each: slug, title, region, summary, body[]
                      // image, gallery[], accent, highlights[{label,value}], id
mmm_program_by_slug( 'save-vision' );
mmm_reports();        // slug, title, place, kind, excerpt, body[], image, date, id
mmm_report_by_slug( 'one-million-patients' );
```

### Option lists
```php
mmm_nav();            // [ {label, href, blurb?, columns:[{heading, links:[{label,href}]}], feature?} ]
mmm_impact_stats();   // [ {value, suffix, label, detail} ]
mmm_giving_tiers();   // [ {amount, title, body, accent} ]
mmm_allocation();     // [ {label, value} ]
mmm_means();          // [ '...' ]
mmm_regions();        // [ {name, detail, focus} ]
mmm_gallery();        // 253 flat rows: [ { src, group, region } ].
                      //   group: field(25) academy-conferences(54) medical(50)
                      //          gaza-food(47) ration(27) gaza-winter(22)
                      //          disaster(15) gaza-water(13)
                      //   region: Pakistan(94) Gaza(159)
                      // Group client-side by `group` to build the filter tabs —
                      // the option is deliberately flat so rows can be added.
mmm_gallery_groups(); // 7 filter tabs, in display order:
                      //   [ { key, label, region } ]
                      //   field    -> "Medical and surgical camps"
                      //   academy-conferences -> "MMM Academy & Annual Conferences"
                      //   disaster -> "Pakistan flood response"
                      //   medical  -> "Gaza field clinics"
                      //   gaza-food -> "Hot meals and parcels"
                      //   gaza-water -> "Water for Life"
                      //   gaza-winter -> "Winter Packages"
                      // The "ration" key occurs on 27 rows but has no filter tab.
                      // The first tab rendered is { key:"all", label:"Everything" }
                      // — that one is fixed UI, not data; add it yourself.
mmm_gallery_group_label( 'gaza-water' );  // -> "Water for Life"
mmm_search_index();   // embedded automatically by footer.php
```

### Helpers
```php
mmm_media( '/media/field/x.jpg' );  // -> URL; but /media/... already resolves
                                    // via a symlink, so plain paths are fine
mmm_link( '/about/' );              // -> home_url() for internal links
mmm_reveal( $html, 'up', 120 );     // reveal wrapper
mmm_reveal_attrs( 'left', 80 );     // reveal attributes only
mmm_count_attrs( 4.2, 'M+', 1 );    // data-count-* for CountUp
```

**Links:** internal hrefs stay literal (`href="/about/"`) exactly as the
original — the site is a root install and `/media/*` is symlinked, so paths
need no rewriting.

**Images:** `src="/media/..."` stays literal too.

---

## 6. Behaviour hooks for `assets/js/mmm.js`

The JS layer is already written and documents its own contract in a header
comment — read the top ~60 lines of
`E:\Wordpress_MMM\theme\mmm\assets\js\mmm.js` before writing interactive
markup. The key points:

- **CountUp** — the original SSR spans carry no attributes. Emit
  `data-count-to`, `data-count-suffix`, `data-count-decimals`.
- **Hero slideshow** — each `.hero-slide` needs
  `data-hero-operation`, `data-hero-place`, `data-hero-href`; the capsule
  needs `[data-hero-dot]`, `[data-hero-prev]`, `[data-hero-next]`,
  `[data-hero-counter]`, `[data-hero-operation]`, `[data-hero-place]`.
- **Mega panel / search panel / drawer** are already in `header.php` with
  `[data-mega-panel]`, `[data-search-panel]`, `[data-drawer]`. Do not
  duplicate them in page templates.
- **Gallery** — filters and lightbox are wired by the JS; keep the original
  markup and its data attributes.
- **Zakat / donation planner / membership form** — the JS owns the logic;
  the PHP only supplies the markup. Do not add `name=` attributes expecting
  a server round-trip: nothing is ever POSTed.

If you need a hook that does not exist, use a `data-*` attribute with a
descriptive name and **report it in your final message** — do not modify
`mmm.js`.

---

## 7. Page-level requirements

- Call `mmm_hero_preload( '...' )` **before** `get_header()` when the original
  `<head>` preloaded a hero image for that page. Check the original `<head>`:
  every page preloads `/media/brand/circle-logo.png` (already emitted by
  `header.php`); some preload one more.
- `$copy` is not auto-populated — call `mmm_copy()` inline where each value is
  used, or assign several up front. Either is fine.
- The `<main>` wrapper is already open when your markup starts.

---

## 8. Verification (do this — it is not optional)

After writing a template, render it and diff against the original:

```powershell
# original
$o = Get-Content "E:\MMM02\out\about\index.html" -Raw
$o = $o.Substring($o.IndexOf('<main'), $o.IndexOf('</main>') - $o.IndexOf('<main'))
$o = [regex]::Replace($o, '(?s)<script>self\.__next_f\.push.*?</script>', '')

# yours
Invoke-WebRequest "http://localhost:8080/about/" -UseBasicParsing -OutFile "$env:TEMP\wp-about.html"
$w = Get-Content "$env:TEMP\wp-about.html" -Raw
$w = $w.Substring($w.IndexOf('<main id="main">'), $w.IndexOf('</main>') - $w.IndexOf('<main id="main">'))
```

Then compare. Differences in whitespace are acceptable; differences in
**classes, structure, or text content** are not. Use a tag/class-only
comparison, e.g. strip all attribute values except `class`, and diff.

Check for PHP notices too:

```powershell
docker compose run --rm wpcli eval-file /setup/status.php
```

---

## 9. Things that are deliberately different

- No React, no `_next` scripts, no `self.__next_f` payload.
- No `<link rel="preload">` for scripts; `wp_head()` emits the stylesheets.
- `data-visible` starts `false` — if your JS fails to run, reveal content is
  invisible. That matches the original, where React had to run too.
- The search index JSON and JSON-LD are printed by `footer.php`.
