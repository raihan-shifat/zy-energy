{{--
    Analytics bootstrap. Outputs nothing unless a GTM or GA4 ID is configured (config/seo.php).
    Respects the cookie choice: no Google script is requested and no analytics cookie is set
    until the visitor accepts the cookie notice (choice kept in localStorage).
    Custom events are pushed to window.dataLayer by public/js/tracking.js.
--}}
@php $analytics = analytics_config(); @endphp
@if ($analytics['enabled'])
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){ dataLayer.push(arguments); }
    // Consent Mode v2: everything denied until the visitor accepts.
    gtag('consent', 'default', {
        analytics_storage: 'denied',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied'
    });
    window.zyAnalytics = {
        gtm: @json($analytics['gtm']),
        ga4: @json($analytics['ga4']),
        consentKey: 'zy_cookie_consent'
    };
    (function () {
        var cfg = window.zyAnalytics;
        var loaded = false;
        function addScript(src) {
            var s = document.createElement('script');
            s.async = true;
            s.src = src;
            document.head.appendChild(s);
        }
        // Called on page load if the visitor already accepted, or when they click Accept.
        cfg.load = function () {
            if (loaded) return;
            loaded = true;
            gtag('consent', 'update', { analytics_storage: 'granted' });
            if (cfg.gtm) {
                dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                addScript('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(cfg.gtm));
            } else if (cfg.ga4) {
                addScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(cfg.ga4));
                gtag('js', new Date());
                gtag('config', cfg.ga4);
            }
        };
        var choice = null;
        try { choice = localStorage.getItem(cfg.consentKey); } catch (e) {}
        if (choice === 'accepted') cfg.load();
    })();
</script>
@endif
