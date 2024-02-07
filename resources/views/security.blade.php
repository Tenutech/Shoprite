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
                                    Security
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
                        <!-- 1. Introduction -->
                        <div>
                            <h5>
                                Introduction
                            </h5>
                            <p class="text-muted">
                                At Orient, the security of our users' information 
                                and the integrity of our marketplace are paramount. We are 
                                committed to employing the best practices and state-of-the-art 
                                security measures to ensure a safe environment for all parties 
                                involved.
                            </p>
                        </div>
                    
                        <!-- 2. Data Protection -->
                        <div>
                            <h5>
                                Data Protection
                            </h5>
                            <p class="text-muted">
                                Encryption: All sensitive data stored in our databases, 
                                including personal information and transaction details, is 
                                encrypted using advanced encryption algorithms.
                            </p>
                            <p class="text-muted">
                                Data Transmission: Secure Sockets Layer (SSL) technology is 
                                employed for the secure transfer of data between the client 
                                and the server.
                            </p>
                        </div>
                    
                        <!-- 3. User Authentication -->
                        <div>
                            <h5>
                                User Authentication
                            </h5>
                            <p class="text-muted">
                                Strong Password Policy: Users are encouraged to create strong 
                                passwords, which must be a combination of letters, numbers, 
                                and special characters.
                            </p>
                            <p class="text-muted">
                                Two-Factor Authentication (2FA): To provide an additional 
                                layer of security, users have the option to enable 2FA for 
                                their accounts.
                            </p>
                        </div>
                    
                        <!-- 4. System and Application Security -->
                        <div>
                            <h5>
                                System and Application Security
                            </h5>
                            <p class="text-muted">
                                Regular Patching: We regularly update and patch our Laravel 
                                framework and associated packages to ensure any known 
                                vulnerabilities are addressed promptly.
                            </p>
                            <p class="text-muted">
                                Code Review: All code, especially dealing with user data, 
                                undergoes a rigorous review process to ensure no security 
                                loopholes exist.
                            </p>
                            <p class="text-muted">
                                Admin Approval Process: All investment opportunities posted 
                                are subject to approval by our admin team, ensuring the quality 
                                and legitimacy of opportunities available on our platform.
                            </p>
                        </div>
                    
                        <!-- 5. Network Security -->
                        <div>
                            <h5>
                                Network Security
                            </h5>
                            <p class="text-muted">
                                Firewalls: We utilize advanced firewall technologies to 
                                block unauthorized access and malicious traffic.
                            </p>
                            <p class="text-muted">
                                Intrusion Detection Systems (IDS): Our IDS monitors our 
                                network for suspicious activities and takes predefined actions 
                                in response to any threats.
                            </p>
                        </div>
                    
                        <!-- 6. Incident Response -->
                        <div>
                            <h5>
                                Incident Response
                            </h5>
                            <p class="text-muted">
                                In the event of a security breach or incident, we have an Incident 
                                Response Team in place to address and manage the situation promptly. 
                                Users will be notified of any breaches that might affect their 
                                personal data.
                            </p>
                        </div>
                    
                        <!-- 7. Continuous Monitoring -->
                        <div>
                            <h5>
                                Continuous Monitoring
                            </h5>
                            <p class="text-muted">
                                We continuously monitor our systems for any vulnerabilities. 
                                Regular security audits and penetration testing are conducted 
                                to ensure the robustness of our security measures.
                            </p>
                        </div>
                    
                        <!-- 8. User Responsibilities -->
                        <div>
                            <h5>
                                User Responsibilities
                            </h5>
                            <p class="text-muted">
                                Confidentiality: Users are reminded to keep their account details, 
                                especially passwords, confidential and not share them with others.
                            </p>
                            <p class="text-muted">
                                Reporting: We encourage our user community to report any 
                                suspicious activities or perceived vulnerabilities in our system. 
                                Reports can be sent to security@opportunitybridge.co.
                            </p>
                        </div>
                    
                        <!-- 9. Third-party Integrations -->
                        <div>
                            <h5>
                                Third-party Integrations
                            </h5>
                            <p class="text-muted">
                                Any third-party integrations or plugins are reviewed for security 
                                compliance before integration into our platform.
                            </p>
                        </div>
                    
                        <!-- 10. Conclusion -->
                        <div>
                            <h5>
                                Conclusion
                            </h5>
                            <p class="text-muted">
                                Security is an ongoing commitment at Orient. 
                                We are devoted to ensuring that our marketplace remains a 
                                trusted environment for all users.
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
