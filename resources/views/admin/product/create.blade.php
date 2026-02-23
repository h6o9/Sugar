@extends('admin.layout.app')
@section('title', 'Dashboard')

@section('content')
<body>
<div class="main-content">
<section class="section">
<div class="section-body">

<a class="btn btn-primary mb-3" href="{{ url()->previous() }}">Back</a>

<form id="add_student" action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="card">
<h4 class="text-center my-4">Add Product</h4>

<div class="container">
<div class="row">

{{-- NAME --}}
<div class="col-sm-6">
<div class="form-group mb-2">
<label>Name</label>
<input type="text" name="name" class="form-control" placeholder="Enter Product Name">
@error('name')
<div class="text-danger">{{ $message }}</div>
@enderror
</div>
</div>

{{-- MENU --}}
<div class="col-sm-6">
<div class="form-group mb-2">
<label>Menus</label>
<select class="form-control" name="menu_id">
<option disabled selected>Select Menus</option>
@foreach ($menus as $menu)
<option value="{{ $menu->id }}">{{ $menu->name }}</option>
@endforeach
</select>
</div>
</div>

</div>
</div>

{{-- PRICE + FEATURE --}}
<div class="container">
<div class="row">

<div class="col-sm-6">

{{-- PRICE --}}
<div class="form-group mb-2">
<label>Price</label>
<input type="text" name="price" id="price" class="form-control" placeholder="Enter Product Price">
</div>

{{-- VARIANTS --}}
<div class="form-group">
<button type="button" class="btn btn-primary mb-3" id="addSizeBtn">Add Variants</button>
<div id="sizeInputs"></div>
</div>

{{-- FEATURED SETTINGS --}}
<div class="form-group mb-3">
    <label>Featured Settings (Optional)</label>
    <div class="row g-3 align-items-center">

        {{-- Action --}}
        <div class="col-md-4 col-sm-6">
            <select name="featured_action" id="featured_action" class="form-control form-control-lg">
                <option disabled selected>Select Operation</option>
                <option value="increase">Increase</option>
                <option value="decrease">Decrease</option>
            </select>
        </div>

        {{-- Method --}}
        <div class="col-md-4 col-sm-6">
            <select name="featured_method" id="featured_method" class="form-control form-control-lg">
                <option disabled selected>Select Method</option>
                <option value="percentage">Percentage (%)</option>
                <option value="fixed amount">Fixed Amount (£)</option>
            </select>
        </div>

        {{-- Amount --}}
        <div class="col-md-4 col-sm-12">
            <div class="input-group input-group-lg">
                <input type="text"
                    name="featured_amount"
                    id="featured_amount"
                    class="form-control"
                    placeholder="Enter Amount">
                <span class="input-group-text d-none" id="featured_symbol">%</span>
            </div>
        </div>

    </div>

    @error('featured_amount')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>
</div>

{{-- IMAGE --}}
<div class="col-sm-6">
<div class="form-group mb-2">
<label>Image</label>
<input type="file" name="image" class="form-control">
</div>
</div>

{{-- TOPPINGS --}}
<div class="col-sm-6">
<div class="form-group mb-2">
<label>Toppings Category (Optional)</label>
<select class="form-control selectric" name="category_id[]" multiple>
@foreach ($categories as $categorie)
<option value="{{ $categorie->id }}">{{ $categorie->name }}</option>
@endforeach
</select>
</div>
</div>

</div>
</div>

<div class="card-footer text-center">
<button type="submit" class="btn btn-success">Add Product</button>
</div>

</div>
</form>

</div>
</section>
</div>
</body>
@endsection


{{-- ================= JS ================= --}}
@section('js')

<script>
$(document).ready(function () {

    /* ================= VARIANTS ================= */

    function checkPriceInput() {
        $('#addSizeBtn').prop('disabled', $('#price').val().trim() !== '');
    }

    $('#price').keyup(checkPriceInput);

    $('#addSizeBtn').click(function () {

        $('#price').prop('disabled', true);

        $('#sizeInputs').append(`
            <div class="row justify-content-center mb-2">
                <input type="text" class="form-control col-5"
                       name="sizes[]" placeholder="Enter Size">

                <input type="text" class="form-control col-5 ml-2"
                       name="prices[]" placeholder="Enter Price">

                <button type="button"
                        class="btn btn-danger ml-2 removeBtn">
                        🗑
                </button>
            </div>
        `);
    });

    $(document).on('click', '.removeBtn', function () {
        $(this).parent().remove();

        if ($('#sizeInputs').children().length === 0) {
            $('#price').prop('disabled', false);
        }

        checkPriceInput();
    });


    /* ================= FEATURED INPUT ================= */

    const method  = $('#featured_method');
    const amount  = $('#featured_amount');
    const symbol  = $('#featured_symbol');

    method.on('change', function () {

        symbol.removeClass('d-none');

        if ($(this).val() === 'percentage') {
            symbol.text('%');
            amount.attr('placeholder','Enter Percentage');
        } else {
            symbol.text('£');
            amount.attr('placeholder','Enter Fixed Amount');
        }
    });

    // Allow only numbers + decimal
    amount.on('input', function () {

        let value = $(this).val().replace(/[^0-9.]/g,'');

        if ((value.match(/\./g)||[]).length > 1) {
            value = value.slice(0,-1);
        }

        $(this).val(value);
    });

    // Clean value before submit
    $('#add_student').on('submit', function () {
        amount.val(amount.val().replace(/[£%]/g,''));
    });

});
</script>

@endsection