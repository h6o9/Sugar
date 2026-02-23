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

{{-- Price & Variants --}}
<div class="container">
<div class="row">

<div class="col-sm-6">

{{-- Price --}}
<div class="form-group mb-2">
<label>Price</label>
<input type="text" name="price" id="price" class="form-control" placeholder="Enter Product Price" value="{{ $product->price }}">
@error('price')
<div class="text-danger">{{ $message }}</div>
@enderror
</div>

{{-- Variants --}}
<div class="form-group">
<button type="button" class="btn btn-primary mb-3" id="addSizeBtn">Add Variants</button>
<div id="sizeInputs">
@foreach ($product->variants as $variant)
<div class="row justify-content-center mb-2">
<input type="text" class="form-control col-5" name="sizes[]" placeholder="Enter Size" value="{{ $variant->size }}">
<input type="text" class="form-control col-5 ml-2" name="prices[]" placeholder="Enter Price" value="{{ $variant->price }}">
<button type="button" class="btn btn-danger ml-2 removeBtn"><i class="fas fa-trash-alt"></i></button>
</div>
@endforeach
</div>
</div>

{{-- Featured Section --}}
<div class="form-group mb-3">
    <label>Featured Settings (Optional)</label>
    <div class="row g-3 align-items-center">

        {{-- Action --}}
        <div class="col-md-4 col-sm-6">
            <select name="featured_action" id="featured_action" class="form-control form-control-lg">
                <option value="" disabled {{ !$product->featured_action ? 'selected' : '' }}>Operation</option>
                <option value="increase" {{ $product->featured_action=='increase' ? 'selected' : '' }}>Increase</option>
                <option value="decrease" {{ $product->featured_action=='decrease' ? 'selected' : '' }}>Decrease</option>
            </select>
        </div>

        {{-- Method --}}
        <div class="col-md-4 col-sm-6">
            <select name="featured_method" id="featured_method" class="form-control form-control-lg">
                <option value="" disabled>Select Method</option>
                <option value="percentage" {{ $product->featured_method=='percentage' ? 'selected' : '' }}>Percentage (%)</option>
                <option value="fixed amount" {{ $product->featured_method=='fixed amount' ? 'selected' : '' }}>Fixed Amount (£)</option>
            </select>
        </div>

        {{-- Amount --}}
        <div class="col-md-4 col-sm-12">
            <div class="input-group input-group-lg">
                <input type="text" name="featured_amount" id="featured_amount" class="form-control"
                    placeholder="Enter Amount" value="{{ $product->featured_amount }}">
                <span class="input-group-text" id="featured_symbol"></span>
            </div>
        </div>

        {{-- Rule --}}
<div class="col-12 mt-2">
    <input type="text" name="rule" id="rule" class="form-control form-control-lg" readonly
        value="{{ ucfirst($product->rule == 'Priority' ? 'Individual' : $product->rule) }}">
</div>

    </div>
    @error('featured_amount')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>
</div> {{-- col-sm-6 end --}}

{{-- Right column --}}
<div class="col-sm-6">
<div class="form-group mb-2">
<label>Image</label>
<input type="file" name="image" class="form-control">
@error('image')
<div class="text-danger">{{ $message }}</div>
@enderror
</div>

{{-- Toppings --}}
<div class="form-group mb-2">
<label>Toppings Category (Optional)</label>
<select class="form-control selectric" name="category_id[]" multiple>
@foreach ($categories as $category)
<option value="{{ $category->id }}" @if(in_array($category->id, $categoryIds)) selected @endif>{{ $category->name }}</option>
@endforeach
</select>
</div>

</div> {{-- col-sm-6 end --}}

</div> {{-- row end --}}
</div> {{-- container end --}}

{{-- Submit --}}
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

{{-- ================= JS ================= --}}
@section('js')
@if (\Illuminate\Support\Facades\Session::has('message'))
<script>
toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
</script>
@endif

<script>
$(document).ready(function() {

    // VARIANTS
    function checkPriceInput() {
        $('#addSizeBtn').prop('disabled', $('#price').val().trim() !== '' && $('#sizeInputs').children().length === 0);
        $('#price').prop('disabled', $('#sizeInputs').children().length > 0);
    }

    $('#price').keyup(checkPriceInput);

    $('#addSizeBtn').click(function() {
        $('#sizeInputs').append('<div class="row justify-content-center mb-2"><input type="text" class="form-control col-5" name="sizes[]" placeholder="Enter Size"><input type="text" class="form-control col-5 ml-2" name="prices[]" placeholder="Enter Price"><button type="button" class="btn btn-danger ml-2 removeBtn"><i class="fas fa-trash-alt"></i></button></div>');
        checkPriceInput();
    });

    $(document).on('click', '.removeBtn', function() {
        $(this).parent().remove();
        checkPriceInput();
    });

    // FEATURED INPUT GROUP
  // FEATURED INPUT GROUP
const method  = $('#featured_method');
const amount  = $('#featured_amount');
const symbol  = $('#featured_symbol');

function setSymbol() {
    if(method.val() === 'percentage'){
        symbol.text('%');
    } else if(method.val() === 'fixed amount'){
        symbol.text('£');
    } else {
        symbol.text('');
    }
}

setSymbol();

method.on('change', function() {
    setSymbol();
    if(method.val() === 'percentage'){
        amount.attr('placeholder','Enter Percentage');
    } else if(method.val() === 'fixed amount'){
        amount.attr('placeholder','Enter Fixed Amount');
    } else {
        amount.attr('placeholder','Enter Amount');
    }
});

// Numbers + decimal only
amount.on('input', function() {
    let val = $(this).val().replace(/[^0-9.]/g,'');
    if((val.match(/\./g)||[]).length > 1){
        val = val.slice(0,-1);
    }
    $(this).val(val);
});

// Before submit: remove symbols
$('#update_product_form').on('submit', function() {
    amount.val(amount.val().replace(/[£%]/g,''));
});

});
</script>
@endsection