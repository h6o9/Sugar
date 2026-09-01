@php
    $gift = $item->complimentaryGift();
@endphp
@if($gift)
    <div class="sp-order-comp">
        <img src="{{ asset($gift->image ?: 'public/img/logo.png') }}" alt="">
        <div>
            <span class="sp-order-comp-badge">BUY 1 GET 1 FREE</span>
            <div class="small fw-bold">{{ $gift->name }}</div>
            <div class="small text-muted">Included with this item · Free</div>
        </div>
    </div>
@endif
