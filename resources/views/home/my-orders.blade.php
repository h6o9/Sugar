@extends('home.layout.app')
@section('title', 'My Orders')
@section('content')
@include('home.partials.page-hero', ['title' => 'My Orders'])
@php
    $lifecycle = app(\App\Services\OrderLifecycleService::class);
@endphp
<div class="sp-orders">
    @if($timerOrders->isEmpty() && $pendingOrders->isEmpty() && $pastOrders->isEmpty())
        <p class="text-center text-muted py-5">You have no orders yet.</p>
    @endif

    @if($timerOrders->isNotEmpty())
        @php
            $addMinutes = (int) ($lifecycle->addToOrderMinutes() ?: 10);
            // #region agent log
            file_put_contents(base_path('debug-1796d5.log'), json_encode([
                'sessionId' => '1796d5',
                'hypothesisId' => 'C',
                'location' => 'my-orders.blade.php:timer-heading',
                'message' => 'blade render add_minutes vs heading',
                'data' => ['addMinutes' => $addMinutes, 'heading_uses' => $addMinutes],
                'timestamp' => (int) round(microtime(true) * 1000),
            ]) . "\n", FILE_APPEND);
            // #endregion
        @endphp
        <h2>Orders you can still update</h2>
        <p class="text-muted">Regular orders use a {{ $addMinutes }}-minute window. Wholesale orders can be updated until 6 hours before the selected delivery time.</p>
        @foreach($timerOrders as $order)
            @php
                $state = $lifecycle->publicState($order);
                $remain = (int) ($state['remaining_seconds'] ?? 0);
                $itemCount = $order->orderItem->count();
                $isWholesale = !empty($state['is_wholesale']);
            @endphp
            @if($remain <= 0 && !$lifecycle->canModify($order))
                @continue
            @endif
            <div class="sp-order-card js-reload-when-done" data-sp-countdown="{{ $remain }}" @if($isWholesale) data-wholesale="1" @endif>
                <div class="sp-timer-note">
                    @if($isWholesale)
                        {{ $state['message'] }}
                        @if(!empty($state['remaining_time']))
                            <div class="mt-1">Time left: <span class="timer-remain">{{ $state['remaining_time'] }}</span></div>
                        @endif
                    @else
                        {{ $addMinutes }} minutes still running: <span class="timer-remain">{{ $state['remaining_time'] ?? '00:00' }}</span>
                    @endif
                </div>
                <div class="sp-order-top">
                    <h3>Order #{{ $order->code }}</h3>
                    <span class="text-danger fw-bold">{{ $order->status }}</span>
                </div>
                @foreach($order->orderItem as $item)
                    @php
                        $toppingNames = $item->orderToppings->map(function ($row) {
                            return optional($row->toppings)->name;
                        })->filter()->values();
                    @endphp
                    <div class="sp-order-item">
                        <img src="{{ asset(optional($item->product)->image ?: 'public/img/logo.png') }}" alt="">
                        <div>
                            <strong>{{ $item->product_name ?: optional($item->product)->name }}</strong>
                            <div class="small text-muted">
                                Qty {{ $item->quantity }}
                                @if($item->product_size)
                                    · {{ $item->product_size }}
                                @endif
                                · £{{ number_format((float) $item->product_price, 2) }}
                            </div>
                            @if($toppingNames->isNotEmpty())
                                <div class="small text-muted">Toppings: {{ $toppingNames->implode(', ') }}</div>
                            @endif
                            @php
                                $isDelivery = (int) ($item->delivery_status ?? 1) === 2;
                                if ($isDelivery) {
                                    $fulfillText = 'Home Delivery';
                                    if ($item->delivery_address) {
                                        $fulfillText .= ' · ' . $item->delivery_address;
                                    }
                                } else {
                                    $fulfillText = 'Store Pickup';
                                }
                            @endphp
                            <div class="small text-muted">{{ $fulfillText }}</div>
                        </div>
                        <div class="sp-item-actions">
                            <a class="btn btn-sm sp-edit-item" href="{{ route('orders.edit-item', [$order->id, $item->id]) }}">Edit</a>
                            <button type="button"
                                class="btn btn-sm sp-remove-item"
                                data-remove-url="{{ route('orders.remove-items', $order->id) }}"
                                data-item-id="{{ $item->id }}"
                                data-item-name="{{ $item->product_name ?: optional($item->product)->name }}"
                                data-item-count="{{ $itemCount }}"
                                @if($isWholesale) data-wholesale="1" @endif>Remove</button>
                        </div>
                    </div>
                @endforeach
                <div class="mt-3 d-flex flex-wrap gap-2 js-window-actions">
                    <a class="btn sp-btn-pink" href="{{ route('orders.add-items', $order->id) }}">Add items</a>
                    <a class="btn sp-edit-item" href="{{ route('orders.edit-fulfillment', $order->id) }}">Change delivery method</a>
                </div>
            </div>
        @endforeach
    @endif

    @if($pendingOrders->isNotEmpty())
        <h2>Pending orders</h2>
        @foreach($pendingOrders as $order)
            <div class="sp-order-card">
                <div class="sp-order-top">
                    <h3>Order #{{ $order->code }}</h3>
                    <span class="text-danger fw-bold">{{ $order->status }}</span>
                </div>
                @foreach($order->orderItem as $item)
                    @php
                        $toppingNames = $item->orderToppings->map(function ($row) {
                            return optional($row->toppings)->name;
                        })->filter()->values();
                    @endphp
                    <div class="sp-order-item">
                        <img src="{{ asset(optional($item->product)->image ?: 'public/img/logo.png') }}" alt="">
                        <div>
                            <strong>{{ $item->product_name ?: optional($item->product)->name }}</strong>
                            <div class="small text-muted">
                                Qty {{ $item->quantity }}
                                @if($item->product_size)
                                    · {{ $item->product_size }}
                                @endif
                                · £{{ number_format((float) $item->product_price, 2) }}
                            </div>
                            @if($toppingNames->isNotEmpty())
                                <div class="small text-muted">Toppings: {{ $toppingNames->implode(', ') }}</div>
                            @endif
                            @php
                                $isDelivery = (int) ($item->delivery_status ?? 1) === 2;
                                if ($isDelivery) {
                                    $fulfillText = 'Home Delivery';
                                    if ($item->delivery_address) {
                                        $fulfillText .= ' · ' . $item->delivery_address;
                                    }
                                } else {
                                    $fulfillText = 'Store Pickup';
                                }
                            @endphp
                            <div class="small text-muted">{{ $fulfillText }}</div>
                        </div>
                        @if($lifecycle->canModify($order))
                        <div class="sp-item-actions">
                            <a class="btn btn-sm sp-edit-item" href="{{ route('orders.edit-item', [$order->id, $item->id]) }}">Edit</a>
                            <button type="button"
                                class="btn btn-sm sp-remove-item"
                                data-remove-url="{{ route('orders.remove-items', $order->id) }}"
                                data-item-id="{{ $item->id }}"
                                data-item-name="{{ $item->product_name ?: optional($item->product)->name }}"
                                data-item-count="{{ $itemCount ?? $order->orderItem->count() }}"
                                @if($lifecycle->isWholesale($order)) data-wholesale="1" @endif>Remove</button>
                        </div>
                        @endif
                    </div>
                @endforeach
                <div class="fw-bold mt-2">Total: £{{ number_format((float) $order->total_amount, 2) }}</div>
                @if($lifecycle->canModify($order))
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <a class="btn sp-btn-pink" href="{{ route('orders.add-items', $order->id) }}">Add items</a>
                        <a class="btn sp-edit-item" href="{{ route('orders.edit-fulfillment', $order->id) }}">Change delivery method</a>
                    </div>
                @elseif($lifecycle->isWholesale($order))
                    <p class="small text-danger mt-2 mb-0">You can no longer update this order.</p>
                @endif
                <a class="btn sp-print-btn" href="{{ route('orders.receipt', $order->id) }}" target="_blank">Print receipt</a>
            </div>
        @endforeach
    @endif

    @if($pastOrders->isNotEmpty())
        <h2>Past orders</h2>
        @foreach($pastOrders as $order)
            <div class="sp-order-card">
                <div class="sp-order-top">
                    <h3>Order #{{ $order->code }}</h3>
                    <span class="fw-bold">{{ $order->status }}</span>
                </div>
                @foreach($order->orderItem as $item)
                    <div class="sp-order-item">
                        <img src="{{ asset(optional($item->product)->image ?: 'public/img/logo.png') }}" alt="">
                        <div>
                            <strong>{{ $item->product_name ?: optional($item->product)->name }}</strong>
                            <div class="small text-muted">Qty {{ $item->quantity }} · £{{ number_format((float) $item->product_price, 2) }}</div>
                        </div>
                    </div>
                @endforeach
                <a class="btn sp-print-btn" href="{{ route('orders.receipt', $order->id) }}" target="_blank">Print receipt</a>
            </div>
        @endforeach
    @endif
</div>
@endsection
@section('js')
@if(session()->has('message'))
<script>
    @if(session('status') === false)
        toastr.error(@json(session('message')));
    @else
        toastr.success(@json(session('message')));
    @endif
</script>
@endif
@endsection
