@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection
@section('css')
<link href="{{ URL::asset('build/libs/jsvectormap/css/jsvectormap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/css/custom.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')


<div class="row mt-5">
    @if ($user->applicant)
        <div class="col">
            <div class="h-100 text-center">
                <div class="row mb-3 pb-1">
                    <div class="col-12">
                        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                            <div class="flex-grow-1">
                                <h1 class="display-2 coming-soon-text mb-4">
                                    Thank You
                                </h1>
                                <p class="fs-16 text-muted mb-0">
                                    We are currently reviewing your application. If you qualify for the next steps, you will receive an invitation for an interview. Please keep an eye on your email and WhatsApp for further updates.
                                </p>
                                <p class="fs-16 text-muted mb-0">
                                    Should you have any questions or require assistance, feel free to reach out. We're here to help!
                                </p>
                            </div>
                        </div><!-- end card header -->
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div><!-- end h-100 -->
        </div><!-- end col -->

    @else

        <!-------------------------------------------------------------------------------------
            Application Form
        -------------------------------------------------------------------------------------->

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        {{ $user->applicant ? 'Update' : 'Post' }} Your Application
                    </h4>
                </div><!-- end card header -->
                <div class="card-body form-steps">
                    <form class="vertical-navs-step" id="{{ $user->applicant ? 'formApplicationUpdate' : 'formApplication' }}"  enctype="multipart/form-data" novalidate>
                        @csrf
                        <input type="hidden" id="id" name="id" value="{{ $user->applicant ? Crypt::encryptString($user->applicant->id) : '' }}"/>
                        <div class="row gy-5">

                            <!-------------------------------------------------------------------------------------
                                Navigation Links
                            -------------------------------------------------------------------------------------->

                            <div class="col-lg-3">
                                <div class="nav flex-column custom-nav nav-pills" role="tablist" aria-orientation="vertical">
                                    <button class="nav-link active" id="v-pills-welcome-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-welcome" type="button" role="tab"
                                        aria-controls="v-pills-welcome" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Welcome:
                                        </span>
                                        Employment Application
                                    </button>
                                    <button class="nav-link" id="v-pills-personal-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-personal" type="button" role="tab"
                                        aria-controls="v-pills-personal" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Section 1:
                                        </span>
                                        Personal Information
                                    </button>
                                    <button class="nav-link" id="v-pills-job-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-job" type="button" role="tab"
                                        aria-controls="v-pills-job" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Section 2:
                                        </span>
                                        Job Information
                                    </button>
                                    <button class="nav-link" id="v-pills-literacy-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-literacy" type="button" role="tab"
                                        aria-controls="v-pills-literacy" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Section 3:
                                        </span>
                                        Literacy Assessment
                                    </button>
                                    <button class="nav-link" id="v-pills-numeracy-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-numeracy" type="button" role="tab"
                                        aria-controls="v-pills-numeracy" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Section 4:
                                        </span>
                                        Numeracy Assessment
                                    </button>
                                    <button class="nav-link" id="v-pills-situational-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-situational" type="button" role="tab"
                                        aria-controls="v-pills-situational" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Section 5:
                                        </span>
                                        Situational Assessment
                                    </button>
                                    <button class="nav-link" id="v-pills-finish-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-finish" type="button" role="tab"
                                        aria-controls="v-pills-finish" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Section 6:
                                        </span>
                                        Finish
                                    </button>
                                </div>
                                <!-- end nav -->
                            </div> <!-- end col-->

                            <!-------------------------------------------------------------------------------------
                                Navigation Tabs
                            -------------------------------------------------------------------------------------->

                            <div class="col-lg-9">
                                <div class="px-lg-4">
                                    <div class="tab-content">

                                        <!-------------------------------------------------------------------------------------
                                            Welcome
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade show active" id="v-pills-welcome" role="tabpanel" aria-labelledby="v-pills-welcome-tab">
                                            <div class="text-center pt-4 pb-2" id="welcome">
                                                <div class="mb-4">
                                                    <lord-icon src="https://cdn.lordicon.com/xzalkbkz.json" trigger="loop" state="hover-2" 
                                                            colors="primary:#0ab39c,secondary:#405189" style="width:120px;height:120px"></lord-icon>
                                                </div>
                                                
                                                <h5 class="mb-3">Welcome to the Shoprite Employment Application!</h5>
                                                
                                                <p class="text-muted">
                                                    You are applying for a position at the Shoprite Group. Your answers will replace the need 
                                                    to submit a physical CV. Please set aside 30 minutes to complete the process.
                                                </p>
                                                
                                                <p class="text-muted">
                                                    By proceeding, you consent to the processing of your personal information in accordance 
                                                    with the Protection of Personal Information Act (POPIA) and accept our 
                                                    <a href="{{ route('terms') }}" class="text-primary text-decoration-underline fst-normal fw-medium" target="_blank">
                                                        Terms of Use
                                                    </a>.
                                                </p>
                                                
                                                <p class="text-muted">
                                                    The Shoprite Group reserves the right to conduct Credential Verification Checks, 
                                                    including Criminal Checks. Do you give consent for these checks?
                                                </p>
                                            
                                                <p class="text-muted">
                                                    By continuing this application, you acknowledge and consent to our 
                                                    <a href="https://bit.ly/srtscsnew" class="text-primary text-decoration-underline fst-normal fw-medium" target="_blank">
                                                        Terms and Conditions
                                                    </a>.
                                                </p>
                                            
                                                <!-- Centered Label and Checkbox -->
                                                <div class="text-center mt-3">
                                                    <!-- Label above checkbox -->
                                                    <label class="form-check-label mb-2" for="consent">
                                                        I agree to the Terms and Conditions, and I consent to the Credential Verification Checks.
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    
                                                    <!-- Larger centered checkbox on a new line -->
                                                    <div>
                                                        <input class="form-check-input" type="checkbox" id="consent" name="consent" required style="transform: scale(1.5);">
                                                        <div class="invalid-feedback">
                                                            Please agree to the T's & C's
                                                        </div>
                                                    </div>
                                                </div>
                                            
                                                <!-- Start button -->
                                                <div class="mt-4">
                                                    <button type="button" class="btn btn-secondary btn-label waves-effect waves-light rounded-pill nexttab" 
                                                            data-nexttab="v-pills-personal-tab">
                                                        <i class="ri-play-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        Start!
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            Personal Information
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade" id="v-pills-personal" role="tabpanel" aria-labelledby="v-pills-personal-tab">
                                            <div>
                                                <h5>Personal Information</h5>
                                                <p class="text-muted">
                                                    Provide your basic details to help us understand your background better.
                                                </p>
                                            </div>

                                            <div>
                                                <div class="row gy-3">
                                                    <div class="col-lg-12 mb-3">
                                                        <div class="mb-3">
                                                            <div class="position-relative d-inline-block">
                                                                <div class="position-absolute  bottom-0 end-0">
                                                                    <label for="avatar" class="mb-0"  data-bs-toggle="tooltip" data-bs-placement="right" title="Select Image">
                                                                        <div class="avatar-xs cursor-pointer">
                                                                            <div class="avatar-title bg-light border rounded-circle text-muted">
                                                                                <i class="ri-image-fill"></i>
                                                                            </div>
                                                                        </div>
                                                                    </label>
                                                                    <input class="form-control d-none" value="" id="avatar" name="avatar" type="file" accept="image/png, image/gif, image/jpeg">
                                                                </div>
                                                                <div class="avatar-xg p-1">
                                                                    <div class="avatar-title bg-light rounded-circle">
                                                                        <img src="{{ URL::asset($user->applicant ? ($user->applicant->avatar ? $user->applicant->avatar : 'images/avatar.jpg') : ($user->avatar ? 'images/'.$user->avatar : 'images/avatar.jpg')) }}" alt="" id="preview" class="avatar-lg rounded-circle object-cover" >
                                                                    </div>
                                                                </div>
                                                            </div>                                                        
                                                        </div>
                                                        <label for="avatar" class="form-label">
                                                            Please upload a clear picture of your South African ID. (Max 5MB)
                                                            <span class="badge bg-secondary-subtle text-secondary badge-border">
                                                                Optional
                                                            </span>
                                                        </label>                                                  
                                                    </div>

                                                    <!-- Fistname -->
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="firstname" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please provide your first name(s) as per your ID.">
                                                                First name(s) as per your ID:
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Enter first name(s)" value="{{ $user->applicant ? $user->applicant->firstname : ($user->firstname ? $user->firstname : '') }}" readonly />
                                                            <div class="invalid-feedback">
                                                                Please enter your firstname(s)!
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Lastname -->
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="lastname" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please provide your surname as per your ID.">
                                                                Surname as per your ID:
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Enter last name(s)" value="{{ $user->applicant ? $user->applicant->lastname : ($user->lastname ? $user->lastname : '') }}" readonly />
                                                            <div class="invalid-feedback">
                                                                Please enter your surname!
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- ID Number -->
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="idNumber" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please provide your South African ID number.">
                                                                ID number
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" class="form-control" id="idNumber" name="id_number" placeholder="Enter ID number" value="{{ $user->applicant ? $user->applicant->id_number : ($user->id_number ? $user->id_number : '') }}" readonly />
                                                            <div class="invalid-feedback">
                                                                Please enter your ID number!
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Phone Number -->
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="phone" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please provide the phone number that we can contact you via WhatsApp.">
                                                                Phone Number 
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <div class="input-group" data-input-flag>
                                                                <button class="btn btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <img src="{{URL::asset('build/images/flags/za.svg')}}" alt="flag img" height="20" class="country-flagimg rounded">
                                                                    <span class="ms-2 country-codeno" id="phoneCountry">+ 27</span>
                                                                </button>
                                                                <input type="text" class="form-control rounded-end flag-input" id="phone" name="phone" placeholder="Enter phone number" value="{{ $user->applicant ? ltrim(str_replace('+27', '', $user->applicant->phone), '0') : ($user->phone ? ltrim(str_replace('+27', '', $user->phone), '0') : '') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^0+/, '').replace(/(\..*?)\..*/g, '$1');" required/>
                                                                <div class="invalid-feedback">
                                                                    Please enter your phone number!
                                                                </div>
                                                                <div class="dropdown-menu w-100">
                                                                    <div class="p-2 px-3 pt-1 searchlist-input">
                                                                        <input type="text" class="form-control form-control-sm border search-countryList" placeholder="Search country name or country code..." data-exclude-validation />
                                                                    </div>
                                                                    <ul class="list-unstyled dropdown-menu-list mb-0"></ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Address -->
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <label for="location" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What is your current residential address where you stay/live 🏡? Please type every detail. (e.g. street number, street name, suburb, town, postal code).">
                                                                Address
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" class="form-control" id="location" name="location" placeholder="Enter physical address" value="{{ $user->applicant ? $user->applicant->location : ($user->address ? $user->address : '') }}" required />
                                                            <div class="invalid-feedback">
                                                                Please enter your physical address!
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Ethnicity -->
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="race" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What is your ethnicity/race?">
                                                                Ethnicity
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-control" id="race" name="race_id" data-choices data-choices-search-false required>
                                                                <option value="">Select ethnicity</option>
                                                                @foreach ($races as $race)
                                                                    <option value="{{ $race->id }}" {{ ($user->applicant && $user->applicant->race_id == $race->id) ? 'selected' : '' }}>{{ $race->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">Please select your ethnicity!</div>
                                                        </div>                                                        
                                                    </div>

                                                    <!-- Email -->
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="email" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="If you have one, please provide your email address:">
                                                                Email
                                                                <span class="badge bg-secondary-subtle text-secondary badge-border">
                                                                    Optional
                                                                </span>
                                                            </label>
                                                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" value="{{ $user->applicant ? $user->applicant->email : ($user->email ? $user->email : '') }}" />
                                                        </div>                                                        
                                                    </div>                                                
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button" class="btn btn-light btn-label previestab"
                                                    data-previous="v-pills-welcome-tab">
                                                    <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                    Back
                                                </button>
                                                <button type="button"
                                                    class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                    data-nexttab="v-pills-job-tab">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Continue
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            Job Information
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade" id="v-pills-job" role="tabpanel" aria-labelledby="v-pills-job-tab">
                                            <div>
                                                <h5>Job Information</h5>
                                                <p class="text-muted">
                                                    Please provide details regarding your job preferences and experience.
                                                </p>
                                            </div>

                                            <div>
                                                <div class="row gy-3">
                                                    
                                                    <!-- Education -->
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="education" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What is your highest completed educational qualification 🎓?">
                                                                Highest Qualification
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-control" id="education" name="education_id" data-choices data-choices-search-false required>
                                                                <option value="">Select education Level</option>
                                                                @foreach ($educations as $education)
                                                                    <option value="{{ $education->id }}" {{ ($user->applicant && $user->applicant->education_id == $education->id) ? 'selected' : '' }}>{{ $education->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">Please select an qualification option!</div>
                                                        </div>                                                        
                                                    </div>

                                                    <!-- Experience Duration -->
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="duration" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="How much experience do you have in a retail environment?">
                                                                Retail Experience
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-control" id="duration" name="duration_id" data-choices data-choices-search-false>
                                                                <option value="">Select duration</option>
                                                                @foreach ($durations as $duration)
                                                                    <option value="{{ $duration->id }}" {{ ($user->applicant && $user->applicant->duration_id == $duration->id) ? 'selected' : '' }}>{{ $duration->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">Please select an experience option!</div>
                                                        </div>                                                        
                                                    </div>

                                                    <!-- Public Holidays -->
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <label for="publicHolidays" class="form-label">
                                                                Are you prepared to work on a rotational shift basis that may include Sundays and public holidays?
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-control" id="publicHolidays" name="public_holidays" data-choices data-choices-search-false required>
                                                                <option value="">Select your answer</option>
                                                                <option value="Yes" {{ ($user->applicant && $user->applicant->public_holidays == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                                <option value="No" {{ ($user->applicant && $user->applicant->public_holidays == 'No') ? 'selected' : '' }}>No</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                Please select your answer!
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Environment -->
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <label for="environment" class="form-label">
                                                                Are you willing to work in an environment that may involve heavy lifting, cold areas, or standing for long periods?
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-control" id="environment" name="environment" data-choices data-choices-search-false required>
                                                                <option value="">Select your answer</option>
                                                                <option value="Yes" {{ ($user->applicant && $user->applicant->environment == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                                <option value="No" {{ ($user->applicant && $user->applicant->environment == 'No') ? 'selected' : '' }}>No</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                Please select your answer!
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Brand -->
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="brand" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Which type of store would you like to work at and be considered for?">
                                                                Type of Store
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-control" id="brands" name="brands[]" data-choices multiple data-choices-search-true data-choices-removeItem required>
                                                                <option value="">Select brand</option>
                                                                @foreach ($brands as $brand)
                                                                    <option value="{{ $brand->id }}" {{ $user->applicant && in_array($brand->id, array_column($user->applicant->brands->toArray(), 'id')) ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">Please select a store type!</div>
                                                        </div>                                                        
                                                    </div>

                                                    <!-- Disability -->
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="disability" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Do you have a disability? This will not affect your application status.">
                                                                Disability
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-control" id="disability" name="disability" data-choices data-choices-search-true required>
                                                                <option value="">Select your answer</option>
                                                                <option value="Yes" {{ ($user->applicant && $user->applicant->disability == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                                <option value="No" {{ ($user->applicant && $user->applicant->disability == 'No') ? 'selected' : '' }}>No</option>
                                                            </select>
                                                            <div class="invalid-feedback">Please select a answer!</div>
                                                        </div>                                                        
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button" class="btn btn-light btn-label previestab"
                                                    data-previous="v-pills-personal-tab">
                                                    <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                    Back
                                                </button>
                                                <button type="button"
                                                    class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                    data-nexttab="v-pills-literacy-tab">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Continue
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            Literacy Assessment
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade" id="v-pills-literacy" role="tabpanel" aria-labelledby="v-pills-literacy-tab">
                                            <div>
                                                <h5>Literacy Assessment</h5>
                                                <p class="text-muted">
                                                    This assessment assesses your reading comprehension, writing skills, and ability to communicate effectively. 
                                                    It's an opportunity to demonstrate your proficiency in understanding and using written language in a workplace context.
                                                </p>
                                            </div>

                                            <div>
                                                <div class="row gy-3">
                                                    @php $literacyQuestionNumber = 1; @endphp <!-- Initialize a literacy question number counter outside the loop -->

                                                    @foreach ($literacyQuestions as $question)
                                                        @php
                                                            // Find the position of the first question mark or line break
                                                            $firstQuestionMarkPos = strpos($question->message, '?');
                                                            $firstLineBreakPos = strpos($question->message, "\n");

                                                            // Determine where to insert the <span> based on your preference
                                                            $insertPos = $firstQuestionMarkPos !== false ? $firstQuestionMarkPos + 1 : $firstLineBreakPos;

                                                            // Insert the <span> tag
                                                            if ($insertPos !== false) {
                                                                $question->message = substr_replace($question->message, ' <span class="text-danger">*</span>', $insertPos, 0);
                                                            }

                                                            // Convert line breaks to <br> tags without escaping the message
                                                            $formattedMessage = nl2br($question->message);
                                                        @endphp

                                                        <div class="col-md-12">
                                                            <div class="mb-3">
                                                                <label for="literacyQuestion-{{ $question->id }}" class="form-label">
                                                                    {{ $literacyQuestionNumber }}.) {!! $formattedMessage !!} <!-- Display the literacy question number -->
                                                                </label>
                                                                <select class="form-control" id="literacyQuestion-{{ $question->id }}" name="literacy_answers[{{ $question->id }}]" data-choices data-choices-search-false required>
                                                                    <option value="">Select an option</option>
                                                                    <option value="a" selected>a</option>
                                                                    <option value="b">b</option>
                                                                    <option value="c">c</option>
                                                                    <option value="d">d</option>
                                                                    <option value="e">e</option>
                                                                </select>
                                                                <div class="invalid-feedback">Please select an option</div>
                                                            </div>                                                        
                                                        </div>
                                                        @php $literacyQuestionNumber++; @endphp <!-- Increment the literacy question number for the next iteration -->
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button" class="btn btn-light btn-label previestab"
                                                    data-previous="v-pills-job-tab">
                                                    <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                    Back
                                                </button>
                                                <button type="button"
                                                    class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                    data-nexttab="v-pills-numeracy-tab">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Continue
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            Numeracy Assessment
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade" id="v-pills-numeracy" role="tabpanel" aria-labelledby="v-pills-numeracy-tab">
                                            <div>
                                                <h5>Numerical Ability Assessment</h5>
                                                <p class="text-muted">
                                                    This assessment evaluates your ability to work with numbers. It includes tasks such as basic arithmetic, interpreting data, 
                                                    and problem-solving with quantitative elements, reflecting the practical math skills required in everyday job tasks.
                                                </p>
                                            </div>

                                            <div>
                                                <div class="row gy-3">
                                                    @php $numeracyQuestionNumber = 1; @endphp <!-- Initialize a numeracy question number counter outside the loop -->

                                                    @foreach ($numeracyQuestions as $question)
                                                        @php
                                                            // Find the position of the first question mark or line break
                                                            $firstQuestionMarkPos = strpos($question->message, '?');
                                                            $firstLineBreakPos = strpos($question->message, "\n");
                                                    
                                                            // Determine where to insert the <span> based on your preference
                                                            $insertPos = $firstQuestionMarkPos !== false ? $firstQuestionMarkPos + 1 : $firstLineBreakPos;
                                                    
                                                            // Insert the <span> tag
                                                            if ($insertPos !== false) {
                                                                $question->message = substr_replace($question->message, ' <span class="text-danger">*</span>', $insertPos, 0);
                                                            }
                                                    
                                                            // Convert line breaks to <br> tags without escaping the message
                                                            $formattedMessage = nl2br($question->message);
                                                        @endphp
                                                    
                                                        <div class="col-md-12">
                                                            <div class="mb-3">
                                                                <label for="numeracyQuestion-{{ $question->id }}" class="form-label">
                                                                    {{ $numeracyQuestionNumber }}.) {!! $formattedMessage !!} <!-- Display the numeracy question number -->
                                                                </label>
                                                                <select class="form-control" id="numeracyQuestion-{{ $question->id }}" name="numeracy_answers[{{ $question->id }}]" data-choices data-choices-search-false required>
                                                                    <option value="">Select an option</option>
                                                                    <option value="a" selected>a</option>
                                                                    <option value="b">b</option>
                                                                    <option value="c">c</option>
                                                                    <option value="d">d</option>
                                                                    <option value="e">e</option>
                                                                </select>
                                                                <div class="invalid-feedback">Please select an option</div>
                                                            </div>                                                        
                                                        </div>
                                                        @php $numeracyQuestionNumber++; @endphp <!-- Increment the numeracy question number for the next iteration -->
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button" class="btn btn-light btn-label previestab"
                                                    data-previous="v-pills-literacy-tab">
                                                    <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                    Back
                                                </button>
                                                <button type="button"
                                                    class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                    data-nexttab="v-pills-situational-tab">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Continue
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            Situational Assessment
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade" id="v-pills-situational" role="tabpanel" aria-labelledby="v-pills-situational-tab">
                                            <div>
                                                <h5>Situational Awareness Assessment</h5>
                                                <p class="text-muted">
                                                    This assessment tests your ability to respond appropriately to workplace scenarios, focusing on decision-making, 
                                                    problem-solving, and handling real-life challenges in a professional environment.
                                                </p>
                                            </div>

                                            <div>
                                                <div class="row gy-3">
                                                    @php $situationalQuestionNumber = 1; @endphp <!-- Initialize a situational question number counter outside the loop -->
                                        
                                                    @foreach ($situationalQuestions as $question)
                                                        @php
                                                            // Find the position of the first question mark or line break
                                                            $firstQuestionMarkPos = strpos($question->message, '?');
                                                            $firstLineBreakPos = strpos($question->message, "\n");
                                        
                                                            // Determine where to insert the <span> based on your preference
                                                            $insertPos = $firstQuestionMarkPos !== false ? $firstQuestionMarkPos + 1 : $firstLineBreakPos;
                                        
                                                            // Insert the <span> tag
                                                            if ($insertPos !== false) {
                                                                $question->message = substr_replace($question->message, ' <span class="text-danger">*</span>', $insertPos, 0);
                                                            }
                                        
                                                            // Convert line breaks to <br> tags without escaping the message
                                                            $formattedMessage = nl2br($question->message);
                                                            $formattedMessage = str_replace('*', '', $formattedMessage);
                                                        @endphp
                                        
                                                        <div class="col-md-12">
                                                            <div class="mb-3">
                                                                <label for="situationalQuestion-{{ $question->id }}" class="form-label">
                                                                    {{ $situationalQuestionNumber }}.) {!! $formattedMessage !!} <!-- Display the situational question number -->
                                                                </label>
                                                                <select class="form-control" id="situationalQuestion-{{ $question->id }}" name="situational_answers[{{ $question->id }}]" data-choices data-choices-search-false required>
                                                                    <option value="">Select an option</option>
                                                                    <option value="a" selected>a</option>
                                                                    <option value="b">b</option>
                                                                    <option value="c">c</option>
                                                                    <option value="d">d</option>
                                                                </select>
                                                                <div class="invalid-feedback">Please select an option</div>
                                                            </div>                                                        
                                                        </div>
                                                        @php $situationalQuestionNumber++; @endphp <!-- Increment the situational question number for the next iteration -->
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button" class="btn btn-light btn-label previestab"
                                                    data-previous="v-pills-numeracy-tab">
                                                    <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                    Back
                                                </button>
                                                <button type="button"
                                                    class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                    data-nexttab="v-pills-finish-tab">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Continue
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            Finish
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade d-flex align-items-center justify-content-center flex-column" id="v-pills-finish" role="tabpanel" aria-labelledby="v-pills-finish-tab">
                                            @if ($user->applicant)
                                                <!-- Update -->
                                                <div class="text-center pt-4 pb-2" id="complete">
                                                    <div class="mb-4">
                                                        <lord-icon src="https://cdn.lordicon.com/nocovwne.json" trigger="loop" colors="primary:#0ab39c,secondary:#405189" id="lordicon" style="width:120px;height:120px"></lord-icon>
                                                    </div>
                                                    <h5 id="completeHeading">Would you like to update your application ?</h5>
                                                    <p class="text-muted" id="completeText">
                                                        You are about to update your application with new information.
                                                    </p>
                                                    <button type="submit" id="updateBtn" class="btn btn-secondary btn-label waves-effect waves-light rounded-pill">
                                                        <i class="ri-check-double-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        Yes, Update !
                                                    </button>
                                                    <a type="button" href="{{ route('profile.index') }}" id="view-application" class="btn btn-primary btn-label waves-effect waves-light rounded-pill">
                                                        <i class="ri-organization-chart label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        View Application
                                                    </a>
                                                </div>

                                                <!-- Loading -->
                                                <div class="text-center pt-4 pb-2 mt-4 d-none" id="loading">
                                                    <div class="spinner-border text-success mb-4" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Confirm -->
                                                <div class="text-center pt-4 pb-2 {{ $user->applicant ? 'd-none' : '' }}" id="confirm">
                                                    <div class="mb-4">
                                                        <lord-icon src="https://cdn.lordicon.com/nocovwne.json" trigger="loop" state="hover-2" colors="primary:#0ab39c,secondary:#405189" style="width:120px;height:120px"></lord-icon>
                                                    </div>
                                                    <h5>Would you like to submit your application ?</h5>
                                                    <p class="text-muted">
                                                        After successful submission you will be notified should you qualify fo an interview.
                                                    </p>
                                                    <button type="button" id="cancelBtn" class="btn btn-light btn-label waves-effect waves-light rounded-pill" data-previous="v-pills-personal-tab">
                                                        <i class="ri-close-circle-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        No, Cancel
                                                    </button>
                                                    <button type="submit" id="submitBtn" class="btn btn-secondary btn-label waves-effect waves-light rounded-pill">
                                                        <i class="ri-check-double-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        Yes, Submit !
                                                    </button>
                                                </div>                                            

                                                <!-- Loading -->
                                                <div class="text-center pt-4 pb-2 d-none" id="loading">
                                                    <div class="spinner-border text-success mb-4" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </div>

                                                <!-- Complete -->
                                                <div class="text-center pt-4 pb-2 d-none" id="complete">
                                                    <div class="mb-4">
                                                        <lord-icon src="https://cdn.lordicon.com/lupuorrc.json" trigger="loop" colors="primary:#0ab39c,secondary:#405189" id="lordicon" style="width:120px;height:120px"></lord-icon>
                                                    </div>
                                                    <h5 id="completeHeading">Application Submitted !</h5>
                                                    <p class="text-muted" id="completeText">
                                                        Your application has been submitted successfully, you will be notified should you qualify 
                                                        for an interview
                                                    </p>
                                                    <a type="button" href="{{ route('profile.index') }}" id="view-application" class="btn btn-primary btn-label waves-effect waves-light rounded-pill">
                                                        <i class="ri-organization-chart label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        View Application
                                                    </a>
                                                </div>
                                            @endif

                                            <!-- Danger Alert -->
                                            <div class="alert alert-danger alert-dismissible fade text-center mt-4" role="alert" id="requiredAlert">
                                                <strong>Some fields are missing!</strong> Please make sure that all the required fields are filled out
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->
                                    </div>
                                    <!-- end tab content -->
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </form>
                </div>
            </div>
            <!-- end -->
        </div>
        <!-- end col -->
    @endif
</div>


@endsection
@section('script')
<!-- sweet alert -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- apexcharts -->
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/jsvectormap/maps/world-merc.js') }}"></script>
<script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
<!-- home init -->
@if ($user->applicant)
    <script src="{{URL::asset('build/js/pages/home.init.js')}}?v={{ filemtime(public_path('build/js/pages/home.init.js')) }}"></script>
@else
    <script src="{{ URL::asset('build/libs/@simonwep/pickr/pickr.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/quill/quill.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/application.init.js') }}?v={{ filemtime(public_path('build/js/pages/application.init.js')) }}"></script>

    <!-- input flag init -->
    <script src="{{URL::asset('build/js/pages/flag-input.init.js')}}"></script>
@endif
<script src="{{ URL::asset('build/js/pages/vacancy-save.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
