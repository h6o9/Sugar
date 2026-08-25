@extends('home.layout.app')
@section('title', 'Login')
@section('content')
    <style>
        .faq {
            background: #FFF;
            box-shadow: 0 2px 48px 0 rgba(0, 0, 0, 0.06);
            border-radius: 4px;
        }

        .faq .card {
            border: none;
            background: none;
            border-bottom: 1px dashed #CEE1F8;
        }

        .faq .card:last-child {
            border-bottom: 0;
        }

        .faq .card .card-header {
            padding: 0px;
            border: none;
            background: none;
            -webkit-transition: all 0.3s ease 0s;
            -moz-transition: all 0.3s ease 0s;
            -o-transition: all 0.3s ease 0s;
            transition: all 0.3s ease 0s;
        }

        .faq .card .card-header:hover {
            background: rgb(29 44 66 / 9%);
            padding-left: 10px;
        }

        .faq .card .card-header .faq-title {
            display: flex;
            width: 100%;
            text-align: left;
            padding: 0 16px;
            letter-spacing: 1px;
            color: #3B566E;
            background: rgb(29 44 66 / 9%);
            text-decoration: none !important;
            -webkit-transition: all 0.3s ease 0s;
            -moz-transition: all 0.3s ease 0s;
            -o-transition: all 0.3s ease 0s;
            transition: all 0.3s ease 0s;
            cursor: pointer;
            padding-top: 20px;
            padding-bottom: 20px;
            margin-bottom: 0;
        }

        .faq .card .card-header .faq-title.collapsed {
            background-color: #FFF;
        }

        .faq .card .card-header .faq-title .badge {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 20px;
            height: 20px;
            line-height: 14px;
            -webkit-border-radius: 100px;
            -moz-border-radius: 100px;
            border-radius: 100px;
            text-align: center;
            background: var(--primary);
            color: #000;
            font-size: 12px;
            margin-right: 8px;
        }

        .faq .card .card-body {
            padding-bottom: 16px;
            font-weight: 400;
            font-size: 15px;
            letter-spacing: 1px;
            border-top: 1px solid #F3F8FF;
            text-align: justify;
        }

        .faq .card .card-body p {
            margin-bottom: 0;
        }
    </style>
    <section class="section">
        <div class="container-xxl position-relative p-0">
            @include('home.partials.page-hero', ['title' => "FAQ's"])
        </div>
        <div class="container-xxl bg-white p-0">
            <!-- FAQ's Start -->
            <div class="px-lg-5 px-3 mx-auto mt-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="p-3 rounded light-box-shadow">
                    <section class="faq-section">
                        <div class="faq" id="accordion">
                            @foreach ($data as $faq)
                                <div class="card">
                                    <div class="card-header" id="faqHeading-{{ $faq->id }}">
                                        <div class="mb-0">
                                            <h6 class="faq-title collapsed" data-bs-toggle="collapse"
                                                data-bs-target="#faqCollapse-{{ $faq->id }}" aria-expanded="false"
                                                aria-controls="faqCollapse-{{ $faq->id }}">
                                                <span class="badge">{{ $loop->iteration }}</span>{!! $faq->question ?? '' !!}
                                            </h6>
                                        </div>
                                    </div>
                                    <div id="faqCollapse-{{ $faq->id }}" class="collapse"
                                        aria-labelledby="faqHeading-{{ $faq->id }}" data-bs-parent="#accordion">
                                        <div class="card-body">
                                            <p>{!! $faq->answer ?? '' !!}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
            <!-- FAQ's End -->
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
