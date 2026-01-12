<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard')</title>

    <!-- ========== Favicon ========== -->
    <link rel="icon" href="{{ asset('public/img/logo.png') }}">

    <!-- ========== Vendor CSS ========== -->
    <link rel="stylesheet" href="{{ asset('public/admin/assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/css/custom.css') }}">

    <!-- ========== Plugins CSS ========== -->
    <link rel="stylesheet" href="{{ asset('public/admin/assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/toastr/css/toastr.css') }}">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/bundles/summernote/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('public/admin/assets/bundles/jquery-selectric/selectric.css') }}">

    <!-- ========== External CSS ========== -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    @yield('style')
</head>

<body>
    <div class="loader"></div>

    <div id="app">
        <div class="main-wrapper main-wrapper-1">

            @include('admin.common.header')
            @include('admin.common.side_menu')

            @yield('content')

            @include('admin.common.footer')

        </div>
    </div>

    <!-- ========== Core JS ========== -->
    <script src="{{ asset('public/admin/assets/js/app.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/js/scripts.js') }}"></script>

    <!-- ========== Plugins JS ========== -->
    <script src="{{ asset('public/admin/assets/toastr/js/toastr.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/bundles/summernote/summernote-bs4.js') }}"></script>
    <script src="{{ asset('public/admin/assets/bundles/jquery-selectric/jquery.selectric.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/bundles/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('public/admin/assets/bundles/apexcharts/apexcharts.min.js') }}"></script>

    <!-- ========== External JS ========== -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>

    <!-- ========== Toastr Config ========== -->
    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 3000
        };

        @if (Session::has('success'))
            toastr.success("{{ Session::get('success') }}");
        @endif

        @if (Session::has('error'))
            toastr.error("{{ Session::get('error') }}");
        @endif
    </script>

   

    <!-- ========== Custom JS ========== -->
    <script src="{{ asset('public/admin/assets/js/custom.js') }}"></script>

    @yield('js')

</body>
</html>
