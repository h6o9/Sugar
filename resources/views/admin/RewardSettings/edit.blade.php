@extends('admin.layout.app')
@section('title', 'Edit Reward Settings')
@section('content')

    <body>
        <div class="main-content">
            <section class="section">
                <div class="section-body">
                    <a class="btn btn-primary mb-3" href="{{ url()->previous() }}">Back</a>
                    <form  action="{{ route('update-reward-settings', $data->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-12 col-lg-12">
                                <div class="card">
                                    <h4 class="text-center my-4">Edit Reward Settings</h4>
                                    <div class="row mx-0 px-4">
                                        <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                            <div class="form-group mb-2">
                                                <label for="name">Points</label>
                                                    <input type="text" placeholder="Points" name="points"
                                                    id="points" value="{{ $data->points }}" class="form-control" readonly>
                                            </div>
                                        </div>
										<div class="col-sm-6 pl-sm-0 pr-sm-3">
                                            <div class="form-group mb-2">
                                                <label for="name">Price</label>
                                                   <div class="input-group"><input 
														type="text" 
														placeholder="Price" 
														name="price"
														id="price" 
														value="{{ $data->price }}" 
														class="form-control">
														<span class="input-group-text" style = "background-color: #c9bebeff;">£</span>
												</div>
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
@endsection
