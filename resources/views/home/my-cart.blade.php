@extends('home.layout.app')
@section('title', 'Login')
@section('content')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/css/pikaday.min.css">
    <style>
        .pika-button:hover {
            color: white;
            background-color: #9e0e1b;
        }

        .time-modal-button {
            background-color: #f1f1f1;

        }

        .calender {
            position: absolute;
            top: 50%;
            right: 1%;
            transform: translate(-50%, -50%);
        }

        .time-radios {
            border-bottom: 1px solid black;
        }

        .time-radios:last-of-type {
            border-bottom: none;
        }

        .cart .order_input {
            border: none;
            width: 40px;
        }

        .cart .order_input:focus {
            outline: none;
        }

        .cart .product_img {
            height: 80px;
            width: 80px;
            object-fit: cover;
        }

        .btn_input {
            margin-left: 80px;
        }

        .billing {
            position: sticky;
            top: 125px;
        }

        .cart_card img {
            height: 75px;
            width: 75px;
            object-fit: cover;
            border-radius: 0;
        }

        .cart_input {
            display: inline-block;
            border: none;
            background: inherit;
        }

        .cart_input:focus {
            outline: none;
        }

        .add_more {
            color: red;
            border: 1px solid red;
            display: block;
            text-align: center;
        }

        .alert p {
            font-size: 14px;
        }

        .common-btn {
            color: black;
            background-color: rgb(241, 240, 240);
        }

        .cart_card .cart_input {
            width: 50px;
        }

        .btn-parent {
            display: flex;
            justify-content: space-between;
        }

        .btn-parent .btn {
            width: 23%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 0%;
        }

        .tip-input-container {
            display: flex;
            justify-content: space-between;
        }

        .tip-input-container.coupan span {
            width: 100%;
        }

        .tip-input-container .btn {
            display: inline-block;
            width: calc(100% - 77%);
            display: flex;
            justify-content: center;
        }

        /* Buttons that increase and decrease the amount order-items */

        .item-btn {
            background-color: rgba(0, 0, 0, 0.1);
            font-size: 20px;
            width: 35px;
            height: 35px;
        }

        /* remove increment buttons from the input */

        .increment-input::-webkit-inner-spin-button,
        .increment-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .increment-input {
            -moz-appearance: textfield;
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
    </style>
    <section class="section">
        <div class="container-xxl bg-white p-0">
            <div class="container-xxl position-relative p-0">
                <div class="container-xxl py-5 bg-primary hero-header mb-5">
                    <div class="container text-center my-lg-5 pt-lg-5 pb-lg-4">
                        <h1 class="display-3 text-dark mb-3 animated slideInDown">Your Cart</h1>
                    </div>
                </div>
            </div>
            <!-- Navbar & Hero End -->
            <!-- Modals Start -->
            @php
                use App\Models\Branch;
            @endphp
            <div class="container-fluid time-modal">
                <div class="modal fade" id="time-Modal" tabindex="-1" aria-labelledby="time-Modal" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header p-3">
                                <button data-bs-toggle="modal" 
                                    class="text-start locattion-button-show-btn rounded rounded-3 time-modal-button btn px-3 py-2 col-12">
                                    <div class="d-flex justify-content-between col-12">
                                        <div>
                                            <h6 class="my-2">

                                                <span class="d-flex">
                                                    @foreach ($branchess as $index => $branch)
                                                        @if ($branch->status == 1)
                                                            <span class="ri-map-pin-line me-2"></span>
                                                            Pickup:<p class="small fw-bold m-0 sel-location mt-0" id="storePickupSection{{ $branch->id }}">
                                                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($branch->location) }}" 
                                                                target="_blank" 
                                                                style="text-decoration: none; color: inherit;">
                                                                    {{ $branch->location }}
                                                                </a>
                                                            </p>
                                                        @endif
                                                    @endforeach
                                                </span>

                                            </h6>
                                        </div>
                                        <h6 class="my-2"><span class="fa fa-solid fa-location-arrow"></span></h6>
                                    </div>
                                </button>
                            </div>
                            <div class="modal-body pt-4 pb-2 m-0 scrollable">
                                <div class="form-group">
                                    <label for="datepicker">
                                        <h6>Select Pickup Time</h6>
                                    </label>
                                    <div class="position-relative">
                                        @if ($dateTime = session('time'))
                                            <input class="form-control rounded py-2 rounded-3 time_input" id="datepicker"
                                                type="text" name="date_input" value="{{ $dateTime['date'] }}">
                                            <span class="fa fa-calendar calender"></span>
                                        @else
                                            <input class="form-control rounded py-2 rounded-3 time_input" id="datepicker"
                                                type="text" name="date_input">
                                            <span class="fa fa-calendar calender"></span>
                                        @endif
                                    </div>
                                </div>

                                @for ($i = 0; $i < count($timeSlots); $i++)
                                    @php
                                        $startTime = \Carbon\Carbon::parse($timeSlots[$i]['start_pickup_time']); // Parse start time
                                        $endTime = \Carbon\Carbon::parse($timeSlots[$i]['end_pickup_time']); // Parse end time
                                        if ($endTime < $startTime) {
                                            $endTime->addDays(1);
                                        }
                                    @endphp
                                    @while ($startTime <= $endTime)
                                        @php
                                         $a = $i ++
                                        @endphp
                                        {{-- Use comparison operator instead of assignment --}}
                                        <div class="d-flex time-radios align-items-center py-3">
                                            <input class="form-check-input me-2" type="radio"
                                                id="time-radio-2-{{ $a }}"
                                                value="{{ $startTime->format('h:i A') }}" name="time-radio">
                                            <label for="time-radio-2-{{ $a }}" class="ms-1">
                                                <h6 class="m-0">{{ $startTime->format('h:i A') }}</h6>
                                            </label>
                                        </div>

                                        @php
                                            $startTime->addMinutes(15); // Add 15 minutes to start time
                                        @endphp
                                    @endwhile
                                @endfor
                                {{-- Time Slots Start End --}}
                            </div>
                            <div class="modal-footer position-relative px-2">
                                <button type="button" style="font-size:30px; position: absolute; left: 0;"
                                    class="btn time-modal-close ri-close-circle-line btn-danger px-2 ms-3 py-0"></button>
                                <div class="text-center mx-auto">
                                    <button class="btn btn-danger px-5 updateTimeBtn">Update Time</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Time Modal Close -->
            <!-- Location Modal Start -->
            <div class="container-fluid cart wow fadeIn" data-wow-delay="0.1s">
                <div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header p-2">
                                <ul class="nav nav-pills w-100" id="pills-tab" role="tablist">
                                    <li class="nav-item w-100" role="presentation">
                                        <button class="nav-link rounded-0 w-100 active" id="pills-pickup-tab"
                                            data-bs-toggle="pill" data-bs-target="#pills-pickup" type="button"
                                            role="tab" aria-controls="pills-pickup" aria-selected="true">Pickup</button>
                                    </li>
                                    {{-- <li class="nav-item w-50" role="presentation">
                                        <button class="nav-link rounded-0 w-100" id="pills-delivery-tab"
                                            data-bs-toggle="pill" data-bs-target="#pills-delivery" type="button"
                                            role="tab" aria-controls="pills-delivery"
                                            aria-selected="false">Delivery</button>
                                    </li> --}}
                                </ul>
                            </div>
                            <div class="modal-body py-0 my-0 scrollable">
                                <div class="tab-content" id="pills-tabContent">
                                    <div class="tab-pane fade show active" id="pills-pickup" role="tabpanel"
                                        aria-labelledby="pills-pickup-tab">
                                        <div class="py-4 border-bottom">
                                            <h5>Select Location</h5>
                                            <p><span class="ri-map-pin-rangex-fill"></span> Find the Nearest Store</p>
                                            <span
                                                class="location-span border rounded rounded-3 px-2 d-flex w-100 justify-content-between align-items-center"><input
                                                    class="cart_input py-2 w-100"
                                                    placeholder="Search by city, state, or ZIP" type="text"><span
                                                    class="ri-send-plane-fill"></span></span>
                                        </div>
                                        <!-- Location's Parent  Start-->
                                        <div class="">
                                            <!-- Location-2 -->
                                            @php

                                                $locationImages = [
                                                    asset('public/img/location-img1.png'),
                                                    asset('public/img/location-img2.png'),
                                                    asset('public/img/location-img3.png'),
                                                    asset('public/img/location-img4.png'),
                                                    // Add more images as needed
                                                ];
                                            @endphp

                                            {{-- @dd($branches); --}}
                                            {{-- @foreach ($branchess as $index => $branch)
                                                <div
                                                    class="d-flex justify-content-between location-card border-bottom py-3">
                                                    <div class="d-flex align-items-start">
                                                        <input class="form-check-input me-2 location-radio" type="radio"
                                                            id="location-{{ $branch->id }}" name="choosen_location"
                                                            data-branch-id="{{ $branch->id }}">
                                                        <label for="location-{{ $branch->id }}"
                                                            class="ms-1 branch-location-parent">
                                                            <h6 class="small fw-bold m-0">{{ $branch->name }}</h6>
                                                            <p class="small fw-bold branch-location m-0">
                                                                {{ $branch->location }}</p>
                                                            <p class="small fw-bold m-0">Bronx, NY 10461</p>
                                                            <b class="text-success m-0">Item Available</b>
                                                        </label>
                                                    </div>
                                                    <a class="location-description" data-bs-toggle="modal"
                                                        data-bs-target="#locationDescription" style="cursor: pointer">
                                                        <!-- Use the loop index to select the corresponding image -->
                                                        <img class="location-img" src="{{ $locationImages[$index] }}"
                                                            alt="location-img{{ $index + 1 }}">
                                                        <p class="small m-0">+100 miles</p>
                                                        <p class="text-danger">Store Info</p>
                                                    </a>
                                                </div>
                                            @endforeach --}}
                                            @foreach ($branchess as $index => $branch)
                                                <div
                                                    class="d-flex justify-content-between location-card border-bottom py-3">
                                                    <div class="d-flex align-items-start">
                                                        <input class="form-check-input me-2 location-radio" type="radio"
                                                            id="location-{{ $branch->id }}" name="choosen_location"
                                                            data-branch-id="{{ $branch->id }}" {{ $branch->status == 1 ? 'checked' : '' }}>
                                                        <label for="location-{{ $branch->id }}"
                                                            class="ms-1 branch-location-parent">
                                                            <h6 class="small fw-bold m-0">{{ $branch->name }}</h6>
                                                            <p class="small fw-bold m-0 branch-location">
                                                                {{ $branch->location }}</p>
                                                                <input type="hidden" class="tax-price" value={{ $branch->tax }}>
                                                            <b class="text-success m-0">Item Available</b>
                                                        </label>
                                                    </div>
                                                    @if (isset($locationImages[$index]))
                                                        <a class="location-description" data-bs-toggle="modal"
                                                            data-bs-target="#locationDescription" style="cursor: pointer">
                                                            <!-- Use the loop index to select the corresponding image -->
                                                            <img class="location-img" src="{{ $locationImages[$index] }}"
                                                                alt="location-img{{ $index + 1 }}">
                                                            <p class="small m-0">+100 miles</p>
                                                            <p class="text-danger">Store Info</p>
                                                        </a>
                                                    @else
                                                        <p class="text-danger">No Image Available</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                            <!-- Location-2 End -->
                                        </div>
                                        <!-- Location's Parent End -->
                                    </div>
                                    <div class="tab-pane fade" id="pills-delivery" role="tabpanel"
                                        aria-labelledby="pills-delivery-tab">
                                        <form>
                                            <div class="row g-3 my-5">
                                                <div class="col-12 mt-0">
                                                    <h5>Enter Delivery Address</h5>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control"
                                                            placeholder="Delivery Address">
                                                        <label>Delivery Address</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control"
                                                            placeholder="Apt, Floor, Suite, etc. (Optional)">
                                                        <label>Apt, Floor, Suite, etc. (Optional)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer position-relative px-2">
                                <button type="button" style="font-size:30px; position: absolute; left: 0;"
                                    class="btn location-modal-close ri-close-circle-line btn-danger px-2 ms-3 py-0"></button>
                                <div class="text-center mx-auto">
                                    <button class="btn btn-danger px-5 updateLocationBtn location-update-btn">Update
                                        Location</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Location Modal End -->
            <!-- Location Description Modal Start -->

            <div class="container-fluid cart wow fadeIn" data-wow-delay="0.1s">
                <div class="modal fade" id="locationDescription" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog border-0 modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0">
                            <div class="modal-body p-0">
                                <iframe class="w-100" height="300"
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3011.8769358092172!2d-73.82903412475196!3d40.98417552090991!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c2933bd5635a09%3A0x502bc89c049525ac!2s2562%20Central%20Park%20Ave%2C%20Yonkers%2C%20NY%2010710%2C%20USA!5e0!3m2!1sen!2s!4v1700483134249!5m2!1sen!2s"
                                    style="border:0;" allowfullscreen="" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                                <div class="p-3">
                                    <h6 class="mb-3 locationHeading">AZ Nutrition & Smoothies</h6>
                                    <p class="m-0 address1">2562 Central Park Av</p>
                                    <p class="mb-3 address2">yonkers, NY 10710</p>
                                    <ul>
                                        @if ($userTimeSlots)
                                            <li>Pickup:
                                                {{ \Carbon\Carbon::parse($userTimeSlots->date)->format('d M, Y') }} at
                                                {{ $userTimeSlots->time }}</li>
                                        @else
                                            @foreach ($timeSlots as $timeSlot)
                                                <li class="selected-time" data-time="{{ $timeSlot->start_pickup_time }}">
                                                    <span class="ri-time-line"> </span>Today Pickup:
                                                    {{ $timeSlot->start_pickup_time }}
                                                </li>
                                            @break
                                        @endforeach
                                    @endif
                                    <li>
                                        Estimated prep time: Available immediately
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" style="font-size:30px"
                                class="btn location-description-btn ri-close-circle-line btn-danger px-2 py-0 me-auto"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Location Description Modal End -->
        <!-- Modals End -->
        <!--Cart Section Start -->
        {{-- import Topping class in html code --}}
        @php
            use App\Models\Topping;
            use App\Models\Category;
        @endphp
        <div class="container-fluid cart wow fadeIn" data-wow-delay="0.1s">
            <div class="col-11 mx-auto mt-5">
                <!-- Diviser -->
                <div class="row justify-content-center justify-content-md-between align-items-start">
                    <!-- The Card Part -->
                    <div class="col-md-6 col-sm-11 col-12 ">
                        <!--  cards Parent -->
                        <div class="card-parent">
                            @php
                                $quantity = 0;
                                foreach (session('cart', []) as $item) {
                                    $quantity += $item['quantity'];
                                }
                            @endphp
                            <h6>You Order Items (<span class='order-items'>{{ $quantity }}</span>)</h6>
                            <!-- The First Card -->
                           @forelse (session('cart', []) as $item)
                                <div class="cart_card mb-3 d-flex border-bottom py-4">
                                    <!-- Img Part -->
                                    <div>
                                        <img src="{{ $item['image'] }}" alt>
                                    </div>

                                    <!-- Description -->
                                    <div class="ms-3 w-100 order-card parent-element">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="m-0">
                                                {{ $item['name'] }}
                                                <span style="font-size:12px">
                                                    {{ $item['size'] ? '(' . $item['size'] . ')' : '' }}
                                                </span>
                                            </h5>
                                            <p class="mb-0 price">£{{ $item['price'] }}</p>
                                        </div>

                                        <p class="mb-1">Variation: Regular</p>

                                        {{-- ✅ Delivery Type --}}
                                        @if(isset($item['delivery_status']))
                                            <p class="mb-1 fw-bold">
                                                @if($item['delivery_status'] == 1)
                                                @elseif($item['delivery_status'] == 2)
                                                @else
                                                    Delivery Type: Not Specified
                                                @endif
                                            </p>
                                        @endif

                                        {{-- ✅ Store Pickup Location --}}
                                            @if(isset($item['delivery_status']) && $item['delivery_status'] == 1 && isset($item['branch_id']))
                                                @php
                                                    $branch = Branch::find($item['branch_id']);
                                                @endphp
                                                @if($branch)
                                                    <p class="small text-muted mb-2">
                                                        <strong>Pickup:</strong> {{ $branch->location }}
                                                    </p>
                                                @endif
                                            @endif

                                        {{-- ✅ Delivery Address (only if Home Delivery) --}}
                                        @if(isset($item['delivery_status']) && $item['delivery_status'] == 2 && !empty($item['delivery_address']))
                                            <p class="small text-muted mb-2">
                                                <strong>Home Delivery:</strong> {{ $item['delivery_address'] }}
                                            </p>
                                        @endif

                                        {{-- ✅ Toppings Section --}}
                                        @if ($item['toppings_by_category'])
                                            <h6>Your Toppings</h6>
                                            @foreach ($item['toppings_by_category'] as $categoryId => $toppingIds)
                                                @php
                                                    $categories = Category::where('id', $categoryId)->get();
                                                @endphp
                                                @foreach ($categories as $category)
                                                    @if ($category)
                                                        <div class='mb-2'>
                                                            <p class="category-name mb-1 fw-bold text-black">{{ $category->name }}</p>
                                                            @foreach ($toppingIds as $toppingId)
                                                                @php
                                                                    $topping = Topping::find($toppingId);
                                                                @endphp
                                                                @if ($topping)
                                                                    <p class="small m-0">
                                                                        {{ $topping->name }}
                                                                        <span class='topping_price-1'>(£{{ number_format($topping->price, 2) }})</span>
                                                                    </p>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        @endif
                                        {{-- ✅ Complementary Product --}}
                                        @if (!empty($item['complementary']))
                                            <div class="d-flex flex-column align-items-start mt-2">
                                                <span class="badge bg-success my-2">Included (Free)</span>
                                                <img 
                                                    src="{{ asset($item['complementary']['image']) }}" 
                                                    alt="{{ $item['complementary']['name'] }}" 
                                                    width="40" 
                                                    height="40"
                                                    style="object-fit: cover" class="rounded-circle"
                                                >
                                                <p class="badge bg-success my-2">{{ $item['complementary']['name'] }}</p>
                                            </div>
                                        @endif
                                        {{-- Quantity & Delete --}}
                                        <div class="d-flex justify-content-between">
                                            <div class="item-btn-parent">
                                                <button class="btn item-btn py-0 px-2 minus-btn"
                                                    data-product-id="{{ $item['product_id'] }} , {{ $item['variant_id'] ?? null }}" disabled>-</button>
                                                <input type="number" name="quantity"
                                                    value="{{ $item['quantity'] }}"
                                                    class="cart_input count-input increment-input text-center">
                                                <button class="btn item-btn py-0 px-2 plus-btn"
                                                    data-product-id="{{ $item['product_id'] }} , {{ $item['variant_id'] ?? null }}">+</button>
                                            </div>
                                            <span class="ri-delete-bin-7-fill del-btn text-danger p-0"
                                                style="font-size: 20px; cursor: pointer;"
                                                onclick="removeFromCart('{{ $item['product_id'] }}', '{{ $item['variant_id'] ?? null }}')">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-danger text-center">Your cart is empty.</p>
                            @endforelse

                        </div>
                        <div>
                            {{-- <div class>
                                <a href="{{ route('get-our-menu') }}" class="add_more py-2 rounded">
                                    Add More
                                </a>
                            </div> --}}
                        </div>
                    </div>
                    <!-- Cart Part End -->
                    <!-- The Right Side Bar -->
                    <div class="mt-md-5 mt-4  col-md-5 col-sm-11 billing col-12">
                        <div class="row justify-content-end">
                            <div class="col-xl-11">
                                <!-- Alert And Location Start -->
                               <div class="border-bottom pb-sm-4 pb-3">
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

                                        {{-- Header with conditional Edit button --}}
                                        <div class="d-flex align-content-center justify-content-between">
                                            <p class="pb-0 text-dark fw-bold">HOW TO GET IT</p>
                                            @if ($hasPickup)
                                                <a class="btn text-danger py-0" data-bs-toggle="modal" data-bs-target="#time-Modal">Edit</a>
                                            @endif
                                        </div>

                                        {{-- 🏬 STORE PICKUP SECTION --}}
                                        @if ($hasPickup)
                                            <div class="pickup-section">
                                                @php
                                                    $branch = \App\Models\Branch::where('name', $storePickupBranch)->first();
                                                @endphp
                                                <span class="pb-2 d-block mt-1">
                                                    <span class="ri-store-2-line"></span>
                                                    <strong>Pickup:</strong>
                                                    {{ $storePickupBranch }}
                                                    @if ($branch && $branch->location)
                                                        — {{ $branch->location }}
                                                    @endif
                                                </span>

                                                {{-- 🕒 Pickup Time --}}
                                                @if ($dateTime = session('time'))
                                                    <span class="ri-time-line mt-1"></span>
                                                    {{ \Carbon\Carbon::parse($dateTime['date'])->format('d M, Y') }} at
                                                    {{ $dateTime['time'] }}
                                                @else
                                                    @foreach ($timeSlots as $timeSlot)
                                                        <span class="selected-time d-block mt-1" data-time="{{ $timeSlot->start_pickup_time }}">
                                                            <span class="ri-time-line"></span>
                                                            Today Pickup: {{ $timeSlot->start_pickup_time }}
                                                        </span>
                                                        @break
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endif

                                        {{-- 🚚 HOME DELIVERY SECTION --}}
                                        @if ($hasHomeDelivery)
                                            <div class="home-delivery-section mt-3">
                                                <span class="pb-2 d-block mt-1">
                                                    <span class="ri-home-line"></span>
                                                    <strong>Home Delivery:</strong>
                                                    {{ $homeDeliveryAddress }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            <!-- Alert And Location End -->
                            <!-- Tip part-->
                            <div class="mt-sm-4 pb-sm-4 mt-3 pb-3 border-bottom">
                                <p class>ADD A TIP</p>
                                <!-- tips Button -->
                                <div class="collapse-div mt-3 align-items-center justify-content-between">
                                    <span class="border border-2 px-2 py-3 d-flex align-items-center">£ <input
                                            type="text" name="tipInputName"
                                            class="ps-2 w-100 cart_input increment-input tip-input"></span>
                                </div>
                            </div>
                             <!-- Loyalty Points Section-->
                            <div class="mt-sm-4 pb-sm-4 mt-3 pb-3 border-bottom">
                                <!-- Available Points -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Loyalty Points:</span>
                                    <strong id="availablePoints">{{ $loyaltyPoints->rewards ?? 0 }}</strong>
                                </div>
                                <!-- Input + Button -->
                                <div class="collapse-div mt-3 d-flex align-items-center gap-2">
                                    <span class="border border-2 px-2 py-3 d-flex align-items-center w-100">
                                        £ 
                                        <input type="number" name="loyalty_points" id="loyaltyPointInput"
                                            class="ps-2 w-100 cart_input"
                                            placeholder="Enter points">
                                    </span>
                                    <!-- Use Points Button -->
                                    {{-- <button type="button" class="btn btn-primary" id="usePointsBtn">
                                        Redeem
                                    </button> --}}
                                </div>
                                <!-- Discount Display -->
                                <div class="mt-2">
                                    <small>Price Per Point: £<span id="pricePerPoint">{{ $pricePerPoint->price ?? 0 }}</span></small>
                                </div>
                            </div>

                            <!-- Tip Part End -->
                            <!-- Coupan Part Start -->
                            {{-- <div
                                class="mt-sm-4 pb-sm-4 mt-3 pb-3 align-items-center justify-content-between border-bottom">
                                <div class="tip-input-container coupan">
                                    <span
                                        class="border  border-2 ri-gift-fill px-2 d-flex align-items-center"><input
                                            type="text" placeholder="Add Coupan or Gift Card"
                                            class=" ps-2 py-3 w-100 cart_input"></span>
                                    <button class="btn rounded-3 btn-danger py-3" hidden>Cancel</button>
                                </div>
                            </div> --}}
                            <!-- Coupan Part End -->
                            <!-- Blling Start -->
                            @php
                                $cartItems = session('cart', []);
                                $firstItem = reset($cartItems);
                                $deliveryCharge = rand(2, 10);
                                session(['delivery_charge' => $deliveryCharge]);
                            @endphp

                            <div class="mt-sm-4 pb-sm-4 mt-3 pb-3">
                                @if ($firstItem)
                                    <div class="d-flex justify-content-between">
                                        <p class="text-muted">Sub Total</p>
                                        <p class="sub-total">£{{ $firstItem['price'] }}</p>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        @foreach ($branchess as $index => $branch)
                                        @if ($branch->status == 1)
                                        <p class="text-muted">Estimated Taxes (New York)</p>
                                        <p class="tax-value">£{{$branch->tax}}</p>
                                        @endif
                                    @endforeach
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p class="text-muted">Tip</p>
                                        <p class="tip-value">£0</p>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p class="tecontinue-to-add-tipxt-muted">Redeem Points Amount</p>
                                        <span>- </span><p class="redeem-value">£0</p>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p class="text-muted">Delivery Charges</p>
                                        <p class="delivery-charge">£{{ $deliveryCharge }}</p>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p class="text-muted">Estimated order Total</p>
                                        <p class="total-value">£{{ floatval($firstItem['price']) + $branch->tax }}</p>
                                    </div>
                                @else
                                    <p class="text-danger text-center">Your cart is empty.</p>
                                @endif

                                <a href="{{ route('checkout') }}"
                                    class="mt-3 w-100 btn py-2 btn-danger continue-to-add-tip continue-to-payment">Continue
                                    to Payment</a>
                            </div>

                            <!-- Billing ENd -->
                        </div>
                    </div>
                    <!-- Right Side Bar End -->
                </div>

            </div>
        </div>
    </div>
    <!--Cart Section End -->
</div>
</section>
@endsection
@section('js')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/pikaday.min.js"></script>
@if (\Illuminate\Support\Facades\Session::has('message'))
<script>
    toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
</script>
@endif
<script> 

   
    $(function() {
        $('.location-update-btn').click(function() {
            $('.location-radio').each(function() {
                if ($(this).is(':checked')) {
                    let branchLoc = $(this).siblings('.branch-location-parent').find(
                        '.branch-location').text();
                    let tax=$(this).siblings('.branch-location-parent').find('.tax-price').val();
                    $('.tax-value').text('£'+tax);
                    Total();
                    $('.branch-pickup').each(function() {
                        $(this).text(branchLoc);
                    })
                }
            })
            $('#locationModal').modal('hide');
            $('#time-Modal').modal('show');
        })
        //Modal Control
        $('.modal-content').click(function() {
            event.stopPropagation();
        });
        $('.locattion-button-show-btn').click(function() {
            event.stopPropagation();
            $('#time-Modal').modal('hide');
        });
        $('.time-modal-close').click(function() {
            event.stopPropagation();
            $('#time-Modal').modal('hide');
        });
        $('#locationModal').click(function() {
            event.stopPropagation();
            $('#time-Modal').modal('show');
        });
        $('.location-modal-close').click(function() {
            event.stopPropagation();
            $('#locationModal').modal('hide');
            $('#time-Modal').modal('show');
        })
        $('.location-description-btn').click(function() {
            event.stopPropagation();
            $('#locationDescription').modal('hide');
            $('#locationModal').modal('show');
        });

        $('.calender').click(function() {
            event.stopPropagation();
            $('.time_input').click();
        });

        $('.location-description').each(function() {
            $(this).click(function() {
                $('#locationModal').modal('hide');
            })
        });
        $('#locationDescription').click(function() {
            event.stopPropagation();
            $('#locationModal').modal('show');
        });

        // Initialize Pikaday
        var picker = new Pikaday({
            field: document.getElementById('datepicker'),
            format: 'YYYY-MM-DD', // Adjust the format as needed
            yearRange: [1900, moment().year()], // Set the range of selectable years
            showYearDropdown: true, // Display a dropdown for selecting the year
            // You can customize other options as needed
            minDate: new Date() // Set the minimum date to the current date
        });
        //coupan Control;
        $('.coupan').on('click keydown keyup keypress', 'input', function() {
            event.stopPropagation()
            if ($(this).val() == '') {
                $('.coupan .btn').text('Cancel');
            } else {
                $('.coupan .btn').text('Apply');
            }
            $('.coupan .btn').removeAttr('hidden');
            $('.coupan span').css('width', '72%')
        });
        $(document).click(function() {
            if ($('.coupan .btn').text() == 'Cancel') {
                $('.coupan .btn').attr('hidden', true);
                $('.coupan span').css('width', '100%')
            }
        })
        // redeem control
        let loyaltyRedeemAmount = 0;
        $('#loyaltyPointInput').on('input', function () {
            let availablePoints = parseFloat($('#availablePoints').text()) || 0;
            let pricePerPoint = parseFloat($('#pricePerPoint').text()) || 0
            let enteredPoints = parseFloat($(this).val()) || 0;
            // Reset case
            if (enteredPoints <= 0) {
                loyaltyRedeemAmount = 0;
                $('.redeem-value').text('£0');
                Total();
                return;
            }
            // Validation
            if (enteredPoints > availablePoints) {
                alert('You cannot use more than available points');
                $(this).val('');
                loyaltyRedeemAmount = 0;
                $('.redeem-value').text('£0');
                Total();
                return;
            }
            // Calculate
            loyaltyRedeemAmount = enteredPoints * pricePerPoint;
            $('.redeem-value').text('£' + loyaltyRedeemAmount.toFixed(2));
            // Recalculate total
            Total();
            return;
        });
        //Order Control
        $('.item-btn-parent').on('click', '.btn', function() {
            let b = $(this).siblings('p').text();
            if ($(this).text() == '+') {
                $('#' + b).siblings('.increment-btn').click();
                let a = Number($(this).closest('.item-btn-parent').find('input').val());
                if (a >= 2) {
                    $(this).siblings('.btn').removeAttr('disabled');
                }
            } else {
                $('#' + b).siblings('.decrement-btn').click()
            }
            Total();
        });
        $('.tip-input').on('click keydown keyup keypress', function() {
            if ($(this).val() < 0) {
                $(this).val(0)
            }
            $('.tip-value').text('£' + $(this).val());
            Total();
        });
        $('.minus-btn').each(function() {
            let a = Number($(this).closest('.item-btn-parent').find('input').val());
            if (a >= 2) {
                $(this).removeAttr('disabled');
            }
        })
        //Control of Total and Sub Total
        function Total() {
            let count = 0;
            let sum = 0;
            let item=0;
            $('.topping_price-1').each(function() {
                item++;
                console.log(item)
                let toppingPriceText = $(this).text().trim();
                console.log(toppingPriceText);
                // Check if the topping price is not empty
                if (toppingPriceText !== '') {
                    let toppingPrice = parseFloat(toppingPriceText.slice(2, -
                        1)); // Extract the numeric part
                    sum += toppingPrice;
                }
            });
            let a = 0;
            $('.count-input').each(function() {
                let productPriceText = $(this).closest('.order-card').find('.price').text().slice(1);
                let productPrice = parseFloat(productPriceText);
                let ItemCount = Number($(this).val());
                a += ItemCount;
                count += ItemCount;
                sum += productPrice * ItemCount;
                sum = parseFloat(sum.toFixed(2));
            });
            $('.order-items').text(a);
            $('.sub-total').text('£' + sum);
            let tipValue = Number($('.tip-value').text().slice(1));
            let tax = Number($('.tax-value').text().slice(1));
            let redeem = Number($('.redeem-value').text().slice(1));
            let delivery = Number($('.delivery-charge').text().slice(1));
            $('.total-value').text('£' + (tipValue + tax + delivery  + sum - redeem).toFixed(2));
            $('.order-input').text(count);
        }

        //delete the border of the last child
        $('.card-parent .cart_card:last').removeClass('border-bottom');
        //Count Input Control
        $('.count-input').each(function() {
            $(this).on('click keydown keyup keypress', function() {
                if ($(this).val() <= 1) {
                    $(this).val(1);
                }
                $('.item-btn-parent .btn').each(function() {
                    if ($(this).text() == '-') {
                        if ($(this).siblings('input').val() >= 2) {
                            $(this).removeAttr('disabled');
                        } else {
                            $(this).attr('disabled', true);
                        }
                    };
                })
                Total();
            });
        })
        Total();
    });
    $('.del-btn').each(function() {
        $(this).click(function() {
            let a = $(this).siblings('.helper-p').text();
            a += 'carted'
            $('#' + a).remove();
            $(this).parents('.cart_card').remove();
            let count = 0;
            let sum = 0;
            $('.topping_price-1').each(function() {
                let toppingPriceText = $(this).text().trim();
                if (toppingPriceText !== '') {
                    let toppingPrice = parseInt(toppingPriceText.slice(2, -
                        1));
                    sum += toppingPrice;
                }
            });
            let bb = 0;
            $('.count-input').each(function() {
                let productPriceText = $(this).closest('.order-card').find('.price').text()
                    .slice(1);
                let productPrice = parseFloat(productPriceText);
                let ItemCount = Number($(this).val());
                bb += ItemCount;
                count += ItemCount;
                sum += productPrice * ItemCount;
                sum = parseFloat(sum.toFixed(2));
            });
            $('.order-items').text(bb);
            $('.cart-counter-1').each(function() {
                $(this).text(bb);
            })
            $('.sub-total').text('£' + sum);
            let tipValue = Number($('.tip-value').text().slice(1));
            let tax = Number($('.tax-value').text().slice(1));
            let delivery = Number($('.delivery-charge').text().slice(1));
            $('.total-value').text('£' + (tipValue + tax + delivery  + sum).toFixed(2));
            $('.order-input').text(count);
        })
    });

    function removeFromCart(productId, variantId) {
        // Use AJAX to remove the item from the cart
        $.ajax({
            type: 'POST',
            url: '{{ route('remove.from.cart') }}',
            data: {
                '_token': '{{ csrf_token() }}',
                'product_id': productId,
                'variant_id': variantId // Include the variant ID in the request
            },
            success: function(data) {
                console.log('Product removed successfully!', data);
                // Update the cart UI or perform any other actions after successful removal
            },
            error: function(error) {
                console.error('Error removing product from cart:', error);
            }
        });
    }

    $('.plus-btn').on('click', function() {
        console.log('Plus button clicked');
        var inputField = $(this).siblings('.increment-input');
        var quantity = parseInt(inputField.val(), 10);
        inputField.val(quantity + 1);

        // Get the product ID and variant ID from the data attribute
        var dataIds = $(this).data('product-id').split(',');
        var productId = dataIds[0].trim();
        var variantId = dataIds[1] ? dataIds[1].trim() : null;

        // Update the session with the new quantity
        updateSession(productId, quantity + 1, variantId);
    });

    $('.minus-btn').on('click', function() {
        console.log('Minus button clicked');
        var inputField = $(this).siblings('.increment-input');
        var quantity = parseInt(inputField.val(), 10);

        if (quantity > 1) {
            inputField.val(quantity - 1);

            // Get the product ID and variant ID from the data attribute
            var dataIds = $(this).data('product-id').split(',');
            var productId = dataIds[0].trim();
            var variantId = dataIds[1] ? dataIds[1].trim() : null;

            // Update the session with the new quantity
            updateSession(productId, quantity - 1, variantId);
        }
    });

    // Function to update the session on the server side
    function updateSession(productId, quantity, variantId) {
        $.ajax({
            type: 'POST',
            url: '{{ route('update.my.cart') }}',
            data: {
                '_token': '{{ csrf_token() }}',
                'product_id': productId,
                'quantity': quantity,
                'variant_id': variantId // Include variant ID in the data
            },
            success: function(data) {
                // Handle the success response, e.g., update UI
                console.log('Session updated successfully:', data);
            },
            error: function(error) {
                // Handle the error response, e.g., show an error message
                console.error('Error updating session:', error);
            }
        });
    }


    $('.updateTimeBtn').on('click', function() {
        var timeModal = $(this).closest('.time-modal');
        var date_input = timeModal.find('input[name="date_input"]').val();
        var selectedTime = timeModal.find('input[name="time-radio"]:checked').val();

        if (!selectedTime) {
            alert('Please select a time slot.');
            return;
        }

        // Store the selected time in local storage
        localStorage.setItem('selectedTime', selectedTime);

        $.ajax({
            type: 'POST',
            url: '{{ route('update.time') }}',
            data: {
                '_token': '{{ csrf_token() }}',
                'date_input': date_input,
                'time-radio': selectedTime,
            },
            success: function(data) {
                toastr.success('Time Updated successfully!');
                setTimeout(function() {
                    location.reload();
                }, 500);
                timeModal.modal('hide');
            },
            error: function(error) {
                // Handle the error response, e.g., show an error message
                console.error('Error updating time:', error);
            }
        });
    });

    // When the page loads, set the checked state based on the stored value
    $(document).ready(function() {
        var storedTime = localStorage.getItem('selectedTime');
        if (storedTime) {
            $('input[name="time-radio"]').filter('[value="' + storedTime + '"]').prop('checked', true);
        }
    });

    $('.continue-to-payment').on('click', function(e) {
        e.preventDefault();

        // Get the selected time from the data attribute
        var selectedTime = $('.selected-time').data('time');

        // Make an AJAX request to store the selected time in the session
        $.ajax({
            type: 'POST',
            url: '{{ route('time-solt') }}',
            data: {
                '_token': '{{ csrf_token() }}',
                'selectedTime': selectedTime,
            },
            success: function(data) {
                console.log(data);

                // Send the tip amount to the server using AJAX
                var tipAmount = $('.tip-input').val();
                var redeemPoints = $('#loyaltyPointInput').val();
                var redeemAmount = Number($('.redeem-value').text().slice(1));
                $.ajax({
                    type: 'POST',
                    url: '{{ route('store.tip') }}', // Replace with the actual route
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'tipAmount': tipAmount,
                        'redeemAmount': redeemAmount,
                        'redeemPoints': redeemPoints
                    },
                    success: function(data) {
                        // Redirect to the checkout page after storing the tip in the session
                        window.location.href = $(e.currentTarget).attr('href');
                    },
                    error: function(error) {
                        // Handle errors if needed
                        console.error('Error storing tip in session:', error);
                    }
                });
            },
            error: function(error) {
                console.error('Error storing selected time in session:', error);
            }
        });
    });


    // location update

    $('.updateLocationBtn').on('click', function() {
        // Find the selected radio button
        var selectedBranch = $('input[name="choosen_location"]:checked');

        if (selectedBranch.length === 0) {
            alert('Please select a location before updating.');
            return;
        }

        // Extract branch ID from the data attribute
        var branchId = selectedBranch.data('branch-id');

        // Send AJAX request to update branch status
        $.ajax({
            type: 'POST',
            url: '{{ route('update.branch.status') }}',
            data: {
                '_token': '{{ csrf_token() }}',
                'branch_id': branchId,
            },
            success: function(data) {
                toastr.success('Updated Successfully');
                // setTimeout(function() {
                //     location.reload();
                // }, 500);
                console.log(response.message);

                // Reload the page after a short delay
                // setTimeout(function() {
                //     location.reload();
                // }, 500);
            },
            error: function(error) {
                console.error('Error updating branch status:', error);
            }
        });
    });
</script>
@endsection
