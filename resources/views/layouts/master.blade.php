<!doctype html >
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-sidebar="light" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>Shoprite - Job Opportunities</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Job Opportunities" name="description" />
    <meta content="Shoprite" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="/">

    <!-- Open Graph Tags -->
    <meta property="og:title" content="Shoprite">
    <meta property="og:description" content="Job Opportunities">
    <meta property="og:image" content="{{ URL::asset('build/images/logo.png') }}">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico')}}">
    @include('layouts.head-css')
    @routes
    @yield('head')
</head>

@section('body')
    @include('layouts.body')
@show
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    @include('layouts.customizer')

    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')
</body>

</html>
