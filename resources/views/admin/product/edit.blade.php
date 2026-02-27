@extends('admin.layout.app')
@section('title', 'Dashboard')

@section('content')
<body>
<div class="main-content">
<section class="section">
<div class="section-body">

<a class="btn btn-primary mb-3" href="{{ url()->previous() }}">Back</a>

<div class="">
<div class="row">
<div class="col-12 col-md-12 col-lg-12">
<div class="card">
<h4 class="text-center my-4">Update Product</h4>

<form id="update_product_form" action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="container">
<div class="row">

{{-- Name --}}
<div class="col-sm-6">
<div class="form-group mb-2">
<label>Name</label>
<input type="text" name="name" class="form-control" placeholder="Enter Product Name" value="{{ $product->name }}">
@error('name')
<div class="text-danger">{{ $message }}</div>
@enderror
</div>
</div>

{{-- Menu --}}
<div class="col-sm-6">
<div class="form-group mb-2">
<label>Menus</label>
<select class="form-control" name="menu_id">
<option disabled>Select Menus</option>
@foreach ($menus as $menu)
<option value="{{ $menu->id }}" {{ $product->menu_id == $menu->id ? 'selected' : '' }}>{{ $menu->name }}</option>
@endforeach
</select>
@error('menu_id')
<div class="text-danger">{{ $message }}</div>
@enderror
</div>
</div>

</div>
</div>

{{-- Variants / Price Adjustment --}}
<div class="container">
<div class="row">

<div class="col-sm-12">

@if($product->variants->isNotEmpty())
{{-- VARIANTS TABLE --}}
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Size</th>
            <th>Base Price (£)</th>
            <th>Price Adjustment (£)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="variantRows">
        @foreach($product->variants as $variant)
        <tr>
            <td>
                <input type="hidden" name="variant_ids[]" value="{{ $variant->id }}">
                <input type="text" class="form-control sizeInput" name="sizes[]" value="{{ $variant->size }}">
            </td>
            <td>
                <input type="text" class="form-control basePriceInput" name="base_prices[]" value="{{ $variant->original_price }}">
            </td>
            <td>
                <input type="text" class="form-control priceAdjustmentInput" name="prices[]" value="{{ $variant->price }}" disabled>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm removeVariantBtn">Delete</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<button type="button" class="btn btn-primary mb-3" id="addSizeBtn">Add Variant</button>

@else
{{-- SINGLE PRODUCT --}}
<div class="row mb-2">
<div class="col-md-6">
<label>Base Price (£)</label>
<input type="text" class="form-control basePriceInput" name="original_price" value="{{ $product->original_price }}">
</div>
<div class="col-md-6">
<label>Price Adjustment (£)</label>
<input type="text" class="form-control priceAdjustmentInput" name="price" value="{{ $product->price }}" disabled>
</div>
</div>
@endif

</div>

{{-- Featured Section --}}
<div class="form-group mb-3" style="margin-left: 19px;">
<label>Featured Settings (Optional)</label>
<div class="row g-3 align-items-center">

<div class="col-md-4 col-sm-6">
<select name="featured_action" id="featured_action" class="form-control form-control-lg">
<option value="" disabled {{ !$product->featured_action ? 'selected' : '' }}>Operation</option>
<option value="increase" {{ $product->featured_action=='increase' ? 'selected' : '' }}>Increase</option>
<option value="decrease" {{ $product->featured_action=='decrease' ? 'selected' : '' }}>Decrease</option>
</select>
</div>

<div class="col-md-4 col-sm-6">
<select name="featured_method" id="featured_method" class="form-control form-control-lg">
<option value="" disabled>Select Method</option>
<option value="percentage" {{ $product->featured_method=='percentage' ? 'selected' : '' }}>Percentage (%)</option>
<option value="fixed amount" {{ $product->featured_method=='fixed amount' ? 'selected' : '' }}>Fixed Amount (£)</option>
</select>
</div>

<div class="col-md-4 col-sm-12">
<div class="input-group input-group-lg">
<input type="text" name="featured_amount" id="featured_amount" class="form-control"
       placeholder="Enter Amount" value="{{ $product->featured_amount }}">
<span class="input-group-text" id="featured_symbol"></span>
</div>
</div>

</div>
@error('featured_amount')
<small class="text-danger">{{ $message }}</small>
@enderror
</div>

</div>
</div>

{{-- Right column --}}
<div class="container mb-3">
<div class="row">
<div class="col-sm-6">
<div class="form-group mb-2">
<label>Image</label>
<input type="file" name="image" class="form-control">
@error('image')
<div class="text-danger">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-sm-6">
<div class="form-group mb-2">
<label>Toppings Category (Optional)</label>
<select class="form-control selectric" name="category_id[]" multiple>
@foreach ($categories as $category)
<option value="{{ $category->id }}" @if(in_array($category->id, $categoryIds)) selected @endif>{{ $category->name }}</option>
@endforeach
</select>
</div>
</div>
</div>
</div>

<div class="card-footer text-center">
<button type="submit" class="btn btn-success">Update Product</button>
</div>

</form>
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

    // Add new variant row
    $('#addSizeBtn').click(function() {
        $('#variantRows').append(`
            <tr>
                <td><input type="hidden" name="variant_ids[]" value=""><input type="text" class="form-control sizeInput" name="sizes[]" placeholder="Enter Size"></td>
                <td><input type="text" class="form-control basePriceInput" name="base_prices[]" value="0"></td>
                <td><input type="text" class="form-control priceAdjustmentInput" name="prices[]" value="0" disabled></td>
                <td><button type="button" class="btn btn-danger btn-sm removeVariantBtn">Delete</button></td>
            </tr>
        `);
    });

    // Delete variant row
    $(document).on('click', '.removeVariantBtn', function() {
        $(this).closest('tr').remove();
    });

    // Featured input
    const method  = $('#featured_method');
    const amount  = $('#featured_amount');
    const symbol  = $('#featured_symbol');

    function setSymbol() {
        if(method.val() === 'percentage'){ symbol.text('%'); } 
        else if(method.val() === 'fixed amount'){ symbol.text('£'); } 
        else { symbol.text(''); }
    }

    setSymbol();

    method.on('change', function() {
        setSymbol();
        if(method.val() === 'percentage'){ amount.attr('placeholder','Enter Percentage'); } 
        else if(method.val() === 'fixed amount'){ amount.attr('placeholder','Enter Fixed Amount'); } 
        else { amount.attr('placeholder','Enter Amount'); }
    });

    // Only numbers + decimal
    amount.on('input', function() {
        let val = $(this).val().replace(/[^0-9.]/g,'');
        if((val.match(/\./g)||[]).length > 1){ val = val.slice(0,-1); }
        $(this).val(val);
    });

    $('#update_product_form').on('submit', function() {
        amount.val(amount.val().replace(/[£%]/g,''));
    });

});
</script>
@endsection