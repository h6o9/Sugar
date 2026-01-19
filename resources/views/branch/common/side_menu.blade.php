<style>
    li a{
        font-size: 14px!important;
    }
</style>

<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ url('/branch/dashboard') }}"> <img alt="image" src="{{ asset('public/img/logo.png') }}"
                    class="header-logo" /> <span class="logo-name"></span>
            </a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Main</li>
            <li class="dropdown {{ request()->is('branch/dashboard') ? 'active' : '' }}">
                <a href="{{ url('/branch/dashboard') }}" class="nav-link"><i
                        data-feather="home"></i><span>Dashboard</span></a>
            </li>
            {{-- <li class="dropdown {{ request()->is('branch/schedule') ? 'active' : '' }}">
                <a href="{{ url('/branch/schedule') }}" class="nav-link"><i data-feather="home"></i><span>Add Pick Up
                        Time</span></a>
            </li> --}}
            {{-- Orders --}}
            <li class="dropdown {{ request()->is('branch/order') ? 'active' : '' }}">
                @php
                    $branchId = Auth::guard('branch')->id();
                    $requestCount = App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->where('order_items.branch_id', $branchId)
                        ->where('orders.status', 'Pending')
                        ->count();
                @endphp
                <a href="{{ url('/branch/order') }}" class="nav-link">
                    <i data-feather="shopping-bag"></i>
                    <span>Orders</span>
                    @if ($requestCount > 0)
                        <div class="badge bg-danger rounded-circle text-white">
                            {{ $requestCount }}</div>
                    @endif
                </a>
            </li>
            {{-- Sells routes --}}
            <li class="dropdown {{ request()->is('branch/sales') ? 'active' : '' }}">
                <a href="{{ url('/branch/sales') }}" class="nav-link"><i
                        data-feather="shopping-cart"></i><span>Sales</span></a>
            </li>
            {{-- <li class="dropdown {{ request()->is('admin/officer*') ? 'active' : '' }}">
                <a href="{{ route('officer.index') }}" class="nav-link"><i data-feather="users"></i><span>Officer</span></a>
            </li> --}}
            {{-- <li class="dropdown {{ request()->is('admin/about*') ? 'active' : '' }}">
                <a href="{{ route('about.index') }}" class="nav-link"><i data-feather="monitor"></i><span>About
                        Us</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/policy*') ? 'active' : '' }}">
                <a href="{{ route('policy.index') }}" class="nav-link"><i data-feather="monitor"></i><span>Privacy
                        Policy</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/terms*') ? 'active' : '' }}">
                <a href="{{ route('terms.index') }}" class="nav-link"><i
                        data-feather="monitor"></i><span>Term&Condition</span></a>
            <li class="dropdown {{ request()->is('admin/faq*') ? 'active' : '' }}">
                <a href="{{ route('faq.index') }}" class="nav-link"><i
                        data-feather="monitor"></i><span>FAQ's</span></a>
            </li> --}}
            </li>
        </ul>
    </aside>
</div>
