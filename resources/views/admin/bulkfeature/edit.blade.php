@extends('admin.layout.app')
@section('title', 'Edit Bulk Feature Settings')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">

            <a class="btn btn-primary mb-3" href="{{ route('bulk-feature.index') }}">Back</a>

            <form action="{{ route('update-bulk-feature-settings', $data->id) }}" method="POST">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center w-100">Edit Bulk Feature Settings</h4>
                    </div>

                    <div class="card-body">

                        {{-- ACTION --}}
                        <div class="form-group">
                            <label>Operation</label>
                            <select name="action" id="action" class="form-control input-field" required>
                                <option value="">Select Action</option>
                                <option value="increase" {{ old('action',$data->action)=='increase'?'selected':'' }}>Increase</option>
                                <option value="decrease" {{ old('action',$data->action)=='decrease'?'selected':'' }}>Decrease</option>
                            </select>

                            @error('action')
                                <small class="text-danger error-msg">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- METHOD --}}
                        <div class="form-group">
                            <label>Method</label>
                            <select name="method" id="method" class="form-control input-field" required>
                                <option value="">Select Method</option>
                                <option value="percentage" {{ old('method',$data->method)=='percentage'?'selected':'' }}>Percentage</option>
                                <option value="fixed amount" {{ old('method',$data->method)=='fixed amount'?'selected':'' }}>Fixed Amount</option>
                            </select>

                            @error('method')
                                <small class="text-danger error-msg">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- AMOUNT --}}
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number"
                                   step="0.01"
                                   name="amount"
                                   id="amount"
                                   value="{{ old('amount',$data->amount) }}"
                                   class="form-control input-field"
                                   placeholder="Enter Amount">

                            @error('amount')
                                <small class="text-danger error-msg">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>

                    <div class="card-footer text-center">
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>

                </div>
            </form>

        </div>
    </section>
</div>
@endsection

@section('js')

@if(session('message'))
<script>
    toastr.success("{{ session('message') }}");
</script>
@endif

<script>
document.querySelectorAll('.input-field').forEach(function(input){
    // Hide error on focus or typing
    input.addEventListener('focus', hideError);
    input.addEventListener('input', hideError);

    function hideError() {
        let group = this.closest('.form-group');
        if(group){
            let error = group.querySelector('.error-msg');
            if(error){
                error.style.display = 'none';
            }
        }
    }
});
</script>

@endsection