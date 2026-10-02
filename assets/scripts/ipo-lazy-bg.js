/**
 * Lazy background images: elements whose inline background-image was moved to
 * data-ipo-bg (includes/ipo-delay-js.php) get it back when they come within
 * 300px of the viewport. Elements added later (slider clones, AJAX) are
 * picked up too. Printed inline before </body>; runs without waiting for the
 * delayed scripts.
 */
(function () {
	function show(el) {
		var bg = el.getAttribute('data-ipo-bg');
		if (!bg) return;
		el.style.backgroundImage = bg;
		el.removeAttribute('data-ipo-bg');
	}

	if (!('IntersectionObserver' in window)) {
		Array.prototype.forEach.call(document.querySelectorAll('[data-ipo-bg]'), show);
		return;
	}

	var io = new IntersectionObserver(function (entries) {
		entries.forEach(function (e) {
			if (e.isIntersecting) {
				io.unobserve(e.target);
				show(e.target);
			}
		});
	}, { rootMargin: '300px 0px 300px 0px' });

	function watch(root) {
		if (root.nodeType !== 1) return;
		if (root.hasAttribute('data-ipo-bg')) io.observe(root);
		Array.prototype.forEach.call(root.querySelectorAll('[data-ipo-bg]'), function (el) { io.observe(el); });
	}

	watch(document.body);

	if ('MutationObserver' in window) {
		new MutationObserver(function (records) {
			records.forEach(function (r) {
				Array.prototype.forEach.call(r.addedNodes, watch);
			});
		}).observe(document.body, { childList: true, subtree: true });
	}
})();
