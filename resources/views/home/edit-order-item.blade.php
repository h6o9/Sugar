@extends('home.layout.app')
@section('title', 'Update item')
@section('content')
@include('home.partials.page-hero', ['title' => 'Update item'])
@php
    $variants = $variants ?? collect();
    $toppingGroups = $toppingGroups ?? [];
    $selectedToppingIds = $selectedToppingIds ?? [];
    $currentSize = strtolower(trim((string) ($item->product_size ?? '')));
    $selectedVariantId = old('variant_id');
    if (!$selectedVariantId && $variants && count($variants)) {
        $match = collect($variants)->first(function ($v) use ($currentSize) {
            return strtolower(trim((string) $v->size)) === $currentSize;
        });
        $selectedVariantId = $match->id ?? collect($variants)->first()->id;
    }
@endphp
<div class="container py-4" style="max-width:640px">
    <div class="sp-edit-card">
        <div class="d-flex gap-3 align-items-center mb-3">
            <img src="{{ asset(optional($product)->image ?: 'public/img/logo.png') }}" alt="" class="sp-edit-thumb">
            <div>
                <h2 class="h5 mb-1">{{ $item->product_name ?: optional($product)->name }}</h2>
                <p class="small text-muted mb-0">Order #{{ $order->code }} · current sizes and toppings from Admin. You can change them while this order can still be updated.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('orders.update-item', [$order->id, $item->id]) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">Quantity</label>
                <input type="number" name="quantity" id="editQty" class="form-control" min="1" value="{{ old('quantity', $item->quantity) }}" required>
                <p class="fw-bold mt-2 mb-0" id="editLineTotal">
                    Line total: £{{ number_format((float) $item->product_price * max(1, (int) old('quantity', $item->quantity)), 2) }}
                    <span class="small text-muted fw-normal">(£{{ number_format((float) $item->product_price, 2) }} each)</span>
                </p>
            </div>

            @if(count($variants) > 0)
                <div class="mb-3">
                    <label class="form-label fw-bold">Size</label>
                    <select name="variant_id" class="form-control" required>
                        @foreach($variants as $variant)
                            @php $variantPrice = (float) (($variant->price ?? 0) > 0 ? $variant->price : ($variant->original_price ?? 0)); @endphp
                            <option value="{{ $variant->id }}" data-price="{{ $variantPrice }}" {{ (int) $selectedVariantId === (int) $variant->id ? 'selected' : '' }}>
                                {{ $variant->size }} — £{{ number_format($variantPrice, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(!empty($toppingGroups))
                <input type="hidden" name="update_toppings" value="1">
                @foreach($toppingGroups as $group)
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ $group['category_name'] }}</label>
                        @foreach($group['items'] as $topping)
                            <div class="form-check d-flex justify-content-between">
                                <div>
                                    <input class="form-check-input" type="checkbox"
                                        name="toppings_by_category[{{ $group['category_id'] }}][]"
                                        id="editTop{{ $group['category_id'] }}{{ $topping['id'] }}"
                                        value="{{ $topping['id'] }}"
                                        {{ !empty($topping['selected']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="editTop{{ $group['category_id'] }}{{ $topping['id'] }}">
                                        {{ $topping['name'] }}
                                    </label>
                                </div>
                                <span>£{{ number_format((float) $topping['price'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif

            <p class="small text-muted mb-0">To change pickup or home delivery, go back to My Orders and use <strong>Change delivery method</strong>.</p>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn sp-btn-pink">Save changes</button>
                <a href="{{ route('my-order') }}" class="btn btn-outline-dark">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
@section('js')
<script>
(function () {
    var qty = document.getElementById('editQty');
    var totalEl = document.getElementById('editLineTotal');
    var sizeSel = document.querySelector('select[name="variant_id"]');
    var unit = {{ json_encode((float) $item->product_price) }};
    function currentUnit() {
        if (sizeSel && sizeSel.options.length) {
            var opt = sizeSel.options[sizeSel.selectedIndex];
            var p = parseFloat(opt.getAttribute('data-price'));
            if (!isNaN(p) && p > 0) return p;
        }
        return unit;
    }
    function refresh() {
        if (!qty || !totalEl) return;
        var n = parseInt(qty.value, 10);
        if (!n || n < 1) n = 1;
        var each = currentUnit();
        var line = (each * n).toFixed(2);
        totalEl.innerHTML = 'Line total: £' + line + ' <span class="small text-muted fw-normal">(£' + each.toFixed(2) + ' × ' + n + ')</span>';
    }
    if (qty) qty.addEventListener('input', refresh);
    if (sizeSel) sizeSel.addEventListener('change', refresh);
    refresh();
})();
</script>
@endsection
