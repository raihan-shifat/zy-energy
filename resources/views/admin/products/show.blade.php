@extends('admin.layouts.admin')
@section('content')

<div class="card mt-4">
    <div class="card-header card-header-bg text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{{ __('cms.products.title_show') }}</h6>
        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i> {{ __('cms.common.edit') }}</a>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <strong>{{ __('cms.products.id') }}:</strong> {{ $product->id }}
            </div>
            <div class="col-md-3">
                <strong>{{ __('cms.products.slug') }}:</strong> {{ $product->slug }}
            </div>
            <div class="col-md-3">
                <strong>{{ __('cms.products.status') }}:</strong>
                <span class="badge bg-{{ $product->status ? 'success' : 'secondary' }}">{{ $product->status ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="col-md-3">
                <strong>{{ __('cms.products.created_at') }}:</strong> {{ $product->created_at?->format('Y-m-d H:i') }}
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <strong>{{ __('cms.products.category') }}:</strong>
                {{ $product->category?->translation->name ?? 'N/A' }}
            </div>
            <div class="col-md-6">
                <strong>{{ __('cms.products.brand') }}:</strong>
                {{ $product->brand?->translation->name ?? 'N/A' }}
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <strong>{{ __('cms.products.translations') }}:</strong>
                <table class="table table-bordered mt-2">
                    <thead>
                        <tr>
                            <th>{{ __('cms.products.language') }}</th>
                            <th>{{ __('cms.products.name') }}</th>
                            <th>{{ __('cms.products.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->translations as $translation)
                            <tr>
                                <td>{{ $translation->language_code }}</td>
                                <td>{{ $translation->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($translation->description ?? '', 100) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <strong>{{ __('cms.products.variants') }}:</strong>
                <table class="table table-bordered mt-2">
                    <thead>
                        <tr>
                            <th>{{ __('cms.products.variant_name') }}</th>
                            <th>{{ __('cms.products.price') }}</th>
                            <th>{{ __('cms.products.discount_price') }}</th>
                            <th>{{ __('cms.products.stock') }}</th>
                            <th>{{ __('cms.products.is_primary') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->variants as $variant)
                            <tr>
                                <td>{{ $variant->translations->first()?->name ?? $variant->variant_slug }}</td>
                                <td>{{ $variant->price }}</td>
                                <td>{{ $variant->discount_price ?? 'N/A' }}</td>
                                <td>{{ $variant->stock }}</td>
                                <td>{{ $variant->is_primary ? 'Yes' : 'No' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ __('cms.products.no_variants') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <strong>{{ __('cms.products.images') }}:</strong>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @forelse($product->images as $image)
                        <img src="{{ asset('storage/' . $image->image_url) }}" alt="{{ $image->name }}" style="max-height: 100px; max-width: 100px; object-fit: cover;">
                    @empty
                        <span class="text-muted">{{ __('cms.products.no_images') }}</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection