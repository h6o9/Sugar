@extends('home.layout.app')
@section('title', 'Login')
@section('content')
    <section class="section">
        <div class="container-xxl position-relative p-0">

            @include('home.partials.page-hero', ['title' => 'Privacy Policy'])
        </div>
        <div class="container-xxl bg-white p-0">
            <!-- Privacy Policy Start -->
            <div class="px-lg-5 px-3 mx-auto mt-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="p-3 rounded light-box-shadow">
                    <p>
                        {!! $data->description ?? '' !!}
                    </p>
                </div>
            </div>
            <!-- Privacy Policy End -->
        </div>
    </section>
@endsection
@section('js')
    @if (\Illuminate\Support\Facades\Session::has('message'))
        <script>
            toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
        </script>
    @endif
@endsection
