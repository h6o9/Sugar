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
                    <div class="">
                        <div class="row">
                            <div class="col-12 col-md-12 col-lg-12">
                                <div class="card">
                                    <h4 class="text-center my-4">Add Product</h4>

                                    <div class="container">
                                        <div class="row">
                                            {{-- Name --}}
                                            <div class="col-sm-6">
                                                <div class="form-group mb-2">
                                                    <label>Name</label>
                                                    <input type="text" placeholder="Enter Product Name" name="name" id="name" class="form-control">
                                                    @error('name')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Menu --}}
                                            <div class="col-sm-6">
                                                <div class="form-group mb-2">
                                                    <label>Menus</label>
                                                    <select id="category-dropdown" class="form-control" name="menu_id">
                                                        <option value="" disabled selected>Select Menus</option>
                                                        @foreach ($menus as $menu)
                                                            <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('menu_id')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Price --}}
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group mb-2">
                                                    <label>Price</label>
                                                    <input type="text" placeholder="Enter Product Price" name="price" id="price" class="form-control">
                                                    @error('price')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Variants --}}
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <button type="button" class="btn btn-primary mb-3" id="addSizeBtn">Add Variants</button>
                                                        <div id="sizeInputs"></div>
                                                    </div>
                                                </div>

                                                {{-- Featured Inputs --}}
                                                <div class="form-group mb-2">
                                                    <label>Optional Featured Settings</label>
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <select name="featured_action" id="featured_action" class="form-control input-field">
                                                                <option value="" disabled selected>Select Operation</option>
                                                                <option value="increase">Increase</option>
                                                                <option value="decrease">Decrease</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-4">
                                                            <select name="featured_method" id="featured_method" class="form-control input-field">
                                                                <option value="" disabled selected>Select Method</option>
                                                                <option value="percentage">Percentage (%)</option>
                                                                <option value="fixed amount">Fixed Amount(£)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-4">
                                                            <input type="text" name="featured_amount" id="featured_amount" class="form-control input-field" placeholder="Enter Amount">
                                                        </div>
                                                    </div>
                                                    @error('featured_amount')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Image --}}
                                            <div class="col-sm-6">
                                                <div class="form-group mb-2">
                                                    <label>Image</label>
                                                    <input type="file" placeholder="Image" name="image" id="image" class="form-control">
                                                    @error('image')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Toppings Category --}}
                                            <div class="col-sm-6">
                                                <div class="form-group mb-2">
                                                    <label>Toppings Category (Optional)</label>
                                                    <select class="form-control selectric" name="category_id[]" multiple>
                                                        <option value="" disabled selected>Select Categories</option>
                                                        @foreach ($categories as $categorie)
                                                            <option value="{{ $categorie->id }}">{{ $categorie->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('category_id')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- Submit --}}
                                    <div class="card-footer text-center row">
                                        <div class="col">
                                            <button type="submit" class="btn btn-success mr-1 btn-bg" id="submit">Add</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
    // Variants logic
    function checkPriceInput() {
        var priceInputValue = $('#price').val().trim();
        if (priceInputValue !== '') {
            $('#addSizeBtn').prop('disabled', true);
        } else {
            $('#addSizeBtn').prop('disabled', false);
        }
    }

    $('#price').keyup(function() { checkPriceInput(); });

    $('#addSizeBtn').click(function() {
        $('#price').prop('disabled', true);
        $('#sizeInputs').append(
            '<div class="row justify-content-center"><input type="text" class="form-control mb-2 col-4 col-md-5 col-sm-4 col-lg-5" name="sizes[]" placeholder="Enter Size"><input type="text" class="form-control mb-2 col-4 col-md-5 col-sm-4 col-lg-5 ml-2" name="prices[]" placeholder="Enter Price"><button type="button" class="btn btn-danger ml-2 mb-2 removeBtn"><i class="fas fa-trash-alt"></i></button></div>'
        );
    });

    $(document).on('click', '.removeBtn', function() {
        $(this).parent('div').remove();
        if ($('#sizeInputs').children().length === 0) {
            $('#price').prop('disabled', false);
        }
        checkPriceInput();
    });

    // Featured amount logic
    const featured_method = $('#featured_method');
    const featured_amount = $('#featured_amount');

    function formatFeaturedAmount() {
        let val = featured_amount.val().replace(/[£%]/g,'');
        if(featured_method.val() === 'percentage' && val) {
            featured_amount.val(val + '%');
        } else if(featured_method.val() === 'fixed amount' && val) {
            featured_amount.val('£' + val);
        }
    }

    // Initial formatting
    formatFeaturedAmount();

    featured_method.on('change', function() {
        let val = featured_amount.val().replace(/[£%]/g,'');
        featured_amount.val(val);
        formatFeaturedAmount();
        featured_amount.attr('placeholder', featured_method.val() === 'percentage' ? 'Enter Percentage %' : 'Enter Fixed Amount (£)');
    });

    featured_amount.on('input', function() {
        let val = $(this).val().replace(/[£%]/g,'');
        $(this).val(val);
        formatFeaturedAmount();
    });

    // Before submit, remove symbols for DB
    $('#add_student').on('submit', function() {
        featured_amount.val(featured_amount.val().replace(/[£%]/g,''));
    });

});
</script>
@endsection