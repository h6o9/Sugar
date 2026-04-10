@extends('home.layout.app')
@section('title', 'Login')
@section('content')
    <style>
        .order-card .cart-counter {
            font-size: 11px;
            width: 18px;
            height: 18px;
            display: flex;
            justify-content: center;
        }

        .sm-card {
            display: flex;
        }

        .sm-card img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .sm-badge {
            position: absolute;
            top: -5%;
            right: -5%;
            background-color: black;
            padding: 2px;
            color: white;
            border-radius: 40%;
        }

        .order-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .order-row .part-1 {
            width: 65%;
        }

        .order-row .part-2 {
            width: 30%;
        }

        .order-container:last-child {
            padding-bottom: 0 !important;
            border-bottom: 0 !important;
        }

        .order-container:first-child {
            padding-top: 0 !important;
        }

        @media(max-width:991px) {
            .order-row {
                flex-direction: column;
            }

            .order-row .part-1 {
                width: 99%;
                margin: auto;
            }

            .order-row .part-2 {
                margin: auto;
                width: 99%;
            }
        }
    </style>
    <section class="section">
        <div class="container-xxl bg-white p-0">
            <div class="container-xxl position-relative p-0">
                <div class="container-xxl py-5 bg-primary hero-header mb-5">
                    <div class="container text-center my-lg-5 pt-lg-5 pb-lg-4">
                        <h1 class="display-3 text-dark mb-3 animated slideInDown">My Orders</h1>
                    </div>
                </div>
            </div>
            <!--Cart Pending Section Start -->

            @php
                $totalAmount = 0;
            @endphp
            <div class="container-fluid cart wow fadeIn" data-wow-delay="0.1s">
                <div class="order-container">

                    @foreach ($orders->groupBy('order.code') as $orderGroup)
                        @php
                            $order = $orderGroup->first(); // Get the first order in the group
                        @endphp
                        @if ($order->order->user_id == Auth::guard('user')->id())
                            @if ($order->order->status === 'Order Ready')
                                <div class="col-12 col-sm-11 mx-auto py-4 py-lg-5 order-block">
                                    <!-- Diviser -->
                                    <div class="order-row justify-content-between">
                                        <!-- Cards Parent -->
                                        <div class="part-1">
                                            <div class="order-card">
                                                <div class="d-flex justify-content-between">
                                                    <h4>Order #<span>{{ $order->order->code }}</span></h4>
                                                    <span
                                                        class="text-{{ $order->order->status === 'Delivered' ? 'success' : 'danger' }} h5 fw-bold">
                                                        ({{ $order->order->status }})
                                                    </span>
                                                </div>
                                                <!-- small Card parent -->
                                                <div class="row parent-ofCards">
                                                    <!-- small cards -->
                                                    @foreach ($orderGroup as $item)
                                                        <div class="mt-sm-4 mt-3 col-xl-4 py-2 col-lg-6 col-md-4 col-sm-6">
                                                            <div class="sm-card">
                                                                <div class="position-relative">
                                                                    <img src="{{ asset($item->product->image) }}"
                                                                        alt="">
                                                                    <span
                                                                        class="badge cart-counter">{{ $item->quantity }}</span>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <h5 class="m-0">{{ $item->product->name }}
                                                                        <span style="font-size:12px">
                                                                            @if ($item->product_size && $item->product_size !== 'NULL')
                                                                                ({{ $item->product_size }})
                                                                            @endif
                                                                        </span>
                                                                    </h5>
                                                                    <h6 class="small">
                                                                        (£{{ $item->product->price ? $item->product->price : $item->product_price }})
                                                                    </h6>
                                                                    {{-- Toppings  --}}
                                                                    @if ($item->orderToppings->isEmpty() || $order->orderToppings->where('toppings', '!=', null)->isEmpty())
                                                                        <div
                                                                            class="badge p-2 badge-shadow btn-danger text-white">
                                                                            No Topping
                                                                        </div>
                                                                    @else
                                                                        @foreach ($item->orderToppings->groupBy('category_id') as $categoryId => $orderItems)
                                                                            @php
                                                                                $categoryName = $orderItems->first()
                                                                                    ->category->name;
                                                                            @endphp
                                                                            <h6 class="small m-0">{{ $categoryName }}:</h6>
                                                                            @foreach ($orderItems as $orderItem)
                                                                                @if ($orderItem->toppings)
                                                                                    <p class="small m-0">
                                                                                        {{ $orderItem->toppings->name }}
                                                                                        (£{{ $orderItem->toppings->price }})
                                                                                    </p>
                                                                                @else
                                                                                    <div
                                                                                        class="badge p-2 badge-shadow btn-danger text-white">
                                                                                        No Topping
                                                                                    </div>
                                                                                @endif
                                                                            @endforeach
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                       <div class="part-2 mt-3 mt-lg-0">
                                            <div>
                                                <div class="row justify-content-end">
                                                    <div>
                                                        <div class="border-bottom pb-2">
                                                            <span class=""><span class="ri-map-pin-line"> </span>
                                                                Pickup: {{ $order->branch->location }}</span><br>
                                                            <span class=""><span class="ri-time-line"> </span>
                                                                @if ($order->order->date)
                                                                    {{ \Carbon\Carbon::parse($order->order->date)->format('d M, Y') }}
                                                                    at {{ $order->order->time }}
                                                                @else
                                                                    {{ \Carbon\Carbon::parse($order->order->created_at)->format('d M, Y') }}
                                                                    at {{ $order->order->time }}
                                                                @endif
                                                            </span>

                                                            @php
                                                                // Collect unique non-empty delivery addresses from order items
                                                                $deliveryAddresses = $orderGroup->pluck('delivery_address')->filter()->unique();
                                                            @endphp

                                                            @if($deliveryAddresses->isNotEmpty())
                                                                <div class="mt-3">
                                                                    <span class=""><span class="ri-home-4-line"></span> Home Delivery:</span>
                                                                    @foreach($deliveryAddresses as $addr)
                                                                        <div class="text-muted">{{ $addr }}</div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                            </div>
                        <!-- Billing Start -->
                        <div class="mt-2">
                            <div class="d-flex justify-content-between">
                                <p class="text-muted m-0">Total Amount</p>
                                <p class="total-value m-0">£{{ $order->order->total_amount - $order->tip - $order->branch->tax }}
                                </p>
                            </div>
                            <div class="d-flex justify-content-between">
                                <p class="text-muted mb-1">Estimated taxes (New York)</p>
                                <p class="tax-value mb-1">£{{ $order->branch->tax }}</p>
                            </div>
                            <div class="d-flex justify-content-between">
                                <p class="text-muted mb-1">Tip</p>
                                @if ($order->tip)
                                    <p class="tip-value mb-1">£{{ $order->tip }}</p>
                                @else
                                    <p class="tip-value mb-1">£0</p>
                                @endif
                            </div>
                            @php
                                $total = $order->order->total_amount;
                            @endphp
                            <div class="d-flex justify-content-between">
                                <p class="text-muted m-0">Estimated item total</p>
                                <p class="total-value m-0">£{{ $total }}</p>
                            </div>
                        </div>
                        <!-- Billing End -->
        </div>
        </div>
        </div>
        </div>
        </div>
        @php
            $totalAmount = 0;
        @endphp
        @endif
        @endif
        @endforeach
        </div>
        </div>

        @php
            $totalAmount = 0;
        @endphp
        <div class="container-fluid cart wow fadeIn" data-wow-delay="0.1s">
            <div class="order-container">

                @foreach ($orders->groupBy('order.code') as $orderGroup)
                    @php
                        $order = $orderGroup->first(); // Get the first order in the group
                    @endphp
                    @if ($order->order->user_id == Auth::guard('user')->id())
                        @if ($order->order->status === 'Pending')
                            <div class="col-12 col-sm-11 mx-auto py-4 py-lg-5 order-block">
                                <!-- Diviser -->
                                <div class="order-row justify-content-between">
                                    <!-- Cards Parent -->
                                    <div class="part-1">
                                        <div class="order-card">
                                            <div class="d-flex justify-content-between">
                                                <h4>Order #<span>{{ $order->order->code }}</span></h4>
                                                <span
                                                    class="text-{{ $order->order->status === 'Delivered' ? 'success' : 'danger' }} h5 fw-bold">
                                                    ({{ $order->order->status }})
                                                </span>
                                            </div>
                                            <!-- small Card parent -->
                                            <div class="row parent-ofCards">
                                                <!-- small cards -->
                                                @foreach ($orderGroup as $item)
                                                    <div class="mt-sm-4 mt-3 col-xl-4 py-2 col-lg-6 col-md-4 col-sm-6">
                                                        <div class="sm-card">
                                                            <div class="position-relative">
                                                                <img src="{{ asset($item->product->image) }}"
                                                                    alt="">
                                                                <span
                                                                    class="badge cart-counter">{{ $item->quantity }}</span>
                                                            </div>
                                                            <div class="ms-3">
                                                                <h5 class="m-0">{{ $item->product->name }}
                                                                    <span style="font-size:12px">
                                                                        @if ($item->product_size && $item->product_size !== 'NULL')
                                                                            ({{ $item->product_size }})
                                                                        @endif
                                                                    </span>
                                                                </h5>
                                                                <h6 class="small">
                                                                    (£{{ $item->product->price ? $item->product->price : $item->product_price }})
                                                                </h6>
                                                                 {{-- 🏬 Pickup Info --}}
                                                                    @if ($item->delivery_status == '1' && $item->branch)
                                                                        <div class="mt-1 text-muted" style="font-size: 13px;">
                                                                            <span class="ri-store-2-line"></span>
                                                                            <strong>Pickup:</strong>
                                                                            <p>{{ $item->branch->location ?? 'N/A' }}</p>
                                                                        </div>
                                                                    @endif

                                                                    {{-- 🚚 Home Delivery Info --}}
                                                                    @if ($item->delivery_status == '2' && !empty($item->delivery_address))
                                                                        <div class="mt-1 text-muted" style="font-size: 13px;">
                                                                            <span class="ri-home-line"></span>
                                                                            <strong>Home Delivery:</strong>
                                                                            <p>{{ $item->delivery_address }}</p>
                                                                        </div>
                                                                    @endif
                                                                {{-- Toppings  --}}
                                                                @if ($item->orderToppings->isEmpty() || $order->orderToppings->where('toppings', '!=', null)->isEmpty())
                                                                    <div
                                                                        class="badge p-2 badge-shadow btn-danger text-white">
                                                                        No Topping
                                                                    </div>
                                                                @else
                                                                    @foreach ($item->orderToppings->groupBy('category_id') as $categoryId => $orderItems)
                                                                        @php
                                                                            $categoryName = $orderItems->first()
                                                                                ->category->name;
                                                                        @endphp
                                                                        <h6 class="small m-0">{{ $categoryName }}:</h6>
                                                                        @foreach ($orderItems as $orderItem)
                                                                            @if ($orderItem->toppings)
                                                                                <p class="small m-0">
                                                                                    {{ $orderItem->toppings->name }}
                                                                                    (£{{ $orderItem->toppings->price }})
                                                                                </p>
                                                                            @else
                                                                                <div
                                                                                    class="badge p-2 badge-shadow btn-danger text-white">
                                                                                    No Topping
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    @endforeach
                                                                @endif
                                                                {{-- ✅ Complementary Product --}}
                                                                @if ($item->complementaryProduct)
                                                                    <div class="d-flex flex-column align-items-start mt-2">
                                                                        <span class="badge bg-success my-2">Included (Free)</span>
                                                                        <img 
                                                                            src="{{ asset($item->complementaryProduct->image) }}" 
                                                                            alt="{{ $item->complementaryProduct->name }}" 
                                                                            width="40" 
                                                                            height="40"
                                                                            style="object-fit: cover" class="rounded-circle"
                                                                        >
                                                                        <p class="badge bg-success my-2">{{ $item->complementaryProduct->name }}</p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="part-2 mt-3 mt-lg-0">
                                        <div>
                                            <div class="row justify-content-end">
                                                <div>
                                                   {{-- 🏬 Pickup Section --}}
                                               @php
                                                        // Fetch all items for this specific order
                                                        $orderItems = \App\Models\OrderItem::where('order_id', $order->order->id)->get();

                                                        $storePickupBranch = null;
                                                        $homeDeliveryAddress = null;

                                                        foreach ($orderItems as $item) {
                                                            // If item has a delivery_status
                                                            if (!empty($item->delivery_status)) {
                                                                if ($item->delivery_status == '1' && $item->branch) {
                                                                    // Store pickup branch location
                                                                    $storePickupBranch = $item->branch->location ?? null;
                                                                } elseif ($item->delivery_status == '2' && !empty($item->delivery_address)) {
                                                                    // Home delivery address
                                                                    $homeDeliveryAddress = $item->delivery_address;
                                                                }
                                                            }
                                                        }

                                                        $hasPickup = !empty($storePickupBranch);
                                                        $hasHomeDelivery = !empty($homeDeliveryAddress);
                                                    @endphp


                                                    <div class="border-bottom pb-2">
                                                        {{-- 🧾 Header --}}
                                                        <div class="d-flex align-content-center justify-content-between">
                                                            <p class="pb-0 text-dark fw-bold">HOW TO GET IT</p>
                                                        </div>

                                                        {{-- 🏬 Store Pickup --}}
                                                        @if ($hasPickup)
                                                            <div class="pickup-section mt-2">
                                                                <span class="pb-2 d-block">
                                                                    <span class="ri-store-2-line"></span>
                                                                    <strong>Pickup:</strong>
                                                                    {{ $storePickupBranch }}
                                                                </span>

                                                                {{-- 🕒 Date and Time --}}
                                                                @if ($order->order->date)
                                                                    <span class="ri-time-line"></span>
                                                                    {{ \Carbon\Carbon::parse($order->order->date)->format('d M, Y') }}
                                                                    at {{ $order->order->time }}
                                                                @else
                                                                    <span class="ri-time-line"></span>
                                                                    {{ \Carbon\Carbon::parse($order->order->created_at)->format('d M, Y') }}
                                                                    at {{ $order->order->time }}
                                                                @endif
                                                            </div>
                                                        @endif

                                                        {{-- 🚚 Home Delivery --}}
                                                        @if ($hasHomeDelivery)
                                                            <div class="home-delivery-section mt-3">
                                                                <span class="pb-2 d-block">
                                                                    <span class="ri-home-line"></span>
                                                                    <strong>Home Delivery:</strong>
                                                                    {{ $homeDeliveryAddress }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <!-- Billing Start -->
                                                <div class="mt-2">
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted m-0">Sub Total</p>
                                                        <p class="total-value m-0">
                                                            £{{ $order->order->total_amount - $order->tip - $order->branch->tax - ($order->order->delivery_charge ?? 0) }}
                                                        </p>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted mb-1">Estimated taxes (New York)</p>
                                                        <p class="tax-value mb-1">£{{ $order->branch->tax }}</p>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted mb-1">Tip</p>
                                                        @if ($order->tip)
                                                            <p class="tip-value mb-1">£{{ $order->tip }}</p>
                                                        @else
                                                            <p class="tip-value mb-1">£0</p>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted mb-1">Redeemed Amount</p>
                                                       <p class="redeemed-value mb-1">
                                                            £{{ $order->order->redeemed }}
                                                        </p>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted mb-1">Delivery Charges</p>
                                                        <p class="delivery-charge mb-1">£{{ number_format($order->order->delivery_charge ?? 0, 2) }}</p>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted m-0">Estimated order total</p>
                                                        <p class="total-value m-0">£{{ $order->order->total_amount }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <!-- Billing End -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $totalAmount = 0;
                            @endphp
                        @endif
                    @endif
                @endforeach
            </div>
        </div>
        <!--Cart Deliverd Section Start -->
        @php
            $totalAmount = 0;
        @endphp
        <div class="container-fluid cart wow fadeIn" data-wow-delay="0.1s">
            <div class="order-container">

                @foreach ($orders->groupBy('order.code') as $orderGroup)
                    @php
                        $order = $orderGroup->first(); // Get the first order in the group
                    @endphp
                    @if ($order->order->user_id == Auth::guard('user')->id())
                        @if ($order->order->status === 'Delivered')
                            <div class="col-12 col-sm-11 mx-auto py-4 py-lg-5 order-block">
                                <!-- Diviser -->
                                <div class="order-row justify-content-between">
                                    <!-- Cards Parent -->
                                    <div class="part-1">
                                        <div class="order-card">
                                            <div class="d-flex justify-content-between">
                                                <h4>Order #<span>{{ $order->order->code }}</span></h4>
                                                <span
                                                    class="text-{{ $order->order->status === 'Delivered' ? 'success' : 'danger' }} h5 fw-bold">
                                                    ({{ $order->order->status }})
                                                </span>
                                            </div>
                                            <!-- small Card parent -->
                                            <div class="row parent-ofCards">
                                                <!-- small cards -->
                                                @foreach ($orderGroup as $item)
                                                    <div class="mt-sm-4 mt-3 col-xl-4 py-2 col-lg-6 col-md-4 col-sm-6">
                                                        <div class="sm-card">
                                                            <div class="position-relative">
                                                                <img src="{{ asset($item->product->image) }}"
                                                                    alt="">
                                                                <span
                                                                    class="badge cart-counter">{{ $item->quantity }}</span>
                                                            </div>
                                                            <div class="ms-3">
                                                                <h5 class="m-0">{{ $item->product->name }}
                                                                    <span style="font-size:12px">
                                                                        @if ($item->product_size && $item->product_size !== 'NULL')
                                                                            ({{ $item->product_size }})
                                                                        @endif
                                                                    </span>
                                                                </h5>
                                                                <h6 class="small">
                                                                    (£{{ $item->product->price ? $item->product->price : $item->product_price }})
                                                                </h6>
                                                                {{-- Toppings  --}}
                                                                @if ($item->orderToppings->isEmpty() || $order->orderToppings->where('toppings', '!=', null)->isEmpty())
                                                                    <div
                                                                        class="badge p-2 badge-shadow btn-danger text-white">
                                                                        No Topping
                                                                    </div>
                                                                @else
                                                                    @foreach ($item->orderToppings->groupBy('category_id') as $categoryId => $orderItems)
                                                                        @php
                                                                            $categoryName = $orderItems->first()
                                                                                ->category->name;
                                                                        @endphp
                                                                        <h6 class="small m-0">{{ $categoryName }}:</h6>
                                                                        @foreach ($orderItems as $orderItem)
                                                                            @if ($orderItem->toppings)
                                                                                <p class="small m-0">
                                                                                    {{ $orderItem->toppings->name }}
                                                                                    (£{{ $orderItem->toppings->price }})
                                                                                </p>
                                                                            @else
                                                                                <div
                                                                                    class="badge p-2 badge-shadow btn-danger text-white">
                                                                                    No Topping
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="part-2 mt-3 mt-lg-0">
                                        <div>
                                            <div class="row justify-content-end">
                                                <div>
                                                    <div class="border-bottom pb-2">
                                                        <span class=""><span class="ri-map-pin-line"> </span>
                                                            Pickup: {{ $order->branch->location }}</span><br>
                                                        @if ($order->order->date)
                                                            {{ \Carbon\Carbon::parse($order->order->updated_at)->format('d M, Y') }}
                                                            at {{ $order->order->time }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <!-- Billing Start -->
                                                <div class="mt-2">
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted m-0">Total Amount</p>
                                                        <p class="total-value m-0">
                                                            £{{ $order->order->total_amount - $order->tip - $order->branch->tax }}
                                                        </p>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted mb-1">Estimated taxes (New York)</p>
                                                        <p class="tax-value mb-1">£{{ $order->branch->tax }}</p>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted mb-1">Tip</p>
                                                        @if ($order->tip)
                                                            <p class="tip-value mb-1">£{{ $order->tip }}</p>
                                                        @else
                                                            <p class="tip-value mb-1">£0</p>
                                                        @endif
                                                    </div>
                                                    @php
                                                        $total = $order->order->total_amount;
                                                    @endphp
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted m-0">Estimated item total</p>
                                                        <p class="total-value m-0">£{{ $total }}</p>
                                                    </div>
                                                </div>
                                                <!-- Billing End -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $totalAmount = 0;
                            @endphp
                        @endif
                    @endif
                @endforeach
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
