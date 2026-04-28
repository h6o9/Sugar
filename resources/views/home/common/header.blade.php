<!-- Spinner Start -->
@php
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Branch;

$notifications = collect();
$notificationCount = 0;
$latestNotifications = collect();
$branches = Branch::all();

if (Auth::guard('user')->check()) {
    $userId = Auth::guard('user')->id();
    $notifications = Notification::where('user_id', $userId)
        ->latest()
        ->get();
    $notificationCount = $notifications->count();
    $latestNotifications = $notifications->take(3);
}

// Initialize product with default values
$product = Product::where('on_top', true)->first();
$discountPercent = 0;
$originalPrice = 0;
$finalPrice = 0;

if ($product) {
    $originalPrice = $product->original_price ?? $product->price ?? 0;
    $finalPrice = $product->price ?? 0;
    
    if ($product->featured_action == 'decrease' && ($product->original_price ?? false)) {
        if ($product->featured_method == 'percentage') {
            $discountPercent = (int) ($product->featured_amount ?? 0);
        } elseif ($product->featured_method == 'amount') {
            if ($originalPrice > 0) {
                $discountPercent = round((($originalPrice - $finalPrice) / $originalPrice) * 100);
            }
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

/* Notifications Text */
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

/* Responsive */
@media(max-width:992px){.mobile-notify-block{width:80%; left:10%; right:auto;}}
@media(max-width:768px){.mobile-notify-block{width:92%; left:4%;}}
@media(max-width:480px){.mobile-notify-block{width:96%; left:2%;}}
</style>

@if (url()->current() !== url('/'))
<style>
    .pac-container {
        z-index: 9999999 !important;
    }
</style>
@endif

<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-dark" style="width:3rem;height:3rem;" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>
<!-- Spinner End -->

<!-- Disclaimer Modal Start -->
<div class="modal fade" id="disclaimerModal" tabindex="-1" aria-labelledby="disclaimerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p class="mb-0 text-danger"><strong>Disclaimer!</strong> Some of our products are made with ingredients that may contain Nuts, Soy, Gluten And/Or Wheat. If you have any allergies, please let us know.</p>
            </div>
        </div>
    </div>
</div>
<!-- Disclaimer Modal End -->

<!-- Navbar Start -->
@if($product)
<div class="promo-bar">
    <a href="#" style="text-decoration: none;" data-bs-toggle="modal" data-bs-target="#menuModalFull-{{ $product->id }}">
        🔥 {{ $discountPercent }}% OFF on {{ $product->name }} – Order Now
    </a>
</div>  
@else
<div class="promo-bar">
    <a href="#" style="text-decoration: none;">
        🔥 Get 20% OFF on your first order – Order Now
    </a>
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

<nav class="navbar navbar-expand-lg navbar-dark px-4 px-lg-5 py-lg-0 py-2">
    <div>
        <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="fa fa-bars"></span>
        </button>
        <a href="{{ route('index') }}" class="navbar-brand p-0">
            <img src="{{ asset('public/img/logo.png') }}" alt="Logo">
        </a>
    </div>

    <div class="d-lg-none">
        <span class="fa fa-search me-2 header-icon open-btn"></span>
        <a href="#" class="fa fa-shopping-cart position-relative header-icon"><span class="badge cart-counter cart-counter-1"></span></a>

        @if(Auth::guard('user')->check())
        <div class="d-inline nav-item dropdown notification-wrapper">
            <a href="#" class="d-inline nav-link p-0" data-bs-toggle="dropdown">
                <span class="fa fa-bell ms-3 position-relative header-icon">
                    @if($notificationCount > 0)
                        <span class="badge bell-counter">{{ $notificationCount }}</span>
                    @endif
                </span>
            </a>

            <div class="mobile-notify-block dropdown-menu py-3 px-0">
                <div class="border-bottom pb-3 px-3">
                    <h5 class="m-0 text-black">Notifications ({{ $notificationCount }})</h5>
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
                    <a href="{{ route('web.notifications.index') }}" class="btn btn-danger px-5 {{ $notificationCount == 0 ? 'disabled' : '' }}">
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
            <a href="{{ route('get-our-gallery') }}" class="nav-item nav-link {{ request()->is('get-our-gallery') ? 'active' : '' }}">EXPLORE Sugar Pappi GALLERY</a>
            @if(Auth::guard('user')->check())
            <a href="{{ route('my-order') }}" class="nav-item nav-link {{ request()->is('my-order') ? 'active' : '' }}">My Orders</a>
            @endif
        </div>

        <span class="fa fa-search header-icon open-btn"></span>

        @if(Auth::guard('user')->check())
        <a href="{{ route('my-profile') }}" class="fa fa-user header-icon mx-xl-3 nav-item nav-link {{ request()->is('my-profile') ? 'active' : '' }}"></a>

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
                    <a href="{{ route('web.notifications.index') }}" class="btn btn-danger px-5 {{ $notificationCount == 0 ? 'disabled' : '' }}">
                        View All Notifications
                    </a>
                </div>
            </div>
        </div>
        @endif
 
        <!-- Cart Dropdown -->
        <div class="ms-2 nav-item dropdown">
            <a href="#" class="nav-link p-0" data-bs-toggle="dropdown">
                <span class="fa fa-shopping-cart me-3 position-relative header-icon"><span class="badge cart-counter cart-counter-1">{{ count(session('cart', [])) }}</span></span>
            </a>
            <div class="carting-card dropdown-menu py-3 px-0">
                <div class="border-bottom mb-1 pb-3 px-3">
                    <h5>Your Cart (<span class="cart-counter-1">{{ count(session('cart', [])) }}</span>)</h5>
                    <b class="small text-danger">You’re viewing a quick summary. Full details including toppings and complimentary items will be available on the cart page.</b>
                </div>
                <div class="cards-parent scrollable">
                    @forelse(session('cart', []) as $item)
                    <div id="{{ $item['product_id'] }}carted" class="carting-child px-3 mt-3 d-flex justify-content-between pb-3 border-bottom">
                        <img src="{{ asset($item['image']) }}" alt="">
                        <div class='content'>
                            <div class="d-flex cart-input-parent justify-content-between">
                                <h6 class="m-0">{{ $item['name'] }} <span style="font-size:12px">{{ !empty($item['size']) ? '('.$item['size'].')' : '' }}</span></h6>
                                <h6 class="m-0 total-price">£{{ number_format(floatval($item['price'])*intval($item['quantity']), 2) }}</h6>
                                <p class="product-price d-none">{{ floatval($item['price']) }}</p>
                            </div>
                            <div class="cart-btn">
                                <button class="btn p-0 decrement-btn" data-product-id="{{ $item['product_id'] }}, {{ $item['variant_id'] ?? null }}">-</button>
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" class="increment-input cart-input cart_input text-center">
                                <button class="btn p-0 increment-btn" data-product-id="{{ $item['product_id'] }}, {{ $item['variant_id'] ?? null }}">+</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-danger text-center">Your cart is empty!</p>
                    @endforelse
                </div>
                <div class="pt-3 border-top mt-1 text-center">
                    <a href="{{ route('my-cart') }}" class="btn btn-danger px-5 {{ count(session('cart', [])) == 0 ? 'disabled' : '' }}">
                        Continue To Cart
                    </a>
                </div>
            </div>
        </div>

        @if(Auth::guard('user')->check())
            <a href="{{ route('user-logout') }}" class="btn btn-primary py-2 px-4" id="logout">Logout</a>
        @else
            <a href="{{ asset('login') }}" class="btn btn-primary py-2 px-4">Login</a>
        @endif
    </div>
</nav>

<!-- Overlay Search -->
<div id="myOverlay" class="overlay">
    <span class="close-btn" title="Close Overlay">×</span>
    <div class="overlay-content">
        <form action="{{ route('product.search') }}" method="GET" class="mb-0">
            <input type="text" placeholder="Search Your Favorite Food ..." name="search">
            <button type="submit" class="btn btn-primary" style="border:none;border-radius:0"><span class="fa fa-search"></span></button>
        </form>
    </div>
</div>

<!-- Modal - Only show if product exists -->
@if($product)
<div class="container-fluid cart food-modal wow fadeIn" data-wow-delay="0.1s">
    <div class="modal fade menu-modal" id="menuModalFull-{{ $product->id }}" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body p-0 scrollable">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    
                    <!-- IMAGE with Discount Badge -->
                    <div class="position-relative">
                        <img class="w-100" src="{{ asset($product->image ?? 'public/img/default.jpg') }}" alt="product-img">
                        
                        @if(isset($product->featured_action) && $product->featured_action == 'decrease' && $discountPercent > 0)
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                {{ $discountPercent }}% OFF
                            </span>
                        @endif
                    </div>

                    <div class="p-3 description">
                        <h3>{{ $product->name ?? 'Product' }}</h3>

                        <!-- PRICE SECTION with Discount Logic -->
                        @if(isset($product->variants) && count($product->variants) > 0)
                            @if(isset($product->featured_action) && $product->featured_action == 'decrease' && ($product->original_price ?? false))
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
                                <p>£ <span class="prodPrice">{{ $product->default_price ?? 0 }}</span></p>
                            @endif

                            <select class="form-control bg-white ps-1 select-size"
                                name="variant_id" id="sizeSelect" style="appearance: auto">
                                @foreach ($product->variants as $variant)
                                    <option value="{{ $variant->id }} {{ $variant->price ?? 0 }}">
                                        {{ $variant->size ?? 'Regular' }}
                                    </option>
                                @endforeach
                            </select>
                            <h6 class="small mt-1 mb-3">Note: Prices vary depending on the selected size</h6>
                                
                        @else
                            @if(isset($product->featured_action) && $product->featured_action == 'decrease' && ($product->original_price ?? false))
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
                                <p>£ <span class="prodPrice">{{ $product->price ?? 0 }}</span></p>
                            @endif
                        @endif

                        <div class="d-flex cart-btn">
                            <button class="btn p-0 decrement" type="button">-</button>
                            <input type="text" class="cart_input increment-input text-center"
                                value="1" name="quantity"
                                id="quantity_{{ $product->id }}">
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

                                    {{-- Pickup Address --}}
                                    <p class="small fw-bold m-0 sel-location mt-1" 
                                    id="storePickupSection{{ $product->id }}_{{ $branch->id }}_{{ $index }}">
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
                                            name="status_{{ $product->id }}" 
                                            id="homeStatus{{ $product->id }}_{{ $branch->id }}_{{ $index }}" 
                                            value="2" 
                                            onchange="toggleDelivery('{{ $product->id }}', '{{ $branch->id }}_{{ $index }}')">

                                        <label class="form-check-label fw-bold small" 
                                            for="homeStatus{{ $product->id }}_{{ $branch->id }}_{{ $index }}">
                                            Home Delivery
                                        </label>
                                    </div>

                                    {{-- Delivery Address Input --}}
                                    <div id="deliveryAddressField{{ $product->id }}_{{ $branch->id }}_{{ $index }}" 
                                        class="mt-2" style="display: none;">
                                        <input type="text" 
                                            id="deliveryInput{{ $product->id }}_{{ $branch->id }}"
                                            name="delivery_address_{{ $product->id }}" 
                                            class="form-control location-input"
                                            data-product="{{ $product->id }}"
                                            data-branch="{{ $branch->id }}"
                                            placeholder="Enter your delivery address"
                                            autocomplete="off"
                                            value=""/>

                                        <!-- Hidden lat/lng -->
                                        <input type="hidden" name="lat_{{ $product->id }}" id="lat{{ $product->id }}_{{ $branch->id }}">
                                        <input type="hidden" name="lng_{{ $product->id }}" id="lng{{ $product->id }}_{{ $branch->id }}">
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <!-- Location End -->

                    <!-- Toppings Start-->
                    @if(isset($product->category) && $product->category && $product->category->isNotEmpty())
                        @foreach ($product->category as $index => $category)
                            @if(isset($category->getCategory))
                            <div class="description p-3">
                                <div class="arrow" style="cursor: pointer"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#toppingFull{{ $index }}{{ $category->id }}">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="m-0">{{ $category->getCategory->name ?? '' }}</h6>
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
                                            $category->getCategory->id ?? 0,
                                        )->get();
                                    @endphp
                                    @foreach ($categoryToppings as $categoryTopping)
                                        @if(isset($categoryTopping->topping))
                                        <div class="d-flex justify-content-between">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    name="toppings[]"
                                                    id="toppingchekFull{{ $index }}{{ $category->id }}{{ $categoryTopping->topping->id }}"
                                                    value="{{ $categoryTopping->topping->id }}"
                                                    data-category-id="{{ $category->getCategory->id ?? 0 }}">
                                                <label class="form-check-label m-0"
                                                    for="toppingchekFull{{ $index }}{{ $category->id }}{{ $categoryTopping->topping->id }}">
                                                    {{ $categoryTopping->topping->name ?? '' }}
                                                </label>
                                            </div>
                                            <p class="m-0">
                                                {{ isset($categoryTopping->topping->price) ? '£' . $categoryTopping->topping->price : '' }}
                                            </p>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif
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
@endif

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
$(document).ready(function(){

    function updateServerCart(productId, variantId, quantity){
        $.post('{{ route("update.cart") }}', {
            '_token':'{{ csrf_token() }}',
            product_id: productId,
            variant_id: variantId,
            quantity: quantity
        }, function(data){ console.log('Cart updated', data); });
    }

    function helper(){
        let total = 0;
        $('.cart-input').each(function(){ total += parseInt($(this).val()); });
        $('.cart-counter-1').text(total);
    }

    $(document).on('click', '.increment-btn', function(){
        let input = $(this).siblings('.increment-input');
        let qty = parseInt(input.val())+1;
        input.val(qty);

        let data = $(this).data('product-id').split(',');
        if(data[0]) updateServerCart(data[0].trim(), data[1]?.trim(), qty);
        let price = parseFloat($(this).closest('.carting-child').find('.product-price').text());
        $(this).closest('.carting-child').find('.total-price').text('£'+(price*qty).toFixed(2));
        helper();
    });

    $(document).on('click', '.decrement-btn', function(){
        let input = $(this).siblings('.increment-input');
        let qty = parseInt(input.val());
        if(qty>1){ 
            qty--; input.val(qty);
            let data = $(this).data('product-id').split(',');
            if(data[0]) updateServerCart(data[0].trim(), data[1]?.trim(), qty);
            let price = parseFloat($(this).closest('.carting-child').find('.product-price').text());
            $(this).closest('.carting-child').find('.total-price').text('£'+(price*qty).toFixed(2));
            helper();
        }
    });

    helper();
});
</script>

@if (url()->current() !== url('/'))
<script async defer 
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUMK9qFdsbuuuTMiaPHCJok4Rro91yvaE&libraries=places&callback=initAllAutocomplete">
</script>
<script>
    $('.addto-cart').on('click', function() {
        var modal = $(this).closest('.food-modal');
        var complementry_id = modal.find('input[name="complementary_id"]').length 
            ? modal.find('input[name="complementary_id"]').val() 
            : null;
            
        var productId = $(this).closest('.food-modal').find('input[name="product_id"]').val();
        if(!productId) {
            toastr.error('Product not found');
            return;
        }
        
        var quantity = $(this).closest('.food-modal').find('input[name="quantity"]').val();
        var isLocationChecked = $(this).closest('.food-modal').find('input[name="location"]:checked').length > 0;
        var branchId = isLocationChecked ? $(this).closest('.food-modal').find('input[name="branch_id"]').val() : '';
        var variantId = '';

        var deliveryStatus = $(this).closest('.food-modal').find('input[name^="status_' + productId + '"]:checked').val();
        var deliveryAddress = '';
        
        if (deliveryStatus == '2') {
            deliveryAddress = $(this).closest('.food-modal').find('input[name="delivery_address_' + productId + '"]').val();
            if (!deliveryAddress) {
                toastr.error('Please enter delivery address');
                return;
            }
        }

        var lat = '';
        var lng = '';
        if (deliveryStatus == '2') {
            lat = $(this).closest('.food-modal').find('input[name="lat_' + productId + '"]').val();
            lng = $(this).closest('.food-modal').find('input[name="lng_' + productId + '"]').val();
        }
        if(deliveryStatus == '2' && (lat == '' || lng == '')) {
            toastr.error('Please select a valid delivery address from suggestions');
            return;
        }

        var variantSelect = $(this).closest('.food-modal').find('select[name="variant_id"]');
        if (variantSelect.length > 0) {
            var variantValue = variantSelect.val();
            if(variantValue) variantId = variantValue.split(' ')[0];
        }

        var selectedToppingsByCategory = {};

        $(this).closest('.food-modal').find('input[name="toppings[]"]:checked').each(function() {
            var categoryId = $(this).data('category-id');
            var toppingId = $(this).val();

            if (!selectedToppingsByCategory.hasOwnProperty(categoryId)) {
                selectedToppingsByCategory[categoryId] = [];
            }

            selectedToppingsByCategory[categoryId].push(toppingId);
        });

        var toppingsArray = Object.entries(selectedToppingsByCategory).map(([categoryId, toppings]) => {
            return {
                category_id: categoryId,
                toppings: toppings
            };
        });

        $.ajax({
            type: 'POST',
            url: '{{ route("add.to.cart") }}',
            data: {
                '_token': '{{ csrf_token() }}',
                'product_id': productId,
                'quantity': quantity,
                'branch_id': branchId,
                'toppings_by_category': toppingsArray,
                'location': isLocationChecked,
                'variant_id': variantId,
                'delivery_status': deliveryStatus,
                'delivery_address': deliveryAddress,
                'lat': lat,
                'lng': lng,
                'complementary_id': complementry_id
            },
            success: function(data) {
                toastr.success('Product Added To Cart Successfully!');
                $('.cart-counter-1').text(Object.keys(data.cart).length);
                if(typeof updateCartUI === 'function') updateCartUI(data);
            },
            error: function(error) {
                console.error('Error adding product to cart:', error);
                toastr.error('Error adding product to cart');
            }
        });
    });
    
    function toggleDelivery(productId, branchUnique) {
        const pickupRadio = document.getElementById(`pickupStatus${productId}_${branchUnique}`);
        const homeRadio = document.getElementById(`homeStatus${productId}_${branchUnique}`);
        const pickupSection = document.getElementById(`storePickupSection${productId}_${branchUnique}`);
        const deliveryField = document.getElementById(`deliveryAddressField${productId}_${branchUnique}`);

        if (homeRadio && homeRadio.checked) {
            if(pickupSection) pickupSection.style.display = 'none';
            if(deliveryField) deliveryField.style.display = 'block';
        } else if (pickupRadio && pickupRadio.checked) {
            if(pickupSection) pickupSection.style.display = 'block';
            if(deliveryField) {
                deliveryField.style.display = 'none';
                var input = deliveryField.querySelector('input');
                if(input) input.value = '';
            }
        }
    }
    
    window.initAllAutocomplete = function () {
        console.log("Google Loaded ✅");
        bindAutocomplete();

        document.addEventListener('shown.bs.modal', function (event) {
            bindAutocomplete(event.target);
        });
    };

    function bindAutocomplete(container = document) {
        if(!container.querySelectorAll) return;
        
        container.querySelectorAll('.location-input').forEach(input => {
            if (input.dataset.autocompleteInit === "1") return;
            input.dataset.autocompleteInit = "1";

            const productId = input.dataset.product;
            const branchId = input.dataset.branch;
            if(!productId || !branchId) return;

            let isSelectedFromList = false;

            const autocomplete = new google.maps.places.Autocomplete(input, {
                fields: ["geometry", "name"],
                types: ['geocode'],
                componentRestrictions: { country: "gb" }
            });

            const latField = document.getElementById(`lat${productId}_${branchId}`);
            const lngField = document.getElementById(`lng${productId}_${branchId}`);
            if(!latField || !lngField) return;

            input.addEventListener('input', function () {
                isSelectedFromList = false;
                if(latField) latField.value = '';
                if(lngField) lngField.value = '';
            });

            autocomplete.addListener('place_changed', function () {
                const place = autocomplete.getPlace();
                if (!place.geometry) return;
                isSelectedFromList = true;
                if(latField) latField.value = place.geometry.location.lat();
                if(lngField) lngField.value = place.geometry.location.lng();
            });

            input.addEventListener('blur', function () {
                setTimeout(() => {
                    if (latField && latField.value && lngField && lngField.value) {
                        return;
                    }
                    input.value = '';
                    if(latField) latField.value = '';
                    if(lngField) lngField.value = '';
                    alert("Please select a location from suggestions only.");
                }, 300);
            });
        });
    }
</script>
@endif