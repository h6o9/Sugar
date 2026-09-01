@php
    $productId = $productId ?? null;
    $order = $updatingOrder ?? null;
    $lockItem = ($order && $order->orderItem) ? $order->orderItem->first() : null;
    $lockStatus = (int) optional($lockItem)->delivery_status === 2 ? 2 : 1;
    $lockAddress = optional($lockItem)->delivery_address;
    $branchList = collect($branches ?? []);
    $lockBranchId = optional($lockItem)->branch_id ?: optional($branchList->firstWhere('status', 1))->id;
@endphp
<div class="description p-3">
    <h6>How to get it</h6>
    <p class="small mb-1">
        This order is <strong>{{ $lockStatus === 2 ? 'Home Delivery' : 'Store Pickup' }}</strong>.
        To change it, open <strong>My Orders</strong> and tap <strong>Change delivery method</strong>.
    </p>
    @if($lockStatus === 2 && $lockAddress)
        <p class="small text-muted mb-0">{{ $lockAddress }}</p>
    @endif
    <input type="hidden" name="branch_id" value="{{ $lockBranchId }}">
    <input type="radio" class="d-none" name="status_{{ $productId }}" value="{{ $lockStatus }}" checked>
    @if($lockStatus === 2)
        <input type="hidden" name="delivery_address_{{ $productId }}" value="{{ $lockAddress }}">
        <input type="hidden" name="lat_{{ $productId }}" value="">
        <input type="hidden" name="lng_{{ $productId }}" value="">
    @endif
</div>
