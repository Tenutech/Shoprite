@extends('layouts.master-without-nav')
@section('title') Orient  - Where Potential Meets Opportunity @endsection
@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
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
        <nav class="navbar navbar-expand-lg navbar-landing fixed-top job-navbar" id="navbar">
            <div class="container-fluid custom-container">
                <a class="navbar-brand" href="index">
                    <img src="{{URL::asset('build/images/logo-dark.png')}}" class="card-logo card-logo-dark" alt="logo dark" height="17">
                    <img src="{{URL::asset('build/images/logo-light.png')}}" class="card-logo card-logo-light" alt="logo light" height="17">
                </a>
                <button class="navbar-toggler py-0 fs-20 text-body" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="mdi mdi-menu"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mx-auto mt-2 mt-lg-0" id="navbar-example">
                        <li class="nav-item">
                            <a class="nav-link fs-16 active" href="#hero">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-16" href="#process">Process</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-16" href="#categories">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-16" href="#findJob">Find Jobs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-16" href="#candidates">Candidates</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-16" href="#blog">Blog</a>
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
                                        $url = 'seller/';
                                        break;
                                    case 4:
                                        $url = 'buyer/';
                                        break;
                                    case 5:
                                        $url = 'advisor/';
                                        break;
                                    case 6:
                                        $url = 'trader/';
                                        break;
                                    default:
                                        $url = '/home';
                                        break;
                                }
                            @endphp
                            <a href="{{ url($url.'home') }}" class="btn btn-soft-success">
                                <i class="ri-home-line align-bottom me-1"></i> 
                                Home
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-soft-primary">
                                <i class="ri-user-3-line align-bottom me-1"></i> 
                                Login
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-soft-danger">
                                <i class="ri-user-3-line align-bottom me-1"></i> 
                                Register
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
        <section class="section job-hero-section bg-light pb-0" id="hero">
            <div class="container">
                <div class="row justify-content-between align-items-center">
                    <div class="col-lg-6">
                        <div>
                            <h1 class="display-6 fw-bold text-capitalize mb-3 lh-base">
                                Where Potential Meets Opportunity
                            </h1>
                            <p class="lead text-muted lh-base mb-4">
                                Discover opportunities tailored for you, stand out with unique 
                                resumes, and propel your aspirations forward.
                            </p>
                            <form action="#" class="job-panel-filter">
                                <div class="row g-md-0 g-2">
                                    <div class="col-md-4">
                                        <div>
                                            <input type="search" id="job-title" class="form-control filter-input-box" placeholder="Search Job...">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-md-4">
                                        <div>
                                            <select class="form-control" data-choices>
                                                <option value="">Select Job</option>
                                                @foreach ($positions as $position)
                                                    <option value="{{ $position->id }}">
                                                        {{ $position->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-md-4">
                                        <div class="h-100">
                                            <button class="btn btn-primary submit-btn w-100 h-100" type="submit"><i class="ri-search-2-line align-bottom me-1"></i> Find Job</button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>

                            <ul class="treding-keywords list-inline mb-0 mt-3 fs-13">
                                <li class="list-inline-item text-danger fw-semibold">
                                    <i class="mdi mdi-tag-multiple-outline align-middle"></i> 
                                    Trending Keywords:
                                </li>
                                <li class="list-inline-item">
                                    <a href="javascript:void(0)" class="link-secondary">
                                        Assistant,
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="javascript:void(0)" class="link-secondary">
                                        Butcher,
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="javascript:void(0)" class="link-secondary">
                                        Clerk,
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="javascript:void(0)" class="link-secondary">
                                        Packer
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!--end col-->
                    <div class="col-lg-4">
                        <div class="position-relative home-img text-center mt-5 mt-lg-0">
                            <div class="card p-3 rounded shadow-lg inquiry-box">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0 me-3">
                                        <div class="avatar-title bg-danger-subtle text-danger rounded fs-18">
                                            <i class="ri-mail-send-line"></i>
                                        </div>
                                    </div>
                                    <h5 class="fs-15 lh-base mb-0">Work Inquiry from Orient</h5>
                                </div>
                            </div>

                            <div class="card p-3 rounded shadow-lg application-box">
                                <h5 class="fs-15 lh-base mb-3">Applications</h5>
                                <div class="avatar-group">
                                    <a href="javascript: void(0);" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Brent Gonzalez">
                                        <div class="avatar-xs">
                                            <img src="{{URL::asset('build/images/users/avatar-17.jpg')}}" alt="" class="rounded-circle img-fluid">
                                        </div>
                                    </a>
                                    <a href="javascript: void(0);" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Ellen Smith">
                                        <div class="avatar-xs">
                                            <div class="avatar-title rounded-circle bg-danger">
                                                S
                                            </div>
                                        </div>
                                    </a>
                                    <a href="javascript: void(0);" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Ellen Smith">
                                        <div class="avatar-xs">
                                            <img src="{{URL::asset('build/images/users/avatar-16.jpg')}}" alt="" class="rounded-circle img-fluid">
                                        </div>
                                    </a>
                                    <a href="javascript: void(0);" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top">
                                        <div class="avatar-xs">
                                            <div class="avatar-title rounded-circle bg-success">
                                                Z
                                            </div>
                                        </div>
                                    </a>
                                    <a href="javascript: void(0);" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Brent Gonzalez">
                                        <div class="avatar-xs">
                                            <img src="{{URL::asset('build/images/users/avatar-12.jpg')}}" alt="" class="rounded-circle img-fluid">
                                        </div>
                                    </a>
                                    <a href="javascript: void(0);" class="avatar-group-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="More Appliances">
                                        <div class="avatar-xs">
                                            <div class="avatar-title fs-13 rounded-circle bg-light border-dashed border text-primary">
                                                2k+
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <img src="{{URL::asset('build/images/job-profile3.png')}}" alt="" class="user-img">

                            <div class="circle-effect">
                                <div class="circle"></div>
                                <div class="circle2"></div>
                                <div class="circle3"></div>
                                <div class="circle4"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </section>
        <!-- end hero section -->

        <!-------------------------------------------------------------------------------------
            Process
        -------------------------------------------------------------------------------------->

        <section class="section" id="process">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="text-center mb-5">
                            <h1 class="mb-3 ff-secondary fw-bold lh-base">
                                Experience the 
                                <span class="text-primary">
                                    Simplified
                                </span> 
                                Hiring Process with Orient
                            </h1>
                            <p class="text-muted">
                                Orient streamlines the hiring process, offering an end-to-end 
                                solution for both applicants and hiring managers. Join us on a 
                                recruitment journey that is efficient, automated, and tailored 
                                to the South African job market.
                            </p>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!--end row-->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-lg">
                            <div class="card-body p-4">
                                <h1 class="fw-bold display-5 ff-secondary mb-4 text-success position-relative">
                                    <div class="job-icon-effect"></div>
                                    <span>1</span>
                                </h1>
                                <h6 class="fs-17 mb-2">
                                    Register Account
                                </h6>
                                <p class="text-muted mb-0 fs-15">
                                    Begin by setting up an account and creating a detailed 
                                    profile.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-none">
                            <div class="card-body p-4">
                                <h1 class="fw-bold display-5 ff-secondary mb-4 text-success position-relative">
                                    <div class="job-icon-effect"></div>
                                    <span>2</span>
                                </h1>
                                <h6 class="fs-17 mb-2">
                                    Browse Vacancies
                                </h6>
                                <p class="text-muted mb-0 fs-15">
                                    Discover a range of vacancies tailored to your preferences 
                                    and skills.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-none">
                            <div class="card-body p-4">
                                <h1 class="fw-bold display-5 ff-secondary mb-4 text-success position-relative">
                                    <div class="job-icon-effect"></div>
                                    <span>3</span>
                                </h1>
                                <h6 class="fs-17 mb-2">
                                    Automated Screening
                                </h6>
                                <p class="text-muted mb-0 fs-15">
                                    Let Orient's AI match you with ideal roles and undergo 
                                    automated pre-screening.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-none">
                            <div class="card-body p-4">
                                <h1 class="fw-bold display-5 ff-secondary mb-4 text-success position-relative">
                                    <div class="job-icon-effect"></div>
                                    <span>4</span>
                                </h1>
                                <h6 class="fs-17 mb-2">
                                    Interview & Onboard
                                </h6>
                                <p class="text-muted mb-0 fs-15">
                                    Schedule interviews, and seamlessly onboard with Orient.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end container-->
        </section>

        <!-------------------------------------------------------------------------------------
            Features
        -------------------------------------------------------------------------------------->

        <!-- start features -->
        <section class="section">
            <div class="container">
                <div class="row align-items-center justify-content-lg-between justify-content-center gy-4">
                    <div class="col-lg-5 col-sm-7">
                        <div class="about-img-section mb-5 mb-lg-0 text-center">
                            <div class="card rounded shadow-lg inquiry-box-2 d-none d-lg-block">
                                <div class="card-body d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0 me-3">
                                        <div class="avatar-title bg-info-subtle text-info rounded-circle fs-18">
                                            <i class="ri-briefcase-2-line"></i>
                                        </div>
                                    </div>
                                    <h5 class="fs-15 lh-base mb-0">Search Over <span class="text-secondary fw-bold">1,00,000+</span> Jobs</h5>
                                </div>
                            </div>

                            <div class="card feedback-box">
                                <div class="card-body d-flex shadow-lg">
                                    <div class="flex-shrink-0 me-3">
                                        <img src="{{URL::asset('build/images/users/avatar-1.jpg')}}" alt="" class="avatar-sm rounded-circle">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-14 lh-base mb-0">Sibongile Khumalo</h5>
                                        <p class="text-muted fs-11 mb-1">General Assistant</p>

                                        <div class="text-warning">
                                            <i class="ri-star-s-fill"></i>
                                            <i class="ri-star-s-fill"></i>
                                            <i class="ri-star-s-fill"></i>
                                            <i class="ri-star-s-fill"></i>
                                            <i class="ri-star-s-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <img src="{{URL::asset('build/images/about.jpg')}}" alt="" class="img-fluid mx-auto rounded-3" />
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="text-muted">
                            <h1 class="mb-3 fw-bold lh-base">Discover Your <span class="text-primary">Next Role</span> with Orient</h1>
                            <p class="ff-secondary fs-16 mb-2">
                                Dive into Orient to kickstart your journey towards your <b>dream job</b>. 
                                Our platform seamlessly connects you to industries and positions that align 
                                with your skills, experience, and aspirations.
                            </p>
                            <p class="ff-secondary fs-16">
                                Engage with potential employers through Orient's intuitive interface. 
                                Apply, interview, and explore roles, all while gaining insights directly from 
                                industry professionals.
                            </p>

                            <div class="vstack gap-2 mb-4 pb-1">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <div class="avatar-xs icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle h2">
                                                <i class="ri-check-fill"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0">
                                            Automated Job Matching
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <div class="avatar-xs icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle h2">
                                                <i class="ri-check-fill"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0">
                                            Comprehensive Screening
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <div class="avatar-xs icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle h2">
                                                <i class="ri-check-fill"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0">
                                            All-in-One Platform
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <a href="#!" class="btn btn-secondary">
                                    Find Your Job 
                                    <i class="ri-arrow-right-line align-bottom ms-1"></i>
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
        <!-- end features -->

        <!-------------------------------------------------------------------------------------
            Services
        -------------------------------------------------------------------------------------->

        <!-- start services -->
        <section class="section bg-light" id="categories">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-7">
                        <div class="text-center mb-5">
                            <h1 class="mb-3 ff-secondary fw-bold text-capitalize lh-base">
                                Empowering Your <span class="text-primary">Recruitment</span> Journey with Orient
                            </h1>
                            <p class="text-muted">
                                Orient streamlines your hiring process, ensuring you find the 
                                best candidates swiftly and efficiently. Explore the range of 
                                tailored features that set our platform apart.
                            </p>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-none text-center py-3">
                            <div class="card-body py-4">
                                <div class="avatar-sm position-relative mb-4 mx-auto">
                                    <div class="job-icon-effect"></div>
                                    <div class="avatar-title bg-transparent text-success rounded-circle">
                                        <i class="ri-pencil-ruler-2-line fs-1"></i>
                                    </div>
                                </div>
                                <a href="#!" class="stretched-link">
                                    <h5 class="fs-17 pt-1">
                                        Vacancy Creation
                                    </h5>
                                </a>
                                <p class="mb-0 text-muted">
                                    Initiate your recruitment by creating single or multiple job vacancies with ease.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-none text-center py-3">
                            <div class="card-body py-4">
                                <div class="avatar-sm position-relative mb-4 mx-auto">
                                    <div class="job-icon-effect"></div>
                                    <div class="avatar-title bg-transparent text-success rounded-circle">
                                        <i class="ri-broadcast-line fs-1"></i>
                                    </div>
                                </div>
                                <a href="#!" class="stretched-link">
                                    <h5 class="fs-17 pt-1">
                                        Holistic Syndication
                                    </h5>
                                </a>
                                <p class="mb-0 text-muted">
                                    Amplify your job posts across internal and external platforms, maximizing reach.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-none text-center py-3">
                            <div class="card-body py-4">
                                <div class="avatar-sm mb-4 mx-auto position-relative">
                                    <div class="job-icon-effect"></div>
                                    <div class="avatar-title bg-transparent text-success rounded-circle">
                                        <i class="ri-profile-line fs-1"></i>
                                    </div>
                                </div>
                                <a href="#!" class="stretched-link">
                                    <h5 class="fs-17 pt-1">
                                        Applicant Profiles
                                    </h5>
                                </a>
                                <p class="mb-0 text-muted">
                                    Enable candidates to create robust profiles, providing a detailed view of their qualifications.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-none text-center py-3">
                            <div class="card-body py-4">
                                <div class="avatar-sm position-relative mb-4 mx-auto">
                                    <div class="job-icon-effect"></div>
                                    <div class="avatar-title bg-transparent text-success rounded-circle">
                                        <i class="ri-shield-star-line fs-1"></i>
                                    </div>
                                </div>
                                <a href="#!" class="stretched-link">
                                    <h5 class="fs-17 pt-1">
                                        POPIA Compliance
                                    </h5>
                                </a>
                                <p class="mb-0 text-muted">
                                    Rest assured, all operations align with South African legislation, ensuring data privacy and security.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-none text-center py-3">
                            <div class="card-body py-4">
                                <div class="avatar-sm position-relative mb-4 mx-auto">
                                    <div class="job-icon-effect"></div>
                                    <div class="avatar-title bg-transparent text-success rounded-circle">
                                        <i class="ri-filter-3-line fs-1"></i>
                                    </div>
                                </div>
                                <a href="#!" class="stretched-link">
                                    <h5 class="fs-17 pt-1">
                                        Automated Screening
                                    </h5>
                                </a>
                                <p class="mb-0 text-muted">
                                    Leverage the power of automation to screen and rank candidates based on pre-set criteria
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-none text-center py-3">
                            <div class="card-body py-4">
                                <div class="avatar-sm position-relative mb-4 mx-auto">
                                    <div class="job-icon-effect"></div>
                                    <div class="avatar-title bg-transparent text-success rounded-circle">
                                        <i class="ri-calendar-event-line fs-1"></i>
                                    </div>
                                </div>
                                <a href="#!" class="stretched-link">
                                    <h5 class="fs-17 pt-1">
                                        Scheduling
                                    </h5>
                                </a>
                                <p class="mb-0 text-muted">
                                    Schedule interviews and meetings directly through the platform for seamless coordination.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-none text-center py-3">
                            <div class="card-body py-4">
                                <div class="avatar-sm position-relative mb-4 mx-auto">
                                    <div class="job-icon-effect"></div>
                                    <div class="avatar-title bg-transparent text-success rounded-circle">
                                        <i class="ri-map-pin-range-line fs-1"></i>
                                    </div>
                                </div>
                                <a href="#!" class="stretched-link">
                                    <h5 class="fs-17 pt-1">
                                        Proximity Insights
                                    </h5>
                                </a>
                                <p class="mb-0 text-muted">
                                    Determine candidate's proximity to store locations, ensuring logistical suitability
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </section>
        <!-- end services -->

        <!-------------------------------------------------------------------------------------
            Call To Action
        -------------------------------------------------------------------------------------->

        <!-- start cta -->
        <section class="py-5 bg-primary position-relative">
            <div class="bg-overlay bg-overlay-pattern opacity-50"></div>
            <div class="container">
                <div class="row align-items-center gy-4">
                    <div class="col-sm">
                        <div>
                            <h4 class="text-white fw-bold mb-2">Ready To Get Started?</h4>
                            <p class="text-white-50 mb-0">Create new account and refer your friend</p>
                        </div>
                    </div>
                    <!-- end col -->
                    <div class="col-sm-auto">
                        <div>
                            <a href="{{ route('register') }}" class="btn bg-gradient btn-danger">Create Free Account</a>
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
            Find Job
        -------------------------------------------------------------------------------------->

        <!-- start find job -->
        <section class="section" id="findJob">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-7">
                        <div class="text-center mb-5">
                            <h1 class="mb-3 ff-secondary fw-bold text-capitalize lh-base">
                                Find the <span class="text-primary">Job</span> that Suits You
                            </h1>
                            <p class="text-muted">
                                Post a vacancy! We'll quickly match you with the right applicants.
                            </p>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card shadow-lg">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-warning-subtle rounded">
                                            <img src="{{URL::asset('build/images/companies/img-3.png')}}" alt="" class="avatar-xxs">
                                        </div>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <a href="#!">
                                            <h5>Assistant</h5>
                                        </a>
                                        <ul class="list-inline text-muted mb-3">
                                            <li class="list-inline-item">
                                                <i class="ri-building-line align-bottom me-1"></i> 
                                                Shoprite
                                            </li>
                                            <li class="list-inline-item">
                                                <i class="ri-map-pin-2-line align-bottom me-1"></i> 
                                                Pretoria
                                            </li>
                                        </ul>
                                        <div class="hstack gap-2">
                                            <span class="badge bg-success-subtle text-success">
                                                Full Time
                                            </span>
                                            <span class="badge bg-danger-subtle text-danger">
                                                On Site
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-ghost-primary btn-icon custom-toggle" data-bs-toggle="button">
                                            <span class="icon-on"><i class="ri-bookmark-line"></i></span>
                                            <span class="icon-off"><i class="ri-bookmark-fill"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-lg">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-primary-subtle rounded">
                                            <img src="{{URL::asset('build/images/companies/img-2.png')}}" alt="" class="avatar-xxs">
                                        </div>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <a href="#!">
                                            <h5>Baker</h5>
                                        </a>
                                        <ul class="list-inline text-muted mb-3">
                                            <li class="list-inline-item">
                                                <i class="ri-building-line align-bottom me-1"></i> 
                                                Checkers
                                            </li>
                                            <li class="list-inline-item">
                                                <i class="ri-map-pin-2-line align-bottom me-1"></i> 
                                                Johannesburg
                                            </li>
                                        </ul>
                                        <div class="hstack gap-2">
                                            <span class="badge bg-primary-subtle text-primary">
                                                Bakery
                                            </span>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                Part Time
                                            </span>
                                            <span class="badge bg-info-subtle text-info">
                                                Cooking
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-ghost-primary btn-icon custom-toggle active" data-bs-toggle="button">
                                            <span class="icon-on"><i class="ri-bookmark-line"></i></span>
                                            <span class="icon-off"><i class="ri-bookmark-fill"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-lg">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-danger-subtle rounded">
                                            <img src="{{URL::asset('build/images/companies/img-4.png')}}" alt="" class="avatar-xxs">
                                        </div>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <a href="#!">
                                            <h5> Butcher/Meat Technician</h5>
                                        </a>
                                        <ul class="list-inline text-muted mb-3">
                                            <li class="list-inline-item">
                                                <i class="ri-building-line align-bottom me-1"></i> 
                                                USave
                                            </li>
                                            <li class="list-inline-item">
                                                <i class="ri-map-pin-2-line align-bottom me-1"></i> 
                                                Durban
                                            </li>
                                        </ul>
                                        <div class="hstack gap-2">
                                            <span class="badge bg-warning-subtle text-warning">
                                                Butchery
                                            </span>
                                            <span class="badge bg-info-subtle text-info">
                                                Meat Specialist
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-ghost-primary btn-icon custom-toggle active" data-bs-toggle="button">
                                            <span class="icon-on"><i class="ri-bookmark-line"></i></span>
                                            <span class="icon-off"><i class="ri-bookmark-fill"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-lg">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-success-subtle rounded">
                                            <img src="{{URL::asset('build/images/companies/img-9.png')}}" alt="" class="avatar-xxs">
                                        </div>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <a href="#!">
                                            <h5>Cashier</h5>
                                        </a>
                                        <ul class="list-inline text-muted mb-3">
                                            <li class="list-inline-item">
                                                <i class="ri-building-line align-bottom me-1"></i> 
                                                OK
                                            </li>
                                            <li class="list-inline-item">
                                                <i class="ri-map-pin-2-line align-bottom me-1"></i> 
                                                Cape Town
                                            </li>
                                        </ul>
                                        <div class="hstack gap-2">
                                            <span class="badge bg-success-subtle text-success">
                                                Retail
                                            </span>
                                            <span class="badge bg-danger-subtle text-danger">
                                                Customer Service
                                            </span>
                                            <span class="badge bg-primary-subtle text-primary">
                                                Full Time
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-ghost-primary btn-icon custom-toggle" data-bs-toggle="button">
                                            <span class="icon-on"><i class="ri-bookmark-line"></i></span>
                                            <span class="icon-off"><i class="ri-bookmark-fill"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-lg">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-info-subtle rounded">
                                            <img src="{{URL::asset('build/images/companies/img-1.png')}}" alt="" class="avatar-xxs">
                                        </div>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <a href="#!">
                                            <h5>Clerk</h5>
                                        </a>
                                        <ul class="list-inline text-muted mb-3">
                                            <li class="list-inline-item">
                                                <i class="ri-building-line align-bottom me-1"></i> 
                                                House & Home
                                            </li>
                                            <li class="list-inline-item">
                                                <i class="ri-map-pin-2-line align-bottom me-1"></i> 
                                                Bloemfontein
                                            </li>
                                        </ul>
                                        <div class="hstack gap-2">
                                            <span class="badge bg-danger-subtle text-danger">
                                                Administration
                                            </span>
                                            <span class="badge bg-success-subtle text-success">
                                                Office
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-ghost-primary btn-icon custom-toggle" data-bs-toggle="button">
                                            <span class="icon-on"><i class="ri-bookmark-line"></i></span>
                                            <span class="icon-off"><i class="ri-bookmark-fill"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-lg">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-success-subtle rounded">
                                            <img src="{{URL::asset('build/images/companies/img-7.png')}}" alt="" class="avatar-xxs">
                                        </div>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <a href="#!">
                                            <h5>Deli, Bakery or Butchery Assistant</h5>
                                        </a>
                                        <ul class="list-inline text-muted mb-3">
                                            <li class="list-inline-item">
                                                <i class="ri-building-line align-bottom me-1"></i> 
                                                Checkers
                                            </li>
                                            <li class="list-inline-item">
                                                <i class="ri-map-pin-2-line align-bottom me-1"></i> 
                                                Port Elizabeth
                                            </li>
                                        </ul>
                                        <div class="hstack gap-2">
                                            <span class="badge bg-success-subtle text-success">
                                                Food Services
                                            </span>
                                            <span class="badge bg-danger-subtle text-danger">
                                                Assistance
                                            </span>
                                            <span class="badge bg-primary-subtle text-primary">
                                                Part Time
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-ghost-primary btn-icon custom-toggle active" data-bs-toggle="button">
                                            <span class="icon-on"><i class="ri-bookmark-line"></i></span>
                                            <span class="icon-off"><i class="ri-bookmark-fill"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-lg">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-info-subtle rounded">
                                            <img src="{{URL::asset('build/images/companies/img-8.png')}}" alt="" class="avatar-xxs">
                                        </div>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <a href="#!">
                                            <h5>General Assistant</h5>
                                        </a>
                                        <ul class="list-inline text-muted mb-3">
                                            <li class="list-inline-item">
                                                <i class="ri-building-line align-bottom me-1"></i> 
                                                LiquorShop
                                            </li>
                                            <li class="list-inline-item">
                                                <i class="ri-map-pin-2-line align-bottom me-1"></i> 
                                                Kimberley
                                            </li>
                                        </ul>
                                        <div class="hstack gap-2">
                                            <span class="badge bg-success-subtle text-success">
                                                Full Time
                                            </span>
                                            <span class="badge bg-info-subtle text-info">
                                                Assistance
                                            </span>
                                            <span class="badge bg-primary-subtle text-primary">
                                                Flexible
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-ghost-primary btn-icon custom-toggle active" data-bs-toggle="button">
                                            <span class="icon-on"><i class="ri-bookmark-line"></i></span>
                                            <span class="icon-off"><i class="ri-bookmark-fill"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-lg">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-warning-subtle rounded">
                                            <img src="{{URL::asset('build/images/companies/img-5.png')}}" alt="" class="avatar-xxs">
                                        </div>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <a href="#!">
                                            <h5>Packer</h5>
                                        </a>
                                        <ul class="list-inline text-muted mb-3">
                                            <li class="list-inline-item">
                                                <i class="ri-building-line align-bottom me-1"></i> 
                                                Shoprite
                                            </li>
                                            <li class="list-inline-item">
                                                <i class="ri-map-pin-2-line align-bottom me-1"></i> 
                                                Nelspruit
                                            </li>
                                        </ul>
                                        <div class="hstack gap-2">
                                            <span class="badge bg-success-subtle text-success">
                                                Warehouse
                                            </span>
                                            <span class="badge bg-danger-subtle text-danger">
                                                Stocking
                                            </span>
                                            <span class="badge bg-primary-subtle text-primary">
                                                Part Time
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-ghost-primary btn-icon custom-toggle" data-bs-toggle="button">
                                            <span class="icon-on"><i class="ri-bookmark-line"></i></span>
                                            <span class="icon-off"><i class="ri-bookmark-fill"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end find job -->

        <!-------------------------------------------------------------------------------------
            Candidates
        -------------------------------------------------------------------------------------->

        <!-- start candidates -->
        <section class="section bg-light" id="candidates">
            <div class="bg-overlay bg-overlay-pattern"></div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="text-center mb-5">
                            <h1 class="mb-3 ff-secondary fw-bold text-capitalize lh-base">
                                Meet Your Dedicated <span class="text-primary">Applicants</span>
                            </h1>
                            <p class="text-muted mb-4">
                                Explore our skilled team of hardworking individuals, ready to 
                                serve and bring excellence to their roles.
                            </p>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="swiper candidate-swiper">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div class="card text-center">
                                        <div class="card-body p-4">
                                            <img src="{{URL::asset('build/images/users/avatar-17.jpg')}}" alt="" class="rounded-circle avatar-md mx-auto d-block">
                                            <h5 class="fs-17 mt-3 mb-2">
                                                Thando Mkhize
                                            </h5>
                                            <p class="text-muted fs-13 mb-3">
                                                Packer
                                            </p>
                                            <p class="text-muted mb-4 fs-14">
                                                <i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i> 
                                                Pretoria, South Africa
                                            </p>
                                            <a href="#!" class="btn btn-secondary w-100">
                                                View Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="card text-center">
                                        <div class="card-body p-4">
                                            <img src="{{URL::asset('build/images/users/avatar-15.jpg')}}" alt="" class="rounded-circle avatar-md mx-auto d-block">
                                            <h5 class="fs-17 mt-3 mb-2">
                                                Lebo Moletsane
                                            </h5>
                                            <p class="text-muted fs-13 mb-3">
                                                General Assistant
                                            </p>
                                            <p class="text-muted mb-4 fs-14">
                                                <i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i> 
                                                Johannesburg, South Africa
                                            </p>
                                            <a href="#!" class="btn btn-secondary w-100">
                                                View Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="card text-center">
                                        <div class="card-body p-4">
                                            <img src="{{URL::asset('build/images/users/avatar-16.jpg')}}" alt="" class="rounded-circle avatar-md mx-auto d-block">
                                            <h5 class="fs-17 mt-3 mb-2">
                                                Siyabonga Ntuli
                                            </h5>
                                            <p class="text-muted fs-13 mb-3">
                                                Butcher/Meat Technician
                                            </p>
                                            <p class="text-muted mb-4 fs-14">
                                                <i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i> 
                                                Bloemfontein, South Africa
                                            </p>
                                            <a href="#!" class="btn btn-secondary w-100">
                                                View Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="card text-center">
                                        <div class="card-body p-4">
                                            <img src="{{URL::asset('build/images/users/avatar-12.jpg')}}" alt="" class="rounded-circle avatar-md mx-auto d-block" />
                                            <h5 class="fs-17 mt-3 mb-2">
                                                Zweli Dlamini
                                            </h5>
                                            <p class="text-muted fs-13 mb-3">
                                                Cashier                                                
                                            </p>
                                            <p class="text-muted mb-4 fs-14">
                                                <i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i> 
                                                Polokwane, South Africa
                                            </p>

                                            <a href="#!" class="btn btn-secondary w-100">
                                                View Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="card text-center">
                                        <div class="card-body p-4">
                                            <img src="{{URL::asset('build/images/users/avatar-13.jpg')}}" alt="" class="rounded-circle avatar-md mx-auto d-block" />
                                            <h5 class="fs-17 mt-3 mb-2">
                                                Nomsa Radebe
                                            </h5>
                                            <p class="text-muted fs-13 mb-3">
                                                Baker
                                            </p>
                                            <p class="text-muted mb-4 fs-14">
                                                <i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i> 
                                                Durban, South Africa
                                            </p>
                                            <a href="#!" class="btn btn-secondary w-100">
                                                View Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="card text-center">
                                        <div class="card-body p-4">
                                            <img src="{{URL::asset('build/images/users/avatar-11.jpg')}}" alt="" class="rounded-circle avatar-md mx-auto d-block" />
                                            <h5 class="fs-17 mt-3 mb-2">
                                                Themba Nxumalo
                                            </h5>
                                            <p class="text-muted fs-13 mb-3">
                                                Assistant
                                            </p>
                                            <p class="text-muted mb-4 fs-14">
                                                <i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i> 
                                                Port Elizabeth, South Africa
                                            </p>
                                            <a href="#!" class="btn btn-secondary w-100">
                                                View Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end container -->
        </section>
        <!-- end candidates -->

        <!-------------------------------------------------------------------------------------
            Blog
        -------------------------------------------------------------------------------------->

        <!-- start blog -->
        <section class="section" id="blog">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="text-center mb-5">
                            <h1 class="mb-3 ff-secondary fw-bold text-capitalize lh-base">
                                Our Latest <span class="text-primary">News</span></h1>
                            <p class="text-muted mb-4">
                                We thrive when coming up with innovative ideas but also understand that a 
                                smart concept should be supported with faucibus sapien odio measurable results.
                            </p>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <img src="{{URL::asset('build/images/small/img-8.jpg')}}" alt="" class="img-fluid rounded" />
                            </div>
                            <div class="card-body">
                                <ul class="list-inline fs-14 text-muted">
                                    <li class="list-inline-item">
                                        <i class="ri-calendar-line align-bottom me-1"></i> 
                                        30 Oct, 2022
                                    </li>
                                    <li class="list-inline-item">
                                        <i class="ri-message-2-line align-bottom me-1"></i> 
                                        364 Comments
                                    </li>
                                </ul>
                                <a href="javascript:void(0);">
                                    <h5> The Importance of On-The-Job Training</h5>
                                </a>
                                <p class="text-muted fs-14">
                                    On-the-job training has become a pivotal aspect for many 
                                    industries. Discover how it boosts employee retention and 
                                    enhances productivity in low-level jobs.
                                </p>

                                <div>
                                    <a href="#!" class="link-success">
                                        Learn More 
                                        <i class="ri-arrow-right-line align-bottom ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <img src="{{URL::asset('build/images/small/img-9.jpg')}}" alt="" class="img-fluid rounded" />
                            </div>
                            <div class="card-body">
                                <ul class="list-inline fs-14 text-muted">
                                    <li class="list-inline-item">
                                        <i class="ri-calendar-line align-bottom me-1"></i> 
                                        02 Oct, 2022
                                    </li>
                                    <li class="list-inline-item">
                                        <i class="ri-message-2-line align-bottom me-1"></i> 
                                        245 Comments
                                    </li>
                                </ul>
                                <a href="javascript:void(0);">
                                    <h5> Health and Safety Protocols in Retail</h5>
                                </a>
                                <p class="text-muted fs-14">
                                    Prioritizing health and safety in retail environments is essential. 
                                    Explore the protocols that are making a difference in today's 
                                    workplaces.
                                </p>

                                <div>
                                    <a href="#!" class="link-success">
                                        Learn More 
                                        <i class="ri-arrow-right-line align-bottom ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <img src="{{URL::asset('build/images/small/img-6.jpg')}}" alt="" class="img-fluid rounded" />
                            </div>
                            <div class="card-body">
                                <ul class="list-inline fs-14 text-muted">
                                    <li class="list-inline-item">
                                        <i class="ri-calendar-line align-bottom me-1"></i> 
                                        23 Sept, 2022
                                    </li>
                                    <li class="list-inline-item">
                                        <i class="ri-message-2-line align-bottom me-1"></i> 
                                        354 Comments
                                    </li>
                                </ul>
                                <a href="javascript:void(0);">
                                    <h5>Embracing Technology in Manual Labor</h5>
                                </a>
                                <p class="text-muted fs-14">
                                    Technology isn't just for office spaces. Understand how 
                                    manual labor sectors are benefiting from the latest tech 
                                    advancements.
                                </p>

                                <div>
                                    <a href="#!" class="link-success">
                                        Learn More 
                                        <i class="ri-arrow-right-line align-bottom ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- end container -->
        </section>
        <!-- end blog -->

        <!-------------------------------------------------------------------------------------
            Subscribe
        -------------------------------------------------------------------------------------->

        <!-- start cta -->
        <section class="py-5 bg-primary position-relative">
            <div class="bg-overlay bg-overlay-pattern opacity-50"></div>
            <div class="container">
                <div class="row align-items-center gy-4">
                    <div class="col-sm">
                        <div>
                            <h4 class="text-white fw-bold">
                                Get New Jobs Notifications!
                            </h4>
                            <p class="text-white text-opacity-75 mb-0">
                                Subscribe & get all related jobs notification.
                            </p>
                        </div>
                    </div>
                    <!-- end col -->
                    <div class="col-sm-auto">
                        <button class="btn btn-danger" type="button"  data-bs-toggle="modal" data-bs-target="#subscribeModal">
                            Subscribe Now 
                            <i class="ri-arrow-right-line align-bottom"></i>
                        </button>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </section>
        <!-- end cta -->

        <!-------------------------------------------------------------------------------------
            Subscribe Modal
        -------------------------------------------------------------------------------------->

        <div id="subscribeModal" class="modal fade" tabindex="-1" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-lg-7">
                            <div class="modal-body p-5">
                                <h2 class="lh-base">
                                    Stay in the Loop,<br>
                                    <span class="text-danger">
                                        Subscribe Now !
                                    </span>
                                </h2>
                                <p class="text-muted mb-4">
                                    Join our mailing list to keep abreast of the latest updates, 
                                    exclusive offers, and industry insights. Don't miss out on 
                                    the action and insights from Orient!
                                </p>
                                <form id="formSubscribe" enctype="multipart/form-data">
                                    @csrf
                                    <div class="input-group mb-3">                                        
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email" aria-label="Example text with button addon" aria-describedby="button-addon1">
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <div class="invalid-feedback">
                                            Please enter email
                                        </div>
                                        <button class="btn btn-primary" type="submit" id="button-addon1">Subscribe Now</button> 
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="subscribe-modals-cover h-100">
                                <img src="{{URL::asset('build/images/small/img-7.jpg')}}" alt="" class="h-100 w-100 object-fit-cover" style="clip-path: polygon(100% 0%, 100% 100%, 100% 100%, 0% 100%, 25% 50%, 0% 0%);">
                            </div>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <!-------------------------------------------------------------------------------------
            Footer
        -------------------------------------------------------------------------------------->

        <!-- Start footer -->
        <footer class="custom-footer bg-dark py-5 position-relative">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mt-4">
                        <div>
                            <div>
                                <img src="{{URL::asset('build/images/logo-light.png')}}" alt="logo light" height="17" />
                            </div>
                            <div class="mt-4 fs-15">
                                <p>
                                    Find jobs, create trackable resumes and enrich your 
                                    applications. Carefully crafted after analyzing the needs of 
                                    different industries.
                                </p>                                
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7 ms-lg-auto">
                        <div class="row">                            
                            <div class="col-sm-4 mt-4">
                                <h5 class="text-white mb-0">Legal</h5>
                                <div class="text-muted mt-3">
                                    <ul class="list-unstyled ff-secondary footer-list fs-15">
                                        <li><a href="{{ route('policy') }}">Privacy Policy</a></li>
                                        <li><a href="{{ route('terms') }}">Terms of Service</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-4 mt-4">
                                <h5 class="text-white mb-0">Support</h5>
                                <div class="text-muted mt-3">
                                    <ul class="list-unstyled ff-secondary footer-list fs-15">
                                        <li><a href="mailto:admin@tenutech.com?subject=Contact%20Inquiry">Contact</a></li>
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
                                    <a href="{{ route('policy') }}">Privacy Policy</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="{{ route('terms') }}">Terms & Conditions</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="{{ route('security') }}">Security</a>
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
