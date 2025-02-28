<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-topbar="light">

    <head>
        <meta charset="utf-8" />
        <title>Shoprite - Job Opportunities</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Job Opportunities" name="description" />
        <meta content="Shoprite" name="author" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Open Graph Tags -->
        <meta property="og:title" content="Shoprite">
        <meta property="og:description" content="Job Opportunities">
        <meta property="og:image" content="{{ URL::asset('build/images/logo.png') }}">

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico')}}">
        @include('layouts.head-css')
    </head>

    @yield('body')

    @yield('content')

    @include('layouts.vendor-scripts')
    </body>
</html>
