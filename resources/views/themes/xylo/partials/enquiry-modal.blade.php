{{-- Reusable B2B Enquiry / RFQ form modal --}}
@php
    $enquiryCategories = getActiveCategories();
@endphp
<div class="modal fade" id="enquiryModal" tabindex="-1" aria-labelledby="enquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enquiryModalLabel">{{ __('store.enquiry.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="enquiry-form" action="{{ url('/api/enquiries') }}" method="POST" novalidate>
                @csrf
                <div class="modal-body">
                    <p class="text-muted">{{ __('store.enquiry.subtitle') }}</p>
                    <div id="enquiry-form-alert" class="d-none alert alert-danger py-2"></div>
                    <div id="enquiry-form-success" class="d-none alert alert-success py-2"></div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('store.enquiry.name') }} *</label>
                            <input type="text" name="name" id="enquiry-name" class="form-control" required>
                            <div class="invalid-feedback">{{ __('store.enquiry.validation_name') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('store.enquiry.company') }}</label>
                            <input type="text" name="company" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('store.enquiry.email') }}</label>
                            <input type="email" name="email" id="enquiry-email" class="form-control">
                            <div class="invalid-feedback">{{ __('store.enquiry.validation_email') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('store.enquiry.phone') }}</label>
                            <input type="tel" name="whatsapp_number" id="enquiry-phone" class="form-control" placeholder="1712 345 678">
                            {{-- Country code derived from the selected flag/dial code (intl-tel-input) --}}
                            <input type="hidden" name="country_code" id="enquiry-country-code">
                            <div class="invalid-feedback">{{ __('store.enquiry.validation_phone') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('store.enquiry.category') }}</label>
                            <select name="category" id="enquiry-category" class="form-select">
                                <option value="">{{ __('store.enquiry.select_category') }}</option>
                                @foreach ($enquiryCategories as $enquiryCategory)
                                    @php
                                        $catLabel = localized_translation_value($enquiryCategory->translations, 'name', __('store.enquiry.category') . ' #' . $enquiryCategory->id);
                                    @endphp
                                    <option value="{{ $enquiryCategory->id }}">{{ $catLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">{{ __('store.enquiry.message') }} *</label>
                            <textarea name="message" id="enquiry-message" rows="4" class="form-control" placeholder="{{ __('store.enquiry.placeholder_message') }}" required></textarea>
                            <div class="invalid-feedback">{{ __('store.enquiry.validation_message') }}</div>
                        </div>
                        <input type="hidden" name="source_page" id="enquiry-source-page">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('store.enquiry.close') }}</button>
                    <button type="submit" id="enquiry-submit-btn" class="btn btn-primary">{{ __('store.enquiry.send') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- intl-tel-input (standard international phone input: flag + dial code dropdown) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/css/intlTelInput.min.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/js/intlTelInput.min.js"></script>
<script>
    (function () {
        var phoneInput = document.getElementById('enquiry-phone');
        if (!phoneInput || typeof window.intlTelInput !== 'function') {
            // intl-tel-input not available - mark as unavailable and continue
            window.enquiryPhoneiti = null;
            return;
        }

        // Single shared instance for the modal
        window.enquiryPhoneiti = intlTelInput(phoneInput, {
            initialCountry: 'cn',           // company is China-based; user can pick any country flag
            separateDialCode: true,         // shows "+86" beside the flag
            preferredCountries: ['cn', 'bd', 'in', 'us', 'ae'],
            utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/js/utils.js',
        });

        /**
         * Sync the selected country code + full E.164 number into the form before submit.
         * Country code is the dial code (e.g. +880) which the backend resolves to country name.
         * Never throws — a failed/unloaded library must not block submission.
         */
        window.syncEnquiryPhone = function () {
            var countryHidden = document.getElementById('enquiry-country-code');
            if (!countryHidden) return;

            try {
                if (!window.enquiryPhoneiti) {
                    return; // intl-tel-input not available
                }

                var selected = window.enquiryPhoneiti.getSelectedCountryData();
                if (selected && selected.dialCode) {
                    countryHidden.value = '+' + selected.dialCode;
                } else {
                    countryHidden.value = '';
                }

                // Store the number in international format when present
                var raw = phoneInput.value.trim();
                if (raw === '') {
                    phoneInput.value = '';
                } else if (window.enquiryPhoneiti.isValidNumber()) {
                    phoneInput.value = window.enquiryPhoneiti.getNumber(); // E.164, e.g. +8801712345678
                }
            } catch (e) {
                // utils.js not loaded yet or library error — leave raw value intact
                console.warn('intl-tel-input unavailable:', e);
            }
        };
    })();
</script>

<script>
    /**
     * Open the enquiry modal.
     * @param {number|null} productId  preselect a product (used from product pages)
     * @param {string|null} productName product name to embed
     */
    window.openEnquiryModal = function (productId, productName) {
        // Product/category dropdown was replaced by a Category select —
        // keep the signature for backwards compatibility with product cards.
        var messageInput = document.getElementById('enquiry-message');
        if (productName && messageInput && !messageInput.value.trim()) {
            messageInput.value = 'Product of interest: ' + productName;
        }
        // Reset feedback from previous attempts
        var form = document.getElementById('enquiry-form');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        if (productName && messageInput) {
            messageInput.value = 'Product of interest: ' + productName;
        }
        document.getElementById('enquiry-form-alert').classList.add('d-none');
        document.getElementById('enquiry-form-success').classList.add('d-none');
        var sourcePageInput = document.getElementById('enquiry-source-page');
        if (sourcePageInput) {
            sourcePageInput.value = window.location.href;
        }
        var modal = new bootstrap.Modal(document.getElementById('enquiryModal'));
        modal.show();
    };
</script>

<script>
    (function () {
        function showAlert(message) {
            var alert = document.getElementById('enquiry-form-alert');
            alert.textContent = message;
            alert.classList.remove('d-none');
            document.getElementById('enquiry-form-success').classList.add('d-none');
        }

        function showSuccess(message) {
            var success = document.getElementById('enquiry-form-success');
            success.textContent = message;
            success.classList.remove('d-none');
            document.getElementById('enquiry-form-alert').classList.add('d-none');
        }

        // Initialize form handler - runs immediately if DOM is ready, otherwise waits for DOMContentLoaded
        function initEnquiryForm() {
            var sourcePageInput = document.getElementById('enquiry-source-page');
            if (sourcePageInput) {
                sourcePageInput.value = window.location.href;
            }

            var productSelect = document.getElementById('enquiry-product');
            if (productSelect) {
                productSelect.addEventListener('change', function () {
                    var nameInput = document.getElementById('enquiry-product-name');
                    var selectedText = productSelect.options[productSelect.selectedIndex] ? productSelect.options[productSelect.selectedIndex].text : '';
                    nameInput.value = productSelect.value ? selectedText : '';
                });
            }

            var form = document.getElementById('enquiry-form');
            var submitBtn = document.getElementById('enquiry-submit-btn');
            if (!form || !submitBtn) {
                return;
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // Only Name and Message are mandatory; Email/Company/WhatsApp/Category optional
                var name = document.getElementById('enquiry-name');
                var message = document.getElementById('enquiry-message');
                var phone = document.getElementById('enquiry-phone');
                var valid = true;

                form.classList.remove('was-validated');
                [name, message].forEach(function (el) {
                    el.classList.remove('is-invalid');
                });

                if (!name.value.trim()) {
                    name.classList.add('is-invalid');
                    valid = false;
                }
                if (!message.value.trim()) {
                    message.classList.add('is-invalid');
                    valid = false;
                }
                // If a WhatsApp number was typed, it must be valid for the selected country
                if (phone && phone.value.trim() !== '' && window.enquiryPhoneiti) {
                    phone.classList.remove('is-invalid');
                    window.syncEnquiryPhone();
                    var numberValid = true;
                    try {
                        // isValidNumber() THROWS while utils.js is still loading — must not kill submission
                        numberValid = window.enquiryPhoneiti.isValidNumber();
                    } catch (e) {
                        console.warn('intl-tel-input utils not ready:', e);
                        numberValid = true; // unknown validity: let it through rather than block
                    }
                    if (!numberValid) {
                        phone.classList.add('is-invalid');
                        valid = false;
                    }
                } else if (typeof window.syncEnquiryPhone === 'function') {
                    // Still derive the country from the flag even when no number is typed
                    window.syncEnquiryPhone();
                }

                if (!valid) {
                    form.classList.add('was-validated');
                    return;
                }

                // Disable button while submitting
                var originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = @json(__('store.enquiry.sending'));

                var data = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: data
                })
                .then(function (res) {
                    return res.json().then(function (body) {
                        return { status: res.status, body: body };
                    });
                })
                .then(function (result) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    if (result.status >= 200 && result.status < 300 && result.body.success) {
                        showSuccess(result.body.message || @json(__('store.enquiry.success_message')));
                        // Close the modal shortly after showing success
                        setTimeout(function () {
                            var modalEl = document.getElementById('enquiryModal');
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) { modal.hide(); }
                            form.reset();
                            document.getElementById('enquiry-form-success').classList.add('d-none');
                        }, 1200);
                    } else {
                        var errMsg = result.body.errors
                            ? Object.values(result.body.errors).map(function (arr) { return arr[0]; }).join(' ')
                            : (result.body.message || @json(__('store.product_detail.something_wrong')));
                        // Inline field errors
                        if (result.body.errors) {
                            Object.keys(result.body.errors).forEach(function (field) {
                                var input = form.querySelector('[name="' + field + '"]');
                                if (input) {
                                    input.classList.add('is-invalid');
                                }
                            });
                        }
                        showAlert(errMsg);
                    }
                })
                .catch(function () {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    showAlert(@json(__('store.product_detail.something_wrong')));
                });
            });
        }

        // Initialize immediately if DOM is ready, otherwise wait for DOMContentLoaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initEnquiryForm);
        } else {
            initEnquiryForm();
        }
    })();
</script>
