{{-- Reusable B2B product card: image, name, short description, Enquiry + View Details (no price) --}}
@php
    $cardName = localized_translation_value($product->translations, 'name', __('store.category.product_name_not_available'));
    $cardDesc = localized_translation_value($product->translations, 'short_description', '');
    $cardImage = optional($product->thumbnail)->image_url ?? 'default.jpg';
    // Use responsive image: WebP with srcset, JPEG fallback, lazy loading
    $cardImageHtml = getResponsiveImageHtml($cardImage, [
        'alt' => $cardName,
        'class' => 'img-fluid',
        'sizes' => '(max-width: 480px) 100vw, (max-width: 768px) 50vw, (max-width: 1200px) 33vw, 25vw',
        'loading' => 'lazy',
    ]);
@endphp
<div class="product-card">
    <div class="product-img">
        <a href="{{ route('product.show', $product->slug) }}">
            {!! $cardImageHtml !!}
        </a>
    </div>
    <div class="product-info mt-4">
        <h3>
            <a href="{{ route('product.show', $product->slug) }}" class="product-title">
                {{ $cardName }}
            </a>
        </h3>
        @if($cardDesc)
            <p class="text-muted small">{{ \Illuminate\Support\Str::limit($cardDesc, 80) }}</p>
        @endif
        <div class="d-flex gap-2 mt-2">
            <a href="{{ route('product.show', $product->slug) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-regular fa-eye"></i> {{ __('store.category.view_details') }}
            </a>
            <button type="button" class="btn btn-primary btn-sm" onclick="openEnquiryModal({{ $product->id }}, '{{ addslashes($cardName) }}')">
                <i class="fa-regular fa-envelope"></i> {{ __('store.category.enquiry') }}
            </button>
        </div>
    </div>
</div>
