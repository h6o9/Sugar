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
                                <h4>Assign Categories To Toppings</h4>
                            </div>
                            <form action="{{ route('toppingAssign') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Toppings Name</label>
                                        <select class="form-control topping-dropdown" name="topping[]" multiple>
                                            <option value="">Select Topping</option>
                                            @foreach ($toppings as $topping)
                                            <option value="{{ $topping->id }}">{{ $topping->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('topping')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                        <input type="hidden" name="category_id" value="{{ $categor_id }}">
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
                                <h4>Assign Toppings<small class="font-weight-bold"></small></h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered text-center" id="table_id_events">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Sr.</th>
                                                <th class="text-center">Categories Name</th>
                                                <th class="text-center">Toppings Name</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($categoriesToppings as $categoriesTopping)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{$categoriesTopping->category->name}}</td>
                                                <td>{{$categoriesTopping->topping->name}}</td>
                                                <td
                                                    style="display: flex;align-items: center;justify-content: center;column-gap: 8px">
                                                    <form method="post"
                                                        action="{{ route('toppingDestroy', $categoriesTopping->id) }}">
                                                        @csrf
                                                        <input name="_method" type="hidden" value="DELETE">
                                                        <button type="submit"
                                                            class="btn btn-danger btn-flat show_confirm"
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
<script>
    $(document).ready(function() {
            $('.topping-dropdown').selectric();
        });
</script>
@endsection
