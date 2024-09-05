@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.signup')
@endsection
@section('content')

    <div class="auth-page-wrapper pt-5">
        <!-- auth page bg -->
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>

            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <!-- auth page content -->
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <a href="index" class="d-inline-block auth-logo">
                                    <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="20">
                                </a>
                            </div>
                            <p class="mt-3 fs-15 fw-medium">Crafting Leaders, Navigating Success.</p>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-8 col-xl-8">
                        <div class="card mt-4">

                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Create New Account</h5>
                                    <p class="text-muted">Get your free Orient account now</p>
                                </div>
                                <div class="p-2 mt-4">
                                    <form class="needs-validation form-steps" id="formRegister" validate method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <!-- First Name -->
                                                <div class="mb-3">
                                                    <label for="firstname" class="form-label">
                                                        First Name <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control @error('firstname') is-invalid @enderror" name="firstname" value="{{ e(old('firstname')) }}" id="firstname" placeholder="Enter first name" required>
                                                    @error('firstname')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ e($message) }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                    
                                                <!-- ID Number -->
                                                <div class="mb-3">
                                                    <label for="idNumber" class="form-label">
                                                        ID Number <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control @error('id_number') is-invalid @enderror" name="id_number" value="{{ e(old('id_number')) }}" id="idNumber" placeholder="Enter id number" required>
                                                    @error('id_number')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ e($message) }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                    
                                            <div class="col-lg-6"> 
                                                <!-- Last Name -->
                                                <div class="mb-3">
                                                    <label for="lastname" class="form-label">
                                                        Last Name <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control @error('lastname') is-invalid @enderror" name="lastname" value="{{ e(old('lastname')) }}" id="lastname" placeholder="Enter last name" required>
                                                    @error('lastname')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ e($message) }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                    
                                                <!-- Phone Number -->
                                                <div class="mb-3">
                                                    <label for="phone" class="form-label">
                                                        Phone Number <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group" data-input-flag>
                                                        <button class="btn btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <img src="{{ URL::asset('build/images/flags/za.svg') }}" alt="flag img" height="20" class="country-flagimg rounded">
                                                            <span class="ms-2 country-codeno">+ 27</span>
                                                        </button>
                                                        <input type="text" class="form-control rounded-end flag-input @error('phone') is-invalid @enderror" name="phone" value="{{ e(old('phone')) }}" id="phone" placeholder="Enter phone number" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^0+/, '').replace(/(\..*?)\..*/g, '$1');" required/>
                                                        @error('phone')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ e($message) }}</strong>
                                                            </span>
                                                        @enderror
                                                        <div class="dropdown-menu w-100">
                                                            <div class="p-2 px-3 pt-1 searchlist-input">
                                                                <input type="text" class="form-control form-control-sm border search-countryList" placeholder="Search country name or country code..." />
                                                            </div>
                                                            <ul class="list-unstyled dropdown-menu-list mb-0"></ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-12" id="guardianMobileContainer" style="display: none;">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">
                                                        Guardian Mobile Number
                                                    </label>
                                                    <p>
                                                        Our system has detected that you are under the age of 18, you will need the consent of your Legal <br>
                                                        guardian to apply to any of the roles.
                                                    </p>
                                                    <input type="tel" class="form-control" id="guardianMobile" placeholder="Enter guardian's mobile number">
                                                    @error('email')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ e($message) }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <!-- Email -->
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">
                                                        Email <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ e(old('email')) }}" id="email" placeholder="Enter email address" required>
                                                    @error('email')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ e($message) }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                    
                                            <div class="col-lg-6">
                                                <!-- Password -->
                                                <div class="mb-2">
                                                    <label for="password" class="form-label">
                                                        Password <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="position-relative auth-pass-inputgroup mb-3">
                                                        <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror" name="password" id="password" placeholder="Enter password" autocomplete="off" required>
                                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon">
                                                            <i class="ri-eye-fill align-middle"></i>
                                                        </button>
                                                        @error('password')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ e($message) }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                    
                                            <div class="col-lg-6">
                                                <!-- Confirm Password -->
                                                <div class=" mb-4">
                                                    <label for="input-password">
                                                        Confirm Password <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="position-relative auth-pass-inputgroup mb-3">
                                                        <input type="password" class="form-control pe-5 password-input @error('password_confirmation') is-invalid @enderror" name="password_confirmation" id="input-password" placeholder="Enter confirm password" autocomplete="off" required>
                                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon-2">
                                                            <i class="ri-eye-fill align-middle"></i>
                                                        </button>
                                                        <div class="form-floating-icon">
                                                            <i data-feather="lock"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    
                                            <div class="col-lg-12">
                                                <!-- Profile Picture -->
                                                <div class=" mb-4">
                                                    <label for="input-avatar">Profile Picture</label>
                                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" name="avatar" id="input-avatar" accept=".jpg, .jpeg, .png">
                                                    @error('avatar')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ e($message) }}</strong>
                                                        </span>
                                                    @enderror
                                                    <div class="">
                                                        <i data-feather="file"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    
                                        <div class="mb-4">
                                            <p class="mb-0 fs-12 text-muted fst-italic">
                                                By registering you agree to the Orient
                                                <a href="{{ route('terms') }}" class="text-primary text-decoration-underline fst-normal fw-medium">
                                                    Terms of Use
                                                </a>
                                            </p>
                                        </div>
                                    
                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Sign Up</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->

                        <div class="mt-4 text-center">
                            <p class="mb-0">Already have an account ? <a href="auth-signin-basic"
                                    class="fw-semibold text-primary text-decoration-underline"> Signin </a> </p>
                        </div>

                    </div>
                </div>
                <!-- end row -->
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">
                                &copy; 
                                <script>document.write(new Date().getFullYear())</script> 
                                Orient. Crafted by OTB Group
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->

@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/form-registration.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
    <!-- input flag init -->
    <script src="{{URL::asset('build/js/pages/flag-input.init.js')}}"></script>
@endsection