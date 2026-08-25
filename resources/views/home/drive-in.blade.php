@extends('home.layout.app')
@section('title', 'Drive-In 20% Off')
@section('content')
@include('home.partials.page-hero', ['title' => 'Drive-In'])
<div class="sp-drive-bar">20% OFF THE FULL MENU</div>
<div class="container py-5">
    <p>Order from the full food menu and receive a real 20% Drive-In discount at checkout. Vehicle details can be added on the checkout page.</p>
    @if(empty($hours['is_open']))
        <div class="alert alert-dark">{{ $hours['message'] }}
            <form method="POST" action="{{ route('schedule.order') }}" class="d-inline">@csrf<button class="btn btn-sm sp-btn-pink ms-2">Schedule Order</button></form>
        </div>
    @endif
    <a href="{{ route('get-our-menu') }}" class="btn sp-btn-pink">Browse the full menu</a>
</div>
@endsection
