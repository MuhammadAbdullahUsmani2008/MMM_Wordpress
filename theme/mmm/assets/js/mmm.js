/* =============================================================================
 * mmm.js — vanilla-JS behaviour layer for the Muslim Medical Mission theme.
 *
 * Self-contained, dependency-free replacement for the original React runtime.
 * Every feature is isolated in its own try/catch and every DOM lookup is
 * null-guarded, so pages that lack a given component never throw.
 *
 * DOM contract consumed by this file (markup emitted by the PHP templates):
 *
 *   Scroll reveal  .reveal[data-visible="false"] (+ .reveal-left/-right/-scale,
 *                  style="--reveal-delay: Nms"); data-reveal-now fires at once.
 *   Counters       [data-count-to] or [data-count]; optional data-count-from,
 *                  data-count-decimals, data-count-suffix, data-count-duration,
 *                  data-count-grouping="false". Without data attrs the target
 *                  and suffix are inferred from the element's own text.
 *   Header         [data-mega-trigger] > button[aria-expanded] + [data-mega-panel]
 *                  [data-search-toggle] [data-search-panel] [data-search-input]
 *                  [data-search-close] [data-search-results]
 *   Drawer         [data-drawer] [data-drawer-backdrop] [data-drawer-sheet]
 *                  [data-drawer-open] [data-drawer-close] [data-drawer-search]
 *                  [data-drawer-section] > sibling [data-drawer-panel]
 *   Floats         button[aria-label="Back to top"] / [data-back-to-top]
 *                  a[aria-label="Chat with us on WhatsApp"] / [data-whatsapp-float]
 *   Hero           .hero-slide (+ .hero-slide-active/-inactive, img.hero-kenburns*)
 *                  dots: [data-hero-dot] (fallback: button[aria-label^="Go to slide"])
 *                  arrows: [data-hero-prev]/[data-hero-next] (fallback: aria-labels)
 *                  capsule: [data-hero-link] [data-hero-tagline]
 *                           [data-hero-counter]
 *                  per-slide data: data-hero-operation / data-hero-place /
 *                  data-hero-href on the .hero-slide element.
 *   Gallery        root [data-gallery]; tabs [data-gallery-filter="key"]
 *                  scroll strip [data-gallery-tabs]; arrows [data-gallery-arrow-left|
 *                  -right]; grid [data-gallery-grid]; items [data-gallery-item]
 *                  with data-group, optional data-label / data-region;
 *                  more button [data-gallery-more] with span [data-gallery-count].
 *   Search index   <script type="application/json" id="mmm-search-index">
 *                  [{ "title","section","href","terms" }, ...]
 *   Zakat          #zakat-amount inside a form; [data-zakat-error],
 *                  [data-zakat-result] > [data-zakat-result-amount],
 *                  [data-zakat-result-note]; live rates panel:
 *                  [data-zakat-rate-status] [data-zakat-rates] [data-zakat-silver]
 *                  [data-zakat-gold] [data-zakat-nisab] [data-zakat-gold-nisab]
 *                  [data-zakat-rate-error]. The live metal-rate fetch (as on the
 *                  original site) only runs when the form carries
 *                  data-zakat-live — off by default to keep the page free of
 *                  third-party requests.
 *   Planner        root [data-donation-planner] (+ data-whatsapp-base)
 *                  causes [data-cause="key"] with data-label, data-unit-cost,
 *                  data-unit-name, data-unit-plural, data-note;
 *                  presets [data-preset-amount="9000"]; custom [data-custom-amount];
 *                  outputs [data-out-heading] [data-out-units] [data-out-unit-label]
 *                          [data-out-note] [data-out-zero] [data-out-cta];
 *                  copy buttons [data-copy-value].
 *   Membership     form#membership-form-element (or [data-membership-form]);
 *                  radios input[name="membershipCategory"];
 *                  conditional blocks [data-when-category="1,2,3,4,5,6"],
 *                  [data-when-how-known="Other"]; dimmed sections
 *                  [data-dim-when-category="..."]; per-error slots
 *                  [data-error-for="fieldId"]; global error box [data-form-errors]
 *                  > [data-form-errors-list]; file displays [data-file-field] >
 *                  [data-file-name]; category titles on radios (data-title).
 * ========================================================================== */

(function () {
	"use strict";

	/* ------------------------------------------------------------------ *
	 * Utilities
	 * ------------------------------------------------------------------ */

	var $ = function (sel, root) {
		return (root || document).querySelector(sel);
	};

	var $$ = function (sel, root) {
		return Array.prototype.slice.call((root || document).querySelectorAll(sel));
	};

	function onReady(fn) {
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", fn, { once: true });
		} else {
			fn();
		}
	}

	function reducedMotion() {
		try {
			return (
				typeof window.matchMedia === "function" &&
				window.matchMedia("(prefers-reduced-motion: reduce)").matches
			);
		} catch (e) {
			return false;
		}
	}

	/** Add one class set and remove another in a single call. */
	function swapClasses(el, add, remove) {
		if (!el) return;
		for (var i = 0; i < add.length; i++) el.classList.add(add[i]);
		for (var j = 0; j < remove.length; j++) el.classList.remove(remove[j]);
	}

	function escapeHtml(value) {
		return String(value).replace(/[&<>"']/g, function (c) {
			return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
		});
	}

	/* Body scroll lock shared by the mobile drawer and the lightbox, so that
	   closing one never unlocks the page while the other is still open. */
	var scrollLocks = 0;
	function lockScroll() {
		scrollLocks++;
		if (scrollLocks === 1) document.body.style.overflow = "hidden";
	}
	function unlockScroll() {
		scrollLocks = Math.max(0, scrollLocks - 1);
		if (scrollLocks === 0) document.body.style.overflow = "";
	}

	/* ------------------------------------------------------------------ *
	 * inView — the single rAF-throttled scroll engine.
	 *
	 * Faithful port of src/lib/inView.ts: a rect check fires on anything at
	 * or above the trigger line (including elements already scrolled past),
	 * which IntersectionObserver would miss on fast scrolls.
	 * ------------------------------------------------------------------ */

	var watched = new Set();
	var frame = 0;
	var fallback = 0;
	var listening = false;

	function checkView() {
		/* Whichever scheduler got here first clears the other, so the check
		   never runs twice for one turn. */
		if (frame) {
			cancelAnimationFrame(frame);
			frame = 0;
		}
		if (fallback) {
			clearTimeout(fallback);
			fallback = 0;
		}
		var vh = window.innerHeight || 0;

		/* Pick up reveal elements added to the DOM after the last check
		   (injected panels, re-rendered grids, ...). */
		scanReveals();
		scanCountUps();

		watched.forEach(function (entry) {
			try {
				if (entry.el.getBoundingClientRect().top < vh * (1 - entry.ratio)) {
					watched.delete(entry);
					entry.fire();
				}
			} catch (e) {
				watched.delete(entry);
			}
		});

		if (watched.size === 0) stopEngine();
	}

	function scheduleCheck() {
		if (frame) return;
		frame = requestAnimationFrame(checkView);

		/* rAF is paused outright in a hidden tab and heavily throttled in
		   some in-app browsers. Everything here depends on this check to
		   become visible at all, so back it up with a timer — timers keep
		   running in those same conditions. The frame above stays the normal
		   path; this only fires when it does not. */
		if (!fallback) {
			fallback = setTimeout(checkView, 150);
		}
	}

	function startEngine() {
		if (listening) return;
		listening = true;
		window.addEventListener("scroll", scheduleCheck, { passive: true });
		window.addEventListener("resize", scheduleCheck, { passive: true });
	}

	function stopEngine() {
		if (!listening) return;
		listening = false;
		window.removeEventListener("scroll", scheduleCheck);
		window.removeEventListener("resize", scheduleCheck);
	}

	/** Register `fire` for `el`, triggered once the element reaches the line. */
	function onEnter(el, ratio, fire) {
		var entry = { el: el, ratio: ratio, fire: fire };
		watched.add(entry);
		startEngine();
		scheduleCheck();
	}

	/* ------------------------------------------------------------------ *
	 * 1. Scroll reveal
	 * ------------------------------------------------------------------ */

	var revealSeen = typeof WeakSet !== "undefined" ? new WeakSet() : null;

	function scanReveals() {
		if (!revealSeen) return;
		$$('.reveal[data-visible="false"]').forEach(function (el) {
			if (revealSeen.has(el)) return;
			revealSeen.add(el);
			if (reducedMotion()) {
				/* CSS already forces .reveal visible under reduced motion; setting
				   the attribute keeps the DOM consistent with the React output. */
				el.setAttribute("data-visible", "true");
				return;
			}
			var ratio = el.hasAttribute("data-reveal-now") ? 0 : 0.15;
			onEnter(el, ratio, function () {
				el.setAttribute("data-visible", "true");
			});
		});
	}

	/* ------------------------------------------------------------------ *
	 * 2. Counters (CountUp)
	 * ------------------------------------------------------------------ */

	var countSeen = typeof WeakSet !== "undefined" ? new WeakSet() : null;

	var countEaseOut = function (t) {
		return 1 - Math.pow(1 - t, 3);
	};

	function parseCounter(el) {
		/* Honour data attributes when present, otherwise infer the target and
		   suffix from the element's own (server-rendered) text. */
		var raw = (el.textContent || "").trim();
		var suffix = el.getAttribute("data-count-suffix");
		if (suffix === null) suffix = "";

		var to = parseFloat(el.getAttribute("data-count-to"));
		var decimalsAttr = el.getAttribute("data-count-decimals");
		var decimals = decimalsAttr === null ? null : parseInt(decimalsAttr, 10);
		if (decimals !== null && (!isFinite(decimals) || decimals < 0)) decimals = null;

		if (!isFinite(to)) {
			var m = raw.match(/^([\d][\d\s,.]*?)([^\d\s,.].*)?$/);
			if (!m) return null;
			var numText = m[1].replace(/[\s,]/g, "");
			to = parseFloat(numText);
			if (!isFinite(to)) return null;
			if (suffix === "") suffix = m[2] || "";
			if (decimals === null && numText.indexOf(".") !== -1) {
				decimals = numText.length - numText.indexOf(".") - 1;
			}
		}

		var from = parseFloat(el.getAttribute("data-count-from"));
		if (!isFinite(from)) from = 0;

		var duration = parseInt(el.getAttribute("data-count-duration"), 10);
		if (!isFinite(duration) || duration <= 0) duration = 1600;

		var groupingAttr = el.getAttribute("data-count-grouping");
		var useGrouping = groupingAttr !== "false";

		return { from: from, to: to, suffix: suffix, decimals: decimals, duration: duration, useGrouping: useGrouping };
	}

	function formatCount(value, cfg) {
		if (cfg.useGrouping) {
			if (cfg.decimals === null) return value.toLocaleString("en-GB");
			return value.toLocaleString("en-GB", {
				minimumFractionDigits: cfg.decimals,
				maximumFractionDigits: cfg.decimals,
			});
		}
		if (cfg.decimals === null) return String(value);
		return value.toFixed(cfg.decimals);
	}

	function initCounter(el, cfg) {
		var fired = false;
		var animate = function () {
			if (fired) return;
			fired = true;
			var start = performance.now();
			var frameId = 0;
			var tick = function (now) {
				var progress = Math.min((now - start) / cfg.duration, 1);
				var eased = countEaseOut(progress);
				var value = Math.round(cfg.from + (cfg.to - cfg.from) * eased);
				el.textContent = formatCount(value, cfg) + cfg.suffix;
				if (progress < 1) frameId = requestAnimationFrame(tick);
			};
			frameId = requestAnimationFrame(tick);
		};

		if (reducedMotion()) {
			el.textContent = formatCount(cfg.to, cfg) + cfg.suffix;
			return;
		}
		/* Ratio 0.2 matches the original onEnter(el, 0.2, animate). */
		onEnter(el, 0.2, animate);
	}

	function scanCountUps() {
		if (!countSeen) return;
		$$("[data-count-to], [data-count]").forEach(function (el) {
			if (countSeen.has(el)) return;
			countSeen.add(el);
			try {
				var cfg = parseCounter(el);
				if (!cfg) return;
				initCounter(el, cfg);
			} catch (e) {
				/* leave the server-rendered text as-is */
			}
		});
	}

	/* ------------------------------------------------------------------ *
	 * 3a. Header — mega menu
	 * ------------------------------------------------------------------ */

	function initMegaMenu() {
		var header = $("header");
		if (!header) return;

		var items = $$("[data-mega-trigger]", header);
		if (!items.length) return;

		var closeTimer = 0;

		function panels() {
			return items.map(function (item) {
				return {
					item: item,
					btn: $("button", item),
					panel: $("[data-mega-panel]", item),
					chev: item.querySelector("button svg"),
				};
			});
		}

		function cancelClose() {
			if (closeTimer) {
				window.clearTimeout(closeTimer);
				closeTimer = 0;
			}
		}

		function open(entry) {
			if (!entry.panel) return;
			closeAll();
			entry.panel.removeAttribute("hidden");
			if (entry.btn) entry.btn.setAttribute("aria-expanded", "true");
			if (entry.chev) entry.chev.classList.add("rotate-180");
		}

		function close(entry) {
			if (!entry.panel) return;
			entry.panel.setAttribute("hidden", "");
			if (entry.btn) entry.btn.setAttribute("aria-expanded", "false");
			if (entry.chev) entry.chev.classList.remove("rotate-180");
		}

		function closeAll() {
			panels().forEach(close);
		}

		/* The original scheduled the close with a 180ms delay so the pointer
		   can travel from the trigger into the panel. */
		function scheduleClose() {
			cancelClose();
			closeTimer = window.setTimeout(function () {
				closeTimer = 0;
				closeAll();
			}, 180);
		}

		items.forEach(function (item) {
			var entryCache = null;
			var entry = function () {
				if (!entryCache) {
					entryCache = {
						item: item,
						btn: $("button", item),
						panel: $("[data-mega-panel]", item),
						chev: item.querySelector("button svg"),
					};
				}
				return entryCache;
			};

			item.addEventListener("mouseenter", function () {
				cancelClose();
				if (entry().panel) open(entry());
				else closeAll();
			});

			item.addEventListener("focusin", function () {
				cancelClose();
				if (entry().panel) open(entry());
			});

			var btn = $("button", item);
			if (btn) {
				btn.addEventListener("click", function () {
					var isOpen = btn.getAttribute("aria-expanded") === "true";
					if (isOpen) close(entry());
					else open(entry());
				});
			}

			var panel = $("[data-mega-panel]", item);
			if (panel) {
				/* Clicking any link inside the panel navigates and closes it. */
				panel.addEventListener("click", function (e) {
					if (e.target && e.target.closest && e.target.closest("a[href]")) {
						cancelClose();
						closeAll();
					}
				});
			}
		});

		header.addEventListener("mouseleave", scheduleClose);

		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape") {
				cancelClose();
				closeAll();
			}
		});
	}

	/* ------------------------------------------------------------------ *
	 * 3b. Header — search overlay
	 * ------------------------------------------------------------------ */

	var SEARCH_LIMIT = 7;

	function initSearch() {
		var toggle = $("[data-search-toggle]");
		var panel = $("[data-search-panel]");
		if (!toggle || !panel) return;

		var input = $("[data-search-input]", panel);
		var closeBtn = $("[data-search-close]", panel);
		var results = $("[data-search-results]", panel);
		if (!input || !results) return;

		/* Read the embedded JSON index; fail gracefully when absent. */
		var index = [];
		try {
			var node = document.getElementById("mmm-search-index");
			if (node && node.textContent) {
				var parsed = JSON.parse(node.textContent);
				if (Array.isArray(parsed)) index = parsed;
			}
		} catch (e) {
			index = [];
		}

		/* Port of src/data/search.ts — scoring and ordering must match. */
		function runSearch(query) {
			var q = String(query || "").trim().toLowerCase();
			if (q.length < 1) return [];
			var words = q.split(/\s+/).filter(Boolean);

			return index
				.map(function (entry) {
					var title = String(entry.title || "").toLowerCase();
					var section = String(entry.section || "").toLowerCase();
					var terms = String(entry.terms || "");
					var haystack = title + " " + section + " " + terms;
					var score = 0;
					for (var i = 0; i < words.length; i++) {
						var w = words[i];
						if (title.indexOf(w) === 0) score += 6;
						else if (title.indexOf(w) !== -1) score += 4;
						else if (haystack.indexOf(w) !== -1) score += 1;
					}
					return { entry: entry, score: score };
				})
				.filter(function (r) {
					return r.score > 0;
				})
				.sort(function (a, b) {
					return b.score - a.score;
				})
				.slice(0, SEARCH_LIMIT)
				.map(function (r) {
					return r.entry;
				});
		}

		function render(query) {
			var q = String(query || "").trim();
			if (q.length < 1) {
				results.setAttribute("hidden", "");
				results.innerHTML = "";
				return;
			}
			var found = runSearch(q);
			var html;
			if (found.length) {
				html = found
					.map(function (r) {
						return (
							'<li><a href="' +
							escapeHtml(r.href || "#") +
							'" class="group flex items-baseline justify-between gap-4 rounded-lg px-3.5 py-2.5 transition-colors hover:bg-blue-50">' +
							'<span class="font-display text-[0.95rem] font-bold text-[#003475] group-hover:text-[#046BD2]">' +
							escapeHtml(r.title || "") +
							'</span>' +
							'<span class="shrink-0 text-[0.78rem] font-semibold text-[#7A7A7A]">' +
							escapeHtml(r.section || "") +
							"</span></a></li>"
						);
					})
					.join("");
			} else {
				html =
					'<li class="px-3.5 py-3 text-[0.92rem] text-slate-500">Nothing matched that query. Try typing: flood, Gaza, water, camp, or volunteer.</li>';
			}
			results.innerHTML = html;
			results.removeAttribute("hidden");
		}

		function openSearch() {
			panel.removeAttribute("hidden");
			toggle.classList.add("bg-[#E9EEF5]", "text-[#075BD6]");
			toggle.classList.remove("text-[#4B5563]");
			input.focus();
		}

		function closeSearch() {
			panel.setAttribute("hidden", "");
			toggle.classList.remove("bg-[#E9EEF5]", "text-[#075BD6]");
			toggle.classList.add("text-[#4B5563]");
			/* The React panel unmounted on close, which reset the query. */
			input.value = "";
			render("");
		}

		toggle.addEventListener("click", function () {
			if (panel.hasAttribute("hidden")) openSearch();
			else closeSearch();
		});

		if (closeBtn) closeBtn.addEventListener("click", closeSearch);

		input.addEventListener("input", function () {
			render(input.value);
		});

		results.addEventListener("click", function (e) {
			if (e.target && e.target.closest && e.target.closest("a[href]")) closeSearch();
		});

		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape" && !panel.hasAttribute("hidden")) closeSearch();
		});

		/* Drawer quick-search opens the same overlay. */
		var drawerSearch = $("[data-drawer-search]");
		if (drawerSearch) {
			drawerSearch.addEventListener("click", function () {
				closeDrawer();
				openSearch();
			});
		}
	}

	/* ------------------------------------------------------------------ *
	 * 3c. Mobile drawer
	 * ------------------------------------------------------------------ */

	var drawerApi = { close: function () {} };

	function closeDrawer() {
		drawerApi.close();
	}

	function initDrawer() {
		var root = $("[data-drawer]");
		if (!root) return;

		var backdrop = $("[data-drawer-backdrop]", root);
		var sheet = $("[data-drawer-sheet]", root);
		var openBtn = $("[data-drawer-open]");
		var closeBtn = $("[data-drawer-close]", root);
		if (!sheet) return;

		var isOpen = false;
		var locked = false;

		function open() {
			if (isOpen) return;
			isOpen = true;
			root.classList.remove("pointer-events-none");
			root.setAttribute("aria-hidden", "false");
			if (backdrop) swapClasses(backdrop, ["opacity-100"], ["opacity-0"]);
			swapClasses(sheet, ["translate-x-0"], ["translate-x-full"]);
			if (!locked) {
				locked = true;
				lockScroll();
			}
		}

		function close() {
			if (!isOpen) return;
			isOpen = false;
			root.classList.add("pointer-events-none");
			root.setAttribute("aria-hidden", "true");
			if (backdrop) swapClasses(backdrop, ["opacity-0"], ["opacity-100"]);
			swapClasses(sheet, ["translate-x-full"], ["translate-x-0"]);
			if (locked) {
				locked = false;
				unlockScroll();
			}
		}

		drawerApi.close = close;

		if (openBtn) openBtn.addEventListener("click", open);
		if (closeBtn) closeBtn.addEventListener("click", close);
		if (backdrop) backdrop.addEventListener("click", close);

		/* Any link inside the sheet navigates and closes the drawer. */
		sheet.addEventListener("click", function (e) {
			if (e.target && e.target.closest && e.target.closest("a[href]")) close();
		});

		/* Accordion sections — one open at a time, like the React state. */
		var sectionBtns = $$("[data-drawer-section]", root);
		var current = null;

		function closeSection(btn, panel) {
			if (!panel) return;
			swapClasses(panel, ["grid-rows-[0fr]", "opacity-0"], ["grid-rows-[1fr]", "pb-3", "opacity-100"]);
			btn.setAttribute("aria-expanded", "false");
			var chev = btn.querySelector("svg");
			if (chev) chev.classList.remove("rotate-180");
		}

		function openSection(btn, panel) {
			swapClasses(panel, ["grid-rows-[1fr]", "pb-3", "opacity-100"], ["grid-rows-[0fr]", "opacity-0"]);
			btn.setAttribute("aria-expanded", "true");
			var chev = btn.querySelector("svg");
			if (chev) chev.classList.add("rotate-180");
		}

		sectionBtns.forEach(function (btn) {
			var panel = btn.parentElement ? $("[data-drawer-panel]", btn.parentElement) : null;
			btn.addEventListener("click", function () {
				var isOpenSection = btn.getAttribute("aria-expanded") === "true";
				if (current && current.btn !== btn) closeSection(current.btn, current.panel);
				if (isOpenSection) {
					closeSection(btn, panel);
					current = null;
				} else {
					openSection(btn, panel);
					current = { btn: btn, panel: panel };
				}
			});
		});

		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape") close();
		});
	}

	/* ------------------------------------------------------------------ *
	 * 4. Back to top + WhatsApp float
	 * ------------------------------------------------------------------ */

	function initBackToTop() {
		var btn = $("[data-back-to-top]") || $('button[aria-label="Back to top"]');
		if (!btn) return;

		var HIDDEN = ["pointer-events-none", "translate-y-3", "opacity-0"];
		var SHOWN = ["translate-y-0", "opacity-100"];

		var update = function () {
			if (window.scrollY > 600) swapClasses(btn, SHOWN, HIDDEN);
			else swapClasses(btn, HIDDEN, SHOWN);
		};
		update();
		window.addEventListener("scroll", update, { passive: true });

		btn.addEventListener("click", function () {
			window.scrollTo({ top: 0, behavior: "smooth" });
		});
	}

	function initWhatsAppFloat() {
		var link = $("[data-whatsapp-float]") || $('a[aria-label="Chat with us on WhatsApp"]');
		if (!link) return;
		var tip = $("[data-whatsapp-tip]", link) || link.querySelector("span");
		if (!tip) return;

		var show = function () {
			swapClasses(tip, ["translate-x-0", "opacity-100"], ["translate-x-2", "opacity-0"]);
		};
		var hide = function () {
			swapClasses(tip, ["translate-x-2", "opacity-0"], ["translate-x-0", "opacity-100"]);
		};

		/* The original shows the tooltip briefly after mount: 1.5s in, 8s out. */
		window.setTimeout(show, 1500);
		window.setTimeout(hide, 8000);
	}

	/* ------------------------------------------------------------------ *
	 * 7. Hero slideshow (5s auto-advance, zoom-blur dissolve)
	 * ------------------------------------------------------------------ */

	var HERO_DURATION = 5000;
	var DOT_ON = ["h-2.5", "w-8", "sm:w-14", "bg-white/25", "shadow-[0_0_10px_rgba(239,59,25,0.4)]"];
	var DOT_OFF = ["h-2.5", "w-2.5", "bg-white/55", "hover:bg-white/90"];

	function initHero() {
		var firstSlide = $(".hero-slide");
		if (!firstSlide) return;
		var section = firstSlide.closest("section");
		if (!section) return;

		var slides = $$(".hero-slide", section);
		if (slides.length < 1) return;
		var count = slides.length;

		/* Locate the active slide from the SSR markup (slide 0 by default). */
		var active = 0;
		for (var i = 0; i < slides.length; i++) {
			if (slides[i].classList.contains("hero-slide-active")) {
				active = i;
				break;
			}
		}

		var dots = $$("[data-hero-dot]", section);
		if (!dots.length) {
			dots = $$('button[aria-label^="Go to slide"]', section).filter(function (b) {
				return b.closest("section") === section;
			});
		}
		dots = dots.slice(0, count);

		var counter = $("[data-hero-counter]", section);
		var link = $("[data-hero-link]", section);
		var tagline = $("[data-hero-tagline]", section);
		var opSpan = link ? $("[data-hero-operation]", link) : $("span[data-hero-operation]", section);
		var placeSpan = link ? $("[data-hero-place]", link) : $("span[data-hero-place]", section);
		var prevBtn = $("[data-hero-prev]", section) || $('button[aria-label="Previous slide"]', section);
		var nextBtn = $("[data-hero-next]", section) || $('button[aria-label="Next slide"]', section);

		var timer = 0;

		function replay(el) {
			/* Restart a CSS animation by removing the class, forcing a reflow
			   and adding it back — the equivalent of React remounting. */
			el.classList.remove("hero-reveal");
			void el.offsetWidth;
			el.classList.add("hero-reveal");
		}

		function render(index, initial) {
			for (var i = 0; i < slides.length; i++) {
				var slide = slides[i];
				var on = i === index;
				swapClasses(slide, on ? ["hero-slide-active"] : ["hero-slide-inactive"], on ? ["hero-slide-inactive"] : ["hero-slide-active"]);
				slide.setAttribute("aria-hidden", on ? "false" : "true");

				var img = slide.querySelector("img");
				if (img) {
					if (on) {
						img.classList.remove("hero-kenburns", "hero-kenburns-first");
						void img.offsetWidth;
						img.classList.add(i % 2 === 0 ? "hero-kenburns-first" : "hero-kenburns");
					} else {
						img.classList.remove("hero-kenburns", "hero-kenburns-first");
					}
				}

				/* Staggered text reveal replays each time a slide comes back. */
				if (on && !initial) {
					$$(".hero-reveal", slide).forEach(replay);
				}
			}

			/* Indicator dots + countdown progress bar. */
			for (var d = 0; d < dots.length; d++) {
				var dot = dots[d];
				var dotOn = d === index;
				if (dotOn) {
					swapClasses(dot, DOT_ON, ["w-2.5", "bg-slate-600", "bg-white/40", "bg-white/55", "hover:bg-white/90"]);
				} else {
					swapClasses(dot, DOT_OFF, ["w-8", "sm:w-14", "bg-white/25", "shadow-[0_0_10px_rgba(239,59,25,0.4)]"]);
				}
				dot.style.backgroundColor = "rgba(255, 255, 255, 0.55)";
				var oldProgress = dot.querySelector(".animate-hero-progress");
				if (oldProgress) oldProgress.remove();
				if (dotOn) {
					var bar = document.createElement("span");
					bar.className = "absolute inset-0 rounded-full bg-[#EF3B19] animate-hero-progress";
					bar.setAttribute("aria-hidden", "true");
					dot.appendChild(bar);
				}
				dot.setAttribute("aria-current", dotOn ? "true" : "false");
			}

			var cIndex = (index + 1 < 10 ? "0" : "") + (index + 1);
			var cTotal = (count < 10 ? "0" : "") + count;
			if (counter) counter.textContent = cIndex + " / " + cTotal;

			/* The capsule tagline and link follow the active slide. */
			var s = slides[index];
			var op = s.getAttribute("data-hero-operation");
			var place = s.getAttribute("data-hero-place");
			var href = s.getAttribute("data-hero-href");
			if (tagline && op && place) {
				if (opSpan) opSpan.textContent = op;
				if (placeSpan) placeSpan.textContent = place;
			} else {
				if (opSpan && op) opSpan.textContent = op;
				if (placeSpan && place) placeSpan.textContent = place;
			}
			if (counter) counter.style.color = "#94a3b8";
			if (link && href) link.setAttribute("href", href);
		}

		function schedule() {
			if (timer) window.clearTimeout(timer);
			timer = window.setTimeout(function () {
				timer = 0;
				goTo((active + 1) % count);
			}, HERO_DURATION);
		}

		function goTo(index) {
			active = ((index % count) + count) % count;
			render(active, false);
			schedule();
		}

		if (prevBtn) prevBtn.addEventListener("click", function () { goTo(active - 1); });
		if (nextBtn) nextBtn.addEventListener("click", function () { goTo(active + 1); });

		dots.forEach(function (dot, i) {
			dot.addEventListener("click", function () { goTo(i); });
		});

		/* Touch swipe, exactly ±50px like the React handlers. */
		var touchX = null;
		section.addEventListener(
			"touchstart",
			function (e) {
				if (e.touches && e.touches.length) touchX = e.touches[0].clientX;
			},
			{ passive: true }
		);
		section.addEventListener(
			"touchend",
			function (e) {
				if (touchX === null || !e.changedTouches || !e.changedTouches.length) return;
				var diff = touchX - e.changedTouches[0].clientX;
				if (diff > 50) goTo(active + 1);
				else if (diff < -50) goTo(active - 1);
				touchX = null;
			},
			{ passive: true }
		);

		render(active, true);
		schedule();
	}

	/* ------------------------------------------------------------------ *
	 * 5. Gallery — filters, pagination and lightbox
	 * ------------------------------------------------------------------ */

	function initGallery() {
		var root = $("[data-gallery]");
		if (!root) return;

		var grid = $("[data-gallery-grid]", root);
		if (!grid) return;

		var allItems = $$("[data-gallery-item]", grid);
		if (!allItems.length) return;

		var tabs = $$("[data-gallery-filter]", root);
		var tabsScroller = $("[data-gallery-tabs]", root);
		var arrowLeft = $("[data-gallery-arrow-left]", root);
		var arrowRight = $("[data-gallery-arrow-right]", root);
		var moreBtn = $("[data-gallery-more]", root);
		var countSpan = moreBtn ? $("[data-gallery-count]", moreBtn) : null;

		var TAB_ON = ["bg-[#046BD2]", "text-white", "shadow-sm"];
		var TAB_OFF = ["bg-slate-100", "text-[#334155]", "hover:bg-blue-50", "hover:text-[#046BD2]"];

		var filter = "all";
		var shown = 24;
		var visibleItems = [];

		function groupOf(item) {
			return item.getAttribute("data-group") || "all";
		}

		function apply() {
			visibleItems =
				filter === "all"
					? allItems.slice()
					: allItems.filter(function (item) {
							return groupOf(item) === filter;
					  });

			allItems.forEach(function (item) {
				var idx = visibleItems.indexOf(item);
				if (idx !== -1 && idx < shown) item.removeAttribute("hidden");
				else item.setAttribute("hidden", "");
			});

			if (countSpan) countSpan.textContent = "(" + Math.min(shown, visibleItems.length) + " of " + visibleItems.length + ")";
			if (moreBtn) {
				if (shown < visibleItems.length) moreBtn.removeAttribute("hidden");
				else moreBtn.setAttribute("hidden", "");
			}
		}

		tabs.forEach(function (tab) {
			tab.addEventListener("click", function () {
				filter = tab.getAttribute("data-gallery-filter") || "all";
				shown = 24;
				tabs.forEach(function (t) {
					var on = t === tab;
					t.setAttribute("aria-pressed", on ? "true" : "false");
					swapClasses(t, on ? TAB_ON : TAB_OFF, on ? TAB_OFF : TAB_ON);
				});
				apply();
			});
		});

		if (moreBtn) {
			moreBtn.addEventListener("click", function () {
				shown += 24;
				apply();
			});
		}

		/* Horizontal scroll arrows on the tab strip. */
		if (tabsScroller && (arrowLeft || arrowRight)) {
			var ARROW_HIDDEN = ["pointer-events-none", "opacity-0"];
			var updateArrows = function () {
				var el = tabsScroller;
				var canLeft = el.scrollLeft > 1;
				var canRight = el.scrollLeft < el.scrollWidth - el.clientWidth - 1;
				if (arrowLeft) swapClasses(arrowLeft, canLeft ? ["opacity-100"] : ARROW_HIDDEN, canLeft ? ARROW_HIDDEN : ["opacity-100"]);
				if (arrowRight) swapClasses(arrowRight, canRight ? ["opacity-100"] : ARROW_HIDDEN, canRight ? ARROW_HIDDEN : ["opacity-100"]);
			};
			updateArrows();
			tabsScroller.addEventListener("scroll", updateArrows, { passive: true });
			window.addEventListener("resize", updateArrows);
			if (arrowLeft) {
				arrowLeft.addEventListener("click", function () {
					tabsScroller.scrollBy({ left: -240, behavior: "smooth" });
				});
			}
			if (arrowRight) {
				arrowRight.addEventListener("click", function () {
					tabsScroller.scrollBy({ left: 240, behavior: "smooth" });
				});
			}
		}

		/* ---- Lightbox ------------------------------------------------- */

		var lightbox = null;
		var lightboxIndex = 0;
		var lastFocused = null;
		var lightboxLocked = false;

		function buildLightbox() {
			var el = document.createElement("div");
			el.className = "fixed inset-0 z-[90] flex items-center justify-center bg-navy-950/94 p-4 backdrop-blur-sm";
			el.setAttribute("role", "dialog");
			el.setAttribute("aria-modal", "true");
			el.setAttribute("aria-label", "Photograph viewer");
			el.innerHTML =
				'<button type="button" data-lb-close aria-label="Close" class="absolute top-5 right-5 grid h-11 w-11 shrink-0 min-h-[44px] min-w-[44px] place-items-center rounded-full border border-white/25 text-white transition-colors hover:bg-white/10">' +
				'<svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"></path></svg></button>' +
				'<button type="button" data-lb-prev aria-label="Previous" class="absolute left-4 grid h-12 w-12 shrink-0 min-h-[44px] min-w-[44px] place-items-center rounded-full border border-white/25 text-white transition-colors hover:bg-white/10 sm:left-8">' +
				'<svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>' +
				'<figure class="max-h-full"><img src="" alt="" class="mx-auto max-h-[78vh] w-auto rounded-xl object-contain" />' +
				'</figure>' +
				'<button type="button" data-lb-next aria-label="Next" class="absolute right-4 grid h-12 w-12 shrink-0 min-h-[44px] min-w-[44px] place-items-center rounded-full border border-white/25 text-white transition-colors hover:bg-white/10 sm:right-8">' +
				'<svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>';
			return el;
		}

		function paint() {
			if (!lightbox) return;
			var item = visibleItems[lightboxIndex];
			if (!item) return;
			var img = item.querySelector("img");
			var lbImg = lightbox.querySelector("img");
			if (img && lbImg) {
				lbImg.setAttribute("src", img.currentSrc || img.getAttribute("src") || "");
				lbImg.setAttribute("alt", img.getAttribute("alt") || "");
			}
		}

		function move(step) {
			if (!visibleItems.length) return;
			lightboxIndex = (lightboxIndex + step + visibleItems.length) % visibleItems.length;
			paint();
		}

		function onLightboxKey(e) {
			if (e.key === "Escape") closeLightbox();
			else if (e.key === "ArrowRight") move(1);
			else if (e.key === "ArrowLeft") move(-1);
			else if (e.key === "Tab") {
				/* Focus trap: cycle through the three controls. */
				var focusable = $$("button", lightbox);
				if (!focusable.length) return;
				var pos = focusable.indexOf(document.activeElement);
				e.preventDefault();
				var nextPos = e.shiftKey
					? (pos <= 0 ? focusable.length - 1 : pos - 1)
					: (pos === -1 || pos === focusable.length - 1 ? 0 : pos + 1);
				focusable[nextPos].focus();
			}
		}

		function openLightbox(index) {
			lightboxIndex = index;
			lastFocused = document.activeElement;
			if (!lightbox) {
				lightbox = buildLightbox();
				lightbox.addEventListener("click", function (e) {
					if (e.target === lightbox) closeLightbox();
				});
				$("[data-lb-close]", lightbox).addEventListener("click", closeLightbox);
				$("[data-lb-prev]", lightbox).addEventListener("click", function () { move(-1); });
				$("[data-lb-next]", lightbox).addEventListener("click", function () { move(1); });
			}
			document.body.appendChild(lightbox);
			lockScroll();
			lightboxLocked = true;
			window.addEventListener("keydown", onLightboxKey);
			paint();
			var closeB = $("[data-lb-close]", lightbox);
			if (closeB) closeB.focus();
		}

		function closeLightbox() {
			if (!lightbox) return;
			lightbox.remove();
			lightbox = null;
			if (lightboxLocked) {
				lightboxLocked = false;
				unlockScroll();
			}
			window.removeEventListener("keydown", onLightboxKey);
			if (lastFocused && lastFocused.focus) lastFocused.focus();
		}

		allItems.forEach(function (item) {
			item.addEventListener("click", function () {
				var idx = visibleItems.indexOf(item);
				if (idx === -1) return;
				openLightbox(idx);
			});
		});

		apply();
	}

	/* ------------------------------------------------------------------ *
	 * 8. Social feed (Curator.io)
	 * ------------------------------------------------------------------ */

	var CURATOR_SCRIPT_ID = "curator-feed-341f05be-b563-4071-9aa1-fe57d57ce790";
	var CURATOR_SCRIPT_URL = "https://cdn.curator.io/published/341f05be-b563-4071-9aa1-fe57d57ce790.js";

	function initSocialFeed() {
		var feedSection = document.getElementById("social-feed");
		if (!feedSection) return;
		var container = document.getElementById("curator-feed-default-feed-layout");
		if (!container) return;

		/* --- Script injection (idempotent) ----------------------------- */
		try {
			var existing = document.getElementById(CURATOR_SCRIPT_ID);
			if (existing && existing.dataset.loaded === "true") existing.remove();

			var pending = document.getElementById(CURATOR_SCRIPT_ID);
			var script = pending;
			if (!script) {
				script = document.createElement("script");
				script.id = CURATOR_SCRIPT_ID;
				script.src = CURATOR_SCRIPT_URL;
				script.async = true;
				script.charset = "UTF-8";
				document.body.appendChild(script);
			}

			var failedShown = false;
			var showFallback = function () {
				if (failedShown) return;
				failedShown = true;
				if ($("[data-curator-fallback]", container.parentElement || container)) return;
				var p = document.createElement("p");
				p.setAttribute("role", "status");
				p.className = "mt-4 text-sm text-[#4B5563]";
				p.setAttribute("data-curator-fallback", "");
				p.innerHTML =
					'The social feed is temporarily unavailable. You can still follow updates on <a href="https://facebook.com/MMMPakOfficial" target="_blank" rel="noreferrer" class="font-semibold text-[#075BD6] underline">MMM&#39;s Facebook page</a>.';
				container.parentNode.insertBefore(p, container.nextSibling);
			};

			script.addEventListener("load", function () {
				script.dataset.loaded = "true";
			});
			script.addEventListener("error", showFallback);
			if (script.dataset.loaded === "true" && script.getAttribute("src") !== CURATOR_SCRIPT_URL) {
				/* loaded elsewhere — nothing to do */
			}
		} catch (e) {
			/* a blocked CDN must never throw */
		}

		/* --- Mobile post culling + single load more ------------------- */
		try {
			if (typeof window.matchMedia !== "function") return;
			var mobileQuery = window.matchMedia("(max-width: 639px)");
			var wasMobile = mobileQuery.matches;
			var mobileVisiblePostCount = 2;

			var loadMoreWrap = container.parentElement ? container.parentElement.querySelector(".mmm-curator-load-more-container") : null;
			if (!loadMoreWrap) {
				loadMoreWrap = document.createElement("div");
				loadMoreWrap.className = "mmm-curator-load-more-container";
				loadMoreWrap.hidden = true;
				var btn = document.createElement("button");
				btn.type = "button";
				btn.className = "mmm-curator-load-more";
				btn.textContent = "Load More";
				loadMoreWrap.appendChild(btn);
				container.parentNode.insertBefore(loadMoreWrap, container.nextSibling);
			}
			var loadMoreBtn = loadMoreWrap.querySelector("button");

			function getPosts() {
				var feed = container.querySelector(".crt-feed");
				if (!feed) return [];
				return $$(".crt-grid-post, .crt-post", feed);
			}

			function getCuratorLoadMore() {
				return $$("button, a, [role='button']", container).filter(function (control) {
					return /load more/i.test(control.textContent || "");
				});
			}

			var framePending = false;
			function updatePosts() {
				if (framePending) return;
				framePending = true;
				requestAnimationFrame(function () {
					framePending = false;
					feedSection.classList.toggle("mmm-curator-mobile", mobileQuery.matches);
					var posts = getPosts();
					posts.forEach(function (post, index) {
						if (mobileQuery.matches && index >= mobileVisiblePostCount) post.setAttribute("hidden", "");
						else post.removeAttribute("hidden");
					});
					var curatorButtons = getCuratorLoadMore();
					curatorButtons.forEach(function (control) {
						if (mobileQuery.matches) {
							control.setAttribute("data-mmm-load-more-hidden", "");
							control.style.setProperty("display", "none", "important");
						} else if (control.hasAttribute("data-mmm-load-more-hidden")) {
							control.removeAttribute("data-mmm-load-more-hidden");
							control.style.removeProperty("display");
						}
					});
					var hasMore = posts.length > mobileVisiblePostCount || curatorButtons.some(function (control) {
						return !control.disabled && control.getAttribute("aria-disabled") !== "true";
					});
					loadMoreWrap.hidden = !mobileQuery.matches || !hasMore;
					if (loadMoreBtn) loadMoreBtn.disabled = !hasMore;
				});
			}

			function handleViewportChange() {
				if (mobileQuery.matches && !wasMobile) mobileVisiblePostCount = 2;
				wasMobile = mobileQuery.matches;
				updatePosts();
			}

			if (typeof MutationObserver !== "undefined") {
				var observer = new MutationObserver(updatePosts);
				observer.observe(feedSection, { childList: true, subtree: true });
			}
			if (typeof mobileQuery.addEventListener === "function") {
				mobileQuery.addEventListener("change", handleViewportChange);
			}
			updatePosts();

			if (loadMoreBtn) {
				loadMoreBtn.addEventListener("click", function () {
					var posts = getPosts();
					mobileVisiblePostCount += 2;
					posts.forEach(function (post, index) {
						if (index >= mobileVisiblePostCount) post.setAttribute("hidden", "");
						else post.removeAttribute("hidden");
					});
					getCuratorLoadMore().forEach(function (control) {
						if (!control.disabled && control.getAttribute("aria-disabled") !== "true") control.click();
					});
					updatePosts();
				});
			}
		} catch (e) {
			/* feed culling is cosmetic — never break the page over it */
		}
	}

	/* ------------------------------------------------------------------ *
	 * 9a. Zakat calculator
	 * ------------------------------------------------------------------ */

	var ZAKAT_RATE = 0.025;
	var SILVER_NISAB_TOLA = 52.5;
	var GOLD_NISAB_TOLA = 7.5;
	var TROY_OZ_PER_TOLA = 11.6638 / 31.1035; /* 1 tola = 11.6638 g; troy oz = 31.1035 g */

	function formatPKR(value) {
		return new Intl.NumberFormat("en-PK", {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2,
		}).format(value);
	}

	function initZakat() {
		var input = document.getElementById("zakat-amount");
		if (!input) return;
		var form = input.closest("form");
		if (!form) return;

		var errorEl = $("[data-zakat-error]") || document.getElementById("zakat-error");
		var resultEl = $("[data-zakat-result]");
		var resultAmount = resultEl ? $("[data-zakat-result-amount]", resultEl) : null;
		var resultNote = resultEl ? $("[data-zakat-result-note]", resultEl) : null;

		var nisab = null;

		function setError(message) {
			if (errorEl) {
				errorEl.removeAttribute("hidden");
				/* Keep any server-rendered icon; replace only the text. */
				Array.prototype.slice.call(errorEl.childNodes).forEach(function (node) {
					if (node.nodeType === 3) node.remove();
				});
				errorEl.appendChild(document.createTextNode(message));
			}
			input.setAttribute("aria-invalid", "true");
			if (errorEl && errorEl.id) input.setAttribute("aria-describedby", errorEl.id);
			if (resultEl) resultEl.setAttribute("hidden", "");
		}

		function clearError() {
			if (errorEl) errorEl.setAttribute("hidden", "");
			input.removeAttribute("aria-invalid");
			input.removeAttribute("aria-describedby");
		}

		function calculate() {
			var trimmed = input.value.trim();

			if (trimmed === "") {
				setError("Please enter the amount on which you want to calculate Zakat.");
				return;
			}

			var value = Number(trimmed);

			if (Number.isNaN(value)) {
				setError("Please enter a valid number. Only digits are accepted.");
				return;
			}
			if (value < 0) {
				setError("The amount cannot be negative. Please enter a positive value.");
				return;
			}
			if (value === 0) {
				setError("The amount cannot be zero. Please enter a value greater than zero.");
				return;
			}
			if (nisab !== null && value < nisab) {
				setError(
					"Your amount is below the Nisab threshold of Rs. " +
						formatPKR(nisab) +
						" (52.5 tola of silver). Zakat is not obligatory on amounts below Nisab."
				);
				return;
			}

			clearError();
			if (resultEl) resultEl.removeAttribute("hidden");
			if (resultAmount) resultAmount.textContent = "Rs. " + formatPKR(value * ZAKAT_RATE);
			if (resultNote)
				resultNote.textContent =
					"This is 2.5% of your Zakat-eligible amount of Rs. " + formatPKR(value) + ".";
		}

		form.addEventListener("submit", function (e) {
			e.preventDefault();
			calculate();
		});

		input.addEventListener("keydown", function (e) {
			if (e.key === "Enter") {
				e.preventDefault();
				calculate();
			}
		});

		/* Live silver & gold rates. The original fetches these on mount; here
		   the fetch is opt-in via data-zakat-live so pages make no third-party
		   requests unless the template asks for it. */
		var live = form.hasAttribute("data-zakat-live") || document.querySelector("[data-zakat-live]");
		if (live && typeof window.fetch === "function") {
			var statusEl = $("[data-zakat-rate-status]");
			var ratesGrid = $("[data-zakat-rates]");
			var rateErrorEl = $("[data-zakat-rate-error]");

			try {
				Promise.all([
					fetch("https://api.gold-api.com/price/XAG"),
					fetch("https://api.gold-api.com/price/XAU"),
					fetch("https://open.er-api.com/v6/latest/USD"),
				])
					.then(function (responses) {
						return Promise.all(responses.map(function (r) { return r.json(); }));
					})
					.then(function (data) {
						var silverUsdPerOz = data[0] && data[0].price;
						var goldUsdPerOz = data[1] && data[1].price;
						var usdToPkr = data[2] && data[2].rates && data[2].rates.PKR;
						if (!silverUsdPerOz || !goldUsdPerOz || !usdToPkr) throw new Error("Missing rate data");

						var silverTola = silverUsdPerOz * TROY_OZ_PER_TOLA * usdToPkr;
						var goldTola = goldUsdPerOz * TROY_OZ_PER_TOLA * usdToPkr;
						nisab = silverTola * SILVER_NISAB_TOLA;
						var goldNisab = goldTola * GOLD_NISAB_TOLA;

						var cells = {
							"[data-zakat-silver]": silverTola,
							"[data-zakat-gold]": goldTola,
							"[data-zakat-nisab]": nisab,
							"[data-zakat-gold-nisab]": goldNisab,
						};
						Object.keys(cells).forEach(function (sel) {
							var el = $(sel);
							if (el) el.textContent = "Rs. " + formatPKR(cells[sel]);
						});
						if (statusEl) statusEl.setAttribute("hidden", "");
						if (ratesGrid) ratesGrid.removeAttribute("hidden");
					})
					.catch(function () {
						if (statusEl) statusEl.setAttribute("hidden", "");
						if (rateErrorEl) {
							rateErrorEl.textContent = "Could not fetch today's silver and gold rates.";
							rateErrorEl.removeAttribute("hidden");
						}
					});
			} catch (e) {
				if (statusEl) statusEl.setAttribute("hidden", "");
			}
		}
	}

	/* ------------------------------------------------------------------ *
	 * 9b. Donation planner
	 * ------------------------------------------------------------------ */

	function initDonationPlanner() {
		var root = $("[data-donation-planner]");
		if (!root) return;

		var causeBtns = $$("[data-cause]", root);
		if (!causeBtns.length) return;

		var causes = [];
		causeBtns.forEach(function (btn, i) {
			var cost = parseFloat(btn.getAttribute("data-unit-cost"));
			if (!isFinite(cost) || cost <= 0) return;
			var unitName = btn.getAttribute("data-unit-name") || "";
			causes.push({
				key: btn.getAttribute("data-cause") || String(i),
				btn: btn,
				label: btn.getAttribute("data-label") || (btn.textContent || "").trim(),
				unitCost: cost,
				unitName: unitName,
				unitPlural: btn.getAttribute("data-unit-plural") || unitName,
				note: btn.getAttribute("data-note") || "",
			});
		});
		if (!causes.length) return;

		var presets = $$("[data-preset-amount]", root);
		var custom = $("[data-custom-amount]", root);
		var heading = $("[data-out-heading]", root);
		var unitsEl = $("[data-out-units]", root);
		var unitLabelEl = $("[data-out-unit-label]", root);
		var noteEl = $("[data-out-note]", root);
		var zeroEl = $("[data-out-zero]", root);
		var ctaEl = $("[data-out-cta]", root);

		var whatsappBase = root.getAttribute("data-whatsapp-base") || "";
		if (!whatsappBase && ctaEl) {
			var rawHref = ctaEl.getAttribute("href") || "";
			whatsappBase = rawHref.split("?")[0];
		}

		var amount = 9000;
		var customValue = "";
		var causeKey = causes[0].key;

		var UNIT_SIZES = ["text-[3.4rem]", "text-[2.7rem]", "text-[2.3rem]", "text-[1.9rem]", "text-[1.5rem]"];

		var money = function (n) {
			return "PKR " + n.toLocaleString("en-PK");
		};

		function currentCause() {
			for (var i = 0; i < causes.length; i++) {
				if (causes[i].key === causeKey) return causes[i];
			}
			return causes[0];
		}

		function render() {
			var cause = currentCause();
			var units = Math.floor(amount / cause.unitCost);
			var unitsText = units.toLocaleString("en-GB");

			if (heading) heading.textContent = money(amount) + " towards " + cause.label.toLowerCase();

			if (unitsEl) {
				var tier =
					unitsText.length >= 12 ? 4 :
					unitsText.length >= 10 ? 3 :
					unitsText.length >= 8 ? 2 :
					unitsText.length >= 6 ? 1 : 0;
				swapClasses(unitsEl, [UNIT_SIZES[tier]], UNIT_SIZES.filter(function (c, i) { return i !== tier; }));
				unitsEl.textContent = unitsText;
			}
			if (unitLabelEl) unitLabelEl.textContent = units === 1 ? cause.unitName : cause.unitPlural;
			if (noteEl) noteEl.textContent = cause.note;
			if (zeroEl) {
				if (units === 0) zeroEl.removeAttribute("hidden");
				else zeroEl.setAttribute("hidden", "");
			}

			if (ctaEl && whatsappBase) {
				var message = encodeURIComponent(
					"Assalamu alaikum. I would like to donate " +
						money(amount) +
						" towards " +
						cause.label +
						". Please confirm the transfer details."
				);
				ctaEl.setAttribute("href", whatsappBase + "?text=" + message);
			}

			causeBtns.forEach(function (btn) {
				var on = (btn.getAttribute("data-cause") || "") === causeKey;
				btn.setAttribute("aria-pressed", on ? "true" : "false");
				swapClasses(
					btn,
					on ? ["border-blue-brand", "bg-blue-soft"] : ["border-line", "hover:border-navy-300"],
					on ? ["border-line", "hover:border-navy-300"] : ["border-blue-brand", "bg-blue-soft"]
				);
			});

			presets.forEach(function (btn) {
				var value = parseFloat(btn.getAttribute("data-preset-amount"));
				var on = amount === value && customValue === "";
				btn.setAttribute("aria-pressed", on ? "true" : "false");
				swapClasses(
					btn,
					on ? ["bg-[#046BD2]", "text-white", "shadow-sm"] : ["bg-slate-100", "text-[#334155]", "hover:bg-[#e53935]", "hover:text-white"],
					on ? ["bg-slate-100", "text-[#334155]", "hover:bg-[#e53935]", "hover:text-white"] : ["bg-[#046BD2]", "text-white", "shadow-sm"]
				);
			});
		}

		causeBtns.forEach(function (btn) {
			btn.addEventListener("click", function () {
				causeKey = btn.getAttribute("data-cause") || causeKey;
				render();
			});
		});

		presets.forEach(function (btn) {
			btn.addEventListener("click", function () {
				var value = parseFloat(btn.getAttribute("data-preset-amount"));
				if (!isFinite(value)) return;
				amount = value;
				customValue = "";
				if (custom) custom.value = "";
				render();
			});
		});

		if (custom) {
			custom.addEventListener("input", function () {
				customValue = custom.value;
				var n = Number(custom.value);
				if (Number.isFinite(n) && n > 0) amount = n;
				render();
			});
		}

		/* Copy-to-clipboard buttons (account title / IBAN / number). */
		$$("[data-copy-value]", root).forEach(function (btn) {
			btn.addEventListener("click", function () {
				var text = btn.getAttribute("data-copy-value") || "";
				var label = btn.querySelector("span") || btn;
				var original = label.textContent;
				try {
					if (!navigator.clipboard || !navigator.clipboard.writeText) return;
					navigator.clipboard.writeText(text).then(
						function () {
							label.textContent = "Copied";
							window.setTimeout(function () {
								label.textContent = original;
							}, 1800);
						},
						function () {
							/* failed — stay on "Copy" */
						}
					);
				} catch (e) {
					/* clipboard unavailable */
				}
			});
		});

		render();
	}

	/* ------------------------------------------------------------------ *
	 * 10. Membership form
	 * ------------------------------------------------------------------ */

	var MEMBER_PROFESSIONAL = ["1", "2", "3", "4", "5", "6"];
	var MEMBER_STUDENT = ["7", "8"];

	var SECTION_ACTIVE = ["border-[#075BD6]/30", "bg-white", "shadow-xs"];
	var SECTION_INACTIVE = ["border-[#DCE2EA]", "bg-slate-50/70", "opacity-80"];
	var CARD_ACTIVE = ["border-[#075BD6]", "bg-[#F3F7FF]", "ring-2", "ring-[#075BD6]/30", "shadow-xs"];
	var CARD_INACTIVE = ["border-[#DCE2EA]", "bg-white", "hover:border-slate-300", "hover:bg-slate-50/50"];
	var CHIP_ACTIVE = ["border-[#075BD6]", "bg-[#F3F7FF]", "font-bold", "text-[#075BD6]"];
	var CHIP_INACTIVE = ["border-[#DCE2EA]", "bg-white", "hover:bg-slate-50", "text-[#334155]"];
	var FIELD_ERROR = ["border-[#EF3B19]", "bg-red-50/20"];
	var FIELD_OK = ["border-[#DCE2EA]", "bg-white"];

	function initMembership() {
		var form = $("[data-membership-form]") || document.getElementById("membership-form-element");
		if (!form) return;

		var catRadios = $$('input[name="membershipCategory"]', form);
		var submitBtn = form.querySelector('button[type="submit"]');
		var submitBtnHtml = submitBtn ? submitBtn.innerHTML : "";

		function field(id) {
			return document.getElementById(id) || form.querySelector('[name="' + id + '"]');
		}

		function val(id) {
			var el = field(id);
			return el ? String(el.value || "").trim() : "";
		}

		function currentCategory() {
			for (var i = 0; i < catRadios.length; i++) {
				if (catRadios[i].checked) return catRadios[i].value;
			}
			return "1";
		}

		function matchesList(attr, value) {
			return (attr || "").split(",").map(function (s) { return s.trim(); }).indexOf(value) !== -1;
		}

		function applyCategory() {
			var cat = currentCategory();

			$$("[data-when-category]", form).forEach(function (el) {
				if (matchesList(el.getAttribute("data-when-category"), cat)) el.removeAttribute("hidden");
				else el.setAttribute("hidden", "");
			});

			$$("[data-dim-when-category]", form).forEach(function (el) {
				var dim = matchesList(el.getAttribute("data-dim-when-category"), cat);
				swapClasses(el, dim ? SECTION_INACTIVE : SECTION_ACTIVE, dim ? SECTION_ACTIVE : SECTION_INACTIVE);
			});

			catRadios.forEach(function (radio) {
				var label = radio.id ? form.querySelector('label[for="' + radio.id + '"]') : null;
				if (!label) return;
				swapClasses(label, radio.checked ? CARD_ACTIVE : CARD_INACTIVE, radio.checked ? CARD_INACTIVE : CARD_ACTIVE);
			});

			applyHowKnown();
		}

		function applyHowKnown() {
			var checked = form.querySelector('input[name="howKnown"]:checked');
			var howKnown = checked ? checked.value : "";

			$$("[data-when-how-known]", form).forEach(function (el) {
				if (matchesList(el.getAttribute("data-when-how-known"), howKnown)) el.removeAttribute("hidden");
				else el.setAttribute("hidden", "");
			});

			$$('input[name="howKnown"]', form).forEach(function (radio) {
				var label = radio.id ? form.querySelector('label[for="' + radio.id + '"]') : null;
				if (!label) return;
				swapClasses(label, radio.checked ? CHIP_ACTIVE : CHIP_INACTIVE, radio.checked ? CHIP_INACTIVE : CHIP_ACTIVE);
			});
		}

		catRadios.forEach(function (radio) {
			radio.addEventListener("change", applyCategory);
		});

		$$('input[name="howKnown"]', form).forEach(function (radio) {
			radio.addEventListener("change", applyHowKnown);
		});

		/* Volunteer interest chips share the same highlight classes. Only
		   labels that already carry the chip chrome are touched, so the
		   consent checkbox keeps its plain presentation. */
		$$('input[type="checkbox"]', form).forEach(function (box) {
			if (box.name === "howKnown") return;
			var label = box.id ? form.querySelector('label[for="' + box.id + '"]') : null;
			if (!label || label.className.indexOf("border-[#DCE2EA]") === -1) return;
			box.addEventListener("change", function () {
				swapClasses(label, box.checked ? CHIP_ACTIVE : CHIP_INACTIVE, box.checked ? CHIP_INACTIVE : CHIP_ACTIVE);
			});
		});

		/* File inputs echo the chosen file name like the React state did. */
		$$('input[type="file"]', form).forEach(function (fileInput) {
			fileInput.addEventListener("change", function () {
				var wrap = fileInput.closest("[data-file-field]") || fileInput.closest("div");
				if (!wrap) return;
				var display = $("[data-file-name]", wrap);
				if (!display) return;
				if (fileInput.files && fileInput.files.length) {
					display.textContent = "Attached: " + fileInput.files[0].name;
					display.removeAttribute("hidden");
				} else {
					display.textContent = "";
					display.setAttribute("hidden", "");
				}
			});
		});

		/* Default declaration date to today, as the React state did. */
		var declDate = field("declarationDate");
		if (declDate && !declDate.value) {
			declDate.value = new Date().toISOString().split("T")[0];
		}

		/* ---- Validation (verbatim port of MembershipForm.validate) ----- */

		function validate() {
			var errs = {};
			var category = currentCategory();
			var isProfessional = MEMBER_PROFESSIONAL.indexOf(category) !== -1;
			var isStudent = MEMBER_STUDENT.indexOf(category) !== -1;
			var isInternational = category === "6";

			if (!val("fullName")) errs.fullName = "Full name (as per CNIC) is required.";
			if (!val("cnic")) errs.cnic = "CNIC / B-Form / NICOP No. is required.";
			if (!val("dob")) errs.dob = "Date of birth is required.";
			if (!val("gender")) errs.gender = "Please select your gender.";
			if (!val("nationality")) errs.nationality = "Nationality is required.";
			if (!val("mobileNumber")) errs.mobileNumber = "Mobile number is required.";
			if (!val("whatsappNumber")) errs.whatsappNumber = "WhatsApp number is required.";
			if (!val("email")) {
				errs.email = "Email address is required.";
			} else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val("email"))) {
				errs.email = "Please enter a valid email address.";
			}
			if (!val("address")) errs.address = "Current mailing address is required.";
			if (!val("city")) errs.city = "City is required.";
			if (!val("district")) errs.district = "District is required.";
			if (!val("province")) errs.province = "Please select your province.";

			if (isInternational) {
				if (!val("countryResidence"))
					errs.countryResidence = "Country of residence is required for international members.";
				if (!val("cityResidence"))
					errs.cityResidence = "City of residence is required for international members.";
			}

			if (isProfessional) {
				if (!val("basicQualification"))
					errs.basicQualification = "Basic qualification is required for healthcare professionals.";
				if (!val("institution")) errs.institution = "Graduating institution is required.";
				if (!val("yearGraduation")) errs.yearGraduation = "Year of graduation is required.";

				if (category === "1" && !val("pmdcRegNo")) {
					errs.pmdcRegNo = "PMDC Registration Number is required.";
				} else if (category === "2" && !val("pptaNo")) {
					errs.pptaNo = "PPTA Membership Number is required.";
				} else if (category === "3" && !val("alliedRegNo")) {
					errs.alliedRegNo = "Registration / Certification Number is required.";
				} else if (category === "4" && !val("pncRegNo")) {
					errs.pncRegNo = "PNC Registration Number is required.";
				} else if (category === "5" && !val("ppcRegNo")) {
					errs.ppcRegNo = "PPC Registration Number is required.";
				} else if (category === "6") {
					if (!val("intlCountryRegulator")) errs.intlCountryRegulator = "Foreign regulator & country is required.";
					if (!val("intlRegLicenceNo")) errs.intlRegLicenceNo = "Registration / licence number is required.";
					if (!val("intlOriginProof")) errs.intlOriginProof = "Proof of Pakistani origin is required.";
				}
			}

			if (isStudent) {
				if (!val("studentInstitution"))
					errs.studentInstitution = "Institution name & campus is required for student members.";
				if (!val("studentProgram")) errs.studentProgram = "Academic programme is required.";
				if (!val("studentYearOfStudy")) errs.studentYearOfStudy = "Current year of study is required.";
			}

			if (!val("nearestChapter"))
				errs.nearestChapter = "Please specify your nearest MMM Chapter / Activity Centre.";

			var agreed = field("declarationAgreed");
			if (!agreed || !agreed.checked) {
				errs.declarationAgreed = "You must agree to the declaration and code of conduct to apply.";
			}
			if (!val("applicantSignature"))
				errs.applicantSignature = "Applicant signature (full legal name) is required.";
			if (!val("declarationDate")) errs.declarationDate = "Date of declaration is required.";

			return errs;
		}

		function showError(key, message) {
			var input = field(key);
			if (input) {
				input.setAttribute("aria-invalid", "true");
				/* Only restyle actual text inputs — checkboxes keep their chrome. */
				if (input.tagName !== "INPUT" || (input.type !== "checkbox" && input.type !== "radio")) {
					swapClasses(input, FIELD_ERROR, FIELD_OK);
				}
			}
			var slot = form.querySelector('[data-error-for="' + key + '"]');
			if (!slot && input) {
				slot = document.createElement("p");
				slot.className = "mt-1 text-xs text-[#EF3B19]";
				slot.setAttribute("data-error-for", key);
				slot.hidden = true;
				var wrap = input.closest("div");
				if (wrap) wrap.appendChild(slot);
				else input.parentNode && input.parentNode.appendChild(slot);
			}
			if (slot) {
				slot.textContent = message;
				slot.removeAttribute("hidden");
			}
		}

		function clearErrors() {
			$$("[aria-invalid]", form).forEach(function (el) {
				el.removeAttribute("aria-invalid");
				if (el.tagName !== "INPUT" || (el.type !== "checkbox" && el.type !== "radio")) {
					swapClasses(el, FIELD_OK, FIELD_ERROR);
				}
			});
			$$("[data-error-for]", form).forEach(function (slot) {
				slot.textContent = "";
				slot.setAttribute("hidden", "");
			});
			var box = $("[data-form-errors]", form);
			if (box) box.setAttribute("hidden", "");
		}

		function showGlobalErrors(errs) {
			var keys = Object.keys(errs);
			if (!keys.length) return;
			var box = $("[data-form-errors]", form);
			var list = box ? $("[data-form-errors-list]", box) : null;
			if (!box || !list) {
				/* Build the summary box above the submit control, as React did. */
				box = document.createElement("div");
				box.className = "rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-[#EF3B19]";
				box.setAttribute("data-form-errors", "");
				box.innerHTML =
					'<div class="flex items-center gap-2 font-bold">' +
					'<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>' +
					"<span>Please correct the highlighted fields before submitting:</span></div>" +
					'<ul data-form-errors-list class="mt-2 list-inside list-disc text-xs space-y-0.5"></ul>';
				list = $("[data-form-errors-list]", box);
				var anchor = submitBtn && submitBtn.parentElement ? submitBtn.parentElement : submitBtn;
				if (anchor && anchor.parentElement) anchor.parentElement.insertBefore(box, anchor);
				else form.appendChild(box);
			}
			list.innerHTML = "";
			keys.slice(0, 4).forEach(function (k) {
				var li = document.createElement("li");
				li.textContent = errs[k];
				list.appendChild(li);
			});
			if (keys.length > 4) {
				var li = document.createElement("li");
				li.textContent = "...and " + (keys.length - 4) + " more field(s) require attention.";
				list.appendChild(li);
			}
			box.removeAttribute("hidden");
		}

		/* ---- Success view (built locally — the original never POSTs) --- */

		function categoryTitle(cat) {
			for (var i = 0; i < catRadios.length; i++) {
				if (catRadios[i].value === cat) {
					var ds = catRadios[i].getAttribute("data-title");
					if (ds) return ds;
					var label = catRadios[i].id ? form.querySelector('label[for="' + catRadios[i].id + '"]') : null;
					if (label) {
						var text = (label.textContent || "").trim();
						var stripped = text.replace(new RegExp("^" + cat.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + "\\.\\s*"), "");
						if (stripped) return stripped;
					}
					return "Member";
				}
			}
			return "Member";
		}

		/* A mailto: link carrying the completed application, so the applicant can
		   actually send it. Nothing is posted by this page. */
		function mailtoHref(data) {
			var lines = [
				"Assalamu alaikum,",
				"",
				"Please find my MMM Pakistan membership application below.",
				"Reference: " + data.submissionRef,
				"Membership category: " + data.categoryName,
				"Full name: " + data.fullName,
				"CNIC / B-Form / NICOP: " + data.cnic,
				"Email: " + data.email,
				"Mobile: " + data.mobileNumber,
				"City / Province: " + data.city + ", " + data.province,
				"Date: " + data.date,
				"",
				"I will attach the supporting documents (CNIC copy, photograph and degree or student ID) to this email.",
			];
			return (
				"mailto:muslimmedics@gmail.com?subject=" +
				encodeURIComponent("MMM Membership Application " + data.submissionRef) +
				"&body=" +
				encodeURIComponent(lines.join("\n"))
			);
		}

		function buildSuccess(data) {
			var wrap = document.createElement("div");
			wrap.className = "rounded-3xl border border-emerald-200 bg-emerald-50/70 p-7 sm:p-10 shadow-soft";
			wrap.innerHTML =
				'<div class="flex flex-col items-center text-center">' +
				'<div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-md">' +
				'<svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></div>' +
				'<span class="mt-4 rounded-full bg-emerald-100 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-emerald-800">Application Ready to Send</span>' +
				'<h3 class="mt-3 font-display text-2xl sm:text-3xl font-black text-[#0A1020]">Jazakallah Khair, ' + escapeHtml(data.fullName) + "!</h3>" +
				'<p class="mt-3 max-w-xl text-[1rem] leading-relaxed text-[#4B5563]">Your membership application for <span class="font-semibold text-[#0A1020]">' +
				escapeHtml(data.categoryName) +
				'</span> is complete and ready to send, with reference ID <span class="font-mono font-bold text-[#075BD6]">' +
				escapeHtml(data.submissionRef) +
				'</span>. This website does not transmit it for you — send it to the membership secretariat to finish applying.</p>' +
				'<div class="mt-8 w-full max-w-xl overflow-hidden rounded-2xl border border-emerald-200 bg-white p-6 text-left shadow-xs">' +
				'<h4 class="border-b border-slate-100 pb-3 font-display text-base font-bold text-[#0A1020]">Submission Summary</h4>' +
				'<dl class="mt-3 grid grid-cols-1 gap-2.5 text-sm sm:grid-cols-2">' +
				"<div><dt class=\"text-slate-500\">Applicant Name:</dt><dd class=\"font-semibold text-[#0A1020]\">" + escapeHtml(data.fullName) + "</dd></div>" +
				"<div><dt class=\"text-slate-500\">CNIC / Identification:</dt><dd class=\"font-semibold text-[#0A1020]\">" + escapeHtml(data.cnic) + "</dd></div>" +
				"<div><dt class=\"text-slate-500\">Membership Category:</dt><dd class=\"font-semibold text-[#0A1020]\">" + escapeHtml(data.categoryName) + "</dd></div>" +
				"<div><dt class=\"text-slate-500\">Submission Date:</dt><dd class=\"font-semibold text-[#0A1020]\">" + escapeHtml(data.date) + "</dd></div>" +
				"<div><dt class=\"text-slate-500\">Contact Email:</dt><dd class=\"font-semibold text-[#0A1020]\">" + escapeHtml(data.email) + "</dd></div>" +
				"<div><dt class=\"text-slate-500\">Location:</dt><dd class=\"font-semibold text-[#0A1020]\">" + escapeHtml(data.city) + ", " + escapeHtml(data.province) + "</dd></div>" +
				"</dl></div>" +
				'<div class="mt-6 max-w-xl rounded-xl border border-blue-100 bg-[#F0F7FF] p-4 text-left text-xs leading-relaxed text-[#334155]">' +
				'<strong class="font-bold text-[#075BD6]">Next Steps:</strong>' +
				'<p class="mt-1">Send this application to <span class="font-semibold">muslimmedics@gmail.com</span> with your CNIC copy and degree or student ID. Our membership secretariat in Johar Town, Lahore will then verify your details with the relevant council or institution.</p></div>' +
				'<div class="mt-8 flex flex-wrap justify-center gap-3 sm:gap-4 w-full">' +
				'<button type="button" data-mmm-another class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-slate-300 px-5 py-2.5 sm:px-6 sm:py-3 font-display text-[0.84rem] xs:text-sm font-bold text-[#334155] whitespace-nowrap min-h-[44px] max-w-full transition-all hover:border-[#075BD6] hover:text-[#075BD6]">Fill Another Application</button>' +
				'<a href="' + mailtoHref(data) + '" class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-5 py-2.5 sm:px-6 sm:py-3 font-display text-[0.84rem] xs:text-sm font-bold text-white whitespace-nowrap min-h-[44px] max-w-full shadow-soft transition-all hover:bg-emerald-700">' +
				'<svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>' +
				"<span>Email This Application</span></a>" +
				'<a href="/MMM_Pakistan_Membership_Application_Form.pdf" download class="inline-flex items-center justify-center gap-2 rounded-full bg-[#075BD6] px-5 py-2.5 sm:px-6 sm:py-3 font-display text-[0.84rem] xs:text-sm font-bold text-white whitespace-nowrap min-h-[44px] max-w-full shadow-soft transition-all hover:bg-[#0649B8]">' +
				'<svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>' +
				"<span>Download Offline PDF Form</span></a></div></div>";
			return wrap;
		}

		form.addEventListener("submit", function (e) {
			e.preventDefault();

			var errs = validate();
			var keys = Object.keys(errs);
			if (keys.length) {
				clearErrors();
				keys.forEach(function (k) {
					showError(k, errs[k]);
				});
				showGlobalErrors(errs);
				var first = field(keys[0]);
				if (first) {
					if (first.scrollIntoView) first.scrollIntoView({ behavior: "smooth", block: "center" });
					first.focus();
				}
				return;
			}

			clearErrors();

			if (submitBtn) {
				submitBtn.disabled = true;
				submitBtn.innerHTML =
					'<svg class="h-5 w-5 shrink-0 animate-spin text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Submitting Application...</span>';
			}

			/* Local submission simulation — the original performed no network
			   call; the success state is produced on the client. */
			window.setTimeout(function () {
				var category = currentCategory();
				var data = {
					categoryName: categoryTitle(category),
					fullName: val("fullName"),
					cnic: val("cnic"),
					email: val("email"),
					mobileNumber: val("mobileNumber"),
					city: val("city"),
					province: val("province"),
					submissionRef:
						"MMM-" +
						new Date().getFullYear() +
						"-" +
						Math.floor(100000 + Math.random() * 900000),
					date: new Date().toLocaleDateString("en-PK", {
						year: "numeric",
						month: "long",
						day: "numeric",
					}),
				};

				var success = buildSuccess(data);
				form.setAttribute("hidden", "");
				if (form.parentElement) {
					form.parentElement.insertBefore(success, form);
					if (success.scrollIntoView) success.scrollIntoView({ behavior: "smooth" });
				}

				var another = $("[data-mmm-another]", success);
				if (another) {
					another.addEventListener("click", function () {
						success.remove();
						form.removeAttribute("hidden");
						form.reset();
						if (submitBtn) {
							submitBtn.disabled = false;
							submitBtn.innerHTML = submitBtnHtml;
						}
						if (declDate && !declDate.value) {
							declDate.value = new Date().toISOString().split("T")[0];
						}
						clearErrors();
						applyCategory();
						/* Reset the visual state of chips that start unchecked. */
						$$('input[type="checkbox"]', form).forEach(function (box) {
							var label = box.id ? form.querySelector('label[for="' + box.id + '"]') : null;
							if (label && label.className.indexOf("border-[#DCE2EA]") !== -1) {
								swapClasses(label, box.checked ? CHIP_ACTIVE : CHIP_INACTIVE, box.checked ? CHIP_INACTIVE : CHIP_ACTIVE);
							}
						});
					});
				}
			}, 1200);
		});

		applyCategory();
	}

	/* ------------------------------------------------------------------ *
	 * Bootstrap
	 * ------------------------------------------------------------------ */

	function init() {
		/* Shared scroll engine + reveal + counters */
		try {
			scanReveals();
			scanCountUps();
			scheduleCheck();
		} catch (e) {
			if (window.console && console.warn) console.warn("mmm.js: reveal/counters failed", e);
		}

		var features = [
			initMegaMenu,
			initSearch,
			initDrawer,
			initBackToTop,
			initWhatsAppFloat,
			initHero,
			initGallery,
			initSocialFeed,
			initZakat,
			initDonationPlanner,
			initMembership,
		];

		features.forEach(function (fn) {
			try {
				fn();
			} catch (e) {
				if (window.console && console.warn) console.warn("mmm.js: " + fn.name + " failed", e);
			}
		});
	}

	onReady(init);
})();
