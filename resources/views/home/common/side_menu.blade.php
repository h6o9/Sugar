<div id="sidenav-overlay"></div>
<div class="d-lg-none hidden sidebar">
    <button type="button" class="sp-close-nav" id="closeSidebar" aria-label="Close">&times;</button>

    <a class="sp-nav-link sp-nav-link-special" href="{{ route('pappi-special') }}">
        <i class="ri-star-smile-line"></i> Sugar Pappi Special
    </a>

    <div class="sp-menu-group">
        <button type="button" class="sp-nav-link sp-menu-toggle" id="spMenuToggle" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <i class="ri-file-list-3-line"></i> Menu
            </span>
            <i class="ri-arrow-right-s-line sp-chevron" aria-hidden="true"></i>
        </button>
        <div class="sp-menu-cats" id="spMenuCats" hidden>
            @foreach(($navMenus ?? collect()) as $menu)
                @php
                    $type = strtolower((string) ($menu->type ?? 'food'));
                    $isSpecial = \App\Support\MenuCatalog::isSpecial($menu);
                    $isWholesale = \App\Support\MenuCatalog::isWholesale($menu);
                @endphp
                @if($type !== 'wholesale' && !$isSpecial && !$isWholesale)
                    <a class="sp-nav-link ps-4" href="{{ route('get-our-menu') }}#menuTab{{ $menu->id }}">
                        <i class="{{ $menu->icon ?: 'ri-restaurant-line' }}"></i> {{ $menu->name }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>

    <a class="sp-nav-link" href="{{ route('drive-in') }}">
        <i class="ri-car-line"></i> Drive-In. <span class="sp-badge-save">SAVE 20%</span>
    </a>
    <a class="sp-nav-link" href="{{ route('private-bookings') }}">
        <i class="ri-calendar-event-line"></i> Private Bookings
    </a>
    <a class="sp-nav-link" href="{{ route('dessert-wholesale') }}">
        <i class="ri-box-3-line"></i> Dessert Wholesale
    </a>
    <a class="sp-nav-link" href="{{ $whatsappUrl ?? 'https://wa.me/447727412922' }}" target="_blank" rel="noopener">
        <i class="ri-whatsapp-line"></i> WhatsApp Us
    </a>
    <a class="sp-nav-link" href="{{ route('get-our-gallery') }}">
        <i class="ri-image-line"></i> Gallery
    </a>
    @if (Auth::guard('user')->check())
        <a class="sp-nav-link" href="{{ route('my-order') }}"><i class="ri-shopping-bag-line"></i> My Orders</a>
        <a href='{{ route('user-logout') }}' class="sp-nav-link logout">Logout</a>
    @else
        <a href="{{ route('login') }}" class="sp-nav-link">Login</a>
    @endif
</div>
