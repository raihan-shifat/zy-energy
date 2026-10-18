/*
 * Enquiry / contact event tracking for Google Tag Manager or GA4.
 * Plain static file (no build step). Pushes events to window.dataLayer; nothing
 * leaves the browser unless analytics is configured AND the visitor accepted
 * cookies (see resources/views/themes/xylo/partials/analytics-head.blade.php).
 *
 * Events: whatsapp_click, email_click, phone_click, file_download, form_submit.
 * No personal data is sent: never the link URL (it can contain a phone number or
 * email), never form field values. Only event name, where the link sits on the
 * page (data-track-location), the page path, and for downloads the file name.
 */
(function () {
    'use strict';

    window.dataLayer = window.dataLayer || [];

    function track(name, params) {
        var data = { event: name };
        for (var key in (params || {})) {
            if (Object.prototype.hasOwnProperty.call(params, key)) data[key] = params[key];
        }
        window.dataLayer.push(data);

        // GA4-only setup (no GTM): send the event directly. With GTM, the container
        // reads the dataLayer event instead, so it is not sent twice.
        var cfg = window.zyAnalytics || {};
        if (!cfg.gtm && cfg.ga4 && typeof window.gtag === 'function') {
            window.gtag('event', name, params || {});
        }
    }

    // Used by the enquiry forms after a successful submit.
    window.zyTrack = track;

    function detectType(href) {
        if (/^mailto:/i.test(href)) return 'email_click';
        if (/^tel:/i.test(href)) return 'phone_click';
        if (/^whatsapp:/i.test(href) || /^(https?:)?\/\/(wa\.me|api\.whatsapp\.com|web\.whatsapp\.com)\//i.test(href)) return 'whatsapp_click';
        if (/\.(pdf|docx?|xlsx?|pptx?|zip)([?#]|$)/i.test(href)) return 'file_download';
        return null;
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('a[href]') : null;
        if (!link) return;

        var href = link.getAttribute('href') || '';
        // data-track on the element wins; otherwise detect from the link type.
        var type = link.getAttribute('data-track') || detectType(href);
        if (!type) return;

        var params = {
            link_location: link.getAttribute('data-track-location') || 'other',
            page_path: window.location.pathname
        };

        if (type === 'file_download') {
            var file = href.split(/[?#]/)[0].split('/').pop() || '';
            params.file_name = file;
            params.file_extension = (file.indexOf('.') > -1 ? file.split('.').pop() : '').toLowerCase();
        }

        track(type, params);
    }, true);
})();
