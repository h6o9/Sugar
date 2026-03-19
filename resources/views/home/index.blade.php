@extends('home.layout.app')
@section('title', 'Login')
@section('content')
    <style>
        button.tab-scroll-btn {
            padding: 5px;
            background: var(--primary);
            color: #000;
            border-radius: 50px;
            border: none;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .about-us-az-1 {
            height: 285px;
            object-fit: cover;
        }


        .about-us-az-2 {
            height: 205px;
            object-fit: cover;
        }

        @media (max-width: 767px) {
            .about-us-az-1 {
                height: 225px;
            }

            .about-us-az-2 {
                height: 145px;
            }
        }

        .accordion-button {
            font-weight: 500;
        }

        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: #212529;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: transparent;
        }

        .menu-category-tabs {
            border-bottom: 2px solid #dee2e6;
        }

        .menu-category-tabs .nav-item {
            margin-bottom: -2px;
        }

        .menu-category-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 15px 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            transition: all 0.3s;
        }

        .menu-category-tabs .nav-link:hover {
            color: #dc3545;
            border-bottom-color: #dc3545;
            background-color: transparent;
        }

        .menu-category-tabs .nav-link.active {
            color: #dc3545;
            border-bottom-color: #dc3545;
            background-color: transparent;
        }

        .popular-item {
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .popular-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .menu-category-tabs {
                overflow-x: auto;
                display: flex;
                flex-wrap: nowrap;
            }

            .menu-category-tabs .nav-item {
                flex-shrink: 0;
            }

            .menu-category-tabs .nav-link {
                padding: 10px 15px;
                font-size: 12px;
            }
        }

        /* Owl Carousel Card Styling */
        .owl-carousel .item {
            padding: 10px;
            width: 100%;
        }

        .owl-carousel .popular-item {
            height: 100%;
            min-height: 350px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .owl-carousel .owl-item {
            display: flex;
        }

        .owl-carousel .owl-stage {
            display: flex;
        }

        .owl-carousel .owl-item .item {
            height: 100%;
            width: 100%;
        }

        /* Sliding Tabs for Menu */
        .menu-tabs-wrapper {
            position: relative;
        }

        .menu-tabs-container {
            overflow: hidden;
            position: relative;
        }

        .menu-category-tabs {
            overflow-x: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            white-space: nowrap;
            display: flex !important;
            gap: 10px;
            flex-wrap: nowrap !important;
        }

        .menu-category-tabs .nav-item {
            flex-shrink: 0 !important;
            display: inline-block;
        }

        .menu-category-tabs .nav-link {
            white-space: nowrap !important;
            flex-shrink: 0;
        }

        .menu-tabs-container .tab-scroll-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: white;
            border: 2px solid #dc3545;
            color: #dc3545;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .menu-tabs-container .tab-scroll-btn:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .menu-tabs-container .tab-scroll-btn.left {
            left: -15px;
        }

        .menu-tabs-container .tab-scroll-btn.right {
            right: -15px;
        }

        .menu-tabs-container .tab-scroll-btn.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .menu-tabs-container .tab-scroll-btn.disabled:hover {
            transform: translateY(-50%) scale(1);
            background: white;
            color: #dc3545;
        }

        .menu-category-tabs::-webkit-scrollbar {
            display: none;
        }

        .menu-category-tabs {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .menu-category-tabs .nav-link {
            white-space: nowrap;
            padding: 12px 25px !important;
        }
    </style>

    <div class="mt-4 container-fluid banner-container">
        <div class="container-fluid">
            <div class="container-fluid">
                <div class="container-fluid position-relative">
                    <img src="{{ asset('public/img/pic-top.jpg') }}" alt="" class="banner-img w-100">
                    <!-- Profile image -->
                    <div class="position-absolute banner-prof-img">
                        <img src="{{ asset('public/img/profile-top.png') }}" alt="Profile">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="container-fluid mt-5">
            <div class="container-fluid">
                <div class="container-fluid">
                    <div class="container-fluid">
                        <div class="row mx-0">
                            <div class="col-lg-8">
                                <h2>Sugar Pappi</h2>
                                <span class="text-dark rating small">4.5 <span class="bi bi-star-half text-black"></span>
                                    <span class="text-muted small">(2,000+)</span>
                                </span>
                                <span class="small"><span class="separator">•</span> Desserts</span>
                                <span class="small"><span class="separator">•</span> Comfort Food</span>
                                <span class="small"><span class="separator">•</span> Cupcakes</span>
                                <span class="small"><span class="separator">•</span> Bubble Tea</span>
                                <span class="small"><span class="separator">•</span> Coffee & Tea</span>
                                <span class="small"><span class="separator">•</span> Steak & Cheese Sandwich</span>
                                <span class="small"><span class="separator">•</span> Tea & Coffee</span>
                                <p class="mb-0 small">Min order value for this shop is £12</p>
                                @if ($timeSlots->isNotEmpty())
                                    <p class="mb-0 small">
                                        Timing:
                                        {{ \Carbon\Carbon::parse($timeSlots->first()->start_pickup_time)->format('g:i A') }}
                                        –
                                        {{ \Carbon\Carbon::parse($timeSlots->first()->end_pickup_time)->format('g:i A') }}
                                    </p>
                                @endif
                                <p class="mb-0 small">Aldow Industrial Estate, Pod 10, Unit D, Jacuna Kitchen,
                                    Ardwick,, Manchester, EMEA M12 6AE</p>
                                <p class="small text-dark">Sugar Pappi in Chorltonne upon Medlock, Manchester, is a dessert
                                    spot that
                                    enjoys a high
                                    customer rating of 4.8. The menu features an array of hot desserts such as Sticky Toffee
                                    Pudding and Apple Crumble, alongside creative options like the ‘Make Your Own Waffle’
                                    and
                                    ‘Kinderlicious Cookie Dough’. For those seeking a unique treat, the ‘Mixed Tango Ice
                                    Blast’
                                    is a popular choice among patrons. This restaurant is particularly favoured for
                                    late-night
                                    dessert cravings, offering a diverse range of sweets that also includes a variety of
                                    mocktails and specialty teas.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-end del-pickup-container">
                                    <!-- Pills -->
                                    <ul class="nav nav-pills mb-0 d-flex align-items-center custom-pills" id="pillsDelPickup"
                                        role="tablist">
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link active" id="pillsDeliveryTab" data-bs-toggle="pill"
                                                data-bs-target="#pillsDelivery" type="button" role="tab"
                                                aria-controls="pillsDelivery" aria-selected="true">Delivery</button>
                                        </li>
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link" id="pillsPickupTab" data-bs-toggle="pill"
                                                data-bs-target="#pillsPickup" type="button" role="tab"
                                                aria-controls="pillsPickup" aria-selected="false">Pick-up</button>
                                        </li>
                                    </ul>
                                </div>

                                <div class="tab-content mt-3" id="pillsDelPickup">
                                    <div class="tab-pane fade show active" id="pillsDelivery" role="tabpanel"
                                        aria-labelledby="pillsDeliveryTab" tabindex="0">
                                        <div class="delivery-table">
                                            <div class="delivery-col">
                                                <p class="title">Delivery Fee</p>
                                                <a href="#">Other Fees</a>
                                            </div>

                                            <div class="delivery-col">
                                                <p class="title">Delivery Unavailable</p>
                                                <p>Delivery Time</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="pillsPickup" role="tabpanel"
                                        aria-labelledby="pillsPickupTab" tabindex="0">
                                        <div class="delivery-table">
                                            <div class="delivery-col">
                                                <p class="title">£0.00</p>
                                                <a href="#">Other Fees</a>
                                            </div>

                                            <div class="delivery-col">
                                                <p class="title">Closed</p>
                                                <p>Pick-up time</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
						<!-- Cibo Express Modal Start -->
						 <section class="cibo-express-section py-5">
    <div class="container">
        @foreach ($ciboExpressItems as $item)
        <div class="row align-items-center mb-5">

            <!-- Left Side Content -->
            <div class="col-lg-6 col-md-6">
                <h2 class="mb-3">{{ $item->title ?? 'Cibo Express' }}</h2>
                <p class="text-muted">
                    {{ $item->description }}
                </p>
            </div>

            <!-- Right Side Image -->
            <div class="col-lg-6 col-md-6 text-center">
                <img src="{{ asset($item->image) }}" 
                     class="img-fluid rounded shadow">
            </div>

        </div>
        @endforeach
    </div>
</section>
                    </div>
                </div>
            </div>
        </div>

        <!--Food Modal Start -->
        @foreach ($products as $product)
    @php
        $originalPrice = $product->original_price ?? $product->price;
        $finalPrice = $product->price;
        $discountPercent = 0;
        $hasDiscount = false;

        if ($product->featured_action == 'decrease' && $product->original_price && $product->original_price > $product->price) {
            $hasDiscount = true;
            if ($product->featured_method == 'percentage') {
                $discountPercent = (int) $product->featured_amount;
            }
        }
    @endphp
    <div class="container-fluid cart food-modal wow fadeIn" data-wow-delay="0.1s">
        <div class="modal fade menu-modal" id="menuModal-{{ $product->id }}" tabindex="-1"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-body p-0 scrollable">
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="position-relative">
                            <img class="w-100" src="{{ asset($product->image) }}" alt="product-img">
                            @if($hasDiscount)
                                <span class="badge bg-danger position-absolute top-0 end-0 m-3 fs-6">{{ $discountPercent }}% OFF</span>
                            @endif
                        </div>
                        <div class="p-3 description">
                            <h3>{{ $product->name }}</h3>

                            @if (count($product->variants) > 0)
                                @if($hasDiscount)
                                    <p>
                                        <span class="text-muted text-decoration-line-through">£{{ number_format($originalPrice, 2) }}</span>
                                        <br>
                                        <span class="text-danger fw-bold prodPrice">£{{ number_format($finalPrice, 2) }}</span>
                                    </p>
                                @else
                                    <p>£ <span class="prodPrice">{{ $product->variants->first()->price }}</span></p>
                                @endif

                                <select class="form-control bg-white ps-1 select-size" name="variant_id"
                                    id="sizeSelect" style="appearance: auto">
                                    @foreach ($product->variants as $variant)
                                        <option value="{{ $variant->id }} {{ $variant->price }}">
                                            {{ $variant->size }}
                                        </option>
                                    @endforeach
                                </select>
                                <h6 class="small mt-1 mb-3">Note: Prices vary depending on the selected size</h6>
                            @else
                                @if($hasDiscount)
                                    <p>
                                        <span class="text-muted text-decoration-line-through">£{{ number_format($originalPrice, 2) }}</span>
                                        <br>
                                        <span class="text-danger fw-bold prodPrice">£{{ number_format($finalPrice, 2) }}</span>
                                    </p>
                                @else
                                    <p>£ <span class="prodPrice">{{ $product->price }}</span></p>
                                @endif
                            @endif

                            {{-- <p class="small">{!! $product->description !!}</p> --}}
                            <div class="d-flex cart-btn">
                                <button class="btn p-0 decrement" type="button">-</button>
                                <input type="text" class="cart_input increment-input text-center"
                                    value="1" name="quantity" id="quantity_{{ $product->id }}">
                                <button class="btn p-0 increment" type="button">+</button>
                            </div>
                        </div>

                        <!-- Location Start -->
                        <div class="description p-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="">How to get it</h6>
                                <h6 class="text-danger">Required</h6>
                            </div>
                            
                            @foreach ($branches as $index => $branch)
                                @if ($branch->status == 1)
                                    <div class="branch-option mb-3">
                                        <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                                        {{-- Store Pickup Option --}}
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                type="radio" 
                                                name="status_{{ $product->id }}"
                                                id="pickupStatus{{ $product->id }}_{{ $branch->id }}" 
                                                value="1" 
                                                checked 
                                                onchange="toggleDelivery('{{ $product->id }}', '{{ $branch->id }}')">
                                            <label class="form-check-label fw-bold small" for="pickupStatus{{ $product->id }}_{{ $branch->id }}">
                                                Store Pickup
                                            </label>
                                        </div>

                                        {{-- Store Pickup Address --}}
                                        <p class="small fw-bold m-0 sel-location mt-1" id="storePickupSection{{ $product->id }}_{{ $branch->id }}">
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($branch->location) }}" 
                                            target="_blank" 
                                            style="text-decoration: none; color: inherit;">
                                                {{ $branch->location }}
                                            </a>
                                        </p>

                                        {{-- Home Delivery Option --}}
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" 
                                                type="radio" 
                                                name="status_{{ $product->id }}" 
                                                id="homeStatus{{ $product->id }}_{{ $branch->id }}" 
                                                value="2" 
                                                onchange="toggleDelivery('{{ $product->id }}', '{{ $branch->id }}')">
                                            <label class="form-check-label fw-bold small" for="homeStatus{{ $product->id }}_{{ $branch->id }}">
                                                Home Delivery
                                            </label>
                                        </div>

                                        {{-- Delivery Address Input --}}
                                        <div id="deliveryAddressField{{ $product->id }}_{{ $branch->id }}" class="mt-2" style="display: none;">
                                            <input type="text" name="delivery_address_{{ $product->id }}" class="form-control" placeholder="Enter your delivery address">
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            <input type="hidden" id="orderLocation" name="address" value="2562 Central Park Av yonkers, NY">
                        </div>
                        <!-- Location End -->

                        <!-- Toppings Start-->
                        @if ($product->category->isNotEmpty())
                            @foreach ($product->category as $index => $category)
                                <div class="description p-3">
                                    <div class="arrow" style="cursor: pointer" data-bs-toggle="collapse"
                                        data-bs-target="#topping{{ $index }}{{ $category->id }}">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="m-0">{{ $category->getCategory->name }}</h6>
                                            <h6 class="fw-normal m-0 d-flex align-items-center">Optional
                                                <span class="h5 m-0 p-0 ri-arrow-up-s-line"></span>
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="collapse show"
                                        id="topping{{ $index }}{{ $category->id }}">
                                        @php
                                            $categoryToppings = App\Models\CategoryTopping::where(
                                                'category_id',
                                                $category->getCategory->id,
                                            )->get();
                                        @endphp
                                        @foreach ($categoryToppings as $categoryTopping)
                                            <div class="d-flex justify-content-between">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="toppings[]"
                                                        id="toppingchek{{ $index }}{{ $category->id }}{{ $categoryTopping->topping->id }}"
                                                        value="{{ $categoryTopping->topping->id }}"
                                                        data-category-id="{{ $category->getCategory->id }}">
                                                    <label class="form-check-label m-0"
                                                        for="toppingchek{{ $index }}{{ $category->id }}{{ $categoryTopping->topping->id }}">
                                                        {{ $categoryTopping->topping->name }}
                                                    </label>
                                                </div>
                                                <p class="m-0">
                                                    {{ isset($categoryTopping->topping->price) ? '£' . $categoryTopping->topping->price : '' }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        <!-- Toppings End -->
                    </div>
                    <div class="modal-footer position-relative px-2">
                        <button type="button"
                            style="font-size: 24px;position: absolute;left: 0;width: 30px;height: 30px;display: flex;justify-content: center;align-items: center"
                            class="btn time-modal-close ri-close-circle-line btn-danger px-2 ms-3 py-0"
                            data-bs-dismiss="modal"></button>
                        <div class="text-center mx-auto">
                            <button class="btn btn-danger addto-cart px-sm-5 px-4" data-bs-dismiss="modal">Add To
                                Order</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
        <!-- Food Modal End -->

        <!-- Location Modal Start -->
        <div class="container-fluid cart wow fadeIn" data-wow-delay="0.1s">
            <div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Select Location</h5>
                            <button type="button" class="btn-close location-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body py-0 my-0 scrollable">
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

                                {{-- @foreach ($branches as $index => $branch)
                        <div class="d-flex justify-content-between location-card border-bottom py-3">
                            <div class="d-flex align-items-start">
                                <input class="form-check-input me-2" type="radio" id="location-{{ $branch->id }}"
                                    name="choosen_location" data-branch-id="{{ $branch->id }}">
                                <label for="location-{{ $branch->id }}" class="ms-1">
                                    <h6 class="small fw-bold m-0">{{ $branch->name }}</h6>
                                    <p class="small fw-bold m-0 branch-location">{{ $branch->location }}</p>
                                    <b class="text-success m-0">Item Available</b>
                                </label>
                            </div>
                            <a class="location-description" data-bs-toggle="modal" data-bs-target="#locationDescription"
                                style="cursor: pointer">
                                <!-- Use the loop index to select the corresponding image -->
                                <img class="location-img" src="{{ $locationImages[$index] }}"
                                    alt="location-img{{ $index + 1 }}">
                                <p class="small m-0">+100 miles</p>
                                <p class="text-danger">Store Info</p>
                            </a>
                        </div>
                        @endforeach --}}
                                @foreach ($branches as $index => $branch)
                                    <div class="d-flex justify-content-between location-card border-bottom py-3">
                                        <div class="d-flex align-items-start">
                                            <input class="form-check-input me-2" type="radio"
                                                id="location-{{ $branch->id }}" name="choosen_location"
                                                data-branch-id="{{ $branch->id }}"
                                                {{ $branch->status == 1 ? 'checked' : '' }}>
                                            <label for="location-{{ $branch->id }}" class="ms-1">
                                                <h6 class="small fw-bold m-0">{{ $branch->name }}</h6>
                                                <p class="small fw-bold m-0 branch-location">{{ $branch->location }}</p>
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
                                <!-- Location-5 -->
                            </div>
                            <!-- Location's Parent End -->
                        </div>
                        <div class="modal-footer">
                            <div class="text-center  mx-auto">
                                <button class="btn btn-danger px-5 updateLocationBtn" data-bs-dismiss="modal">Update
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
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d24143.571414161433!2d-73.88015888916016!3d40.85110000000003!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c2f4ab1d311eaf%3A0x3ffe46c5ab0f82ad!2sStarbucks!5e0!3m2!1sen!2sus!4v1761317780273!5m2!1sen!2sus"
                                style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                            <div class="p-3">
                                <h6 class="mb-3 locationHeading">Sugar Pappi</h6>
                                <p class="m-0 address1"></p>
                                <ul>
                                    @if ($userTimeSlots)
                                        <li>Pickup:
                                            {{ \Carbon\Carbon::parse($userTimeSlots->date)->format('d M, Y') }} at
                                            {{ $userTimeSlots->time }}</li>
                                    @else
                                        @foreach ($timeSlots as $timeSlot)
                                            <li>
                                                Today Pickup:
                                                {{ $timeSlot->start_pickup_time }}
                                            </li>
                                        @break
                                    @endforeach
                                @endif
                                <li>
                                    Estimated prep time: Available Immediately
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" style="font-size:30px"
                            class="btn location-description-btn ri-close-circle-line btn-danger px-2 py-0 me-auto"
                            data-bs-dismiss="modal"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Location Description Modal End -->

    <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">See it in Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- 16:9 aspect ratio -->
                    <div class="ratio ratio-16x9">
                        <iframe class="embed-responsive-item" src="" id="video" allowfullscreen
                            allowscriptaccess="always" allow="autoplay"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Reservation End -->

    <!-- Menu Start -->
    <!-- <div class="container-xxl py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container">
        <div class="text-center">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal">Our Menu's</h5>
            <h3 class="mb-5 col-sm-8 mx-auto">Featured Items</h3>
        </div> -->

    <!-- Image Popup -->
    <!-- <div id="imagePopup" class="popup">
            <div class="popup-content">
                <span class="close" id="closeImagePopup">&times;</span>
                <img id="popupImage" alt="Image">
            </div>
        </div>
        <div class="owl-carousel gallery-carousel">
            @foreach ($menuGalleries as $menuGallery)
{{-- <a class="col-sm-4 col-6 gallery-item" href="{{ asset($menuGallery->image) }}"
                data-lg-size="1600-2400"> --}}
                <a class="col-sm-4 col-6 gallery-item" data-lg-size="1600-2400">
                    <div class="bg-transparent border rounded p-4">
                        <img class="w-100 clickable-image" src="{{ asset($menuGallery->image) }}" alt="Gallery Image" />
                    </div>
                </a>
@endforeach
        </div>
        <div class="text-center">
            <a class="btn btn-primary py-3 px-5 mt-5" href="{{ route('get-menu-picture') }}">View All</a>
        </div>
    </div>
</div> -->
    <!-- Most Popular Start -->
    <div class="container-xxl pt-5 pb-3">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h5 class="section-title ff-secondary text-center fw-normal">Our Menu's</h5>
                <h3 class="mb-5 col-sm-8 mx-auto">Featured Items</h3>
            </div>
            <div class="owl-carousel popular-carousel gallery-carousel">
              @foreach ($products as $product)
@php
    $originalPrice = $product->original_price ?? $product->price;
    $finalPrice = $product->price;
    $discountPercent = 0;

    if ($product->featured_action == 'decrease' && $product->original_price) {
        if ($product->featured_method == 'percentage') {
            $discountPercent = (int) $product->featured_amount;
        } elseif ($product->featured_method == 'amount') {
            $discountPercent = round((($originalPrice - $finalPrice) / $originalPrice) * 100);
        }
    }

    // Complementary Product
    $comp = optional($product->complementaryProduct);
@endphp

<div class="item">
<a class="popular-item bg-transparent border rounded p-4 d-block text-center h-100 position-relative"
    href="#" data-bs-toggle="modal" data-bs-target="#menuModal-{{ $product->id }}">

@if($product->featured_action == 'decrease' && $discountPercent > 0)
<span class="badge bg-danger position-absolute top-0 end-0 m-2">
    {{ $discountPercent }}% OFF
</span>
@endif


{{-- Product + Complementary Section --}}
<div class="mb-3 d-flex justify-content-center align-items-center gap-2 flex-column">
    {{-- Complementary Product --}}
    @if(optional($comp)->complementary)
        {{-- Main Product --}}
            <img class="img-fluid" src="{{ asset($product->image) }}">
    
            <span style="font-size:22px;font-weight:bold;">+</span>
    
            <img class="img-fluid" src="{{ asset($comp->complementary->image) }}">
            <span class="badge bg-success m-2">
                BUY 1 GET 1 FREE
            </span>
    @else
        <img class="img-fluid" src="{{ asset($product->image) }}" style="width:150px;height:150px;object-fit:cover;border-radius:10px">
    @endif
</div>

<div class="mb-2">
<h5 class="mb-2 main-heading">{{ $product->name }}</h5>    
    @if(optional($comp)->complementary)
        <span>+</span>
        <h6 class="mt-2">{{ $comp->complementary->name }}</h6>        
    @endif
<p class="mb-2">

@if ($product->variants && $product->variants->isNotEmpty())

    @if($product->featured_action == 'decrease' && $product->original_price)

        <span class="text-muted text-decoration-line-through small d-block">
            £{{ number_format($originalPrice, 2) }}
        </span>

        <span class="badge bg-primary fs-6 py-2 px-3">
            From £{{ number_format($finalPrice, 2) }}
        </span>

    @else

        <span class="badge bg-primary fs-6 py-2 px-3">
            From £{{ $product->variants->first()->price }}
        </span>

    @endif

@else

    @if($product->featured_action == 'decrease' && $product->original_price)

        <span class="text-muted text-decoration-line-through small d-block">
            £{{ number_format($originalPrice, 2) }}
        </span>

        <span class="badge bg-primary fs-6 py-2 px-3">
            £{{ number_format($finalPrice, 2) }}
        </span>

    @else

        <span class="badge bg-primary fs-6 py-2 px-3">
            £{{ $product->price }}
        </span>

    @endif

@endif

</p>
</div>

</a>
</div>

@endforeach
            </div>

            <!-- <div class="text-center">
            <a class="btn btn-primary py-3 px-5 mt-5" href="{{ route('get-our-menu') }}">Explore More</a>
        </div> -->
            <!-- <div class="text-center">
            <a class="btn btn-primary py-3 px-5 mt-5" href="#">View All</a>
        </div> -->
        </div>
    </div>
    <!-- Menu End -->

    <!-- Full Menu Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="wow fadeInUp mb-2" data-wow-delay="0.1s">
                <div id="menuContainer"
                    class="d-flex flex-column align-items-center justify-content-center flex-wrap">
                    <div class="text-center">
                    @if ($timeSlots->isNotEmpty())
                        <h5 class="section-title ff-secondary fw-normal m-0">
                            {{ \Carbon\Carbon::parse($timeSlots->first()->start_pickup_time)->format('g:i A') }} –
                            {{ \Carbon\Carbon::parse($timeSlots->first()->end_pickup_time)->format('g:i A') }}
                        </h5>
						@endif
							<h3 class="m-0">Explore Our Complete Menu</h3>
						</div>
						<div class="w-100 d-flex align-items-center justify-content-end gap-2">
							<button class="tab-scroll-btn left" onclick="scrollTabs('left')">
								<span class="ri-arrow-left-line"></span>
							</button>
							<button class="tab-scroll-btn right" onclick="scrollTabs('right')">
								<span class="ri-arrow-right-line"></span>
							</button>
						</div>
					</div>
				</div>

				@if ($menuCategories && $menuCategories->isNotEmpty())
					<!-- Menu Tabs -->
					<div class="row mb-4">
						<div class="col-12">
							<div class="menu-tabs-wrapper">
								<div class="menu-tabs-container">
									<ul class="nav nav-tabs nav-justified menu-category-tabs" id="menuTabs"
										role="tablist">
										@foreach ($menuCategories as $index => $menuCat)
											<li class="nav-item" role="presentation">
												<button class="nav-link @if ($index == 0) active @endif"
													id="tab{{ $menuCat->id }}" data-bs-toggle="tab"
													data-bs-target="#menuTab{{ $menuCat->id }}" type="button"
													role="tab" aria-controls="menuTab{{ $menuCat->id }}"
													aria-selected="@if ($index == 0) true @else false @endif">
													{{ $menuCat->name }}
												</button>
											</li>
										@endforeach
									</ul>
								</div>
							</div>
						</div>
					</div>

					<!-- Menu Content -->
					<div class="tab-content" id="menuContent">
						@foreach ($menuCategories as $index => $menuCat)
							<div class="tab-pane fade @if ($index == 0) show active @endif"
								id="menuTab{{ $menuCat->id }}" role="tabpanel"
								aria-labelledby="tab{{ $menuCat->id }}">
								@if ($menuCat->product && $menuCat->product->isNotEmpty())
									<div class="row g-4">
										@foreach ($menuCat->product as $prod)
											@php
                                                $originalPrice = $prod->original_price ?? $prod->price;
                                                $finalPrice = $prod->price;
                                                $discountPercent = 0;

                                                if ($prod->featured_action == 'decrease' && $prod->original_price) {
                                                    if ($prod->featured_method == 'percentage') {
                                                        $discountPercent = (int) $prod->featured_amount;
                                                    } elseif ($prod->featured_method == 'amount') {
                                                        $discountPercent = round((($originalPrice - $finalPrice) / $originalPrice) * 100);
                                                    }
                                                }
                                            @endphp
											<div class="col-xl-3 col-lg-4 col-md-6">
												<a class="popular-item bg-transparent border rounded p-4 d-block text-start h-100 position-relative"
													href="#" data-bs-toggle="modal"
													data-bs-target="#menuModalFull-{{ $prod->id }}">
														@if($prod->featured_action == 'decrease' && $discountPercent > 0)
                                                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">{{ $discountPercent }}% OFF</span>
                                                        @endif
													<div class="text-center mb-3 d-flex justify-content-center align-items-center gap-2">

    {{-- Main Product --}}
    <img class="img-fluid"
        src="{{ asset($prod->image) }}"
        style="width:120px;height:120px;object-fit:cover;border-radius:10px;">
@php
$comp = optional($prod->complementaryProduct);
@endphp

@if(optional($comp)->complementary)

<span style="font-size:22px;font-weight:bold;">+</span>

<img class="img-fluid"
     src="{{ asset($comp->complementary->image) }}"
     style="width:90px;height:90px;object-fit:cover;border-radius:10px;">
	 
    <p class="mb-0 small mt-1" style= "color:#000;">{{ $comp->complementary->name }}</p>

@endif
</div>
													<div class="mb-2">
														<h5 class="mb-1 main-heading text-center">{{ $prod->name }}</h5>
														<p class="text-center mb-2">
															@if ($prod->variants && $prod->variants->isNotEmpty())
																@if($prod->featured_action == 'decrease' && $prod->original_price)
                                                                    <span class="text-muted text-decoration-line-through small d-block">£{{ number_format($originalPrice, 2) }}</span>
                                                                    <span class="badge bg-primary">From £{{ number_format($finalPrice, 2) }}</span>
                                                                @else
                                                                    <span class="badge bg-primary">From £{{ $prod->variants->first()->price }}</span>
                                                                @endif
															@else
																@if($prod->featured_action == 'decrease' && $prod->original_price)
                                                                    <span class="text-muted text-decoration-line-through small d-block">£{{ number_format($originalPrice, 2) }}</span>
                                                                    <span class="badge bg-primary">£{{ number_format($finalPrice, 2) }}</span>
                                                                @else
                                                                    <span class="badge bg-primary">£{{ $prod->price }}</span>
                                                                @endif
															@endif
														
														</p>
													</div>
													{{-- <p class="mb-0 text-muted small text-center">{!! $prod->description !!}</p> --}}
												</a>
											</div>
										@endforeach
									</div>
								@else
									<div class="alert alert-warning text-center">
										<h5>No products found in {{ $menuCat->name }}!</h5>
										<p class="mb-0">We're currently updating this section. Please check back soon!
										</p>
									</div>
								@endif
							</div>
						@endforeach
					</div>
				@endif
			</div>
		</div>
    <!-- Full Menu End -->

    <!-- FAQs Section Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h5 class="section-title ff-secondary text-center fw-normal">FAQ's</h5>
                <h1 class="mb-5">Frequently Asked Questions</h1>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="accordion" id="faqAccordion">
                        @foreach ($faqs as $index => $faq)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $faq->id }}">
                                    <button
                                        class="accordion-button @if ($index != 0) collapsed @endif"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{ $faq->id }}"
                                        aria-expanded="@if ($index == 0) true @else false @endif"
                                        aria-controls="collapse{{ $faq->id }}">
                                        <span class="badge bg-primary me-3">{{ $loop->iteration }}</span>
                                        {!! $faq->question !!}
                                    </button>
                                </h2>
                                <div id="collapse{{ $faq->id }}"
                                    class="accordion-collapse collapse @if ($index == 0) show @endif"
                                    aria-labelledby="heading{{ $faq->id }}" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        {!! $faq->answer !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- FAQs Section End -->

    <!-- Food Modals for Full Menu Start -->
    @if ($menuCategories && $menuCategories->isNotEmpty())
        @foreach ($menuCategories as $menuCat)
            @if ($menuCat->product && $menuCat->product->isNotEmpty())
                @foreach ($menuCat->product as $prod)
                
                @php
                    $originalPrice = $prod->original_price ?? $prod->price;
                    $finalPrice = $prod->price;
                    $discountPercent = 0;

                    if ($prod->featured_action == 'decrease' && $prod->original_price) {

                        if ($prod->featured_method == 'percentage') {
                            $discountPercent = (int) $prod->featured_amount;
                        } elseif ($prod->featured_method == 'amount') {
                            $discountPercent = round((($originalPrice - $finalPrice) / $originalPrice) * 100);
                        }
                    }
                @endphp

                <div class="container-fluid cart food-modal wow fadeIn" data-wow-delay="0.1s">
                    <div class="modal fade menu-modal" id="menuModalFull-{{ $prod->id }}" tabindex="-1"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-body p-0 scrollable">
                                    <input type="hidden" name="product_id" value="{{ $prod->id }}">
                                    
                                    <!-- IMAGE with Discount Badge -->
                                    <div class="position-relative">
                                        <img class="w-100" src="{{ asset($prod->image) }}" alt="product-img">
                                        
                                        @if($prod->featured_action == 'decrease' && $discountPercent > 0)
                                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                                {{ $discountPercent }}% OFF
                                            </span>
                                        @endif
                                    </div>

                                    <div class="p-3 description">
                                        <h3>{{ $prod->name }}</h3>

                                        <!-- PRICE SECTION with Discount Logic -->
                                        @if (count($prod->variants) > 0)
                                            @if($prod->featured_action == 'decrease' && $prod->original_price)
                                                <p>
                                                    <span class="text-muted text-decoration-line-through">
                                                        £{{ number_format($originalPrice,2) }}
                                                    </span>
                                                    <br>
                                                    <span class="text-danger fw-bold prodPrice">
                                                        {{ number_format($finalPrice,2) }}
                                                    </span>
                                                </p>
                                            @else
                                                <p>£ <span class="prodPrice">{{ $prod->variants->first()->price }}</span></p>
                                            @endif

                                            <select class="form-control bg-white ps-1 select-size"
                                                name="variant_id" id="sizeSelect" style="appearance: auto">
                                                @foreach ($prod->variants as $variant)
                                                    <option value="{{ $variant->id }} {{ $variant->price }}">
                                                        {{ $variant->size }}</option>
                                                @endforeach
                                            </select>
                                            <h6 class="small mt-1 mb-3">Note: Prices vary depending on the selected
                                                size</h6>
                                                
                                        @else
                                            @if($prod->featured_action == 'decrease' && $prod->original_price)
                                                <p>
                                                    <span class="text-muted text-decoration-line-through">
                                                        £{{ number_format($originalPrice,2) }}
                                                    </span>
                                                    <br>
                                                    <span class="text-danger fw-bold prodPrice">
                                                        {{ number_format($finalPrice,2) }}
                                                    </span>
                                                </p>
                                            @else
                                                <p>£ <span class="prodPrice">{{ $prod->price }}</span></p>
                                            @endif
                                        @endif

                                        {{-- <p class="small">{!! $prod->description !!}</p> --}}
                                        <div class="d-flex cart-btn">
                                            <button class="btn p-0 decrement" type="button">-</button>
                                            <input type="text" class="cart_input increment-input text-center"
                                                value="1" name="quantity"
                                                id="quantity_{{ $prod->id }}">
                                            <button class="btn p-0 increment" type="button">+</button>
                                        </div>
                                    </div>

                                    <!-- Location Start -->
                                    <div class="description p-3">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="">How to get it</h6>
                                            <h6 class="text-danger">Required</h6>
                                        </div>
                                        
                                        @foreach ($branches as $index => $branch)
                                            @if ($branch->status == 1)
                                                <div class="branch-option mb-3">
                                                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                                                    {{-- Store Pickup --}}
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                            type="radio" 
                                                            name="status_{{ $prod->id }}" 
                                                            id="pickupStatus{{ $prod->id }}_{{ $branch->id }}_{{ $index }}" 
                                                            value="1"
                                                            checked
                                                            onchange="toggleDelivery('{{ $prod->id }}', '{{ $branch->id }}_{{ $index }}')">

                                                        <label class="form-check-label fw-bold small" 
                                                            for="pickupStatus{{ $prod->id }}_{{ $branch->id }}_{{ $index }}">
                                                            Store Pickup
                                                        </label>
                                                    </div>

                                                    {{-- Pickup Address --}}
                                                    <p class="small fw-bold m-0 sel-location mt-1" 
                                                    id="storePickupSection{{ $prod->id }}_{{ $branch->id }}_{{ $index }}">
                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($branch->location) }}" 
                                                        target="_blank" 
                                                        style="text-decoration: none; color: inherit;">
                                                            {{ $branch->location }}
                                                        </a>
                                                    </p>

                                                    {{-- Home Delivery --}}
                                                    <div class="form-check mt-3">
                                                        <input class="form-check-input" 
                                                            type="radio" 
                                                            name="status_{{ $prod->id }}" 
                                                            id="homeStatus{{ $prod->id }}_{{ $branch->id }}_{{ $index }}" 
                                                            value="2" 
                                                            onchange="toggleDelivery('{{ $prod->id }}', '{{ $branch->id }}_{{ $index }}')">

                                                        <label class="form-check-label fw-bold small" 
                                                            for="homeStatus{{ $prod->id }}_{{ $branch->id }}_{{ $index }}">
                                                            Home Delivery
                                                        </label>
                                                    </div>

                                                    {{-- Delivery Address Input --}}
                                                    <div id="deliveryAddressField{{ $prod->id }}_{{ $branch->id }}_{{ $index }}" 
                                                        class="mt-2" style="display: none;">
                                                        <input type="text" 
                                                            name="delivery_address_{{ $prod->id }}" 
                                                            class="form-control" 
                                                            placeholder="Enter your delivery address">
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach

                                        
                                    </div>
                                    <!-- Location End -->

                                    <!-- Toppings Start-->
                                    @if ($prod->category && $prod->category->isNotEmpty())
                                        @foreach ($prod->category as $index => $category)
                                            <div class="description p-3">
                                                <div class="arrow" style="cursor: pointer"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#toppingFull{{ $index }}{{ $category->id }}">
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="m-0">{{ $category->getCategory->name }}</h6>
                                                        <h6 class="fw-normal m-0 d-flex align-items-center">
                                                            Optional
                                                            <span class="h5 m-0 p-0 ri-arrow-up-s-line"></span>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="collapse show"
                                                    id="toppingFull{{ $index }}{{ $category->id }}">
                                                    @php
                                                        $categoryToppings = App\Models\CategoryTopping::where(
                                                            'category_id',
                                                            $category->getCategory->id,
                                                        )->get();
                                                    @endphp
                                                    @foreach ($categoryToppings as $categoryTopping)
                                                        <div class="d-flex justify-content-between">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="toppings[]"
                                                                    id="toppingchekFull{{ $index }}{{ $category->id }}{{ $categoryTopping->topping->id }}"
                                                                    value="{{ $categoryTopping->topping->id }}"
                                                                    data-category-id="{{ $category->getCategory->id }}">
                                                                <label class="form-check-label m-0"
                                                                    for="toppingchekFull{{ $index }}{{ $category->id }}{{ $categoryTopping->topping->id }}">
                                                                    {{ $categoryTopping->topping->name }}
                                                                </label>
                                                            </div>
                                                            <p class="m-0">
                                                                {{ isset($categoryTopping->topping->price) ? '£' . $categoryTopping->topping->price : '' }}
                                                            </p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    <!-- Toppings End -->
                                </div>
                                <div class="modal-footer position-relative px-2">
                                    <button type="button"
                                        style="font-size: 24px;position: absolute;left: 0;width: 30px;height: 30px;display: flex;justify-content: center;align-items: center"
                                        class="btn time-modal-close ri-close-circle-line btn-danger px-2 ms-3 py-0"
                                        data-bs-dismiss="modal"></button>
                                    <div class="text-center mx-auto">
                                        <button class="btn btn-danger addto-cart px-sm-5 px-4"
                                            data-bs-dismiss="modal">Add To Order</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        @endforeach
    @endif
    <!-- Food Modals for Full Menu End -->

    <!-- About Start -->
    <!-- <div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 mt-lg-5 mt-0">
                <div class="row g-3">
                    <div class="col-6 text-start">
                        <img class="img-fluid rounded w-100 wow zoomIn about-us-az-1" data-wow-delay="0.1s"
                            src="{{ asset('public/img/az-1.jpg') }}">
                    </div>
                    <div class="col-6 text-start">
                        <img class="img-fluid rounded w-75 wow zoomIn about-us-az-2" data-wow-delay="0.3s"
                            src="{{ asset('public/img/az-2.JPG') }}" style="margin-top: 5rem">
                    </div>
                    <div class="col-6 text-end">
                        <img class="img-fluid rounded w-75 wow zoomIn about-us-az-2" data-wow-delay="0.5s"
                            src="{{ asset('public/img/az-16.jpg') }}">
                    </div>
                    <div class="col-6 text-end">
                        <img class="img-fluid rounded w-100 wow zoomIn about-us-az-1" data-wow-delay="0.7s"
                            src="{{ asset('public/img/az-3.jpg') }}">
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-sm-start text-center">
                <h5 class="section-title ff-secondary text-start text-primary fw-normal">Loyalty Points</h5>
                <h1 class="mb-4">Become A Loyal Member</h1>
                <p class="mb-4">Members enjoy exclusive benefits and access.</p>
                <p class="mb-4">For every dollar spent on an order, you'll earn 1 point.</p>
                <p class="mb-4">Receive a $5 reward upon reaching 150 points.</p>
                <a class="btn btn-primary py-3 px-5 mt-2" href="{{ route('loyality-points') }}">Loyalty Points</a>
            </div>
        </div>
    </div>
</div> -->
    <!-- About End -->
@endsection
@section('js')

    @if (\Illuminate\Support\Facades\Session::has('message'))
        <script>
            toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
        </script>
    @endif
    <script>
        $(function() {
            $(document).on('change', '.select-size', function() {
                let a = $(this).val().split(' ')[1];
                $(this).closest('.description').find('.prodPrice').text(a);
            })

            $(document).on('click', 'input:radio', function() {
                var chooseLocation = $('input[name="choosen_location"]:checked').siblings('label').find(
                    '.branch-location').text();
                $('.sel-location').text(chooseLocation);
                var dataBranchId = $(this).attr('data-branch-id');
                $('input[name="branch_id"]').val(dataBranchId);
            });

            $(document).on('click', 'a[data-bs-toggle="modal"]', function() {
                setTimeout(() => {
                    $('.modal.show .loc-input').prop('checked', true);
                }, 200);
            })

            //Description Dealing
            $(document).on('click', '.arrow', function() {
                let a = $(this).find('span');
                if (a.hasClass('ri-arrow-up-s-line')) {
                    a.removeClass('ri-arrow-up-s-line')
                    a.addClass('ri-arrow-down-s-line');
                } else {
                    a.addClass('ri-arrow-up-s-line')
                    a.removeClass('ri-arrow-down-s-line');
                }
            });

            //array to store the Google Map Location
            let arr = [{
                source: "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3018.0477543418483!2d-73.84479512475968!3d40.84887532922607!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c2f4ab4f07396d%3A0xe6c4ae3a2866d1b8!2sA%20Z%20Nutrition!5e0!3m2!1sen!2s!4v1700484957680!5m2!1sen!2s",
                locationHeading: 'Sugar Pappi',
                address1: '1578 Main Avenue Clifton, NJ 07011'
            }, {
                source: "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3011.8769358092172!2d-73.82903412475196!3d40.98417552090991!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c2933bd5635a09%3A0x502bc89c049525ac!2s2562%20Central%20Park%20Ave%2C%20Yonkers%2C%20NY%2010710%2C%20USA!5e0!3m2!1sen!2s!4v1700483134249!5m2!1sen!2s",
                locationHeading: 'Sugar Pappi',
                address1: '2562 Central Park Av yonkers, NY 10710'
            }, {
                source: "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3016.25629501146!2d-74.15504762475744!3d40.888192526811835!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c2fe99dc1b2ba9%3A0x84d65884be972356!2sA-Z%20Nutrition%26Smoothies!5e0!3m2!1sen!2s!4v1700485074231!5m2!1sen!2s",
                locationHeading: 'Sugar Pappi',
                address1: '1776 Eastchester Road Bronx, NY 10461'
            }, {
                source: "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d96659.29314183394!2d-74.34112495664061!3d40.79274319999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25566ba35d8cb%3A0x2cb13edad59b0cbc!2sAmazon%20Hub%20Counter%20-%20A-Z%20Nutrition%20%26%20Smoothies!5e0!3m2!1sen!2s!4v1700485211429!5m2!1sen!2s",
                locationHeading: 'Sugar Pappi',
                address1: '549 Bloomfield Avenue Bloomfield, NJ 07003'
            }, ];

            //Control Of Modals
            // $('.chose-location').click(function() {
            //     $('.menu-modal').modal('hide')
            // });
            // $('#locationModal').click(function() {
            //     event.stopPropagation();
            //     $('.menu-modal').modal('show');
            // });
            // $('#locationModal .modal-dialog').click(function() {
            //     event.stopPropagation();
            //     $('.menu-modal').modal('hide');
            // });
            // $('.location-close').click(function() {
            //     event.stopPropagation();
            //     $('.menu-modal').modal('show');
            //     $('#locationModal').modal('hide')
            // });
            // $('#locationDescription').click(function() {
            //     event.stopPropagation();
            //     $('#locationModal').modal('show');
            // });
            // $('#locationDescription').click(function() {
            //     event.stopPropagation();
            //     $('#locationModal').modal('show');
            // });
            // $('#locationDescription .modal-dialog').click(function() {
            //     event.stopPropagation();
            //     $('#locationModal').modal('hide');
            // });
            // $('.location-description-btn').click(function() {
            //     $('#locationDescription').modal('hide');
            //     $('#locationModal').modal('show');
            // });
            //this is updation of the Modal

            $('.location-description').each(function(index) {
                $(this).click(function() {
                    // event.stopPropagation();
                    // $('#locationModal').modal('hide');
                    let i = index;
                    let a = arr[i];
                    //Changing location-description Modal;
                    $('.locationHeading').text(a.locationHeading);
                    $('.address1').text(a.address1);
                    $('.address2').text(a.address2);
                    $('iframe').attr('src', a.source);
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            // Get all increment and decrement buttons
            const incrementButtons = document.querySelectorAll('.increment');
            const decrementButtons = document.querySelectorAll('.decrement');

            // Attach click event listeners to each button
            incrementButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('.cart_input');
                    input.value = parseInt(input.value) + 1;
                });
            });

            decrementButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('.cart_input');
                    const value = parseInt(input.value) - 1;
                    input.value = value >= 1 ? value : 1;
                });
            });
        });

    //      $('.addto-cart').on('click', function() {
    //     var productId = $(this).closest('.food-modal').find('input[name="product_id"]').val();
    //     var quantity = $(this).closest('.food-modal').find('input[name="quantity"]').val();
    //     var isLocationChecked = $(this).closest('.food-modal').find('input[name="location"]:checked').length >
    //         0;
    //     var branchId = isLocationChecked ? $(this).closest('.food-modal').find('input[name="branch_id"]')
    //         .val() : '';
    //     var variantId = $(this).closest('.food-modal').find('select[name="variant_id"]').val().split(' ')[0]; // Get selected variant ID
    //     var toppings = $(this).closest('.food-modal').find('input[name="toppings[]"]:checked').map(function() {
    //         return $(this).val();

    //     }).get();

    //     $.ajax({
    //         type: 'POST',
    //         url: '{{ route('add.to.cart') }}',
    //         data: {
    //             '_token': '{{ csrf_token() }}',
    //             'product_id': productId,
    //             'quantity': quantity,
    //             'branch_id': branchId,
    //             'toppings': toppings,
    //             'location': isLocationChecked,
    //             'variant_id': variantId
    //         },
    //         success: function(data) {
    //             toastr.success('Product Added To Cart Successfully!');
    //             updateCartUI(data);
    //         },
    //         error: function(error) {
    //             console.error('Error adding product to cart:', error);
    //         }
    //     });
    // });  

        // last commit
        $('.addto-cart').on('click', function() {
            var productId = $(this).closest('.food-modal').find('input[name="product_id"]').val();
            var quantity = $(this).closest('.food-modal').find('input[name="quantity"]').val();
            var isLocationChecked = $(this).closest('.food-modal').find('input[name="location"]:checked').length > 0;
            var branchId = isLocationChecked ? $(this).closest('.food-modal').find('input[name="branch_id"]').val() : '';
            var variantId = '';

            // Get delivery status and address
            var deliveryStatus = $(this).closest('.food-modal').find('input[name="status_' + productId + '"]:checked').val();
            var deliveryAddress = '';
            if (deliveryStatus == '2') { // If home delivery
                deliveryAddress = $(this).closest('.food-modal').find('input[name="delivery_address_' + productId + '"]').val();
                if (!deliveryAddress) {
                    toastr.error('Please enter delivery address');
                    return;
                }
            }

            // Check if the product has variants
            var variantSelect = $(this).closest('.food-modal').find('select[name="variant_id"]');
            if (variantSelect.length > 0) {
                variantId = variantSelect.val().split(' ')[0];
            }

            // Initialize an object to store selected toppings by category
            var selectedToppingsByCategory = {};

            // Get selected toppings
            $(this).closest('.food-modal').find('input[name="toppings[]"]:checked').each(function() {
                var categoryId = $(this).data('category-id');
                var toppingId = $(this).val();

                // Check if category ID already exists in the object
                if (!selectedToppingsByCategory.hasOwnProperty(categoryId)) {
                    selectedToppingsByCategory[categoryId] = [];
                }

                selectedToppingsByCategory[categoryId].push(toppingId);
            });

            // Convert the object to an array of objects
            var toppingsArray = Object.entries(selectedToppingsByCategory).map(([categoryId, toppings]) => {
                return {
                    category_id: categoryId,
                    toppings: toppings
                };
            });

            // Send AJAX request
            $.ajax({
                type: 'POST',
                url: '{{ route('add.to.cart') }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'product_id': productId,
                    'quantity': quantity,
                    'branch_id': branchId,
                    'toppings_by_category': toppingsArray,
                    'location': isLocationChecked,
                    'variant_id': variantId,
                    'delivery_status': deliveryStatus,
                    'delivery_address': deliveryAddress
                },
                success: function(data) {
                    toastr.success('Product Added To Cart Successfully!');
					location.reload();
                    // Update all cart counters in the header immediately
                    $('.cart-counter-1').text(Object.keys(data.cart).length);
                    // Update the cart UI
                    updateCartUI(data);
                },
                error: function(error) {
                    console.error('Error adding product to cart:', error);
                }
            });
        });


        function updateCartUI(data) {
            var cartItemCount = 0;
            var html = '';
            jQuery.each(data['cart'], function(i, product) {
                cartItemCount += parseInt(product.quantity);
                html += '<div class="carting-child px-3 mt-3 d-flex justify-content-between pb-3 border-bottom" id="' + product.product_id + 'carted">';
                html += '<img src="' + product.image +
                    '" alt=""><div class="content"><div class="d-flex cart-input-parent justify-content-between">';
                html += '<h6 class="m-0">' + product.name;
                // Check if size exists and wrap it in a span within parentheses
                html += product.size ? ' (<span style="font-size: 12px;">' + product.size + '</span>)' : '';
                html += '</h6><h6 class="m-0 total-price">£' + ((parseFloat(product.price) * product.quantity)
                        .toFixed(2)) +
                    '</h6><p class="product-price d-none">' + product.price + '</p></div>';
                
                // Add delivery status and address
                html += '<div class="delivery-info mb-2">';
                html += '<p class="small m-0 text-' + (product.delivery_status == '2' ? 'info' : 'success') + '">';
                html += product.delivery_status == '2' ? 'Home Delivery' : 'Store Pickup';
                html += '</p>';
                // if (product.delivery_status == '2' && product.delivery_address) {
                //     html += '<p class="small m-0">Delivery to: ' + product.delivery_address + '</p>';
                // }
                html += '</div>';

                html += '<div class="mb-2"><h6 class="m-0">Toppings</h6>';
                if (product.toppingsName_by_categoryName) {
                    $.each(product.toppingsName_by_categoryName, function(index, category) {
                        html += '<div class="mb-2">';
                        html += '<p class="category-name mb-1 fw-bold pb-1 text-black">' + category.category_name + '</p>';
                        $.each(category.topping_names, function(i, topping) {
                            html += '<p class="small m-0">' + topping + '</p>';
                        });
                        html += '</div>';
                    });
                }

                html += '</div><div class="cart-btn">';
                html += '<button class="btn decrement-btn p-0" data-product-id="' + product.product_id + ',' +
                    product.variant_id +
                    '">-</button>';
                html += '<input type="number" name="quantity" value="' + product.quantity +
                    '" class="increment-input cart-input cart_input text-center">';
                html += '<button class="btn increment-btn p-0" data-product-id="' + product.product_id + ',' +
                    product.variant_id +
                    '">+</button>';
                html += '<p id="' + product.product_id + '" class="d-none sibling-p"></p>';
                html += '</div></div></div>';
            });

            $('.cart-counter-1').text(cartItemCount);
            $('.cards-parent').html(html);
            // Add event listeners for increment and decrement buttons in the updated UI


            if (cartItemCount > 0) {
                $('.button-disable').removeClass('disabled');
            }

            function updateQuantity(productId, change) {
                var product = data['cart'].find(p => p.product_id === productId);
                if (product) {
                    product.quantity += change;

                    // Update the total price dynamically
                    var totalElement = $('.total-price[data-product-id="' + productId + '"]');
                    var newTotalPrice = '£' + ((parseFloat(product.price) * product.quantity).toFixed(2));
                    totalElement.text(newTotalPrice);
                }
            }
        }


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
                    toastr.success('Location Updated Successful');
                    console.log(response.message);

                    // Reload the page after a short delay
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(error) {
                    console.error('Error updating branch status:', error);
                }
            });

        });

    //     $('.sizeSelect').change(function () {
    //     // Get the selected option
    //     var selectedOption = $(this).find(':selected');
    //     alert(selectedOption);
    //     var selectedSize = selectedOption.data('size');

    //     // Make an Ajax request to fetch the price
    //     $.ajax({
    //         url: '/get-price', // Change this to your Laravel route
    //         type: 'GET',
    //         data: { size: selectedSize },
    //         success: function (data) {
    //             // Update the price input field with the received data
    //             $('#selSizePrice').val(data.price);
    //         },
    //         error: function (error) {
    //             console.log(error);
    //         }
    //     });
    // });  

        // Menu Tabs Scrolling Function
        function scrollTabs(direction) {
            const tabsContainer = document.querySelector('.menu-category-tabs');
            const scrollAmount = 200;
            const currentScroll = tabsContainer.scrollLeft;

            if (direction === 'left') {
                tabsContainer.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            } else if (direction === 'right') {
                tabsContainer.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            }
        }

        // Check scroll position and update arrow states
        document.addEventListener('DOMContentLoaded', function() {
            const tabsContainer = document.querySelector('.menu-category-tabs');
            const leftBtn = document.querySelector('.tab-scroll-btn.left');
            const rightBtn = document.querySelector('.tab-scroll-btn.right');

            if (tabsContainer && leftBtn && rightBtn) {
                function updateButtons() {
                    const isScrollable = tabsContainer.scrollWidth > tabsContainer.clientWidth;

                    if (!isScrollable) {
                        leftBtn.style.display = 'none';
                        rightBtn.style.display = 'none';
                    } else {
                        const isAtStart = tabsContainer.scrollLeft === 0;
                        const isAtEnd = tabsContainer.scrollLeft + tabsContainer.clientWidth >= tabsContainer
                            .scrollWidth - 10;

                        leftBtn.classList.toggle('disabled', isAtStart);
                        rightBtn.classList.toggle('disabled', isAtEnd);
                    }
                }

                tabsContainer.addEventListener('scroll', updateButtons);
                updateButtons();
            }
        });
    </script>

<script>
function toggleDelivery(productId, branchUnique) {
    const pickupRadio = document.getElementById(`pickupStatus${productId}_${branchUnique}`);
    const homeRadio = document.getElementById(`homeStatus${productId}_${branchUnique}`);
    const pickupSection = document.getElementById(`storePickupSection${productId}_${branchUnique}`);
    const deliveryField = document.getElementById(`deliveryAddressField${productId}_${branchUnique}`);

    if (homeRadio.checked) {
        pickupSection.style.display = 'none';
        deliveryField.style.display = 'block';
    } else if (pickupRadio.checked) {
        pickupSection.style.display = 'block';
        deliveryField.style.display = 'none';
        deliveryField.querySelector('input').value = '';
    }
}

</script>




@endsection