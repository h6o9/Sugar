@extends('admin.layout.app')
@section('title', 'Dashboard')
@section('content')

    <body>
        <div class="main-content">
            <section class="section">
                <div class="section-body">
                    <a class="btn btn-primary mb-3" href="{{ url()->previous() }}">Back</a>
                    <form  action="{{ route('branch-update', $branch->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-12 col-lg-12">
                                <div class="card">
                                    <h4 class="text-center my-4">Add Sugar-Papi Branch</h4>
                                    <div class="row mx-0 px-4">
                                        <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                            <div class="form-group mb-2">
                                                <label for="name">Branch Name</label>
                                                    <input type="text" placeholder="branch Name" name="name"
                                                    id="branch_name" value="{{ $branch->name }}" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                            <div class="form-group mb-2">
                                                <label>Branch Email</label>
                                                <input type="email" placeholder="email" name="email" id="email"
                                                    value="{{ $branch->email }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mx-0 px-4">
                                        <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                            <div class="form-group mb-2">
                                                <label for="phone">Branch Phone#</label>
                                                    <input type="number"  name="phone_number"
                                                     value="{{ $branch->phone_number }}" class="form-control">
                                            </div>
                                        </div>
                                        {{-- <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                            <div class="form-group mb-2">
                                                <label for="phone">Branch No#</label>
                                                    <input type="number"  name="branch_number"
                                                    value="{{ $branch->branch_number }}" class="form-control">
                                            </div>
                                        </div> --}}
                                    </div>
                                    <div class="row mx-0 px-4">
                                        <div class="col-sm-6 pl-sm-0 pr-sm-2">
                                            <div class="form-group mb-3">
                                                <label>Location</label>

                                                <!-- Autocomplete Input -->
                                                <input type="text" id="location" class="form-control" placeholder="Select location" autocomplete="off" name ="location" value="{{ $branch->location }}">

                                                <!-- Hidden Fields -->
                                                <input type="hidden" name="latitude" id="lat" value="{{ $branch->latitude }}">
                                                <input type="hidden" name="longitude" id="lng" value="{{ $branch->longitude }}">

                                                <!-- Error message -->
                                                <small id="location-error" style="color:red; display:none;">
                                                    Please select a valid location from suggestions
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mx-0 px-4">
                                        <div class="col-sm-6 pl-sm-0 pr-sm-2">
                                            <div class="form-group mb-3">
                                                <label for="phone">Tax</label>
                                                    <input type="text"  name="tax"
                                                    value="{{ $branch->tax }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-sm-6 pl-sm-0 pr-sm-2">
                                            <div class="form-group mb-3">
                                                <label>City label (landing page)</label>
                                                <input type="text" name="city_label" value="{{ $branch->city_label }}" class="form-control" placeholder="Manchester City Centre">
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" name="is_orderable" value="1" class="form-check-input" {{ !empty($branch->is_orderable) ? 'checked' : '' }}>
                                                <label class="form-check-label">Allow ordering from this store</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center row">
                                        <div class="col">
                                            <button type="submit" class="btn btn-success mr-1 btn-bg"
                                                id="submit">Update</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </body>
@endsection

@section('js')
    @if (\Illuminate\Support\Facades\Session::has('message'))
        <script>
            toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
        </script>
    @endif
<script async
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUMK9qFdsbuuuTMiaPHCJok4Rro91yvaE&libraries=places&v=beta&loading=async">
</script>

<script>
window.addEventListener("load", function () {

    const input = document.getElementById("location");
    const latInput = document.getElementById("lat");
    const lngInput = document.getElementById("lng");
    const errorMsg = document.getElementById("location-error");

    let selected = false;

    // Initialize Autocomplete
    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ["geocode"], // addresses only
        // componentRestrictions: { country: "pk" } // optional (Pakistan only)
    });

    // When user selects place
    autocomplete.addListener("place_changed", function () {

        const place = autocomplete.getPlace();

        if (!place.geometry) {
            selected = false;
            latInput.value = "";
            lngInput.value = "";
            errorMsg.style.display = "block";
            return;
        }

        // Set values
        latInput.value = place.geometry.location.lat();
        lngInput.value = place.geometry.location.lng();

        input.value = place.formatted_address;

        selected = true;
        errorMsg.style.display = "none";

        console.log("LAT:", latInput.value);
        console.log("LNG:", lngInput.value);
    });

    // ❌ Prevent manual typing
    input.addEventListener("input", function () {
        selected = false;
        latInput.value = "";
        lngInput.value = "";
    });

    // ❌ Prevent leaving field without selection
    input.addEventListener("blur", function () {
        if (!selected) {
            input.value = "";
            latInput.value = "";
            lngInput.value = "";
            errorMsg.style.display = "block";
        }
    });

});
</script>
@endsection
