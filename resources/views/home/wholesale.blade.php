@extends('home.layout.app')
@section('title', 'Dessert Wholesale')
@section('content')
@include('home.partials.page-hero', ['title' => (optional($content)->title ?: 'Dessert Wholesale')])
<div class="container py-5">
    <h1 class="mb-3">{{ optional($content)->title ?: 'Dessert Wholesale' }}</h1>
    <p>{{ optional($content)->description ?: 'Choose your preferred wholesale delivery date. Deliveries are available Monday, Thursday and Saturday from 7 PM to 10 PM.' }}</p>
    @if(optional($content)->image)
        <img src="{{ asset($content->image) }}" class="img-fluid rounded mb-4" alt="Dessert Wholesale">
    @endif

    <div class="alert" style="background:#111;color:#fff;">
        Choose your preferred wholesale delivery date. Deliveries are available Monday, Thursday and Saturday from {{ $dates[0]['window'] ?? '7:00 PM – 10:00 PM' }}.
        Wholesale orders can be updated until 6 hours before delivery. Add trays here, then place the order from My Cart.
    </div>

    <form method="POST" action="{{ route('wholesale.date') }}" class="mb-4">
        @csrf
        <label class="form-label fw-bold">Select Delivery Date</label>
        <select name="wholesale_delivery_date" class="form-control" required>
            <option value="">Select an available date</option>
            @foreach($dates as $date)
                <option value="{{ $date['date'] }}" {{ session('wholesale_delivery_date') == $date['date'] ? 'selected' : '' }}>
                    {{ $date['label'] }} ({{ $date['window'] }})
                </option>
            @endforeach
        </select>
        <button class="btn sp-btn-pink mt-3">Save delivery date</button>
    </form>

    <div class="row g-4">
        @forelse($products as $product)
            @php
                $displayPrice = $product->resolvedDisplayPrice();
                $hasVariants = $product->variants && $product->variants->count() > 0;
            @endphp
            <div class="col-md-4">
                <div class="food-modal border rounded p-3 h-100 d-flex flex-column">
                    <img src="{{ asset($product->image ?? 'public/img/logo.png') }}" class="img-fluid mb-2" alt="{{ $product->name }}">
                    <h5>{{ $product->name }}</h5>
                    <p class="mb-2">
                        <strong>{{ $hasVariants ? 'From ' : '' }}£{{ number_format($displayPrice, 2) }}</strong>
                    </p>
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="branch_id" value="{{ optional($defaultBranch)->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <input type="radio" class="d-none" name="status_{{ $product->id }}" value="2" checked>
                    @if($hasVariants)
                        <select class="form-control bg-white mb-2 select-size" name="variant_id" style="appearance:auto">
                            @foreach($product->variants as $variant)
                                <option value="{{ $variant->id }} {{ number_format((float) ($variant->price > 0 ? $variant->price : ($variant->original_price ?? 0)), 2) }}"
                                    {{ $loop->first ? 'selected' : '' }}>
                                    {{ $variant->size }} – £{{ number_format((float) ($variant->price > 0 ? $variant->price : ($variant->original_price ?? 0)), 2) }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <button type="button" class="btn cz-add-btn addto-cart mt-auto" data-product-id="{{ $product->id }}" data-wholesale="1" data-add-label="Add to Cart">
                        Add to Cart
                    </button>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p>Wholesale products can be managed from Admin → Product by assigning them to the Dessert Wholesale menu.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
