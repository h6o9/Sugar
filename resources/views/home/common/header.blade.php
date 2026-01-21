<!-- Spinner Start -->
@php
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

$notifications = collect();
$notificationCount = 0;

if (Auth::guard('user')->check()) {
    $userId = Auth::guard('user')->id();
    $notifications = Notification::where('user_id', $userId)
        ->where('seenByUser', '0')
        ->latest()
        ->get();
    $notificationCount = $notifications->count();
}
@endphp

<style>
/* Notification CSS */
.notification-wrapper { position: relative; }
.mobile-notify-block { width:340px; max-width:90%; right:0; left:auto; transform:none; margin-top:12px; }
.notify-block { max-height:300px; overflow-y:auto; }
.notification-title, .notification-desc, .mobile-notify-block h5, .mobile-notify-block { color:#000 !important; }
.notification-title { font-weight:600; font-size:14px; margin-bottom:4px; }
.notification-desc { font-size:13px; line-height:1.4; }
.bell-counter { background:red; color:#fff; font-size:11px; padding:2px 6px; border-radius:50%; position:absolute; top:-6px; right:-6px; }
.mobile-notify-block .card { border:none; border-bottom:1px solid #eee; }
.mobile-notify-block .card:last-child { border-bottom:none; }

/* Notifications Text */
.mobile-notify-block,
.mobile-notify-block h5,
.mobile-notify-block .notification-title,
.mobile-notify-block .notification-desc,
.mobile-notify-block .card-body {
    color: #000 !important;  /* Black color */
}


/* Responsive */
@media(max-width:992px){.mobile-notify-block{width:80%; left:10%; right:auto;}}
@media(max-width:768px){.mobile-notify-block{width:92%; left:4%;}}
@media(max-width:480px){.mobile-notify-block{width:96%; left:2%;}}
</style>

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
                    @forelse($notifications as $notification)
                    <div class="card">
                        <div class="card-body">
                            <div class="notification-title" style="color:#000 !important;">{{ $notification->title }}</div>
<div class="notification-desc" style="color:#000 !important;">{{ $notification->description }}</div>

                        </div>
                    </div>
                    @empty
                    <div class="card">
                        <div class="card-body text-center text-black">No Notifications Found</div>
                    </div>
                    @endforelse
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

        <div class="nav-item dropdown">
            <a href="#" class="nav-link p-0" data-bs-toggle="dropdown">
                <span class="fa fa-bell me-3 position-relative header-icon"><span class="badge bell-counter">{{ $notificationCount }}</span></span>
            </a>
            <div class="carting-card dropdown-menu py-3 px-0">
                <div class="border-bottom pb-3 px-3">
                    <h5 class="m-0">Notifications ({{ $notificationCount }})</h5>
                </div>
                <div class="notify-block scrollable">
                    @forelse($notifications as $notification)
                    <div class="card">
                        <div class="card-body">
                            <a href="#" class="markAsRead" data-notification-id="{{ $notification->id }}">
                                {{ $notification->title }}
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="card">
                        <div class="card-body text-center text-danger">No Notifications Found!</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        <!-- Cart Dropdown -->
        <div class="ms-2 nav-item dropdown">
            <a href="#" class="nav-link p-0" data-bs-toggle="dropdown">
                <span class="fa fa-shopping-cart me-3 position-relative header-icon"><span class="badge cart-counter cart-counter-1"></span></span>
            </a>
            <div class="carting-card dropdown-menu py-3 px-0">
                <div class="border-bottom mb-1 pb-3 px-3">
                    <h5 class="m-0">Your Cart (<span class="cart-counter-1"></span>)</h5>
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
                    <a href="{{ route('my-cart') }}" class="btn btn-danger px-5 {{ count(session('cart', [])) == 0 ? 'disabled' : '' }}">Continue To Cart</a>
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
        updateServerCart(data[0].trim(), data[1]?.trim(), qty);
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
            updateServerCart(data[0].trim(), data[1]?.trim(), qty);
            let price = parseFloat($(this).closest('.carting-child').find('.product-price').text());
            $(this).closest('.carting-child').find('.total-price').text('£'+(price*qty).toFixed(2));
            helper();
        }
    });

    $('.markAsRead').click(function(e){
        e.preventDefault();
        let id = $(this).data('notification-id');
        let item = $(this);
        $.get('{{ route("markAllAsRead") }}', {id:id}, function(){
            item.remove();
            let counter = parseInt($('.bell-counter').text())-1;
            $('.bell-counter').text(counter > 0 ? counter : 0);
        });
    });

    helper();
});
</script>
