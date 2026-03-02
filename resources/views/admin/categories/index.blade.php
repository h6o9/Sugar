@extends('admin.layout.app')
@section('title', 'Dashboard')
@section('content')

    <body>
        <div class="main-content">
            <section class="section">
                <div class="section-body">
                    <div class="row">
                        <div class="col-12 col-md-4 col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Add Topping Category</h4>
                                </div>
                                <form action="{{ route('category.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Category Name</label>
                                            <input type="text" class="form-control" placeholder="Enter Category Name" name="name">
                                            @error('name')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="card-footer text-right">
                                        <button class="btn btn-primary mr-1" type="submit">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-12 col-md-8 col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Categories<small class="font-weight-bold"></small></h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered text-center" id="table_id_events">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Sr.</th>
                                                    <th class="text-center">Name</th>
                                                    <th class="text-center">Topping</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($categories as $category)
                                                <tr>

                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{$category->name}}</td>
                                                    <td>
                                                        <a class="btn btn-success"
                                                            href="{{ route('category-toppings',$category->id) }}">Assign</a>
                                                    </td>
                                                    <td
                                                        style="display: flex;align-items: center;justify-content: center;column-gap: 8px">
                                                        <a class="btn btn-info"
                                                            href="{{ route('category.edit', $category->id) }}">Edit</a>
                                                            <form method="post"
                                                            action="{{ route('category.destroy', $category->id) }}">
                                                            @csrf
                                                            <input name="_method" type="hidden" value="DELETE">
                                                            <button type="submit" class="btn btn-danger btn-flat show_confirm"
                                                                data-toggle="tooltip">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
    <script>
        $(document).ready(function() {
            $('#table_id_events').DataTable()

        })
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script type="text/javascript">
        $('.show_confirm').click(function(event) {
            var form = $(this).closest("form");
            var name = $(this).data("name");
            event.preventDefault();
            swal({
                    title: `Are you sure you want to delete this record?`,
                    text: "If you delete this, it will be gone forever.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                    }
                });
        });
    </script>
@endsection

