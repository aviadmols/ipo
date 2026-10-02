/**
 * Delayed scripts: run every <script type="ipo/delay"> on the visitor's first
 * interaction, in document order. Printed inline at the top of <head> by
 * includes/ipo-delay-js.php — keep it small and dependency-free.
 *
 * - External scripts are preloaded in parallel, then executed one after the
 *   other (async ones do not hold the queue), so jQuery and its plugins keep
 *   their order.
 * - DOMContentLoaded / load listeners added while replaying are collected and
 *   fired once everything ran, so jQuery(window).on('load') and friends work.
 * - A click made before the scripts are ready is held and replayed afterwards,
 *   so the first tap on the mobile menu is not lost.
 */
(function () {
	var EVENTS = ['keydown', 'mousedown', 'mousemove', 'touchstart', 'touchmove', 'touchend', 'wheel'];
	var started = false;
	var done = false;
	var heldClicks = [];
	var root = document.documentElement;

	// Content that its own script would reveal stays visible until that script
	// runs — otherwise the page is blank until the first touch:
	// - AOS entrance animations ([data-aos] starts at opacity 0);
	// - the hero slider's mobile "loading" state, which hides the whole slider.
	//   Its first slide is server-rendered as the active one, so showing it
	//   early is safe (and the plugin does not always clear that state).
	root.classList.add('ipo-js-wait');
	var waitCss = document.createElement('style');
	waitCss.textContent =
		'html.ipo-js-wait [data-aos]{opacity:1!important;transform:none!important;transition:none!important}' +
		'.ipo-hero-slider-shell--loading .ipo-hero-slider{opacity:1!important;visibility:visible!important;transition:none!important}';
	document.head.appendChild(waitCss);

	// Hand the content back to AOS once it runs: whatever is on or above the
	// screen keeps its "animated" state (no flicker), the rest animates in on
	// scroll as before. If AOS never starts, everything simply stays visible.
	function unwait() {
		if (!window.AOS) return;
		Array.prototype.forEach.call(document.querySelectorAll('[data-aos]'), function (el) {
			if (el.getBoundingClientRect().top < window.innerHeight) el.classList.add('aos-init', 'aos-animate');
		});
		root.classList.remove('ipo-js-wait');
	}

	function start() {
		if (started) return;
		started = true;
		EVENTS.forEach(function (t) { window.removeEventListener(t, start, { passive: true }); });
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', run);
		} else {
			run();
		}
		// Never hold clicks forever if a script stalls.
		setTimeout(release, 10000);
	}

	EVENTS.forEach(function (t) { window.addEventListener(t, start, { passive: true }); });

	window.addEventListener('click', function (e) {
		if (done || !e.isTrusted) return;
		e.preventDefault();
		e.stopImmediatePropagation();
		heldClicks.push(e.target);
		start();
	}, true);

	function release() {
		if (done) return;
		done = true;
		setTimeout(unwait, 300);
		setTimeout(function () {
			heldClicks.forEach(function (el) {
				if (el && document.contains(el) && typeof el.click === 'function') el.click();
			});
			heldClicks = [];
		}, 100);
	}

	function run() {
		var scripts = Array.prototype.slice.call(document.querySelectorAll('script[type="ipo/delay"]'));

		scripts.forEach(function (s) {
			var src = s.getAttribute('data-ipo-src');
			if (!src) return;
			var link = document.createElement('link');
			link.rel = 'preload';
			link.as = 'script';
			link.href = src;
			if (s.hasAttribute('crossorigin')) link.crossOrigin = s.getAttribute('crossorigin');
			document.head.appendChild(link);
		});

		var docAdd = document.addEventListener;
		var winAdd = window.addEventListener;
		var readyHandlers = [];
		var loadHandlers = [];
		var onloadBefore = window.onload;
		var docWrite = document.write;
		var docWriteln = document.writeln;
		var current = null;

		document.addEventListener = function (type, fn) {
			if (type === 'DOMContentLoaded') { readyHandlers.push([document, fn]); return; }
			return docAdd.apply(this, arguments);
		};
		window.addEventListener = function (type, fn) {
			if (type === 'load') { loadHandlers.push([window, fn]); return; }
			if (type === 'DOMContentLoaded') { readyHandlers.push([window, fn]); return; }
			return winAdd.apply(this, arguments);
		};
		document.write = document.writeln = function (html) {
			if (current) current.insertAdjacentHTML('beforebegin', html);
		};

		var i = 0;

		function next() {
			if (i >= scripts.length) { finish(); return; }

			var old = scripts[i++];
			var el = document.createElement('script');
			var type = old.getAttribute('data-ipo-type');
			var src = old.getAttribute('data-ipo-src');

			for (var a = 0; a < old.attributes.length; a++) {
				var name = old.attributes[a].name;
				if (name === 'type' || name === 'data-ipo-src' || name === 'data-ipo-type') continue;
				el.setAttribute(name, old.attributes[a].value);
			}
			if (type) el.type = type;
			current = el;

			if (src) {
				var hold = !old.hasAttribute('async') && type !== 'module';
				el.async = false;
				if (hold) el.onload = el.onerror = next;
				el.src = src;
				old.parentNode.replaceChild(el, old);
				if (!hold) next();
			} else {
				el.text = old.text;
				old.parentNode.replaceChild(el, old);
				next();
			}
		}

		function call(list, eventName) {
			var ev;
			try { ev = new Event(eventName); } catch (x) { ev = document.createEvent('Event'); ev.initEvent(eventName, false, false); }
			list.forEach(function (pair) {
				try {
					if (typeof pair[1] === 'function') pair[1].call(pair[0], ev);
					else if (pair[1] && typeof pair[1].handleEvent === 'function') pair[1].handleEvent(ev);
				} catch (err) {
					setTimeout(function () { throw err; });
				}
			});
		}

		function finish() {
			document.addEventListener = docAdd;
			window.addEventListener = winAdd;
			document.write = docWrite;
			document.writeln = docWriteln;
			current = null;

			call(readyHandlers, 'DOMContentLoaded');

			var fireLoad = function () {
				call(loadHandlers, 'load');
				if (window.onload && window.onload !== onloadBefore) {
					try { window.onload(new Event('load')); } catch (err) { setTimeout(function () { throw err; }); }
				}
				release();
			};

			if (document.readyState === 'complete') fireLoad();
			else winAdd.call(window, 'load', fireLoad);
		}

		next();
	}
})();
