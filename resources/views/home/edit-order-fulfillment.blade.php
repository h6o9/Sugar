@extends('home.layout.app')
@section('title', 'Change delivery method')
@section('content')
@include('home.partials.page-hero', ['title' => 'Change delivery method'])
@php
    $currentDelivery = (int) old('delivery_status', $currentDelivery ?? 1);
    if ($currentDelivery !== 2) {
        $currentDelivery = 1;
    }
    $item = $item ?? null;
    $pickupBranch = $pickupBranch ?? null;
@endphp
<div class="container py-4" style="max-width:640px">
    <div class="sp-edit-card">
        <h2 class="h5 mb-2">Order #{{ $order->code }}</h2>
        <p class="small text-muted mb-4">Switch this whole order between store pickup and home delivery.</p>

        <form method="POST" action="{{ route('orders.update-fulfillment', $order->id) }}">
            @csrf
            <div class="mb-3">
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
            </div>
            <div id="editDeliveryFields" class="mt-2" style="{{ $currentDelivery === 2 ? '' : 'display:none;' }}">
                <input type="text"
                    name="delivery_address"
                    id="deliveryInputedit_fulfillment"
                    class="form-control location-input"
                    data-product="edit"
                    data-branch="fulfillment"
                    value="{{ old('delivery_address', optional($item)->delivery_address) }}"
                    placeholder="Enter your delivery address"
                    autocomplete="off">
                <input type="hidden" name="lat" id="latedit_fulfillment" value="{{ old('lat') }}">
                <input type="hidden" name="lng" id="lngedit_fulfillment" value="{{ old('lng') }}">
                <p class="small text-muted mt-1 mb-0">Pick an address from the Google suggestions.</p>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn sp-btn-pink">Save delivery method</button>
                <a href="{{ route('my-order') }}" class="btn btn-outline-dark">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
@section('js')
@if(session()->has('message') && session('status') === false)
<script>toastr.error(@json(session('message')));</script>
@endif
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
