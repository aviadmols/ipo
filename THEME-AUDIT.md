# IPO Theme (wpstack-child) — Full Audit
*Generated 2026-06-21 from a 6-analyst + synthesis multi-agent audit. Analysis only — no code was changed by this report.*

> Scope: every theme-authored stylesheet, script, PHP template, include, and the enqueue/asset pipeline. Vendor libraries (bootstrap/owl/slick/splide/etc.) were noted but not audited internally.

## Executive summary

The wpstack-child theme (RTL Hebrew default, en-US + ar via WPML, depends on the absent wpstack-parent for jQuery/enqueue/the_part) is a large legacy theme carrying ~14,600 lines of hand-authored CSS across 5+ files, ~2,750 lines of non-vendor JS across 17 globally-loaded scripts, 74 page-templates + 48 parts + a stale parts_OLD backup tree, and a ~852KB uncompressed per-page asset payload. The recent consolidation made real progress (moreConcerts on Splide, ~38 admin code-manager snippets folded into ipo-custom.css/js + third-party parts, calendar scroll-to-top removed), but it preserved provenance over structure: ipo-custom.css/js are verbatim snippet concatenations that load LAST and win by force (490 !important in CSS, redefined isScrolledIntoView/throttle and duplicate-bound handlers in JS). The single highest-priority work is SECURITY: an under-gated importer with SSRF + SSL-verification-disabled, a SQL injection in get_attachment_id_by_title, an unauthenticated nopriv API proxy, and pervasive unescaped ACF output — these are confirmed in code and must be fixed first (after verifying the parent wpstack_ajax base class's nonce/cap behavior). Next are correctness bugs that break pages today (two hard JS runtime errors on every page, the silent isScrolledIntoView override, an unbalanced moreConcerts <section> in single-artist.php, $post global clobbering in single-program.php). Then comes the structural cleanup: finish owl→Splide migration to collapse to a single slider engine, dissolve ipo-custom.css/js into concern-based layers, consolidate responsive logic onto one breakpoint ladder, delete the confirmed dead code (parts_OLD, backups, dead files), and move asset loading to conditional/deferred enqueue. The work is heavily sequenced: security and runtime-error fixes are independent and immediate; owl removal must precede owl-CSS deletion; CSS/JS dissolution must precede !important reduction; nothing destructive on template version-families until live-route assignment is verified against the WP DB.

## Big-picture structural problems

- Provenance over structure: ipo-custom.css (7,330 lines / 38 snippets) and ipo-custom.js (~891 lines / ~25 IIFE blocks) are verbatim admin-snippet dumps loaded LAST. They win the cascade by brute force (490 !important in CSS) rather than by design, masking ~109+56+42 cross-file selector collisions and carrying forward per-snippet JS duplication (handlers bound 2x that fight each other).
- Three slider engines ship on every page: owlCarousel (bundled in plugins.js, 149KB, still driving 6 sliders), Slick (42KB, banner sliders), and Splide (29KB, moreConcerts + calendar). All three + their CSS load regardless of whether any slider is present. Splide is the chosen target; owl and Slick should be retired.
- No conditional or deferred loading exists in the child theme. ~852KB uncompressed (calendar, isotope, hc-sticky, animate.css 72KB, 3 slider engines, AOS/lottie/anime from public CDNs render-blocking in <head>) loads on every page including plain content pages. Asset strategy, not asset need, is the problem.
- Organized by AUTHOR/SNIPPET, not by CONCERN: style-sagi.css, style-shojib.css, two 'Aviad Css' blocks and three per-developer QA blocks inside ipo-custom.css all touch the same components (calendar, img_box, header, events-program), so changing one component requires editing 3-4 files. 255 selectors are defined more than once.
- Directional i18n is bolted on as ~263 [lang='en-US'] LTR re-statements of RTL rules instead of CSS logical properties; Arabic (the third WPML language) has ZERO dedicated rules and is likely visually unverified.
- Severe version-sprawl and dead code in the PHP layer: 5 live_streaming_page variants, 3 Lobby-new-season variants, 4 orckestraguest variants, an entire parts_OLD/ backup tree (README says safe to remove), 14 orphaned parts, numerous .bkp/.txt/0-byte files, and a fatal-risk triple declaration of `class ipo_importer`.
- Security was never hardened: the AJAX layer (importer, post-processor, API proxy) relies on the parent's wpstack_ajax base class with no visible nonce/capability checks, fetches user-supplied URLs with SSL verification off, runs an unauthenticated nopriv proxy, has a raw-SQL injection, and echoes ACF values unescaped throughout templates.
- Hard dependency on the absent wpstack-parent theme, which owns jQuery, the real wp_enqueue calls, head-vs-footer placement, versioning, and the the_part/get_part/wpstack_ajax APIs. Any defer/conditional/auth strategy must be coordinated with — and verified against — the parent, which is not in this checkout.

## Top issues (ranked)

| # | Severity | Area | Issue | Why it matters |
|---|----------|------|-------|----------------|
| 1 | critical | PHP security (includes/class-ipo-importer.php:51-65, includes/ajax/ajax_import_batch/ajax_import_batch.php:16,90) | Under-gated importer: SSRF + arbitrary remote fetch with SSL verification disabled | ipo_importer fetches user-supplied $_POST['raw_url']/['file_url'] via cURL (CURLOPT_SSL_VERIFYPEER=false) or file_get_contents, driven by ajax_import_batch which extends wpstack_ajax with no visible check_ajax_referer/current_user_can. Any caller reaching admin-ajax.php with action=ajax_import_batch can make the server fetch internal/cloud-metadata/file:// URLs and import the response as posts/attachments. SSL-off also enables MITM. Confirmed in code; gating depends on verifying the parent base class. |
| 2 | critical | PHP security (includes/wp_insert_attachment_from_url.php:14) | SQL injection in get_attachment_id_by_title (no $wpdb->prepare) | SELECT * FROM $wpdb->posts WHERE post_title = '$title' interpolates $title directly with no prepare/escaping. $title derives from the basename of importer-fetched URLs, so the SQLi is reachable via the same under-gated importer path. Textbook unparameterised query regardless of source. |
| 3 | critical | PHP security (functions.php:256-276) | Unauthenticated nopriv API proxy with upstream URL injection and SSL off | handle_fetch_presentation_data is registered for wp_ajax_nopriv with no nonce/cap, builds wp_remote_get('https://ipo.pres.global/api/presentations/{apiEventId}', sslverify=false) from $_POST['apiEventId'] (sanitize_text_field does not URL-encode, allowing path/query manipulation and limited SSRF), turns the site into an open proxy, and the else branch references undefined $body (notice + json_decode(null)). |
| 4 | high | PHP security (single-program.php, single-artist.php, parts/loop-program.php, class-ipo-event.php:312/319, class-ipo-program.php) | Pervasive unescaped ACF/user output (stored-XSS surface) | 25+ sites echo get_field() values into HTML/href/src without esc_html/esc_url/esc_attr (ticket_purchase, audio_track, artist_img, donate_href are the dangerous href/src sinks). Admin-write gated, but any contributor with ACF access becomes an XSS vector and malformed data breaks markup. |
| 5 | high | JS correctness (assets/scripts/anim-reveal.js:130-142 + empty wow.js; assets/scripts/ipo-custom.js:864-865) | Two hard JS runtime errors fire on (almost) every page | anim-reveal.js calls new WOW()/wow.init() but wow.js is a 0-byte file and WOW is never defined → ReferenceError aborts the reveal/scrollSpy ready block. ipo-custom.js does querySelector('[lang=en-US] input[name=input_3]').setAttribute(...) with no null check → TypeError on the entire Hebrew/Arabic site and any EN page without that form, aborting trailing top-level code. |
| 6 | high | JS correctness (assets/scripts/anim-base.js:1 vs ipo-custom.js:868 vs main.js bottom copy) | Silent global isScrolledIntoView override breaks reveal thresholds site-wide | ipo-custom.js (loads last) and main.js redefine the global isScrolledIntoView WITHOUT the $extra offset param that anim-base.js provides. Callers passing offsets (120 for titles, 900 for data-load-on-view) have them silently ignored, so every scroll-reveal threshold is wrong. Three competing definitions of one helper. |
| 7 | high | JS correctness (assets/scripts/ipo-custom.js) | Duplicate 'fighting' JS handlers carried verbatim from snippets | mobile-search-toggle bound 2x with contradictory state models (406-417 vs 539-554); slider-banner Slick init'd 2x (30-44 vs 234-250) → double-init; Months Popup bound 2x language-agnostic (53-93 EN vs 100-140 HE) so clicking a month fires both and HE navigation wins on EN pages; order_area sticky and submenu positioners each implemented twice. These produce visible bugs, not just bloat. |
| 8 | high | PHP correctness (single-program.php:12-16, 394-441) | single-program.php clobbers global $post without wp_reset_postdata | $post_id = raw $_GET['p'] (unsanitized IDOR, collides with WP reserved 'p'); inside the moreConcerts loop $post = get_post($related_id) overwrites the global with no reset, so footer/widget the_title()/get_the_ID() reflect the last related program. Both a correctness bug and an injection-surface. |
| 9 | high | PHP correctness (single-artist.php:381-409) | single-artist.php unbalanced moreConcerts <section> markup | The <?php endif;?> is placed inside .splide__track (line 400) so closing </div></section> tags fall outside the conditional while their openings are inside it. Both branches ($count>0 and ==0) emit invalid/unbalanced HTML — the slider markup is broken regardless of state. |
| 10 | high | PHP correctness (includes/class-ipo-importer.php:7, class-ipo-importer-artist_plan.php:7, class-ipo-importer-series.php:7) | Fatal-risk: three files declare `class ipo_importer` | Three incompatible constructors all named ipo_importer. Only one is loaded today, but including any second variant fatals with 'Cannot redeclare class'. The artist_plan/series loaders also assume the wrong constructor signature. |
| 11 | high | Performance / enqueue (functions.php:70-115; parts/ipo-third-party-head.php:23-25) | Three slider engines + ~852KB uncompressed assets on every page, no conditional/deferred loading | owl+slick+splide all ship globally; calendar/isotope/hc-sticky/animate.css(72KB) load on plain content pages; AOS/lottie-player/anime load render-blocking from public CDNs (unpkg/cdnjs) in <head>. Biggest first-paint and transfer win is conditional enqueue + defer/self-host. |
| 12 | high | CSS/JS architecture (assets/styles/ipo-custom.css 7,330 lines; assets/scripts/ipo-custom.js ~891 lines) | ipo-custom.css/js as a permanent 'loads-last-wins' override layer | 38 CSS snippets redefine 109 style.css + 56 style-sagi + 42 responsive selectors and carry 490 !important; the JS is ~25 independent ready()/IIFE blocks. Until dissolved by concern, the cascade and event wiring are decided by load order, not design — this masks every real conflict and blocks !important reduction. |
| 13 | medium | CSS structure (style.css 28 @media, ipo-custom.css 80, responsive.css 7) | Responsive logic fragmented across 3 files with ~26 ad-hoc breakpoints incl. typos | The dedicated responsive.css was abandoned at 7 media queries while 108 live in style.css/ipo-custom.css. Breakpoints include typos (786, 765), near-duplicates (990/991/992, 767/768, 1250/1270/1280/1300), and a 768-min vs 769-min 1px dead zone. No single place to reason about any breakpoint. |
| 14 | medium | PHP templates (page-templates/, parts/, parts_OLD/, includes/) | Template version-sprawl and a large dead-code/backup footprint | 5 live_streaming_page, 3 Lobby-new-season, 4 orckestraguest, 2 orckestrateam, history/Musicians old-vs-new variants are all independently assignable; parts_OLD/ (42 files, README: safe to remove), 14 orphaned parts, .bkp/.txt/0-byte/.php.php files, and ~1,070 lines of inline CSS in a single template add confusion and deploy weight. Deleting variant-family losers requires WP-DB route verification first. |

## Remediation roadmap

### 1. Phase 0 — Verify external dependencies (gate for everything)  _(risk: low, effort: S)_

**Goal:** Establish ground truth that the rest of the plan depends on, before changing anything, so remediation targets the real production behavior.
**Depends on:** none

- Obtain and read the parent wpstack-parent class-wpstack-ajax to confirm whether wpstack_ajax registers wp_ajax_nopriv and whether it already enforces nonce/capability — this gates ALL AJAX-auth remediation.
- Read the parent's add_script/add_style enqueue() to learn head-vs-footer placement and whether it appends filemtime/version strings (needed for defer + cache-busting strategy).
- Pull the WP DB _wp_page_template meta (or the Pages list 'Template' column) to record which Template Name is assigned for each version-family (live_streaming_page x5, Lobby-new-season x3, orckestraguest x4, orckestrateam x2, Musicians, history, contents.php.php).
- Confirm in a browser network tab whether header.php:19 spacing.css (Windows-backslash href) currently 200s or 404s, and whether the Google Fonts request is needed.
- Confirm wpstack-parent ships in the production deploy (a missing parent fatals the site).

### 2. Phase 1 — Security hardening  _(risk: medium, effort: L)_

**Goal:** Close the confirmed critical/high security defects in the PHP layer.
**Depends on:** Phase 0 (parent wpstack_ajax base-class behavior)

- Gate the state-changing AJAX handlers (ajax_import_batch, ajax_process_posts) with check_ajax_referer + current_user_can('manage_options'/'edit_posts') — in the parent base class if it owns registration, else per-handler.
- In ipo_importer: replace raw cURL/file_get_contents with wp_safe_remote_get, remove CURLOPT_SSL_VERIFYPEER=>false, allowlist import source hosts, reject file:// schemes.
- Rewrite get_attachment_id_by_title with $wpdb->prepare and SELECT ID (wp_insert_attachment_from_url.php:14); validate fetched Content-Type against an image allowlist and force a safe extension in wp_upload_bits.
- Remove the wp_ajax_nopriv registration on fetch_presentation_data (or add nonce+cap), absint()+rawurlencode the apiEventId, remove sslverify=false, and fix the undefined-$body else branch (functions.php:256-276).
- Sweep templates and ipo_event/ipo_program classes to wrap echoed ACF in esc_html/esc_url/esc_attr/wp_kses_post, prioritising href/src sinks (ticket_purchase, audio_track, artist_img, donate_href).
- Sanitize single-program.php $_GET['p'] with absint() + post-type check; intval() the offset/amount in ajax_process_posts.
- Delete the dead insecure example-ajax.php (open redirect via HTTP_REFERER) and the unused conflicting importer variants after the rename in Phase 2.

### 3. Phase 2 — Stop the bleeding: runtime errors & correctness bugs  _(risk: medium, effort: M)_

**Goal:** Eliminate the JS runtime errors and PHP correctness bugs that break pages today; cheap, high-impact, mostly independent of the big refactors.
**Depends on:** Phase 0 (route verification for single-artist/single-program if behavior-sensitive)

- Resolve the WOW dependency: either load a real WOW build or migrate .animate_wow/data-wow-delay to the already-loaded AOS, then delete the empty wow.js + its enqueue (functions.php:72) and the dead WOW block in anim-reveal.js.
- Guard ipo-custom.js:865 input_3 querySelector with a null check (or set the placeholder server-side).
- Keep ONLY the anim-base.js isScrolledIntoView (with $extra); delete the ipo-custom.js:868 and main.js copies (behavioural fix).
- Fix single-artist.php: move the misplaced endif to after </section> (line 409) to balance the moreConcerts markup; remove undefined $page_id usages; remove the stray if(!$donate_href); move the conected_artiest redirect to a pre-output hook.
- Fix single-program.php: replace global $post = get_post($related_id) with a local var (or wp_reset_postdata after the loop).
- Rename two of the three `class ipo_importer` declarations (or delete the unused variants) to remove the fatal-redeclare risk.
- Null-guard main.js header.offsetHeight (541) and lottieElementlogo (565-573); guard the header offset()/height() reads in both submenu positioners; fix readMoreButton-undefined (ipo-custom.js:192); remove the undefined $events_html line in ajax_get_calendar_events.php:99.

### 4. Phase 3 — De-duplicate JS event wiring  _(risk: medium, effort: L)_

**Goal:** Collapse the 'fighting' duplicate handlers and build one shared util layer, without yet migrating sliders.
**Depends on:** Phase 2 (isScrolledIntoView + util layer)

- Merge mobile-search-toggle (2x), slider-banner Slick init (2x, add slick-initialized guard), Months Popup (2x → one lang-parametrised module), order_area sticky (keep main.js rAF, remove ipo-custom copy), submenu positioners (2x → one), toggle_link (reconcile across files).
- Build one shared util loaded first: dir/RTL detection (replaces ~6 recomputations of lang==='en-US'), throttle, debounce, isScrolledIntoView; delete the dead inner throttle copies in ipo-custom.js (472, 788).
- Restructure ipo-custom.js from ~25 IIFE/ready blocks into feature modules each guarded to no-op when its root element is absent (removes most console errors on pages lacking elements).
- Strip front-end console.log/warn from calendar-filter.js (esp. the per-event loop), calendar-behavior.js, and the ajax scripts; gate any diagnostics behind a debug flag.
- Delete confirmed-dead JS: player-slider owl init (main.js:493, 0 PHP refs), create_events_ajax.js (debug scaffolding), loader.js duplicate load handler, commented WOW/legacy blocks.
- Reduce Splide re-mounting in the calendar: one shared .calendar-row instance reused across ready/select_next_day_with_events/reveal_events instead of constructing it 3x.

### 5. Phase 4 — Finish owl→Splide migration (single slider engine)  _(risk: high, effort: XL)_

**Goal:** Migrate the 6 remaining owl sliders + the 3 legacy moreConcerts-slider markups + the Slick banner sliders to Splide, then remove owl and (ideally) Slick.
**Depends on:** Phase 3 (JS dedup, shared util, single-ready init); Phase 0 (route verification for artist/concert-screen templates)

- Migrate meet/recommended/recommended-child/upcoming/video sliders to Splide using the sliders-splide.js per-slider-config pattern; move all main.js top-level slider/isotope/magnific inits (234-538) inside one document.ready.
- Convert the 3 remaining .moreConcerts-slider (owl) markups to .moreConcerts-splide (parts/artist.php — archive if orphaned; page-templates/artist.php, template-concert-screen.php — wire to real data or archive; the hardcoded 26.02.22 demo content proves they're unfinished).
- Extract the 18 copy-pasted owl video-slider markup blocks into one parts/section-video-slider.php taking args, replacing inline copies (do it during this migration).
- Unbundle plugins.js into bootstrap-js / owl / magnific handles; remove owl entirely + owl.carousel.min.css.
- Migrate the Slick banner sliders (ipo-custom.js) and calendar-slider to Splide, then drop slick.min.js + slick.css; standardize on Splide as the single engine.

### 6. Phase 5 — CSS consolidation by concern  _(risk: high, effort: XL)_

**Goal:** Dissolve the author/snippet-organized CSS into concern-based layers so the cascade is resolved by structure, then remove the now-dead owl CSS.
**Depends on:** Phase 4 (owl removal must finish before owl-CSS deletion)

- Quick safe wins first (no behavior change): delete the dead ine-height block (style.css:200-202), ~17 empty rule blocks, orphaned root style.css/helper.css/popup.css, the 3 empty vendor enqueues, and trim animate.css to .animated+fadeIn/fadeInUp (~3,000 lines).
- Target architecture: vendor.css → base.css (reset/typography/RTL defaults) → components.css (one authoritative block per component: header, calendar, events-program, sliders, img_box) → responsive.css (ALL @media) → language.css (the [lang=en-US] LTR layer), loaded last so it wins without !important.
- Dissolve ipo-custom.css: redistribute its 38 blocks into the concern layers; reconcile the calendar overflow:hidden conflict (sagi enables, ipo comments out) deliberately; retire style-sagi.css and style-shojib.css by folding into calendar.css / events-program.css.
- Consolidate ALL @media onto one documented breakpoint ladder (e.g. 1800/1650/1500/1400/1200/991/767/576/380); snap 786→768, 990/950→991, 1250-1300→1300, etc.; normalize media-query syntax; resolve the 768-min vs 769-min dead zone.
- Collapse the 255 multiply-defined selectors into one authoritative definition each (base + explicit responsive/lang overrides) — this is the lever that removes most of the 1,214 !important.
- After owl removal (Phase 4) completes, delete all .owl-* and .moreConcerts-slider rules across style.css/responsive.css/ipo-custom.css/style-shojib.css in one sweep.
- Migrate directional CSS to logical properties to eliminate the ~263 [lang=en-US] duplicates; then audit Arabic (currently zero rules).
- Extract the large inline <style> blocks (new-theme-artist-plan.php ~1,070 lines, Lobby-New-season-v2/v3, Philharmonic-FEST, single-artist_plan, single-program) into conditionally-enqueued stylesheets.
- Add a stylelint CI gate (no-duplicate-selectors, declaration-no-important in component files, no-descending-specificity) to prevent regression.

### 7. Phase 6 — Template & dead-code cleanup  _(risk: medium, effort: L)_

**Goal:** Remove confirmed dead code and collapse version-families to their assigned survivors.
**Depends on:** Phase 0 (DB route assignment); Phase 4 (artist/concert-screen template decisions)

- Tier-1 (delete now, no route check): entire parts_OLD/ tree; editor backups (newseries.bkp, class-ipo-importer.bkp, ipo-importer.bkp, create-program.php-, backup.txt); zero-byte non-templates (lobby.php.txt, Found-commemoration.php); parts/headers/header-1-bak.php; parts/hero-section-new.php.
- Tier-2 (delete after 0-ref + not-assigned confirmation): the 13 remaining orphaned parts (part-lobby-* set, uppart-lobby-program, section-artists-page, part-hero, parts/artist.php); template-test.php; contents.php.php. Do NOT remove the 3 functions.php-included parts that only look orphaned (ipo-marketing-body, ipo-third-party-head/footer).
- Tier-3 (collapse version-families to the Phase-0 assigned survivor): live_streaming_page x5→1, Lobby-new-season x3→1, orckestraguest x4→1, orckestrateam x2→1, Musicians old/new, history/history-old.
- Resolve the two empty-but-invoked parts (live-streaming-categories.php, page-banner.php): implement or remove both the file and its the_part call.
- Move new-theme-artist-plan.php out of page-templates/ (it has no Template Name; it's an include-target) into parts/ or includes/.
- De-duplicate PHP/JS server-side: drop the inline window.soundplay/pauseVid in functions.php (ipo-custom.js owns them); single-source the AT-popup (one owner; load web-view loader once); pick one canonical event-creation path and delete the competing ones (create-event.php, ipo_event::create).

### 8. Phase 7 — Performance & enqueue  _(risk: medium, effort: L)_

**Goal:** Cut first-paint and transfer weight via conditional, deferred, self-hosted, cache-busted asset loading.
**Depends on:** Phase 4 (single slider engine), Phase 5 (animate trim / bootstrap subset), Phase 0 (parent enqueue + spacing.css 404 verification)

- Conditional enqueue (biggest win): gate calendar scripts, the (now single) slider engine + slider CSS, isotope, hc-sticky, animate.css, magnific/lity behind is_page_template/is_singular/has_shortcode so plain content pages drop from ~852KB to a small core.
- Self-host AOS/lottie-player/anime locally and move the JS to footer or add defer (via a child script_loader_tag filter on parent-registered handles); add preconnect only if any external lib must stay.
- PurgeCSS-subset bootstrap.min.css (145KB → likely <40KB) against the templates.
- Delete dead font weight: the 4 Lato folders (~430KB), the mandatoryvariable-tester woff2 (210KB); drop PT Sans (0 uses) and possibly Open Sans (1 use) from the Google Fonts request; remove the 2 preconnects if the request goes.
- Fix spacing.css: replace the hardcoded backslash <link> (header.php:19) with a proper add_style enqueue using forward slashes + filemtime versioning (fixes the likely Linux 404 and the fake classic-theme-styles id collision).
- Consolidate footer inline JS (lottie placeholders, hero controls, privacy banner, geo-IP banner) into ipo-custom.js; run the geo-IP fetch chain under requestIdleCallback; fix the root cause so the 1200ms setTimeout reveal fallback can be removed.
- Add explicit filemtime versioning to the volatile child handles (ipo-custom.css/js, sliders-splide, moreconcerts) to prevent stale-cache after edits.

## Quick wins

- Delete the dead JS: player-slider owl init (main.js:493, 0 PHP refs), create_events_ajax.js, loader.js duplicate load handler, commented WOW/legacy blocks.
- Delete confirmed-dead CSS files (root style.css, assets/styles/helper.css, popup.css), the dead ine-height block (style.css:200-202), and ~17 empty rule blocks — zero runtime risk.
- Guard ipo-custom.js:865 input_3 querySelector with a null check — single line, removes a TypeError on every Hebrew/Arabic page.
- Delete the entire parts_OLD/ tree (README explicitly says it is unloaded and safe to remove) plus the .bkp/.txt/.php-/0-byte backup files in includes/ and page-templates/.
- Delete the 4 Lato font folders (~430KB) and the mandatoryvariable-tester woff2 (210KB) — referenced nowhere.
- Drop PT Sans (0 usages) from the Google Fonts request in header.php:18.
- Strip the per-event console.log loop in calendar-filter.js (and other front-end console noise) — measurable jank on a busy calendar with devtools open.
- Rewrite get_attachment_id_by_title with $wpdb->prepare (wp_insert_attachment_from_url.php:14) and remove CURLOPT_SSL_VERIFYPEER=>false in the importer — small diffs, closes critical security holes.
- Remove the wp_ajax_nopriv registration on fetch_presentation_data and fix its undefined-$body else branch (functions.php:257,272).
- Fix the misplaced endif in single-artist.php:400 so the moreConcerts <section> markup balances.

## Already done (this refactor)

- moreConcerts slider rebuilt on Splide — new assets/scripts/sliders-splide.js + assets/styles/moreconcerts.css (migrated on single-artist.php, single-program.php, Simple_page.php).
- Forced scroll-to-top after load removed from calendar-behavior.js.
- ~38 WordPress admin code-manager snippets consolidated INTO the theme: assets/styles/ipo-custom.css (front-end, enqueued last), assets/scripts/ipo-custom.js (front-end), additions to style-admin.css/scripts-admin.js (admin), and parts/ipo-third-party-{head,footer,body}.php for AOS/lottie-player/anime/WhatsApp/geo-banner/AT-popup.
- Third-party libs centralized into the ipo-third-party parts (AOS/lottie/anime), and the AT-popup callbacks were renamed (handleATPopupEvent vs _ipo) to avoid a JS name collision.
- parts_OLD/ already carries a README marking it as a non-loaded, safe-to-remove archive (cleanup decision pre-made, just needs executing).
- Loader script is already conditionally enqueued (front page / calendar templates only) — the pattern to extend to other scripts in Phase 7 (functions.php:88).
- Some portability hardening started: ipo_arrow_icon_url/ipo_theme_uri helpers replace hardcoded domains; several create-event includes already commented out.
- The to-merge/ folder holds the original consolidated source (ignored — staging only, not part of the runtime audit).

## Overall risks

- Parent-theme dependency is a hard blocker and an unknown: wpstack-parent (not in this checkout) owns jQuery, the real enqueue (head/footer placement, versioning), the the_part/get_part API, and the wpstack_ajax base class. The AJAX-auth fixes, defer strategy, and cache-busting all hinge on parent behavior that must be verified first (Phase 0). A missing parent fatals the site.
- Destructive template cleanup before verifying live-route assignment in the WP DB risks deleting the actually-assigned variant of a version-family (5 live_streaming_page, 3 Lobby, 4 orckestraguest, etc.). Code alone cannot tell which is live — Phase 0 DB check is mandatory before any Tier-3 deletion.
- CSS consolidation removes the 'loads-last-wins' crutch and the 1,214 !important that currently mask conflicts; collapsing them will surface latent cascade bugs that were hidden. Requires component-by-component visual regression testing at every breakpoint, especially RTL vs en-US (and the untested Arabic).
- owl→Splide migration is the largest, highest-risk change (XL): 6 sliders + 18 inline video-slider copies + 3 legacy moreConcerts markups across many templates. Deleting owl CSS before migration completes would break the artist/concert-screen pages that still emit owl markup — sequencing (Phase 4 before Phase 5 owl-CSS deletion) is critical.
- Arabic (ar) has zero dedicated CSS/JS rules and is likely visually unverified; converting directional CSS to logical properties and any header/calendar changes must be checked in all three WPML languages, not just he-IL and en-US.
- The static-demo moreConcerts sliders (hardcoded href='#', 26.02.22, fixed images on artist.php / template-concert-screen.php) indicate unfinished features; wiring them to real data is scope that may surface missing ACF fields or data model gaps.
- Security fixes that add nonce/capability checks could break the importer/admin tooling workflows if those tools are run from contexts that don't carry a nonce — coordinate with whoever operates the IPO importer before deploying the gating.
- No build/minification/CI lint gate exists today; without adding stylelint/eslint gates (Phase 5) the consolidation will re-fragment over time as multiple developers continue committing.

---

# Detailed findings by area

## CSS — cross-file duplication & conflict map (enqueued stylesheets)

The theme ships ~16,000 lines of theme-authored CSS across 11 files, of which ipo-custom.css (7,330 lines) is a verbatim concatenation of ~38 independent WordPress code-manager snippets (each with its own author/ID header, e.g. "#14 QA – Sagi (ID 30602)", "#27 Aviad Css (ID 29140)", "#38 Aviad Css all web (ID 662)"). It is loaded LAST and therefore wins almost every cascade tie, but it heavily re-defines selectors that already exist in style.css (109 shared selectors), style-sagi.css (56), responsive.css (42) and style-shojib.css (5). The result is layered override-on-override: ipo-custom.css and style.css alone carry ~490 and ~486 !important declarations respectively (980 of ~1,215 total), which is the symptom of selectors fighting across files. Responsive logic is fragmented across THREE layers (style.css 28 media queries, responsive.css 7, ipo-custom.css 80) with inconsistent breakpoints (768/765/767/786 for mobile; 990/991/992 for tablet; 768 vs 769 min-width create a 1px dead zone). Per-developer/per-feature siloing is structural: style-sagi.css (calendar + switch toggle), style-shojib.css (events-Contents/img_Contents program template only), and inside ipo-custom.css the named QA blocks (#14 Sagi, #15 orckestra QA, #16 Shojib, #24 Lobby-Shojib, #29 Artist plan QA) plus two separate "Aviad Css" blocks. spacing.css is hardcoded in header.php BEFORE wp_head(), so despite its name it has the LOWEST priority of all stylesheets, and its href uses Windows backslashes that will not resolve on a Linux server. Three CSS files are completely dead (root style.css, assets/styles/helper.css, popup.css — referenced nowhere). The moreConcerts→Splide migration is PARTIAL, so its old owl rules are dead in some templates but still live in others.

**Metrics**

- Theme-authored CSS total: ~15,997 lines across 11 files (vendor excluded)
- Largest file: ipo-custom.css = 7,330 lines / 140KB = a concatenation of ~38 ex-admin code-manager snippets
- !important counts (enqueued): ipo-custom.css 490, style.css 486, responsive.css 208, style-shojib.css 22, style-sagi.css 8, moreconcerts.css 1 — total ~1,215; spacing.css/helper.css 0
- Unique selectors per file: style.css 656, ipo-custom.css 1,138, responsive.css 207, style-sagi.css 169, style-shojib.css 53
- Pairwise SHARED (redefined) selectors: ipo-custom.css↔style.css 109; ipo-custom.css↔style-sagi.css 56; ipo-custom.css↔responsive.css 42; responsive.css↔style.css 73; ipo-custom.css↔style-shojib.css 5; style-sagi.css↔style.css 16
- Media queries (responsive logic) split 3 ways: style.css 28, responsive.css 7, ipo-custom.css 80
- Distinct mobile max-width breakpoints in use: 768(24x), 767, 765, 786, plus 500/400/600 etc.; tablet boundary split 990/991/992; min-width split 768(11x) vs 769(10x)
- [lang='en-US'] LTR override declarations: ipo-custom.css 239, style.css 24, responsive.css 5, style-shojib.css 5 (Arabic/ar overrides: 0 in every file)
- Cluster selector spread — .img_box: style.css 67, ipo-custom.css 45, style-shojib.css 26, style-sagi.css 17, responsive.css 5 (5 files); .calendar_area/calendar: ipo-custom.css 48, style.css 27, style-sagi.css 25, responsive.css 8; .upcoming_area: style.css 41, ipo-custom.css 19, responsive.css 6, style-sagi.css 2; .meet_area: style.css 30, ipo-custom.css 11, responsive.css 10
- Dead files (referenced nowhere): root style.css (30 lines), assets/styles/helper.css (5 lines), popup.css (62 lines)

### Findings

- **[high] ipo-custom.css is 38 concatenated admin snippets that re-define 109 selectors already in style.css** — `assets/styles/ipo-custom.css (whole file, section headers at lines 1,17,28,63,98,262,310,1228,1242,1524,1963,2574,2713,2882,2980,3084,3353,3383,3440,3516,3525,4139,4187,5250,5366,5407,5811,5823,6067,6251,6310,6337,6738,6849,7107,7162)`
  - _category:_ architecture
  - Each block carries its origin header e.g. line 1964 '#14 | QA – Sagi (ID: 30602)', 2714 '#16 | QA – Shojib', 4187 '#27 | Aviad Css (ID 29140)', 6849 '#38 | Aviad Css (all web) (ID 662)'. The file is enqueued dead-last (functions.php:113) so it wins ties, but it duplicates 109 selectors from style.css, 56 from style-sagi.css, 42 from responsive.css. Topic blocks overlap each other internally too: header logic in #8 Mobile header, #12 Header – mobile, #23 Header search, #30 Header menu; calendar in #36 Calendar, #37 Event Filter, #39 Calendar animations; a whole #13 Responsive block (lines 1524–1962) replicates responsive.css.
  - **Fix:** Treat ipo-custom.css as a migration staging buffer, not a permanent layer. Fold each block back into the correct domain stylesheet by topic (header→one header partial, calendar→one calendar partial, responsive→responsive.css) and delete the per-snippet headers. This removes the 'last file wins' dependency that is masking real conflicts.
- **[high] Calendar block in ipo-custom #36 is a near-verbatim copy of style-sagi.css calendar rules, with a silent behavior change** — `assets/styles/style-sagi.css:685-694 vs assets/styles/ipo-custom.css:6337-6359`
  - _category:_ duplication
  - style-sagi.css:691-694 sets '.calendar-full .loop-day .contents, .loop-calendar-horizontal-event { overflow: hidden; }'. The ipo-custom #36 copy (line 6356-6359) keeps the same selector group but COMMENTS OUT overflow:hidden and drops the .contents selector. Because ipo-custom loads after style-sagi, the override silently disables overflow clipping. The hover-scale rule '.loop-calendar-horizontal-event .img_box .position-relative img' is also duplicated identically (sagi 686, ipo 6351). 56 calendar selectors are shared between the two files.
  - **Fix:** Pick ONE calendar source of truth (style-sagi.css is the smaller, cleaner authoring origin). Delete the duplicated calendar rules from ipo-custom #36/#37/#39 and reconcile the overflow:hidden decision explicitly rather than via a commented-out override.
- **[high] Responsive rules fragmented across 3 files with inconsistent and off-by-one breakpoints** — `assets/styles/responsive.css (7 @media) ; assets/styles/style.css (28 @media) ; assets/styles/ipo-custom.css (80 @media, e.g. lines 5,21,40,53,920,1155,1170,1528,1590,1655,2110,3162,3414,3462...)`
  - _category:_ conflict
  - The dedicated responsive.css carries only 7 media queries while style.css has 28 and ipo-custom.css has 80, so responsive overrides live in three competing layers. Breakpoint values are not standardized: mobile max-width appears as 768(24x), 767, 765, 786; tablet boundary as 990/991/992; min-width mobile as 768(11x) vs 769(10x). The 768-max vs 769-min split is fine, but the 768-min vs 769-min mix creates ambiguous overlap at the boundary. Same selectors (e.g. .moreConcerts .img_box) appear in responsive.css:359 and ipo-custom.css:7033.
  - **Fix:** Define a single breakpoint token set (e.g. 576/768/992/1200/1600) as CSS variables or a documented map, consolidate ALL @media blocks into responsive.css (or co-locate with each component), and normalize 765/766/767/786→768 and 990/992→991.
- **[medium] Self-duplication and adjacent duplicate selectors WITHIN style.css** — `assets/styles/style.css:370-375 and 3723-3726 (.hamburger-menu); 3728 and 3731 (body.overflow-hidden .hamburger-menu)`
  - _category:_ duplication
  - .hamburger-menu base rule is split across line 370 (cursor/display/z-index/float) and line 3723 (height/width). Worse, 'body.overflow-hidden .hamburger-menu' is declared twice back-to-back at lines 3728 and 3731 with different single properties (margin-top vs margin-right) — should be one rule. This pattern (a selector partially defined far from its other half) recurs throughout style.css and makes value tracing error-prone.
  - **Fix:** Merge split definitions of the same selector into a single rule. Run a CSS deduplication pass (e.g. csscomb/stylelint no-duplicate-selectors) on style.css to collapse the adjacent and far-apart duplicates.
- **[medium] English/LTR support is bolted on as 239 [lang='en-US'] overrides concentrated in ipo-custom.css** — `assets/styles/ipo-custom.css (#10 LTR – ENGLISH block, lines 311–1227; counts: 239 en-US overrides) ; also style.css:24, responsive.css:5, style-shojib.css:5`
  - _category:_ architecture
  - LTR is implemented by re-stating RTL rules under [lang='en-US'] rather than using logical properties. ipo-custom.css alone has 239 such overrides (the whole #10 block ~900 lines), with more scattered in style.css and responsive.css — so the same component is styled twice (RTL base + en-US override) in multiple files. Arabic (ar, the third WPML language) has ZERO overrides in every file, so Arabic likely renders with RTL defaults only and may be visually unverified.
  - **Fix:** Migrate directional CSS to logical properties (margin-inline, padding-inline, inset-inline, text-align:start/end) so a single rule serves all three languages, eliminating the 239+ en-US duplicates. Audit Arabic separately since it has no dedicated rules.
- **[medium] spacing.css hardcoded before wp_head with a Windows backslash path — lowest priority and likely broken on Linux** — `header.php:19 (link href uses backslashes '\wpstack-child\assets\styles\spacing.css'); loaded before wp_head() at header.php:27`
  - _category:_ dead-code
  - spacing.css (1,111 lines) is injected via a raw <link> in header.php BEFORE wp_head(), so every enqueued stylesheet (including style.css, responsive.css, ipo-custom.css) overrides it — the opposite of what its name implies. The href mixes a forward slash with backslashes ('/wp-content/themes\wpstack-child\assets\styles\spacing.css'); backslashes are not valid URL path separators and will 404 on a case/slash-sensitive Linux host, meaning the file may not load at all in production.
  - **Fix:** Either enqueue spacing.css through functions.php with a deliberate priority, or remove it if its 1,111 spacing utilities are unused. At minimum fix the href to forward slashes. Verify in the browser network tab whether it currently 200s or 404s.
- **[medium] Three CSS files are referenced nowhere (truly dead)** — `style.css (repo root, 30 lines); assets/styles/helper.css (5 lines); popup.css (repo root, 62 lines)`
  - _category:_ dead-code
  - Global grep across all PHP found no enqueue, no get_stylesheet_uri(), and no <link> for root style.css, helper.css, or popup.css. functions.php enqueues assets/styles/style.css (the 4,666-line one), not the 30-line root style.css. These are leftover/abandoned files.
  - **Fix:** Delete root style.css, assets/styles/helper.css, and popup.css after a final git-history check. They add confusion (two files named style.css) with zero runtime effect.
- **[medium] moreConcerts owl rules are only PARTIALLY dead — migration is incomplete across templates** — `style.css:2801,2937,3993,4000 (.moreConcerts .owl-*) ; responsive.css:618,781,1060 ; ipo-custom.css:932 ; new module assets/styles/moreconcerts.css (.moreConcerts-splide)`
  - _category:_ duplication
  - The Splide rebuild uses class .moreConcerts-splide (moreconcerts.css). But the outer <section class='moreConcerts'> wrapper is still emitted everywhere, and the migration is split per template: single-artist.php:391, single-program.php:388, Simple_page.php:151 use .moreConcerts-splide, while page-templates/artist.php:60, page-templates/template-concert-screen.php:163, parts/artist.php:60 still output 'owl-carousel owl-theme moreConcerts-slider'. So the old .moreConcerts .owl-* / .moreConcerts-slider rules in style.css/responsive.css are LIVE on artist/concert-screen pages and DEAD on the migrated pages — not uniformly dead.
  - **Fix:** Do not delete the .moreConcerts owl rules yet. Finish migrating page-templates/artist.php, parts/artist.php and template-concert-screen.php to Splide first, then remove .moreConcerts-slider and .moreConcerts .owl-* from style.css, responsive.css and ipo-custom.css in one sweep. The .moreConcerts wrapper rules (padding/img_box) stay until the wrapper class itself is renamed.
- **[medium] Per-developer/per-feature stylesheets overlap the same components instead of owning them** — `assets/styles/style-sagi.css (169 sel: switch toggle + calendar) ; assets/styles/style-shojib.css (53 sel: section.events-Contents / .img_box.img_Contents) ; cross-overlap into style.css, style-sagi & ipo-custom`
  - _category:_ fragmentation
  - style-shojib.css is dedicated to one component family (section.events-Contents 33x, img_box.img_Contents 22x) but that same component is ALSO styled in style.css (img_box.img_Contents 3x) and ipo-custom.css (events-Contents 4x, img_Contents 6x), and 5 selectors are shared shojib↔ipo-custom (e.g. '.program-toggle:after', 'section.events-Contents .events'). style-sagi.css owns calendar + a .switch toggle but shares 56 selectors with ipo-custom and 16 with style.css. So no file fully owns a component; edits require touching 3-4 files.
  - **Fix:** Re-organize by component/domain, not by author: one calendar.css (absorb sagi calendar + ipo #36/#37/#39 + style.css calendar), one events-program.css (absorb shojib + the img_Contents bits from style.css/ipo-custom), etc. Retire the author-named files once their content is redistributed.
- **[low] responsive.css ↔ style.css share 73 selectors — mostly legitimate but unverified for redundant repeats** — `assets/styles/responsive.css vs assets/styles/style.css (73 shared selectors)`
  - _category:_ conflict
  - 73 selectors are defined in both responsive.css and style.css. Much of this is the intended pattern (base in style.css, override in responsive.css media query), but the raw match also catches non-media-query redefinitions where responsive.css simply restates a property unchanged or fights an !important. Given responsive.css has 208 !important declarations for only 207 unique selectors (~1 !important per selector), specificity escalation against style.css is the norm.
  - **Fix:** Audit the 73 shared pairs to separate genuine breakpoint overrides from redundant restatements; remove !important where the cascade order already guarantees the win (responsive.css loads after style.css).

### Duplicates

- **Calendar component (event filter, loop-day, horizontal-event, hint-popup, calendar_area numCount) defined in two-to-four files**
  - `assets/styles/style-sagi.css:660-1006 (calendar block)`
  - `assets/styles/ipo-custom.css:6337-6738 (#36 Calendar)`
  - `assets/styles/ipo-custom.css:6738-6849 (#37 Event Filter)`
  - `assets/styles/ipo-custom.css:7107-7162 (#39 Calendar animations)`
  - `assets/styles/style.css (27 .calendar_area refs)`
  - `assets/styles/responsive.css (8 calendar refs)`
  - **Fix:** Merge into a single calendar.css. Use style-sagi's authoring as the base, delete ipo-custom #36/#37/#39 duplicates, and resolve the overflow:hidden conflict (sagi enables it, ipo comments it out) deliberately.
- **Responsive/media-query rules duplicated between the dedicated responsive.css and an in-line #13 Responsive block in ipo-custom.css**
  - `assets/styles/responsive.css (entire file, 7 @media, 207 selectors)`
  - `assets/styles/ipo-custom.css:1524-1962 (#13 Responsive, multiple @media at 1528/1590/1655/1744/1784/1840/1900/1912/1924)`
  - `assets/styles/style.css (28 @media inline)`
  - **Fix:** Move every @media block (style.css 28 + ipo-custom 80) into responsive.css OR co-locate with components; standardize breakpoints; delete the #13 block after merge.
- **.hamburger-menu base rule split and duplicated within a single file**
  - `assets/styles/style.css:370-386`
  - `assets/styles/style.css:3723-3733 (incl. adjacent duplicate 'body.overflow-hidden .hamburger-menu' at 3728 and 3731)`
  - **Fix:** Collapse into one .hamburger-menu rule and merge the two body.overflow-hidden duplicates into a single declaration block.
- **English/LTR styling duplicated as [lang='en-US'] re-statements of RTL rules across files**
  - `assets/styles/ipo-custom.css:311-1227 (#10 LTR, 239 en-US overrides)`
  - `assets/styles/style.css (24 en-US)`
  - `assets/styles/responsive.css (5 en-US)`
  - `assets/styles/style-shojib.css (5 en-US)`
  - **Fix:** Replace directional duplicates with CSS logical properties so one rule serves RTL+LTR+Arabic; delete the en-US override layer.
- **events-Contents / img_box.img_Contents program template styled in three files**
  - `assets/styles/style-shojib.css (events-Contents 33, img_box.img_Contents 22)`
  - `assets/styles/ipo-custom.css (events-Contents 4, img_Contents 6; shared selectors .program-toggle:after, 'section.events-Contents .events', 'section.events-Contents .program-row .program-toggle')`
  - `assets/styles/style.css (img_box.img_Contents 3)`
  - **Fix:** Consolidate into one events-program stylesheet owned by the component, not split by author.
- **.img_box component scattered across five files (180+ total references)**
  - `assets/styles/style.css (67)`
  - `assets/styles/ipo-custom.css (45)`
  - `assets/styles/style-shojib.css (26)`
  - `assets/styles/style-sagi.css (17)`
  - `assets/styles/responsive.css (5)`
  - `assets/styles/moreconcerts.css:19,30 (.moreConcerts-splide .img_box)`
  - **Fix:** Establish a single img_box.css partial; variants (.img_Contents, .loop-program .img_box, .upcoming_area .img_box) become modifiers inside it rather than spread across author files.
- **Old moreConcerts owl slider rules duplicated in style.css + responsive.css + ipo-custom.css, superseded by moreconcerts.css for migrated templates only**
  - `assets/styles/style.css:2279-2304,2801,2937,3647,3993,4000`
  - `assets/styles/responsive.css:359,366,618,781,1052,1056,1060,1067,1068,1072`
  - `assets/styles/ipo-custom.css:932,7033`
  - `assets/styles/moreconcerts.css (replacement, .moreConcerts-splide)`
  - **Fix:** Finish migrating page-templates/artist.php, parts/artist.php, template-concert-screen.php to Splide, THEN delete the .moreConcerts-slider / .moreConcerts .owl-* rules from the three files. Until then they are only partially dead.

### Dead / unused

- **Root-level style.css (30 lines)** — `style.css (repo root)` (Global grep for 'themes/wpstack-child/style.css' and 'get_stylesheet_uri' returned no matches; functions.php:103 enqueues assets/styles/style.css, not the root file. No <link> in any PHP.)
- **assets/styles/helper.css (5 lines)** — `assets/styles/helper.css` (Not in the functions.php add_style list (lines 100-115) and grep for 'helper.css' across all PHP returned no matches.)
- **popup.css (62 lines)** — `popup.css (repo root)` (Grep for 'popup.css' across PHP returned no matches; popups are loaded via external web-view.net CSS (includes/ipo-shortcodes.php:215) and magnific-popup, not this file.)
- **moreConcerts owl rules on already-migrated templates** — `style.css:2801,2937,3993,4000; responsive.css:781,1060; ipo-custom.css:932` (single-artist.php:391, single-program.php:388, Simple_page.php:151 now output .moreConcerts-splide (Splide). owl-specific .moreConcerts .owl-* rules no longer match on those pages — but remain LIVE on page-templates/artist.php:60 & template-concert-screen.php:163 which still emit owl, so removal must follow completing the migration.)
- **Arabic (lang='ar') has zero dedicated CSS despite being a WPML language** — `all enqueued CSS files (grep lang="ar" = 0 everywhere)` (Counts: style.css ar=0, responsive.css ar=0, style-sagi ar=0, style-shojib ar=0, ipo-custom ar=0. en-US gets 239+ overrides; ar gets none — suggesting Arabic is either unstyled-beyond-RTL or unverified, not necessarily dead but an untested gap.)

### Recommendations

- De-risk first: delete the 3 confirmed dead files (root style.css, helper.css, popup.css) and fix the spacing.css href in header.php:19 (backslashes -> forward slashes) or migrate it to a proper enqueue — verify in browser whether it currently 404s.
- Dissolve ipo-custom.css as a permanent layer. It is a staging buffer of 38 ex-admin snippets. Redistribute each block into a domain stylesheet (header, calendar, events-program, hero, footer, responsive) and delete the per-snippet headers. This is the single highest-leverage move — it removes the 'loads last so it wins' crutch that hides 109+56+42 selector conflicts.
- Reorganize by COMPONENT, not by AUTHOR. Retire style-sagi.css and style-shojib.css by folding their content into calendar.css / events-program.css. After that, no component should require editing more than one file.
- Consolidate ALL responsive logic into responsive.css with a single documented breakpoint scale (576/768/992/1200/1600). Normalize the stray 765/766/767/786->768 and 990/992->991, and resolve the 768-min vs 769-min ambiguity. Move the 80 ipo-custom @media + 28 style.css @media in.
- Eliminate the [lang='en-US'] override layer (239 in ipo-custom + 34 elsewhere) by converting directional CSS to logical properties (margin-inline / inset-inline / text-align:start-end). Then audit Arabic, which currently has zero rules.
- Cut !important dependency progressively. Once files are merged and cascade order is deterministic, ~980 !important in style.css+ipo-custom can be reduced; add stylelint rules (no-duplicate-selectors, declaration-no-important in component files, no-descending-specificity) to a CI lint gate to prevent regression.
- Finish the owl->Splide migration for the remaining moreConcerts templates (page-templates/artist.php, parts/artist.php, template-concert-screen.php) BEFORE deleting any .moreConcerts owl rules, since they are only partially dead today.
- After consolidation, run a duplicate-selector report (e.g. css-analyzer / stylelint) as the acceptance metric: target zero cross-file selector collisions outside intentional media-query overrides, and merge the intra-file duplicates (e.g. style.css .hamburger-menu at 370 & 3723, body.overflow-hidden .hamburger-menu at 3728 & 3731).

---

## CSS — dead/overridden rules, responsive structure, and consolidation target

The child theme ships ~15,800 lines of CSS across 14 files. The five hand-authored files in scope total ~14,589 lines: style.css (4666), ipo-custom.css (7330), style-sagi.css (1160), style-shojib.css (326), responsive.css (1107). Cascade load order (functions.php:100-114): bootstrap → owl → magnific-popup → style → responsive → style-admin → style-sagi → slick → style-shojib → splide → animate → moreconcerts → ipo-custom (last). ipo-custom.css is the recently-consolidated WordPress code-manager dump — 37 snippet sections concatenated verbatim, organized by ORIGINAL SNIPPET/author (e.g. "#13 Responsive", "#14 QA – Sagi", "#16 QA – Shojib", two separate "Aviad Css" blocks #27/#38), NOT by concern. Because it loads last, it leans on !important to win (490 of the theme's 1214 total !important occurrences live there). Responsive logic is the single biggest structural problem: responsive.css is a clean, Bootstrap-style file (only 7 media queries, tidy 1200/1199/992/991/767/768 breakpoints with rem html-scaling) but it was effectively abandoned — the bulk of responsive rules now sprawl across style.css (25 media queries) and ipo-custom.css (80 media queries) using ~26 ad-hoc breakpoints including obvious typos (786px, 765px) and near-duplicates (990/991/992, 767/768, 1000, 1010, 1024, 1100, 1250, 1270, 1280, 1300, 1350, 1500, 1550, 1600, 1650, 1799, 1800). Dead code is real but moderate: the known typo rule (.img_box li span { ine-height } at style.css:200-202) is fully dead, ~17 empty rule blocks exist, root popup.css (.custom-popup) is orphaned (never enqueued, never referenced), and animate.css ships 152 keyframe animations of which only fadeIn/fadeInUp are used. Owl-* rules are NOT broadly dead — only moreConcerts migrated to Splide, and even its legacy .moreConcerts-slider markup still exists on 3 pages, so owl styling stays live until the other 6 sliders migrate. Of ~2781 rule blocks, only 1543 selectors are unique; 255 selectors are defined more than once. The RTL-default / [lang=en-US]-LTR override architecture is coherent (239 LTR overrides in ipo-custom.css, a dedicated 917-line "#10 LTR – ENGLISH" block) and MUST be preserved; note [lang=ar] has ZERO dedicated rules (Arabic relies on the default RTL).

**Metrics**

- In-scope hand-authored CSS: ~14,589 lines across 5 files — style.css 4666, ipo-custom.css 7330, style-sagi.css 1160, style-shojib.css 326, responsive.css 1107
- Total CSS incl. vendor/utility: ~15,796 lines across 14 files
- !important occurrences: 1214 total (ipo-custom.css 490, style.css 486, responsive.css 208, style-shojib.css 22, style-sagi.css 8)
- Media queries: 122 total — ipo-custom.css 80, style.css 28, responsive.css 7, style-sagi.css 3, style-shojib.css 1
- Distinct responsive breakpoints in use: ~26 (350,380/390,400,500,576,600/601,765,768/786,920,950,990,991,992,1000,1010,1024,1100,1200,1250,1270,1280,1300,1350,1400,1500,1550,1600,1650,1799,1800)
- Snippet sections concatenated in ipo-custom.css: 37 (by source/author, not by concern)
- Rule blocks: ~2781 total; unique top-level selectors: 1543; selectors defined >1x: 255
- Empty rule blocks: ~17 (style.css 8, ipo-custom.css 8, style-sagi.css 1)
- [lang=en-US] LTR overrides: 263 total (ipo-custom.css 239, style.css 24, style-shojib.css 5, responsive.css 5); [lang=ar]: 0
- Comment+blank lines in ipo-custom.css: 1587 (~22% of the file)
- animate.css: 3340 lines / 152 keyframes; only .animated + fadeIn/fadeInUp applied by anim-reveal.js

### Findings

- **[high] Responsive rules sprawl across style.css + ipo-custom.css instead of the dedicated responsive.css** — `assets/styles/ipo-custom.css (80 @media); assets/styles/style.css (28 @media); assets/styles/responsive.css (only 7 @media)`
  - _category:_ responsive-structure
  - responsive.css is the cleanest file (commented 'Extra large / Medium devices', tidy 1200/1199/992/991/767/768 breakpoints, base rem scaling html{font-size:56%→48%} at responsive.css:9-11,305-307) but it was abandoned at 1107 lines. The real responsive logic moved into style.css (interleaved with desktop rules, lines 2009-4642) and ipo-custom.css (80 media queries). Desktop/tablet/mobile are NOT separated — min-width and max-width blocks are scattered through every snippet section. There is no single place to reason about any breakpoint.
  - **Fix:** Make responsive.css (or a new responsive layer) the single home for all media-query rules. Move every @media block out of style.css and ipo-custom.css into it, grouped desktop→tablet→mobile.
- **[high] ~26 ad-hoc breakpoints incl. typos and near-duplicates** — `ipo-custom.css (full file); style.css:2009-4642`
  - _category:_ responsive-structure
  - Breakpoint values in use include obvious typos 786px (ipo-custom.css, clearly meant 768) and 765px, plus clusters that should be one value each: 990/991/992, 767/768, 1000/1010/1024, 1250/1270/1280/1300, 1500/1550, 1600/1650, and one-off 350/380/390/400/500/576/600/601/920/950/1100/1350/1799/1800. Whitespace is also inconsistent: '@media(max-width:768px)', '@media ( max-width: 768px )', '@media only screen and (max-width: 768px)' all coexist.
  - **Fix:** Define a canonical breakpoint ladder (e.g. 1800/1650/1500/1400/1200/991/767/576/380) as documented constants. Snap 786→768, 990→991, 950→991, 1010/1024→1100, 1250/1270/1280/1300→1300, etc., re-testing each. Normalize media-query syntax to one form.
- **[high] 255 selectors defined more than once; heavy same-selector override stacking** — `theme-wide; examples: .img_box li span (style.css:200,991,1241,3246,3249; style-shojib.css:300; ipo-custom.css:402); header .search_input (style.css:1408,1444; responsive.css:131,145; ipo-custom.css:545,1265,1327); .calendar_area .numCount li (13x); .meet_area .owl-carousel .item (7x)`
  - _category:_ override-stacking
  - Of ~2781 rule blocks only 1543 are unique selectors; 255 selectors are redefined. Many redefinitions re-set the same property in different files/snippets, so the winner is decided purely by load order — which is why ipo-custom.css needs 490 !important to override style.css. This makes every visual change a cascade-archaeology exercise.
  - **Fix:** During consolidation, collapse each selector's scattered definitions into one authoritative block (base value + explicit responsive/lang overrides). Eliminating duplicate property sets is what lets you drop most of the 1214 !important.
- **[medium] Fully dead rule: typo 'ine-height' (the known typo) — entire block is inert** — `assets/styles/style.css:200-202`
  - _category:_ dead-code
  - .img_box li span { ine-height: 1.5!important; } — the only declaration is an invalid property ('ine-height'), so the rule does nothing. Even if corrected to line-height, it would be overridden: the same selector is redefined with a valid line-height:1.4 at style.css:991 and again at 1241 and 3246/3249. The whole 3-line block is dead.
  - **Fix:** Delete style.css:200-202 outright. Do not 'fix' the typo — line-height for this selector is already authoritatively set at style.css:991.
- **[medium] Orphaned stylesheet: root popup.css is never enqueued and never referenced** — `popup.css (theme root)`
  - _category:_ dead-code
  - popup.css defines .custom-popup (with many !important) but functions.php has no add_style('popup'), no <link>/wp_enqueue references it, and grep for 'custom-popup' / 'popup.css' across all PHP returns nothing. It is a fully orphaned file.
  - **Fix:** Remove popup.css from the repo (or confirm it is intentionally dead and document it). Saves the entire file.
- **[medium] RTL/LTR override architecture is coherent and must be preserved; Arabic (ar) has zero dedicated rules** — `ipo-custom.css:311-1228 ('#10 LTR – ENGLISH', ~917 lines, 239 [lang=en-US] rules); style.css (24); plus he-IL overrides`
  - _category:_ i18n-rtl
  - The theme is RTL-by-default; [lang="en-US"] selectors flip direction/margins/padding to LTR (e.g. ipo-custom.css:314-359). This is the correct pattern and any consolidation MUST keep these overrides paired with their base rules. However [lang="ar"] (also RTL via WPML) has 0 dedicated rules across all files — Arabic inherits the he-IL RTL defaults, which is fine for layout but means any he-specific typography/spacing tuned under default selectors silently applies to ar too.
  - **Fix:** In the target architecture, keep a dedicated language-overrides layer (base RTL → [lang=en-US] LTR). Confirm with the team whether ar needs its own tuning; if not, document that ar deliberately reuses the RTL default.
- **[medium] ipo-custom.css is organized by source snippet/author, not by concern; ~22% is comments/blank** — `assets/styles/ipo-custom.css (37 sections: #2..#40, e.g. :311 '#10 LTR', :1525 '#13 Responsive', :1964 '#14 QA – Sagi', :2714 '#16 QA – Shojib', :4187 '#27 Aviad Css', :6849 '#38 Aviad Css all web')`
  - _category:_ fragmentation
  - The consolidation preserved snippet provenance (good for traceability) but the result is concern-fragmented: responsive rules live in #13 AND #14/#16/#25/#27; header rules in #8/#12/#30; calendar in #36/#39; two separate 'Aviad Css' blocks (#27, #38) and three QA blocks by different devs overlap heavily. 1587 of 7330 lines are pure comment/blank.
  - **Fix:** This file is the prime consolidation target: split it by concern into the target layers (base/components/responsive/language) rather than by snippet. Keep a one-line provenance comment where useful, drop the rest of the banner noise.
- **[low] ~17 empty rule blocks across theme files** — `style.css:1090,1095,1100,1412,2364,3331,3681,4491; style-sagi.css:90; ipo-custom.css:442,648,1920,2239,3070,3074,5631,5635`
  - _category:_ dead-code
  - Empty selectors carry zero styling but add parse weight and noise, e.g. p a {}, header .search_input img {}, footer .container .row {} (style.css:2364), .calendar_area .calendar-row {} (style-sagi.css:90), [lang="en-US"] .desktop-menu .menu li a {} (ipo-custom.css:442), .tax-list .panel {} (ipo-custom.css:3070).
  - **Fix:** Delete all empty blocks. Trivial, safe, ~40 lines removed.
- **[low] Empty/placeholder vendor stylesheets are still enqueued** — `functions.php:102,109,110; assets/styles/magnific-popup.css (0 lines), splide.min.css (0), splide-core.min.css (0)`
  - _category:_ vendor
  - magnific-popup.css, splide.min.css and splide-core.min.css are all 0-byte/empty but still enqueued, adding empty HTTP requests. Splide's actual styling is supplied by moreconcerts.css; magnific-popup may be unused entirely (verify JS).
  - **Fix:** Drop the empty enqueues. If Splide's official CSS is genuinely needed later, restore a real file; otherwise keep relying on moreconcerts.css. Verify magnific-popup JS usage before removing its style.
- **[low] animate.css ships 152 animations; only fadeIn/fadeInUp are used** — `assets/styles/animate.css (3340 lines); used via assets/scripts/anim-reveal.js:119-188 ('animated','fadeInUp','fadeIn')`
  - _category:_ vendor
  - anim-reveal.js (WOW-style reveal) only ever applies .animated + .fadeIn / .fadeInUp. The other ~150 keyframe animations (bounce, flip, rotate, zoom, lightSpeed, roll, etc.) and their classes are never referenced in markup or JS. Markup uses custom trigger classes animate_wow / fade-in-bottom, not animate.css names.
  - **Fix:** Trim animate.css to the .animated base + fadeIn/fadeInUp keyframes (or inline those ~40 lines into the theme), removing ~3000 lines of unused vendor CSS. Not dead today, but a large trim opportunity.
- **[info] Owl-* rules are NOT broadly dead yet — only moreConcerts migrated, and its legacy class still renders on 3 pages** — `assets/styles/style.css (49 owl rules), ipo-custom.css (43), responsive.css (20), style-shojib.css (2); markup: parts/artist.php:60, page-templates/artist.php:60, page-templates/template-concert-screen.php:163 still output .owl-carousel .moreConcerts-slider`
  - _category:_ owl-migration
  - The new Splide module is scoped to .moreConcerts-splide (single-program.php:388, single-artist.php:391 .t3, Simple_page.php:151) and is cleanly isolated in moreconcerts.css. But .moreConcerts-slider (owl) markup still exists on artist.php / concert-screen, and main.js:155 still inits it; and the other 6 sliders (meet/recommended/recommended-child/upcoming/video/player) are still owl (main.js:93,297,351,396,461,493). So generic .owl-* and .moreConcerts-slider styling remains live. style-shojib.css:25-30 (.moreConcerts-slider:not(.owl-loaded)) is also still live.
  - **Fix:** Do NOT remove owl CSS now. After the pending 6-slider Splide migration AND converting the 3 legacy .moreConcerts-slider markups to .moreConcerts-splide, then remove owl.carousel.min.css + all .owl-* theme rules and main.js owl inits. Track this as a follow-up tied to the JS migration, not a CSS-only task.

### Duplicates

- **.img_box li span redefined 7x (one fully dead with the ine-height typo)**
  - `assets/styles/style.css:200`
  - `assets/styles/style.css:991`
  - `assets/styles/style.css:1241`
  - `assets/styles/style.css:3246`
  - `assets/styles/style.css:3249`
  - `assets/styles/style-shojib.css:300`
  - `assets/styles/ipo-custom.css:402`
  - **Fix:** Collapse into one base rule (line-height:1.4, font-size:1.6rem, font-weight:300 from :991) plus explicit responsive overrides; delete the dead :200 block and the [lang=en-US] dupes folded into the language layer.
- **header .search_input redefined 7x across 3 files**
  - `assets/styles/style.css:1408`
  - `assets/styles/style.css:1444`
  - `assets/styles/responsive.css:131`
  - `assets/styles/responsive.css:145`
  - `assets/styles/ipo-custom.css:545`
  - `assets/styles/ipo-custom.css:1265`
  - `assets/styles/ipo-custom.css:1327`
  - **Fix:** Consolidate desktop definition once, then one tablet and one mobile override; merge the [lang=en-US] variant into the language layer.
- **.calendar_area .numCount li redefined 13x (most-duplicated selector in the theme)**
  - `assets/styles/style.css (multiple)`
  - `assets/styles/responsive.css`
  - `assets/styles/ipo-custom.css`
  - `assets/styles/style-sagi.css`
  - **Fix:** Single owner block in the calendar component section + grouped responsive overrides; this alone removes ~12 redundant declarations and several !important.
- **.meet_area .owl-carousel .item redefined 7x**
  - `assets/styles/style.css`
  - `assets/styles/responsive.css`
  - `assets/styles/ipo-custom.css`
  - **Fix:** Consolidate now (still live owl slider); revisit when meet-slider migrates to Splide.
- **Duplicate @media (min-width: 992px) blocks in the dedicated responsive file**
  - `assets/styles/responsive.css:18`
  - `assets/styles/responsive.css:293`
  - **Fix:** Merge into a single 992px block. Also reconcile max-width:767 (responsive.css:304) vs max-width:768 (responsive.css:762) off-by-one overlap.
- **Two separate 'Aviad Css' snippet sections + three per-developer QA sections with overlapping concerns**
  - `assets/styles/ipo-custom.css:4187 (#27 Aviad Css)`
  - `assets/styles/ipo-custom.css:6849 (#38 Aviad Css all web)`
  - `assets/styles/ipo-custom.css:1964 (#14 QA – Sagi)`
  - `assets/styles/ipo-custom.css:2575 (#15 orckestra QA)`
  - `assets/styles/ipo-custom.css:2714 (#16 QA – Shojib)`
  - **Fix:** Dissolve author-based sections; redistribute their rules into concern-based layers, merging duplicate selectors as you go.
- **margin/padding utility classes defined twice (helper.css vs spacing.css)**
  - `assets/styles/helper.css`
  - `assets/styles/spacing.css`
  - **Fix:** helper.css and spacing.css are both mt_/mb_/pt_/pl_ utility generators and neither is enqueued by the child functions.php; the mt_/pt_ classes have zero usage in child markup. Verify against wpstack-parent (not in this checkout) — if parent doesn't need them either, delete one or both.

### Dead / unused

- **Dead rule block: .img_box li span { ine-height: 1.5!important; }** — `assets/styles/style.css:200-202` ('ine-height' is not a valid CSS property (the known typo); the only declaration in the block is therefore inert, and line-height for this selector is authoritatively set later at style.css:991.)
- **Orphaned stylesheet popup.css (.custom-popup)** — `popup.css (theme root)` (No add_style('popup') in functions.php; grep for 'popup.css' and 'custom-popup' across all PHP returns nothing.)
- **~17 empty rule blocks** — `style.css:1090,1095,1100,1412,2364,3331,3681,4491; style-sagi.css:90; ipo-custom.css:442,648,1920,2239,3070,3074,5631,5635` (Selectors with {} or {whitespace-only} bodies — verified via multiline grep for \{\s*\}.)
- **Empty/placeholder vendor CSS still enqueued** — `assets/styles/magnific-popup.css, splide.min.css, splide-core.min.css (functions.php:102,109,110)` (wc -l reports 0 lines for all three; they generate empty network requests.)
- **~3000 lines of unused animate.css animations** — `assets/styles/animate.css (3340 lines, 152 keyframes)` (anim-reveal.js only applies .animated + fadeIn/fadeInUp (anim-reveal.js:119-188); no markup/JS references the other ~150 animation class names. Trim, not full delete (fadeIn/fadeInUp are live).)
- **Possibly-dead utility files helper.css + spacing.css (verify against parent)** — `assets/styles/helper.css, assets/styles/spacing.css` (Neither is enqueued by child functions.php; mt_/pt_/etc. utility classes have zero usage in child PHP markup. Parent theme wpstack-parent is not in this checkout, so cannot confirm parent does not enqueue/use them — flag for verification, do not delete blindly.)
- **NOT dead yet — owl-* CSS and .moreConcerts-slider (owl) styling** — `style.css (49 owl rules), ipo-custom.css (43), responsive.css (20), style-shojib.css:25-30` (6 sliders still use owlCarousel (main.js:93,297,351,396,461,493) and legacy .moreConcerts-slider markup persists at parts/artist.php:60, page-templates/artist.php:60, template-concert-screen.php:163. Listed here only to prevent premature removal.)

### Recommendations

- TARGET ARCHITECTURE — replace the current author/snippet-organized pile with a concern-based layer set, each internally ordered desktop → tablet → mobile, keeping the existing load order so the cascade still resolves: (1) vendor.css [bootstrap/owl/slick/splide/animate-trimmed/magnific — only what's used], (2) base.css [reset, html rem-scaling, typography, colors, RTL defaults], (3) components.css [header, menu, footer, calendar, sliders, forms, breadcrumbs, cards — one authoritative block per component], (4) responsive.css [ALL @media rules, grouped by canonical breakpoint, desktop→tablet→mobile], (5) language.css [the [lang=en-US] LTR layer + any future [lang=ar]], loaded LAST so it still wins without needing !important. This preserves the proven RTL-default / LTR-override model while making every concern findable in one file.
- Define and document a canonical breakpoint ladder (suggest 1800 / 1650 / 1500 / 1400 / 1200 / 991 / 767 / 576 / 380) and migrate the ~26 ad-hoc values onto it: snap 786→768/767, 990/950→991, 1000/1010/1024→1100, 1250/1270/1280/1300→1300, 1550→1500, 1600/1650 keep. Normalize media-query syntax to a single form. Re-test each component at every breakpoint after snapping.
- Collapse the 255 multiply-defined selectors into one authoritative definition each (base + explicit responsive/lang overrides). This is the lever that removes most of the 1214 !important — once load-order is no longer the override mechanism, the !important crutch in ipo-custom.css (490 of them) can be largely deleted.
- Quick safe wins to do first (no behavior change): delete style.css:200-202 (dead ine-height block), remove ~17 empty rules, delete orphaned popup.css, drop the 3 empty vendor enqueues, and trim animate.css to .animated+fadeIn/fadeInUp. Estimated immediate removal ~3,100 lines (mostly animate.css trim) with effectively zero visual risk.
- Sequence the owl removal with the JS work, NOT as a CSS-only step: after the pending meet/recommended/recommended-child/upcoming/video/player Splide migration AND converting the 3 remaining .moreConcerts-slider markups (artist.php, page-templates/artist.php, template-concert-screen.php) to .moreConcerts-splide, then delete owl.carousel.min.css + all ~114 .owl-* theme rules + style-shojib.css:25-30 + the owl inits in main.js. Until then leave owl CSS in place.
- ESTIMATED TOTAL REDUCTION: of ~14,589 in-scope hand-authored lines, expect ~30-45% removable through dedup once duplicates collapse and !important is unwound (~255 duplicate selectors + comment/blank noise — ipo-custom.css alone is 1587 comment/blank lines, ~22%). Adding the animate.css trim (~3000) and owl removal (~114 rules across files, post-migration) pushes the whole-theme CSS reduction toward 40-50%. Target end state: ~5 organized files instead of the current sprawl, with the cascade resolved by structure rather than by !important.
- Confirm helper.css/spacing.css and magnific-popup against the wpstack-parent theme (not in this checkout) before deleting — they are not enqueued by the child and have no child-markup usage, but the parent may own them.

---

## JavaScript audit (wpstack-child WordPress theme — Israel Philharmonic)

The theme runs ~20 front-end JS files (≈4,150 LOC excluding vendors) loaded globally on every page via the parent theme's add_script() loop (functions.php:70-97), in this output order: scrollspy, wow(EMPTY), anim-base, anim-reveal, isotope, plugins(Bootstrap+owl+magnific), main, scripts-admin, splide.min, sliders-splide, calendar-behavior, calendar-filter, [loader], slick.min, hc-sticky, scripts-custom, ipo-custom. Three slider engines coexist (owlCarousel bundled in plugins.js drives 6 sliders in main.js; Splide drives moreConcerts + calendar; Slick drives slider-banner + calendar-slider). The consolidated ipo-custom.js (the old WordPress code-manager snippets, loaded LAST) still contains the original per-snippet duplication: each former snippet kept its own IIFE/ready block, its own throttle definition, and several selectors are bound 2x and fight each other. Two outright runtime errors fire on most/all pages (ReferenceError: WOW is not defined in anim-reveal.js; unguarded querySelector on input_3 in ipo-custom.js, and a global isScrolledIntoView redefinition that silently breaks reveal offsets). Performance is dominated by many separate unthrottled scroll/resize listeners (at least 12 window-scroll + 5 window-resize handlers across files), several of which read layout (offset/height) and write styles every frame, plus heavy work on load. Owl removal is close: only the 6 main.js owl inits + plugins.js owl code + owl.carousel.min.css remain; markup classes still exist (player-slider has 0 PHP refs = dead).

**Metrics**

- Front-end JS files loaded globally: 17 (+ admin/ajax). Non-vendor LOC ≈ 2,750; ipo-custom.js alone 891 lines / 27KB
- Slider engines coexisting: 3 (owlCarousel via plugins.js for 6 sliders; Splide for moreConcerts+calendar; Slick for slider-banner+calendar-slider)
- owl-driven sliders in main.js: meet(93), moreConcerts-slider(155), recommended(297), recommended-slider-child(351), upcoming(396), video(461), player(493) — note player has 0 PHP refs (dead)
- Window scroll handlers: main.js 5 (l.25,37,223,554,576), ipo-custom.js 2 (l.315,377), anim-reveal.js 3 (l.44,91,121,157), scrollspy.js 1 — ~11+ live, only 2 throttled (order_area rAF l.223; scrollspy)
- Window resize handlers: ipo-custom.js l.522/622/834, scrollspy l.187/207, main.js #19-style l.622 — 5+; load-time heavy work: 5x setTimeout blocks + wrapInner letter splitting + menu width loop
- Runtime errors on load: 2 hard (WOW undefined anim-reveal.js:134; input_3 unguarded ipo-custom.js:865) + 1 silent regression (isScrolledIntoView redefined ipo-custom.js:868) + 1 conditional (readMoreButton undefined ipo-custom.js:192)
- Duplicate-bound selectors: mobile-search-toggle (2x), .rendered-date/#monthsPopup (2x), .toggle_link (2x across files), .slider-banner slick (2x), submenu positioners (2x: set_submenu_position + setMenuPosition)
- console.log noise left in production: calendar-filter.js 17, calendar-behavior.js 9, ajax_get_events ~6, ajax_get_month ~4 (run on real user pages)
- wow.js = 0 bytes (empty) yet enqueued (functions.php:72); WOW class never defined anywhere in child theme or third-party parts

### Findings

- **[critical] ReferenceError: WOW is not defined — breaks the reveal-animation ready() block on every page** — `assets/scripts/anim-reveal.js:130,134,142; assets/scripts/wow.js (0 bytes); functions.php:72`
  - _category:_ bug
  - anim-reveal.js does WOW.prototype.addBox=... (l.130), new WOW({...}) (l.134) and wow.init() (l.142). The WOW library is never loaded: wow.js is enqueued (functions.php:72) but is an empty 0-byte file, there is no wow.min CDN in parts/ipo-third-party-head.php, and grep finds no WOW definition anywhere in the child theme. The window.WOW global is therefore undefined, so the first reference throws a ReferenceError, aborting the rest of that jQuery(document).ready block (including the .wow-w scrollSpy:exit binding). Note an unconditional $(window).scroll handler (l.121) is registered just before, so it survives, but the WOW init and scrollSpy wiring do not.
  - **Fix:** Decide intent: if reveal-on-scroll is wanted, load a real WOW build (or migrate .animate_wow/.wow to AOS which is already loaded in parts/ipo-third-party-footer.php). If not, delete the empty wow.js enqueue and the whole WOW block in anim-reveal.js. Either way remove the 0-byte wow.js.
- **[critical] Unguarded querySelector on input_3 throws TypeError on every page that lacks the EN contact input** — `assets/scripts/ipo-custom.js:864-865`
  - _category:_ bug
  - var inputElement = document.querySelector('[lang="en-US"] input[name="input_3"]'); inputElement.setAttribute('placeholder','Full name'); There is no null check. On the entire Hebrew site (default), on Arabic, and on any EN page without that specific Gravity/Contact form field, querySelector returns null and .setAttribute throws 'Cannot read properties of null'. This runs at top level (not inside a ready/guard), so it aborts the remaining top-level statements in ipo-custom.js after this point (the isScrolledIntoView redefinition at l.868 still runs because it is a hoisted function declaration, but any other trailing top-level code is at risk).
  - **Fix:** Guard: var el = document.querySelector(...); if (el) el.setAttribute('placeholder','Full name'); Better, scope it to pages that actually render that form, or set the placeholder server-side in the form template.
- **[high] Global isScrolledIntoView is redefined (losing the $extra offset param) — silently breaks title + data-load-on-view reveals** — `assets/scripts/anim-base.js:1 vs assets/scripts/ipo-custom.js:868; consumers anim-reveal.js:47,94 and main.js`
  - _category:_ bug
  - anim-base.js defines the canonical global isScrolledIntoView(elem, $extra=0) used by anim-reveal.js as isScrolledIntoView($(this),120) (title animation) and isScrolledIntoView($el,900) (data-load-on-view lazy reveal). ipo-custom.js loads LAST (functions.php:94) and redefines a function isScrolledIntoView(elem) with NO $extra param and different math (returns elemTop-500<=docViewTop). Because both are plain function declarations on the global scope, the later one (ipo-custom) wins. Result: every caller that passes an offset (120, 900) has that argument silently ignored, so reveal thresholds are wrong everywhere. main.js also has a THIRD copy at the bottom (l.868-877 region of main.js, identical to ipo-custom's). Three competing definitions of one helper.
  - **Fix:** Keep exactly one definition (the anim-base.js version with $extra). Delete the ipo-custom.js:868 and main.js copies. This is a behavioural fix, not just cleanup.
- **[high] Two mobile-search-toggle click handlers fight each other** — `assets/scripts/ipo-custom.js:406-417 (snippet #12) and 539-554 (snippet #17)`
  - _category:_ bug
  - #12 binds .mobile-search-toggle click to slideUp/slideDown of .mobile-header .width-33.t1. #17 binds the SAME selector to a toggle of .open class + body.search-open + input focus/blur. Both fire on the same click; their state models are independent and contradictory (one animates a panel, the other toggles classes and clears the input). This is a classic 'fighting dup' carried over verbatim from the separate code-manager snippets. Order of execution is registration order (#12 then #17).
  - **Fix:** Merge into a single mobile-search controller with one source of truth for open/closed state. Delete whichever behaviour is obsolete (likely #12's slideUp/Down).
- **[high] slider-banner Slick initialized twice (double-init)** — `assets/scripts/ipo-custom.js:30-44 (snippet #2) and 234-250 (snippet #7)`
  - _category:_ bug
  - jQuery('.slider-banner.desktop').slick({...}) and jQuery('.slider-banner.mobile').slick({...}) appear verbatim in BOTH snippet #2 and snippet #7, each inside its own ready(). On a page that has .slider-banner, Slick is initialized twice on the same element; the second init either no-ops with a warning or duplicates clones/arrows depending on Slick version, and doubles the bound resize/scroll handlers Slick attaches internally. Note also the original snippet #7 (Home Page) additionally contains the main-video mobile-source swap, so the two are not pure copies — but the slick block is duplicated.
  - **Fix:** Keep one slider-banner init (in the home section module), remove the other. Add an is-initialized guard (e.g. if(!$el.hasClass('slick-initialized'))) before any slick() call.
- **[high] Months Popup bound twice, language-agnostic — Hebrew handler wins on EN pages** — `assets/scripts/ipo-custom.js:53-93 (snippet #4 EN) and 100-140 (snippet #5 HE)`
  - _category:_ bug
  - Snippet #4 (EN) and #5 (HE) each call $(document).ready, each bind $('.rendered-date').on('click') and $(document).on('click','#monthsPopup li') and each define a renderMonthsList(). Neither checks the page language. Both run on every page. They define two different renderMonthsList functions and two different #monthsPopup li handlers: clicking a month fires BOTH handlers, navigating to '/en/calendar/?...' (EN, l.90) AND '/לוח-שנה/?...' (HE, l.123) — last one registered (HE) typically wins the navigation, so the English calendar month picker sends users to the Hebrew calendar. The month-name array is also language-specific but both render handlers are attached.
  - **Fix:** Gate each block on document.documentElement.lang (e.g. lang==='en-US'). Better: one parametrised module that picks the month-name array and base URL from lang. Remove the duplicate renderMonthsList/handlers.
- **[high] Many separate unthrottled window scroll/resize listeners; several read layout and write styles every frame** — `main.js:25,37,554,576; ipo-custom.js:315,377,622,834; anim-reveal.js:44,91,121,157; functions.php:386 (inline hero handler)`
  - _category:_ performance
  - At least ~11 live window 'scroll' subscriptions and ~5 'resize' subscriptions are registered across files, mostly unthrottled. Worst offenders that read layout then write styles per frame: (1) main.js:554-562 reads header.offsetHeight (captured once, ok) but on EVERY scroll computes rgba/box-shadow strings and writes header.style.backgroundColor + boxShadow — forced style write each frame. (2) main.js:576/568 resetAnimationSmoothly plays a Lottie on scroll. (3) main.js:25-32 sticky_header adds/removes class each scroll (unthrottled). (4) main.js:37-43 back-to-top fadeIn/Out each scroll. (5) ipo-custom.js:315 iterates ALL lottie-player elements on every scroll calling isScrolledIntoView (which calls offset()/height() = layout read) per element. (6) ipo-custom.js:377 reads p.offset().top + window height each scroll. (7) anim-reveal.js:44 and :91 each iterate matched elements per scroll calling isScrolledIntoView (offset/height reads); :91 with a 900px threshold runs on '[data-load-on-view]'. Only main.js:223 (order_area, rAF+passive) and scrollspy.js are throttled.
  - **Fix:** Consolidate to ONE rAF-batched scroll dispatcher and ONE debounced resize dispatcher that all consumers subscribe to. Use passive:true. Cache layout reads (offsets/heights) and recompute only on resize, not per scroll frame. Replace per-element offset() reads in the Lottie/reveal loops with IntersectionObserver (eliminates layout thrash entirely). Throttle sticky_header and back-to-top.
- **[high] Three slider engines loaded site-wide; owlCarousel still drives 6 sliders in main.js** — `main.js:93,155,297,351,396,461,493; plugins.js (owl bundled, l.~ fn.owlCarousel); functions.php:78,81,82,91`
  - _category:_ architecture
  - owlCarousel is bundled inside plugins.js (Bootstrap+owl+magnific mega-bundle, 149KB) and inits 6 live sliders in main.js (meet-slider, recommended-slider, recommended-slider-child, upcoming-slider, video-slider) plus the legacy moreConcerts-slider (l.155, now superseded by Splide) and player-slider (l.493, DEAD — 0 PHP refs). Splide (splide.min.js, 30KB) drives moreConcerts-splide (sliders-splide.js) and the calendar (calendar-behavior.js). Slick (slick.min.js, 43KB) drives slider-banner (ipo-custom.js) and calendar-slider (ajax_get_events.js). All three engines + their CSS (owl.carousel.min.css, slick.css, splide.min.css) load on every page. moreConcerts-slider(owl) and moreConcerts-splide(Splide) even appear in overlapping templates (artist.php has both classes available), risking double behaviour.
  - **Fix:** Per the pending plan, migrate meet/recommended/recommended-child/upcoming/video to Splide (reuse the sliders-splide.js pattern with per-slider config), delete the player-slider init (dead), then rebuild plugins.js WITHOUT owl (keep Bootstrap+magnific only or split them), and drop owl.carousel.min.css. Standardize on Splide; keep Slick only if calendar-slider variableWidth can't be reproduced — but ideally migrate it too and drop slick (43KB + slick.css).
- **[medium] readMoreButton is undefined inside the read-less handler (ReferenceError on click)** — `assets/scripts/ipo-custom.js:184-196 (snippet #6)`
  - _category:_ bug
  - readLessButtons.forEach(button => { ... if(lang==='he'){ ... readMoreButton.textContent='קרא עוד'; readMoreButton.classList.remove('read-less'); }}). Only 'button' is in scope; readMoreButton is never declared in this closure. Clicking a Hebrew read-less control throws ReferenceError: readMoreButton is not defined. (The EN read-more/less toggle in the same snippet is handled separately via the read-more buttons block and works.)
  - **Fix:** Use the in-scope 'button' (or the closest .program-info's read-more button). Simpler: drop the separate read-less loop and let the single read-more handler toggle both states (it already does for EN).
- **[medium] header.offsetHeight read without null guard on non-front pages** — `assets/scripts/main.js:541-562`
  - _category:_ bug
  - DOMContentLoaded handler: const header=document.querySelector('.header'); const headerHeight=header.offsetHeight; On any page/template that does not render an element with class .header (some landing/Shogun templates use a different header markup), header is null and header.offsetHeight throws, aborting the handler (including the scroll-driven header background effect). The intronlottie branch is guarded but the headerHeight read is not.
  - **Fix:** if(!header) return; before reading offsetHeight. Same pattern needed for lottieElementlogo at main.js:565-573 (resetAnimationSmoothly calls lottieElementlogo.setDirection/.play without a null check; throws on pages without .logo-lottie).
- **[medium] Heavy synchronous work and multiple staggered setTimeouts on load** — `main.js:4,91-142; anim-reveal.js:5-42,104-109; ipo-custom.js:300-332,436-447; calendar-behavior.js:36; loader.js`
  - _category:_ performance
  - On load the theme: splits every .home .animate-letters heading into per-letter spans via regex+wrapInner (anim-reveal.js:5-42) — DOM-heavy; runs a menu-width loop measuring each .menu-pc .menu-item and writing width (ipo-custom.js:436-447, layout read+write in a loop); fires numerous setTimeout chains (main.js:4 preloader, main.js:91-142 owl meet-slider after 200ms then AOS attribute stamping per child, ipo-custom.js:323-332 two load timeouts, calendar-behavior.js:36 select_next_day after 2500ms, anim-reveal.js:104/107 retries at 800/2000ms, loader.js two competing load handlers). The owl meet-slider init is deferred 200ms inside ready then re-iterates owl children twice to stamp data-aos attributes.
  - **Fix:** Defer non-critical work to requestIdleCallback; do the letter-splitting only for headings actually in/near viewport; batch the menu-width measure (read all, then write all). Collapse loader.js's two duplicate load handlers (see dead/dup section).
- **[medium] order_area sticky logic implemented twice with different thresholds** — `main.js:206-229 (rAF-throttled, threshold 600/800) vs ipo-custom.js:374-398 (snippet #11, unthrottled, offset-of-.moreevent based)`
  - _category:_ bug
  - main.js has a refactored rAF+passive order_area sticky toggle (good). ipo-custom.js snippet #11 ALSO toggles .order_area .sticky on scroll, unthrottled, using a different rule (scroll+window.height >= .timeZone_area .moreevent offset+100), and additionally calls p.offset() each scroll (layout read; throws if .moreevent missing — but it is guarded by the length check at l.376). Two competing rules add/remove the same 'sticky' class on the same element; they will flicker against each other on pages where both element sets exist.
  - **Fix:** Keep only one order_area sticky implementation (the rAF one in main.js). Remove the ipo-custom.js:374-398 block, or fold its .order_area .btn smooth-scroll-to-#time_zone behaviour into the main.js module.
- **[medium] Two submenu positioner functions doing overlapping work, both on resize/hover** — `ipo-custom.js:507-522 (snippet #16 set_submenu_position) and 814-836 (snippet #23 setMenuPosition)`
  - _category:_ architecture
  - set_submenu_position() sets .desktop-menu .menu > li > .sub-menu top = header height, on load + throttled resize. setMenuPosition() ALSO repositions .desktop-menu .menu > li > ul.sub-menu (right/top/width from header offset) on resize + on every menu li mouseenter. Both read header height/width and write sub-menu CSS; they target the same submenu elements with different rules, so the visible position depends on which ran last (hover triggers setMenuPosition, resize triggers both). Both also call $('.site > header').offset()/.height() without guarding for header absence (set_submenu_position:511 will throw if .site > header is missing).
  - **Fix:** Merge into one submenu-positioning module with a single rule, called on load + debounced resize + (if needed) mouseenter. Guard the header lookups.
- **[medium] Production console.log/console.warn noise in calendar + ajax scripts** — `calendar-filter.js (17 logs, e.g. l.4,24-25,56,61,...); calendar-behavior.js (9 logs, l.108,113,155,...); includes/ajax/ajax_get_events.js (l.16,25,...); ajax_get_month.js (l.16,110,...); ajax_get_calendar_events.js; ajax_process_posts.js`
  - _category:_ maintainability
  - These run on real user-facing pages (the calendar runs everywhere per functions.php:84-86). filterEvents() logs on every keyup and per event in a loop (calendar-filter.js:22-152 logs ~5 lines per event), which on a busy calendar is hundreds of console writes per keystroke — measurable jank in some browsers/devtools-open, and information leakage.
  - **Fix:** Strip all console.* from front-end calendar/ajax scripts (keep error logging only, behind a debug flag). The per-event logging loop in filterEvents is the highest priority.
- **[medium] throttle helper defined 3+ times; block-scoped function declarations inside if() are dead** — `anim-base.js:17 (global throttle); ipo-custom.js:472-503 and 788-810 (inner 'if(typeof throttle!==function){ function throttle(){...} }'); scrollspy.js:125 (own throttle)`
  - _category:_ maintainability
  - anim-base.js loads first and defines a global throttle. ipo-custom.js (loads last) wraps two more throttle definitions in 'if(typeof throttle!=="function")' — but throttle IS already a function (from anim-base), so these inner declarations never run; they are dead and also rely on block-scoped function-declaration hoisting which is non-standard across engines. scrollspy.js has its own (Underscore) throttle. So there are 2 live implementations (anim-base + scrollspy/underscore) and 2 dead ones, with different semantics (anim-base's takes no args through; the snippet ones pass args/options).
  - **Fix:** Provide one shared throttle + debounce util loaded first; delete the dead inner copies in ipo-custom.js; leave scrollspy.js's internal copy (vendor) alone or refactor scrollspy onto the shared util.
- **[low] player-slider owl init is dead (no markup renders the class)** — `main.js:493-521`
  - _category:_ dead-code
  - $('.owl-carousel.player-slider').owlCarousel({...}) — grep across all *.php returns 0 references to player-slider. This slider never appears in the DOM, so the init is a no-op that still parses and ships.
  - **Fix:** Delete the player-slider owl block. Removes one of the 6 owl inits, easing owl removal.
- **[low] create_events_ajax.js is debug-only console.log scaffolding (acf-button hooks)** — `assets/scripts/create_events_ajax.js (whole file)`
  - _category:_ bug
  - Every acf.addAction/addFilter handler just console.log()s arguments (and one sets data.custom_key='value'). It is leftover ACFE button debug scaffolding. Also note it is not in the add_script list (functions.php) at all — so it may already be orphaned/enqueued elsewhere (create-event includes are commented out at functions.php:25-27).
  - **Fix:** Confirm it is unused (the create-event includes are disabled) and delete the file, or at minimum strip the console.log debug output if any hook is still needed.
- **[low] loader.js registers two competing window load handlers adding the same body class** — `assets/scripts/loader.js:1-14 and 17-24`
  - _category:_ bug
  - Inside a ready() it binds window.load -> setTimeout(0) -> body.addClass('finished-loading'); then OUTSIDE ready it binds another window.load -> setTimeout(100) -> the same body.addClass('finished-loading'). Two handlers, two timers, same effect. Pure duplication carried from a copy/paste.
  - **Fix:** Keep one window.load handler. Trivial delete of lines 17-24 (or 1-14).
- **[low] video-slider owl reads children().length and the player Lottie autoplays unconditionally** — `main.js:454-491`
  - _category:_ performance
  - videoSliderItems = $('.video-slider').children().length runs at parse time at top level (outside ready); if .video-slider is absent it's 0 (harmless) but it's a top-level DOM read before DOM may be fully built. The owl video-slider config branches on hasTwoVideoPosts. Minor, but part of the broad pattern of top-level DOM work in main.js after the IIFE's ready() block (lines 234-538 run at IIFE-execution time, i.e. when plugins are loaded but before DOMContentLoaded in some cases) — isotope/owl inits here can run before the DOM nodes exist depending on script position.
  - **Fix:** Move all the top-level (post-ready) slider/isotope/magnific inits in main.js (l.234-538) inside a single document.ready to guarantee DOM availability and a single init pass. Folds naturally into the Splide migration.
- **[low] isotope grid filter wired but the grid init is commented out** — `main.js:250-283`
  - _category:_ bug
  - The $grid.isotope({...}) initialization is commented out (l.250-255), but the .box-tabs_button click handler (l.259-283) still calls grid.isotope({filter:...}) on click. If isotope was never initialized on the grid, the first click calls .isotope() which lazily inits with default options (no itemSelector/layoutMode/originLeft set as intended), giving wrong layout, OR throws if isotope isn't present. isotope.pkgd.min.js is enqueued so the plugin exists, but the intended config is lost.
  - **Fix:** Either restore a proper isotope init with the intended options, or remove the filter handler if isotope grids are no longer used. Verify which templates render .grid/.box-tabs_button.

### Duplicates

- **mobile-search-toggle click handler**
  - `assets/scripts/ipo-custom.js:406-417`
  - `assets/scripts/ipo-custom.js:539-554`
  - **Fix:** Merge into one handler with a single open/closed state model; delete the slideUp/Down variant if obsolete.
- **slider-banner Slick init (desktop+mobile)**
  - `assets/scripts/ipo-custom.js:30-44`
  - `assets/scripts/ipo-custom.js:234-250`
  - **Fix:** Keep one init guarded by !hasClass('slick-initialized'); remove the duplicate.
- **Months Popup (renderMonthsList + .rendered-date + #monthsPopup li handlers)**
  - `assets/scripts/ipo-custom.js:53-93 (EN)`
  - `assets/scripts/ipo-custom.js:100-140 (HE)`
  - **Fix:** Single language-parametrised module gated on html lang; pick month array + base URL by lang.
- **toggle_link click toggle**
  - `assets/scripts/main.js:285-288`
  - `assets/scripts/ipo-custom.js:655-661`
  - **Fix:** Keep one (note slightly different behaviour: main toggles parent .open; ipo slides .toggle-content). Reconcile to one intended behaviour.
- **isScrolledIntoView helper**
  - `assets/scripts/anim-base.js:1-15 (canonical, with $extra)`
  - `assets/scripts/ipo-custom.js:868-877 (no $extra — overrides)`
  - `assets/scripts/main.js (bottom copy, no $extra)`
  - **Fix:** Keep ONLY anim-base.js version; delete the other two (this is also a behaviour fix — see findings).
- **throttle utility**
  - `assets/scripts/anim-base.js:17-26 (global, live)`
  - `assets/scripts/scrollspy.js:125-153 (Underscore, live)`
  - `assets/scripts/ipo-custom.js:472-503 (dead, inside if)`
  - `assets/scripts/ipo-custom.js:788-810 (dead, inside if)`
  - **Fix:** One shared throttle/debounce util loaded first; delete the two dead copies in ipo-custom.js.
- **window load -> body.addClass('finished-loading')**
  - `assets/scripts/loader.js:1-14`
  - `assets/scripts/loader.js:17-24`
  - **Fix:** Delete one of the two identical load handlers.
- **order_area sticky scroll toggle**
  - `assets/scripts/main.js:206-229 (rAF, threshold-based)`
  - `assets/scripts/ipo-custom.js:374-398 (#11, unthrottled, offset-based)`
  - **Fix:** Keep the main.js rAF version; remove the ipo-custom one (fold its .order_area .btn scroll-to behaviour in).
- **submenu positioner**
  - `assets/scripts/ipo-custom.js:507-522 (set_submenu_position)`
  - `assets/scripts/ipo-custom.js:814-836 (setMenuPosition)`
  - **Fix:** Merge to one positioning module; single rule on load+debounced resize(+hover).
- **$rtl computation (html lang === 'en-US')**
  - `main.js:14-19`
  - `main.js:243-248`
  - `anim-reveal.js:10`
  - `sliders-splide.js:17`
  - `includes/ajax/ajax_get_events.js:276-279`
  - `calendar-behavior is implicit`
  - **Fix:** Define one window-level helper (e.g. window.IPO_RTL / getDir()) once and reuse; it is recomputed in ~6 places.
- **WOW reveal block (active vs commented legacy)**
  - `assets/scripts/anim-reveal.js:117-149 (active)`
  - `assets/scripts/anim-reveal.js:151-193 (commented legacy copy)`
  - **Fix:** Delete the commented legacy block; resolve the active block per the WOW-undefined finding.
- **Hebrew/English month-name arrays + month formatting**
  - `assets/scripts/ipo-custom.js:58-61 & 105-108`
  - `includes/ajax/ajax_get_month/ajax_get_month.js:62-88 (+ commented 31-59)`
  - **Fix:** Centralize month-name maps (ideally server-side via wp_localize_script) instead of duplicating in 3 places.

### Dead / unused

- **wow.js — empty 0-byte file still enqueued** — `assets/scripts/wow.js; functions.php:72` (wc -c reports 0 bytes; WOW class is never defined here or via CDN, yet add_script('wow') runs.)
- **player-slider owlCarousel init** — `assets/scripts/main.js:493-521` (grep -rln 'player-slider' --include=*.php returns 0 files; the class is never rendered.)
- **create_events_ajax.js — debug-only ACFE button hooks** — `assets/scripts/create_events_ajax.js (entire file)` (All handlers only console.log; create-event includes are commented out at functions.php:25-27; file is not in the add_script list.)
- **Commented-out legacy WOW reveal block** — `assets/scripts/anim-reveal.js:151-193` (Whole block wrapped in /* */; superseded by the active block above it (l.117-149).)
- **Large commented-out filter logic in filterEvents** — `assets/scripts/calendar-filter.js:48-54,119-141` (Old condition logic left in /* */ comments alongside the live rewrite.)
- **Commented item-revealed / reveal_events dead paths** — `assets/scripts/calendar-behavior.js:40-69,106-107,122; includes/ajax/ajax_get_month/ajax_get_month.js:31-59,113-122` (Multiple commented-out blocks (item-revealed listener, reveal_events call, abbreviated month map) retained.)
- **reveal_events redundantly re-mounts Splide inside its per-item loop** — `assets/scripts/calendar-behavior.js:132-158` (new Splide('.calendar-row').mount() is constructed inside the .each() callback (l.143-149) on every matched event — needless repeated mounts of the same selector; the function's reveal_events($container) is also commented out at its main call sites (calendar-behavior.js:122).)
- **Duplicate/competing select_next_day_with_events Splide constructions** — `assets/scripts/calendar-behavior.js:4-10,77-86,143-149` (Splide('.calendar-row') is re-instantiated in 3 places (ready, select_next_day_with_events, reveal_events) instead of one shared instance.)
- **isotope grid init commented out while filter handler remains** — `assets/scripts/main.js:250-255 (commented) vs 259-283 (active)` ($grid.isotope init block is commented; click handler still calls grid.isotope().)
- **includes/example-ajax.php references non-existent news-ajax.js** — `includes/example-ajax.php:3,9-11` (Enqueues assets/scripts/news-ajax.js which does not exist in assets/scripts (not in the dir listing); sample/boilerplate.)
- **Empty-string read-less / no-op upscroll branches** — `assets/scripts/anim-reveal.js:75-77 (empty else)` (if/else where the else branch is empty placeholder.)

### Recommendations

- Fix the two hard runtime errors first (cheap, high impact): guard ipo-custom.js:865 input_3 querySelector; resolve WOW (load a real WOW build, migrate to the already-loaded AOS, or delete the block + the empty wow.js). Then fix the silent isScrolledIntoView override by deleting the ipo-custom.js:868 and main.js copies so the anim-base $extra version wins again.
- De-duplicate the 'fighting' bindings in ipo-custom.js: collapse mobile-search-toggle (2x), Months Popup (2x, gate by lang), slider-banner slick (2x, add slick-initialized guard), submenu positioners (2x), order_area sticky (remove the ipo-custom copy, keep main.js rAF), toggle_link (reconcile across main.js/ipo-custom). Each is a verbatim copy from the old code-manager snippets.
- Restructure ipo-custom.js from ~25 independent ready()/IIFE snippet blocks into a small set of feature modules (header/menu, search, calendar-month-popup, program-read-more, home-video) each with a guard that no-ops when its root element is absent — this alone removes most console errors on pages lacking elements.
- Build ONE shared util layer loaded first: dir/rtl detection (replaces ~6 recomputations of html lang==='en-US'), throttle, debounce, isScrolledIntoView. Delete the dead inner throttle copies in ipo-custom.js (l.472, l.788) and the duplicate isScrolledIntoView definitions.
- Consolidate scroll/resize handling: a single rAF-batched scroll dispatcher + a single debounced resize dispatcher with passive listeners. Migrate the per-element offset()-reading reveal/Lottie loops (anim-reveal.js:44/91, ipo-custom.js:315) to IntersectionObserver to eliminate layout thrash. Cache header height/width and recompute only on resize.
- Complete the owl removal: migrate meet/recommended/recommended-child/upcoming/video to Splide using the sliders-splide.js pattern (per-slider config object), delete the dead player-slider init, move all main.js top-level slider/isotope/magnific inits (l.234-538) inside one document.ready, then rebuild plugins.js without owl and drop owl.carousel.min.css. Evaluate dropping Slick too (migrate slider-banner + calendar-slider to Splide) to ship a single slider engine.
- Strip all front-end console.log/warn from calendar-filter.js (esp. the per-event loop), calendar-behavior.js and the ajax scripts; gate any remaining diagnostics behind a debug flag. Delete create_events_ajax.js (debug scaffolding) and the broken includes/example-ajax.php news-ajax enqueue after confirming both are unused.
- Fix the smaller bugs: readMoreButton undefined (ipo-custom.js:192) use in-scope button; null-guard header.offsetHeight (main.js:544) and lottieElementlogo (main.js:565-573); guard the header offset()/height() reads in both submenu positioners; collapse loader.js's duplicate load handlers; restore or remove the commented-out isotope init (main.js:250).
- Reduce Splide re-mounting in the calendar: create one shared Splide instance for .calendar-row and reuse it in ready/select_next_day_with_events/reveal_events instead of constructing it 3x (and inside a per-item loop). After AJAX month load, re-sync that instance rather than newing another.

---

## PHP TEMPLATES inventory, version-sprawl & usage (wpstack-child WordPress theme)

The theme has 9 root PHP files, 74 files in page-templates/ (70 carry a "Template Name:" header making them WP-assignable), 48 active parts in parts/, plus a full stale backup tree parts_OLD/ (42 files, README explicitly says "not loaded... safe to remove"). Version sprawl is severe: 5 live_streaming_page variants (orig/_new/_v3/_v4/_v5), 3 Lobby new-season variants (Lobby-new-season + Lobby-New-season-v2/-v3), 2 orckestrateam (+ -new), 3 orckestraguest variants, history + history-old, plus backup/junk files (newseries.bkp, contents.php.php, lobby.php.txt, Found-commemoration.php [0 bytes], includes/*.bkp, includes/create-program.php-, includes/backup.txt). Structural debt: huge inline <style>/<script> blocks — new-theme-artist-plan.php is 1567 lines with ~1070 lines of inline CSS; Lobby-New-season-v2.php is 1031 lines with 569 inline-CSS lines; Lobby-New-season-v3 (383), Philharmonic-FEST (226), single-artist_plan (246), single-program (135) all carry large inline CSS. The owl "video-slider" block is hand-duplicated 18 times across 7 templates and should be a part. Three static-demo owl moreConcerts instances (hardcoded href="#", fixed 26.02.22 dates, hardcoded images) exist in parts/artist.php, page-templates/artist.php, and page-templates/template-concert-screen.php. 14 part files are fully orphaned (zero references via the_part/get_part/include). Parts are resolved by the parent theme wpstack-parent (the_part/get_part not defined in this child).

**Metrics**

- Root PHP files: 9 (404, header, footer, functions, single-artist, single-artist_plan, single-program, single-serie, taxonomy-serie)
- page-templates/: 74 files; 70 with a 'Template Name:' header (assignable); 2 with NO header (Found-commemoration.php empty, new-theme-artist-plan.php is an include-target not a page template); 2 non-php junk (newseries.bkp, lobby.php.txt)
- parts/: 48 files (3 zero-byte); parts/headers/: 2 (header-1 + header-1-bak backup)
- parts_OLD/: 42 files = stale backup tree (README: 'not loaded by the theme. Safe to remove'); every sampled file is an older/smaller copy of its parts/ counterpart
- live_streaming_page family: 5 variants — live_streaming_page.php(374), _new.php(388), _v3.php(359), _v4.php(79), _v5.php(41). v4/v5 are the refactored the_part versions; orig/_new/_v3 are monolithic
- Lobby new-season family: 3 — Lobby-new-season.php(355), Lobby-New-season-v2.php(1031), Lobby-New-season-v3.php(804)
- orckestrateam: 2 (orckestrateam.php 410 + orckestrateam-new.php 374). orckestraguest: 3 (orckestraguest.php 230, orckestra_guest_new.php 190, orckestra_guest_new1.php 189) + orchestraguest_template.php 141
- Musicians: Musicians-new.php (Template Name 'Musicians') vs template-musicians.php (Template Name 'Musicians old') — old/new pair
- history.php vs history-old.php (Template Name 'Content sections with sidebar')
- Largest inline-CSS offenders: new-theme-artist-plan.php 1070 lines, Lobby-New-season-v2.php 569, Lobby-New-season-v3.php 383, single-artist_plan.php 246, Philharmonic-FEST.php 226, single-program.php 135, Lobby-Children.php 132
- owl 'video-slider' markup duplicated 18 times across 7 templates (Philharmonic-FEST x5, Lobby-listen-from-home x5, Lobby-Listen-to-us x4, Chamber-Concerts/Child-streaming/Lobby-Children/Lobby x1)
- Static-demo owl moreConcerts: 3 instances (parts/artist.php:60, page-templates/artist.php:60, page-templates/template-concert-screen.php:163)
- Orphaned part files (0 refs): 14 — part-hero, part-lobby-podcasts/program/series/upcoming/videos, uppart-lobby-program, section-artists-page, parts/artist.php, hero-section-new (empty), headers/header-1-bak
- Backup/junk in includes/: class-ipo-importer.bkp, ipo-importer.bkp, create-program.php-, backup.txt; empty: program-farthest-date.php, ipo-create-events-custom-functions.php (10 bytes)

### Findings

- **[high] Five live_streaming_page variants coexist** — `page-templates/live_streaming_page.php, live_streaming_page_new.php, live_streaming_page_v3.php, live_streaming_page_v4.php, live_streaming_page_v5.php`
  - _category:_ version-sprawl
  - All five carry distinct 'Template Name' headers (live_streaming_page / - NEW / - V3 / - V4 / - V5) so all are independently assignable in WP. v1/_new are near-identical monolithic markup (374/388 lines, same banner+contant structure at lines 23-82); v3 (359) reworks the hero; v4 (79) and v5 (41) are the modern refactor delegating to the_part('live-streaming-*'). Only one is the real live route; the rest are abandoned iterations.
  - **Fix:** Confirm via WP admin (Pages > Template column) / DB which Template Name is assigned to the live streaming page. Keep that one (likely v4 or v5, the the_part-based versions). Archive the other 3-4. Cannot determine from code alone — verify against live routes first.
- **[high] Three Lobby new-season variants; v2/v3 are monolithic with massive inline CSS** — `page-templates/Lobby-new-season.php (355), page-templates/Lobby-New-season-v2.php (1031), page-templates/Lobby-New-season-v3.php (804)`
  - _category:_ version-sprawl
  - Template Names 'Lobby New season' / 'Lobby New season V2' / 'Lobby New season V3' — all assignable. v2 carries 569 lines of inline <style> + 56 lines inline <script> (a jQuery owl init at line 520); v3 carries 383 inline-CSS lines and has a commented-out the_part at v3:314 ('<!-- $theme->the_part(\'Recommended-series\'); -->'). The three share the same section-upcoming/section-video-banner part calls but diverge heavily in inline styling. This is per-developer iteration left in place.
  - **Fix:** Identify the assigned template for the current season lobby page; keep one. Extract the survivor's inline CSS to a stylesheet (e.g. assets/styles/) and the inline owl init to a JS file (this also feeds the pending owl->Splide migration). Archive the other two after route verification.
- **[high] Enormous inline <style> blocks belong in stylesheets** — `page-templates/new-theme-artist-plan.php (1070 inline-CSS lines / 1567 total), Lobby-New-season-v2.php (569), Lobby-New-season-v3.php (383), single-artist_plan.php (246), Philharmonic-FEST.php (226), single-program.php (135), Lobby-Children.php (132), Chamber-Concerts.php (114), taxonomy-serie.php (104)`
  - _category:_ structure
  - new-theme-artist-plan.php (included by single-artist_plan.php:24) is 68% inline CSS. These blocks ship un-cached, un-minified CSS on every page render, duplicate selectors across templates (e.g. .owl-carousel .owl-stage-outer reappears in Chamber-Concerts:450, Lobby-Children:526), and make the templates unreadable. This is the single largest structural debt in the template layer.
  - **Fix:** Extract per-template inline CSS into dedicated files under assets/styles/ enqueued conditionally (is_page_template). Consolidate the repeated owl/.owl-* rules once owl is migrated to Splide. Prioritize new-theme-artist-plan and Lobby-New-season-v2.
- **[medium] orckestrateam / orckestraguest old-vs-new clusters** — `page-templates/orckestrateam.php + orckestrateam-new.php; orckestraguest.php + orckestra_guest_new.php + orckestra_guest_new1.php + orchestraguest_template.php`
  - _category:_ version-sprawl
  - orckestrateam-new.php (Template Name 'Orckestra Team Page - NEW') vs orckestrateam.php ('Orckestra Team Page'). The guest side has FOUR overlapping templates with confusingly similar Template Names ('Orckestra Guest Page', 'Orchestra_Guests', 'Orchestra_Guests_new1', 'Orchestraguests_template'). orckestra_guest_new1 is a one-character-name-apart copy of orckestra_guest_new (190 vs 189 lines). orckestrateam-new uses get_part('loop-artist-team') (orckestrateam-new.php:314).
  - **Fix:** Determine which team page and which guest page are assigned to live pages. Expect to keep one team + one guest template and archive the other 4. The '_new1' file is almost certainly a throwaway dup.
- **[medium] history vs history-old; Musicians old-vs-new** — `page-templates/history.php (Template Name 'History Page') + history-old.php (Template Name 'Content sections with sidebar'); page-templates/Musicians-new.php (Template Name 'Musicians') + template-musicians.php (Template Name 'Musicians old')`
  - _category:_ version-sprawl
  - history-old.php is renamed-but-retained (its Template Name was changed to 'Content sections with sidebar', possibly to keep it usable for generic sidebar pages — verify before deleting). Musicians-new.php is the active 'Musicians'; template-musicians.php self-labels 'Musicians old'. Also note history_title.php and musical_history.php are separate history-area pages, not necessarily dups.
  - **Fix:** history-old: confirm no page uses 'Content sections with sidebar' before archiving (it was deliberately repurposed). template-musicians ('Musicians old'): archive once no page references it.
- **[medium] 14 fully-orphaned part files (zero the_part/get_part/include references)** — `parts/part-hero.php, parts/part-lobby-podcasts.php, parts/part-lobby-program.php, parts/part-lobby-series.php, parts/part-lobby-upcoming.php, parts/part-lobby-videos.php, parts/uppart-lobby-program.php, parts/section-artists-page.php, parts/artist.php, parts/hero-section-new.php (empty), parts/headers/header-1-bak.php`
  - _category:_ dead-code
  - Cross-referencing every the_part('...') and get_part('...') call plus all dynamic template-name args (get_days_html/get_events_html) across root+page-templates+parts+includes, these parts are never invoked by any mechanism. The part-lobby-* / uppart-lobby-program set appears to be an abandoned lobby-componentization attempt superseded by section-* parts. parts/artist.php is a static demo (see separate finding). header-1-bak.php is an explicit backup of header-1.php.
  - **Fix:** Archive all 14. Keep ipo-marketing-body.php and ipo-third-party-{head,footer}.php (they LOOK orphaned to the_part but are loaded by direct include in functions.php:1572/1581/1589 — do NOT remove).
- **[medium] Backup/junk files scattered through the tree** — `page-templates/newseries.bkp, page-templates/contents.php.php, page-templates/lobby.php.txt (0 bytes), page-templates/Found-commemoration.php (0 bytes), includes/class-ipo-importer.bkp, includes/ipo-importer.bkp, includes/create-program.php-, includes/backup.txt`
  - _category:_ dead-code
  - newseries.bkp (106 lines) is an old copy of newseries.php (370). contents.php.php is a double-extension artifact carrying Template Name 'Contents' — WP will still parse it as a template (assignable) but the filename signals an accidental copy. lobby.php.txt and Found-commemoration.php are 0 bytes (Found- is a typo'd empty sibling of Fund-commemoration.php). includes/*.bkp / *.php- / backup.txt are non-loaded editor backups.
  - **Fix:** Delete the 0-byte and clearly-suffixed backup files (.bkp, .php-, .txt, .php.php) — none are loaded by WP. Verify contents.php.php ('Contents' template) isn't assigned to a live page before removing (double-extension is suspicious but the header is valid).
- **[medium] owl 'video-slider' markup hand-duplicated 18 times across 7 templates** — `page-templates/Philharmonic-FEST.php (x5: 156,198,239,281,323), Lobby-listen-from-home.php (x5), Lobby-Listen-to-us.php (x4), Chamber-Concerts.php:237, Child-streaming.php:138, Lobby-Children.php:285, Lobby.php:247`
  - _category:_ structure
  - The same '<div class="owl-carousel video-slider">' block structure is copy-pasted 18 times. This is exactly the kind of repeated markup that should be a single reusable part (e.g. parts/section-video-slider.php) taking an args array. It also multiplies the upcoming owl->Splide migration surface — migrating one part vs 18 inline copies.
  - **Fix:** Extract a single video-slider part and replace the 18 copies with the_part calls. Do this as part of the pending video-slider owl->Splide migration so the conversion happens once.
- **[medium] Static-demo owl moreConcerts instances with hardcoded placeholder data** — `parts/artist.php:51-128, page-templates/artist.php:51-, page-templates/template-concert-screen.php:154-163`
  - _category:_ dead-demo
  - All three contain a 'קונצרטים נוספים' moreConcerts owl slider populated with hardcoded demo content: href="#" links, fixed dates '26.02.22', literal 'תל אביב', and hardcoded image paths (/wp-content/uploads/2022/06/upcoming-img-5.png). parts/artist.php is also orphaned (no the_part ref). page-templates/artist.php (Template Name 'Artist') and template-concert-screen.php (Template Name 'Concert screen') are assignable but the slider markup is non-dynamic demo scaffolding.
  - **Fix:** parts/artist.php: archive (orphaned + demo). For page-templates/artist.php and template-concert-screen.php: confirm whether either is a live route; if live, the moreConcerts slider must be wired to real data (and migrated off owl); if dead, archive. The hardcoded markup confirms these were never finished.
- **[low] Empty parts are still invoked via the_part (render nothing)** — `parts/live-streaming-categories.php (0 bytes, called from live_streaming_page_v4.php:70), parts/page-banner.php (0 bytes, called from template-urgency-fund.php:48 as the_part('page-banner',$args))`
  - _category:_ latent-bug
  - Two zero-byte parts are actively referenced. the_part renders nothing, so any output/section they were meant to provide is silently missing on those pages. live-streaming-categories was likely a planned filter UI that was never built; page-banner is called WITH args ($args) implying it once rendered a banner.
  - **Fix:** Either implement these parts or remove both the empty file and its the_part call. For page-banner, confirm template-urgency-fund.php isn't silently missing its banner in production.
- **[low] new-theme-artist-plan.php lives in page-templates/ but is an include-target, not a page template** — `page-templates/new-theme-artist-plan.php (no Template Name header) <- included by single-artist_plan.php:24`
  - _category:_ structure
  - It is one of only two files in page-templates/ without a Template Name header. It is pulled in via include 'page-templates/new-theme-artist-plan.php' from the single-artist_plan post-type template. Placing an include-only fragment in page-templates/ (where WP scans for assignable templates) is misleading; it belongs in parts/ or includes/.
  - **Fix:** Move to parts/ (and convert to the_part) or includes/. Low risk but improves clarity. Not dead — it is the live artist-plan body.
- **[info] Four ACTION-* admin utility templates share identical inline 'Template Name: ACTION - IPO IMPORTER' comment but distinct registered names** — `page-templates/action-ipo-importer.php, action-ipo-extracter.php, action-ipo-delete-duplicate-media.php, action-ipo-process_posts.php`
  - _category:_ version-sprawl
  - The grep for the in-file '* Template Name:' comment shows all four print 'ACTION - IPO IMPORTER' on the sampled line, but the authoritative headers differ (IPO IMPORTER / IPO EXTRACTOR / action-ipo-delete-duplicate-media / IPO POSTS PROCESS). These are one-off admin/data-migration utilities run by assigning the template to a throwaway page, not visitor-facing routes.
  - **Fix:** Keep but tag clearly as admin-only utilities (consider gating with current_user_can checks if not already). Not dead, but should not be confused with front-end templates during the refactor.
- **[info] parts_OLD/ is an explicit, non-loaded backup tree** — `parts_OLD/ (42 files incl. parts_OLD/README.txt and parts_OLD/headers/header-1.php)`
  - _category:_ dead-code
  - README.txt states: 'Archive of previous template parts. Active templates are in ../parts/. This folder is not loaded by the theme. Safe to remove if no longer needed.' Every sampled file is an older, smaller version of its parts/ counterpart (section-hero 85 vs 155, loop-program 149 vs 285, loop-artist 99 vs 143, calendar-full 216 vs 270). No file exists only in parts_OLD.
  - **Fix:** Delete the entire parts_OLD/ tree. It is git-tracked history anyway; nothing references it. Highest-confidence removal in this audit.

### Duplicates

- **live_streaming_page template, 5 coexisting iterations**
  - `page-templates/live_streaming_page.php`
  - `page-templates/live_streaming_page_new.php`
  - `page-templates/live_streaming_page_v3.php`
  - `page-templates/live_streaming_page_v4.php`
  - `page-templates/live_streaming_page_v5.php`
  - **Fix:** Keep the assigned live one (likely v4/v5, the the_part-based refactor); archive the other 3-4 after confirming the assigned Template Name in WP admin/DB.
- **Lobby new-season template, 3 iterations**
  - `page-templates/Lobby-new-season.php`
  - `page-templates/Lobby-New-season-v2.php`
  - `page-templates/Lobby-New-season-v3.php`
  - **Fix:** Keep one (route-verify); extract its inline CSS/JS to files; archive the rest.
- **Orckestra team template old/new pair**
  - `page-templates/orckestrateam.php`
  - `page-templates/orckestrateam-new.php`
  - **Fix:** Keep the assigned one; archive the other.
- **Orckestra guest template, 4 overlapping versions**
  - `page-templates/orckestraguest.php`
  - `page-templates/orckestra_guest_new.php`
  - `page-templates/orckestra_guest_new1.php`
  - `page-templates/orchestraguest_template.php`
  - **Fix:** Collapse to one. orckestra_guest_new1.php (1-line diff from orckestra_guest_new) is almost certainly a throwaway.
- **Musicians old/new pair**
  - `page-templates/Musicians-new.php`
  - `page-templates/template-musicians.php`
  - **Fix:** Musicians-new is active ('Musicians'); template-musicians self-labels 'Musicians old' — archive after route check.
- **History original vs old (repurposed)**
  - `page-templates/history.php`
  - `page-templates/history-old.php`
  - **Fix:** history-old's Template Name was changed to 'Content sections with sidebar' (may still be used as a generic layout); verify before archiving.
- **newseries current vs backup**
  - `page-templates/newseries.php`
  - `page-templates/newseries.bkp`
  - **Fix:** Delete newseries.bkp (editor backup, not loaded by WP).
- **header-1 vs its backup**
  - `parts/headers/header-1.php`
  - `parts/headers/header-1-bak.php`
  - **Fix:** Delete header-1-bak.php (orphaned backup).
- **importer classes vs .bkp copies**
  - `includes/class-ipo-importer.php`
  - `includes/class-ipo-importer.bkp`
  - `includes/ipo-importer.bkp`
  - **Fix:** Delete the .bkp files.
- **parts/ vs parts_OLD/ — entire directory is a stale duplicate set**
  - `parts/ (48 files)`
  - `parts_OLD/ (42 older copies)`
  - **Fix:** Delete parts_OLD/ wholesale (README confirms it is unloaded and safe to remove).
- **owl 'video-slider' block copy-pasted 18x**
  - `page-templates/Philharmonic-FEST.php`
  - `page-templates/Lobby-listen-from-home.php`
  - `page-templates/Lobby-Listen-to-us.php`
  - `page-templates/Chamber-Concerts.php`
  - `page-templates/Child-streaming.php`
  - `page-templates/Lobby-Children.php`
  - `page-templates/Lobby.php`
  - **Fix:** Extract one parts/section-video-slider.php and replace all 18 with the_part; do during the owl->Splide video-slider migration.
- **Static-demo moreConcerts owl markup repeated 3x**
  - `parts/artist.php`
  - `page-templates/artist.php`
  - `page-templates/template-concert-screen.php`
  - **Fix:** Archive parts/artist.php (orphaned). For the two page-templates, wire to real data + Splide if live, else archive.

### Dead / unused

- **parts_OLD/ entire directory (42 files + README + headers/)** — `parts_OLD/` (parts_OLD/README.txt: 'This folder is not loaded by the theme. Safe to remove if no longer needed.' Every file is an older/smaller copy of its parts/ counterpart; no file is unique to parts_OLD; zero references in any loaded code.)
- **14 orphaned part files (no the_part/get_part/include ref anywhere)** — `parts/part-hero.php, parts/part-lobby-podcasts.php, parts/part-lobby-program.php, parts/part-lobby-series.php, parts/part-lobby-upcoming.php, parts/part-lobby-videos.php, parts/uppart-lobby-program.php, parts/section-artists-page.php, parts/artist.php, parts/hero-section-new.php, parts/headers/header-1-bak.php` (grep of every the_part('...')/get_part('...') and dynamic template arg across root+page-templates+parts+includes returns 0 references for each (e.g. 'part-hero -> 0 refs', 'part-lobby-* -> 0 refs', 'section-artists-page -> 0 refs').)
- **Found-commemoration.php (0 bytes, no Template Name) — typo sibling of Fund-commemoration.php** — `page-templates/Found-commemoration.php` (wc -l = 0; no Template Name header; name is 'Found-' vs the real 'Fund-commemoration.php' (260 lines).)
- **lobby.php.txt (0 bytes, .txt extension — not a PHP template)** — `page-templates/lobby.php.txt` (0 bytes; .txt extension means WP never loads it.)
- **contents.php.php (double extension, Template Name 'Contents')** — `page-templates/contents.php.php` (Filename has accidental .php.php; appears to be a copy artifact. WP WILL still parse the header — verify no live page is assigned 'Contents' before deleting.)
- **Editor backup files in includes/ and page-templates/** — `includes/class-ipo-importer.bkp, includes/ipo-importer.bkp, includes/create-program.php-, includes/backup.txt, page-templates/newseries.bkp` (Non-standard extensions (.bkp, .php-, .txt) are never autoloaded; newseries.bkp is a 106-line older copy of newseries.php (370 lines).)
- **Empty includes that are required but contribute nothing** — `includes/program-farthest-date.php (0 bytes), includes/ipo-create-events-custom-functions.php (10 bytes)` (ls shows 0 / 10 bytes; if required by functions.php they are no-ops — verify and drop the require.)
- **Two empty parts that ARE invoked (silent no-op render)** — `parts/live-streaming-categories.php (0 bytes; called live_streaming_page_v4.php:70), parts/page-banner.php (0 bytes; called template-urgency-fund.php:48)` (0-byte files; both appear as the_part targets in active templates — they render nothing, indicating an unfinished/removed feature.)
- **template-test.php — developer scratch template** — `page-templates/template-test.php` (Template Name 'TEST Template'; body just lists all 'program' posts with comments 'Loop through each program, print their title' — a debugging scratch page, not a real route.)
- **Old/new variant losers (pending route verification)** — `page-templates/live_streaming_page{,_new,_v3}.php, Lobby-New-season-v{2,3} or Lobby-new-season, orckestrateam(-new), orckestra_guest_new1.php, template-musicians.php, history-old.php` (Each has a newer sibling with overlapping purpose and a distinct Template Name; only one per family can be the live route. Cannot confirm from code — DB/admin assignment needed.)

### Recommendations

- VERIFY AGAINST LIVE ROUTES FIRST for every version-family before deleting: pull the WP DB _wp_page_template meta (or check Pages list 'Template' column) to learn which Template Name is actually assigned. The code cannot reveal this. Treat the archive list below as candidates, not confirmed-dead.
- Tier-1 (delete now, highest confidence — no route check needed): the entire parts_OLD/ tree; all editor-backup files (page-templates/newseries.bkp, includes/class-ipo-importer.bkp, includes/ipo-importer.bkp, includes/create-program.php-, includes/backup.txt); zero-byte non-templates (page-templates/lobby.php.txt, page-templates/Found-commemoration.php); parts/headers/header-1-bak.php; parts/hero-section-new.php (empty + orphaned).
- Tier-2 (delete after a quick 0-ref + not-assigned confirmation): the 13 remaining orphaned parts (part-lobby-* set, uppart-lobby-program, section-artists-page, part-hero, parts/artist.php); page-templates/template-test.php (scratch); page-templates/contents.php.php (double-extension).
- Tier-3 (collapse version families to the assigned survivor): live_streaming_page x5 -> 1; Lobby new-season x3 -> 1; orckestraguest x4 -> 1; orckestrateam x2 -> 1; Musicians-new vs template-musicians; history vs history-old.
- Do NOT remove the 3 functions.php-included parts that look orphaned to the_part: parts/ipo-marketing-body.php, parts/ipo-third-party-head.php, parts/ipo-third-party-footer.php (loaded by direct include at functions.php:1572/1581/1589).
- Extract inline CSS/JS to enqueued asset files, starting with the biggest: new-theme-artist-plan.php (1070 CSS lines), Lobby-New-season-v2.php (569 + inline owl init), Lobby-New-season-v3.php, Philharmonic-FEST.php, single-artist_plan.php, single-program.php. Sequence this with the pending owl->Splide work so duplicated .owl-* rules are consolidated once.
- Extract the 18 copy-pasted owl 'video-slider' blocks into a single parts/section-video-slider.php taking an args array; do it as part of the video-slider owl->Splide migration so the conversion is done once across all 7 templates.
- Resolve the two empty-but-invoked parts (live-streaming-categories.php, page-banner.php): either implement or remove both file and the_part call so template-urgency-fund and the live-streaming page don't silently drop a section.
- Move page-templates/new-theme-artist-plan.php out of page-templates/ (it has no Template Name and is an include-target of single-artist_plan.php) into parts/ or includes/ to stop it being mistaken for an assignable template.
- Wire the static-demo moreConcerts sliders (page-templates/artist.php, template-concert-screen.php) to real event data if those routes are live; otherwise archive them. The hardcoded href="#" / 26.02.22 / fixed images prove they are unfinished scaffolding.

---

## PHP includes / functions — SECURITY + correctness (functions.php child, includes/*, parts/loop-program.php)

The PHP layer carries several real security defects. The most serious are: (1) an SSRF + arbitrary-file-fetch importer (ipo_importer::__construct fetches user-supplied $_POST['raw_url']/'file_url' via cURL/file_get_contents with SSL verification disabled) driven by the ajax_import_batch handler, which — like all five wpstack_ajax-based handlers — appears to carry NO nonce or capability check; (2) a SQL injection in wp_insert_attachment_from_url.php (get_attachment_id_by_title interpolates a title straight into a $wpdb query with no prepare); (3) two unauthenticated (wp_ajax_nopriv) handlers in functions.php — handle_fetch_presentation_data (an external-API proxy taking $_POST['apiEventId']) and the dead example-ajax.php (open redirect via HTTP_REFERER); (4) single-program.php uses raw $_GET['p'] as a post id and reassigns the global $post inside the moreConcerts loop with no wp_reset_postdata, corrupting later template state. Pervasive secondary issue: dozens of get_field()/ACF and user values are echoed into HTML/href/src without esc_html/esc_url/esc_attr across single-program.php, single-artist.php, parts/loop-program.php, class-ipo-event.php and class-ipo-program.php (stored-XSS surface, admin-write gated). Correctness bugs include the misplaced <?php endif;?> in single-artist.php that unbalances the moreConcerts markup, undefined $page_id/$donate_html ordering and $events_html in two handlers, and THREE separate files all declaring `class ipo_importer` (fatal if more than one is loaded). Large amounts of dead code exist (commented requires, *.bkp, backup.txt, create-program.php-, 0-byte files, example-ajax.php, the two unloaded importer variants). A confirmed live JS duplicate remains: soundplay()/pauseVid() defined both inline in functions.php and in assets/scripts/ipo-custom.js. NOTE: the wpstack_ajax base class and wpstack_post_processor live in the parent theme, which is NOT present in this archive — the 'no nonce/cap on AJAX' findings are strong inferences from the child handlers and MUST be verified against the parent base class before remediation.

**Metrics**

- Files reviewed: functions.php (~1600 lines, sampled), 5 AJAX handlers, class-ipo-{calendar,event,program}.php, 3 ipo_importer variants, wp_insert_attachment_from_url.php, custom-functions.php, event-permalink.php, ipo-bidirectional.php, serie-category-programs.php, taxonomy-radio-buttons.php, ipo-shortcodes.php, single-program.php, single-artist.php, parts/loop-program.php, plus dead-code files
- wp_ajax handlers found: 7 total (5 via wpstack_ajax base, 2 native in functions.php); wp_ajax_nopriv (unauthenticated): 2 live (fetch_presentation_data) + 1 dead (example-ajax)
- Explicit nonce checks in area: 1 (add_event_from_api). Explicit capability checks: 1 (display_event_table_page page render only)
- SQL injection sites: 1 (get_attachment_id_by_title)
- SSRF / arbitrary-URL fetch sites: 4 (ipo_importer cURL, ipo_importer file_get_contents, wp_insert_attachment_from_url WP_Http, handle_fetch_presentation_data proxy)
- Duplicate `class ipo_importer` declarations: 3 files
- Dead / unloaded include files: ~12
- Unescaped ACF/user output sites: 25+ across templates and classes

### Findings

- **[critical] SSRF + arbitrary remote fetch in importer, SSL verification disabled, likely no auth** — `includes/class-ipo-importer.php:28-69 (driven by includes/ajax/ajax_import_batch/ajax_import_batch.php:11-16,90)`
  - _category:_ security
  - ipo_importer::__construct reads $_POST['raw_url'] and $_POST['file_url'] (only sanitize_text_field) and fetches them: cURL with CURLOPT_SSL_VERIFYPEER=>false (lines 51-56) or file_get_contents($this->file_url) (line 65). ajax_import_batch passes data['url'] into new ipo_importer(['url'=>$url]) and runs the full import. The handler registers via `new ajax_import_batch('ajax_import_batch','post')` and performs no check_ajax_referer / current_user_can. Any caller who can reach admin-ajax.php with action=ajax_import_batch can make the server fetch arbitrary internal/external URLs (cloud metadata 169.254.169.254, internal services, file:// via file_get_contents) and import the response as posts/attachments. SSL verify off also enables MITM.
  - **Fix:** Add check_ajax_referer + current_user_can('manage_options') (or equivalent) in the wpstack_ajax base class or each handler; validate raw_url/file_url against an allowlist of expected hosts (e.g. ipo.* domains); use wp_safe_remote_get (blocks internal hosts via http_request_host_is_external) instead of raw cURL/file_get_contents; remove CURLOPT_SSL_VERIFYPEER=>false; reject file:// schemes. FIRST verify whether the parent wpstack_ajax base class already gates these — it is not in this archive.
- **[critical] SQL injection in get_attachment_id_by_title (no $wpdb->prepare)** — `includes/wp_insert_attachment_from_url.php:11-25`
  - _category:_ security
  - $attachments = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_title = '$title' AND post_type = 'attachment' " ); $title is interpolated directly with no escaping/prepare. It is called from wp_insert_attachment_from_url() (lines 67-71) with a value derived from basename()/sanitize_file_name() of a fetched URL. Because the importer fetches attacker-influenceable URLs, the filename (and thus $title) can carry crafted content; sanitize_file_name reduces but does not eliminate SQL-significant characters in all locales/configs. Even absent the importer path, this is a textbook unparameterised query.
  - **Fix:** Use $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'attachment' LIMIT 1", $title ) (and select ID only, not *). Consider replacing the title-based lookup with a proper attachment-by-source-url meta lookup.
- **[high] Unauthenticated wp_ajax_nopriv API proxy (fetch_presentation_data) with upstream URL injection** — `functions.php:256-276`
  - _category:_ security
  - handle_fetch_presentation_data is registered for both wp_ajax_ and wp_ajax_nopriv_ (line 257) with no nonce and no capability check. It builds wp_remote_get("https://ipo.pres.global/api/presentations/{$apiEventId}") from $_POST['apiEventId']; sanitize_text_field does NOT URL-encode, so values like '../../' or '?x=' allow path/query manipulation of the upstream request (limited SSRF / API relay) and lets anonymous users use the site as a free proxy. Also line 272 references undefined $body in the else branch (notice + json_decode(null)).
  - **Fix:** Drop the nopriv registration (or add it only if truly needed for the front-end), add check_ajax_referer and a capability check, cast apiEventId with absint() and rawurlencode it into the path, and fix the else branch to wp_send_json_error('Missing event ID').
- **[high] All five wpstack_ajax handlers lack nonce/capability checks (incl. importer & post processor)** — `includes/ajax/ajax_import_batch/ajax_import_batch.php:90; includes/ajax/ajax_process_posts/ajax_process_posts.php:42; includes/ajax/ajax_get_events/ajax_get_events.php:89; includes/ajax/ajax_get_calendar_events/...:117; includes/ajax/ajax_get_month/...:34`
  - _category:_ security
  - Every handler extends wpstack_ajax and is instantiated with just an action name and 'get'/'post'. None of the five call check_ajax_referer or current_user_can. ajax_import_batch (creates posts/artists, fetches URLs) and ajax_process_posts (writes ACF field_633b132f99155 across all events via update_field) are state-changing and would be CSRF-able / abusable if the base class registers nopriv or omits nonce verification. ajax_process_posts also passes raw $_POST-derived offset/amount into get_posts without intval.
  - **Fix:** Inspect the parent wpstack_ajax base class: if it does not enforce nonce + capability, add per-handler check_ajax_referer + current_user_can('edit_posts'/'manage_options') for the write handlers, and intval() the offset/amount in ajax_process_posts. Read-only calendar handlers can keep nopriv but still want a nonce.
- **[high] Global $post reassigned in moreConcerts loop without wp_reset_postdata** — `single-program.php:394-441 (esp. 397) and 472`
  - _category:_ correctness
  - Inside `foreach ($ids as $rel)` the code does `$post = get_post($related_id);` (line 397), overwriting the global $post for the rest of the template. There is no wp_reset_postdata()/wp_reset_query() after the loop. After the loop, line 472 checks `is_object($event)` (a leftover from the earlier events loop) and renders closest-event-floater, and get_footer() runs with a clobbered global $post — any later the_title()/get_the_ID() in footer/widgets reflects the last related program, not the current program.
  - **Fix:** Use a local variable (e.g. $related_post = get_post($related_id)) instead of $post, or call wp_reset_postdata() immediately after the foreach.
- **[high] Misplaced <?php endif;?> unbalances moreConcerts markup in single-artist** — `single-artist.php:381-409 (endif at 400)`
  - _category:_ correctness
  - The block opens `<?php if ($count > 0 && $items_html): ?>` at line 381 and emits <section class="moreConcerts">...<div class="splide__track"><ul>. The matching `<?php endif; ?>` is placed at line 400, INSIDE .splide__track and BEFORE the closing </ul></div> (399 is inside), the </div> at 404, </section> at 409. Result: when $count>0 the </div> (404) and </section> (409) close tags fall outside the conditional but their openings are inside it — and the slider track close is on the wrong side of endif. When $count==0 the whole top section is skipped yet the trailing </div></section> at 401-409 still render as orphan closing tags. Either branch produces invalid/unbalanced HTML.
  - **Fix:** Move the endif to after the full section closes (after </section> at line 409) so the entire <section class="moreConcerts"> ... </section> is inside one if/endif; re-indent and re-balance the splide track/list divs.
- **[medium] Arbitrary file write via importer attachment fetch (basename-derived filename)** — `includes/wp_insert_attachment_from_url.php:47-56`
  - _category:_ security
  - wp_insert_attachment_from_url() calls wp_upload_bits( basename($url), null, $response['body'] ) using the remote URL's basename as the filename and the raw response body as content, with no MIME/extension allowlist beyond wp_check_filetype. Combined with the unauthenticated/under-gated importer, a controlled source can write attacker-chosen bytes under a chosen filename into wp-content/uploads. WordPress normally blocks dangerous extensions, but reliance on that plus SSL-off fetching is fragile.
  - **Fix:** Validate the fetched Content-Type against an image allowlist, force a safe extension, and sanitize the filename; gate the importer behind capability checks (see above).
- **[medium] Unsanitized $_GET['p'] used as post id / IDOR in single-program template** — `single-program.php:12-16`
  - _category:_ security
  - $post_id = $_GET['p'] is taken raw and then used in get_related_event_ids($post_id), get_field(...,$post_id), wp_get_post_terms($post_id,...), wp_get_attachment_image_url, etc. No absint/validation and no post-type or status check, so any program (including drafts/other types) can be rendered by id and the value flows into multiple queries. Also $_GET['p'] collides with WP's reserved 'p' query var.
  - **Fix:** Use $post_id = isset($_GET['p']) ? absint($_GET['p']) : get_the_ID(); and verify get_post_type($post_id)==='program' before rendering. Prefer a non-reserved query var name.
- **[medium] Fatal: three files declare `class ipo_importer`** — `includes/class-ipo-importer.php:7; includes/class-ipo-importer-artist_plan.php:7; includes/class-ipo-importer-series.php:7`
  - _category:_ correctness
  - All three define `class ipo_importer` with incompatible constructors (__construct($data=false) vs __construct($url,$amount,$offset)). Only class-ipo-importer.php is loaded today (transitively via ajax_import_batch using `new ipo_importer`); the artist_plan/series variants and their loaders (ipo-importer-artist_plan.php / ipo-importer-series.php) are not required anywhere. If any second variant is ever included, PHP fatals with 'Cannot redeclare class', and the artist_plan/series loaders silently use the wrong constructor signature.
  - **Fix:** Rename the variant classes (e.g. ipo_artist_plan_importer, ipo_series_importer) or namespace them; delete the unused variants if the new functions.php API import flow has superseded them.
- **[medium] Undefined variables: $events_html (calendar handler) and $events_html in get_events handler edge** — `includes/ajax/ajax_get_calendar_events/ajax_get_calendar_events.php:99`
  - _category:_ correctness
  - $response['data']['content'] = $events_html; — $events_html is never assigned anywhere in this handler (only $days and $list_events_html are produced). It emits an undefined-variable notice and sends an empty 'content'. Also $layout is computed (lines 33-40) but never used.
  - **Fix:** Remove the dead $response['data']['content'] = $events_html line (or assign the intended value), and drop the unused $layout block.
- **[medium] single-artist.php: undefined $page_id and use-before-define of $donate_href; brace-less if** — `single-artist.php:51-56, 87-119`
  - _category:_ correctness
  - Lines 51-56 call get_field('main_text',$page_id) and get_field('conected_artiest',$page_id) but $page_id is never defined in this template (always null → falls back to current post, masking intent). Line 87 `if(!$donate_href)` evaluates $donate_href before it is assigned at line 119 (undefined-variable notice; condition is effectively always true). The if has no braces, so only line 88 ($language_prefix='') is conditional — the pll block at 89-102 always runs regardless. The redirect block at 58-64 also issues wp_redirect after get_header() has already sent output (headers-already-sent risk).
  - **Fix:** Remove the bogus $page_id usages (use get_the_ID()), delete the stray `if(!$donate_href)`, and move the conected_artiest redirect to a template_redirect hook before any output.
- **[medium] Unescaped ACF/user output into HTML, href and src (stored-XSS surface)** — `class-ipo-event.php:229,296,312,319; class-ipo-program.php:122; parts/loop-program.php:139,148,158,161,169,269; single-program.php:204,251,267,296,376,453-458; single-artist.php:119,203,212,218,261,285`
  - _category:_ security
  - Many fields are echoed without esc_*: e.g. class-ipo-event.php builds `<a href="'.$url.'">` from get_field('ticket_purchase') (line 312/319) and echoes get_field('button_to_purchasetxt')/sold_out (296,229) unescaped; parts/loop-program.php echoes $permalink, $title, $subtitle, $program_label, more_atist_text into markup; single-program.php echoes $audio_track into href (204), program_description fields, get_field('title_programs') into <h2> (376), and $artist_img into <img src> (296); single-artist.php echoes $donate_href into href (266) and main_text/read_more/fields/donation_bio. These are admin-managed ACF values, so exploitation requires editor-level write, but several feed href/src/style attributes where even trusted-but-malformed data breaks markup, and any lower-privileged contributor with ACF access becomes an XSS vector.
  - **Fix:** Wrap text in esc_html(), URLs in esc_url(), attribute values in esc_attr(); for rich-text fields use wp_kses_post(). Prioritise the href/src sinks (ticket_purchase, audio_track, artist_img, donate_href).
- **[low] taxonomy-radio-buttons.php declares a class inside a function** — `includes/taxonomy-radio-buttons.php:10-23`
  - _category:_ correctness
  - Category_Radio_Checklist is declared inside term_radio_checklist() guarded by class_exists. Works, but is fragile (the class only exists after first filter call) and unusual; if another plugin references it earlier it won't be found.
  - **Fix:** Move the class declaration to file scope (top level) guarded by class_exists, and only assign the walker inside the function.
- **[low] connect_to_translation reads ->trid on possibly-null language details** — `includes/class-ipo-importer.php:423-438`
  - _category:_ correctness
  - $original_post_language_info->trid and ->language_code are dereferenced without checking that apply_filters('wpml_element_language_details',...) returned an object. For a freshly created post not yet language-registered this is null → fatal/notice. The callers wrap it in try/catch but a PHP notice/error on null property access is not an Exception in older PHP.
  - **Fix:** Guard: if (is_object($original_post_language_info)) { ... } else add_msg and return.
- **[low] Reserved global $post used as foreach/iterator variable in shortcode and templates** — `includes/ipo-shortcodes.php:199 (foreach($posts as $post)); single-program.php:397`
  - _category:_ correctness
  - shortcode_ipo_programs does `foreach($posts as $post)` over an array of IDs, shadowing the global $post within the shortcode (and $post holds an int here, not a WP_Post). Minor in a shortcode (returns a string), but combined with the single-program.php global-$post clobber it reflects a recurring pattern.
  - **Fix:** Rename loop variables to $program_id / $pid and avoid reusing $post.

### Duplicates

- **soundplay() / pauseVid() defined twice (live)**
  - `functions.php:344,354 (window.soundplay/window.pauseVid, printed in wp_footer at priority 25 via ipo_hero_video_lottie_controls)`
  - `assets/scripts/ipo-custom.js:257,284 (global function soundplay()/pauseVid(), enqueued via add_script('ipo-custom'))`
  - **Fix:** Keep one definition. The ipo-custom.js versions are the consolidated source; remove the inline window.soundplay/pauseVid from ipo_hero_video_lottie_controls (keep only the Lottie 'reveal' fallback there) so the two implementations stop fighting over which loads last.
- **handleATPopupEvent JS handler — old plain name still shipped alongside the reconciled _ipo version**
  - `includes/ipo-shortcodes.php:243 (function handleATPopupEvent, and pushed as OnEventCallback at line 238)`
  - `parts/ipo-marketing-body.php:187 (function handleATPopupEvent_ipo — the reconciled version)`
  - **Fix:** The [subscribe_contact] shortcode still emits the old handleATPopupEvent and registers it as the AT popup callback. If both render, decide on one popup integration. Either rename the shortcode's callback to handleATPopupEvent_ipo (to match the consolidated marketing body) or retire the shortcode's inline popup in favour of the part.
- **class ipo_importer declared in three files**
  - `includes/class-ipo-importer.php:7`
  - `includes/class-ipo-importer-artist_plan.php:7`
  - `includes/class-ipo-importer-series.php:7`
  - **Fix:** Rename two of the three to unique class names or delete the unused variants (see findings).
- **AT popup markup/CSS duplicated between shortcode and marketing body part**
  - `includes/ipo-shortcodes.php:211-316 (at_popup_shortcode)`
  - `parts/ipo-marketing-body.php (full _atPopupSU markup + styles)`
  - **Fix:** Single-source the AT popup markup (one part included by both the shortcode and the marketing body) to avoid divergence in fields/styles/callback name.
- **WPML connect-to-translation logic duplicated across importer classes**
  - `includes/class-ipo-importer.php:423-438 (connect_to_translation)`
  - `includes/class-ipo-importer-artist_plan.php:451-472 (connect_artist_plan_to_translation)`
  - `includes/class-ipo-importer-series.php (connect_serie_to_translation)`
  - **Fix:** Extract one shared helper (trait or function) for wpml_set_element_language_details wiring.
- **on-publish event field-sync logic duplicated (live native importer vs dead create-event)**
  - `functions.php:1238-1320 (add_event_from_api — current API import)`
  - `includes/create-event.php:5-138 (on_event_post_publish_func — dead)`
  - `includes/class-ipo-event.php:37-172 (ipo_event::create — alternate path)`
  - **Fix:** Pick the canonical creation path (the AJAX add_event_from_api appears current) and delete the others to remove three competing field-mapping implementations.

### Dead / unused

- **Commented-out require_once lines for several includes** — `functions.php:22,25,26,27,35` (events-api-class.php, create-event.php, program-create-events.php, ipo-create-events-custom-functions.php, acf-event-field.php are all commented out and never loaded.)
- **example-ajax.php (insecure template AJAX)** — `includes/example-ajax.php (whole file)` (Registers wp_ajax_nopriv_news_ajax_action and redirects via unvalidated $_SERVER['HTTP_REFERER'] (open redirect / header injection); references undefined $body in else branch; not required anywhere in functions.php. Pure dead template — delete (do not enable as-is).)
- **Unused importer variants and their loaders** — `includes/class-ipo-importer-artist_plan.php, includes/class-ipo-importer-series.php, includes/ipo-importer-artist_plan.php, includes/ipo-importer-series.php` (All declare/use class ipo_importer or wpstack_post_processor('import') but none are required by functions.php; they also point at ipoold.local. Dead and conflicting.)
- **Backup / placeholder files** — `includes/ipo-importer.bkp, includes/class-ipo-importer.bkp, includes/backup.txt, includes/create-program.php-, includes/program-farthest-date.php (0 bytes), includes/ipo-create-events-custom-functions.php (10 bytes), parts/hero-section-new.php (0 bytes), parts/live-streaming-categories.php (0 bytes), parts/page-banner.php (0 bytes)` (File listing shows .bkp/.txt/trailing-dash backups and empty stubs; not loadable as PHP includes and never referenced.)
- **ipo_new_events_api class + curl helpers tied to old endpoints** — `functions.php:1323-1391` (ipo_new_events_api uses https://ipo.pres.global/api/presentations and a raw curl helper; appears superseded by ipo_get_api_presentations() (wp_remote_get + transient). Verify call sites; likely removable.)
- **Commented dead blocks inside live files** — `class-ipo-importer-artist_plan.php:96-156,398-437; class-ipo-calendar.php:351-446; class-ipo-event.php:280-282 (empty if); single-artist.php:328-355` (Large commented-out method bodies and dead branches retained inline.)
- **Unused $layout computation in calendar AJAX** — `includes/ajax/ajax_get_calendar_events/ajax_get_calendar_events.php:33-40` ($layout is derived from $_GET['layout'] but never read afterwards.)

### Recommendations

- VERIFY FIRST: the wpstack_ajax base class and wpstack_post_processor live in the PARENT theme (not in this archive). Before any AJAX-auth remediation, read the parent's class-wpstack-ajax to confirm whether it registers wp_ajax_nopriv and whether it already enforces a nonce/capability. All 'no nonce/cap' findings above hinge on this.
- Lock down the importer chain: gate ajax_import_batch and ajax_process_posts behind current_user_can('manage_options') + check_ajax_referer; replace raw cURL/file_get_contents in ipo_importer with wp_safe_remote_get; remove CURLOPT_SSL_VERIFYPEER=>false; allowlist import source hosts.
- Fix the SQL injection: rewrite get_attachment_id_by_title with $wpdb->prepare and SELECT ID only.
- Remove the wp_ajax_nopriv registration on fetch_presentation_data (or add nonce+cap), absint+rawurlencode the apiEventId, and fix the undefined $body else branch.
- Harden single-program.php: absint($_GET['p']) + post-type check; replace `$post = get_post($related_id)` with a local var (or wp_reset_postdata after the loop).
- Fix single-artist.php: relocate the misplaced endif to balance the moreConcerts section; remove undefined $page_id usages; delete the stray `if(!$donate_href)`; move the conected_artiest redirect to a pre-output hook.
- Sweep templates and the ipo_event/ipo_program classes to wrap echoed ACF values in esc_html/esc_url/esc_attr/wp_kses_post, prioritising href/src/style sinks (ticket_purchase, audio_track, artist_img, donate_href, banner_title_color).
- Resolve the triple `class ipo_importer` declaration (rename or delete variants) to eliminate the fatal-redeclare risk.
- De-duplicate the live JS: drop the inline window.soundplay/pauseVid in functions.php now that ipo-custom.js owns them; align the shortcode's handleATPopupEvent with the reconciled handleATPopupEvent_ipo.
- Delete the dead-code set (example-ajax.php, *.bkp, backup.txt, create-program.php-, 0/10-byte stubs, unused importer variants, commented requires) to shrink the audit surface; if any commented require is intended to return, security-review it before re-enabling.
- Add intval/absint to ajax_process_posts offset/amount and remove the undefined $events_html line in ajax_get_calendar_events.

---

## Asset loading / enqueue / dependencies / front-end performance (wpstack-child theme)

Every front-end page loads ~507 KB of theme-local CSS (14 stylesheets) + ~345 KB of theme-local JS (16 scripts) UNCOMPRESSED, on top of the parent theme's own assets, jQuery, a render-blocking Google Fonts request, and 5 render-blocking third-party libraries injected via wp_head/wp_footer. Three slider engines ship simultaneously: owlCarousel (bundled in plugins.js, 149 KB, still driving 6 sliders via main.js), Slick (slick.min.js 42 KB + slick.css, driving the banner sliders in ipo-custom.js), and Splide (splide.min.js 29 KB + 2 CSS, driving moreConcerts). Each engine pays full CSS+JS cost on every page even when its sliders are absent. The new parts/ipo-third-party-head.php injects AOS CSS + lottie-player + anime.js as plain render-blocking <script>/<link> in the head with no defer/async and from public CDNs (unpkg/cdnjs); the footer part adds AOS JS + init. AOS (444 data-aos attributes) and lottie-player (animated header logo) and anime.js (used by anim-reveal.js) are all genuinely used, so the issue is loading strategy, not need. The web-view AT-Popup loader is loaded twice from two code paths (always-on in parts/ipo-marketing-body.php with PopupId zfta3d2hua, and again via the at_popup_shortcode() in includes/ipo-shortcodes.php). The Google Fonts request pulls Open Sans (4 weights) + PT Sans (2 weights) but PT Sans is referenced nowhere and Open Sans only once; the real UI fonts (Simpler, Mandatory) are self-hosted. The Lato webfont family (4 folders shipped) is never referenced in any CSS/PHP/JS — pure dead weight. wow.js is enqueued but is a 0-byte empty file while the markup still uses animate_wow/data-wow-delay. spacing.css is hardcoded in header.php with Windows backslashes in the URL. No defer/async/dequeue/resource-hint management exists in the child theme. The theme has a hard runtime dependency on the parent theme wpstack-parent (not present in this checkout), which owns jQuery, the real wp_enqueue_* calls, and the add_script/add_style/the_part API.

**Metrics**

- Theme-local front-end CSS: ~507 KB uncompressed across 14 stylesheets enqueued on EVERY page (bootstrap.min 145 KB, ipo-custom 140 KB, style 88 KB, animate 72 KB, responsive 21 KB, style-sagi 21 KB lead the list)
- Theme-local front-end JS: ~345 KB uncompressed across 16 scripts on EVERY page (plugins.js 149 KB [Bootstrap+owl+magnific], slick.min 42 KB, isotope 35 KB, splide.min 29 KB, ipo-custom 27 KB)
- Combined theme-local front-end payload: ~852 KB uncompressed, every page, before parent assets / jQuery / fonts / 3rd-party
- 3 slider engines shipped together: owlCarousel (in plugins.js 149 KB + owl.carousel.min.css 3 KB), Slick (slick.min.js 42 KB + slick.css 1.7 KB), Splide (splide.min.js 29 KB + splide-core.min.css 2 KB + splide.min.css 5 KB)
- 5 render-blocking third-party resources injected via parts: AOS css (unpkg), lottie-player.js (unpkg), anime.min.js (cdnjs) in <head>; AOS js + AOS.init in footer; plus web-view AT-Popup loader (loaded twice)
- Render-blocking head fonts: 1 Google Fonts request = Open Sans 4 variants + PT Sans 2 variants (PT Sans 0 usages, Open Sans 1 usage)
- Self-hosted webfonts: Simpler family (Black/Regular/Light/Bold, .eot/.woff/.woff2/.ttf/.svg each) + Mandatory PH (MandatoryVariable.woff2 210 KB) + Mandatory-47.woff2
- Dead font weight: assets/fonts/LatoBold|LatoLight|LatoMedium|LatoRegular (~430 KB of files) referenced by zero CSS/PHP/JS
- owlCarousel init calls still live in main.js: 7 (.meet-slider, .moreConcerts-slider, .recommended-slider, .recommended-slider-child, .upcoming-slider, .video-slider, .player-slider)
- AOS usage: 444 data-aos attributes across front-end templates (justifies AOS but argues for self-host + lazy-init)
- wow.js enqueued but file is 0 bytes (empty); animate_wow / data-wow-delay markup still present in footer.php and parts

### Findings

- **[high] All 3rd-party libs in head load render-blocking with no defer/async, from public CDNs** — `parts/ipo-third-party-head.php:23-25; functions.php:1571-1576 (wp_head hook)`
  - _category:_ Render-blocking / 3rd-party
  - aos.css (unpkg), lottie-player.js (unpkg) and anime.min.js (cdnjs) are printed as plain <link>/<script> in <head> via the wp_head hook. They are parser-blocking: the browser stalls HTML parsing/first paint on three separate third-party DNS+TLS+download round trips before the page can render. None carry defer/async. AOS, lottie-player and anime.js are all actually used (444 data-aos attrs; animated header logo; anim-reveal.js calls anime.) so they cannot be removed, but loading strategy is wrong.
  - **Fix:** Move lottie-player.js and anime.js to wp_footer or add defer (they are not needed for first paint). Keep aos.css small but self-host it. Best: register all three as proper wp_enqueue handles (in_footer=true for the JS) so WP manages order, then add a script_loader_tag filter to attach defer. Add preconnect to unpkg.com/cdnjs.cloudflare.com if they must stay external.
- **[high] web-view AT-Popup library loaded twice from two independent code paths** — `parts/ipo-marketing-body.php:156-178 (PopupId zfta3d2hua, always-on every page); includes/ipo-shortcodes.php:218-240 (at_popup_shortcode, PopupId x3fafsa2u2) + ipo-shortcodes.php:215 (web-view CSS)`
  - _category:_ Duplication / 3rd-party
  - The marketing-body part unconditionally injects the web-view loader (//cdn-media.web-view.net/popups/lib/v1/loader.min.js) on every page. The at_popup_shortcode() injects the SAME loader again (plus main_combined-rtl.css) wherever the shortcode is placed. On any page using the shortcode the loader script + the entire _atpopq bootstrap runs twice with two different PopupIds. The two callbacks were renamed to avoid a JS collision (handleATPopupEvent vs handleATPopupEvent_ipo) but the library itself is still double-loaded.
  - **Fix:** Decide one owner for the AT-Popup. If the global signup popup is intended site-wide, keep only the marketing-body version and make the shortcode reuse the already-loaded loader (push to existing _atpopq) instead of re-injecting the script. Load the loader once, async, and only on pages that need it.
- **[high] Three slider engines (owl + slick + splide) all enqueued on every page** — `functions.php:78,81,82,91,101,107,109,110,112 (add_script/add_style); main.js:93,155,297,351,396,461,493 (owl inits); assets/scripts/ipo-custom.js:30,38,235,243 (slick inits); assets/scripts/sliders-splide.js:33 (Splide)`
  - _category:_ Slider engines
  - owlCarousel ships inside plugins.js (149 KB) + owl.carousel.min.css and still drives 6 sliders (meet/recommended/recommended-child/upcoming/video/player) from main.js. Slick (slick.min.js 42 KB + slick.css) drives the .slider-banner sliders in ipo-custom.js. Splide (splide.min.js 29 KB + splide-core.min.css + splide.min.css) drives moreConcerts. All three engines + their CSS load on EVERY page regardless of whether any slider exists, ~80 KB+ JS and ~12 KB CSS of slider code alone, much of it redundant. The moreConcerts owl init in main.js:155 targets .moreConcerts-slider while the new Splide targets .moreConcerts-splide, so the old owl moreConcerts code is now effectively dead but still present.
  - **Fix:** Per the stated plan: migrate the 6 remaining owl sliders to Splide, then remove owlCarousel (split it out of plugins.js or replace plugins.js) and owl.carousel.min.css; consolidate onto Splide as the single engine and retire slick.min.js + slick.css too once the banner sliders move. Conditionally enqueue the slider engine only on templates that render sliders. Immediately, delete the dead .moreConcerts-slider owl init in main.js:155.
- **[high] Calendar + slider + heavy scripts enqueued globally instead of where used** — `functions.php:70-115 (base add_script/add_style block; comment lines 84-85 explicitly load calendar everywhere)`
  - _category:_ Conditional loading
  - calendar-behavior.js + calendar-filter.js are loaded on every page (comment: 'load everywhere so .calendar_area works on any page'); isotope.pkgd.min.js (35 KB, masonry filtering), hc-sticky.js (12 KB), slick/splide/owl, animate.css (72 KB), magnific-popup, lity are all global. Only 'loader' is conditionally gated (front page / calendar templates). The result is the full ~852 KB theme-local payload on simple pages (e.g. a text page) that use none of these features.
  - **Fix:** Introduce conditional enqueue: calendar scripts only where a .calendar_area / calendar template is rendered; isotope only on filtered grids (artists/lobby); hc-sticky only where sticky columns exist; animate.css only if WOW/AOS reveal is used; slider engine + CSS only on slider templates. Use is_page_template/is_singular/has_block/has_shortcode or a per-section enqueue-on-render pattern. This is the single biggest first-paint and transfer win for content pages.
- **[medium] Core animation libs served from public CDNs (unpkg/cdnjs) instead of self-hosted** — `parts/ipo-third-party-head.php:23-25; parts/ipo-third-party-footer.php:18`
  - _category:_ Hosting / reliability
  - AOS 2.3.1, lottie-player 1.0.0, anime 2.0.2 load from unpkg.com and cdnjs.cloudflare.com. These are unversioned-cache, third-party single points of failure (outage = broken animations / blocked render), add extra DNS/TLS handshakes, prevent HTTP/2 multiplexing with first-party assets, and leak referrer. The theme already self-hosts everything else.
  - **Fix:** Download AOS, lottie-player, anime.js into assets/scripts + assets/styles, enqueue them as local handles with version strings (cache-busting via filemtime). Removes 3-4 external connections from the critical path and the third-party SPOF.
- **[medium] Lato webfont family shipped (~430 KB) but referenced nowhere** — `assets/fonts/LatoBold/, assets/fonts/LatoLight/, assets/fonts/LatoMedium/, assets/fonts/LatoRegular/`
  - _category:_ Dead weight / fonts
  - Four Lato weight folders (.eot/.ttf/.woff/.woff2) are committed but grep finds zero 'Lato' references in any .css/.php/.js (excluding to-merge/parts_OLD), and there is no @font-face declaring Lato. They are never downloaded by browsers (no CSS triggers them) but bloat the repo/deploy and confuse maintenance.
  - **Fix:** Delete the four Lato folders. If a future design needs Lato, add it deliberately via @font-face with font-display:swap and a subset woff2 only.
- **[medium] Render-blocking Google Fonts request loads PT Sans (0 uses) and Open Sans (1 use)** — `header.php:18`
  - _category:_ Wasted font request
  - Head requests Open Sans (4 variants incl italic) + PT Sans (2 weights) from fonts.googleapis.com. PT Sans is referenced nowhere in the theme; Open Sans appears once (AT-popup styling in ipo-custom.css line 927). This is a render-blocking external stylesheet (plus the font files it pulls) almost entirely for unused families; the real UI fonts are the self-hosted Simpler/Mandatory.
  - **Fix:** Drop PT Sans entirely. If Open Sans is truly needed for the popup, self-host a single subset weight or fold it into the existing webfont set; otherwise remove the Google Fonts <link> and its two preconnects, eliminating one render-blocking request + DNS/TLS to googleapis/gstatic.
- **[medium] Bootstrap loaded as both standalone CSS and bundled JS, plus possible double CSS** — `functions.php:100 (bootstrap.min.css 145 KB); assets/scripts/plugins.js:11 (Bootstrap v4.6.0 bundled JS + owl + magnific)`
  - _category:_ Duplication
  - bootstrap.min.css (145 KB, the single largest stylesheet) loads on every page. plugins.js bundles Bootstrap 4.6 JS together with owlCarousel and Magnific Popup, so you cannot drop owl without surgically extracting it from plugins.js, and the whole 149 KB bundle loads even on pages using none of Bootstrap's JS components. Combined with the 140 KB ipo-custom.css and 88 KB style.css, three files alone are ~373 KB CSS.
  - **Fix:** Audit which Bootstrap utilities/components are actually used; replace the full bootstrap.min.css with a purged/subset build (PurgeCSS against the templates) - likely cuts 100 KB+. Unbundle plugins.js into separate, individually-enqueueable handles (bootstrap-js / owl / magnific) so each can be conditionally loaded or removed independently during the owl migration.
- **[medium] wow.js enqueued but is a 0-byte empty file while markup still uses WOW classes** — `functions.php:72 (add_script('wow')); assets/scripts/wow.js (0 bytes); footer.php:85,93,109 + many parts use class 'animate_wow' / data-wow-delay`
  - _category:_ Broken / missing asset
  - $theme->add_script('wow') registers an empty 0-byte script. The footer and numerous parts still emit animate_wow and data-wow-delay attributes, implying WOW.js reveal animations are expected but the library is absent, so those animations are silently broken (or silently superseded by AOS/anim-reveal). This is both a broken feature and a wasted enqueue/HTTP request for an empty file.
  - **Fix:** Decide the reveal strategy. If AOS/anim-reveal is the system of record, remove the wow enqueue and migrate animate_wow/data-wow-delay usages to data-aos (or strip them); also drop animate.css (72 KB) if it was only there for WOW. If WOW is still wanted, restore the real wow.js. Either way, stop enqueuing the empty file.
- **[medium] spacing.css hardcoded in header with Windows backslashes and a misleading id, bypassing enqueue** — `header.php:19`
  - _category:_ Bug / portability
  - <link ... id='classic-theme-styles-css' href='/wp-content/themes\wpstack-child\assets\styles\spacing.css' ...>. The href uses backslashes (themes\wpstack-child\assets\styles\spacing.css) which are not valid URL path separators on Linux/most servers, so this stylesheet may 404 in production; the path is also hardcoded to the theme slug (breaks if renamed) and the id masquerades as WordPress core's classic-theme-styles handle. spacing.css is not registered via add_style, so it bypasses versioning/cache-busting and dependency ordering.
  - **Fix:** Remove the manual <link> and register spacing.css through $theme->add_style('spacing') (or wp_enqueue_style with get_stylesheet_directory_uri()), using forward slashes and filemtime() versioning. Fixes the likely 404, the rename fragility, and the fake core id collision.
- **[low] Developer-named stylesheets shipped to all front-end visitors** — `functions.php:106 (style-sagi.css 21 KB), 108 (style-shojib.css 7 KB); style-admin.css/scripts-admin.js flagged admin-only`
  - _category:_ Per-developer fragmentation
  - style-sagi.css (21 KB) and style-shojib.css (7 KB) are per-developer override sheets enqueued for every visitor, adding ~28 KB of fragmented, hard-to-maintain CSS that competes in the cascade with style.css, responsive.css and ipo-custom.css. style-admin/scripts-admin are correctly gated with admin=>true.
  - **Fix:** Fold style-sagi.css and style-shojib.css into the consolidated front-end stylesheets (or delete superseded rules) as part of the planned stylesheet consolidation; eliminate the per-developer sheets so there is one front-end CSS owner.
- **[low] External libs pinned by URL but local enqueues rely on parent versioning; no filemtime cache-busting visible in child** — `parts/ipo-third-party-head.php:23-25; functions.php:70-115`
  - _category:_ Caching / cache-busting
  - Third-party libs are version-pinned in their URLs (good for cache, bad if you self-host without your own versioning). The child's add_script/add_style calls delegate versioning to the parent; if the parent does not append filemtime/version, updated local assets (e.g. the freshly edited ipo-custom.js/.css) can be served stale from browser/CDN cache. ipo-custom.css/js are large and change often, so stale-cache risk is real.
  - **Fix:** When self-hosting the 3rd-party libs and when consolidating stylesheets, ensure every enqueue passes a version = filemtime(file) so edits bust caches deterministically. Verify the parent's add_style/add_script already does this; if not, version the volatile handles (ipo-custom, sliders-splide, moreconcerts) explicitly.
- **[low] Multiple inline <script> blocks injected late in footer add work and a forced 1200ms timeout** — `functions.php:304-328 (lottie placeholders, prio 1), 335-386 (hero/lottie controls + setTimeout 1200ms, prio 25), 1495-1534 (privacy banner inline JS+CSS), parts/ipo-marketing-body.php:54-200 (geo-banner + AT-popup inline)`
  - _category:_ Footer inline scripts
  - wp_footer accumulates several inline scripts: lottie safe-placeholders, hero video controls with a hard 1200ms setTimeout reveal fallback, a cookie privacy banner, the geo-IL banner (3 sequential IP-API fetches with 2.5s timeouts each), and the AT-popup. These are unminified, run on every page, and the 1200ms timeout + geo fetch chain can delay perceived interactivity/animation start. They also duplicate concerns (the reveal fallback exists because the parent's reveal script runs before jQuery and throws).
  - **Fix:** Consolidate footer inline JS into the existing ipo-custom.js (one cached, minifiable file) rather than many inline blocks. Fix the root cause of the parent reveal script throwing so the 1200ms timeout fallback can be removed. Gate the geo-banner fetch behind requestIdleCallback so it never competes with first paint.
- **[info] Hard runtime dependency on parent theme wpstack-parent (owns jQuery + real enqueue + part API)** — `functions.php:10 (require_once get_template_directory().'/functions.php'); style.css:6 (Template: wpstack-parent); includes/ajax/*.php (new wpstack_theme(), extends wpstack_ajax)`
  - _category:_ Parent dependency
  - The entire asset pipeline (add_script/add_style/add_parent_script/add_parent_style/the_part/get_part/get_the_image) is defined by the parent wpstack_theme class, which is NOT present in this checkout. The parent therefore owns: jQuery enqueue (every child script assumes jQuery/$), the actual wp_enqueue_script/style calls, and head-vs-footer placement of all handles registered via add_script. The child even notes (functions.php:68-69) it must register at load time, not on wp_enqueue_scripts, because deferring broke Slick/isScrolledIntoView - a sign the parent's enqueue timing is fragile. fancybox is pulled via add_parent_script/add_parent_style (parent-owned).
  - **Fix:** Document the parent dependency and audit the parent's enqueue() to learn whether it: places scripts in head or footer, attaches versions, and whether jQuery is the bundled vs CDN copy. Any defer/async or conditional-enqueue strategy must be coordinated with the parent (e.g. a child script_loader_tag filter on parent-registered handles). Confirm the parent ships in production deploys; a missing parent fatals the site.

### Duplicates

- **web-view AT-Popup loader library (//cdn-media.web-view.net/popups/lib/v1/loader.min.js) bootstrapped from two places**
  - `parts/ipo-marketing-body.php:156-178 (PopupId zfta3d2hua, unconditional every page)`
  - `includes/ipo-shortcodes.php:218-240 (PopupId x3fafsa2u2, via at_popup_shortcode) + ipo-shortcodes.php:215 (web-view main_combined-rtl.css)`
  - **Fix:** Load the web-view loader exactly once; have the shortcode reuse the existing _atpopq queue instead of re-injecting the script + CSS.
- **Bootstrap loaded twice in spirit: full CSS standalone + JS bundled in plugins.js**
  - `functions.php:100 (bootstrap.min.css 145 KB)`
  - `assets/scripts/plugins.js:11 (Bootstrap v4.6.0 JS bundled with owl + magnific)`
  - **Fix:** Purge/subset bootstrap.min.css to used selectors; unbundle Bootstrap JS from plugins.js so it can be dropped/conditionally loaded.
- **Three slider engines providing overlapping carousel capability**
  - `plugins.js (owlCarousel) + owl.carousel.min.css`
  - `slick.min.js + slick.css`
  - `splide.min.js + splide-core.min.css + splide.min.css`
  - **Fix:** Standardize on Splide (per migration plan); remove owl and slick after porting their inits.
- **Two near-identical Mandatory variable woff2 files**
  - `assets/fonts/MandatoryVariable.woff2 (210 KB, referenced by style.css)`
  - `assets/fonts/mandatoryvariable-tester-0.3.woff2 (210 KB, a 'tester' file)`
  - **Fix:** Delete the unused -tester file; keep the single production MandatoryVariable.woff2.
- **AT-popup event callbacks defined twice (already renamed to avoid collision, but indicates the popup code itself was duplicated)**
  - `parts/ipo-marketing-body.php:187 (handleATPopupEvent_ipo)`
  - `includes/ipo-shortcodes.php:243 (handleATPopupEvent)`
  - **Fix:** Single popup owner removes the need for two identical empty switch-statement callbacks.

### Dead / unused

- **Lato webfont family (4 weight folders, ~430 KB)** — `assets/fonts/LatoBold/, LatoLight/, LatoMedium/, LatoRegular/` (grep for 'Lato' across all .css/.php/.js (excluding to-merge/parts_OLD) returns zero matches; no @font-face declares Lato. Browsers never fetch these.)
- **PT Sans portion of the render-blocking Google Fonts request** — `header.php:18 (family=PT+Sans:wght@400;700)` (grep 'PT Sans' / 'PT+Sans' across styles/php returns 0 usages.)
- **Empty wow.js enqueued (broken WOW.js dependency)** — `functions.php:72 add_script('wow'); assets/scripts/wow.js (0 bytes)` (wc -c reports 0 bytes; footer.php and parts still emit class 'animate_wow' + data-wow-delay with no library to consume them.)
- **Dead owlCarousel init for moreConcerts (superseded by Splide)** — `assets/scripts/main.js:155 ($('.moreConcerts-slider').owlCarousel(...))` (Splide migration (sliders-splide.js:24) targets the new selector .moreConcerts-splide; the old .moreConcerts-slider owl init no longer matches the rebuilt markup.)
- **mandatoryvariable-tester-0.3.woff2 (210 KB test artifact)** — `assets/fonts/mandatoryvariable-tester-0.3.woff2` (Named a 'tester'; style.css @font-face references only MandatoryVariable.woff2, not the tester file.)
- **anime.js loaded render-blocking in head though only used by a footer-scope reveal helper** — `parts/ipo-third-party-head.php:25 vs only consumer assets/scripts/anim-reveal.js:64` (anime is referenced (anime.) only in anim-reveal.js, a deferred reveal effect; placing it parser-blocking in head is unnecessary for first paint (it is used, but mis-placed).)
- **header-1-bak.php backup header part shipped in theme** — `parts/headers/header-1-bak.php (9.2 KB)` (Backup copy of the active header-1.php sitting in the deployed parts dir; not loaded but carried in the repo/deploy.)

### Recommendations

- IMPACT 1 - Conditional enqueue (biggest first-paint + transfer win for content pages): stop loading the calendar scripts, slider engine(s) + slider CSS, isotope (35 KB), hc-sticky (12 KB), animate.css (72 KB), magnific/lity globally. Gate each behind is_page_template/is_singular/has_shortcode or enqueue-on-render so a plain content page drops from ~852 KB to a small core. The base block in functions.php:70-115 is the single place to refactor.
- IMPACT 2 - Defer/relocate and self-host the 3rd-party libs: move lottie-player.js + anime.js out of the render-blocking head (to footer or add defer), self-host AOS/lottie-player/anime locally (removes unpkg+cdnjs from the critical path and the third-party SPOF), and keep aos.css inlined-or-local. Coordinate via a child script_loader_tag filter so defer can be attached to parent-registered handles too.
- IMPACT 3 - Collapse to one slider engine: finish the planned owl->Splide migration of the 6 remaining sliders, then remove owlCarousel (unbundle from plugins.js) + owl.carousel.min.css, and migrate the slick banner sliders in ipo-custom.js to Splide so slick.min.js (42 KB) + slick.css can also go. Immediately delete the dead .moreConcerts-slider owl init in main.js:155.
- IMPACT 4 - Shrink the three biggest stylesheets: PurgeCSS-subset bootstrap.min.css (145 KB -> likely <40 KB), and during the stated stylesheet consolidation fold style-sagi.css/style-shojib.css and the to-be-removed engine CSS into the consolidated sheets so the per-page CSS count drops from 14 files toward ~3-4.
- IMPACT 5 - Fix the wasted/broken font + asset loose ends: delete the Lato folders (~430 KB) and the mandatoryvariable-tester woff2 (210 KB); drop PT Sans (and Open Sans if the popup can use an existing webfont) from header.php:18, removing one render-blocking external request + 2 preconnects; remove the empty wow.js enqueue and migrate animate_wow/data-wow-delay to AOS (or restore WOW); convert spacing.css from the hardcoded backslash <link> in header.php:19 to a proper add_style enqueue with filemtime versioning.
- IMPACT 6 - De-duplicate the AT-Popup: pick one owner for the web-view popup, load loader.min.js once (async, only where needed), and have at_popup_shortcode reuse the existing _atpopq queue instead of re-injecting the loader + CSS.
- IMPACT 7 - Consolidate footer inline JS into ipo-custom.js (lottie placeholders, hero controls, privacy banner, geo banner), fix the parent reveal script so the 1200ms setTimeout fallback can be removed, and run the geo-IP fetch chain under requestIdleCallback so it never delays first paint.
- CROSS-CUTTING - Document and verify the wpstack-parent dependency: confirm the parent ships in production, learn whether it enqueues in head vs footer and whether it adds version strings, and ensure any defer/conditional strategy is implemented as child-side filters on parent-registered handles. Add explicit filemtime versioning to the volatile child handles (ipo-custom.css/js, sliders-splide, moreconcerts) to prevent stale-cache after edits.

---
