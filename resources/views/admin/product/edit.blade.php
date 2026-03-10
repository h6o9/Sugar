@extends('admin.layout.app')
@section('title', 'Dashboard')

@section('content')
<style>
	.select2-container--default .select2-selection--single .select2-selection__rendered {
		display: flex;
	}
	.select2-container--default .select2-selection--single .select2-selection__clear {
		height: 44px;
	}
</style>

<body>
<div class="main-content">

<section class="section">
<div class="section-body">

<a class="btn btn-primary mb-3" href="{{ url()->previous() }}">Back</a>

<div class="row">
<div class="col-12">
<div class="card">
<h4 class="text-center my-4">Update Product</h4>

<form id="update_product_form" action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="container">

{{-- Name & Menu --}}
<div class="row mb-3">
    <div class="col-sm-6">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter Product Name" value="{{ $product->name }}">
            @error('name')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label>Menus</label>
            <select class="form-control" name="menu_id">
                <option disabled>Select Menus</option>
                @foreach ($menus as $menu)
                    <option value="{{ $menu->id }}" {{ $product->menu_id == $menu->id ? 'selected' : '' }}>{{ $menu->name }}</option>
                @endforeach
            </select>
            @error('menu_id')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

{{-- Variants Table --}}
<div class="row mb-3">
<div class="col-sm-12">
<table class="table table-bordered" id="variantTable" style="{{ $product->variants->isEmpty() ? 'display:none;' : '' }}">
    <thead id="variantHeader">
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


{{-- Single Product Price --}}
<div class="row mb-2 singlePriceRow" style="{{ $product->variants->isEmpty() ? '' : 'display:none;' }}">
    <div class="col-md-6">
        <label>Base Price (£)</label>
        <input type="text" class="form-control basePriceInput" name="original_price" value="{{ $product->original_price }}">
    </div>
    <div class="col-md-6">
        <label>Price Adjustment (£)</label>
        <input type="text" class="form-control adjustmentPriceSingle" name="price" value="{{ $product->price }}" disabled>
    </div>
</div>
{{-- Add Variant Buttons --}}
<button type="button" class="btn btn-primary mb-3" id="addVariantTop" {{ $product->variants->isEmpty() ? 'style=display:none;' : '' }}>Add Variant</button>
<button type="button" class="btn btn-primary mb-3" id="addVariantBottom" style="{{ $product->variants->isEmpty() ? '' : 'display:none;' }}">Add Variant</button>

</div>
</div>
{{-- Complementary Product --}}
<div class="row mb-3">
	<div class="col-sm-6">
		<div class="form-group mb-2">
			<label>Complementary Product (Optional)</label>
			<select class="form-control select2-product" name="complementary_product_id" style="width: 100%;">
				<option value="">Select Complementary Product</option>
				@if(isset($complementaryProductId) && $complementaryProductId)
					@php
						$compProduct = \App\Models\Product::find($complementaryProductId);
					@endphp
					@if($compProduct)
						<option value="{{ $compProduct->id }}" selected>{{ $compProduct->name }}</option>
					@endif
				@endif
			</select>
		</div>
	</div>
</div>

{{-- Featured Section --}}
<div class="form-group mb-3">
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
            <input type="text" name="featured_amount" id="featured_amount" class="form-control" placeholder="Enter Amount" value="{{ $product->featured_amount }}">
            <span class="input-group-text" id="featured_symbol"></span>
        </div>
    </div>
</div>
@error('featured_amount')<small class="text-danger">{{ $message }}</small>@enderror
</div>

{{-- Image & Category --}}
<div class="row mb-3">
    <div class="col-sm-6">
        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" class="form-control">
            @error('image')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label>Toppings Category (Optional)</label>
            <select class="form-control selectric" name="category_id[]" multiple>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @if(in_array($category->id, $categoryIds)) selected @endif>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="card-footer text-center">
    <button type="submit" class="btn btn-success">Update Product</button>
</div>

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
$('.select2-product').select2({
    placeholder: 'Search Complement Product...',
    allowClear: true,
    minimumInputLength: 0,

    ajax: {
        url: '{{ route("products.search") }}',
        type: 'GET',
        dataType: 'json',
        delay: 250,

        data: function (params) {
            return {
                search: params.term || '', 
                page: params.page || 1
            };
        },

        processResults: function (data, params) {

            params.page = params.page || 1;

            return {
                results: data.results,
                pagination: {
                    more: data.more
                }
            };
        },

        cache: true
    }
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
        symbol.removeClass('d-none');
        if ($(this).val() === 'percentage') { symbol.text('%'); amount.attr('placeholder','Enter Percentage'); } 
        else { symbol.text('£'); amount.attr('placeholder','Enter Fixed Amount'); }
    });

    amount.on('input', function() {
        let val = $(this).val().replace(/[^0-9.]/g,'');
        if((val.match(/\./g)||[]).length > 1){ val = val.slice(0,-1); }
        $(this).val(val);
    });

    $('#update_product_form').on('submit', function() {
        amount.val(amount.val().replace(/[£%]/g,''));
    });

    // Variants logic
    const variantTable = $('#variantTable');
    const variantHeader = $('#variantHeader');
    const variantRows = $('#variantRows');
    const singlePriceRow = $('.singlePriceRow');
    const addVariantTop = $('#addVariantTop');
    const addVariantBottom = $('#addVariantBottom');

    function toggleVariantsDisplay() {
        if(variantRows.children().length > 0){
            variantTable.show();
            variantHeader.show();
            singlePriceRow.hide();
            addVariantTop.show();
            addVariantBottom.hide();
        } else {
            variantTable.hide();
            variantHeader.hide();
            singlePriceRow.show();
            addVariantTop.hide();
            addVariantBottom.show();
        }
    }

    toggleVariantsDisplay();

    function addVariantRow() {
        variantRows.append(`
            <tr>
                <td><input type="hidden" name="variant_ids[]" value=""><input type="text" class="form-control sizeInput" name="sizes[]" placeholder="Enter Size"></td>
                <td><input type="text" class="form-control basePriceInput" name="base_prices[]" value="0"></td>
                <td><input type="text" class="form-control priceAdjustmentInput" name="prices[]" value="0" disabled></td>
                <td><button type="button" class="btn btn-danger btn-sm removeVariantBtn">Delete</button></td>
            </tr>
        `);
        toggleVariantsDisplay();
    }

    addVariantTop.click(addVariantRow);
    addVariantBottom.click(addVariantRow);

    $(document).on('click', '.removeVariantBtn', function() {
        $(this).closest('tr').remove();
        toggleVariantsDisplay();
    });

});
</script>
@endsection