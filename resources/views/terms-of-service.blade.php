@extends('layouts.master-without-nav')
@section('content')
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
                        <a class="nav-link fs-16 active" href="/#hero">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-16" href="/#process">Process</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-16" href="/#categories">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-16" href="/#findJob">Find Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-16" href="/#candidates">Candidates</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-16" href="/#blog">Blog</a>
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

    <section class="d-flex align-items-center" style="padding-top:80px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="bg-primary-subtle position-relative">
                        <div class="card-body p-5">
                            <div class="text-center">
                                <h3>
                                    Term & Conditions
                                </h3>
                                <p class="mb-0 text-muted">
                                    Last update: 16 Sept, 2023
                                </p>
                            </div>
                        </div>
                        <div class="shape">
                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                                xmlns:svgjs="http://svgjs.com/svgjs" width="1440" height="60" preserveAspectRatio="none"
                                viewBox="0 0 1440 60">
                                <g mask="url(&quot;#SvgjsMask1001&quot;)" fill="none">
                                    <path d="M 0,4 C 144,13 432,48 720,49 C 1008,50 1296,17 1440,9L1440 60L0 60z"
                                        style="fill: var(--vz-secondary-bg);"></path>
                                </g>
                                <defs>
                                    <mask id="SvgjsMask1001">
                                        <rect width="1440" height="60" fill="#ffffff"></rect>
                                    </mask>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div>
                            <h5>
                                Welcome to Orient!
                            </h5>
                            <p class="text-muted">
                                These terms and conditions define the guidelines for using 
                                Orient's Website, located at 
                                https://www.opportunitybridge.co/.
                            </p>
                            <p class="text-muted">
                                By navigating our website, you acknowledge and agree to abide by 
                                these terms and conditions in full. If you do not concur with any 
                                of these terms, we advise not to use Orient's platform.
                            </p>
                            <p class="text-muted">
                                Our platform, like many interactive websites, uses cookies to 
                                remember user details across visits. These cookies enhance user 
                                functionality and are essential for certain areas of our website. 
                                Moreover, some of our partners or advertisers might use cookies 
                                as well.
                            </p>
                        </div>
        
                        <div>
                            <h5>
                                License
                            </h5>
                            <p class="text-muted">
                                All materials on Orient, unless otherwise noted, 
                                are protected by copyright laws. Orient and its 
                                licensors own the intellectual property rights for all these 
                                materials. Users are granted limited license only for purposes 
                                of viewing the material.
                            </p>
                            <p class="text-muted">
                                The following actions are strictly prohibited:
                            </p>
                            <ul class="text-muted vstack gap-2">
                                <li>
                                    Republishing material from Orient without proper 
                                    attribution.
                                </li>
                                <li>
                                    Selling, renting, or sub-licensing material from Opportunity 
                                    Bridge.
                                </li>
                                <li>
                                    Reproducing, duplicating, or copying material from Opportunity 
                                    Bridge for commercial purposes.
                                </li>
                                <li>
                                    Redistributing content from Orient without prior 
                                    consent.
                                </li>
                            </ul>
                            <p class="text-muted">
                                This Agreement becomes effective from your first visit to the 
                                website.
                            </p>
                            <p class="text-muted">
                                Certain sections of our website allow users to post comments, 
                                share experiences, or provide feedback. Orient does 
                                not necessarily review these comments before they are displayed. 
                                The opinions in the comments section belong solely to the users 
                                and do not represent Orient's stance or views.
                            </p>
                        </div>
        
                        <div>
                            <p class="text-muted">
                                Orient maintains the authority to oversee all 
                                comments and has the discretion to delete or edit any comments 
                                deemed inappropriate, offensive, or in breach of these 
                                Terms and Conditions.
                            </p>
                            <p class="text-muted">
                                When you post comments, you ensure that:
                            </p>
                            <ul class="text-muted vstack gap-2">
                                <li>
                                    You have the legal right and necessary permissions to share 
                                    the comments on our platform.
                                </li>
                                <li>
                                    Your comments do not infringe on any third party's intellectual 
                                    property rights, including but not limited to copyrights, 
                                    patents, or trademarks.
                                </li>
                                <li>
                                    Your comments do not contain any content that could be 
                                    considered libelous, offensive, or illegal, and do not invade 
                                    anyone's privacy.
                                </li>
                                <li>
                                    Your comments are not intended to solicit business, endorse 
                                    commercial endeavors, or promote illegal activities.
                                </li>
                            </ul>
                            <p class="text-muted">
                                By posting comments, you provide Orient a non-exclusive 
                                license to use, reproduce, modify, and distribute these comments 
                                across various media channels.
                            </p>
                            <p class="text-muted">
                                Certain organizations, upon approval, may hyperlink to our 
                                website in the following ways:
                            </p>
                            <ul class="text-muted vstack gap-2">
                                <li>
                                    Using our corporate name: Orient.
                                </li>
                                <li>
                                    By direct use of our website URL: 
                                    https://www.opportunitybridge.co/.
                                </li>
                                <li>
                                    Using a relevant description related to our website, which 
                                    aligns with the content on the linking party's website.
                                </li>
                            </ul>
                            <p class="text-muted fw-semibold">
                                Please note that without a formal agreement, usage of 
                                Orient's logo or other graphics for linking is 
                                strictly prohibited.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
