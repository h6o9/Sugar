<div class="container-fluid text-light footer mt-5 pt-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-4 col-md-6">
                <h4 class="section-title ff-secondary text-start fw-normal mb-4 text-white">Links</h4>
                <a class="btn btn-link" href="{{route('index')}}">Home</a>
                <a class="btn btn-link" href="{{route('get-our-gallery')}}">Explore Sugar Pappi Gallery</a>
                <a class="btn btn-link" href="{{route('dessert-wholesale')}}">Dessert Wholesale</a>
                <a class="btn btn-link" href="{{route('private-bookings')}}">Private Bookings</a>
                <a class="btn btn-link" href="{{ $whatsappUrl ?? 'https://wa.me/447727412922' }}" target="_blank" rel="noopener">WhatsApp Us</a>
                <a class="btn btn-link" href="{{route('get-faqs')}}">FAQ's</a>
                {{--
                <div class="mt-3">
                    <a href="#" class="d-inline-block">
                        <img src="{{ asset('public/img/gslogo.png') }}" alt="Google Play" width="145">
                    </a>
                    <a href="#" class="d-inline-block">
                        <img src="{{ asset('public/img/pslogo.png') }}" alt="App Store" width="145">
                    </a>
                </div>
                --}}
            </div>
            <div class="col-lg-4 col-md-6">
                <h4 class="section-title ff-secondary text-start fw-normal mb-4 text-white">Contact Us</h4>
                {{-- <p class="mb-2"><span class="fa fa-map-marker-alt me-3"></span>123 Street, New York, USA</p>
                <p class="mb-2"><span class="fa fa-phone-alt me-3"></span>+012 345 67890</p> --}}
                <p class="mb-2"><a href="mailto:contact@sugarpappi.com" class="text-white"><span class="fa fa-envelope me-3"></span>contact@sugarpappi.com</a></p>
                <div class="d-flex pt-2">
                    {{-- <a class="btn btn-outline-light btn-social" href=""><span class="fab fa-twitter"></span></a> --}}
                    <a class="btn btn-outline-light btn-social" href=""><span
                            class="fab fa-facebook-f"></span></a>
                    <a class="btn btn-outline-light btn-social" href=""><span class="fab fa-youtube"></span></a>
                    <a class="btn btn-outline-light btn-social"
                        href=""><span
                            class="fab fa-instagram"></span></a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <h4 class="section-title ff-secondary text-start fw-normal mb-4 text-white">Stay Updated!</h4>
                <p class="small text-white">This form is protected by reCAPTCHA and the Google <a class="text-white text-decoration-underline" rel="noreferrer noopener"
                        href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a> and <a class="text-white text-decoration-underline"
                        rel="noreferrer noopener" href="https://policies.google.com/terms" target="_blank">Terms of
                        Service</a> apply.</p>
                <div class="position-relative mx-auto" style="max-width: 400px;">
                    <input class="form-control border-primary w-100 py-3 ps-4 footer-email-box" type="text"
                        placeholder="Your email">
                    <button type="button"
                    id="signupButton" class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2">SignUp</button>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="copyright">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-white">Sugar Pappi &copy;
                            <script>
                                document.write(new Date().getFullYear());
                            </script>
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="footer-menu">
                        {{-- <a type="button" data-bs-toggle="modal" data-bs-target="#disclaimerModal">Disclaimer</a> --}}
                        <a href="{{route('privacy-policy')}}">Privacy Policy</a>
                        <a href="{{route('terms-conditions')}}">Terms & Conditions</a>
                        <a href="{{route('getcontact')}}">Contact Us</a>
                        <a href="{{route('get-faqs')}}">FAQ's</a>
                    </div>
                </div>
                {{-- <div class="col-md-12 text-center">
                    <img src="{{ asset('public/img/googlepay.svg') }}" alt="googlepay" class="m-1" />
                    <img src="{{ asset('public/img/applepay.svg') }}" alt="applepay" class="m-1" />
                    <img src="{{ asset('public/img/visa.svg') }}" alt="visa" class="m-1" />
                    <img src="{{ asset('public/img/mastercard.svg') }}" alt="mastercard" class="m-1" />
                    <img src="{{ asset('public/img/americanexpress.svg') }}" alt="americanexpress" class="m-1" />
                    <img src="{{ asset('public/img/discover.svg') }}" alt="discover" class="m-1" />
                    <img src="{{ asset('public/img/jcb.svg') }}" alt="jcb" class="m-1" />
                    <img src="{{ asset('public/img/cashapp.svg') }}" alt="cashapp" class="m-1" />
                    <img src="{{ asset('public/img/afterpay.svg') }}" alt="afterpay" class="m-1" />
                </div> --}}
            </div>
        </div>
    </div>
</div>
<!-- Footer End -->

<!-- Back to Top -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
</div>

<script>
    document.getElementById('signupButton').addEventListener('click', function() {
        // Redirect to the desired route
        window.location.href = "{{ route('getRegistor') }}";
    });
</script>
