@extends('admin.layout.app')
@section('title', 'Edit Bulk Feature Settings')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">

            <a class="btn btn-primary mb-3" href="{{ route('bulk-feature.index') }}">Back</a>

            <form action="{{ route('update-bulk-feature-settings', $data->id) }}" method="POST" id="bulkFeatureForm">
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
                                <option value="" disabled>Select Operation</option>
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
                                <option value="" disabled>Select Method</option>
                                <option value="percentage" {{ old('method', $data->method) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed amount" {{ old('method', $data->method) == 'fixed amount' ? 'selected' : '' }}>Fixed Amount (£)</option>
                            </select>
                            @error('method')
                                <small class="text-danger error-msg">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- AMOUNT --}}
                        <div class="form-group">
                            <label>Amount</label>
                            <div class="input-group input-group-lg">
                                <input type="text"
                                       name="amount"
                                       id="amount"
                                       value="{{ old('amount', $data->amount) }}"
                                       class="form-control input-field"
                                       placeholder="{{ $data->method == 'percentage' ? 'Enter Percentage %' : 'Enter Fixed Amount (£)' }}">
                                <span class="input-group-text" id="amount_symbol"></span>
                            </div>
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

// --------------------
// Dynamic Amount Input + Numeric Only for DB
// --------------------
document.addEventListener('DOMContentLoaded', function() {
    const methodSelect = document.getElementById('method');
    const amountInput = document.getElementById('amount');
    const symbolSpan = document.getElementById('amount_symbol');
    const form = document.getElementById('bulkFeatureForm');

    function setSymbol() {
        let rawValue = amountInput.value.replace(/[£%]/g,''); // remove old symbols
        if(methodSelect.value === 'percentage' && rawValue) {
            amountInput.value = rawValue;
            symbolSpan.textContent = '%';
        } else if(methodSelect.value === 'fixed amount' && rawValue) {
            amountInput.value = rawValue;
            symbolSpan.textContent = '£';
        } else {
            symbolSpan.textContent = '';
        }
    }

    // Initial symbol
    setSymbol();

    // On method change
    methodSelect.addEventListener('change', function() {
        setSymbol();
        amountInput.placeholder = methodSelect.value === 'percentage' ? 'Enter Percentage %' : 'Enter Fixed Amount (£)';
    });

    // Live typing: numeric + decimal only
    amountInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9.]/g,'');
        if((this.value.match(/\./g)||[]).length > 1){
            this.value = this.value.slice(0,-1);
        }
    });

    // Before submit: remove symbols for DB
    form.addEventListener('submit', function() {
        amountInput.value = amountInput.value.replace(/[£%]/g,'');
    });
});
</script>
@endsection