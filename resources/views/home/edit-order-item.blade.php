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
                <input type="number" name="quantity" class="form-control" min="1" value="{{ old('quantity', $item->quantity) }}" required>
            </div>

            @if(count($variants) > 0)
                <div class="mb-3">
                    <label class="form-label fw-bold">Size</label>
                    <select name="variant_id" class="form-control" required>
                        @foreach($variants as $variant)
                            @php $variantPrice = (float) (($variant->price ?? 0) > 0 ? $variant->price : ($variant->original_price ?? 0)); @endphp
                            <option value="{{ $variant->id }}" {{ (int) $selectedVariantId === (int) $variant->id ? 'selected' : '' }}>
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

            @php
                $isWholesaleOrder = app(\App\Services\OrderLifecycleService::class)->isWholesale($order);
                $currentDelivery = (int) old('delivery_status', $item->delivery_status ?? 1);
                if ($currentDelivery !== 2) {
                    $currentDelivery = 1;
                }
                $pickupBranch = $item->branch ?: \App\Models\Branch::where('status', 1)->first();
            @endphp
            @if(!$isWholesaleOrder)
            <div class="mb-3">
                <label class="form-label fw-bold">How to get it</label>
                <p class="small text-muted">This updates the whole order. Switch pickup to home delivery if you changed your mind.</p>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="delivery_status" id="editPickup" value="1" {{ $currentDelivery === 1 ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="editPickup">Store Pickup</label>
                </div>
                @if($pickupBranch && $pickupBranch->location)
                    <p class="small mb-2" id="editPickupNote">{{ $pickupBranch->location }}</p>
                @endif
                <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="delivery_status" id="editDelivery" value="2" {{ $currentDelivery === 2 ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="editDelivery">Home Delivery</label>
                </div>
                <div id="editDeliveryFields" class="mt-2" style="{{ $currentDelivery === 2 ? '' : 'display:none;' }}">
                    <input type="text"
                        name="delivery_address"
                        id="deliveryInputedit_fulfillment"
                        class="form-control location-input"
                        data-product="edit"
                        data-branch="fulfillment"
                        value="{{ old('delivery_address', $item->delivery_address) }}"
                        placeholder="Enter your delivery address"
                        autocomplete="off">
                    <input type="hidden" name="lat" id="latedit_fulfillment" value="{{ old('lat') }}">
                    <input type="hidden" name="lng" id="lngedit_fulfillment" value="{{ old('lng') }}">
                    <p class="small text-muted mt-1 mb-0">Pick an address from the Google suggestions.</p>
                </div>
            </div>
            @endif

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
document.querySelectorAll('input[name="delivery_status"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        var box = document.getElementById('editDeliveryFields');
        if (!box) return;
        box.style.display = this.value === '2' ? '' : 'none';
    });
});
</script>
@endsection
