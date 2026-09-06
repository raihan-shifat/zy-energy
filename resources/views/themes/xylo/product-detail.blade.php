@extends('themes.xylo.layouts.master')

@section('css')
    @vite(['resources/views/themes/xylo/css/slick.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.css">
@endsection

@section('js')
    @vite(['resources/views/themes/xylo/js/slick.min.js'])
@endsection

@section('content')
<section class="breadcrumb-section">
    <div class="container">
        <div class="breadcrumbs" aria-label="breadcrumb">
            <a href="{{ url('/') }}">{{ __('store.product_detail.home') }}</a>
            <i class="fa fa-angle-right"></i>
            @foreach($breadcrumbs as $category)
                <a href="{{ url('category/' . $category->slug) }}">
                    {{ $category->translation->name ?? $category->slug }}
                </a>
                <i class="fa fa-angle-right"></i>
            @endforeach
            <span>{{ localized_translation_value($product->translations, 'name', '') }}</span>
        </div>
    </div>
</section>
<div class="main-detail pt-5 pb-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6 position-relative">
                <div class="product-slider">
                    @forelse ($product->images as $image)
                        <div>
                            {!! getResponsiveImageHtml($image['image_url'], [
                                'alt' => $image['name'] ?? '',
                                'class' => 'img-fluid',
                                'sizes' => '(max-width: 768px) 100vw, 50vw',
                                'loading' => 'lazy',
                            ]) !!}
                        </div>
                    @empty
                        <div>
                            <img src="{{ Storage::url('default.jpg') }}" alt="{{ __('store.category.product_name_not_available') }}" style="width: 100%; height: auto;" loading="lazy" />
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="col-md-6 pro-textarea">
                @php
                    $productName = localized_translation_value($product->translations, 'name', __('store.category.product_name_not_available'));
                    $productShortDesc = localized_translation_value($product->translations, 'short_description', '');
                @endphp
                <h1 class="sec-heading mb-3">{{ $productName }}</h1>

                @if($productShortDesc)
                    <p>{{ $productShortDesc }}</p>
                @endif

                {{-- B2B: price hidden (quote-based) --}}
                <div class="alert alert-info d-flex align-items-center gap-2 mt-3">
                    <i class="fa-regular fa-envelope"></i>
                    <span>{{ __('store.product_detail.quote_text') }}</span>
                </div>

                {{-- Tech-spec table from product attributes --}}
                @php
                    $groupedAttributes = $product->attributeValues->groupBy(fn($item) => $item->attribute->id);
                @endphp
                @if($groupedAttributes->count())
                <div class="spec-table mt-4">
                    <h3 class="mb-3">{{ __('store.product_detail.specifications') }}</h3>
                    <table class="table table-bordered">
                        <tbody>
                            @foreach ($groupedAttributes as $attributeId => $values)
                                <tr>
                                    <th style="width:40%; background:#f8f9fa;">
                                        {{ $values->first()->attribute->name }}
                                    </th>
                                    <td>
                                        {{ $values->pluck('translated_value')->implode(', ') ?: $values->pluck('value')->implode(', ') }}
                                    </td>
                                </tr>
                            @endforeach
                            @if ($product->series)
                                <tr>
                                    <th style="width:40%; background:#f8f9fa;">{{ __('store.category.series') }}</th>
                                    <td>{{ $product->series }}</td>
                                </tr>
                            @endif
                            @if ($product->type)
                                <tr>
                                    <th style="width:40%; background:#f8f9fa;">{{ __('store.category.type') }}</th>
                                    <td>{{ $product->type }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Enquiry button --}}
                <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
                    <button type="button" class="btn btn-primary btn-lg" onclick="openEnquiryModal({{ $product->id }}, '{{ addslashes($productName) }}')">
                        <i class="fa-regular fa-envelope"></i> {{ __('store.home_b2b.send_enquiry') }}
                    </button>

                    {{-- Contact Us popup (WhatsApp / WeChat QR / Email / Phone) — from Site Settings --}}
                    @include('themes.xylo.partials.contact-us', ['contactProductName' => $productName])

                    {{-- B2B: older inline quick-contact icon row replaced by the Contact Us popup --}}
                    {{-- @include('themes.xylo.partials.quick-contact', ['contactProductName' => $productName]) --}}
                </div>

                {{-- B2B: old wishlist heart, price, attributes, qty + cart commented out --}}
                {{--
                @auth('customer')
                @php
                    $isFavorite = auth('customer')->user()
                        ->wishlistProducts()
                        ->where('product_id', $product->id)
                        ->exists();
                @endphp
                @else
                    @php
                        $isFavorite = false;
                    @endphp
                @endauth

                <button id="test-heart" class="border-0 bg-transparent">
                    <i class="{{ $isFavorite ? 'fa-solid fa-heart text-danger' : 'fa-regular fa-heart text-secondary' }} fs-4"></i>
                </button>

                <h2><span id="currency-symbol">{{ $currency->symbol }}</span><span  id="variant-price" >{{ $product->primaryVariant->converted_price ?? 'N/A' }}</span></h2>

                <div id="product-attributes" class="product-options">
                    @php
                        $groupedAttributes = $product->attributeValues->groupBy(fn($item) => $item->attribute->id);
                    @endphp

                    @foreach ($groupedAttributes as $attributeId => $values)
                        <div class="attribute-options mt-3">
                            <h3>{{ __('store.product_detail.' . strtolower($values->first()->attribute->name)) }}</h3>
                            <div class="{{ strtolower($values->first()->attribute->name) }}-wrapper">
                                @foreach ($values as $index => $value)
                                    @php
                                        $inputId = strtolower($values->first()->attribute->name) . '-' . $index;
                                    @endphp
                                    <input type="radio" name="attribute_{{ $attributeId }}" id="{{ $inputId }}" value="{{ $value->id }}" {{ $index === 0 ? 'checked' : '' }}>
                                   <label for="{{ $inputId }}" class="{{ strtolower($values->first()->attribute->name) === 'color' ? 'color-circle' : 'size-box' }}" style="{{ strtolower($values->first()->attribute->name) === 'color' ? 'background-color:' . strtolower($value->value) . ';' : '' }}">
                                    @if(strtolower($values->first()->attribute->name) === 'size')
                                        {{ $value->translated_value }}
                                    @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="cart-actions mt-3 d-flex">
                    <div class="quantity me-4">
                        <button onclick="changeQty(-1)">-</button>
                        <input type="text" id="qty" value="1">
                        <button onclick="changeQty(1)">+</button>
                    </div>
                    <button class="add-to-cart read-more" onclick="addToCart({{ $product->id }}, '{{ $product->product_type }}')">{{ __('store.product_detail.add_to_cart') }}</button>
                </div>
                --}}
            </div>
        </div>
    </div>
</div>
<div class="reviewbox py-5">
  <div class="container">
    <div class="row">
      <div class="col-12">

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description"
                    type="button" role="tab" aria-controls="description" aria-selected="true">{{ __('store.product_detail.description') }}</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews"
                    type="button" role="tab" aria-controls="reviews" aria-selected="false">{{ __('store.product_detail.reviews') }} ({{ $product->reviews_count }})</button>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content pt-3" id="myTabContent">
          <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
            {!! localized_translation_value($product->translations, 'description', '') !!}
          </div>
          <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
           <div class="product-detail-customer-review">

                @auth('customer')
                <div class="mt-4 mb-4">
                    <h5>{{ __('store.product_detail.submit_review_title') }}</h5>

                    <form action="{{ route('review.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="rating" id="rating-value" required>

                        <div id="starWrapper" style="font-size: 1.5rem; line-height: 1; display: inline-block;">
                            <span class="star" data-value="1" style="color:#ccc; cursor:pointer;">★</span>
                            <span class="star" data-value="2" style="color:#ccc; cursor:pointer;">★</span>
                            <span class="star" data-value="3" style="color:#ccc; cursor:pointer;">★</span>
                            <span class="star" data-value="4" style="color:#ccc; cursor:pointer;">★</span>
                            <span class="star" data-value="5" style="color:#ccc; cursor:pointer;">★</span>
                        </div>

                        <div class="mb-3 mt-3">
                            <label>{{ __('store.product_detail.review_optional') }}</label>
                            <textarea name="review" class="form-control" rows="3"></textarea>
                        </div>

                        <button class="btn btn-primary">{{ __('store.product_detail.submit_review_btn') }}</button>
                    </form>
                </div>

                @else
                <p class="mt-3">{{ __('store.product_detail.please') }} <a href="{{ route('customer.login') }}">{{ __('store.product_detail.login') }}</a> {{ __('store.product_detail.submit') }}</p>
                @endauth

                @if($product->reviews->isEmpty())
                    <p>{{ __('store.product_detail.no_reviews_yet') }}</p>
                @else
                    <ul>
                        @foreach($product->reviews as $review)
                            @if($review->is_approved)
                                <li>
                                    <div class="review-customer-info">
                                        <img src="{{ $review->customer->profile_image
                                                ? asset('storage/' . $review->customer->profile_image)
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($review->customer->name) . '&background=0D8ABC&color=fff&size=70' }}"
                                            alt="{{ $review->customer->name }}"
                                            class="review-customer-avatar"
                                            style="width:45px; height:45px; border-radius:50%; object-fit:cover;"/>
                                        <strong>{{ ucwords($review->customer->name) }}</strong>
                                    </div>

                                   <div class="review-rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span style="color: {{ $i <= $review->rating ? 'gold' : '#ccc' }}">&#9733;</span>
                                        @endfor
                                        <span class="review-time">
                                            @php
                                                $created_at = \Carbon\Carbon::parse($review->created_at);
                                                $diffInDays = $created_at->diffInDays(\Carbon\Carbon::now());
                                            @endphp
                                            ({{ $diffInDays }} {{ $diffInDays == 1 ? __('store.product_detail.day') : __('store.product_detail.days')  }} {{ __('store.product_detail.ago') }})
                                        </span>
                                    </div>

                                    @if($review->review)
                                        <p>{{ $review->review }}</p>
                                    @else
                                        <p>{{ __('store.product_detail.no_review_text') }}</p>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>

                        <div class="average-rating">
                            <div class="review-rating">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($product->reviews_avg_rating))
                                        <span style="color: gold">★</span>
                                    @elseif($i == ceil($product->reviews_avg_rating) && ($product->reviews_avg_rating - floor($product->reviews_avg_rating)) >= 0.5)
                                        <span style="color: gold">★</span>
                                    @else
                                        <span style="color: #ccc">★</span>
                                    @endif
                                @endfor
                                {{ number_format($product->reviews_avg_rating, 1) }} <span>{{ __('store.product_detail.average_rating') }}</span>
                            </div>
                        </div>
                @endif
                </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const stars = document.querySelectorAll('#starWrapper .star');
        const ratingInput = document.getElementById('rating-value');

        stars.forEach(star => {
            star.addEventListener('mouseover', function () {
                const val = parseInt(this.dataset.value);
                stars.forEach(s => {
                    s.style.color = (parseInt(s.dataset.value) <= val) ? 'gold' : '#ccc';
                });
            });

            star.addEventListener('mouseout', function () {
                const currentRating = parseInt(ratingInput.value) || 0;
                stars.forEach(s => {
                    s.style.color = (parseInt(s.dataset.value) <= currentRating) ? 'gold' : '#ccc';
                });
            });

            star.addEventListener('click', function () {
                const val = parseInt(this.dataset.value);
                ratingInput.value = val;
                stars.forEach(s => {
                    s.style.color = (parseInt(s.dataset.value) <= val) ? 'gold' : '#ccc';
                });
            });
        });
    });
    </script>

    <script>
    @if(Session::has('success'))
        toastr.success("{{ session('success') }}");
    @endif

    @if(Session::has('error'))
        toastr.error("{{ session('error') }}");
    @endif
    </script>

    <script>
        $(document).ready(function() {
            $('.product-slider').slick({
                arrows: true,
                dots: false,
                infinite: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                prevArrow: '<button type="button" class="slick-prev">←</button>',
                nextArrow: '<button type="button" class="slick-next">→</button>',
            });
        });
    </script>
@endsection
