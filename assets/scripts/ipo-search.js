/**
 * IPO Search — searches a static JSON index in the browser.
 *
 * The index is fetched once, on the first time the box is opened, and kept in
 * memory for the rest of the visit. Every keystroke after that is a filter over
 * arrays: no network, no admin-ajax, no waiting.
 *
 * Vanilla on purpose — this runs on every page and does not need jQuery.
 */
(function () {
	'use strict';

	var CONFIG = window.IPO_SEARCH || {};
	var TEXT = CONFIG.i18n || {};

	var MIN_CHARS = 2;
	var LIMIT_PROGRAMS = 6;
	var LIMIT_SIDE = 5;
	var DATES_SHOWN = 3;

	var index = null;
	var loading = null;
	var showPast = false;

	/**
	 * Fold away the differences that stop a Hebrew search from matching:
	 * niqqud, geresh variants, and the maqaf. Latin text is lowercased.
	 */
	function normalise(text) {
		return String(text || '')
			.toLowerCase()
			.replace(/[֑-ׇ]/g, '')     // niqqud and cantillation
			.replace(/[׳’']/g, '')      // geresh, curly and straight apostrophes
			.replace(/[־‐-―-]/g, ' ')
			.replace(/\s+/g, ' ')
			.trim();
	}

	function loadIndex() {
		if (index) {
			return Promise.resolve(index);
		}
		if (loading) {
			return loading;
		}

		loading = fetch(CONFIG.index, { credentials: 'omit' })
			.then(function (response) {
				if (!response.ok) {
					throw new Error('index ' + response.status);
				}
				return response.json();
			})
			.then(function (data) {
				index = data;
				prepare(index);
				return index;
			})
			.catch(function (error) {
				loading = null;
				throw error;
			});

		return loading;
	}

	/**
	 * Pre-normalise every title once, so typing does not redo it per keystroke.
	 */
	function prepare(data) {
		['pr', 'pp', 'ar', 'se', 'pg'].forEach(function (key) {
			(data[key] || []).forEach(function (row) {
				row.n = normalise(row[0]);
			});
		});
	}

	function match(rows, needle, limit) {
		var starts = [];
		var contains = [];

		for (var i = 0; i < rows.length; i++) {
			var position = rows[i].n.indexOf(needle);
			if (position === 0) {
				starts.push(rows[i]);
			} else if (position > 0) {
				contains.push(rows[i]);
			}
			// Keep scanning: a later title may still start with the term, and
			// those should outrank the ones that merely contain it.
			if (starts.length >= limit && contains.length >= limit) {
				break;
			}
		}

		return starts.concat(contains).slice(0, limit);
	}

	function formatDate(value) {
		// value is "YYYY-MM-DD HH:MM"
		var parts = String(value).split(/[- :]/);
		if (parts.length < 3) {
			return value;
		}
		var day = parts[2];
		var month = parts[1];
		var time = parts.length > 4 ? parts[3] + ':' + parts[4] : '';
		return day + '.' + month + (time ? ' · ' + time : '');
	}

	function escapeHtml(text) {
		return String(text).replace(/[&<>"']/g, function (character) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character];
		});
	}

	function imageUrl(suffix) {
		if (!suffix) {
			return '';
		}
		return /^https?:/.test(suffix) ? suffix : index.up + suffix;
	}

	function linkUrl(suffix) {
		return /^https?:/.test(suffix) ? suffix : index.home + suffix;
	}

	function programRow(row, isPast) {
		var image = imageUrl(row[2]);
		var dates = '';

		if (!isPast) {
			var all = row[4] || [];
			var shown = all.slice(0, DATES_SHOWN).map(function (date) {
				return '<span class="ipo-search__date">' + escapeHtml(formatDate(date)) + '</span>';
			}).join('');
			var rest = all.length - DATES_SHOWN;
			if (rest > 0) {
				shown += '<span class="ipo-search__date ipo-search__date--more">+' + rest + ' ' + escapeHtml(TEXT.more || '') + '</span>';
			}
			dates = '<span class="ipo-search__dates">' + shown + '</span>';
		} else {
			dates = '<span class="ipo-search__dates"><span class="ipo-search__date ipo-search__date--past">'
				+ escapeHtml(formatDate(row[4])) + '</span></span>';
		}

		var venues = (!isPast && row[5]) ? '<span class="ipo-search__venue">' + escapeHtml(row[5]) + '</span>' : '';

		return '<a class="ipo-search__hit" href="' + escapeHtml(linkUrl(row[1])) + '">'
			+ (image ? '<span class="ipo-search__thumb"><img src="' + escapeHtml(image) + '" alt="" loading="lazy"></span>' : '<span class="ipo-search__thumb ipo-search__thumb--empty"></span>')
			+ '<span class="ipo-search__body">'
			+ '<span class="ipo-search__title">' + escapeHtml(row[0]) + '</span>'
			+ (row[3] ? '<span class="ipo-search__subtitle">' + escapeHtml(row[3]) + '</span>' : '')
			+ dates + venues
			+ '</span></a>';
	}

	function sideRow(row, withThumb) {
		var image = withThumb ? imageUrl(row[2]) : '';
		return '<a class="ipo-search__side-hit" href="' + escapeHtml(linkUrl(row[1])) + '">'
			+ (image ? '<span class="ipo-search__side-thumb"><img src="' + escapeHtml(image) + '" alt="" loading="lazy"></span>' : '')
			+ '<span>' + escapeHtml(row[0]) + '</span></a>';
	}

	function group(heading, html) {
		if (!html) {
			return '';
		}
		return '<div class="ipo-search__group">'
			+ '<div class="ipo-search__heading">' + escapeHtml(heading) + '</div>'
			+ html + '</div>';
	}

	function render(panel, term) {
		var needle = normalise(term);

		if (needle.length < MIN_CHARS) {
			panel.innerHTML = '';
			panel.classList.remove('is-open');
			return;
		}

		var programs = match(index.pr || [], needle, LIMIT_PROGRAMS);
		var past = showPast ? match(index.pp || [], needle, LIMIT_PROGRAMS) : [];
		var artists = match(index.ar || [], needle, LIMIT_SIDE);
		var series = match(index.se || [], needle, LIMIT_SIDE);
		var pages = match(index.pg || [], needle, LIMIT_SIDE);

		var pastCount = (index.pp || []).filter(function (row) {
			return row.n.indexOf(needle) !== -1;
		}).length;

		var main = '';

		if (programs.length) {
			main += group(TEXT.concerts, programs.map(function (row) {
				return programRow(row, false);
			}).join(''));
		}

		if (showPast && past.length) {
			main += group(TEXT.past, past.map(function (row) {
				return programRow(row, true);
			}).join(''));
		}

		if (pastCount) {
			main += '<button type="button" class="ipo-search__past-toggle" data-past-toggle>'
				+ escapeHtml(showPast ? TEXT.hidePast : TEXT.showPast)
				+ ' (' + pastCount + ')</button>';
		}

		var side = '';
		if (artists.length) {
			side += group(TEXT.artists, artists.map(function (row) {
				return sideRow(row, true);
			}).join(''));
		}
		if (series.length) {
			side += group(TEXT.series, series.map(function (row) {
				return sideRow(row, false);
			}).join(''));
		}
		if (pages.length) {
			side += group(TEXT.pages, pages.map(function (row) {
				return sideRow(row, false);
			}).join(''));
		}

		if (!main && !side) {
			panel.innerHTML = '<div class="ipo-search__empty">' + escapeHtml(TEXT.noResults) + ' "' + escapeHtml(term) + '"</div>';
			panel.classList.add('is-open');
			return;
		}

		panel.innerHTML = '<div class="ipo-search__layout">'
			+ '<div class="ipo-search__main">' + (main || '') + '</div>'
			+ (side ? '<div class="ipo-search__side">' + side + '</div>' : '')
			+ '</div>';
		panel.classList.add('is-open');
	}

	function build(root) {
		var toggle = root.querySelector('.ipo-search__toggle');

		var form = document.createElement('form');
		form.className = 'ipo-search__form';
		form.setAttribute('role', 'search');
		form.action = index ? index.home : '/';

		var input = document.createElement('input');
		input.type = 'search';
		input.name = 's';
		input.className = 'ipo-search__input';
		input.placeholder = TEXT.placeholder || '';
		input.autocomplete = 'off';
		input.setAttribute('aria-label', TEXT.placeholder || 'search');

		var panel = document.createElement('div');
		panel.className = 'ipo-search__panel';

		form.appendChild(input);
		root.appendChild(form);
		root.appendChild(panel);

		var timer = null;

		function schedule() {
			clearTimeout(timer);
			timer = setTimeout(function () {
				render(panel, input.value);
			}, 90);
		}

		input.addEventListener('input', schedule);

		panel.addEventListener('click', function (event) {
			var button = event.target.closest('[data-past-toggle]');
			if (!button) {
				return;
			}
			event.preventDefault();
			showPast = !showPast;
			render(panel, input.value);
		});

		function open() {
			root.classList.add('is-open');
			toggle.setAttribute('aria-expanded', 'true');
			loadIndex().then(function () {
				input.focus();
				if (input.value) {
					render(panel, input.value);
				}
			});
		}

		function close() {
			root.classList.remove('is-open');
			toggle.setAttribute('aria-expanded', 'false');
			panel.classList.remove('is-open');
		}

		toggle.addEventListener('click', function () {
			if (root.classList.contains('is-open')) {
				close();
			} else {
				open();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && root.classList.contains('is-open')) {
				close();
				toggle.focus();
			}
		});

		document.addEventListener('click', function (event) {
			if (root.classList.contains('is-open') && !root.contains(event.target)) {
				close();
			}
		});

		// Warm the index up when the pointer approaches, so the first open feels
		// instant rather than waiting on the download.
		root.addEventListener('mouseenter', function () {
			loadIndex().catch(function () {});
		}, { once: true });

		// The mobile header already has its own magnifier that predates this
		// module, and it sits outside the box. Let it drive the instance in the
		// mobile header rather than adding a second icon next to it.
		if (root.closest('.mobile-header')) {
			var external = document.querySelectorAll('.mobile-search-toggle');

			Array.prototype.forEach.call(external, function (trigger) {
				trigger.addEventListener('click', function (event) {
					event.preventDefault();
					if (root.classList.contains('is-open')) {
						close();
					} else {
						open();
					}
				});
			});
		}
	}

	function start() {
		var roots = document.querySelectorAll('[data-ipo-search]');
		for (var i = 0; i < roots.length; i++) {
			build(roots[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
}());
