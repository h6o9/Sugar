@if(!empty($activeOrderState['can_add_items']) && !empty($activeOrderState['current_order']['id']))
    @php
        $timerOrderId = (int) $activeOrderState['current_order']['id'];
        $timerItems = [];
        try {
            $timerItems = \App\Models\OrderItem::where('order_id', $timerOrderId)
                ->get(['id', 'product_name', 'quantity'])
                ->map(function ($row) {
                    return [
                        'id' => (int) $row->id,
                        'name' => $row->product_name ?: ('Item #' . $row->id),
                        'qty' => (int) $row->quantity,
                    ];
                })->values();
        } catch (\Throwable $e) {
            $timerItems = [];
        }
    @endphp
    <div class="sp-timer-bar" id="addToOrderTimer"
        data-sp-countdown="{{ (int) $activeOrderState['remaining_seconds'] }}"
        data-minutes="{{ (int) ($activeOrderState['add_minutes'] ?? 10) }}"
        data-order-id="{{ $timerOrderId }}"
        data-remove-url="{{ route('orders.remove-items', $timerOrderId) }}"
        data-items='@json($timerItems)'>
        <div class="sp-timer-clock">Time left: <span class="timer-remain">{{ $activeOrderState['remaining_time'] }}</span></div>
        <p class="sp-timer-msg">Your order is placed. You have {{ (int) ($activeOrderState['add_minutes'] ?? 10) }} minutes to add items, remove items, or change size and toppings. Any change cancels the old receipt, issues a new one, and restarts this timer.</p>
        <div class="sp-timer-actions">
            <a class="btn btn-sm btn-dark" href="{{ route('orders.add-items', $timerOrderId) }}">Add items</a>
            <a class="btn btn-sm btn-outline-light" href="{{ route('my-order') }}">Change size / toppings</a>
            <button type="button" class="btn btn-sm btn-outline-light" id="spRemoveItemsBtn">Remove items</button>
        </div>
    </div>
@endif
