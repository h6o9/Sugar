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
                                <h4>Users</h4>
                            </div>
                        </div>
                        <div class="card-body table-striped table-bordered table-responsive">
                            <table class="table text-center" id="table_id_users">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone Number</th>
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
                                        <td>
                                            @if(!empty($user->email))
                                                <a href="mailto:{{ $user->email }}" style="text-decoration: underline; color: #007bff !important;">
                                                    {{ $user->email }}
                                                </a>
                                            @else
                                                <span class="text-muted">No Email</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($user->phone))
                                                {{ $user->phone }}
                                            @else
                                                <span class="text-muted">No Phone</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($user->postcode))
                                                {{ $user->postcode }}
                                            @else
                                                <span class="text-muted">No Postcode</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($user->address))
                                                {{ $user->address }}
                                            @else
                                                <span class="text-muted">No Address</span>
                                            @endif
                                        </td>
                                        <td>£{{ number_format($user->average_spend, 2) }}</td>
                                        <td>
                                            <form id="delete-form-{{ $user->id }}" action="{{ route('users-delete', $user->id) }}" method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <button class="show_confirm btn btn-danger btn-sm" data-form="delete-form-{{ $user->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
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
</div>
@endsection

@section('js')
@if (\Illuminate\Support\Facades\Session::has('message'))
<script>
    toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
</script>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#table_id_users')) {
        $('#table_id_users').DataTable().destroy();
    }
    $('#table_id_users').DataTable();

    // SweetAlert Delete Confirmation
    $(document).on('click', '.show_confirm', function(event) {
        event.preventDefault();
        var formId = $(this).data("form");
        var form = document.getElementById(formId);

        swal({
            title: "Are you sure you want to delete this user?",
            text: "If you delete this, it will be gone forever.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                form.submit();
            }
        });
    });
});
</script>
@endsection