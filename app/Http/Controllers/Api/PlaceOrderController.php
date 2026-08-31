<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ApiOrderPlacementService;
use App\Services\OrderLifecycleService;
use App\Support\CartCheckout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class PlaceOrderController extends Controller
{
    // ============================================================
    // TEMP TEST FLAG — Stripe skip
    // true  = Stripe skip, order seedha create (testing)
    // false = Stripe wapas ON (live payment)
    // File: app/Http/Controllers/Api/PlaceOrderController.php
    // ============================================================
    private const SKIP_STRIPE_FOR_TESTING = true;

    public function __construct(
        private ApiOrderPlacementService $orderService,
        private OrderLifecycleService $lifecycle
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/place-order
    //
    // CRITICAL DESIGN:
    //   We validate EVERYTHING before creating the Stripe session.
    //   If any check fails → clear error shown to user → NO payment page opened.
    //   This prevents the scenario: payment succeeds but order cannot be created.
    //
    // Flow:
    //   1. Auth check
    //   2. Pre-flight validation (cart, products, address, points, minimums)
    //   3. Only if ALL checks pass → create Stripe session
    //   4. Return payment URL to the app
    // ─────────────────────────────────────────────────────────────────────────
    public function placeOrder(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'code'    => 'UNAUTHORIZED',
                'message' => 'You are not logged in. Please log in again and try.',
            ], 401);
        }

        try {
            // ── STEP 1: Pre-flight validation (BEFORE touching Stripe) ────────
            // If anything here fails, the user sees a clear error message and
            // NO Stripe session is created — no money is charged.
            $validationResult = $this->orderService->validateOrderBeforePayment($user->id, $request);

            if (!$validationResult['valid']) {
                Log::warning('PlaceOrder: pre-flight validation failed', [
                    'user_id' => $user->id,
                    'code'    => $validationResult['code'],
                    'message' => $validationResult['message'],
                ]);

                return response()->json([
                    'status'  => false,
                    'code'    => $validationResult['code'],
                    'message' => $validationResult['message'],
                ], 422);
            }

            // ── STEP 2: All validations passed → initiate Stripe checkout ────
            // ===== TEMP TEST START: Stripe skip =====
            if (self::SKIP_STRIPE_FOR_TESTING) {
                return $this->placeOrderSkippingStripe($request, $user->id, $validationResult['data']);
            }
            // ===== TEMP TEST END =====
            return $this->initiateStripeCheckout($request, $user->id, $validationResult['data']);

        } catch (\RuntimeException $e) {
            Log::warning('PlaceOrder: RuntimeException', [
                'user_id' => $user->id ?? null,
                'error'   => $e->getMessage(),
            ]);
            return response()->json([
                'status'  => false,
                'code'    => 'VALIDATION_ERROR',
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('PlaceOrder: unexpected server error', [
                'user_id' => $user->id ?? null,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => false,
                'code'    => 'SERVER_ERROR',
                'message' => 'A server error occurred. Please try again in a moment.',
            ], 500);
        }
    }

    // GET /api/place-order/{orderId}
    // GET /api/order-confirmation/{orderId}
    public function getConfirmation(Request $request, $orderId)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'code'    => 'UNAUTHORIZED',
                'message' => 'You are not logged in. Please log in again and try.',
            ], 401);
        }

        $order = Order::with(['orderItem.branch'])
            ->where('user_id', $user->id)
            ->where(function ($query) use ($orderId) {
                $query->where('id', $orderId)
                    ->orWhere('code', $orderId);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'code'    => 'NOT_FOUND',
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json($this->confirmationPayload($order));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private: Create Stripe checkout session
    // Only called AFTER all pre-flight validations pass.
    // $preValidatedData is already calculated — no need to recalculate totals.
    // ─────────────────────────────────────────────────────────────────────────
    private function initiateStripeCheckout(Request $request, int $userId, array $preValidatedData)
    {
        $finalTotal        = max(0, (float) $preValidatedData['estimatedTotal']);
        $gatewayFee        = round(($finalTotal * 0.025) + 0.25, 2);
        $finalTotalWithFee = round($finalTotal + $gatewayFee, 2);

        // ── Save points_to_redeem into cart rows for this user ────────────────
        $pointsToRedeem = (int) $request->input('points_to_redeem', 0);
        DB::table('add_to_cart_items')
            ->where('user_id', $userId)
            ->update(['points_to_redeem' => $pointsToRedeem]);

        // ── Store order context in Stripe metadata ────────────────────────────
        // Cart items are NOT stored here — on success we re-read from DB.
        // We DO store the expected_total as a tamper-detection snapshot.
        $ctx = \App\Support\AppCartContext::get($userId);
        $metaOrderType       = (string) ($request->input('order_type',       'delivery'));
        $metaDeliveryAddress = (string) ($request->input('delivery_address', ''));
        $metaDeliveryCharges = (string) ($request->input('delivery_charges', '0'));
        $metaTip             = (string) ($request->input('tip',              '0'));
        $metaBranchId        = (string) ($request->input('branch_id',        ''));
        $metaVehicleColor    = (string) ($request->input('vehicle_color',    ''));
        $metaVehicleNumber   = (string) ($request->input('vehicle_number',   ''));
        $metaPickupTime      = (string) ($request->input('pickup_time',      $ctx['pickup_time'] ?? ''));
        $metaChannel         = \App\Support\AppCartContext::normalizeChannel($request->input('channel', $ctx['channel'] ?? 'regular'));
        $metaMenuType        = (string) ($request->input('menu_type', $metaChannel === 'regular' ? 'food' : $metaChannel));
        $metaWholesaleDate   = (string) ($request->input('wholesale_delivery_date', $ctx['wholesale_delivery_date'] ?? ''));
        $metaFulfillment     = (string) ($request->input('fulfillment', $ctx['fulfillment'] ?? ''));
        $metaCartItemIds     = implode(',', array_map('intval', (array) ($request->input('cart_item_ids', $ctx['checkout_item_ids'] ?? []))));

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency'     => 'gbp',
                        'product_data' => ['name' => 'Order Payment'],
                        'unit_amount'  => $this->orderService->amountToStripePence($finalTotalWithFee),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => [
                    'user_id'          => (string) $userId,
                    'gateway_fee'      => (string) $gatewayFee,
                    'expected_total'   => (string) round($preValidatedData['estimatedTotal'], 2),
                    'expected_subtotal'=> (string) round($preValidatedData['subtotal'], 2),
                    'order_type'       => $metaOrderType,
                    'delivery_address' => $metaDeliveryAddress,
                    'delivery_charges' => $metaDeliveryCharges,
                    'tip'              => $metaTip,
                    'branch_id'        => $metaBranchId,
                    'vehicle_color'    => $metaVehicleColor,
                    'vehicle_number'   => $metaVehicleNumber,
                    'pickup_time'      => $metaPickupTime,
                    'channel'          => $metaChannel,
                    'menu_type'        => $metaMenuType,
                    'fulfillment'      => $metaFulfillment,
                    'wholesale_delivery_date' => $metaWholesaleDate,
                    'cart_item_ids'    => $metaCartItemIds,
                    'is_scheduled'     => ($request->boolean('is_scheduled') || !empty($ctx['is_scheduled'])) ? '1' : '0',
                    'scheduled_at'     => (string) ($request->input('scheduled_at') ?: ($ctx['scheduled_at'] ?? '')),
                ],
                'success_url' => url('/api/payment/stripe/webview/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url'  => url('/api/payment/stripe/webview/cancel'),
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('PlaceOrder: Stripe session creation failed', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
            return response()->json([
                'status'  => false,
                'code'    => 'STRIPE_ERROR',
                'message' => 'Could not connect to the payment service. Please try again.',
            ], 502);
        }

        Log::info('PlaceOrder: Stripe session created', [
            'user_id'    => $userId,
            'session_id' => $session->id,
            'amount'     => $finalTotalWithFee,
        ]);

        return response()->json([
            'status'              => true,
            'message'             => 'Complete your payment to place the order.',
            'payment_required'    => true,
            'stripe_checkout_url' => $session->url,
            'payment_url'         => url('/api/payment/stripe/webview/direct?stripe_session_id=' . $session->id),
            'summary' => [
                'subtotal'         => round($preValidatedData['subtotal'], 2),
                'tax'              => round($preValidatedData['totalTax'], 2),
                'tip'              => round($preValidatedData['tip'], 2),
                'delivery_charge'  => round($preValidatedData['deliveryCharges'], 2),
                'points_discount'  => round($preValidatedData['pointsDiscount'], 2),
                'drive_in_discount'=> round($preValidatedData['driveInDiscount'] ?? 0, 2),
                'channel'          => $preValidatedData['channel'] ?? $metaChannel,
                'total_before_fee' => round($finalTotal, 2),
                'gateway_fee'      => $gatewayFee,
                'final_total'      => $finalTotalWithFee,
            ],
        ]);
    }

    // ===== TEMP TEST START: Stripe ke baghair order (testing only) =====
    // Stripe wapas on: SKIP_STRIPE_FOR_TESTING = false
    private function placeOrderSkippingStripe(Request $request, int $userId, array $preValidatedData)
    {
        $pointsToRedeem = (int) $request->input('points_to_redeem', 0);
        DB::table('add_to_cart_items')
            ->where('user_id', $userId)
            ->update(['points_to_redeem' => $pointsToRedeem]);

        $order = $this->orderService->createOrderFromCart(
            $userId,
            $request,
            'test_skip_stripe',
            null,
            0
        );

        Log::warning('PlaceOrder: STRIPE SKIPPED FOR TESTING — order created without payment', [
            'user_id'  => $userId,
            'order_id' => $order->id,
            'code'     => $order->code,
        ]);

        return response()->json($this->confirmationPayload($order, $preValidatedData, [
            'message'          => 'Your order has been placed.',
            'payment_required' => false,
            'stripe_skipped'   => true,
        ]));
    }
    // ===== TEMP TEST END =====

    private function confirmationPayload(Order $order, ?array $preValidatedData = null, array $extra = []): array
    {
        $order->loadMissing(['orderItem.branch']);
        $item = $order->orderItem->first();
        $delivery = $this->resolveDeliveryInfo($order, $item, $preValidatedData);

        $subtotal = round((float) ($preValidatedData['subtotal'] ?? $order->subtotal ?? 0), 2);
        $tax = round((float) ($preValidatedData['totalTax'] ?? $order->tax ?? 0), 2);
        $tip = round((float) ($preValidatedData['tip'] ?? $order->tips ?? 0), 2);
        $deliveryCharge = round((float) ($preValidatedData['deliveryCharges'] ?? $order->delivery_charge ?? 0), 2);
        $pointsDiscount = round((float) ($preValidatedData['pointsDiscount'] ?? $order->points_discount ?? 0), 2);
        $driveInDiscount = round((float) ($preValidatedData['driveInDiscount'] ?? $order->discount_amount ?? 0), 2);
        $estimated = round((float) ($preValidatedData['estimatedTotal'] ?? $order->estimated_total ?? $order->total_amount ?? 0), 2);
        $channel = $preValidatedData['channel'] ?? $order->channelKey();
        $timer = $this->lifecycle->timerPayload($order);

        return array_merge([
            'status'           => true,
            'message'          => 'Your order has been placed.',
            'payment_required' => false,
            'delivery_address' => $delivery['delivery_address'],
            'delivery_method'  => $delivery['delivery_method'],
            'delivery_method_key' => $delivery['delivery_method_key'],
            'is_pickup'        => $delivery['is_pickup'],
            'is_home_delivery' => $delivery['is_home_delivery'],
            'order_type'       => $delivery['order_type'],
            'timer'            => $timer,
            'order'            => [
                'id'               => $order->id,
                'code'             => $order->code,
                'status'           => $order->status,
                'delivery_address' => $delivery['delivery_address'],
                'delivery_method'  => $delivery['delivery_method'],
                'delivery_method_key' => $delivery['delivery_method_key'],
                'order_type'       => $delivery['order_type'],
                'timer'            => $timer,
            ],
            'summary' => [
                'subtotal'           => $subtotal,
                'tax'                => $tax,
                'tip'                => $tip,
                'delivery_charge'    => $deliveryCharge,
                'points_discount'    => $pointsDiscount,
                'drive_in_discount'  => $driveInDiscount,
                'channel'            => $channel,
                'delivery_address'   => $delivery['delivery_address'],
                'delivery_method'    => $delivery['delivery_method'],
                'timer'              => $timer,
                'total_before_fee'   => $estimated,
                'gateway_fee'        => 0,
                'final_total'        => $estimated,
            ],
        ], $extra);
    }

    private function resolveDeliveryInfo(Order $order, $item, ?array $preValidatedData = null): array
    {
        $orderType = strtolower((string) (
            $preValidatedData['orderType']
            ?? $order->order_type
            ?? optional($item)->order_type
            ?? 'delivery'
        ));

        $address = $preValidatedData['deliveryAddress']
            ?? optional($item)->delivery_address
            ?? optional(optional($item)->branch)->location
            ?? null;

        $status = (int) (optional($item)->delivery_status ?? ($orderType === 'pickup' ? 1 : 2));

        if (in_array($orderType, ['pickup', 'takeaway'], true) || $status === 1) {
            $method = 'Pickup';
            $key = 'pickup';
        } else {
            $method = 'Home';
            $key = 'home';
        }

        if (in_array($orderType, ['drive_in', 'drive-in', 'drivein'], true)) {
            $method = 'Drive-In';
            $key = 'drive_in';
        } elseif (in_array($orderType, ['wholesale', 'dessert_wholesale'], true)) {
            $method = 'Wholesale Delivery';
            $key = 'wholesale';
        }

        if (($key === 'pickup' || $key === 'drive_in') && empty($address) && $item && $item->branch) {
            $address = $item->branch->location;
        }

        return [
            'delivery_address'    => $address,
            'delivery_method'     => $method,
            'delivery_method_key' => $key,
            'order_type'          => $orderType,
            'is_pickup'           => $key === 'pickup',
            'is_home_delivery'    => $key === 'home',
            'fulfillment_label'   => $item ? CartCheckout::fulfillmentLabel($item, $orderType) : $method,
        ];
    }
}