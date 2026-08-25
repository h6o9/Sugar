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
        <h2>Orders in {{ $addMinutes }}-minute window</h2>
        <p class="text-muted">These orders are placed. You still have time to add or remove items. Any change restarts the {{ $addMinutes }}-minute timer.</p>
        @foreach($timerOrders as $order)
            @php
                $state = $lifecycle->publicState($order);
                $remain = (int) ($state['remaining_seconds'] ?? 0);
                $itemCount = $order->orderItem->count();
            @endphp
            @if($remain <= 0)
                @continue
            @endif
            <div class="sp-order-card js-reload-when-done" data-sp-countdown="{{ $remain }}">
                <div class="sp-timer-note">
                    {{ $addMinutes }} minutes still running: <span class="timer-remain">{{ $state['remaining_time'] ?? '00:00' }}</span>
                </div>
                <div class="sp-order-top">
                    <h3>Order #{{ $order->code }}</h3>
                    <span class="text-danger fw-bold">{{ $order->status }}</span>
                </div>
                @foreach($order->orderItem as $item)
                    <div class="sp-order-item">
                        <img src="{{ asset(optional($item->product)->image ?: 'public/img/logo.png') }}" alt="">
                        <div>
                            <strong>{{ $item->product_name ?: optional($item->product)->name }}</strong>
                            <div class="small text-muted">Qty {{ $item->quantity }} · £{{ number_format((float) $item->product_price, 2) }}</div>
                        </div>
                        <button type="button"
                            class="btn btn-sm sp-remove-item"
                            data-remove-url="{{ route('orders.remove-items', $order->id) }}"
                            data-item-id="{{ $item->id }}"
                            data-item-name="{{ $item->product_name ?: optional($item->product)->name }}"
                            data-item-count="{{ $itemCount }}">Remove</button>
                    </div>
                @endforeach
                <div class="mt-3 d-flex flex-wrap gap-2 js-window-actions">
                    <a class="btn sp-btn-pink" href="{{ route('orders.add-items', $order->id) }}">Add items</a>
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
                    <div class="sp-order-item">
                        <img src="{{ asset(optional($item->product)->image ?: 'public/img/logo.png') }}" alt="">
                        <div>
                            <strong>{{ $item->product_name ?: optional($item->product)->name }}</strong>
                            <div class="small text-muted">Qty {{ $item->quantity }} · £{{ number_format((float) $item->product_price, 2) }}</div>
                        </div>
                    </div>
                @endforeach
                <div class="fw-bold mt-2">Total: £{{ number_format((float) $order->total_amount, 2) }}</div>
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
