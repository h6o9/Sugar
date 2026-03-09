@extends('admin.layout.app')
@section('title','Products')
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Products</h4>
                            <a class="btn btn-success" href="{{ route('product.create') }}">Add Product</a>
                        </div>
                        <div class="card-body table-responsive">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
    <!-- Left side: Rows per page -->
    <div class="d-flex align-items-center gap-2">
        <label for="perPage" class="mb-0">Show</label>
        <select id="perPage" class="form-control" style="width:80px">
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>  
            <option value="100">100</option>
        </select>
        <label for="perPage" class="mb-0">entries</label>
    </div>

    <!-- Right side: Search bar -->
    <div class="d-flex align-items-center gap-2">
        <label for="searchInput" class="mb-0" style="margin-right: 8px;">Search:</label>
        <input type="text" id="searchInput" class="form-control ms-1">
    </div>
</div>

                            <table class="table table-striped table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Menu Name</th>
                                        <th>Product Name</th>
                                        <th>Image</th>
                                        <th>Base Price</th>
                                        <th>Adjustment Price</th>
                                        <th>Sizes</th>
                                        <th>Settings Applied</th>
                                        <th>Featured</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody">
                                    <tr><td colspan="11" class="text-center">Loading...</td></tr>
                                </tbody>
                            </table>
                            <div id="paginationContainer" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
@section('js')
<script>
$(document).ready(function(){
    loadProducts(1);

    $('#perPage').change(function(){ loadProducts(1); });
    $('#searchInput').on('keyup', function(){ loadProducts(1); });

    $(document).on('click','.pageBtn', function(){ loadProducts($(this).data('page')); });

    $(document).on('click', '.show_confirm', function(event) {

    event.preventDefault();

    var form = $(this).closest("form");

    swal({
        title: "Are you sure you want to delete this record?",
        text: "If you delete this, it will be gone forever.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((willDelete) => {
        if (willDelete) {
            form.submit();
        }
    });

});
});

function loadProducts(page){
    let perPage = $('#perPage').val();
    let search = $('#searchInput').val();

    $('#productsTableBody').html('<tr><td colspan="11" class="text-center">Loading...</td></tr>');

    $.ajax({
        url: "{{ route('admin.products.get') }}",
        type: "GET",
        data: {page: page, per_page: perPage, search: search},
        success: function(response){
            $('#productsTableBody').html(response.html);
            renderPagination(response.current_page, response.last_page);
        }
    });
}

function renderPagination(current, last){
    let html = '<ul class="pagination justify-content-center">';
    if(current>1) html += `<li class="page-item"><a class="page-link pageBtn" data-page="${current-1}">Previous</a></li>`;
    for(let i=1;i<=last;i++) html += `<li class="page-item ${i==current?'active':''}"><a class="page-link pageBtn" data-page="${i}">${i}</a></li>`;
    if(current<last) html += `<li class="page-item"><a class="page-link pageBtn" data-page="${current+1}">Next</a></li>`;
    html += '</ul>';
    $('#paginationContainer').html(html);
}
</script>
@endsection