@extends('admin.layout.app')
@section('title', 'Edit Cibo Express')

@section('content')
<div class="main-content">
<section class="section">
<div class="section-body">

<a class="btn btn-primary mb-4" href="{{ url()->previous() }}">Back</a>

<form action="{{ route('cibo-express.update', $ciboExpressItem->id) }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="card">
<div class="card-header">
<h4 class="text-center w-100">Edit Cibo Express</h4>
</div>

<div class="card-body">

<div class="row">

{{-- Title --}}
<div class="col-md-6">
<div class="form-group">
<label>Title</label>
<input type="text"
name="title"
class="form-control"
placeholder="Enter Title"
value="{{ $ciboExpressItem->title }}">

@error('title')
<div class="text-danger">{{ $message }}</div>
@enderror
</div>
</div>

{{-- Image --}}
<div class="col-md-6">
<div class="form-group">
<label>Image</label>
<input type="file" name="image" class="form-control">

@error('image')
<div class="text-danger">{{ $message }}</div>
@enderror
</div>
</div>

{{-- Description --}}
<div class="col-md-12">
<div class="form-group">
<label>Description</label>
<textarea
name="description"
class="form-control"
rows="4"
placeholder="Enter Description">{{ $ciboExpressItem->description }}</textarea>

@error('description')
<div class="text-danger">{{ $message }}</div>
@enderror
</div>
</div>

</div>

</div>

<div class="card-footer text-center">
<button type="submit" class="btn btn-success px-5">
Update
</button>
</div>

</div>

</form>

</div>
</section>
</div>
@endsection


@section('js')
@if (\Illuminate\Support\Facades\Session::has('message'))
<script>
toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
</script>
@endif
@endsection