@extends('layouts.master-without-nav')
@section('title') Shoprite - Job Opportunities @endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/css/welcome.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('body')

<body data-bs-spy="scroll" data-bs-target="#navbar-example">
    @endsection
    @section('content')

    <!-------------------------------------------------------------------------------------
        Navigation
    -------------------------------------------------------------------------------------->

    <!-- Begin page -->
    <div class="layout-wrapper landing">
        <nav class="navbar navbar-expand-lg navbar-landing fixed-top job-navbar" id="navbar" style="background-color: #fff;">
            <div class="container-fluid custom-container">
                <a class="navbar-brand" href="/">
                    <img src="{{URL::asset('build/images/logo-dark.png')}}" class="card-logo card-logo-dark" alt="Shoprite Logo" height="30">
                    <img src="{{URL::asset('build/images/logo-light.png')}}" class="card-logo card-logo-light" alt="Shoprite Logo" height="30">
                </a>
                <button class="navbar-toggler py-0 fs-20 text-body" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="mdi mdi-menu"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mx-auto mt-2 mt-lg-0" id="navbar-example">
                        <li class="nav-item">
                            <a class="nav-link nav-shoprite fs-16 active" href="#hero">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-shoprite fs-16" href="#apply">How to apply</a>
                        </li>
                    </ul>

                    <div class="d-flex flex-wrap gap-2">
                        @auth
                            @php
                                $user = Auth::user();
                                switch ($user->role_id) {
                                    case 1:
                                    case 2:
                                        $url = 'admin/';
                                        break;
                                    case 3:
                                        $url = 'rpp/';
                                        break;
                                    case 4:
                                        $url = 'dtdp/';
                                        break;
                                    case 5:
                                        $url = 'dpp/';
                                        break;
                                    case 6:
                                        $url = 'manager/';
                                        break;
                                    default:
                                        $url = '/';
                                        break;
                                }
                            @endphp
                            <a href="{{ url($url.'home') }}" class="btn btn-soft-danger">
                                <i class="ri-home-line align-bottom me-1"></i> 
                                Home
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-shoprite-primary">
                                Store Login
                                <i class="ri-arrow-right-line align-bottom me-1"></i> 
                            </a>
                        @endauth
                    </div>
                </div>

            </div>
        </nav>
        <!-- end navbar -->

        <!-------------------------------------------------------------------------------------
            Hero
        -------------------------------------------------------------------------------------->

        <!-- start hero section -->
        <section class="section job-hero-section pb-0 h-100" id="hero">
            <!-- Full-width bg-light with limited height -->
            <div class="bg-light w-100 py-5" id="greyBanner"></div>
        
            <div class="w-100 py-5">
                <div class="container" id="heroContainer">
                    <!-- Text and Image Row -->
                    <div class="row align-items-center" id="heroRow">
                        <div class="col-lg-6" style="margin-top: -80px;">
                            <div class="p-4">
                                <h1 class="display-6 fw-bold text-capitalize mb-3 lh-base">
                                    Ready to get started?
                                </h1>
                                <p class="lead lh-base mb-4 w-400">
                                    Welcome to the Shoprite, Checkers, and Usave employment journey for store jobs! We’ll guide you through each step of the process.
                                </p>
                                <p class="lead lh-base mb-4">
                                    Once you are registered, you will be added to our talent pool. If your application moves forward, we’ll keep you updated.
                                </p>
                                <p class="lead lh-base mb-4">
                                    If you have already registered on WhatsApp, you can use this platform to update your personal details.
                                </p>
                                <div class="d-flex">
                                    <a href="{{ route('login') }}" class="btn btn-shoprite-primary me-2">
                                        JOBS AT STORES
                                        <i class="ri-arrow-right-line align-bottom me-1"></i> 
                                    </a>
                                    <a href="https://www.shopriteholdings.co.za/careers.html" target="_blank" class="btn btn-shoprite-outline-primary">
                                        WORK AT OUR OFFICES
                                        <i class="ri-arrow-right-line align-bottom me-1"></i> 
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-lg-6 text-center">
                            <img src="{{URL::asset('build/images/job-profile4.png')}}" alt="Shoprite employees" class="img-fluid rounded">
                        </div>
                    </div>
                    <!-- end row -->
        
                    <!-- Logos row contained within the first half of the screen -->
                    <div class="row" id="logoRow">
                        <div class="col-lg-6 text-center">
                            <div class="row">
                                <div class="col-4">
                                    <img src="{{URL::asset('build/images/shoprite-logo.png')}}" alt="Shoprite" class="img-fluid" style="max-height: 60px;">
                                </div>
                                <div class="col-4">
                                    <img src="{{URL::asset('build/images/checkers-logo.png')}}" alt="Checkers" class="img-fluid" style="max-height: 60px;">
                                </div>
                                <div class="col-4">
                                    <img src="{{URL::asset('build/images/usave-logo.png')}}" alt="Usave" class="img-fluid" style="max-height: 60px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end logos row -->
                </div>
                <!-- end container -->
            </div>
        </section>                           
        <!-- end hero section -->

        <!-------------------------------------------------------------------------------------
            Call To Action
        -------------------------------------------------------------------------------------->

        <!-- start cta -->
        <section class="py-5 bg-shoprite-primary" id="cta">
            <div class="container">
                <div class="row align-items-center gy-4">
                    <div class="col-12">
                        <div class="live-preview">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('register') }}" class="btn btn-light">
                                    REGISTER A NEW ACCOUNT
                                    <i class="ri-arrow-right-line align-bottom me-1"></i> 
                                </a>
                                <a href="{{ route('login') }}" class="btn  btn-outline-light">
                                    LOGIN TO YOUR ACCOUNT
                                    <i class="ri-arrow-right-line align-bottom me-1"></i> 
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </section>
        <!-- end cta -->

        <!-------------------------------------------------------------------------------------
            Apply
        -------------------------------------------------------------------------------------->

        <section class="section" id="apply" style="padding-top: 70px; padding-bottom: 50px;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="text-center mb-5">
                            <h1 class="mb-3 ff-secondary fw-bold lh-base">
                                Here's how to apply:
                            </h1>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
        
                <!-- Steps Row with 5 Columns -->
                <div class="row justify-content-center d-flex align-items-stretch">
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                        <div class="card shadow-none bg-light h-100">
                            <div class="card-body p-4">
                                <h1 class="fw-bold display-5 ff-secondary mb-4 text-shoprite-secondary position-relative">
                                    <span>1</span>
                                </h1>
                                <h6 class="fs-17 mb-2">Register Account</h6>
                                <p class="text-muted fs-15">
                                    Register online with your name and contact details.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                        <div class="card shadow-none bg-light h-100">
                            <div class="card-body p-4">
                                <h1 class="fw-bold display-5 ff-secondary mb-4 text-shoprite-secondary position-relative">
                                    <span>2</span>
                                </h1>
                                <h6 class="fs-17 mb-2">Set Password</h6>
                                <p class="text-muted fs-15">
                                    Choose a password that you will remember so that you can update your details if anything changes.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                        <div class="card shadow-none bg-light h-100">
                            <div class="card-body p-4">
                                <h1 class="fw-bold display-5 ff-secondary mb-4 text-shoprite-secondary position-relative">
                                    <span>3</span>
                                </h1>
                                <h6 class="fs-17 mb-2">Complete Details</h6>
                                <p class="text-muted fs-15">
                                    Answer questions about your qualifications and experience.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                        <div class="card shadow-none bg-light h-100">
                            <div class="card-body p-4">
                                <h1 class="fw-bold display-5 ff-secondary mb-4 text-shoprite-secondary position-relative">
                                    <span>4</span>
                                </h1>
                                <h6 class="fs-17 mb-2">Assessment</h6>
                                <p class="text-muted fs-15">
                                    Complete an assessment exercise.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                        <div class="card shadow-none bg-light h-100">
                            <div class="card-body p-4">
                                <h1 class="fw-bold display-5 ff-secondary mb-4 text-shoprite-secondary position-relative">
                                    <span>5</span>
                                </h1>
                                <h6 class="fs-17 mb-2">Next Steps</h6>
                                <p class="text-muted fs-15">
                                    We will make contact if you move to the next stage in our employment process.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>                
                <!-- end steps row -->
            </div>
            <!-- end container -->
        </section>

        <!-------------------------------------------------------------------------------------
            Banner
        -------------------------------------------------------------------------------------->

        <section class="section banner-section" id="banner" style="padding-top: 0px; padding-bottom: 10px;">
            <div class="container text-center">
                <img src="{{ URL::asset('build/images/shoprite-banner.png') }}" alt="Shoprite - Job Opportunities" class="img-fluid rounded">
            </div>
        </section>
        
        <!-------------------------------------------------------------------------------------
            Call to Action
        -------------------------------------------------------------------------------------->

        <!-- start cta -->
        <section class="py-5 bg-shoprite-secondary position-relative">
            <div class="container">
                <div class="row align-items-center gy-4">
                    <div class="col-sm">
                        <div>
                            <h4 class="text-white fw-bold">
                                Ready to get started?
                            </h4>
                            <p class="text-white mb-0">
                                Create new account and refer your friend
                            </p>
                        </div>
                    </div>
                    <!-- end col -->
                    <div class="col-sm-auto">
                        <a href="{{ route('register') }}" class="btn btn-outline-light">
                            CREATE FREE ACCOUNT 
                            <i class="ri-arrow-right-line align-bottom me-1"></i>
                        </a>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </section>
        <!-- end cta -->

        <!-------------------------------------------------------------------------------------
            Footer
        -------------------------------------------------------------------------------------->

        <!-- Start footer -->
        <footer class="custom-footer bg-shoprite-primary py-5 position-relative">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mt-4">
                        <div>
                            <div>
                                <img src="{{URL::asset('build/images/logo-light.png')}}" alt="Shoprite Logo" height="30" />
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7 ms-lg-auto">
                        <div class="row">                            
                            <div class="col-sm-4 mt-4">
                                <h5 class="text-white mb-0">Legal</h5>
                                <div class="text-muted mt-3">
                                    <ul class="list-unstyled ff-secondary footer-list fs-15">
                                        <li><a href="https://bit.ly/srtscsnew" target="_blank">Privacy Policy</a></li>
                                        <li><a href="https://bit.ly/srtscsnew" target="_blank">Terms & Conditions</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-4 mt-4">
                                <h5 class="text-white mb-0">Support</h5>
                                <div class="text-muted mt-3">
                                    <ul class="list-unstyled ff-secondary footer-list fs-15">
                                        <li><a href="mailto:help@shoprite.co.za?subject=Contact%20Inquiry">Contact</a></li>
                                    </ul>                                
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row text-center text-sm-start align-items-center mt-5">
                    <div class="col-sm-6">
                        <div>
                            <p class="copy-rights mb-0">
                                <script>
                                    document.write(new Date().getFullYear())

                                </script> © Orient - OTB Group
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end mt-3 mt-sm-0">
                            <ul class="list-inline mb-0 footer-list gap-4 fs-15">
                                <li class="list-inline-item">
                                    <a href="https://bit.ly/srtscsnew" target="_blank">Privacy Policy</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="https://bit.ly/srtscsnew" target="_blank">Terms & Conditions</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="https://bit.ly/srtscsnew" target="_blank">Security</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end footer -->


        <!--start back-to-top-->
        <button onclick="topFunction()" class="btn btn-info btn-icon landing-back-top" id="back-to-top">
            <i class="ri-arrow-up-line"></i>
        </button>
        <!--end back-to-top-->

    </div>
    <!-- end layout wrapper -->

    @endsection
    @section('script')
        <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
        <script src="{{ URL::asset('build/js/pages/job-lading.init.js') }}"></script>
        <script src="{{ URL::asset('build/js/pages/subscribe.init.js') }}"></script>
    @endsection
