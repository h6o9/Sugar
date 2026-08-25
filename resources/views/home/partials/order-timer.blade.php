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
        <p class="sp-timer-msg">Your order is placed. You have {{ (int) ($activeOrderState['add_minutes'] ?? 10) }} minutes to add or remove items. If you add or deduct anything, the old receipt is cancelled, a new receipt is issued, and this timer restarts. If you do nothing, the order stays as placed.</p>
        <div class="sp-timer-actions">
            <a class="btn btn-sm btn-dark" href="{{ route('orders.add-items', $timerOrderId) }}">Add items</a>
            <button type="button" class="btn btn-sm btn-outline-light" id="spRemoveItemsBtn">Remove items</button>
        </div>
    </div>
@endif
