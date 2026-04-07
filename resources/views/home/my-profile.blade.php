@extends('home.layout.app')
@section('title', 'Login')
@section('content')
    <section class="section">
        <!-- My Profile Start -->
        <div class="container-xxl py-5 pb-lg-5 pb-0 px-0 wow fadeInUp" data-wow-delay="0.1s">
            <div class="row g-0">
                <div class="col-xl-6 col-sm-8 col-11 mx-auto bg-primary d-flex align-items-center">
                    <div class="p-xl-5 p-4 wow fadeInUp light-box-shadow" data-wow-delay="0.2s">
                        <h5 class="section-title ff-secondary text-start text-dark fw-normal">My Profile</h5>
                        <h1 class="text-dark mb-4">Hey {{ $user->name }}!</h1>
                        <form action="{{ route('update-profile', ['id' => $user->id]) }}" method="post">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="Your Name" value="{{ $user->name }}">
                                        <label for="name">Your Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="Your Email" value="{{ $user->email }}" readonly>
                                        <label for="email">Your Email</label>
                                    </div>
                                </div>
								 <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            placeholder="Your Phone Number" value="{{ $user->phone }}">
                                        <label for="phone">Phone Number</label>
                                    </div>
                                </div>
								 <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="postcode" name="postcode"
                                            placeholder="Your Postcode" value="{{ $user->postcode }}">
                                        <label for="postcode">Postcode</label>
                                    </div>
                                </div>
								 <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="address" name="address"
                                            placeholder="Your Address" value="{{ $user->address }}" >
                                        <label for="address">Address</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="userPassword" name="password"
                                            placeholder="Password">
                                        <label for="userPassword">Password</label>
                                    </div>
                                    @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="userConfirmPassword"
                                            name="password_confirmation" placeholder="Confirm Password">
                                        <label for="userConfirmPassword">Confirm Password</label>
                                    </div>
                                    @error('password_confirmation')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="userLoyaltyPoints"
                                            name="rewards" placeholder="Loyalty Points" value="{{ $reward->rewards ?? 0 }}" readonly>
                                        <label for="userLoyaltyPoints">Loyalty Points</label>
                                    </div>
                                    @error('rewards')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary w-100 py-3" type="submit">Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- My Profile End -->
    </section>
@endsection
@section('js')
    @if (\Illuminate\Support\Facades\Session::has('message'))
        <script>
            toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
        </script>
    @endif
@endsection
