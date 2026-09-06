@foreach($products as $product)
<div class="col-6 col-md-4">
    @include('themes.xylo.partials.product-card-b2b', ['product' => $product])
</div>
@endforeach
