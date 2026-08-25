@extends('admin.layout.app')
@section('title', 'Dashboard')
@section('content')

    <body>
        <div class="main-content">
            <section class="section">
                <div class="section-body">
                    <div class="row">
                        <div class="col-12 ">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Update Menu</h4>
                                </div>
                                <form action="{{ route('menu.update', $menu->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Category Name</label>
                                            <input type="text" class="form-control" placeholder="Category" name="name"
                                                value="{{ $menu->name }}">
                                            @error('name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label>Type</label>
                                            <select name="type" class="form-control">
                                                <option value="food" {{ ($menu->type ?? '')=='food' ? 'selected' : '' }}>Food</option>
                                                <option value="special" {{ ($menu->type ?? '')=='special' ? 'selected' : '' }}>Pappi Special</option>
                                                <option value="wholesale" {{ ($menu->type ?? '')=='wholesale' ? 'selected' : '' }}>Wholesale</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Icon class</label>
                                            <input type="text" class="form-control" name="icon" value="{{ $menu->icon }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Sort order</label>
                                            <input type="number" class="form-control" name="sort_order" value="{{ $menu->sort_order }}">
                                        </div>
                                    </div>
                                    <div class="card-footer text-right">
                                        <button class="btn btn-primary mr-1" type="submit" id="submit">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
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
