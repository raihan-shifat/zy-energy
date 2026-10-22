<script>
    {{-- Excerpt character counter: shows usage vs the 500-char limit (the field's maxlength stops longer input). --}}
    document.addEventListener('DOMContentLoaded', function () {
        var EXCERPT_LIMIT = 500;
        document.querySelectorAll('textarea[name$="[excerpt]"]').forEach(function (ta) {
            var counter = document.createElement('small');
            counter.className = 'excerpt-counter d-block mt-1 text-muted';
            ta.insertAdjacentElement('afterend', counter);

            function update() {
                var len = ta.value.length;
                counter.textContent = len + ' / ' + EXCERPT_LIMIT + ' characters';
                counter.classList.toggle('text-danger', len >= EXCERPT_LIMIT);
            }
            ta.addEventListener('input', update);
            update();
        });

        // After a failed save, open the language tab that holds the first error.
        var firstError = document.querySelector('.tab-pane .is-invalid, .tab-pane .invalid-feedback');
        var pane = firstError && firstError.closest('.tab-pane');
        var trigger = pane && document.querySelector('[data-bs-target="#' + pane.id + '"]');
        if (trigger && window.bootstrap) {
            new bootstrap.Tab(trigger).show();
        }
    });
</script>
