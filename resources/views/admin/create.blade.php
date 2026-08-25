@extends('admin.layout.app')
@section('title', 'Dashboard')
@section('content')

    <body>
        <div class="main-content">
            <section class="section">
                <div class="section-body">
                    <a class="btn btn-primary mb-3" href="{{ url()->previous() }}">Back</a>
                    <form  action="{{ route('register-branch') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-12 col-lg-12">
                                <div class="card">
                                    <h4 class="text-center my-4">Add Branch</h4>
                                    <div class="row mx-0 px-4">
                                        <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                            <div class="form-group mb-2">
                                                <label for="name">Branch Name</label>
                                                <input id="name" type="text" placeholder="Enter branch name"
                                                    class="form-control" name="name" tabindex="1" autofocus>
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                            <div class="form-group mb-2">
                                                <label>Branch Email</label>
                                                <input type="email" placeholder="Enter email" name="email"
                                                    id="email" class="form-control">
                                                @error('email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mx-0 px-4">
                                        <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                            <div class="form-group mb-2">
                                                <label for="phone">Branch Phone#</label>
                                                <input id="phone" type="number" placeholder="Enter phone no"
                                                    class="form-control" name="phone_number" tabindex="1" autofocus>
                                                @error('phone_number')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                            <div class="form-group mb-2">
                                                <label for="phone">Branch No#</label>
                                                <input id="branch_number" type="number" placeholder="Enter branch no"
                                                    class="form-control" name="branch_number" tabindex="1" autofocus>
                                                @error('branch_number')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div> --}}
                                    </div>
                                    <div class="row mx-0 px-4">
                                        <div class="col-sm-6 pl-sm-0 pr-sm-2">
                                            <div class="form-group mb-3">
                                                <label for="phone">Location</label>
                                                <input type="text" placeholder="Enter location" class="form-control" name="location" tabindex="1"
                                                    autofocus>
                                                @error('location')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mx-0 px-4">
                                        <div class="col-sm-6 pl-sm-0 pr-sm-2">
                                            <div class="form-group mb-3">
                                                <label for="phone">Tax</label>
                                                <input type="text" placeholder="Enter Tax" class="form-control" name="tax" tabindex="1"
                                                    autofocus>
                                                @error('tax')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center row">
                                        <div class="col">
                                            <button type="submit" class="btn btn-success mr-1 btn-bg"
                                                id="submit">Add</button>
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
@endsection
