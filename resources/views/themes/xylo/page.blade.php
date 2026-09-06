@extends('themes.xylo.layouts.master')
@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1 class="mb-4">{{ $translation->title }}</h1>
                <div class="page-content">
                    {!! $translation->content !!}
                </div>
            </div>
        </div>

        @if ($page->slug === 'contact')
            <div class="row justify-content-center mt-5">
                <div class="col-md-10">
                    <h2 class="sec-heading mb-4">{{ __('store.enquiry.title') }}</h2>
                    <p class="text-muted mb-4">{{ __('store.enquiry.subtitle') }}</p>

                    @php
                        $contactProducts = \App\Models\Category::with('translation')
                            ->where('status', 1)
                            ->get();
                    @endphp
                    <form id="contact-enquiry-form" action="{{ route('enquiry.store') }}" method="POST" class="mb-5">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('store.enquiry.name') }} *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('store.enquiry.company') }}</label>
                                <input type="text" name="company" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('store.enquiry.email') }}</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('store.enquiry.phone') }}</label>
                                <input type="tel" name="whatsapp_number" id="contact-phone" class="form-control" placeholder="1712 345 678">
                                {{-- Country code derived from the selected flag/dial code (intl-tel-input) --}}
                                <input type="hidden" name="country_code" id="contact-country-code">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('store.enquiry.category') }}</label>
                                <select name="category" class="form-select">
                                    <option value="">{{ __('store.enquiry.select_category') }}</option>
                                    @foreach ($contactProducts as $contactCategory)
                                        @php
                                            $contactCatLabel = localized_translation_value($contactCategory->translations, 'name', __('store.enquiry.category') . ' #' . $contactCategory->id);
                                        @endphp
                                        <option value="{{ $contactCategory->id }}">{{ $contactCatLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('store.enquiry.message') }} *</label>
                                <textarea name="message" rows="4" class="form-control" placeholder="{{ __('store.enquiry.placeholder_message') }}" required></textarea>
                            </div>
                            <input type="hidden" name="source_page" id="contact-enquiry-source-page">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">{{ __('store.enquiry.send') }}</button>
                            </div>
                        </div>
                    </form>

                    <h2 class="sec-heading mb-4">{{ __('store.footer.contact') }}</h2>
                    @include('themes.xylo.partials.contact-us')
                    <div class="ratio ratio-16x9 mb-3 mt-3">
                        <iframe
                            src="https://www.google.com/maps/embed?pb="
                            width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('js')
@if ($page->slug === 'contact')
{{-- intl-tel-input (standard international phone input: flag + dial code dropdown) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/css/intlTelInput.min.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/js/intlTelInput.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sourcePageInput = document.getElementById('contact-enquiry-source-page');
    if (sourcePageInput) {
        sourcePageInput.value = window.location.href;
    }

    // International phone input with country-code picker
    var phoneInput = document.getElementById('contact-phone');
    var countryHidden = document.getElementById('contact-country-code');
    var iti = null;
    if (phoneInput && typeof window.intlTelInput === 'function') {
        iti = intlTelInput(phoneInput, {
            initialCountry: 'cn',
            separateDialCode: true,
            preferredCountries: ['cn', 'bd', 'in', 'us', 'ae'],
            utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/js/utils.js',
        });
    }

    function syncPhone() {
        if (!iti || !countryHidden) return;
        try {
            var selected = iti.getSelectedCountryData();
            countryHidden.value = selected && selected.dialCode ? '+' + selected.dialCode : '';
            var raw = phoneInput.value.trim();
            if (raw === '') {
                phoneInput.value = '';
            } else if (iti.isValidNumber()) {
                phoneInput.value = iti.getNumber(); // E.164, e.g. +8801712345678
            }
        } catch (e) {
            // utils.js not loaded yet or library error — leave raw value intact
            console.warn('intl-tel-input unavailable:', e);
        }
    }

    var form = document.getElementById('contact-enquiry-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Derive country from flag selection before submitting
            syncPhone();
            if (phoneInput && phoneInput.value.trim() !== '' && iti) {
                var numberValid = true;
                try {
                    // isValidNumber() THROWS while utils.js is still loading — must not block submission
                    var numberValid = iti.isValidNumber();
                } catch (e) {
                    console.warn('intl-tel-input utils not ready:', e);
                    numberValid = true; // unknown validity: let it through rather than block
                }
                if (!numberValid) {
                    toastr.error('Please enter a valid WhatsApp number.');
                    phoneInput.classList.add('is-invalid');
                    return;
                }
            }
            phoneInput && phoneInput.classList.remove('is-invalid');

            // Clear previous errors
            document.querySelectorAll('.is-invalid').forEach(function(el) { el.classList.remove('is-invalid'); });
            document.querySelectorAll('.invalid-feedback').forEach(function(el) { el.remove(); });

            var data = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: data
            })
            .then(function (res) {
                if (res.status === 422) {
                    return res.json().then(function(err) { throw err; });
                }
                if (!res.ok) { throw new Error('Request failed'); }
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    toastr.success(data.message);
                    form.reset();
                } else {
                    toastr.error(data.message || 'Something went wrong.');
                }
            })
            .catch(function (err) {
                if (err.errors) {
                    // Display validation errors
                    Object.keys(err.errors).forEach(function(field) {
                        var input = form.querySelector('[name="' + field + '"]');
                        if (input) {
                            input.classList.add('is-invalid');
                            var feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback d-block';
                            feedback.textContent = err.errors[field][0];
                            input.parentNode.appendChild(feedback);
                        }
                    });
                    toastr.error('Please fix the errors below.');
                } else {
                    toastr.error(err.message || 'Something went wrong. Please try again.');
                }
            });
        });
    }
});
</script>
@endif
@endsection
