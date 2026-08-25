<div class="{{ $wrapperClass ?? 'nav-item dropdown' }}">
    <a href="#" class="{{ $toggleClass ?? 'nav-link p-0' }}" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        <span class="fa fa-shopping-cart {{ $iconClass ?? 'me-3' }} position-relative header-icon">
            <span class="badge cart-counter cart-counter-1">{{ count(session('cart', [])) }}</span>
        </span>
    </a>
    <div class="carting-card dropdown-menu py-2 px-0">
        <div class="border-bottom mb-1 pb-2 px-3 d-flex justify-content-between align-items-start">
            <div>
                <h5 class="mb-1">Your Cart (<span class="cart-counter-1">{{ count(session('cart', [])) }}</span>)</h5>
                <b class="small text-danger d-block">Quick summary — full details on the cart page.</b>
            </div>
            <button type="button" class="btn-close sp-dropdown-close d-lg-none ms-2 mt-1" aria-label="Close"></button>
        </div>
        <div class="cards-parent scrollable">
            @forelse(session('cart', []) as $item)
            <div id="{{ $item['product_id'] }}carted"
                class="carting-child px-3 mt-3 d-flex justify-content-between pb-3 border-bottom">
                <img src="{{ asset($item['image']) }}" alt="">
                <div class="content">
                    <div class="d-flex cart-input-parent justify-content-between">
                        <h6 class="m-0">{{ $item['name'] }}
                            <span style="font-size:12px">{{ !empty($item['size']) ? '('.$item['size'].')' : '' }}</span>
                        </h6>
                        <h6 class="m-0 total-price">
                            £{{ number_format(floatval($item['price']) * intval($item['quantity']), 2) }}
                        </h6>
                        <p class="product-price d-none">{{ floatval($item['price']) }}</p>
                    </div>
                    <div class="cart-btn">
                        <button class="btn p-0 decrement-btn"
                            data-product-id="{{ $item['product_id'] }}, {{ $item['variant_id'] ?? null }}">-</button>
                        <input type="number" name="quantity" value="{{ $item['quantity'] }}"
                            class="increment-input cart-input cart_input text-center">
                        <button class="btn p-0 increment-btn"
                            data-product-id="{{ $item['product_id'] }}, {{ $item['variant_id'] ?? null }}">+</button>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-danger text-center">Your cart is empty!</p>
            @endforelse
        </div>
        <div class="pt-3 border-top mt-1 text-center">
            <a href="{{ route('my-cart') }}"
                class="btn btn-danger px-5 {{ count(session('cart', [])) == 0 ? 'disabled' : '' }}">
                Continue To Cart
            </a>
        </div>
    </div>
</div>
