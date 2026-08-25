<!-- Spinner Start -->
@php
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Branch;

$notifications        = collect();
$notificationCount    = 0;
$latestNotifications  = collect();
$branches             = Branch::all();
$spAddingToOrder      = false;
try {
    $spAddingToOrder = app(\App\Services\OrderLifecycleService::class)
        ->hasActiveAddToOrderSession(Auth::guard('user')->id() ? (int) Auth::guard('user')->id() : null);
} catch (\Throwable $e) {
    $spAddingToOrder = false;
}

if (Auth::guard('user')->check()) {
    $userId              = Auth::guard('user')->id();
    $notifications       = Notification::where('user_id', $userId)->latest()->get();
    $notificationCount   = $notifications->count();
    $latestNotifications = $notifications->take(3);
}

// ── on_top product ────────────────────────────────────────────────────────────
$product         = Product::with(['variants', 'category.getCategory'])
                           ->where('on_top', true)
                           ->first();

$hasDiscount     = false;
$badgeText       = '';
$originalPrice   = 0;   // price to show WITH strikethrough
$finalPrice      = 0;   // price to show as current (red)

if ($product) {
    $hasVariants = $product->variants && $product->variants->count() > 0;

    if ($hasVariants) {
        /*
         * Variant product
         * variant.original_price = base price (e.g. £12.00)
         * variant.price          = discounted price (e.g. £9.00)
         *
         * We pick the "display" variant for the header price:
         *   prefer 'regular', else first variant with price > 0
         */
        $displayVariant = $product->variants->where('size', 'regular')->first();
        if (!$displayVariant || $displayVariant->price <= 0) {
            $displayVariant = $product->variants->where('price', '>', 0)->first();
        }
        if (!$displayVariant) {
            $displayVariant = $product->variants->first();
        }

        $finalPrice    = $displayVariant ? floatval($displayVariant->price)                            : 0;
        $originalPrice = $displayVariant ? floatval($displayVariant->original_price ?? $finalPrice)    : 0;

    } else {
        // Simple product
        $finalPrice    = floatval($product->price ?? 0);
        $originalPrice = floatval($product->original_price ?? $finalPrice);
    }

    // Build badge text
    if (
        $product->featured_action == 'decrease' &&
        $originalPrice > $finalPrice &&
        $originalPrice > 0
    ) {
        $hasDiscount = true;
        if ($product->featured_method == 'percentage' && $product->featured_amount > 0) {
            $badgeText = (int) $product->featured_amount . '% OFF';
        } else {
            $badgeText = '£' . number_format($product->featured_amount ?? ($originalPrice - $finalPrice), 0) . ' OFF';
        }
    }
}
@endphp

<style>
/* Notification CSS */
.notification-wrapper { position: relative; }
.mobile-notify-block { width:340px; max-width:90%; right:0; left:auto; transform:none; margin-top:12px; }
.notify-block { max-height:300px; overflow:auto; }
.notification-title, .notification-desc, .mobile-notify-block h5, .mobile-notify-block { color:#000 !important; }
.notification-title { font-weight:600; font-size:14px; margin-bottom:4px; }
.notification-desc { font-size:13px; line-height:1.4; color:#555 !important; margin-bottom:8px; }
.bell-counter { background:red; color:#fff; font-size:11px; padding:2px 6px; border-radius:50%; position:absolute; top:-6px; right:-6px; }
.mobile-notify-block .card { border:none; border-bottom:1px solid #eee; }
.mobile-notify-block .card:last-child { border-bottom:none; }

/* Desktop notification styles */
.carting-card { width: 350px; }
.carting-card .card { border:none; border-bottom:1px solid #eee; margin:0; }
.carting-card .card:last-child { border-bottom:none; }
.carting-card .card-body { padding: 12px 15px; }

.mobile-notify-block,
.mobile-notify-block h5,
.mobile-notify-block .notification-title,
.mobile-notify-block .notification-desc,
.mobile-notify-block .card-body,
.carting-card,
.carting-card h5,
.carting-card .notification-title,
.carting-card .notification-desc {
    color: #000 !important;
}

@media(max-width:992px){ .mobile-notify-block{ width:80%; left:10%; right:auto; } }
@media(max-width:768px){ .mobile-notify-block{ width:92%; left:4%; } }
@media(max-width:480px){ .mobile-notify-block{ width:96%; left:2%; } }

/* pac-container fix for modals */
.pac-container { z-index: 9999999 !important; }
</style>

@if (url()->current() !== url('/'))
<style>.pac-container { z-index: 9999999 !important; }</style>
@endif

<div id="spinner" class="bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-dark" style="width:3rem;height:3rem;" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>
<div id="spHeaderBackdrop" aria-hidden="true"></div>

<!-- Disclaimer Modal -->
<div class="modal fade" id="disclaimerModal" tabindex="-1" aria-labelledby="disclaimerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p class="mb-0 text-danger"><strong>Disclaimer!</strong> Some of our products are made with ingredients that may contain Nuts, Soy, Gluten And/Or Wheat. If you have any allergies, please let us know.</p>
            </div>
        </div>
    </div>
</div>

<!-- Promo Bar + Navbar stay visible above wholesale bar -->
<div class="sp-site-header">
@if($product)
<div class="promo-bar">
    <a href="#" style="text-decoration:none;"
        data-bs-toggle="modal" data-bs-target="#menuModalPromo-{{ $product->id }}">
        🔥 {{ $badgeText ?: '25% OFF' }} on {{ $product->name }} – Order Now
    </a>
</div>
@else
<div class="promo-bar">
    <a href="#" style="text-decoration:none;">🔥 Get 20% OFF on your first order – Order Now</a>
</div>
@endif

<div class="app-download-banner">
    <p>🍕 Craving your favorites? Get the Sugar Pappi App for exclusive offers!</p>
    <div>
        <a href="#" target="_blank" class="d-inline-block">
            <img src="{{ asset('public/img/gslogo.png') }}" alt="Google Play" width="145">
        </a>
        <a href="#" target="_blank" class="d-inline-block">
            <img src="{{ asset('public/img/pslogo.png') }}" alt="App Store" width="145">
        </a>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-4 px-lg-5 py-lg-0 py-2">
    <div>
        <button class="navbar-toggler me-2 d-inline-flex" type="button" aria-label="Open menu">
            <span class="fa fa-bars"></span>
        </button>
        <a href="{{ route('index') }}" class="navbar-brand p-0">
            <img src="{{ asset('public/img/logo.png') }}" alt="Logo">
        </a>
    </div>

    <div class="d-lg-none d-flex align-items-center sp-header-actions">
        <span class="fa fa-search header-icon open-btn"></span>
        @include('home.partials.header-cart', ['wrapperClass' => 'd-inline nav-item dropdown', 'toggleClass' => 'd-inline nav-link p-0', 'iconClass' => ''])

        @if(Auth::guard('user')->check())
        <div class="d-inline nav-item dropdown notification-wrapper">
            <a href="#" class="d-inline nav-link p-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                <span class="fa fa-bell position-relative header-icon">
                    @if($notificationCount > 0)
                        <span class="badge bell-counter">{{ $notificationCount }}</span>
                    @endif
                </span>
            </a>
            <div class="mobile-notify-block dropdown-menu py-3 px-0">
                <div class="border-bottom pb-3 px-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 text-black">Notifications ({{ $notificationCount }})</h5>
                    <button type="button" class="btn-close sp-dropdown-close d-lg-none" aria-label="Close"></button>
                </div>
                <div class="notify-block">
                    @forelse($latestNotifications as $notification)
                    <div class="card">
                        <div class="card-body">
                            <div class="notification-title text-dark">{{ $notification->title }}</div>
                            <div class="notification-desc text-dark">{{ $notification->description }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="card">
                        <div class="card-body text-center text-black">No Notifications Found</div>
                    </div>
                    @endforelse
                </div>
                <div class="pt-3 border-top mt-1 text-center">
                    <a href="{{ route('web.notifications.index') }}"
                        class="btn btn-danger px-5 {{ $notificationCount == 0 ? 'disabled' : '' }}">
                        View All Notifications
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto py-0 pe-xl-4 pe-3">
            <a href="{{ route('index') }}" class="nav-item nav-link {{ request()->is('/') ? 'active' : '' }}">Home</a>
            <a href="{{ route('get-our-menu') }}" class="nav-item nav-link {{ request()->is('get-our-menu') ? 'active' : '' }}">Menu</a>
            <a href="{{ route('pappi-special') }}" class="nav-item nav-link {{ request()->is('pappi-special') ? 'active' : '' }}">Pappi Special</a>
            <a href="{{ route('get-our-gallery') }}" class="nav-item nav-link {{ request()->is('get-our-gallery') ? 'active' : '' }}">Gallery</a>
            <a href="{{ route('dessert-wholesale') }}" class="nav-item nav-link">Dessert Wholesale</a>
            <a href="{{ route('drive-in') }}" class="nav-item nav-link">Drive-In</a>
            <a href="{{ $whatsappUrl ?? 'https://wa.me/447727412922' }}" class="nav-item nav-link" target="_blank" rel="noopener">WhatsApp</a>
            @if(Auth::guard('user')->check())
                <a href="{{ route('my-order') }}" class="nav-item nav-link {{ request()->is('my-order') ? 'active' : '' }}">My Orders</a>
            @endif
        </div>

        <span class="fa fa-search header-icon open-btn"></span>

        @if(Auth::guard('user')->check())
        <a href="{{ route('my-profile') }}"
            class="fa fa-user header-icon mx-xl-3 nav-item nav-link {{ request()->is('my-profile') ? 'active' : '' }}"></a>

        <!-- Notifications Dropdown -->
        <div class="nav-item dropdown">
            <a href="#" class="nav-link p-0" data-bs-toggle="dropdown">
                <span class="fa fa-bell me-3 position-relative header-icon">
                    @if($notificationCount > 0)
                        <span class="badge bell-counter">{{ $notificationCount }}</span>
                    @endif
                </span>
            </a>
            <div class="carting-card dropdown-menu py-3 px-0">
                <div class="border-bottom pb-3 px-3">
                    <h5 class="m-0 text-dark">Notifications ({{ $notificationCount }})</h5>
                </div>
                <div class="notify-block scrollable">
                    @forelse($latestNotifications as $notification)
                    <div class="card">
                        <div class="card-body">
                            <div class="notification-title text-dark">{{ $notification->title }}</div>
                            <div class="notification-desc text-dark">{{ $notification->description }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="card">
                        <div class="card-body text-center text-dark">No Notifications Found!</div>
                    </div>
                    @endforelse
                </div>
                <div class="pt-3 border-top mt-1 text-center">
                    <a href="{{ route('web.notifications.index') }}"
                        class="btn btn-danger px-5 {{ $notificationCount == 0 ? 'disabled' : '' }}">
                        View All Notifications
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Cart Dropdown -->
        <div class="ms-2 d-none d-lg-block">
            @include('home.partials.header-cart')
        </div>

        @if(Auth::guard('user')->check())
            <a href="{{ route('user-logout') }}" class="btn btn-primary py-2 px-4" id="logout">Logout</a>
        @else
            <a href="{{ asset('login') }}" class="btn btn-primary py-2 px-4">Login</a>
        @endif
    </div>
</nav>
</div>

<!-- Overlay Search -->
<div id="myOverlay" class="overlay">
    <span class="close-btn" title="Close Overlay">×</span>
    <div class="overlay-content">
        <form action="{{ route('product.search') }}" method="GET" class="mb-0">
            <input type="text" placeholder="Search Your Favorite Food ..." name="search">
            <button type="submit" class="btn btn-primary" style="border:none;border-radius:0">
                <span class="fa fa-search"></span>
            </button>
        </form>
    </div>
</div>

{{-- =====================================================================
     PROMO MODAL  (on_top product)
     ✅ FIX: data-original on each option, correct initial price display
     ===================================================================== --}}
@if($product)
<div class="container-fluid cart food-modal">
    <div class="modal fade menu-modal" id="menuModalPromo-{{ $product->id }}"
        tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body p-0 scrollable">

                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    {{-- Image + badge --}}
                    <div class="position-relative">
                        <img class="w-100"
                             src="{{ asset($product->image ?? 'public/img/default.jpg') }}"
                             alt="product-img">
                        @if($hasDiscount)
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2 fs-6">
                                {{ $badgeText }}
                            </span>
                        @endif
                    </div>

                    <div class="p-3 description">
                        <h3>{{ $product->name }}</h3>

                        @php $hasVariants = $product->variants && $product->variants->count() > 0; @endphp

                        @if($hasVariants)
                            {{--
                                ✅ FIX:
                                - .variant-original-price  → strikethrough, updated by JS on select change
                                - .prodPrice               → current price,  updated by JS on select change
                                - Both are initialised by 'shown.bs.modal' JS event below
                            --}}
                            <p class="mb-1">
                                <span class="text-muted text-decoration-line-through d-block variant-original-price"
                                    @if(!($originalPrice > $finalPrice)) style="display:none!important" @endif>
                                    £{{ number_format($originalPrice, 2) }}
                                </span>
                                £&nbsp;<span class="prodPrice">{{ number_format($finalPrice, 2) }}</span>
                            </p>

                            {{-- ✅ KEY FIX: data-original holds variant.original_price --}}
                            <select class="form-control bg-white ps-1 select-size"
                                    name="variant_id" style="appearance:auto">
                                @foreach ($product->variants as $variant)
                                    <option
                                        value="{{ $variant->id }} {{ number_format((float)$variant->price, 2) }}"
                                        data-original="{{ number_format((float)($variant->original_price ?? 0), 2) }}"
                                        {{ $loop->first ? 'selected' : '' }}>
                                        {{ $variant->size }} – £{{ number_format((float)$variant->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <h6 class="small mt-1 mb-3">Note: Prices vary depending on the selected size</h6>

                        @else
                            {{-- Simple product --}}
                            @if($hasDiscount)
                                <p>
                                    <span class="text-muted text-decoration-line-through">
                                        £{{ number_format($originalPrice, 2) }}
                                    </span><br>
                                    <span class="text-danger fw-bold prodPrice">
                                        £{{ number_format($finalPrice, 2) }}
                                    </span>
                                </p>
                            @else
                                <p>£&nbsp;<span class="prodPrice">{{ number_format($finalPrice, 2) }}</span></p>
                            @endif
                        @endif

                        <div class="d-flex cart-btn">
                            <button class="btn p-0 decrement" type="button">-</button>
                            <input type="text" class="cart_input increment-input text-center"
                                value="1" name="quantity" id="quantity_{{ $product->id }}">
                            <button class="btn p-0 increment" type="button">+</button>
                        </div>
                    </div>

                    <!-- How to get it -->
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
                                            name="status_{{ $product->id }}"
                                            id="pickupStatus{{ $product->id }}_{{ $branch->id }}_{{ $index }}"
                                            value="1"
                                            {{ $loop->first ? 'checked' : '' }}
                                            onchange="toggleDelivery('{{ $product->id }}', '{{ $branch->id }}_{{ $index }}')">
                                        <label class="form-check-label fw-bold small"
                                            for="pickupStatus{{ $product->id }}_{{ $branch->id }}_{{ $index }}">
                                            Store Pickup
                                        </label>
                                    </div>

                                    <p class="small fw-bold m-0 sel-location mt-1"
                                        id="storePickupSection{{ $product->id }}_{{ $branch->id }}_{{ $index }}">
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($branch->location) }}"
                                            target="_blank" style="text-decoration:none;color:inherit;">
                                            {{ $branch->location }}
                                        </a>
                                    </p>

                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="radio"
                                            name="status_{{ $product->id }}"
                                            id="homeStatus{{ $product->id }}_{{ $branch->id }}_{{ $index }}"
                                            value="2"
                                            onchange="toggleDelivery('{{ $product->id }}', '{{ $branch->id }}_{{ $index }}')">
                                        <label class="form-check-label fw-bold small"
                                            for="homeStatus{{ $product->id }}_{{ $branch->id }}_{{ $index }}">
                                            Home Delivery
                                        </label>
                                    </div>

                                    <div id="deliveryAddressField{{ $product->id }}_{{ $branch->id }}_{{ $index }}"
                                        class="mt-2" style="display:none;">
                                        <input type="text"
                                            id="deliveryInput{{ $product->id }}_{{ $branch->id }}"
                                            name="delivery_address_{{ $product->id }}"
                                            class="form-control location-input"
                                            data-product="{{ $product->id }}"
                                            data-branch="{{ $branch->id }}"
                                            placeholder="Enter your delivery address"
                                            autocomplete="off" />
                                        <input type="hidden" name="lat_{{ $product->id }}"
                                            id="lat{{ $product->id }}_{{ $branch->id }}">
                                        <input type="hidden" name="lng_{{ $product->id }}"
                                            id="lng{{ $product->id }}_{{ $branch->id }}">
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Toppings -->
                    @if(isset($product->category) && $product->category && $product->category->isNotEmpty())
                        @foreach ($product->category as $index => $category)
                            @if(isset($category->getCategory))
                            <div class="description p-3">
                                <div class="arrow" style="cursor:pointer"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#toppingPromo{{ $index }}{{ $category->id }}">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="m-0">{{ $category->getCategory->name ?? '' }}</h6>
                                        <h6 class="fw-normal m-0 d-flex align-items-center">
                                            Optional
                                            <span class="h5 m-0 p-0 ri-arrow-up-s-line"></span>
                                        </h6>
                                    </div>
                                </div>
                                <div class="collapse show"
                                    id="toppingPromo{{ $index }}{{ $category->id }}">
                                    @php
                                        $categoryToppings = App\Models\CategoryTopping::where(
                                            'category_id', $category->getCategory->id ?? 0
                                        )->get();
                                    @endphp
                                    @foreach ($categoryToppings as $categoryTopping)
                                        @if(isset($categoryTopping->topping))
                                        <div class="d-flex justify-content-between">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    name="toppings[]"
                                                    id="toppingPromo{{ $index }}{{ $category->id }}{{ $categoryTopping->topping->id }}"
                                                    value="{{ $categoryTopping->topping->id }}"
                                                    data-category-id="{{ $category->getCategory->id ?? 0 }}">
                                                <label class="form-check-label m-0"
                                                    for="toppingPromo{{ $index }}{{ $category->id }}{{ $categoryTopping->topping->id }}">
                                                    {{ $categoryTopping->topping->name ?? '' }}
                                                </label>
                                            </div>
                                            <p class="m-0">
                                                {{ isset($categoryTopping->topping->price) ? '£'.$categoryTopping->topping->price : '' }}
                                            </p>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                    @endif

                </div>{{-- /modal-body --}}

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
@endif

<script>
window.spInitStorefront = function () {
    if (window.__spStorefrontReady || !window.jQuery) return;
    window.__spStorefrontReady = true;
    window.spAddingToOrder = @json(!empty($spAddingToOrder));
    window.jQuery(function ($) {

    // ── Cart +/- buttons in header dropdown ─────────────────────────────
    function updateServerCart(productId, variantId, quantity) {
        $.post('{{ route("update.cart") }}', {
            '_token':    '{{ csrf_token() }}',
            product_id:  productId,
            variant_id:  variantId,
            quantity:    quantity
        });
    }

    function refreshCartCounter() {
        let total = 0;
        $('.cart-input').each(function () { total += parseInt($(this).val()) || 0; });
        $('.cart-counter-1').text(total);
    }

    $(document).on('click', '.increment-btn', function () {
        let $input = $(this).siblings('.increment-input');
        let qty    = parseInt($input.val()) + 1;
        $input.val(qty);

        let data = $(this).data('product-id').toString().split(',');
        if (data[0]) updateServerCart(data[0].trim(), (data[1] || '').trim(), qty);

        let price = parseFloat($(this).closest('.carting-child').find('.product-price').text()) || 0;
        $(this).closest('.carting-child').find('.total-price').text('£' + (price * qty).toFixed(2));
        refreshCartCounter();
    });

    $(document).on('click', '.decrement-btn', function () {
        let $input = $(this).siblings('.increment-input');
        let qty    = parseInt($input.val());
        if (qty > 1) {
            qty--;
            $input.val(qty);
            let data = $(this).data('product-id').toString().split(',');
            if (data[0]) updateServerCart(data[0].trim(), (data[1] || '').trim(), qty);
            let price = parseFloat($(this).closest('.carting-child').find('.product-price').text()) || 0;
            $(this).closest('.carting-child').find('.total-price').text('£' + (price * qty).toFixed(2));
            refreshCartCounter();
        }
    });

    refreshCartCounter();

    // ── Qty +/- inside modal ─────────────────────────────────────────────
    $(document).on('click', '.increment', function () {
        let $inp = $(this).siblings('.cart_input');
        $inp.val(parseInt($inp.val()) + 1);
    });
    $(document).on('click', '.decrement', function () {
        let $inp = $(this).siblings('.cart_input');
        let v    = parseInt($inp.val()) - 1;
        $inp.val(v >= 1 ? v : 1);
    });

    // ── Topping arrow toggle ─────────────────────────────────────────────
    $(document).on('click', '.arrow', function () {
        $(this).find('span').toggleClass('ri-arrow-up-s-line ri-arrow-down-s-line');
    });

    // ── ✅ VARIANT SELECT CHANGE → update prodPrice + strikethrough ──────
    $(document).on('change', '.select-size', function () {
        var $opt          = $(this).find('option:selected');
        var parts         = $(this).val().trim().split(' ');
        var price         = parseFloat(parts[parts.length - 1]);           // variant.price
        var originalPrice = parseFloat($opt.data('original')) || 0;        // variant.original_price
        var $modalBody    = $(this).closest('.modal-body');

        // Update current price
        $modalBody.find('.prodPrice').text(price.toFixed(2));

        // Update strikethrough
        var $strike = $modalBody.find('.variant-original-price');
        if (originalPrice > 0 && originalPrice > price) {
            $strike.text('£' + originalPrice.toFixed(2)).removeAttr('style').show();
        } else {
            $strike.text('').hide();
        }
    });

    // ── ✅ MODAL OPEN → initialise price from pre-selected variant ───────
    $(document).on('shown.bs.modal', '.menu-modal', function () {
        var $select = $(this).find('.select-size');
        if (!$select.length) return;

        var $opt          = $select.find('option:selected');
        var parts         = $opt.val().trim().split(' ');
        var price         = parseFloat(parts[parts.length - 1]);
        var originalPrice = parseFloat($opt.data('original')) || 0;

        $(this).find('.prodPrice').text(price.toFixed(2));

        var $strike = $(this).find('.variant-original-price');
        if (originalPrice > 0 && originalPrice > price) {
            $strike.text('£' + originalPrice.toFixed(2)).removeAttr('style').show();
        } else {
            $strike.text('').hide();
        }
    });

    // ── Add to cart / Add to order (once only) ────────────────────────────
    $(document).off('click.spAddCart', '.addto-cart').on('click.spAddCart', '.addto-cart', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var $btn   = $(this);
        if ($btn.data('spAdding')) return;
        $btn.data('spAdding', true);
        var $modal = $btn.closest('.food-modal');
        var addLabel = $btn.data('addLabel') || (window.spAddingToOrder ? 'Add to Order' : 'Add to Cart');

        var productId       = $modal.find('input[name="product_id"]').val();
        var quantity        = $modal.find('input[name="quantity"]').val() || 1;
        var branchId        = $modal.find('input[name="branch_id"]').first().val();
        var complementaryId = $modal.find('input[name="complementary_id"]').length
                              ? $modal.find('input[name="complementary_id"]').val() : null;

        function resetBtn() {
            $btn.data('spAdding', false);
            $btn.prop('disabled', false).text(addLabel);
        }

        if (!productId) { toastr.error('Product not found'); resetBtn(); return; }

        var isWholesaleAdd = !!$btn.data('wholesale') || !!window.spWholesalePage;
        if (isWholesaleAdd && !window.spWholesaleDate) {
            toastr.error('Select and save a wholesale delivery date first (Monday / Thursday / Saturday, 7–10 PM).');
            var bar = document.getElementById('wholesaleDateBar');
            if (bar) bar.scrollIntoView({ behavior: 'smooth', block: 'start' });
            resetBtn();
            return;
        }
        var deliveryStatus  = isWholesaleAdd ? '2' : ($modal.find('input[name^="status_"]:checked').val() || '1');
        var deliveryAddress = '', lat = '', lng = '';

        if (!isWholesaleAdd && deliveryStatus == '2') {
            deliveryAddress = $modal.find('input[name="delivery_address_' + productId + '"]').val();
            lat             = $modal.find('input[name="lat_' + productId + '"]').val();
            lng             = $modal.find('input[name="lng_' + productId + '"]').val();

            if (!deliveryAddress)  { toastr.error('Please enter delivery address'); resetBtn(); return; }
            if (!lat || !lng)      { toastr.error('Please select a valid address from suggestions'); resetBtn(); return; }
        }

        var variantId  = '';
        var $varSel    = $modal.find('select[name="variant_id"]');
        if ($varSel.length && $varSel.val()) {
            variantId = $varSel.val().trim().split(' ')[0];
        }

        var toppingsByCategory = {};
        $modal.find('input[name="toppings[]"]:checked').each(function () {
            var catId = $(this).data('category-id');
            if (!toppingsByCategory[catId]) toppingsByCategory[catId] = [];
            toppingsByCategory[catId].push($(this).val());
        });
        var toppingsArray = Object.entries(toppingsByCategory).map(function ([catId, tops]) {
            return { category_id: catId, toppings: tops };
        });

        function sendAdd() {
            $btn.prop('disabled', true).text('Adding...');
            $.ajax({
                type: 'POST',
                url:  '{{ route("add.to.cart") }}',
                data: {
                    _token:               '{{ csrf_token() }}',
                    product_id:           productId,
                    quantity:             quantity,
                    branch_id:            branchId,
                    variant_id:           variantId,
                    delivery_status:      deliveryStatus,
                    delivery_address:     deliveryAddress,
                    lat:                  lat,
                    lng:                  lng,
                    complementary_id:     complementaryId,
                    location:             deliveryStatus,
                    toppings_by_category: toppingsArray,
                    wholesale:            isWholesaleAdd ? 1 : 0,
                },
                success: function (data) {
                    if (data && data.success === false) {
                        toastr.error((data && data.message) || 'Something went wrong.');
                        return;
                    }
                    if (data && data.receipt_generated) {
                        toastr.success(data.message || 'Your product has been added in My Orders.');
                        var count = 0;
                        $('.cart-counter-1').text(count);
                        if (typeof window.updateCartUI === 'function') window.updateCartUI({ cart: {} });
                        $btn.closest('.modal').modal('hide');
                        return;
                    }
                    toastr.success(window.spAddingToOrder ? 'Your product has been added in My Orders.' : (isWholesaleAdd ? 'Added. Open My Cart, then Place Order. No timer on wholesale.' : 'Product added to cart!'));
                    var count = data && data.cart ? Object.keys(data.cart).length : 0;
                    $('.cart-counter-1').text(count);
                    if (typeof window.updateCartUI === 'function') window.updateCartUI(data);
                    if (count > 0) {
                        $('a[href*="my-cart"]').removeClass('disabled').prop('disabled', false);
                    }
                    $btn.closest('.modal').modal('hide');
                },
                error: function (xhr) {
                    console.error('Cart error:', xhr.responseText);
                    toastr.error('Error: ' + ((xhr.responseJSON && xhr.responseJSON.message) || 'Server error'));
                },
                complete: function () {
                    resetBtn();
                }
            });
        }

        if (window.spAddingToOrder && typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to add this product in your order?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ff2d87',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    resetBtn();
                    return;
                }
                sendAdd();
            });
            return;
        }
        sendAdd();
    });

    });
};
if (window.jQuery) {
    window.spInitStorefront();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        if (window.spInitStorefront) window.spInitStorefront();
    });
}

// ── toggleDelivery (global — called from onchange) ────────────────────────
function toggleDelivery(productId, branchUnique) {
    var pickupRadio   = document.getElementById('pickupStatus'         + productId + '_' + branchUnique);
    var homeRadio     = document.getElementById('homeStatus'           + productId + '_' + branchUnique);
    var pickupSection = document.getElementById('storePickupSection'   + productId + '_' + branchUnique);
    var deliveryField = document.getElementById('deliveryAddressField' + productId + '_' + branchUnique);

    if (!homeRadio || !pickupRadio) return;

    if (homeRadio.checked) {
        if (pickupSection) pickupSection.style.display = 'none';
        if (deliveryField) deliveryField.style.display = 'block';
    } else {
        if (pickupSection) pickupSection.style.display  = 'block';
        if (deliveryField) {
            deliveryField.style.display = 'none';
            var inp = deliveryField.querySelector('input[type="text"]');
            if (inp) inp.value = '';
        }
    }
}
</script>

@if (url()->current() !== url('/'))
{{-- Google Maps Autocomplete --}}
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUMK9qFdsbuuuTMiaPHCJok4Rro91yvaE&libraries=places&callback=initAllAutocomplete&loading=async">
</script>
<script>
window.initAllAutocomplete = function () {
    bindAutocomplete(document);

    document.addEventListener('shown.bs.modal', function (e) {
        bindAutocomplete(e.target);

        // ✅ Prevent Bootstrap modal from swallowing pac-container clicks
        e.target.addEventListener('mousedown', function (ev) {
            if (ev.target.closest && ev.target.closest('.pac-container')) {
                ev.stopPropagation();
            }
        }, true);
    });
};

function bindAutocomplete(container) {
    if (!container || !container.querySelectorAll) return;

    container.querySelectorAll('.location-input').forEach(function (input) {
        if (input.dataset.autocompleteInit === '1') return;
        input.dataset.autocompleteInit = '1';

        var productId = input.dataset.product;
        var branchId  = input.dataset.branch;
        if (!productId || !branchId) return;

        var latField = document.getElementById('lat' + productId + '_' + branchId);
        var lngField = document.getElementById('lng' + productId + '_' + branchId);
        if (!latField || !lngField) return;

        var autocomplete = new google.maps.places.Autocomplete(input, {
            fields: ['geometry', 'formatted_address'],
            types:  ['geocode'],
            componentRestrictions: { country: 'gb' }
        });

        input.addEventListener('input', function () {
            latField.value = '';
            lngField.value = '';
        });

        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            if (!place || !place.geometry) return;
            latField.value = place.geometry.location.lat();
            lngField.value = place.geometry.location.lng();
            if (place.formatted_address) input.value = place.formatted_address;
        });

        input.addEventListener('blur', function () {
            setTimeout(function () {
                if (!latField.value || !lngField.value) {
                    input.value    = '';
                    latField.value = '';
                    lngField.value = '';
                }
            }, 400);
        });
    });
}
</script>
@endif