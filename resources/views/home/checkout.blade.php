@extends('home.layout.app')
@section('title', 'Login')
@section('content')
    <style>
        svg.app {
            height: 40px;
        }

        .checkout .pay .btn {
            border: 2px solid black;
            border-radius: 10px;
        }

        /* remove increment buttons from the input */

        .number-input::-webkit-inner-spin-button,
        .number-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .number-input {
            -moz-appearance: textfield;
        }

        .select-input {
            display: flex;
            justify-content: space-between;
        }

        .select-input .select,
        .payment-cards .select,
        form.payment input {
            width: 30%;
            border-radius: 8px;
            padding: 7px 10px;
            border: 1px solid black;
        }

        .is-success {
            color: green;
        }

        .is-failure {
            color: red;
        }

        .select select {
            width: 100%;
            border: none;
        }

        .select select:focus {
            outline: none;
        }

        .select-input input {
            width: 66%;
        }

        .contact input {
            border: 1px solid black;
            border-radius: 8px;
            padding: 7px 10px;
        }

        .hide {
            display: none;
        }

        .active.deactive {
            border: 2px solid black;
            border-radius: 8px;
        }

        .deactive {
            border: 1px solid rgba(0, 0, 0, 0.3);
            border-radius: 8px;
        }

        .square-pay .square-checkbox,
        .earn-points {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .billing {
            position: sticky;
            top: 125px;
        }

        @media (max-width:991px) and (min-width:768px) {
            .billing {
                top: 10px;
            }
        }

        @media (max-width:767px) {
            .billing {
                position: static;
            }
        }

        .apple-pay-button {
            height: 48%;
            width: 100%;
            display: inline-block;
            -webkit-appearance: -apple-pay-button;
            -apple-pay-button-type: plain;
            -apple-pay-button-style: black;
        }
    </style>
    <section class="section">
        <div class="container-xxl position-relative p-0">
            <div class="container-xxl py-5 bg-primary hero-header mb-md-5 mb-3">
                <div class="container text-center my-lg-5 pt-lg-5 pb-lg-4">
                    <h1 class="display-3 text-dark mb-3 animated slideInDown">Checkout</h1>
                </div>
            </div>
        </div>

        <!--Cart Section Start -->
        <div class="container-fluid cart wow fadeIn" data-wow-delay="0.1s">
            <div class="col-12 col-sm-11 col-md-12 col-lg-11 mx-auto mt-5">
                <div class="row justify-content-between align-items-start">
                    <div class="order-sm-1 order-2 col-md-7 col-lg-6 checkout mt-0">
                        <!-- Checkout Start -->

                        <!-- Express Check Out-->
                        <div class="pb-4 border-bottom">
                            <div class="d-flex flex-wrap justify-content-sm-between justify-content-center pay">
                                <h5 class="me-2 mb-3">EXPRESS CHECKOUT</h5>
                                <button class="btn w-50 btn-dark px-0 text-center"><svg data-v-7490c580=""
                                        xmlns="http://www.w3.org/2000/svg" width="119" height="22"
                                        viewBox="0 0 119 22" fill="none" svg-inline="" role="presentation"
                                        focusable="false" tabindex="-1" class="square-icon">
                                        <path data-v-7490c580=""
                                            d="M17.685 0H3.547A3.547 3.547 0 000 3.547v14.135a3.55 3.55 0 003.547 3.55h14.135a3.55 3.55 0 003.55-3.55V3.547A3.547 3.547 0 0017.682 0h.003zm-.313 16.252c0 .62-.503 1.12-1.12 1.12H4.982c-.616 0-1.12-.5-1.12-1.12V4.982c0-.62.5-1.12 1.12-1.12h11.27c.617 0 1.12.5 1.12 1.12v11.273-.003zm-4.503-2.754c.357 0 .643-.29.643-.646v-4.5a.644.644 0 00-.643-.647H8.363a.644.644 0 00-.643.646v4.5c0 .357.286.647.643.647h4.506zm13.992-.307h2.319c.117 1.313 1.006 2.336 2.801 2.336 1.602 0 2.588-.792 2.588-1.988 0-1.12-.772-1.623-2.164-1.95l-1.795-.387c-1.95-.424-3.419-1.681-3.419-3.728 0-2.26 2.01-3.807 4.617-3.807 2.764 0 4.542 1.447 4.694 3.594h-2.24c-.27-1.006-1.102-1.603-2.454-1.603-1.43 0-2.415.772-2.415 1.758 0 .985.851 1.584 2.319 1.912l1.778.386c1.95.424 3.284 1.602 3.284 3.67 0 2.628-1.971 4.193-4.79 4.193-3.167 0-4.927-1.72-5.12-4.386h-.003zm18.205 8.041v-3.845l.152-1.687h-.152c-.529 1.213-1.649 1.874-3.163 1.874-2.445 0-4.264-1.989-4.264-5.041 0-3.053 1.819-5.041 4.264-5.041 1.497 0 2.558.701 3.163 1.8h.152v-1.61h2.01v13.547h-2.162v.003zm.076-8.696c0-1.95-1.193-3.088-2.652-3.088s-2.652 1.137-2.652 3.088c0 1.95 1.193 3.088 2.652 3.088 1.46 0 2.652-1.138 2.652-3.088zm3.688 1.003V7.685h2.16v5.664c0 1.535.74 2.274 1.972 2.274 1.514 0 2.5-1.079 2.5-2.766V7.685h2.16v9.702h-2.008v-2.01h-.152c-.474 1.29-1.515 2.2-3.126 2.2-2.313 0-3.506-1.477-3.506-4.035v-.003zm10.108 1.137c0-1.819 1.27-2.88 3.524-3.012l2.673-.17v-.757c0-.91-.664-1.459-1.84-1.459-1.079 0-1.725.55-1.894 1.328H59.24c.228-1.971 1.856-3.109 4.055-3.109 2.483 0 3.998 1.062 3.998 3.109v6.784h-2.01v-1.802h-.151c-.456 1.194-1.4 1.989-3.223 1.989-1.821 0-2.974-1.176-2.974-2.898l.003-.003zm6.197-1.193v-.512l-2.179.152c-1.175.077-1.705.512-1.705 1.384 0 .74.606 1.269 1.46 1.269 1.535 0 2.424-.986 2.424-2.293zm3.769 3.904V7.685h2.009V9.54h.152c.283-1.269 1.251-1.856 2.69-1.856h.985v1.95h-1.23c-1.401 0-2.445.91-2.445 2.635v5.114h-2.161v.003zm15.72-4.454h-7.372c.114 1.781 1.366 2.784 2.749 2.784 1.175 0 1.912-.474 2.33-1.269h2.14c-.587 1.97-2.312 3.126-4.49 3.126-2.86 0-4.87-2.14-4.87-5.041 0-2.901 2.065-5.041 4.89-5.041 2.825 0 4.699 1.95 4.699 4.377 0 .474-.038.72-.076 1.061v.003zm-2.064-1.497c-.077-1.345-1.194-2.254-2.56-2.254-1.289 0-2.368.816-2.634 2.254h5.194zm7.395 5.95V4.127h4.889c2.822 0 4.547 1.439 4.547 4.035 0 2.597-1.726 4.036-4.547 4.036h-2.635v5.193h-2.254v-.003zm2.254-7.219h2.71c1.346 0 2.217-.681 2.217-2.009 0-1.327-.871-2.009-2.216-2.009h-2.71v4.018zm7.293 4.51c0-1.82 1.269-2.881 3.523-3.013l2.673-.17v-.757c0-.91-.664-1.459-1.839-1.459-1.079 0-1.649.55-1.819 1.328h-2.161c.228-1.971 1.781-3.109 3.98-3.109 2.482 0 3.997 1.062 3.997 3.109v6.784h-2.009v-1.802h-.152c-.456 1.194-1.404 1.989-3.222 1.989-1.819 0-2.974-1.176-2.974-2.898l.003-.003zm6.196-1.194v-.512l-2.178.152c-1.176.077-1.705.512-1.705 1.384 0 .74.605 1.269 1.459 1.269 1.535 0 2.424-.986 2.424-2.293zm3.143 7.465v-1.912h1.629c.854 0 1.328-.266 1.629-1.006l.19-.491-4.091-9.852h2.407l2.102 5.474.494 1.591h.152l.474-1.59 1.988-5.475h2.313l-4.111 10.685c-.74 1.913-1.705 2.576-3.43 2.576h-1.746z"
                                            fill="var(--icon-fill, white)"></path>
                                    </svg></button>
                            </div>
                        </div>

                        <!-- Contact End -->
                        <!-- Payment Start-->
                        <div class="mt-4">
                            <p class="mb-0">Payment</p>
                            <p class="small">All transactions are secure and encrypted</p>

                            {{-- Square Pyament Gate way --}}
                            <form class="mt-2 payment" action="{{ route('orders') }}" method="POST">
                                @csrf
                                @foreach ($branchess as $branch)
                                    @if ($branch->status == 1)
                                        @if (Auth::guard('user')->check())
                                            {{-- <div class="mb-3">
                                                <label for="vehicle_color">Vehicle Color</label>
                                                <input type="text" class="form-control" name="vehicle_color" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="vehicle_no">Vehicle No</label>
                                                <input type="text" class="form-control" name="vehicle_no" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="redeemed">Redeem (optional)</label>
                                                <input type="number" class="form-control" name="redeemed">
                                            </div> --}}

                                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                            <input type="hidden" name="payment_method" value="offline">

                                            <button type="submit"
                                                class="mt-3 w-100 rounded-3 btn py-2 btn-danger placeOrderBtn">Place
                                                Order</button>
                                        @else
                                            <a href="{{ route('login') }}"
                                                class="mt-3 w-100 rounded-3 btn py-2 btn-danger">Get Login First</a>
                                        @endif
                                    @endif
                                @endforeach
                            </form>
                        </div>

                    </div>
                    <!-- Part 2 -->
                    @php
                        use App\Models\Topping;
                        use App\Models\Branch;
                    @endphp
                    <div class="order-sm-2 order-1 col-md-5 billing">
                        <div class="row justify-content-end mt-0">
                            <div class="col-xl-11 mt-0">
                                <!-- Alert And Location Start -->
                                <div class="border-bottom pb-3 mt-0">
                                            <h5 class="mb-3">PICKUP AT</h5>

                                            @php
                                                $cart = session('cart', []);
                                                $storePickupBranch = null;
                                                $homeDeliveryAddress = null;

                                                foreach ($cart as $item) {
                                                    if (!empty($item['delivery_status'])) {
                                                        if ($item['delivery_status'] == '1') {
                                                            $storePickupBranch = $item['branch_name'] ?? null;
                                                        } elseif ($item['delivery_status'] == '2') {
                                                            $homeDeliveryAddress = $item['delivery_address'] ?? null;
                                                        }
                                                    }
                                                }

                                                $hasPickup = !empty($storePickupBranch);
                                                $hasHomeDelivery = !empty($homeDeliveryAddress);
                                            @endphp

                                            {{-- 🏬 Store Pickup Section --}}
                                            @if ($hasPickup)
                                                @php
                                                    $branch = \App\Models\Branch::where('name', $storePickupBranch)->first();
                                                @endphp
                                                <div class="pb-2">
                                                    <span class="ri-map-pin-line"></span>
                                                    <strong>Pickup:</strong>
                                                    @if ($branch && $branch->location)
                                                        {{ $branch->location }}
                                                    @endif
                                                </div>

                                                {{-- 🕒 Pickup Time --}}
                                                @if ($dateTime = session('time'))
                                                    <div>
                                                        <span class="ri-time-line"></span>
                                                        {{ \Carbon\Carbon::parse($dateTime['date'])->format('d M, Y') }} at {{ $dateTime['time'] }}
                                                    </div>
                                                @else
                                                    @foreach ($timeSlots as $timeSlot)
                                                        <div>
                                                            <span class="ri-time-line"></span>
                                                            Today Pickup: {{ $timeSlot->start_pickup_time }}
                                                        </div>
                                                        @break
                                                    @endforeach
                                                @endif
                                            @endif

                                            {{-- 🚚 Home Delivery Section --}}
                                            @if ($hasHomeDelivery)
                                                <div class="pb-2 mt-2">
                                                    <span class="ri-home-line"></span>
                                                    <strong>Home Delivery:</strong> {{ $homeDeliveryAddress }}
                                                </div>
                                            @endif
                                        </div>

                                @if (Auth::guard('user')->check() && $hasPickup)
                                    <h6 class="mt-3">Vehicle Info (Optional)</h6>
                                    <div class="row vehicle-info">
                                        <div class="col-6 px-2">
                                            <input type="text" id="vehicleColor" name="vehicle_color"
                                                class="form-control" placeholder="Vehicle Color"
                                                style="border-radius: 6px">
                                        </div>
                                        <div class="col-6 px-2">
                                            <input type="text" id="vehicleNumber" name="vehicle_no"
                                                class="form-control" placeholder="Vehicle No #"
                                                style="border-radius: 6px">
                                        </div>
                                    </div>
                                @endif

                                @php
                                    $userId = Auth::guard('user')->id();
                                    $reward = App\Models\Reward::where('user_id', $userId)->first();
                                    $remaining = null;
                                    if ($reward) {
                                        $remaining = $reward->rewards - $reward->redeemed;
                                    }
                                @endphp
                                
                            </div>
                            <!-- Location -->
                            <!-- Blling Start -->
                            @php
                                $cartItems = session('cart', []);
                                // Calculate the total based on the prices of all items
                                $totalQuantity = count($cartItems);
                                $subtotal = 0;

                                foreach ($cartItems as $item) {
                                    // @dd($item['branch_id.tax']);
                                    $subtotal += floatval($item['price'] * $item['quantity']);
                                    // $subtotal += floatval(trim($item['price'])) * floatval($item['quantity']);

                                    // Check if 'topping_prices' index exists in the $item array

                                    if (isset($item['toppings_by_category'])) {
                                        foreach ($item['toppings_by_category'] as $category => $toppingIds) {
                                            foreach ($toppingIds as $toppingId) {
                                                // Assuming you have a Toppings model with a 'price' column
                                                $topping = Topping::find($toppingId);
                                                if ($topping) {
                                                    $subtotal += $topping->price;
                                                }
                                            }
                                        }
                                    }
                                }
                                // $tip_amount = session('tip_amount', []);
                                $tip_amount = session('tip_amount', 0);
                                $tax = 0; // Initialize tax variable
                                foreach ($branchess as $index => $branch) {
                                    if ($branch->status == 1) {
                                        $tax = $branch->tax; // Assign branch tax to $tax variable
                                    }
                                }

                                // Assuming a fixed tax amount
                                $tip = $tip_amount;
                                // @dd($tip);
                                // $tip = is_array($tip_amount) ? 0 : $tip_amount;
                                // $orderTotal = $subtotal + $tax + $tip;
                                $orderTotal = $subtotal + $tax + $tip;
                                session(['orderTotal' => $orderTotal]);
                            @endphp

                            <div class="mt-sm-4 pb-sm-4 mt-3 pb-3">

                                <!-- Subtotal -->
                                <div class="d-flex justify-content-between">
                                    <p class="text-muted">Sub Total</p>
                                    <p class="sub-total">£{{ number_format($subtotal, 2) }}</p>
                                </div>
                                <!-- Estimated taxes -->
                                <div class="d-flex justify-content-between">
                                    <p class="text-muted">Estimated taxes (New York)</p>
                                    <p class="tax-value">£{{ number_format($tax, 2) }}</p>
                                </div>
                                <!-- Tip -->
                                <div class="d-flex justify-content-between">
                                    <p class="text-muted">Tip</p>
                                    <p class="tip-value">£{{ number_format($tip, 2) }}</p>
                                </div>
                                <!-- Estimated order total -->
                                <div class="d-flex justify-content-between">
                                    <p class="text-muted">Estimated order total</p>
                                    <p class="total-value">£{{ number_format($orderTotal, 2) }}</p>
                                </div>
                            </div>
                            <p>Additional taxes and fees will be calculated at checkout</p>

                            {{-- End --}}
                            {{--
                            <button class="mt-3 w-100 rounded-3 btn py-2 btn-danger creditCard-btn"
                                id="placeOrderBtn">Place Order</button> --}}
                            </form>
                            <button
                                class="mt-3 w-100  justify-content-center align-items-center gap-1 rounded-3 btn py-2 btn-dark cashPay-btn"
                                hidden><span class="h2 m-0 p-0"><svg data-v-6959bfec="" data-v-7c3b755e=""
                                        width="34" height="24" viewBox="0 0 34 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" svg-inline="" role="presentation"
                                        focusable="false" tabindex="-1" class="pay-with-cash-payment-option__icon">
                                        <path data-v-6959bfec="" data-v-7c3b755e=""
                                            d="M0 4a4 4 0 014-4h26a4 4 0 014 4v16a4 4 0 01-4 4H4a4 4 0 01-4-4V4z"
                                            fill="#00D632"></path>
                                        <path data-v-6959bfec="" data-v-7c3b755e="" fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M17.485 8.176c1.14 0 2.232.462 2.946 1.095.18.16.45.16.62-.012l.85-.862a.443.443 0 00-.021-.645 6.716 6.716 0 00-2.277-1.28l.266-1.27a.445.445 0 00-.435-.535h-1.642a.445.445 0 00-.435.352l-.24 1.128c-2.182.109-4.031 1.198-4.031 3.432 0 1.933 1.53 2.762 3.145 3.336 1.53.573 2.337.786 2.337 1.594 0 .828-.807 1.317-1.997 1.317-1.085 0-2.222-.358-3.104-1.228a.443.443 0 00-.623 0l-.913.9a.448.448 0 00.003.639c.712.69 1.613 1.19 2.641 1.47l-.25 1.176a.445.445 0 00.432.538l1.644.012a.444.444 0 00.438-.353l.238-1.13c2.612-.162 4.211-1.582 4.211-3.66 0-1.912-1.593-2.72-3.527-3.379-1.105-.404-2.061-.68-2.061-1.508 0-.808.892-1.127 1.785-1.127z"
                                            fill="#fff"></path>
                                    </svg></span>
                                Cash App Pay</button>
                            <button class="mt-3 w-100 rounded-3 btn text-dark py-1 afterPay-btn"
                                style="background-color:rgb(175, 252, 240);" hidden>Continue with <span><svg
                                        class="app" data-v-8f3aa612="" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="300 225 1250 400" svg-inline="" aria-hidden="true" tabindex="-1"
                                        role="presentation" focusable="false">
                                        <path data-v-8f3aa612=""
                                            d="M1492 353.5l-34.6-19.8-35.1-20.1c-23.2-13.3-52.2 3.4-52.2 30.2v4.5c0 2.5 1.3 4.8 3.5 6l16.3 9.3c4.5 2.6 10.1-.7 10.1-5.9V347c0-5.3 5.7-8.6 10.3-6l32 18.4 31.9 18.3c4.6 2.6 4.6 9.3 0 11.9l-31.9 18.3-32 18.4c-4.6 2.6-10.3-.7-10.3-6V415c0-26.8-29-43.6-52.2-30.2l-35.1 20.1-34.6 19.8c-23.3 13.4-23.3 47.1 0 60.5l34.6 19.8 35.1 20.1c23.2 13.3 52.2-3.4 52.2-30.2v-4.5c0-2.5-1.3-4.8-3.5-6l-16.3-9.3c-4.5-2.6-10.1.7-10.1 5.9v10.7c0 5.3-5.7 8.6-10.3 6l-32-18.4-31.9-18.3c-4.6-2.6-4.6-9.3 0-11.9l31.9-18.3 32-18.4c4.6-2.6 10.3.7 10.3 6v5.3c0 26.8 29 43.6 52.2 30.2l35.1-20.1L1492 414c23.3-13.5 23.3-47.1 0-60.5zM1265 360.1l-81 167.3h-33.6l30.3-62.5-47.7-104.8h34.5l30.6 70.2 33.4-70.2h33.5zM455.1 419.5c0-20-14.5-34-32.3-34s-32.3 14.3-32.3 34c0 19.5 14.5 34 32.3 34s32.3-14 32.3-34m.3 59.4v-15.4c-8.8 10.7-21.9 17.3-37.5 17.3-32.6 0-57.3-26.1-57.3-61.3 0-34.9 25.7-61.5 58-61.5 15.2 0 28 6.7 36.8 17.1v-15h29.2v118.8h-29.2zM626.6 452.5c-10.2 0-13.1-3.8-13.1-13.8V386h18.8v-25.9h-18.8v-29h-29.9v29H545v-11.8c0-10 3.8-13.8 14.3-13.8h6.6v-23h-14.4c-24.7 0-36.4 8.1-36.4 32.8v15.9h-16.6V386h16.6v92.9H545V386h38.6v58.2c0 24.2 9.3 34.7 33.5 34.7h15.4v-26.4h-5.9zM734 408.8c-2.1-15.4-14.7-24.7-29.5-24.7-14.7 0-26.9 9-29.9 24.7H734zm-59.7 18.5c2.1 17.6 14.7 27.6 30.7 27.6 12.6 0 22.3-5.9 28-15.4h30.7c-7.1 25.2-29.7 41.3-59.4 41.3-35.9 0-61.1-25.2-61.1-61.1 0-35.9 26.6-61.8 61.8-61.8 35.4 0 61.1 26.1 61.1 61.8 0 2.6-.2 5.2-.7 7.6h-91.1zM956.5 419.5c0-19.2-14.5-34-32.3-34-17.8 0-32.3 14.3-32.3 34 0 19.5 14.5 34 32.3 34 17.8 0 32.3-14.7 32.3-34m-94.1 107.9V360.1h29.2v15.4c8.8-10.9 21.9-17.6 37.5-17.6 32.1 0 57.3 26.4 57.3 61.3s-25.7 61.5-58 61.5c-15 0-27.3-5.9-35.9-15.9v62.5h-30.1zM1091.7 419.5c0-20-14.5-34-32.3-34-17.8 0-32.3 14.3-32.3 34 0 19.5 14.5 34 32.3 34 17.8 0 32.3-14 32.3-34m.3 59.4v-15.4c-8.8 10.7-21.9 17.3-37.5 17.3-32.6 0-57.3-26.1-57.3-61.3 0-34.9 25.7-61.5 58-61.5 15.2 0 28 6.7 36.8 17.1v-15h29.2v118.8H1092zM809.7 371.7s7.4-13.8 25.7-13.8c7.8 0 12.8 2.7 12.8 2.7v30.3s-11-6.8-21.1-5.4c-10.1 1.4-16.5 10.6-16.5 23v70.3h-30.2V360.1h29.2v11.6z">
                                        </path>
                                    </svg></span></button>
                        </div>
                        <!-- Billing ENd -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
@endsection
@section('js')
@if (\Illuminate\Support\Facades\Session::has('message'))
    <script>
        toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
    </script>
@endif




@endsection
