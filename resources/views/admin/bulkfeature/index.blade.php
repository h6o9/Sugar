@extends('admin.layout.app')
@section('title', 'Bulk Feature Settings')
@section('content')
    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Bulk Feature Settings <small class="font-weight-bold text-danger">
								(Use this feature to adjust the prices of all products at once, either by a fixed amount or a percentage. Products that already have individual price settings will retain their adjustment prices and will not be affected by this bulk feature update.)
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
                                            <th>Operation</th>
											<th>Method</th>
											<th>Amount</th>
											<th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($details as $data)
<tr>
    <td>{{ $loop->iteration }}</td>
    
    {{-- Action --}}
    <td>{{ ucfirst($data->action ?? 'Register') }}</td>                                                

    {{-- Method --}}
    <td>
        @if(($data->method ?? '') == 'percentage')
            {{ ucfirst($data->method) }} (%) 
        @elseif(($data->method ?? '') == 'fixed amount')
            {{ ucfirst($data->method) }} (£)
        @else
            {{ ucfirst($data->method ?? '-') }}
        @endif
    </td>

    {{-- Amount --}}
    <td>
        @if(($data->method ?? '') == 'percentage')
            {{ $data->amount ?? 0 }}%
        @elseif(($data->method ?? '') == 'fixed amount')
            £{{ $data->amount ?? 0 }}
        @else
            {{ $data->amount ?? 0 }}
        @endif
    </td>

    {{-- Edit Button --}}
    <td style="display: flex; align-items: center; justify-content: center; column-gap: 8px">
        <a class="btn btn-info" href="{{ route('edit-bulk-feature-settings', $data->id) }}">Edit</a>
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
