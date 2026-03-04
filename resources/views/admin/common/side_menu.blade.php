<style>
    .main-sidebar li span {
        font-size: 14px !important;
    }
</style>
<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ url('/admin/dashboard') }}"> <img alt="image" src="{{ asset('public/img/logo.png') }}"
                    class="header-logo" /> <span class="logo-name"></span>
            </a>
        </div>
        @php

            $requestCount = App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'Pending')
                ->count();

        @endphp
        <ul class="sidebar-menu">
            <li class="menu-header">Main</li>
            <li class="dropdown {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <a href="{{ url('/admin/dashboard') }}" class="nav-link"><i
                        data-feather="home"></i><span>Dashboard</span></a>
            </li>
            {{-- Ordera --}}
            <li class="dropdown {{ request()->is('admin/order*') ? 'active' : '' }}">
                <a href="{{ route('orders.index') }}" class="nav-link">
                    <i class="fas fa-shopping-bag"></i><span>Orders</span>
                    @if ($requestCount > 0)
                        <div class="badge bg-danger rounded-circle text-white">
                            {{ $requestCount }}</div>
                    @endif
                </a>
            </li>

			            {{-- Order Completion Rewards  --}}

			 <li class="dropdown {{ request()->is('admin/completion-order*') ? 'active' : '' }}">
                <a href="{{ route('order-completion') }}" class="nav-link">
                    <i class="fas fa-check-circle"></i><span>Order Completion Rewards</span></a>
            </li>

			{{-- Referral Link Reward Settings  --}}
			 <li class="dropdown {{ request()->is('admin/referral-reward-settings*') ? 'active' : '' }}">
				<a href="{{ route('referral-reward-settings') }}" class="nav-link">
					<i class="fas fa-gift"></i><span>Referral Link Reward Settings</span></a>
	        </li>

						{{-- Reward Settings  --}}
			 <li class="dropdown {{ request()->is('admin/reward-settings*') ? 'active' : '' }}">
				<a href="{{ route('reward-settings') }}" class="nav-link">
					<i class="fas fa-gift"></i><span>Reward Settings</span></a>
	        </li>

					
			
            {{-- Sales  --}}
            <li class="dropdown {{ request()->is('admin/sales*') ? 'active' : '' }}">
                <a href="{{ route('sales.index') }}" class="nav-link">
                    <i class="fas fa-shopping-cart"></i><span>Sales</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/menu*') ? 'active' : '' }}">
                <a href="{{ route('menu.index') }}" class="nav-link"><i
                        class="fa fa-list-alt"></i><span>Menu</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/topping*') ? 'active' : '' }}">
                <a href="{{ route('topping.index') }}" class="nav-link"><i
                        class="fas fa-globe"></i><span>Toppings</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/category*') ? 'active' : '' }}">
                <a href="{{ route('category.index') }}" class="nav-link"><i
                        class="fas fa-globe"></i><span>Categories</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/product*') ? 'active' : '' }}">
                <a href="{{ route('product.index') }}" class="nav-link"><i
                        class="fab fa-product-hunt"></i><span>Product</span></a>
            </li>

				{{-- Bulk Feature Settings  --}}
			 <li class="dropdown {{ request()->is('admin/bulk-feature-settings*') ? 'active' : '' }}">
				<a href="{{ route('bulk-feature.index') }}" class="nav-link">
					<i class="fas fa-boxes"></i><span>Bulk Feature Settings</span></a>
	        </li>
			
				{{-- Bundle Offers  --}}
			 <li class="dropdown {{ request()->is('admin/bundle-offers*') ? 'active' : '' }}">
				<a href="{{ route('complementary.index') }}" class="nav-link">
					<i class="fas fa-boxes"></i><span>Bundle Offers</span></a>
	        </li>
			
            <li class="dropdown {{ request()->is('admin/gallery*') ? 'active' : '' }}">
                <a href="{{ route('gallery.index') }}" class="nav-link">
                    <i class="fas fa-images"></i><span>Our Gallery</span></a>
            </li>
            {{-- Menu Gallery  --}}
            {{-- <li class="dropdown {{ request()->is('admin/m-gallery*') ? 'active' : '' }}">
                <a href="{{ route('m-gallery.index') }}" class="nav-link">
                    <i class="fas fa-images"></i><span>Menu Gallery</span></a>
            </li> --}}

            <li class="dropdown {{ request()->is('admin/branch*') ? 'active' : '' }}">
                <a href="{{ route('branches.index') }}" class="nav-link">
                    <i class="fas fa-code-branch"></i><span>Sugar-Papi Branch</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/user*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="nav-link">
                    <i class="fas fa-user"></i><span>Users</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/time-slots*') ? 'active' : '' }}">
                <a href="{{ route('time-slot.index') }}" class="nav-link">
                    <i class="fas fa-clock"></i><span>Time Slot</span></a>
            </li>
            </li>

            {{-- <li class="dropdown {{ request()->is('admin/officer*') ? 'active' : '' }}">
                <a href="{{ route('officer.index') }}" class="nav-link"><i
                        data-feather="users"></i><span>Officer</span></a>
            </li> --}}
            {{-- <li class="dropdown {{ request()->is('admin/about*') ? 'active' : '' }}">
                <a href="{{ route('about.index') }}" class="nav-link"><i data-feather="monitor"></i><span>About
                        Us</span></a>
            </li> --}}

			 {{-- Notification --}}



           
             <!-- {-- Notification --}} -->


             <li class="dropdown {{ request()->is('admin/notification*') ? 'active' : '' }}">

            <a href="

                {{ route('notification.index') }}

                " class="nav-link">

                <i data-feather="bell"></i><span>Notifications</span>

            </a>

            </li>  

            <li class="dropdown {{ request()->is('admin/policy*') ? 'active' : '' }}">
                <a href="{{ route('policy.index') }}" class="nav-link"><i class="fas fa-file-alt"></i><span>Privacy
                        Policy</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/terms*') ? 'active' : '' }}">
                <a href="{{ route('terms.index') }}" class="nav-link"><i class="fas fa-file-alt"></i><span>Terms &
                        Conditions</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/faq*') ? 'active' : '' }}">
                <a href="{{ route('faq.index') }}" class="nav-link"><i class="fas fa-file-alt"></i><span>FAQ's</span></a>
            </li>
            {{-- <li class="dropdown {{ request()->is('admin/seamoss*') ? 'active' : '' }}">
                <a href="{{ route('seamoss.index') }}" class="nav-link"><i data-feather="video"></i><span>NEW!
                        SEA MOSS
                    </span></a>
            </li> --}}
            </li>
        </ul>
    </aside>
</div>
