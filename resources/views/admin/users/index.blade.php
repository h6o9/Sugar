@extends('admin.layout.app')
@section('title', 'Users')
@section('content')
<div class="main-content" style="min-height: 562px;">
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="col-12">
                                <h4>Users
                                </h4>
                            </div>
                        </div>

                        <div class="card-body table-striped table-bordered table-responsive">
                            <table class="table text-center" id="table_id_events">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Name</th>
                                        <th>Email</th>
										<th>Phone</th>
										<th>Postcode</th>
										<th>Address</th>
										<th>Average Spend (£)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
							@foreach ($users as $user)
							<tr>
								<td>{{ $loop->iteration }}</td>

								<td>{{ $user->name }}</td>
								<td>{{ $user->email }}</td>
								<td>{{ $user->phone }}</td>
								<td>{{ $user->postcode }}</td>
								<td>{{ $user->address }}</td>

								<td>£{{ $averagespend[$user->id] ?? 0 }}</td>

								<td style="display: flex; align-items: center; justify-content: center; column-gap: 8px">
									<form method="POST" action="{{ route('users-delete', $user->id) }}">
										@csrf
										@method('DELETE')

										<button type="submit" class="btn btn-danger btn-flat show_confirm">
											Delete
										</button>
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
    $('#table_id_events').DataTable();

    // Delete Confirmation
    $('.show_confirm').click(function(event) {
        var form = $(this).closest("form");
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
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
@endsection