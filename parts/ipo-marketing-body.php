<?php
/**
 * Body-level marketing / 3rd-party markup (printed via wp_footer, very late).
 *
 * Origin: WordPress admin code-manager snippets, de-duplicated:
 *   - #1 (ID: 63811) תוסף נגישות ווואצאפ
 *   - #2 (ID: 60841) הודעת POPUP ללקוחות שבחו"ל  (geo-IL block banner)
 *   - #3 (ID: 60331) header – contact join us       (web-view AT-Popup)
 *   - #4 (ID: 54590) whatsapp-button
 *
 * De-duplication notes:
 *   - WhatsApp button: only the COMPLETE version from #4 (with <img> + inline
 *     style) is kept; the broken empty <a> fragment from #1 is dropped.
 *   - andifn accessibility include appears in both #1 and #4 — included once.
 *   - The AT-Popup callback is RENAMED here to handleATPopupEvent_ipo to avoid
 *     colliding with the identically-named handleATPopupEvent already defined
 *     in includes/ipo-shortcodes.php.
 *
 * @package wpstack-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- WhatsApp floating button (origin snippet #4 / ID: 54590) -->
<a href="https://wa.me/972557000232" target="_blank" class="whatsapp-button">
  <img src="https://www.ipo.co.il/wp-content/uploads/2025/05/whatsapp-1.png" alt="WhatsApp" width="50" height="50" loading="lazy" decoding="async" />
</a>

<style>
  .whatsapp-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
	    border-radius: 100%;
    z-index: 9999;
    display: inline-block;
    width: 50px;
    height: 50px;
  }

  .whatsapp-button img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
</style>

<!-- Accessibility (andifn) include (origin snippets #1 / ID: 63811 and #4 / ID: 54590 — included once) -->
<script src="https://system.user-a.co.il/Customers/3748629/www_ipo_co_il/andifn1.js" id="andipath"></script>

<!-- Geo IL block banner (origin snippet #2 / ID: 60841) -->
<script>
(function () {
  const CONFIG = {
    allowedCountry: 'IL',
    storageKey: 'geo_il_block_banner_dismissed',
    messageHtml: `
      <div>We detected you're browsing outside Israel.</div>
      <div>For information security reasons, the ticketing system is blocked.</div>
      <div style="margin-top:8px">
        You are welcome to contact us at
        <a href="mailto:info@ipo.co.il" style="color:#fff; text-decoration:underline">info@ipo.co.il</a>
        <br>
        or via WhatsApp at
        <a href="https://wa.me/972557000232" target="_blank" rel="noopener" style="color:#fff; text-decoration:underline">+972-055-7000232</a>
        for assistance.
      </div>
    `,
    endpoints: [
      'https://api.country.is/',
      'https://ipapi.co/json/',
      'https://ipwho.is/'
    ],
    timeoutMs: 2500
  };

  if (localStorage.getItem(CONFIG.storageKey) === '1') return;

  const withTimeout = (p, ms) =>
    Promise.race([
      p,
      new Promise((_, rej) => setTimeout(() => rej(new Error('timeout')), ms))
    ]);

  const parseCountry = (url, data) => {
    if (!data || typeof data !== 'object') return null;
    if (url.includes('country.is')) return (data.country || '').toUpperCase() || null;
    if (url.includes('ipapi.co')) return (data.country || '').toUpperCase() || null;
    if (url.includes('ipwho.is')) return (data.country_code || '').toUpperCase() || null;
    return null;
  };

  const fetchCountry = async () => {
    for (const url of CONFIG.endpoints) {
      try {
        const res = await withTimeout(fetch(url, { cache: 'no-store' }), CONFIG.timeoutMs);
        if (!res.ok) continue;
        const data = await res.json();
        const country = parseCountry(url, data);
        if (country) return country;
      } catch (e) {}
    }
    return null;
  };

  const showBanner = () => {
    if (document.getElementById('geo-il-block-banner')) return;

    const banner = document.createElement('div');
    banner.id = 'geo-il-block-banner';
    banner.setAttribute('role', 'dialog');
    banner.setAttribute('aria-live', 'polite');
    banner.innerHTML = `
      <div style="
        position:fixed; left:16px; right:16px; bottom:16px; z-index:999999;
        background:rgba(0,0,0,0.75); color:#fff; border-radius:14px;
        padding:14px 44px 14px 16px; box-shadow:0 10px 30px rgba(0,0,0,0.35);
        font-family:'Simpler', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        line-height:1.35; font-size:14px;
      ">
        <button type="button" aria-label="Close" style="
          position:absolute; top:8px; right:10px;
          width:28px; height:28px; border:0; border-radius:10px;
          background:rgba(255,255,255,0.12); color:#fff; cursor:pointer;
          font-size:18px; line-height:28px; padding:0;
        ">×</button>
        <div style="text-align: center;">${CONFIG.messageHtml}</div>
      </div>
    `;

    banner.querySelector('button').addEventListener('click', () => {
      localStorage.setItem(CONFIG.storageKey, '1');
      banner.remove();
    });

    document.body.appendChild(banner);
  };

  const init = async () => {
    const country = await fetchCountry();
    if (!country) return;
    if (country !== CONFIG.allowedCountry) showBanner();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>

<!-- Web-view AT-Popup (origin snippet #3 / ID: 60331). Callback renamed to handleATPopupEvent_ipo to avoid collision with includes/ipo-shortcodes.php -->
<script type='text/javascript'>
	(function () {
		var _atpopq = window._atpopq || (window._atpopq = []);
		window._atpopobj = {};
		if (!_atpopq.loaded) {
			var v = Math.floor((Math.random() * 1000000) + 1);
			var atpopjs = document.createElement('script');
			atpopjs.type = 'text/javascript';
			atpopjs.async = true;
			atpopjs.src = '//cdn-media.web-view.net/popups/lib/v1/loader.min.js?v=' + v;
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(atpopjs, s);
			_atpopq.loaded = true;
		}
		_atpopq.push(['UserId', 'zw3jefdca8w2']);
		_atpopq.push(['PopupId', 'zfta3d2hua']);
		_atpopq.push(['IsraelCode', '104']);
		_atpopq.push(['CountryCode', '104']);
		_atpopq.push(['IsEmbed', true]);
		_atpopq.push(['IgnoreMainCss', true]);
		_atpopq.push(['OnEventCallback', 'handleATPopupEvent_ipo']);
	})();
</script>

<style>
	.signup-field {
		min-width: 100%;
	}
	</style>
<script type="text/javascript">
	//Sample event handler function
	function handleATPopupEvent_ipo(ev,args){
		switch(ev){
			case 'display':
				//Do this when the popup is displayed
				break;
			case 'close':
				//Do this when the popup gets closed by the user
				break;
			case 'submit':
				//Do this when popup gets submitted and the user doesn't get redirected to a URL
				break;
		}
	}
</script>
