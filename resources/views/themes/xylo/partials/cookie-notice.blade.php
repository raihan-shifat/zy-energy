{{--
    Cookie notice. Only rendered when analytics is configured (see partials/analytics-head).
    Does not block the page; the choice is kept in localStorage ("accepted" / "declined").
--}}
@if (analytics_config()['enabled'])
<div id="cookie-notice" class="d-none position-fixed bottom-0 start-0 end-0 bg-dark text-white shadow-lg"
     role="region" aria-label="{{ __('store.consent.title') }}" style="z-index: 100000;">
    <div class="container py-3 d-flex flex-wrap align-items-center gap-3">
        <p class="mb-0 flex-grow-1 small">
            {{ __('store.consent.text') }}
            <a href="/cookie-policy" class="text-white text-decoration-underline">{{ __('store.consent.more') }}</a>
        </p>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light btn-sm" data-consent="declined">{{ __('store.consent.decline') }}</button>
            <button type="button" class="btn btn-light btn-sm" data-consent="accepted">{{ __('store.consent.accept') }}</button>
        </div>
    </div>
</div>
<script>
    (function () {
        var cfg = window.zyAnalytics || {};
        var key = cfg.consentKey || 'zy_cookie_consent';
        var box = document.getElementById('cookie-notice');
        if (!box) return;
        var choice = null;
        try { choice = localStorage.getItem(key); } catch (e) {}
        // Show only if the visitor has not chosen yet.
        if (choice !== 'accepted' && choice !== 'declined') box.classList.remove('d-none');

        box.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-consent]');
            if (!btn) return;
            var value = btn.getAttribute('data-consent');
            try { localStorage.setItem(key, value); } catch (err) {}
            box.classList.add('d-none');
            if (value === 'accepted' && typeof cfg.load === 'function') {
                cfg.load();
            } else if (value === 'declined' && typeof window.gtag === 'function') {
                // Withdrawing consent after accepting: stop storage for the rest of this page view.
                window.gtag('consent', 'update', { analytics_storage: 'denied' });
            }
        });

        // "Change cookie choice" buttons (e.g. on the Cookie Policy page) reopen the notice.
        document.addEventListener('click', function (e) {
            if (!e.target.closest('[data-cookie-settings]')) return;
            e.preventDefault();
            try { localStorage.removeItem(key); } catch (err) {}
            box.classList.remove('d-none');
        });
    })();
</script>
@endif
