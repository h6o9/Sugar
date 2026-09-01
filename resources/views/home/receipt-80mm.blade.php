<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $receipt->receipt_number }}</title>
    <link rel="stylesheet" href="{{ asset('public/css/sugarpappi-update.css') }}">
    <style>
        body { background:#f4f4f4; }
        .wrap { padding: 16px; }
        .receipt-logo { display:block; max-width:72px; height:auto; margin:0 auto 6px; }
    </style>
</head>
<body>
    <div class="wrap no-print text-center mb-3">
        <button onclick="window.print()">Print 80mm receipt</button>
        @if($receipt->status !== 'active')
            <p style="color:red">This receipt has been {{ $receipt->status }} and should not be used.</p>
        @endif
    </div>
    @php
        $snap = $receipt->snapshot ?: [];
        $firstItem = $order->orderItem->first();
        $ff = $firstItem ? \App\Support\CartCheckout::fulfillmentDetails($firstItem, $order) : null;
        $logoUrl = $snap['logo_url'] ?? \App\Support\CartCheckout::logoUrl();
        $companyName = $snap['brand'] ?? \App\Support\CartCheckout::companyName();
        $branchName = $ff['pickup_name'] ?? optional(optional($firstItem)->branch)->name;
        $branchAddress = $ff['pickup_address'] ?? optional(optional($firstItem)->branch)->location;
    @endphp
    <div class="receipt-80mm">
        <div style="text-align:center;border-bottom:1px dashed #000;padding-bottom:8px;margin-bottom:8px;">
            <img class="receipt-logo" src="{{ $logoUrl }}" alt="{{ $companyName }}" style="display:block;max-width:72px;height:auto;margin:0 auto 6px;">
            <strong>{{ strtoupper($companyName) }}</strong><br>
            {{ $branchName }}<br>
            {{ $branchAddress }}
        </div>
        <div>Order #{{ $snap['order_number'] ?? $order->code }}</div>
        <div>Receipt {{ $snap['receipt_number'] ?? $receipt->receipt_number }} (v{{ $receipt->version }})</div>
        <div>Status: {{ strtoupper($receipt->status) }}</div>
        <div>Date: {{ $snap['date'] ?? optional($order->created_at)->format('Y-m-d') }}</div>
        <div>Time: {{ $snap['time'] ?? optional($order->created_at)->format('H:i') }}</div>
        <div>Type: {{ $snap['order_type'] ?? $order->order_type }}</div>
        <div>Order status: {{ $order->status }}</div>
        @if(!empty($snap['customer']))<div>Customer: {{ $snap['customer'] }}</div>@endif
        @if($ff)
            <div>{{ $ff['label'] }}</div>
            @if($ff['is_delivery'] && !empty($ff['delivery_address']))
                <div>Delivery address: {{ $ff['delivery_address'] }}</div>
            @elseif(!empty($ff['pickup_address']))
                <div>Pickup address: {{ $ff['pickup_address'] }}</div>
            @endif
        @endif
        @if($order->wholesale_delivery_date)<div>Wholesale delivery: {{ $order->wholesale_delivery_date }} (7PM–10PM)</div>@endif
        @if($order->is_scheduled)<div>Scheduled: {{ $order->scheduled_at }}</div>@endif
        <hr>
        <table>
            <thead>
                <tr><th align="left">Item</th><th>Qty</th><th align="right">Total</th></tr>
            </thead>
            <tbody>
            @foreach(($snap['items'] ?? []) as $item)
                <tr>
                    <td>
                        {{ $item['name'] }}
                        @if(!empty($item['size']) && $item['size'] !== 'NULL') ({{ $item['size'] }}) @endif
                        @if(!empty($item['fulfillment']))
                            <div style="font-size:11px;font-weight:700;">{{ is_array($item['fulfillment']) ? ($item['fulfillment']['label'] ?? '') : $item['fulfillment'] }}</div>
                        @endif
                        @if(!empty($item['address']))
                            <div style="font-size:11px;">{{ $item['address'] }}</div>
                        @elseif(!empty($item['fulfillment']['display_address']))
                            <div style="font-size:11px;">{{ $item['fulfillment']['display_address'] }}</div>
                        @endif
                        @foreach(($item['modifiers'] ?? []) as $mod)
                            <div style="font-size:11px">+ {{ $mod['name'] }}</div>
                        @endforeach
                    </td>
                    <td align="center">{{ $item['qty'] }}</td>
                    <td align="right">£{{ number_format((float)$item['line_total'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <hr>
        <div>Subtotal: £{{ number_format((float)($snap['subtotal'] ?? $order->subtotal), 2) }}</div>
        @if(!empty($snap['discount']))
            <div>Discount {{ $snap['discount_label'] }}: -£{{ number_format((float)$snap['discount'], 2) }}</div>
        @endif
        <div>Delivery: £{{ number_format((float)($snap['delivery_charge'] ?? $order->delivery_charge), 2) }}</div>
        <div>Tax: £{{ number_format((float)($snap['tax'] ?? 0), 2) }}</div>
        <div><strong>Grand total: £{{ number_format((float)($snap['grand_total'] ?? $order->total_amount), 2) }}</strong></div>
        <div>Payment: {{ $snap['payment'] ?? $order->payment }} / {{ $snap['payment_status'] ?? '' }}</div>
        @if(!empty($snap['notes']))<div>Notes: {{ $snap['notes'] }}</div>@endif
        <div style="text-align:center;margin-top:10px;">Thank you</div>
    </div>
</body>
</html>
