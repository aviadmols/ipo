/**
 * Event Table > תוכניות מקושרות.
 *
 * Boot data comes from ipo_related_programs_admin_page(); the whole option is
 * held here as `store`, edited in place, and saved back in one request. The
 * preview is computed by the server from the rules on screen.
 */
(function () {
	'use strict';

	var B = window.ipoRelatedPrograms;
	var root = document.getElementById('ipo-rp-app');

	if (!B || !root) {
		return;
	}

	/* ------------------------------------------------------------------ data */

	var zones = {};
	B.zones.forEach(function (zone) { zones[zone.key] = zone; });

	var programs = {};
	B.programs.forEach(function (program) { programs[program.id] = program; });

	var categories = {};
	B.categories.forEach(function (category) { categories[category.id] = category; });

	var LIST_KEYS = ['categories', 'manual_ids', 'promoted_ids', 'exclude_ids'];

	var MODES = {
		same_category: { title: 'אותה קטגוריה', hint: 'תוכניות מהקטגוריה של התוכנית שבעמוד.' },
		upcoming: { title: 'כל האירועים הקרובים', hint: 'כל תוכנית שיש לה תאריך עתידי באתר.' },
		categories: { title: 'קטגוריות נבחרות', hint: 'רק תוכניות מהקטגוריות שתסמנו.' },
		manual: { title: 'רק בחירה ידנית', hint: 'רק התוכניות שתוסיפו כאן, ולא שום דבר אוטומטי.' },
		legacy: { title: 'אוטומטי (הגדרה ישנה)', hint: 'מציג את 20 האירועים הקרובים באתר. מומלץ לעבור ל״כל האירועים הקרובים״ עם מגבלת כמות.' }
	};

	var SOURCE_LABELS = {
		promoted: 'מקודמת',
		manual: 'נוספה ידנית',
		page_pick: 'מהבחירה בעמוד',
		pool: ''
	};

	function clone(value) {
		return JSON.parse(JSON.stringify(value));
	}

	/** PHP sends an empty map as []; everything here expects objects. */
	function normalizeStore(store) {
		store = store && typeof store === 'object' ? store : {};

		Object.keys(zones).forEach(function (zoneKey) {
			var zone = store[zoneKey] = store[zoneKey] || {};

			zone.default = normalizeRule(zone.default);
			zone.respect_page_pick = zone.respect_page_pick === undefined ? zones[zoneKey].pickDefault : (zone.respect_page_pick ? 1 : 0);

			['by_category', 'by_page'].forEach(function (group) {
				if (!zone[group] || Array.isArray(zone[group])) {
					zone[group] = {};
				}

				Object.keys(zone[group]).forEach(function (key) {
					var override = normalizeRule(zone[group][key]);
					override.enabled = zone[group][key].enabled ? 1 : 0;
					override.respect_page_pick = zone[group][key].respect_page_pick ? 1 : 0;
					zone[group][key] = override;
				});
			});
		});

		return store;
	}

	function normalizeRule(rule) {
		var out = clone(B.defaults);

		rule = rule && typeof rule === 'object' ? rule : {};

		Object.keys(out).forEach(function (key) {
			if (rule[key] !== undefined) {
				out[key] = rule[key];
			}
		});

		LIST_KEYS.forEach(function (key) {
			out[key] = (Array.isArray(out[key]) ? out[key] : []).map(Number).filter(Boolean);
		});

		out.require_future_event = out.require_future_event ? 1 : 0;
		out.max_items = Math.max(0, parseInt(out.max_items, 10) || 0);

		return out;
	}

	/** Stable text of the store, for the unsaved-changes check. */
	function canonical(value) {
		if (Array.isArray(value)) {
			return '[' + value.map(canonical).join(',') + ']';
		}

		if (value && typeof value === 'object') {
			return '{' + Object.keys(value).sort().map(function (key) {
				return JSON.stringify(key) + ':' + canonical(value[key]);
			}).join(',') + '}';
		}

		return JSON.stringify(typeof value === 'boolean' ? (value ? 1 : 0) : value);
	}

	var store = normalizeStore(clone(B.store));
	var baseline = canonical(store);

	var state = {
		zone: 'home_upcoming',
		type: 'default',
		key: 0,
		lang: B.defaultLang || 'he',
		samples: {},
		navQuery: '',
		saving: false
	};

	/* --------------------------------------------------------------- places */

	function placeId(zone, type, key) {
		return zone + '/' + type + '/' + (key || 0);
	}

	function findPlace(zone, type, key) {
		if (type === 'default') {
			return { type: 'default', key: 0, title: 'ברירת מחדל', sub: 'לכל מקום שאין לו הגדרה ייעודית' };
		}

		var list = B.places[zone] || [];

		for (var i = 0; i < list.length; i++) {
			if (list[i].type === type && Number(list[i].key) === Number(key)) {
				return list[i];
			}
		}

		return null;
	}

	function groupOf(type) {
		return type === 'category' ? 'by_category' : 'by_page';
	}

	function overrideOf(zone, type, key) {
		if (type === 'default') {
			return null;
		}

		return store[zone][groupOf(type)][key] || null;
	}

	function isCustom(zone, type, key) {
		var override = overrideOf(zone, type, key);

		return !!(override && override.enabled);
	}

	/** The rule a place runs on right now, respect flag included. */
	function effectiveRule(zone, type, key) {
		if (isCustom(zone, type, key)) {
			return overrideOf(zone, type, key);
		}

		var rule = clone(store[zone].default);
		rule.respect_page_pick = store[zone].respect_page_pick;

		return rule;
	}

	/** The object edits on this place write to. */
	function editable() {
		if (state.type === 'default') {
			return store[state.zone].default;
		}

		return overrideOf(state.zone, state.type, state.key);
	}

	function respectOf() {
		return state.type === 'default'
			? store[state.zone].respect_page_pick
			: overrideOf(state.zone, state.type, state.key).respect_page_pick;
	}

	function setRespect(value) {
		if (state.type === 'default') {
			store[state.zone].respect_page_pick = value ? 1 : 0;
		} else {
			overrideOf(state.zone, state.type, state.key).respect_page_pick = value ? 1 : 0;
		}
	}

	function makeCustom() {
		var group = store[state.zone][groupOf(state.type)];
		var existing = group[state.key];

		// A rule switched off earlier comes back as it was left.
		if (existing) {
			existing.enabled = 1;
			return;
		}

		var rule = clone(store[state.zone].default);
		rule.respect_page_pick = store[state.zone].respect_page_pick;
		rule.enabled = 1;

		if (state.type === 'category') {
			rule.mode = 'categories';
			rule.categories = [Number(state.key)];
		}

		group[state.key] = rule;
	}

	function modesFor(zone, type, currentMode) {
		var modes;

		if (zone === 'single_program') {
			modes = type === 'category'
				? ['categories', 'upcoming', 'manual']
				: ['same_category', 'categories', 'upcoming', 'manual'];
		} else {
			modes = ['upcoming', 'categories', 'manual'];

			// same_category on a page finds nothing and hands over to the
			// module's own fallback; it is kept on offer only while it is in use.
			if (currentMode === 'same_category') {
				modes.push('legacy');
			}
		}

		return modes;
	}

	/* ------------------------------------------------------------- helpers */

	function esc(value) {
		return String(value === undefined || value === null ? '' : value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function programLabel(id) {
		var program = programs[id];

		return program ? program.t : '#' + id + ' (לא פורסם / לא נמצא)';
	}

	function langTag(code) {
		return code ? '<span class="ipo-rp-lang">' + esc(code) + '</span>' : '';
	}

	function dateText(program) {
		if (!program) {
			return '';
		}

		return program.d ? program.d + (program.n > 1 ? ' · ' + program.n + ' מופעים' : '') : 'אין תאריך עתידי';
	}

	function plural(n, one, many) {
		return n === 1 ? one : n + ' ' + many;
	}

	function isDirty() {
		return canonical(store) !== baseline;
	}

	function post(action, data) {
		var body = new FormData();

		body.append('action', action);
		body.append('nonce', B.nonce);

		Object.keys(data).forEach(function (key) {
			body.append(key, data[key]);
		});

		return fetch(B.ajax, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (response) { return response.json(); })
			.then(function (json) {
				if (!json || !json.success) {
					throw new Error(json && json.data && json.data.message ? json.data.message : 'השרת לא אישר את הבקשה.');
				}

				return json.data;
			});
	}

	var toastTimer;

	function toast(message, isError) {
		var el = document.querySelector('.ipo-rp-toast');

		el.textContent = message;
		el.classList.toggle('is-error', !!isError);
		el.classList.add('is-visible');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { el.classList.remove('is-visible'); }, 2600);
	}

	/* -------------------------------------------------------------- render */

	function renderShell() {
		root.innerHTML =
			'<header class="ipo-rp-head">' +
				'<div>' +
					'<span class="ipo-rp-eyebrow">Event Table · מודולי תוכניות</span>' +
					'<h2 class="ipo-rp-title">איפה מוצגות<strong>התוכניות באתר</strong></h2>' +
					'<p class="ipo-rp-lede">כל מקום באתר שמציג רצועת תוכניות מופיע ברשימה. בחרו מקום, קבעו מה יוצג בו ומה יקודם לראש הרשימה, ובדקו בתצוגה המקדימה בדיוק מה המבקרים יראו.</p>' +
				'</div>' +
				'<div class="ipo-rp-stats" data-slot="stats"></div>' +
			'</header>' +
			'<div class="ipo-rp-grid">' +
				'<nav class="ipo-rp-nav" aria-label="מקומות באתר">' +
					'<input type="search" class="ipo-rp-search" data-input="nav" placeholder="חיפוש מקום…" aria-label="חיפוש מקום">' +
					'<div data-slot="nav"></div>' +
				'</nav>' +
				'<main class="ipo-rp-editor" data-slot="editor"></main>' +
				'<aside class="ipo-rp-preview" data-slot="preview" aria-label="תצוגה מקדימה"></aside>' +
			'</div>';

		var bar = document.createElement('div');
		bar.className = 'ipo-rp-bar';
		bar.setAttribute('dir', 'rtl');
		bar.innerHTML =
			'<span class="ipo-rp-bar-text"><b>יש שינויים שלא נשמרו.</b> הם חלים על האתר רק אחרי שמירה.</span>' +
			'<span class="ipo-rp-bar-actions">' +
				'<button type="button" class="ipo-rp-btn" data-act="discard">ביטול השינויים</button>' +
				'<button type="button" class="ipo-rp-btn is-primary" data-act="save">שמירה <small>(Ctrl+S)</small></button>' +
			'</span>';
		document.body.appendChild(bar);

		var toastEl = document.createElement('div');
		toastEl.className = 'ipo-rp-toast';
		toastEl.setAttribute('role', 'status');
		document.body.appendChild(toastEl);

		bar.addEventListener('click', onClick);
	}

	function slot(name) {
		return root.querySelector('[data-slot="' + name + '"]');
	}

	function renderAll() {
		renderStats();
		renderNav();
		renderEditor();
		renderBar();
		schedulePreview(0);
	}

	function renderStats() {
		var places = 0;
		var custom = 0;
		var promoted = 0;

		Object.keys(zones).forEach(function (zoneKey) {
			places += 1 + (B.places[zoneKey] || []).length;
			promoted += store[zoneKey].default.promoted_ids.length;

			['by_category', 'by_page'].forEach(function (group) {
				Object.keys(store[zoneKey][group]).forEach(function (key) {
					if (store[zoneKey][group][key].enabled) {
						custom++;
						promoted += store[zoneKey][group][key].promoted_ids.length;
					}
				});
			});
		});

		slot('stats').innerHTML =
			'<div class="ipo-rp-stat"><b>' + places + '</b><span>מקומות</span></div>' +
			'<div class="ipo-rp-stat"><b>' + custom + '</b><span>עם הגדרה ייעודית</span></div>' +
			'<div class="ipo-rp-stat"><b>' + promoted + '</b><span>קידומים פעילים</span></div>';
	}

	function navRow(zoneKey, place) {
		var type = place.type;
		var key = place.key || 0;
		var active = state.zone === zoneKey && state.type === type && Number(state.key) === Number(key);
		var rule = effectiveRule(zoneKey, type, key);
		var flags = [];

		if (type === 'default') {
			flags.push('<span class="ipo-rp-tag is-muted">בסיס</span>');
		} else if (isCustom(zoneKey, type, key)) {
			flags.push('<span class="ipo-rp-tag is-solid">ייעודי</span>');
		}

		if (rule.promoted_ids.length && (type === 'default' || isCustom(zoneKey, type, key))) {
			flags.push('<span class="ipo-rp-tag" title="תוכניות מקודמות">★ ' + rule.promoted_ids.length + '</span>');
		}

		var sub = place.sub || '';
		var draft = false;

		if (type === 'page') {
			var langs = place.versions.map(function (version) { return version.lang; }).filter(Boolean);
			var pick = place.versions.reduce(function (sum, version) { return sum + version.pick; }, 0);

			sub = [place.sub, langs.join(' · ').toUpperCase()].filter(Boolean).join(' · ');
			draft = !place.published;

			if (draft) {
				flags.push('<span class="ipo-rp-tag is-muted">טיוטה</span>');
			}

			if (pick && rule.respect_page_pick) {
				flags.push('<span class="ipo-rp-tag is-muted" title="בחירה ידנית בעמוד קובעת את הרשימה">✎</span>');
			}
		}

		return '<button type="button" class="ipo-rp-place' + (active ? ' is-active' : '') + (draft ? ' is-draft' : '') + '"' +
			' data-act="place" data-zone="' + esc(zoneKey) + '" data-type="' + esc(type) + '" data-key="' + esc(key) + '"' +
			(active ? ' aria-current="true"' : '') + '>' +
			'<span class="ipo-rp-place-name">' + esc(place.title) + '</span>' +
			'<span class="ipo-rp-place-sub">' + esc(sub) + '</span>' +
			'<span class="ipo-rp-place-flags">' + flags.join('') + '</span>' +
		'</button>';
	}

	function renderNav() {
		var query = state.navQuery.trim().toLowerCase();
		var html = '';

		B.zones.forEach(function (zone) {
			var rows = [{ type: 'default', key: 0, title: 'ברירת מחדל', sub: zone.pageRules ? 'לכל עמוד שאין לו הגדרה משלו' : (zone.categoryRules ? 'לכל תוכנית שאין לקטגוריה שלה הגדרה' : '') }]
				.concat(B.places[zone.key] || []);

			if (query) {
				rows = rows.filter(function (place) {
					var text = (place.title + ' ' + (place.sub || '') + ' ' + zone.short + ' ' + (place.versions || []).map(function (version) { return version.title; }).join(' ')).toLowerCase();

					return text.indexOf(query) !== -1;
				});
			}

			if (!rows.length) {
				return;
			}

			html += '<section class="ipo-rp-group">' +
				'<h3 class="ipo-rp-group-title"><span>' + esc(zone.short) + '</span><span>' + ((B.places[zone.key] || []).length + 1) + '</span></h3>' +
				rows.map(function (place) { return navRow(zone.key, place); }).join('') +
			'</section>';
		});

		slot('nav').innerHTML = html || '<p class="ipo-rp-empty">לא נמצא מקום בשם הזה.</p>';
	}

	function sectionHead(number, title, hint) {
		return '<div class="ipo-rp-section-head"><span class="ipo-rp-num">' + number + '</span><div>' +
			'<h3 class="ipo-rp-section-title">' + title + '</h3>' +
			(hint ? '<span class="ipo-rp-section-hint">' + hint + '</span>' : '') +
		'</div></div>';
	}

	function combo(list, placeholder) {
		return '<div class="ipo-rp-combo">' +
			'<input type="search" class="ipo-rp-field" data-combo="' + list + '" placeholder="' + esc(placeholder) + '" autocomplete="off"' +
			' role="combobox" aria-expanded="false" aria-autocomplete="list" aria-controls="ipo-rp-results-' + list + '">' +
			'<ul class="ipo-rp-results" id="ipo-rp-results-' + list + '" role="listbox" hidden></ul>' +
		'</div>';
	}

	function placeHeader(zone, place) {
		var links = [];

		if (place.type === 'page') {
			place.versions.forEach(function (version) {
				var lang = version.lang ? version.lang.toUpperCase() : '';

				if (version.view) {
					links.push('<a href="' + esc(version.view) + '" target="_blank" rel="noopener">צפייה בעמוד ' + esc(lang) + ' ↗</a>');
				}

				if (version.edit) {
					links.push('<a href="' + esc(version.edit) + '" target="_blank" rel="noopener">עריכת העמוד ' + esc(lang) + '</a>');
				}
			});
		}

		var desc;

		if (place.type === 'default') {
			desc = zone.description + ' ' + (zone.pageRules
				? 'ההגדרה כאן חלה על כל עמוד ברשימה שאין לו הגדרה ייעודית.'
				: 'ההגדרה כאן חלה על כל תוכנית שהקטגוריה שלה לא קיבלה הגדרה ייעודית.');
		} else if (place.type === 'category') {
			desc = 'חל על עמודי תוכנית בקטגוריה ' + place.title + '. אם תוכנית שייכת לכמה קטגוריות — הראשונה עם הגדרה ייעודית קובעת.';
		} else {
			desc = 'העמוד בנוי בתבנית ' + place.sub + '. ההגדרה חלה על העמוד ועל התרגומים שלו.';
		}

		return '<div class="ipo-rp-place-head">' +
			'<span class="ipo-rp-eyebrow">' + esc(zone.label) + '</span>' +
			'<h2 class="ipo-rp-place-title">' + esc(place.type === 'default' ? 'ברירת מחדל — ' + zone.short : place.title) + '</h2>' +
			'<p class="ipo-rp-place-desc">' + esc(desc) + '</p>' +
			(links.length ? '<div class="ipo-rp-links">' + links.join('') + '</div>' : '') +
		'</div>';
	}

	function summary(rule) {
		var mode = MODES[rule.mode === 'same_category' && state.zone !== 'single_program' ? 'legacy' : rule.mode];
		var parts = [mode ? mode.title : rule.mode];

		if (rule.mode === 'categories' && rule.categories.length) {
			parts[0] += ': ' + rule.categories.map(function (id) { return categories[id] ? categories[id].name : id; }).join(', ');
		}

		if (rule.promoted_ids.length) {
			parts.push(plural(rule.promoted_ids.length, 'מקודמת אחת', 'מקודמות'));
		}

		if (rule.exclude_ids.length) {
			parts.push(plural(rule.exclude_ids.length, 'מוסתרת אחת', 'מוסתרות'));
		}

		parts.push(rule.max_items ? 'עד ' + rule.max_items + ' כרטיסים' : 'ללא הגבלת כמות');

		return parts.join(' · ');
	}

	function renderEditor() {
		var zone = zones[state.zone];
		var place = findPlace(state.zone, state.type, state.key);

		if (!place) {
			state.type = 'default';
			state.key = 0;
			place = findPlace(state.zone, 'default', 0);
		}

		var html = placeHeader(zone, place);
		var custom = state.type === 'default' || isCustom(state.zone, state.type, state.key);

		if (state.type !== 'default') {
			html += '<div class="ipo-rp-inherit" role="group" aria-label="מקור ההגדרה">' +
				'<button type="button" data-act="inherit" aria-pressed="' + (!custom) + '"><b>לפי ברירת המחדל</b><span>' + esc(zone.short) + ' — כמו כל השאר</span></button>' +
				'<button type="button" data-act="own" aria-pressed="' + custom + '"><b>הגדרה ייעודית</b><span>קביעה משלו למקום הזה בלבד</span></button>' +
			'</div>';
		}

		if (!custom) {
			html += '<div class="ipo-rp-inherit-note">' +
				'<p>המקום הזה מציג לפי ברירת המחדל: <b>' + esc(summary(effectiveRule(state.zone, 'default', 0))) + '</b>.</p>' +
				'<button type="button" class="ipo-rp-btn" data-act="own">התאמה למקום הזה</button> ' +
				'<button type="button" class="ipo-rp-linkbtn" data-act="goto-default">עריכת ברירת המחדל</button>' +
			'</div>';

			slot('editor').innerHTML = html;
			return;
		}

		var rule = editable();
		var modes = modesFor(state.zone, state.type, rule.mode);
		var currentMode = rule.mode === 'same_category' && state.zone !== 'single_program' ? 'legacy' : rule.mode;
		var n = 1;

		// 1 — source
		html += '<section class="ipo-rp-section">' + sectionHead(n++, 'מה יוצג', 'מאיפה נשלפות התוכניות למקום הזה.') + '<div class="ipo-rp-section-body">';
		html += '<div class="ipo-rp-modes" role="radiogroup" aria-label="מקור התוכניות">' + modes.map(function (mode) {
			var checked = currentMode === mode;

			return '<label class="ipo-rp-mode' + (checked ? ' is-checked' : '') + '">' +
				'<input type="radio" name="ipo-rp-mode" value="' + mode + '"' + (checked ? ' checked' : '') + ' data-change="mode">' +
				'<b>' + MODES[mode].title + '</b><span>' + MODES[mode].hint + '</span>' +
			'</label>';
		}).join('') + '</div>';

		if (rule.mode === 'categories') {
			html += '<div class="ipo-rp-chips" role="group" aria-label="קטגוריות">' + B.categories.map(function (category) {
				var on = rule.categories.indexOf(category.id) !== -1;

				return '<button type="button" class="ipo-rp-chip" data-act="cat" data-id="' + category.id + '" aria-pressed="' + on + '">' +
					esc(category.name) + '<small>' + category.count + '</small></button>';
			}).join('') + '</div>';

			if (!rule.categories.length) {
				html += '<div class="ipo-rp-callout is-warn">לא סומנה אף קטגוריה, ולכן יוצגו רק תוכניות שנוספו ידנית או קודמו.</div>';
			}
		}

		if (zone.pickLabel) {
			html += '<div style="margin-top:18px">' + toggle('respect', respectOf(),
				'הבחירה הידנית בעמוד גוברת',
				'כשהשדה ' + zone.pickLabel + ' מלא, הוא קובע אילו תוכניות יוצגו, וההגדרות כאן רק ממיינות, מסננות ומקדמות.') + '</div>';
		}

		html += '</div></section>';

		// 2 — promoted
		html += '<section class="ipo-rp-section">' + sectionHead(n++, 'מקודמות — מוצגות ראשונות', 'בסדר שתקבעו כאן. אפשר לגרור, או להשתמש בחיצים. תוכנית שכל התאריכים שלה עברו יורדת מהרשימה באתר מעצמה.') + '<div class="ipo-rp-section-body">';

		if (rule.promoted_ids.length) {
			html += '<ol class="ipo-rp-list" data-list="promoted">' + rule.promoted_ids.map(function (id, index) {
				var program = programs[id];
				var past = !program || !program.d;

				return '<li class="ipo-rp-row' + (past ? ' is-past' : '') + '" draggable="true" data-id="' + id + '">' +
					'<span class="ipo-rp-handle" aria-hidden="true">⋮⋮</span>' +
					'<span class="ipo-rp-rank">' + (index + 1) + '</span>' +
					'<span style="min-width:0"><span class="ipo-rp-row-title" title="' + esc(programLabel(id)) + '">' + esc(programLabel(id)) + '</span>' +
					'<span class="ipo-rp-row-meta">' + langTag(program && program.l) +
						(past ? '<span class="ipo-rp-tag is-warn">אין תאריך עתידי — לא יוצג</span>' : '<span>' + esc(dateText(program)) + '</span>') +
						'<span>#' + id + '</span></span></span>' +
					'<span class="ipo-rp-row-actions">' +
						'<button type="button" class="ipo-rp-icon" data-act="up" data-id="' + id + '"' + (index === 0 ? ' disabled' : '') + ' aria-label="למעלה">↑</button>' +
						'<button type="button" class="ipo-rp-icon" data-act="down" data-id="' + id + '"' + (index === rule.promoted_ids.length - 1 ? ' disabled' : '') + ' aria-label="למטה">↓</button>' +
						'<button type="button" class="ipo-rp-icon is-danger" data-act="remove" data-list="promoted_ids" data-id="' + id + '" aria-label="הסרה מהמקודמות">✕</button>' +
					'</span>' +
				'</li>';
			}).join('') + '</ol>';
		} else {
			html += '<p class="ipo-rp-empty">אין תוכניות מקודמות. חפשו תוכנית למטה, או לחצו ★ על כרטיס בתצוגה המקדימה.</p>';
		}

		html += combo('promoted_ids', 'הוספת תוכנית מקודמת — חיפוש לפי שם או מספר…') + '</div></section>';

		// 3 — always add
		var manualTitle = rule.mode === 'manual' ? 'התוכניות שיוצגו' : 'תוספות קבועות';
		var manualHint = rule.mode === 'manual'
			? 'במצב ״רק בחירה ידנית״ אלה התוכניות היחידות שיוצגו (יחד עם המקודמות), ממוינות לפי תאריך.'
			: 'מצטרפות לרשימה גם אם הכלל האוטומטי לא מביא אותן, וממוינות לפי תאריך יחד עם השאר.';

		html += '<section class="ipo-rp-section">' + sectionHead(n++, manualTitle, manualHint) + '<div class="ipo-rp-section-body">' +
			pills(rule.manual_ids, 'manual_ids', false) +
			combo('manual_ids', 'הוספת תוכנית…') +
		'</div></section>';

		// 4 — hidden
		html += '<section class="ipo-rp-section">' + sectionHead(n++, 'לא להציג', 'לא יוצגו במקום הזה בשום מצב.') + '<div class="ipo-rp-section-body">' +
			pills(rule.exclude_ids, 'exclude_ids', true) +
			combo('exclude_ids', 'הסתרת תוכנית…') +
		'</div></section>';

		// 5 — display
		html += '<section class="ipo-rp-section">' + sectionHead(n++, 'סדר ותצוגה', '') + '<div class="ipo-rp-section-body"><div class="ipo-rp-options">' +
			'<div><span class="ipo-rp-label">סדר אחרי המקודמות</span><div class="ipo-rp-seg" role="group">' +
				'<button type="button" data-act="order" data-val="date_asc" aria-pressed="' + (rule.order === 'date_asc') + '">מהקרוב לרחוק</button>' +
				'<button type="button" data-act="order" data-val="date_desc" aria-pressed="' + (rule.order === 'date_desc') + '">מהרחוק לקרוב</button>' +
			'</div></div>' +
			'<div><span class="ipo-rp-label">מספר כרטיסים מרבי</span><div class="ipo-rp-stepper">' +
				'<button type="button" data-act="max" data-val="1" aria-label="עוד כרטיס">+</button>' +
				'<output aria-live="polite">' + (rule.max_items ? rule.max_items : 'ללא הגבלה') + '</output>' +
				'<button type="button" data-act="max" data-val="-1" aria-label="פחות כרטיסים">−</button>' +
			'</div></div>' +
			'<div>' + toggle('future', rule.require_future_event, 'רק תוכניות עם תאריך עתידי', 'להסתיר תוכניות שכל התאריכים שלהן כבר עברו.') + '</div>' +
		'</div></div></section>';

		slot('editor').innerHTML = html;
	}

	function toggle(name, checked, title, hint) {
		return '<label class="ipo-rp-toggle">' +
			'<input type="checkbox" data-change="' + name + '"' + (checked ? ' checked' : '') + '>' +
			'<span class="ipo-rp-switch" aria-hidden="true"></span>' +
			'<span><b>' + esc(title) + '</b><span class="ipo-rp-toggle-hint">' + esc(hint) + '</span></span>' +
		'</label>';
	}

	function pills(ids, list, excluded) {
		if (!ids.length) {
			return '';
		}

		return '<ul class="ipo-rp-pills">' + ids.map(function (id) {
			var program = programs[id];

			return '<li class="ipo-rp-pill' + (excluded ? ' is-excluded' : '') + '" title="' + esc(programLabel(id) + ' · ' + dateText(program)) + '">' +
				langTag(program && program.l) + '<span>' + esc(programLabel(id)) + '</span>' +
				'<button type="button" data-act="remove" data-list="' + list + '" data-id="' + id + '" aria-label="הסרה">✕</button>' +
			'</li>';
		}).join('') + '</ul>';
	}

	function renderBar() {
		document.querySelector('.ipo-rp-bar').classList.toggle('is-visible', isDirty());
	}

	/* ------------------------------------------------------------- preview */

	var previewTimer;
	var previewSeq = 0;
	var lastPreview = null;

	function schedulePreview(delay) {
		clearTimeout(previewTimer);
		previewTimer = setTimeout(loadPreview, delay === undefined ? 450 : delay);

		var cards = slot('preview').querySelector('.ipo-rp-cards');

		if (cards) {
			cards.classList.add('is-loading');
		}
	}

	function loadPreview() {
		var seq = ++previewSeq;
		var rule = effectiveRule(state.zone, state.type, state.key);
		var id = placeId(state.zone, state.type, state.key);

		if (!slot('preview').querySelector('.ipo-rp-cards')) {
			renderPreview(null);
		}

		post('ipo_related_programs_preview', {
			zone: state.zone,
			place_type: state.type,
			place_key: state.key || 0,
			lang: state.lang,
			sample: state.samples[id] || 0,
			rule: JSON.stringify(rule)
		}).then(function (data) {
			if (seq === previewSeq) {
				lastPreview = data;
				renderPreview(data);
			}
		}).catch(function (error) {
			if (seq === previewSeq) {
				renderPreview({ error: error.message });
			}
		});
	}

	function renderPreview(data) {
		var langs = B.languages.length > 1
			? '<div class="ipo-rp-seg" role="group" aria-label="שפה">' + B.languages.map(function (lang) {
				return '<button type="button" data-act="lang" data-val="' + esc(lang) + '" aria-pressed="' + (state.lang === lang) + '">' + esc(lang) + '</button>';
			}).join('') + '</div>'
			: '';

		var html = '<div class="ipo-rp-preview-head"><h3 class="ipo-rp-preview-title">כך זה <strong>ייראה</strong></h3>' + langs + '</div>';

		if (!data) {
			slot('preview').innerHTML = html + '<p class="ipo-rp-preview-context">מחשב…</p><ol class="ipo-rp-cards is-loading"></ol>';
			return;
		}

		if (data.error) {
			slot('preview').innerHTML = html + '<div class="ipo-rp-callout is-warn">' + esc(data.error) + '</div>';
			return;
		}

		var context = data.context || {};
		var line = '';

		if (state.zone === 'single_program') {
			line = context.title
				? 'בעמוד התוכנית <a href="' + esc(context.url) + '" target="_blank" rel="noopener">' + esc(context.title) + '</a> · <button type="button" class="ipo-rp-linkbtn" data-act="sample">החלפת תוכנית לדוגמה</button>'
				: 'לא נמצאה תוכנית לדוגמה בקטגוריה הזו.';
			line += '<div class="ipo-rp-combo" data-sample-combo hidden style="margin-top:8px">' +
				'<input type="search" class="ipo-rp-field" data-combo="sample" placeholder="בחירת תוכנית לדוגמה…" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="ipo-rp-results-sample">' +
				'<ul class="ipo-rp-results" id="ipo-rp-results-sample" role="listbox" hidden></ul></div>';
		} else if (context.title) {
			line = 'בעמוד <a href="' + esc(context.url) + '" target="_blank" rel="noopener">' + esc(context.title) + '</a>';
		} else if (context.note) {
			line = esc(context.note);
		} else {
			line = 'בכל עמוד שמשתמש בברירת המחדל (ללא בחירה ידנית בעמוד).';
		}

		html += '<p class="ipo-rp-preview-context">' + line + '</p>';

		if (context.pick_used) {
			html += '<div class="ipo-rp-callout" style="margin-bottom:12px">בעמוד הזה נבחרו ידנית <b>' + context.pick + ' תוכניות</b>, והן שקובעות את הרשימה. כדי שההגדרות כאן יקבעו, כבו את ״הבחירה הידנית בעמוד גוברת״.</div>';
		} else if (context.pick && !context.pick_used) {
			html += '<div class="ipo-rp-callout" style="margin-bottom:12px">בעמוד יש בחירה ידנית של ' + context.pick + ' תוכניות, אבל היא לא בשימוש כי ההגדרות כאן גוברות.</div>';
		}

		var items = data.items || [];

		if (!items.length) {
			html += '<p class="ipo-rp-preview-empty">' + (context.fallback
				? 'הכללים לא מחזירים אף תוכנית, ולכן המודול יציג את <b>האירועים הקרובים באתר</b> (ההתנהגות הישנה).'
				: (state.zone === 'single_program' ? 'אין תוכניות להציג — המודול לא יופיע בעמוד.' : 'אין תוכניות להציג.')) + '</p>';
		} else {
			html += '<ol class="ipo-rp-cards">' + items.map(function (item, index) {
				var tags = [];

				if (item.promoted) {
					tags.push('<span class="ipo-rp-tag is-solid">★ מקודמת</span>');
				}

				if (SOURCE_LABELS[item.source] && item.source !== 'promoted') {
					tags.push('<span class="ipo-rp-tag is-muted">' + SOURCE_LABELS[item.source] + '</span>');
				}

				return '<li class="ipo-rp-card">' +
					'<span class="ipo-rp-card-n">' + (index + 1) + '</span>' +
					(item.thumb ? '<img src="' + esc(item.thumb) + '" alt="" loading="lazy">' : '<span class="ipo-rp-noimg"></span>') +
					'<span style="min-width:0"><a class="ipo-rp-card-title" href="' + esc(item.view) + '" target="_blank" rel="noopener" style="color:inherit;text-decoration:none">' + esc(item.title) + '</a>' +
						'<span class="ipo-rp-card-meta">' + (item.date ? '<span>' + esc(item.date) + '</span>' : '<span>ללא תאריך</span>') + tags.join('') + '</span></span>' +
					'<span class="ipo-rp-card-actions">' +
						'<button type="button" class="ipo-rp-icon" data-act="pin" data-id="' + item.id + '" title="' + (item.promoted ? 'ביטול הקידום' : 'קידום לראש הרשימה') + '" aria-label="' + (item.promoted ? 'ביטול הקידום' : 'קידום') + '">' + (item.promoted ? '☆' : '★') + '</button>' +
						'<button type="button" class="ipo-rp-icon is-danger" data-act="hide" data-id="' + item.id + '" title="לא להציג כאן" aria-label="הסתרה">⦸</button>' +
					'</span>' +
				'</li>';
			}).join('') + '</ol>';

			html += '<p class="ipo-rp-preview-foot">' + items.length + ' כרטיסים · מחושב מההגדרות שעל המסך, גם לפני שמירה.</p>';
		}

		slot('preview').innerHTML = html;
	}

	/* -------------------------------------------------------------- search */

	var combo$ = null;

	function normalizeText(text) {
		return String(text || '').toLowerCase().replace(/[֑-ׇ]/g, '').replace(/["'׳״]/g, '');
	}

	function searchPrograms(query, list) {
		var needle = normalizeText(query.trim());
		var taken = list === 'sample' ? [] : editable()[list];
		var results = [];

		B.programs.forEach(function (program) {
			if (needle) {
				if (normalizeText(program.t).indexOf(needle) === -1 && String(program.id) !== needle.replace('#', '')) {
					return;
				}
			} else if (!program.d || (program.l && program.l !== state.lang)) {
				// With nothing typed yet, offer what is coming up in this language.
				return;
			}

			results.push(program);
		});

		results.sort(function (a, b) {
			var aLang = a.l === state.lang ? 0 : 1;
			var bLang = b.l === state.lang ? 0 : 1;

			if (!!a.ts !== !!b.ts) {
				return a.ts ? -1 : 1;
			}

			if (aLang !== bLang) {
				return aLang - bLang;
			}

			return (a.ts || 0) - (b.ts || 0) || a.t.localeCompare(b.t, 'he');
		});

		return results.slice(0, 40).map(function (program) {
			return { program: program, taken: taken.indexOf(program.id) !== -1 };
		});
	}

	function openCombo(input) {
		var list = input.getAttribute('data-combo');
		var results = searchPrograms(input.value, list);
		var ul = input.parentNode.querySelector('.ipo-rp-results');

		combo$ = { input: input, list: list, ul: ul, results: results, index: -1 };

		if (!results.length) {
			ul.innerHTML = '<li class="ipo-rp-empty-results">לא נמצאה תוכנית. נסו חלק מהשם או מספר (#).</li>';
		} else {
			ul.innerHTML = results.map(function (result, index) {
				var program = result.program;
				var cats = program.c.map(function (id) { return categories[id] ? categories[id].name : ''; }).filter(Boolean).join(', ');

				return '<li class="ipo-rp-result' + (program.d ? '' : ' is-past') + (result.taken ? ' is-taken' : '') + '" role="option" id="ipo-rp-opt-' + list + '-' + index + '" data-index="' + index + '" aria-selected="false">' +
					'<span class="ipo-rp-result-title">' + esc(program.t) + '</span>' +
					'<span class="ipo-rp-result-meta">' + esc(dateText(program)) + (cats ? ' · ' + esc(cats) : '') + ' · #' + program.id + '</span>' +
					'<span class="ipo-rp-result-side">' + (result.taken ? '<span class="ipo-rp-tag is-muted">כבר ברשימה</span>' : '') + langTag(program.l) + '</span>' +
				'</li>';
			}).join('');
		}

		ul.hidden = false;
		input.setAttribute('aria-expanded', 'true');
	}

	function closeCombo() {
		if (!combo$) {
			return;
		}

		combo$.ul.hidden = true;
		combo$.input.setAttribute('aria-expanded', 'false');
		combo$.input.removeAttribute('aria-activedescendant');
		combo$ = null;
	}

	function highlight(index) {
		if (!combo$ || !combo$.results.length) {
			return;
		}

		combo$.index = (index + combo$.results.length) % combo$.results.length;

		Array.prototype.forEach.call(combo$.ul.children, function (li, i) {
			li.setAttribute('aria-selected', i === combo$.index ? 'true' : 'false');

			if (i === combo$.index) {
				li.scrollIntoView({ block: 'nearest' });
				combo$.input.setAttribute('aria-activedescendant', li.id);
			}
		});
	}

	function choose(index) {
		var result = combo$ && combo$.results[index];

		if (!result || result.taken) {
			return;
		}

		var list = combo$.list;
		var id = result.program.id;

		closeCombo();

		if (list === 'sample') {
			state.samples[placeId(state.zone, state.type, state.key)] = id;
			schedulePreview(0);
			return;
		}

		addTo(list, id);
		changed();

		var input = slot('editor').querySelector('[data-combo="' + list + '"]');

		if (input) {
			input.focus();
		}

		toast('נוספה: ' + result.program.t);
	}

	/** Put an ID on one list, taking it off the lists it cannot share. */
	function addTo(list, id) {
		var rule = editable();

		if (list === 'exclude_ids') {
			rule.promoted_ids = rule.promoted_ids.filter(function (x) { return x !== id; });
			rule.manual_ids = rule.manual_ids.filter(function (x) { return x !== id; });
		} else {
			rule.exclude_ids = rule.exclude_ids.filter(function (x) { return x !== id; });
		}

		if (rule[list].indexOf(id) === -1) {
			rule[list].push(id);
		}
	}

	/* -------------------------------------------------------------- events */

	function changed() {
		renderStats();
		renderNav();
		renderEditor();
		renderBar();
		schedulePreview();
	}

	function selectPlace(zone, type, key) {
		state.zone = zone;
		state.type = type;
		state.key = Number(key) || 0;

		try {
			history.replaceState(null, '', '#' + placeId(zone, type, key));
		} catch (e) {}

		renderNav();
		renderEditor();
		renderPreview(null);
		schedulePreview(0);
		slot('editor').scrollIntoView({ block: 'nearest' });
	}

	/** Pin / hide from the preview edit this place, making it its own first. */
	function ensureOwn() {
		if (state.type !== 'default' && !isCustom(state.zone, state.type, state.key)) {
			makeCustom();
			toast('המקום הזה עבר להגדרה ייעודית');
		}
	}

	function onClick(event) {
		var target = event.target.closest('[data-act]');

		if (!target) {
			return;
		}

		var act = target.getAttribute('data-act');
		var id = Number(target.getAttribute('data-id'));
		var rule;
		var index;

		switch (act) {
			case 'place':
				selectPlace(target.getAttribute('data-zone'), target.getAttribute('data-type'), target.getAttribute('data-key'));
				return;

			case 'goto-default':
				selectPlace(state.zone, 'default', 0);
				return;

			case 'own':
				makeCustom();
				changed();
				return;

			case 'inherit':
				overrideOf(state.zone, state.type, state.key) && (overrideOf(state.zone, state.type, state.key).enabled = 0);
				changed();
				return;

			case 'cat':
				rule = editable();
				index = rule.categories.indexOf(id);

				if (index === -1) {
					rule.categories.push(id);
				} else {
					rule.categories.splice(index, 1);
				}

				changed();
				return;

			case 'up':
			case 'down':
				rule = editable();
				index = rule.promoted_ids.indexOf(id);

				var to = act === 'up' ? index - 1 : index + 1;

				if (index !== -1 && to >= 0 && to < rule.promoted_ids.length) {
					rule.promoted_ids.splice(index, 1);
					rule.promoted_ids.splice(to, 0, id);
					changed();
				}

				return;

			case 'remove':
				rule = editable();
				rule[target.getAttribute('data-list')] = rule[target.getAttribute('data-list')].filter(function (x) { return x !== id; });
				changed();
				return;

			case 'order':
				editable().order = target.getAttribute('data-val');
				changed();
				return;

			case 'max':
				rule = editable();
				rule.max_items = Math.max(0, rule.max_items + Number(target.getAttribute('data-val')));
				changed();
				return;

			case 'lang':
				state.lang = target.getAttribute('data-val');
				renderPreview(lastPreview);
				schedulePreview(0);
				return;

			case 'sample':
				var box = slot('preview').querySelector('[data-sample-combo]');

				box.hidden = false;
				box.querySelector('input').focus();
				return;

			case 'pin':
				ensureOwn();
				rule = editable();

				var card = lastPreview && lastPreview.items.filter(function (item) { return item.id === id; })[0];
				var refs = card && card.refs ? card.refs.map(Number) : [];

				if (card && (card.promoted || refs.length)) {
					// The preview may be in the other language; drop the stored
					// entries that map onto this card.
					rule.promoted_ids = rule.promoted_ids.filter(function (x) { return x !== id && refs.indexOf(x) === -1; });
					toast('הקידום בוטל');
				} else {
					addTo('promoted_ids', id);
					toast('קודמה לראש הרשימה');
				}

				changed();
				return;

			case 'hide':
				ensureOwn();
				addTo('exclude_ids', id);
				toast('לא תוצג במקום הזה');
				changed();
				return;

			case 'save':
				save();
				return;

			case 'discard':
				if (window.confirm('לבטל את כל השינויים שלא נשמרו?')) {
					store = normalizeStore(JSON.parse(baselineJson));
					changed();
				}

				return;
		}
	}

	function onChange(event) {
		var target = event.target;
		var kind = target.getAttribute('data-change');

		if (!kind) {
			return;
		}

		if (kind === 'mode') {
			editable().mode = target.value === 'legacy' ? 'same_category' : target.value;

			// Moving a page off the legacy fallback onto "all upcoming" with no
			// cap would put every programme on the page.
			if (target.value === 'upcoming' && !editable().max_items) {
				editable().max_items = 12;
			}
		} else if (kind === 'respect') {
			setRespect(target.checked);
		} else if (kind === 'future') {
			editable().require_future_event = target.checked ? 1 : 0;
		}

		changed();
	}

	function onInput(event) {
		var target = event.target;

		if (target.getAttribute('data-input') === 'nav') {
			state.navQuery = target.value;
			renderNav();
			return;
		}

		if (target.hasAttribute('data-combo')) {
			openCombo(target);
		}
	}

	function onKeydown(event) {
		var target = event.target;

		if (target.hasAttribute('data-combo')) {
			if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
				event.preventDefault();

				if (!combo$ || combo$.input !== target) {
					openCombo(target);
				}

				highlight(combo$.index + (event.key === 'ArrowDown' ? 1 : -1));
			} else if (event.key === 'Enter') {
				event.preventDefault();

				if (combo$ && combo$.index > -1) {
					choose(combo$.index);
				} else if (combo$ && combo$.results.length === 1) {
					choose(0);
				}
			} else if (event.key === 'Escape') {
				closeCombo();
			}
		}
	}

	var baselineJson = JSON.stringify(store);

	function save() {
		if (state.saving) {
			return;
		}

		state.saving = true;
		document.querySelectorAll('[data-act="save"]').forEach(function (button) { button.disabled = true; });

		post('ipo_related_programs_save', { store: JSON.stringify(store) })
			.then(function (data) {
				store = normalizeStore(data.store);
				baseline = canonical(store);
				baselineJson = JSON.stringify(store);
				toast('נשמר ב-' + data.saved + ' — האתר מעודכן');
				changed();
			})
			.catch(function (error) {
				toast('השמירה נכשלה: ' + error.message, true);
			})
			.then(function () {
				state.saving = false;
				document.querySelectorAll('[data-act="save"]').forEach(function (button) { button.disabled = false; });
			});
	}

	/* Drag to reorder the promoted list. */
	var dragId = null;

	function onDragStart(event) {
		var row = event.target.closest && event.target.closest('.ipo-rp-row');

		if (!row) {
			return;
		}

		dragId = Number(row.getAttribute('data-id'));
		row.classList.add('is-dragging');
		event.dataTransfer.effectAllowed = 'move';

		try {
			event.dataTransfer.setData('text/plain', String(dragId));
		} catch (e) {}
	}

	function onDragOver(event) {
		var row = event.target.closest && event.target.closest('.ipo-rp-row');

		if (!row || dragId === null) {
			return;
		}

		event.preventDefault();
		root.querySelectorAll('.ipo-rp-row.is-over').forEach(function (el) { el.classList.remove('is-over'); });
		row.classList.add('is-over');
	}

	function onDrop(event) {
		var row = event.target.closest && event.target.closest('.ipo-rp-row');

		if (!row || dragId === null) {
			return;
		}

		event.preventDefault();

		var rule = editable();
		var overId = Number(row.getAttribute('data-id'));
		var from = rule.promoted_ids.indexOf(dragId);

		if (from !== -1 && overId !== dragId) {
			rule.promoted_ids.splice(from, 1);
			rule.promoted_ids.splice(rule.promoted_ids.indexOf(overId), 0, dragId);
		}

		dragId = null;
		changed();
	}

	function onDragEnd() {
		dragId = null;
		root.querySelectorAll('.ipo-rp-row').forEach(function (el) { el.classList.remove('is-dragging', 'is-over'); });
	}

	/* ---------------------------------------------------------------- boot */

	renderShell();

	root.addEventListener('click', onClick);
	root.addEventListener('change', onChange);
	root.addEventListener('input', onInput);
	root.addEventListener('keydown', onKeydown);
	root.addEventListener('dragstart', onDragStart);
	root.addEventListener('dragover', onDragOver);
	root.addEventListener('drop', onDrop);
	root.addEventListener('dragend', onDragEnd);

	root.addEventListener('focusin', function (event) {
		if (event.target.hasAttribute('data-combo')) {
			openCombo(event.target);
		}
	});

	root.addEventListener('mousedown', function (event) {
		var option = event.target.closest('.ipo-rp-result');

		if (option && combo$) {
			// mousedown, so the pick lands before the input's blur closes the list.
			event.preventDefault();
			choose(Number(option.getAttribute('data-index')));
		}
	});

	document.addEventListener('mousedown', function (event) {
		if (combo$ && !event.target.closest('.ipo-rp-combo')) {
			closeCombo();
		}
	});

	document.addEventListener('keydown', function (event) {
		if ((event.ctrlKey || event.metaKey) && (event.key === 's' || event.key === 'S')) {
			event.preventDefault();

			if (isDirty()) {
				save();
			}
		}
	});

	window.addEventListener('beforeunload', function (event) {
		if (isDirty()) {
			event.preventDefault();
			event.returnValue = '';
		}
	});

	// Open on the place in the URL, else the home page's upcoming strip.
	var hash = decodeURIComponent((location.hash || '').replace(/^#/, '')).split('/');

	if (hash.length === 3 && zones[hash[0]] && findPlace(hash[0], hash[1], hash[2])) {
		state.zone = hash[0];
		state.type = hash[1];
		state.key = Number(hash[2]) || 0;
	} else {
		var home = (B.places.home_upcoming || []).filter(function (place) { return place.is_home; })[0];

		if (home) {
			state.type = 'page';
			state.key = home.key;
		}
	}

	renderAll();
})();
