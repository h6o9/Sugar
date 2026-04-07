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

                                                <!-- New Google Element -->
                                                <gmp-place-autocomplete id="place-autocomplete"></gmp-place-autocomplete>

                                                <!-- Hidden fields -->
                                                <input type="text" name="location" id="location">
                                                <input type="text" name="lat" id="lat">
                                                <input type="text" name="lng" id="lng">
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
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUMK9qFdsbuuuTMiaPHCJok4Rro91yvaE&libraries=places&v=beta"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const el = document.getElementById('place-autocomplete');

    if (!el) {
        console.log('Element not found');
        return;
    }

    el.addEventListener('gmp-placechange', async () => {

        console.log('EVENT TRIGGERED');

        const place = el.value; // ✅ IMPORTANT FIX

        if (!place) {
            console.log('No place selected');
            return;
        }

        await place.fetchFields({
            fields: ['formattedAddress', 'location']
        });

        console.log('PLACE DATA:', place);

        if (!place.location) {
            alert('No location data');
            return;
        }

        document.getElementById('location').value = place.formattedAddress;
        document.getElementById('lat').value = place.location.lat();
        document.getElementById('lng').value = place.location.lng();

        console.log('SUCCESS SET');
    });

});
</script>
@endsection
