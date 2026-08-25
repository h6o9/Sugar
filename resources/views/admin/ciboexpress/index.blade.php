@extends('admin.layout.app')
@section('title', 'Website Content')
@section('content')
    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Dessert Wholesale / Private Booking Content</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                <table class="table text-center" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Image</th>
                                            <th>Page</th>
                                            <th>Title</th>
                                            <th>Description</th>	
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($ciboExpressItems as $cibo)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
												<td>
                                                    <img src="{{ asset($cibo->image) }}" alt="" height="50"
                                                        width="50" class="image">
                                                </td>
                                                <td>{{ $cibo->page_key ?? 'legacy' }}</td>
                                                <td>{{ $cibo->title }}</td>
                                                <td>{{ $cibo->description }}</td>
                                                <td>
                                                    <a class="btn btn-info"
                                                        href="{{ route('cibo-express.edit', $cibo->id) }}">Edit</a>
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
        </section>

        <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg scrol" id="mymodal">
            </div>

        </div>
    </div>

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

        $(document).on('click', '.product-data', function() {
            var id = $(this).attr('id');
            alert('id');
            $.ajax({
                type: "GET",
                dataType: "json",
                headers: {
                    'X-CSRF-Token': '{{ csrf_token() }}',
                },
                url: "{{ url('admin/product/show') }}",
                data: {
                    'id': id,

                },
                success: function(response) {
                    $("#mymodal").html(response);

                }
            });
        });
    </script>

@endsection
