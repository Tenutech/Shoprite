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
                                    $url = '/';
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
                                    Privacy Policy
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
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <i data-feather="check-circle" class="text-primary icon-dual-primary icon-xs"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5>Privacy Policy for Orient</h5>
                                <p class="text-muted">
                                    At Orient, accessible at 
                                    https://www.opportunitybridge.co/, we deeply value and respect 
                                    the privacy of our members and visitors. Our Privacy Policy 
                                    outlines the kinds of data we collect, record, and how we use 
                                    it to improve your experience.
                                </p>
                                <p class="text-muted">
                                    Should you need more clarity or have any inquiries about our 
                                    Privacy Policy, please don't hesitate to contact us at 
                                    info@opportunitybridge.co.
                                </p>
                                <p class="text-muted">
                                    Please note that this Privacy Policy pertains exclusively to activities on 
                                    Orient's online platform. It doesn't encompass information gathered 
                                    offline or via other channels.
                                </p>
                                <p class="text-muted">
                                    <b>Understanding the Data We Collect:</b>
                                </p>
                                <ul class="text-muted">
                                    <li>
                                        <p>
                                            <b>**User-Provided Data**:</b> This includes all the data you directly provide 
                                            to us, whether it's your name, email address, profile details, or any 
                                            other personal information necessary for the smooth operation of our 
                                            platform.
                                        </p>
                                    </li>
                                    <li>
                                        <p>
                                            <b>**Log and Usage Data**:</b> Like most digital platforms, we gather data sent 
                                            by your browser when you visit our site. This can encompass your IP 
                                            address, browser type, the pages you visited, the time and date of your 
                                            visit, the time spent on those pages, and other diagnostic data.
                                        </p>
                                    </li>
                                    <li>
                                        <p>
                                            <b>**Cookies and Tracking Data**:</b> Cookies help us enhance the user experience 
                                            on our site. They allow us to remember user preferences, tailor content, 
                                            and more effectively manage and optimize our platform. 
                                        </p>
                                    </li>
                                    <li>
                                        <p>
                                            <b>**Communication Data**:</b> This includes emails, messages, or any other 
                                            forms of communication established through our platform.
                                        </p>
                                    </li>
                                </ul>

                                <p class="text-muted">
                                    <b>How and Why We Use Your Data:</b>
                                </p>
                                <ul class="text-muted">
                                    <li>
                                        <p>
                                            <b>**Enhancing User Experience**:</b> The primary reason for collecting your 
                                            data is to improve and customize your experience on Orient. 
                                            By understanding user preferences and behavior, we can tailor content, 
                                            showcase relevant opportunities, and ensure smoother site navigation.
                                        </p>
                                    </li>
                                    
                                    <li>
                                        <p>
                                            <b>**Security Measures**:</b> Your safety is paramount. We use collected data 
                                            to safeguard against unauthorized access, detect potential threats, 
                                            and ensure the integrity of user data.
                                        </p>
                                    </li>
                                    
                                    <li>
                                        <p>
                                            <b>**Feedback and Updates**:</b> Occasionally, we may use your contact 
                                            information to provide updates about our platform, policies, or to 
                                            gather feedback to improve our services.
                                        </p>
                                    </li>
                            
                                    <li>
                                        <p>
                                            <b>**Service Expansion**:</b> By analyzing usage patterns, we can identify 
                                            areas of potential growth, develop new features, and optimize existing 
                                            services for our diverse user base.
                                        </p>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <i data-feather="check-circle" class="text-primary icon-dual-primary icon-xs"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5>Collecting and Using Your Information</h5>
                                <p class="text-muted">
                                    Your trust is of utmost importance to us. We collect information to provide better 
                                    services to all our users, ensuring transparency and trustworthiness at every step.
                                </p>
                                <p class="text-muted">
                                    Here's a look into how we might utilize the information we collect:
                                </p>
                                <ul class="text-muted vstack gap-2">
                                    <li>Ensure smooth functioning and optimization of our website.</li>
                                    <li>Enhance your user experience and personalize content.</li>
                                    <li>Analyze website usage patterns for research and development purposes.</li>
                                    <li>Introduce new tools and offerings based on user preferences.</li>
                                    <li>Stay connected with our users through periodic emails.</li>
                                    <li>Ensure the safety and security of our platform by preventing fraudulent activities.</li>
                                </ul>
                                <p class="text-muted">
                                    Similar to other platforms, Orient employs 'cookies'. 
                                    These cookies help enrich user experiences by remembering preferences and session 
                                    information.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <i data-feather="check-circle" class="text-primary icon-dual-primary icon-xs"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5>
                                    Third-Party Involvements
                                </h5>
                                <p class="text-muted">
                                    ur platform may engage with third-party entities for advertising. 
                                    These parties might use cookies, which are beyond our control. 
                                    While we choose our partners with care, we recommend users to remain 
                                    informed.
                                </p>
                                <p class="text-muted">
                                    <b>
                                        Note: Orient's Privacy Policy does not extend to 
                                        third-party advertisers or other websites. We urge users to 
                                        acquaint themselves with the individual privacy policies of 
                                        these third-party entities for a more detailed understanding.
                                    </b>
                                </p>
                            </div>
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
