@extends('themes.xylo.layouts.master')

@section('content')
    @php $currency = activeCurrency(); @endphp
    <section class="banner-area inner-banner pt-5 animate__animated animate__fadeIn productinnerbanner">
        <div class="container h-100">
            <div class="row">
                <div class="col-md-4">
                    <div class="breadcrumbs">
                        <a href="{{ url('/') }}">{{ __('store.checkout.breadcrumb_home') }}</a> <i class="fa fa-angle-right"></i> {{ __('store.checkout.breadcrumb_checkout') }}
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="cart-page pb-5 pt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    <form id="checkout-form" method="POST" action="{{ route('checkout.process') }}">
                        @csrf

                        <!-- Shipping Information -->
                        <div class="shipping_info">
                            <h3 class="cart-heading">{{ __('store.checkout.shipping_information') }}</h3>
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <input type="text" name="first_name" class="form-control" placeholder="{{ __('store.checkout.first_name') }}" required>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <input type="text" name="last_name" class="form-control" placeholder="{{ __('store.checkout.last_name') }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mt-3">
                                    <input type="text" name="address" class="form-control" placeholder="{{ __('store.checkout.address') }}" required>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <input type="text" name="suite" class="form-control" placeholder="{{ __('store.checkout.suite') }}">
                                </div>
                                <div class="col-md-6 mt-3">
                                    <select name="country" class="form-select" required>
                                        <option value="">{{ __('store.checkout.select_country') }}</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="United States">United States</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="India">India</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="France">France</option>
                                        <option value="Japan">Japan</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <input type="text" name="city" class="form-control" placeholder="{{ __('store.checkout.city') }}" required>
                                </div>
                                <div class="col-md-3 mt-3">
                                    <select name="state" class="form-select" required>
                                        <option value="">{{ __('store.checkout.select_state') }}</option>
                                        <option value="Dhaka">Dhaka</option>
                                        <option value="Chattogram">Chattogram</option>
                                        <option value="Rajshahi">Rajshahi</option>
                                        <option value="Khulna">Khulna</option>
                                        <option value="Sylhet">Sylhet</option>
                                        <option value="Barishal">Barishal</option>
                                        <option value="Rangpur">Rangpur</option>
                                        <option value="Mymensingh">Mymensingh</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mt-3">
                                    <input type="text" name="zipcode" class="form-control" placeholder="{{ __('store.checkout.zipcode') }}" required>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label>
                                    <input type="checkbox" name="use_as_billing" value="1" checked>{{ __('store.checkout.use_as_billing') }}
                                </label>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="shipping_info">
                            <h3 class="cart-heading mt-5">{{ __('store.checkout.contact_information') }}</h3>
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <input type="email" name="email" class="form-control" placeholder="{{ __('store.checkout.email') }}" required>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <input type="text" name="phone" class="form-control" placeholder="{{ __('store.checkout.phone') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="shipping_info mt-5">
                            <h3 class="cart-heading">{{ __('store.checkout.payment_method') }}</h3>

                            @foreach($paymentGateways as $gateway)
                                <div class="form-check mt-2">
                                    <input type="radio" name="gateway" value="{{ $gateway->code }}" 
                                        id="gateway-{{ $gateway->id }}" required>
                                    <label for="gateway-{{ $gateway->id }}">{{ $gateway->name }}</label>
                                </div>

                                @if($gateway->code === 'paypal')
                                    <div id="paypal-button-container" class="mt-3" style="display: none;"></div>
                                @endif

                                @if($gateway->code === 'stripe')
                                    <div id="card-element" class="mt-3" style="display: none;"></div>
                                    <div id="card-errors" class="text-danger mt-2"></div>
                                @endif
                            @endforeach

                            <div id="payment-fields">
                                <!-- Stripe/PayPal fields injected with JS -->
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-4">
                            <button type="submit" id="place-order" class="btn btn-primary w-100">{{ __('store.checkout.place_order') }}</button>
                        </div>
                    </form>
                </div>

                <div class="col-md-5 mt-5 mt-md-0">
                    <div class="cart-box">
                        <h3 class="cart-heading">{{ __('store.checkout.order_summary') }}</h3>

                        <div class="row border-bottom pb-2 mb-2 mt-4">
                            <div class="col-6 col-md-4">{{ __('store.checkout.subtotal') }}</div>
                            <div class="col-6 col-md-8 text-end">{{ $currency->symbol }}{{ number_format($subtotal, 2) }}</div>
                        </div>
                        <div class="row border-bottom pb-2 mb-2">
                            <div class="col-4 col-md-4">{{ __('store.checkout.shipping') }}</div>
                            <div class="col-8 col-md-8 text-end"><small>{{ __('store.checkout.shipping_info') }}</small></div>
                        </div>
                        <div class="row border-bottom pb-2 mb-2">   
                            <div class="col-6 col-md-4">{{ __('store.checkout.total') }}</div>
                            <div class="col-6 col-md-8 text-end"><span>{{ $currency->symbol }}{{ number_format($total, 2) }}</span></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


@endsection

@section('js')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const gatewayRadios = document.querySelectorAll('input[name="gateway"]');
    const paypalContainer = document.getElementById("paypal-button-container");
    const stripeContainer = document.getElementById("card-element");
    const placeOrderBtn = document.getElementById("place-order");
    const form = document.getElementById("checkout-form");

    let stripe = null;
    let card = null;
    let paypalButtons = null;

    @if(!empty($stripePublicKey) && $stripePublicKey !== 'your-stripe-public-key')
        stripe = Stripe("{{ $stripePublicKey }}");
        let elements = stripe.elements();
        card = elements.create("card");
        card.mount("#card-element");
    @endif

    // Show correct payment fields
    gatewayRadios.forEach(radio => {
        radio.addEventListener("change", function () {
            if (this.value === "paypal") {
                paypalContainer.style.display = "block";
                stripeContainer.style.display = "none";
            } else if (this.value === "stripe") {
                stripeContainer.style.display = "block";
                paypalContainer.style.display = "none";
            } else {
                paypalContainer.style.display = "none";
                stripeContainer.style.display = "none";
            }
        });
    });

    @if(!empty($paypalClientId) && $paypalClientId !== 'your-paypal-client-id')
        if (typeof paypal !== "undefined") {
            paypal.Buttons({
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [{ amount: { value: "{{ number_format($total, 2, '.', '') }}" } }]
                    });
                },
                onApprove: function (data, actions) {
                    return actions.order.capture().then(function (details) {
                        fetch("{{ route('checkout.process') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Accept": "application/json",
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                gateway: "paypal",
                                order_id: data.orderID,
                                details: details
                            })
                        }).then(res => res.json()).then(result => {
                            window.location.href = result.redirect || "{{ route('thankyou') }}";
                        });
                    });
                }
            }).render("#paypal-button-container");
        }
    @endif

    // Form submit
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        let selectedGateway = document.querySelector('input[name="gateway"]:checked').value;

        if (selectedGateway === "stripe" && stripe && card) {
            const {paymentMethod, error} = await stripe.createPaymentMethod({
                type: "card",
                card: card,
            });

            if (error) {
                document.getElementById("card-errors").textContent = error.message;
            } else {
                let formData = new FormData(form);
                formData.append("payment_method_id", paymentMethod.id);

                fetch("{{ route('checkout.process') }}", {
                    method: "POST",
                    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json"},
                    body: formData
                }).then(res => res.json()).then(result => {
                    window.location.href = result.redirect || "{{ route('thankyou') }}";
                });
            }
        } else if (selectedGateway === "paypal") {
            alert("Please complete payment with PayPal button");
        } else {
            form.submit();
        }
    });
});
</script>
@endsection