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

        .about-us-az-1 { height: 285px; object-fit: cover; }
        .about-us-az-2 { height: 205px; object-fit: cover; }

        @media (max-width: 767px) {
            .about-us-az-1 { height: 225px; }
            .about-us-az-2 { height: 145px; }
        }

        .accordion-button { font-weight: 500; }
        .accordion-button:not(.collapsed) { background-color: #f8f9fa; color: #212529; }
        .accordion-button:focus { box-shadow: none; border-color: transparent; }

        .menu-category-tabs { border-bottom: 2px solid #dee2e6; }
        .menu-category-tabs .nav-item { margin-bottom: -2px; }
        .menu-category-tabs .nav-link {
            color: #6c757d; border: none;
            border-bottom: 3px solid transparent;
            padding: 15px 20px; font-weight: 600;
            text-transform: uppercase; font-size: 14px; transition: all 0.3s;
        }
        .menu-category-tabs .nav-link:hover,
        .menu-category-tabs .nav-link.active {
            color: #dc3545; border-bottom-color: #dc3545; background-color: transparent;
        }

        .popular-item { transition: transform 0.3s, box-shadow 0.3s; cursor: pointer; }
        .popular-item:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        @media (max-width: 768px) {
            .menu-category-tabs { overflow-x: auto; display: flex; flex-wrap: nowrap; }
            .menu-category-tabs .nav-item { flex-shrink: 0; }
            .menu-category-tabs .nav-link { padding: 10px 15px; font-size: 12px; }
        }

        .owl-carousel .item { padding: 10px; width: 100%; }
        .owl-carousel .popular-item { height: 100%; min-height: 350px; display: flex; flex-direction: column; justify-content: space-between; }
        .owl-carousel .owl-item, .owl-carousel .owl-stage { display: flex; }
        .owl-carousel .owl-item .item { height: 100%; width: 100%; }

        .menu-tabs-wrapper { position: relative; }
        .menu-tabs-container { overflow: hidden; position: relative; }
        .menu-category-tabs {
            overflow-x: auto; scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch; white-space: nowrap;
            display: flex !important; gap: 10px; flex-wrap: nowrap !important;
        }
        .menu-category-tabs .nav-item { flex-shrink: 0 !important; display: inline-block; }
        .menu-category-tabs .nav-link { white-space: nowrap !important; flex-shrink: 0; }

        .menu-tabs-container .tab-scroll-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: white; border: 2px solid #dc3545; color: #dc3545;
            width: 45px; height: 45px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 10; transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .menu-tabs-container .tab-scroll-btn:hover { background: #dc3545; color: white; transform: translateY(-50%) scale(1.1); }
        .menu-tabs-container .tab-scroll-btn.left { left: -15px; }
        .menu-tabs-container .tab-scroll-btn.right { right: -15px; }
        .menu-tabs-container .tab-scroll-btn.disabled { opacity: 0.3; cursor: not-allowed; }
        .menu-tabs-container .tab-scroll-btn.disabled:hover { transform: translateY(-50%) scale(1); background: white; color: #dc3545; }

        .menu-category-tabs::-webkit-scrollbar { display: none; }
        .menu-category-tabs { -ms-overflow-style: none; scrollbar-width: none; }
        .menu-category-tabs .nav-link { white-space: nowrap; padding: 12px 25px !important; }

        /* ✅ FIX: pac-container z-index for modal */
        .pac-container { z-index: 99999 !important; }

        .prodPrice { font-weight: bold; font-size: 1.2rem; }
        .price-display { font-size: 1.2rem; font-weight: bold; }
    </style>

    @include('home.partials.landing-hero')

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
                                <p class="mb-0 small">Aldow Industrial Estate, Pod 10, Unit D, Jacuna Kitchen, Ardwick,, Manchester, EMEA M12 6AE</p>
                                <p class="small text-dark">Sugar Pappi in Chorltonne upon Medlock, Manchester, is a dessert
                                    spot that enjoys a high customer rating of 4.8...</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-end del-pickup-container">
                                    <ul class="nav nav-pills mb-0 d-flex align-items-center custom-pills" id="pillsDelPickup" role="tablist">
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link active" id="pillsDeliveryTab" data-bs-toggle="pill"
                                                data-bs-target="#pillsDelivery" type="button" role="tab">Delivery</button>
                                        </li>
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link" id="pillsPickupTab" data-bs-toggle="pill"
                                                data-bs-target="#pillsPickup" type="button" role="tab">Pick-up</button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content mt-3" id="pillsDelPickup">
                                    <div class="tab-pane fade show active" id="pillsDelivery" role="tabpanel">
                                        <div class="delivery-table">
                                            <div class="delivery-col"><p class="title">Delivery Fee</p><a href="#">Other Fees</a></div>
                                            <div class="delivery-col"><p class="title">Delivery Unavailable</p><p>Delivery Time</p></div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="pillsPickup" role="tabpanel">
                                        <div class="delivery-table">
                                            <div class="delivery-col"><p class="title">£0.00</p><a href="#">Other Fees</a></div>
                                            <div class="delivery-col"><p class="title">Closed</p><p>Pick-up time</p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Cibo Express removed. Dessert Wholesale lives at /dessert-wholesale --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

        {{-- =====================================================
             HELPER MACRO: compute discount for any product+variant
             ===================================================== --}}
        @php
        /**
         * ✅ FIX: Centralized discount calculation
         *
         * DB structure:
         *   products.price          = 0 (base, often 0 when variants exist)
         *   products.original_price = original non-discounted price (e.g. 5.50)
         *   products.featured_action= 'decrease'
         *   products.featured_method= 'percentage' | 'fixed amount'
         *   products.featured_amount= numeric amount
         *
         *   variants.price          = discounted/adjusted price (e.g. 17, 19)
         *   variants.original_price = base price before discount (e.g. 18, 20)
         *                             (mapped from "Base Price" column in admin)
         *
         * So for variants: original_price=base, price=adjusted(discounted)
         * Discount % = round((original - adjusted) / original * 100)
         */
        function calcDiscount($product) {
            $hasVariants = $product->variants && $product->variants->count() > 0;

            // ── Pick the "display" variant ──────────────────────────────
            $displayVariant = null;
            if ($hasVariants) {
                // prefer 'regular', else first with price > 0
                $displayVariant = $product->variants->where('size', 'regular')->first();
                if (!$displayVariant || $displayVariant->price <= 0) {
                    $displayVariant = $product->variants->where('price', '>', 0)->first();
                }
                if (!$displayVariant) {
                    $displayVariant = $product->variants->first();
                }
            }

            // ── Prices ──────────────────────────────────────────────────
            if ($hasVariants && $displayVariant) {
                // variant.original_price = base price (£18, £20)
                // variant.price          = discounted price (£17, £19)
                $originalPrice = floatval($displayVariant->original_price ?? $displayVariant->price);
                $finalPrice    = floatval($displayVariant->price);
            } else {
                $originalPrice = floatval($product->original_price ?? $product->price);
                $finalPrice    = floatval($product->price);
            }

            // ── Discount ────────────────────────────────────────────────
            $hasDiscount    = false;
            $discountPercent = 0;
            $badgeText      = '';

            if ($product->featured_action == 'decrease' && $originalPrice > $finalPrice && $originalPrice > 0) {
                $hasDiscount = true;

                if ($product->featured_method == 'percentage' && $product->featured_amount > 0) {
                    $discountPercent = (int) $product->featured_amount;
                    $badgeText = $discountPercent . '% OFF';
                } else {
                    // ✅ FIX: fixed amount — show amount, not percentage
                    $badgeText = '£' . number_format($product->featured_amount, 0) . ' OFF';
                    $discountPercent = 1; // flag for badge display
                }
            }

            return [
                'hasVariants'     => $hasVariants,
                'displayVariant'  => $displayVariant,
                'originalPrice'   => $originalPrice,
                'finalPrice'      => $finalPrice,
                'hasDiscount'     => $hasDiscount,
                'discountPercent' => $discountPercent,
                'badgeText'       => $badgeText,
                'comp'            => optional($product->complementaryProductSingle),
            ];
        }
        @endphp

        {{-- ===========================
             FOOD MODALS (Featured loop)
             =========================== --}}
        @foreach ($products as $product)
        @php $d = calcDiscount($product); @endphp
        <div class="container-fluid cart food-modal wow fadeIn" data-wow-delay="0.1s">
            <div class="modal fade menu-modal" id="menuModal-{{ $product->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-body p-0 scrollable">
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div class="position-relative">
                                <img class="w-100" src="{{ asset($product->image) }}" alt="product-img">
                                @if($d['hasDiscount'])
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-3 fs-6">
                                        {{ $d['badgeText'] }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-3 description">
                                <h3>{{ $product->name }}</h3>

                                @if(optional($d['comp'])->complementary)
                                    <input type="hidden" value="{{ $d['comp']->complementary->id }}" name="complementary_id">
                                    <div class="mt-3 text-center">
                                        <img class="img-fluid rounded-circle"
                                             src="{{ asset($d['comp']->complementary->image) }}"
                                             alt="{{ $d['comp']->complementary->name }}"
                                             style="width:100px;height:100px;object-fit:cover;">
                                        <br>
                                        <span class="badge bg-success m-2">BUY 1 GET 1 FREE</span>
                                        <p class="mb-0 small fw-medium text-dark">{{ $d['comp']->complementary->name }}</p>
                                    </div>
                                @endif

                                {{-- ── Price display ── --}}
                                @if ($d['hasVariants'])
                                    <p class="price-display mb-1">
                                        {{-- ✅ strikethrough original_price from variant --}}
                                        <span class="text-muted text-decoration-line-through d-block variant-original-price"
                                            @if(!($d['originalPrice'] > 0 && $d['originalPrice'] > $d['finalPrice'])) style="display:none!important" @endif>
                                            £{{ number_format($d['originalPrice'], 2) }}
                                        </span>
                                        £ <span class="prodPrice">{{ number_format($d['finalPrice'], 2) }}</span>
                                    </p>

                                    <select class="form-control bg-white ps-1 select-size" name="variant_id" style="appearance: auto">
                                        @foreach ($product->variants as $variant)
                                            <option value="{{ $variant->id }} {{ $variant->price }}"
                                                data-original="{{ $variant->original_price ?? 0 }}"
                                                {{ $loop->first ? 'selected' : '' }}>
                                                {{ $variant->size }} - £{{ number_format($variant->price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <h6 class="small mt-1 mb-3">Note: Prices vary depending on the selected size</h6>
                                @else
                                    @if($d['hasDiscount'])
                                        <p>
                                            <span class="text-muted text-decoration-line-through">£{{ number_format($d['originalPrice'], 2) }}</span><br>
                                            <span class="text-danger fw-bold prodPrice">£{{ number_format($d['finalPrice'], 2) }}</span>
                                        </p>
                                    @else
                                        <p>£ <span class="prodPrice">{{ number_format($d['finalPrice'], 2) }}</span></p>
                                    @endif
                                @endif

                                <div class="d-flex cart-btn">
                                    <button class="btn p-0 decrement" type="button">-</button>
                                    <input type="text" class="cart_input increment-input text-center" value="1" name="quantity">
                                    <button class="btn p-0 increment" type="button">+</button>
                                </div>
                            </div>

                            {{-- Location --}}
                            <div class="description p-3">
                                <div class="d-flex justify-content-between">
                                    <h6>How to get it</h6>
                                    <h6 class="text-danger">Required</h6>
                                </div>
                                @foreach ($branches as $index => $branch)
                                    @if ($branch->status == 1)
                                        <div class="branch-option mb-3">
                                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="status_{{ $product->id }}_{{ $branch->id }}"
                                                    id="pickupStatus{{ $product->id }}_{{ $branch->id }}"
                                                    value="1" checked
                                                    onchange="toggleDelivery('{{ $product->id }}', '{{ $branch->id }}')">
                                                <label class="form-check-label fw-bold small" for="pickupStatus{{ $product->id }}_{{ $branch->id }}">Store Pickup</label>
                                            </div>
                                            <p class="small fw-bold m-0 sel-location mt-1" id="storePickupSection{{ $product->id }}_{{ $branch->id }}">
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($branch->location) }}"
                                                   target="_blank" style="text-decoration:none;color:inherit;">
                                                    {{ $branch->location }}
                                                </a>
                                            </p>
                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="radio"
                                                    name="status_{{ $product->id }}_{{ $branch->id }}"
                                                    id="homeStatus{{ $product->id }}_{{ $branch->id }}"
                                                    value="2"
                                                    onchange="toggleDelivery('{{ $product->id }}', '{{ $branch->id }}')">
                                                <label class="form-check-label fw-bold small" for="homeStatus{{ $product->id }}_{{ $branch->id }}">Home Delivery</label>
                                            </div>
                                            <div id="deliveryAddressField{{ $product->id }}_{{ $branch->id }}" class="mt-2" style="display:none;">
                                                <input type="text"
                                                    id="deliveryInput{{ $product->id }}_{{ $branch->id }}"
                                                    name="delivery_address_{{ $product->id }}"
                                                    class="form-control location-input"
                                                    data-product="{{ $product->id }}"
                                                    data-branch="{{ $branch->id }}"
                                                    placeholder="Enter your delivery address"
                                                    autocomplete="off" />
                                                <input type="hidden" name="lat_{{ $product->id }}" id="lat{{ $product->id }}_{{ $branch->id }}">
                                                <input type="hidden" name="lng_{{ $product->id }}" id="lng{{ $product->id }}_{{ $branch->id }}">
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Toppings --}}
                            @if ($product->category->isNotEmpty())
                                @foreach ($product->category as $index => $category)
                                    <div class="description p-3">
                                        <div class="arrow" style="cursor:pointer"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#topping{{ $index }}{{ $category->id }}">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="m-0">{{ $category->getCategory->name }}</h6>
                                                <h6 class="fw-normal m-0 d-flex align-items-center">Optional <span class="h5 m-0 p-0 ri-arrow-up-s-line"></span></h6>
                                            </div>
                                        </div>
                                        <div class="collapse show" id="topping{{ $index }}{{ $category->id }}">
                                            @php $categoryToppings = App\Models\CategoryTopping::where('category_id', $category->getCategory->id)->get(); @endphp
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
                                                    <p class="m-0">{{ isset($categoryTopping->topping->price) ? '£'.$categoryTopping->topping->price : '' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="modal-footer position-relative px-2">
                            <button type="button"
                                style="font-size:24px;position:absolute;left:0;width:30px;height:30px;display:flex;justify-content:center;align-items:center"
                                class="btn time-modal-close ri-close-circle-line btn-danger px-2 ms-3 py-0"
                                data-bs-dismiss="modal"></button>
                            <div class="text-center mx-auto">
                                <button class="btn btn-danger addto-cart px-sm-5 px-4">Add To Order</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        {{-- ========================
             FEATURED / POPULAR CARDS
             ======================== --}}
        <div class="container-xxl pt-5 pb-3" id="featured-items">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center fw-normal">Featured</h5>
                    <h3 class="mb-5 col-sm-8 mx-auto">Featured Items</h3>
                </div>
                <div class="owl-carousel popular-carousel gallery-carousel">
                    @foreach ($products as $product)
                    @php $d = calcDiscount($product); @endphp
                    <div class="item">
                        <a class="popular-item bg-transparent border rounded p-4 d-block text-center h-100 position-relative"
                            href="#" data-bs-toggle="modal" data-bs-target="#menuModal-{{ $product->id }}">

                            @if($d['hasDiscount'])
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                    {{ $d['badgeText'] }}
                                </span>
                            @endif

                            <div class="mb-3 d-flex justify-content-center align-items-center gap-2 flex-column">
                                @if(optional($d['comp'])->complementary)
                                    <img class="img-fluid" src="{{ asset($product->image) }}" style="width:150px;height:150px;object-fit:cover;border-radius:10px">
                                    <span style="font-size:22px;font-weight:bold;">+</span>
                                    <img class="img-fluid" src="{{ asset($d['comp']->complementary->image) }}" style="width:120px;height:120px;object-fit:cover;border-radius:10px">
                                    <span class="badge bg-success m-2">BUY 1 GET 1 FREE</span>
                                    <p class="mb-0 small text-center">{{ $d['comp']->complementary->name }}</p>
                                @else
                                    <img class="img-fluid" src="{{ asset($product->image) }}" style="width:150px;height:150px;object-fit:cover;border-radius:10px">
                                @endif
                            </div>

                            <div class="mb-2">
                                <h5 class="mb-2 main-heading">{{ $product->name }}</h5>
                                @if(optional($d['comp'])->complementary)
                                    <h6 class="mt-2">{{ $d['comp']->complementary->name }}</h6>
                                @endif
                                <p class="mb-2">
                                    @if($d['hasVariants'])
                                        @if($d['hasDiscount'])
                                            <span class="text-muted text-decoration-line-through small d-block">£{{ number_format($d['originalPrice'], 2) }}</span>
                                            <span class="badge bg-primary fs-6 py-2 px-3">From £{{ number_format($d['finalPrice'], 2) }}</span>
                                        @else
                                            <span class="badge bg-primary fs-6 py-2 px-3">From £{{ number_format($d['finalPrice'], 2) }}</span>
                                        @endif
                                    @else
                                        @if($d['hasDiscount'])
                                            <span class="text-muted text-decoration-line-through small d-block">£{{ number_format($d['originalPrice'], 2) }}</span>
                                            <span class="badge bg-primary fs-6 py-2 px-3">£{{ number_format($d['finalPrice'], 2) }}</span>
                                        @else
                                            <span class="badge bg-primary fs-6 py-2 px-3">£{{ number_format($d['finalPrice'], 2) }}</span>
                                        @endif
                                    @endif
                                </p>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ================
             FULL MENU TABS
             ================ --}}
        <div class="container-xxl py-5">
            <div class="container">
                <div class="wow fadeInUp mb-2" data-wow-delay="0.1s">
                    <div id="menuContainer" class="d-flex flex-column align-items-center justify-content-center flex-wrap">
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
                            <button class="tab-scroll-btn left" onclick="scrollTabs('left')"><span class="ri-arrow-left-line"></span></button>
                            <button class="tab-scroll-btn right" onclick="scrollTabs('right')"><span class="ri-arrow-right-line"></span></button>
                        </div>
                    </div>
                </div>

                @if ($menuCategories && $menuCategories->isNotEmpty())
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="menu-tabs-wrapper">
                                <div class="menu-tabs-container">
                                    <ul class="nav nav-tabs nav-justified menu-category-tabs" id="menuTabs" role="tablist">
                                        @foreach ($menuCategories as $index => $menuCat)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link @if($index==0) active @endif"
                                                    id="tab{{ $menuCat->id }}" data-bs-toggle="tab"
                                                    data-bs-target="#menuTab{{ $menuCat->id }}" type="button">
                                                    {{ $menuCat->name }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="menuContent">
                        @foreach ($menuCategories as $index => $menuCat)
                            <div class="tab-pane fade @if($index==0) show active @endif"
                                id="menuTab{{ $menuCat->id }}" role="tabpanel">

                                @if ($menuCat->product && $menuCat->product->isNotEmpty())
                                    <div class="row g-4">
                                        @foreach ($menuCat->product as $prod)
                                        @php $d = calcDiscount($prod); @endphp
                                        <div class="col-xl-3 col-lg-4 col-md-6">
                                            <a class="popular-item bg-transparent border rounded p-4 d-block text-center h-100 position-relative"
                                                href="#" data-bs-toggle="modal"
                                                data-bs-target="#menuModalFull-{{ $prod->id }}">

                                                @if($d['hasDiscount'])
                                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">{{ $d['badgeText'] }}</span>
                                                @endif

                                                <div class="text-center mb-3">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <img class="img-fluid rounded"
                                                             src="{{ asset($prod->image) }}"
                                                             alt="{{ $prod->name }}"
                                                             style="width:130px;height:130px;object-fit:cover;">
                                                        @if(optional($d['comp'])->complementary)
                                                            <span class="mx-2" style="font-size:24px;font-weight:bold;color:#000;">+</span>
                                                            <div class="text-center">
                                                                <img class="img-fluid rounded"
                                                                     src="{{ asset($d['comp']->complementary->image) }}"
                                                                     alt="{{ $d['comp']->complementary->name }}"
                                                                     style="width:100px;height:100px;object-fit:cover;">
                                                                <br>
                                                                <span class="badge bg-success m-2">BUY 1 GET 1 FREE</span>
                                                                <p class="mb-0 small mt-2 fw-medium text-dark">{{ $d['comp']->complementary->name }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="mb-2">
                                                    <h5 class="mb-1 main-heading text-center">{{ $prod->name }}</h5>
                                                    <p class="text-center mb-2">
                                                        @if($d['hasVariants'])
                                                            @if($d['hasDiscount'])
                                                                <span class="text-muted text-decoration-line-through small d-block">£{{ number_format($d['originalPrice'], 2) }}</span>
                                                                <span class="badge bg-primary">From £{{ number_format($d['finalPrice'], 2) }}</span>
                                                            @else
                                                                <span class="badge bg-primary">From £{{ number_format($d['finalPrice'], 2) }}</span>
                                                            @endif
                                                        @else
                                                            @if($d['hasDiscount'])
                                                                <span class="text-muted text-decoration-line-through small d-block">£{{ number_format($d['originalPrice'], 2) }}</span>
                                                                <span class="badge bg-primary">£{{ number_format($d['finalPrice'], 2) }}</span>
                                                            @else
                                                                <span class="badge bg-primary">£{{ number_format($d['finalPrice'], 2) }}</span>
                                                            @endif
                                                        @endif
                                                    </p>
                                                </div>
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning text-center">
                                        <h5>No products found in {{ $menuCat->name }}!</h5>
                                        <p class="mb-0">We're currently updating this section. Please check back soon!</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning text-center">
                        <h5>No menu categories found!</h5>
                        <p class="mb-0">We're currently updating the menu. Please check back soon!</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========
             FAQs
             ======== --}}
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
                                        <button class="accordion-button @if($index!=0) collapsed @endif"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $faq->id }}"
                                            aria-expanded="@if($index==0) true @else false @endif">
                                            <span class="badge bg-primary me-3">{{ $loop->iteration }}</span>
                                            {!! $faq->question !!}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $faq->id }}"
                                        class="accordion-collapse collapse @if($index==0) show @endif"
                                        data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">{!! $faq->answer !!}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====================================
             FULL MENU MODALS
             ==================================== --}}
        @if ($menuCategories && $menuCategories->isNotEmpty())
            @foreach ($menuCategories as $menuCat)
                @if ($menuCat->product && $menuCat->product->isNotEmpty())
                    @foreach ($menuCat->product as $prod)
                    @php $d = calcDiscount($prod); @endphp
                    <div class="container-fluid cart food-modal wow fadeIn" data-wow-delay="0.1s">
                        <div class="modal fade menu-modal" id="menuModalFull-{{ $prod->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-body p-0 scrollable">
                                        <input type="hidden" name="product_id" value="{{ $prod->id }}">

                                        <div class="position-relative">
                                            <img class="w-100" src="{{ asset($prod->image) }}" alt="product-img">
                                            @if($d['hasDiscount'])
                                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">{{ $d['badgeText'] }}</span>
                                            @endif
                                        </div>

                                        <div class="p-3 description">
                                            <h3>{{ $prod->name }}</h3>

                                            @if(optional($d['comp'])->complementary)
                                                <input type="hidden" value="{{ $d['comp']->complementary->id }}" name="complementary_id">
                                                <div class="mt-3 text-center">
                                                    <img class="img-fluid rounded-circle"
                                                         src="{{ asset($d['comp']->complementary->image) }}"
                                                         alt="{{ $d['comp']->complementary->name }}"
                                                         style="width:100px;height:100px;object-fit:cover;">
                                                    <br>
                                                    <span class="badge bg-success m-2">BUY 1 GET 1 FREE</span>
                                                    <p class="mb-0 small fw-medium text-dark">{{ $d['comp']->complementary->name }}</p>
                                                </div>
                                            @endif

                                            {{-- ── Price display ── --}}
                                            @if($d['hasVariants'])
                                                <p class="price-display mb-1">
                                                    {{-- ✅ variant original_price strikethrough --}}
                                                    <span class="text-muted text-decoration-line-through d-block variant-original-price"
                                                        @if(!($d['originalPrice'] > 0 && $d['originalPrice'] > $d['finalPrice'])) style="display:none!important" @endif>
                                                        £{{ number_format($d['originalPrice'], 2) }}
                                                    </span>
                                                    £ <span class="prodPrice">{{ number_format($d['finalPrice'], 2) }}</span>
                                                </p>

                                                <select class="form-control bg-white ps-1 select-size" name="variant_id" style="appearance: auto">
                                                    @foreach ($prod->variants as $variant)
                                                        <option value="{{ $variant->id }} {{ $variant->price }}"
                                                            data-original="{{ $variant->original_price ?? 0 }}"
                                                            {{ $loop->first ? 'selected' : '' }}>
                                                            {{ $variant->size }} - £{{ number_format($variant->price, 2) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <h6 class="small mt-1 mb-3">Note: Prices vary depending on the selected size</h6>
                                            @else
                                                @if($d['hasDiscount'])
                                                    <p>
                                                        <span class="text-muted text-decoration-line-through">£{{ number_format($d['originalPrice'], 2) }}</span><br>
                                                        <span class="text-danger fw-bold prodPrice">£{{ number_format($d['finalPrice'], 2) }}</span>
                                                    </p>
                                                @else
                                                    <p>£ <span class="prodPrice">{{ number_format($d['finalPrice'], 2) }}</span></p>
                                                @endif
                                            @endif

                                            <div class="d-flex cart-btn">
                                                <button class="btn p-0 decrement" type="button">-</button>
                                                <input type="text" class="cart_input increment-input text-center" value="1" name="quantity">
                                                <button class="btn p-0 increment" type="button">+</button>
                                            </div>
                                        </div>

                                        {{-- Location --}}
                                        <div class="description p-3">
                                            <div class="d-flex justify-content-between">
                                                <h6>How to get it</h6>
                                                <h6 class="text-danger">Required</h6>
                                            </div>
                                            @foreach ($branches as $branchIndex => $branch)
                                                @if ($branch->status == 1)
                                                    <div class="branch-option mb-3">
                                                        <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                name="status_{{ $prod->id }}"
                                                                id="pickupStatus{{ $prod->id }}_{{ $branch->id }}_{{ $branchIndex }}"
                                                                value="1" checked
                                                                onchange="toggleDelivery('{{ $prod->id }}', '{{ $branch->id }}_{{ $branchIndex }}')">
                                                            <label class="form-check-label fw-bold small"
                                                                for="pickupStatus{{ $prod->id }}_{{ $branch->id }}_{{ $branchIndex }}">Store Pickup</label>
                                                        </div>
                                                        <p class="small fw-bold m-0 sel-location mt-1"
                                                            id="storePickupSection{{ $prod->id }}_{{ $branch->id }}_{{ $branchIndex }}">
                                                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($branch->location) }}"
                                                               target="_blank" style="text-decoration:none;color:inherit;">
                                                                {{ $branch->location }}
                                                            </a>
                                                        </p>
                                                        <div class="form-check mt-3">
                                                            <input class="form-check-input" type="radio"
                                                                name="status_{{ $prod->id }}"
                                                                id="homeStatus{{ $prod->id }}_{{ $branch->id }}_{{ $branchIndex }}"
                                                                value="2"
                                                                onchange="toggleDelivery('{{ $prod->id }}', '{{ $branch->id }}_{{ $branchIndex }}')">
                                                            <label class="form-check-label fw-bold small"
                                                                for="homeStatus{{ $prod->id }}_{{ $branch->id }}_{{ $branchIndex }}">Home Delivery</label>
                                                        </div>
                                                        <div id="deliveryAddressField{{ $prod->id }}_{{ $branch->id }}_{{ $branchIndex }}"
                                                            class="mt-2" style="display:none;">
                                                            <input type="text"
                                                                id="deliveryInput{{ $prod->id }}_{{ $branch->id }}"
                                                                name="delivery_address_{{ $prod->id }}"
                                                                class="form-control location-input"
                                                                data-product="{{ $prod->id }}"
                                                                data-branch="{{ $branch->id }}"
                                                                placeholder="Enter your delivery address"
                                                                autocomplete="off" />
                                                            <input type="hidden" name="lat_{{ $prod->id }}" id="lat{{ $prod->id }}_{{ $branch->id }}">
                                                            <input type="hidden" name="lng_{{ $prod->id }}" id="lng{{ $prod->id }}_{{ $branch->id }}">
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        {{-- Toppings --}}
                                        @if ($prod->category && $prod->category->isNotEmpty())
                                            @foreach ($prod->category as $toppingIndex => $category)
                                                <div class="description p-3">
                                                    <div class="arrow" style="cursor:pointer"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#toppingFull{{ $toppingIndex }}{{ $category->id }}">
                                                        <div class="d-flex justify-content-between">
                                                            <h6 class="m-0">{{ $category->getCategory->name }}</h6>
                                                            <h6 class="fw-normal m-0 d-flex align-items-center">Optional <span class="h5 m-0 p-0 ri-arrow-up-s-line"></span></h6>
                                                        </div>
                                                    </div>
                                                    <div class="collapse show" id="toppingFull{{ $toppingIndex }}{{ $category->id }}">
                                                        @php $categoryToppings = App\Models\CategoryTopping::where('category_id', $category->getCategory->id)->get(); @endphp
                                                        @foreach ($categoryToppings as $categoryTopping)
                                                            <div class="d-flex justify-content-between">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="toppings[]"
                                                                        id="toppingchekFull{{ $toppingIndex }}{{ $category->id }}{{ $categoryTopping->topping->id }}"
                                                                        value="{{ $categoryTopping->topping->id }}"
                                                                        data-category-id="{{ $category->getCategory->id }}">
                                                                    <label class="form-check-label m-0"
                                                                        for="toppingchekFull{{ $toppingIndex }}{{ $category->id }}{{ $categoryTopping->topping->id }}">
                                                                        {{ $categoryTopping->topping->name }}
                                                                    </label>
                                                                </div>
                                                                <p class="m-0">{{ isset($categoryTopping->topping->price) ? '£'.$categoryTopping->topping->price : '' }}</p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="modal-footer position-relative px-2">
                                        <button type="button"
                                            style="font-size:24px;position:absolute;left:0;width:30px;height:30px;display:flex;justify-content:center;align-items:center"
                                            class="btn time-modal-close ri-close-circle-line btn-danger px-2 ms-3 py-0"
                                            data-bs-dismiss="modal"></button>
                                        <div class="text-center mx-auto">
                                            <button class="btn btn-danger addto-cart px-sm-5 px-4">Add To Order</button>
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
    </div>
@endsection

@section('js')
@if (\Illuminate\Support\Facades\Session::has('message'))
<script>toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');</script>
@endif

<script>
$(function() {

    // =========================================
    // 1. VARIANT SELECT → DYNAMIC PRICE UPDATE
    // ✅ FIX: shows variant.original_price as strikethrough
    //         and variant.price as current price
    // =========================================
    $(document).on('change', '.select-size', function() {
        var selectedOption = $(this).find('option:selected');
        var parts          = $(this).val().trim().split(' ');
        var price          = parseFloat(parts[parts.length - 1]);           // variant.price (adjusted)
        var originalPrice  = parseFloat(selectedOption.data('original')) || 0; // variant.original_price (base)
        var modalBody      = $(this).closest('.modal-body');

        // Update current price
        modalBody.find('.prodPrice').text(price.toFixed(2));

        // ✅ Update strikethrough: show if original > current
        var strikeEl = modalBody.find('.variant-original-price');
        if (originalPrice > 0 && originalPrice > price) {
            strikeEl.text('£' + originalPrice.toFixed(2)).removeAttr('style').show();
        } else {
            strikeEl.text('').hide();
        }
    });

    // =========================================
    // 2. MODAL OPEN → INIT PRICE FROM SELECTED VARIANT
    // =========================================
    $(document).on('shown.bs.modal', '.menu-modal', function() {
        var sizeSelect = $(this).find('.select-size');
        if (!sizeSelect.length) return;

        var initialOption = sizeSelect.find('option:selected');
        var parts         = initialOption.val().trim().split(' ');
        var price         = parseFloat(parts[parts.length - 1]);
        var originalPrice = parseFloat(initialOption.data('original')) || 0;

        $(this).find('.prodPrice').text(price.toFixed(2));

        var strikeEl = $(this).find('.variant-original-price');
        if (originalPrice > 0 && originalPrice > price) {
            strikeEl.text('£' + originalPrice.toFixed(2)).removeAttr('style').show();
        } else {
            strikeEl.text('').hide();
        }
    });

    // =========================================
    // 3. INCREMENT / DECREMENT
    // =========================================
    $(document).on('click', '.increment', function() {
        var input = $(this).siblings('.cart_input');
        input.val(parseInt(input.val()) + 1);
    });

    $(document).on('click', '.decrement', function() {
        var input = $(this).siblings('.cart_input');
        var val   = parseInt(input.val()) - 1;
        input.val(val >= 1 ? val : 1);
    });

    // Add to cart is handled once in header.blade.php
    // =========================================
    // 5. UPDATE CART UI
    // =========================================
    window.updateCartUI = function(data) {
        var cartItemCount = 0;
        var html = '';

        $.each(data.cart, function(key, product) {
            cartItemCount += parseInt(product.quantity);
            html += '<div class="carting-child px-3 mt-3 d-flex justify-content-between pb-3 border-bottom" id="' + product.product_id + 'carted">';
            html += '<img src="' + product.image + '" alt="">';
            html += '<div class="content">';
            html += '<div class="d-flex cart-input-parent justify-content-between">';
            html += '<h6 class="m-0">' + product.name;
            html += product.size ? ' (<span style="font-size:12px">' + product.size + '</span>)' : '';
            html += '</h6>';
            html += '<h6 class="m-0 total-price">£' + (parseFloat(product.price) * product.quantity).toFixed(2) + '</h6>';
            html += '<p class="product-price d-none">' + product.price + '</p>';
            html += '</div>';
            html += '<div class="delivery-info mb-2"><p class="small m-0 text-' + (product.delivery_status == '2' ? 'info' : 'success') + '">';
            html += product.delivery_status == '2' ? 'Home Delivery' : 'Store Pickup';
            html += '</p></div>';
            html += '<div class="mb-2"><h6 class="m-0">Toppings</h6>';
            if (product.toppingsName_by_categoryName && product.toppingsName_by_categoryName.length) {
                $.each(product.toppingsName_by_categoryName, function(i, cat) {
                    html += '<div class="mb-2"><p class="mb-1 fw-bold text-black">' + cat.category_name + '</p>';
                    $.each(cat.topping_names, function(j, name) { html += '<p class="small m-0">' + name + '</p>'; });
                    html += '</div>';
                });
            }
            html += '</div>';
            html += '<div class="cart-btn">';
            html += '<button class="btn decrement-btn p-0" data-product-id="' + product.product_id + ',' + product.variant_id + '">-</button>';
            html += '<input type="number" name="quantity" value="' + product.quantity + '" class="increment-input cart-input cart_input text-center">';
            html += '<button class="btn increment-btn p-0" data-product-id="' + product.product_id + ',' + product.variant_id + '">+</button>';
            html += '</div></div></div>';
        });

        $('.cart-counter-1').text(cartItemCount);
        $('.cards-parent').html(html);
        if (cartItemCount > 0) $('.button-disable').removeClass('disabled');
    }

    // =========================================
    // 6. UPDATE LOCATION BUTTON
    // =========================================
    $(document).on('click', '.updateLocationBtn', function() {
        var selectedBranch = $('input[name="choosen_location"]:checked');
        if (!selectedBranch.length) { alert('Please select a location.'); return; }
        $.ajax({
            type: 'POST',
            url: '{{ route("update.branch.status") }}',
            data: { _token: '{{ csrf_token() }}', branch_id: selectedBranch.data('branch-id') },
            success: function() {
                toastr.success('Location Updated Successfully');
                setTimeout(function() { location.reload(); }, 1000);
            }
        });
    });

    // =========================================
    // 7. TAB SCROLL
    // =========================================
    window.scrollTabs = function(direction) {
        var tabs = document.querySelector('.menu-category-tabs');
        if (tabs) tabs.scrollBy({ left: direction === 'left' ? -200 : 200, behavior: 'smooth' });
    };

    (function() {
        var tabs = document.querySelector('.menu-category-tabs');
        var leftBtn = document.querySelector('.tab-scroll-btn.left');
        var rightBtn = document.querySelector('.tab-scroll-btn.right');
        if (!tabs || !leftBtn || !rightBtn) return;

        function updateBtns() {
            if (tabs.scrollWidth <= tabs.clientWidth) {
                leftBtn.style.display = rightBtn.style.display = 'none'; return;
            }
            leftBtn.classList.toggle('disabled', tabs.scrollLeft === 0);
            rightBtn.classList.toggle('disabled', tabs.scrollLeft + tabs.clientWidth >= tabs.scrollWidth - 10);
        }
        tabs.addEventListener('scroll', updateBtns);
        updateBtns();
    })();

}); // end $(function)

// =========================================
// 8. TOGGLE DELIVERY
// =========================================
function toggleDelivery(productId, branchUnique) {
    var pickupRadio   = document.getElementById('pickupStatus'           + productId + '_' + branchUnique);
    var homeRadio     = document.getElementById('homeStatus'             + productId + '_' + branchUnique);
    var pickupSection = document.getElementById('storePickupSection'     + productId + '_' + branchUnique);
    var deliveryField = document.getElementById('deliveryAddressField'   + productId + '_' + branchUnique);

    if (!homeRadio || !pickupRadio) return;

    if (homeRadio.checked) {
        if (pickupSection) pickupSection.style.display = 'none';
        if (deliveryField) deliveryField.style.display = 'block';
    } else {
        if (pickupSection) pickupSection.style.display = 'block';
        if (deliveryField) {
            deliveryField.style.display = 'none';
            var inp = deliveryField.querySelector('input[type="text"]');
            if (inp) inp.value = '';
        }
    }
}
</script>

{{-- =========================================
     GOOGLE MAPS AUTOCOMPLETE
     ✅ FIX: prevent modal from swallowing pac-container clicks
     ========================================= --}}
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUMK9qFdsbuuuTMiaPHCJok4Rro91yvaE&libraries=places&callback=initAllAutocomplete&loading=async">
</script>

<script>
window.initAllAutocomplete = function() {
    bindAutocomplete(document);

    // Re-bind when any modal opens
    document.addEventListener('shown.bs.modal', function(e) {
        bindAutocomplete(e.target);

        // ✅ FIX: prevent Bootstrap modal from swallowing pac-container clicks
        var modal = e.target;
        modal.addEventListener('mousedown', function preventModalClose(ev) {
            if (ev.target.closest('.pac-container')) {
                ev.stopPropagation();
            }
        }, true);
    });
};

function bindAutocomplete(container) {
    container.querySelectorAll('.location-input').forEach(function(input) {
        if (input.dataset.autocompleteInit === '1') return;
        input.dataset.autocompleteInit = '1';

        var productId = input.dataset.product;
        var branchId  = input.dataset.branch;

        var autocomplete = new google.maps.places.Autocomplete(input, {
            fields: ['geometry', 'name', 'formatted_address'],
            types: ['geocode'],
            componentRestrictions: { country: 'gb' }
        });

        var latField = document.getElementById('lat' + productId + '_' + branchId);
        var lngField = document.getElementById('lng' + productId + '_' + branchId);

        // Clear lat/lng when user types manually
        input.addEventListener('input', function() {
            if (latField) latField.value = '';
            if (lngField) lngField.value = '';
        });

        // ✅ Set lat/lng when suggestion is selected
        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place || !place.geometry) return;

            if (latField) latField.value = place.geometry.location.lat();
            if (lngField) lngField.value = place.geometry.location.lng();

            // Update input with formatted address if available
            if (place.formatted_address) {
                input.value = place.formatted_address;
            }
        });

        // ✅ FIX: validate on blur — clear if no selection made
        input.addEventListener('blur', function() {
            setTimeout(function() {
                if (!latField || !latField.value || !lngField || !lngField.value) {
                    input.value = '';
                    if (latField) latField.value = '';
                    if (lngField) lngField.value = '';
                }
            }, 400); // 400ms to allow pac-item click to register
        });
    });
}
</script>
@endsection