@extends('admin.layout.app')
@section('title','Users')

@section('content')

<div class="main-content">
<section class="section">
<div class="section-body">

<div class="card">

<div class="card-header">
<h4>Users</h4>
</div>

<div class="card-body table-responsive">

<div class="d-flex justify-content-between mb-3">

<div>
<label>Rows per page</label>

<select id="perPage" class="form-control" style="width:80px">

<option value="10">10</option>
<option value="20">20</option>
<option value="50">50</option>

</select>
</div>

<div>
<input type="text" id="searchUser"
class="form-control"
placeholder="Search User"
style="width:250px">
</div>

</div>

<table class="table table-bordered text-center">

<thead>
<tr>
<th>Sr.</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Postcode</th>
<th>Address</th>
<th>Average Spend (£)</th>
<th>Action</th>
</tr>
</thead>

<tbody id="usersTableBody">

<tr>
<td colspan="8">Loading...</td>
</tr>

</tbody>

</table>

<div id="paginationContainer"></div>

</div>
</div>
</div>
</section>
</div>

@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@section('scripts')
<script>

$(document).ready(function(){

    loadUsers(1);

    $("#perPage").change(function(){
        loadUsers(1);
    });

    $("#searchUser").keyup(function(){
        loadUsers(1);
    });

    $(document).on("click",".pageBtn",function(){
        let page=$(this).data("page");
        loadUsers(page);
    });

});

function loadUsers(page){

    let perPage=$("#perPage").val();
    let search=$("#searchUser").val();

    $.ajax({

        url:"{{ route('users.ajax') }}",
        type:"GET",

        data:{
            page:page,
            per_page:perPage,
            search:search
        },

        success:function(res){

            $("#usersTableBody").html(res.html);

            renderPagination(res.current_page,res.last_page);

        }

    });

}


// DELETE USER AJAX
$(document).on('click','.show_confirm',function(e){

    e.preventDefault();

    let button=$(this);
    let form=button.closest("form");
    let url=form.attr("action");

    swal({

        title:"Are you sure you want to delete this user?",
        text:"If you delete this it will be gone forever.",
        icon:"warning",
        buttons:true,
        dangerMode:true,

    })

    .then((willDelete)=>{

        if(willDelete){

            $.ajax({

                url:url,
                type:"POST",
                data:form.serialize(),

                success:function(){

                    button.closest("tr").remove();

                    toastr.success("User deleted successfully");

                },

                error:function(){
                    toastr.error("Something went wrong");
                }

            });

        }

    });

});



// PAGINATION FUNCTION
function renderPagination(current,last){

    let html='<ul class="pagination justify-content-center">';

    if(current>1){

        html+=`<li class="page-item">
        <a class="page-link pageBtn" data-page="${current-1}">Previous</a>
        </li>`;

    }

    let start=Math.max(1,current-2);
    let end=Math.min(last,current+2);

    if(start>1){

        html+=`<li class="page-item">
        <a class="page-link pageBtn" data-page="1">1</a>
        </li>`;

        if(start>2){
            html+=`<li class="page-item disabled">
            <span class="page-link">...</span>
            </li>`;
        }

    }

    for(let i=start;i<=end;i++){

        html+=`<li class="page-item ${i==current?'active':''}">
        <a class="page-link pageBtn" data-page="${i}">${i}</a>
        </li>`;

    }

    if(end<last){

        if(end<last-1){

            html+=`<li class="page-item disabled">
            <span class="page-link">...</span>
            </li>`;

        }

        html+=`<li class="page-item">
        <a class="page-link pageBtn" data-page="${last}">${last}</a>
        </li>`;

    }

    if(current<last){

        html+=`<li class="page-item">
        <a class="page-link pageBtn" data-page="${current+1}">Next</a>
        </li>`;

    }

    html+='</ul>';

    $("#paginationContainer").html(html);

}

</script>
@endsection