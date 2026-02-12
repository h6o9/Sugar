@extends('admin.layout.app')
@section('title', 'index')
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

                                <table class="table text-center" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>User Name</th>
                                            <th>Email</th>
											<th>Average Spent</th>
                                            {{-- <th>Loyalty Points</th> --}}
                                            {{-- <th>Rewards</th> --}}
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
                                                        <a href="{{ 'mailto:' . $user->email }}" 
                                                        class="text-primary" 
                                                        style="text-decoration: underline; word-break: break-all; color: #007bff !important;">
                                                            {{ $user->email }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">No Email</span>
                                                    @endif
                                                </td>
												<td>£{{ $user->average_spend }}</td>
                                                {{-- <td>{{ $user->point ?? '0' }}</td> --}}
                                                {{-- <td><a class="btn btn-info" href="{{ route('rewards', $user->id) }}">Rewards</a></td> --}}
                                                <td>
                                                    <form id="delete-form-{{ $user->id }}" 
                                                        action="{{ route('users-delete', $user->id) }}" 
                                                        method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>

                                                    <button class="show_confirm btn btn-danger" 
                                                            data-form="delete-form-{{ $user->id }}" 
                                                            type="button">
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
        // $(document).ready(function() {
        //     $('#table_id_events').DataTable()

        // })
        $(document).ready(function() {

            // ===== DataTable Initialization =====
            if ($.fn.DataTable.isDataTable('#table_id_events')) {
                $('#table_id_events').DataTable().destroy();
            }
            $('#table_id_events').DataTable();

            // ===== SweetAlert2 Delete Confirmation =====
            $(document).on('click', '.show_confirm', function(event) {
                event.preventDefault();
                var formId = $(this).data("form");
                var form = document.getElementById(formId);

                swal({
                    title: "Are you sure you want to delete this record?",
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>


@endsection
