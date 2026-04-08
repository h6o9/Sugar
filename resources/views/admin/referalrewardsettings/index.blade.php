@extends('admin.layout.app')
@section('title', 'Referal Link Reward Settings')
@section('content')
    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Referal Link Reward Settings <small class="font-weight-bold text-danger">
								(Users earn loyalty points for every successful action completed through their referral links. For example, if a user shares a link with 10 people and 3 of them register on the app after installing it, the user will earn loyalty points only for those 3 registrations. If each registration is worth 10 loyalty points, the user will receive 30 loyalty points.)
								</small>
								</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                {{-- <a class="btn btn-success mb-3" href="{{ route('create-branch') }}">Add Branch</a> --}}
                                <table class="table text-center" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Loyalty Points</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data as $data)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $data->reward_points }}</td>                                                
												<td
                                                    style="display: flex;align-items: center;justify-content: center;column-gap: 8px">
                                                    <a class="btn btn-info"
                                                        href="{{ route('edit-referral-reward-settings', $data->id) }}">Edit</a>
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


@endsection
