@php
    $ff = \App\Support\CartCheckout::fulfillmentDetails($item, $order);
@endphp
<div class="small text-muted">
    {{ $ff['label'] }}
    @if(!empty($ff['display_address']))
        · {{ $ff['display_address'] }}
    @endif
</div>
