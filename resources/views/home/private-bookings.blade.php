@extends('home.layout.app')
@section('title', 'Private Bookings')
@section('content')
@include('home.partials.page-hero', ['title' => 'Private Bookings'])
<div class="container py-5">
    <div class="row align-items-center g-4">
        <div class="col-lg-6">
            <img src="{{ asset(optional($content)->image ?: \App\Models\BusinessSetting::getValue('private_booking_image', 'public/img/private-bookings.jpg')) }}"
                 class="img-fluid rounded shadow" alt="Private Bookings">
        </div>
        <div class="col-lg-6">
            <h1>{{ optional($content)->title ?: \App\Models\BusinessSetting::getValue('private_booking_title', 'Private Bookings / Large Orders') }}</h1>
            <p>{{ optional($content)->description ?: \App\Models\BusinessSetting::getValue('private_booking_description', 'Book Sugar Pappi for private events and large orders.') }}</p>
            <a class="btn sp-btn-pink" href="{{ $whatsappUrl ?? 'https://wa.me/'.$whatsapp }}" target="_blank" rel="noopener">
                <i class="ri-whatsapp-line"></i> WhatsApp Us
            </a>
        </div>
    </div>
</div>
@endsection
