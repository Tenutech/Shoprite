@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection
@section('css')
<link href="{{ URL::asset('build/libs/jsvectormap/css/jsvectormap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/css/custom.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')


<div class="row mt-5">
    @if ($user->applicant && Auth::id() != 3)
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
                    {{ $user->applicant && Auth::id() != 3 ? 'Update' : 'Post' }} Your Application
                </h4>
            </div><!-- end card header -->
            <div class="card-body form-steps">
                <form class="vertical-navs-step" id="{{ $user->applicant && Auth::id() != 3 ? 'formApplicationUpdate' : 'formApplication' }}"  enctype="multipart/form-data" novalidate>
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
                                <button class="nav-link" id="v-pills-qualifications-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-qualifications" type="button" role="tab"
                                    aria-controls="v-pills-qualifications" aria-selected="false">
                                    <span class="step-title me-2">
                                        <i class="ri-close-circle-fill step-icon me-2"></i> Section 2:
                                    </span>
                                    Qualifications
                                </button>
                                <button class="nav-link" id="v-pills-experience-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-experience" type="button" role="tab"
                                    aria-controls="v-pills-experience" aria-selected="false">
                                    <span class="step-title me-2">
                                        <i class="ri-close-circle-fill step-icon me-2"></i> Section 3:
                                    </span>
                                    Experience
                                </button>
                                <button class="nav-link" id="v-pills-punctuality-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-punctuality" type="button" role="tab"
                                    aria-controls="v-pills-punctuality" aria-selected="false">
                                    <span class="step-title me-2">
                                        <i class="ri-close-circle-fill step-icon me-2"></i> Section 4:
                                    </span>
                                    Punctuality
                                </button>
                                <button class="nav-link" id="v-pills-reason-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-reason" type="button" role="tab"
                                    aria-controls="v-pills-reason" aria-selected="false">
                                    <span class="step-title me-2">
                                        <i class="ri-close-circle-fill step-icon me-2"></i> Section 5:
                                    </span>
                                    Reason for Application
                                </button>
                                <button class="nav-link" id="v-pills-literacy-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-literacy" type="button" role="tab"
                                    aria-controls="v-pills-literacy" aria-selected="false">
                                    <span class="step-title me-2">
                                        <i class="ri-close-circle-fill step-icon me-2"></i> Section 6:
                                    </span>
                                    Literacy Test
                                </button>
                                <button class="nav-link" id="v-pills-numeracy-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-numeracy" type="button" role="tab"
                                    aria-controls="v-pills-numeracy" aria-selected="false">
                                    <span class="step-title me-2">
                                        <i class="ri-close-circle-fill step-icon me-2"></i> Section 7:
                                    </span>
                                    Numeracy Test
                                </button>
                                <button class="nav-link" id="v-pills-finish-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-finish" type="button" role="tab"
                                    aria-controls="v-pills-finish" aria-selected="false">
                                    <span class="step-title me-2">
                                        <i class="ri-close-circle-fill step-icon me-2"></i> Section 8:
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
                                                <lord-icon src="https://cdn.lordicon.com/xzalkbkz.json" trigger="loop" state="hover-2" colors="primary:#0ab39c,secondary:#405189" style="width:120px;height:120px"></lord-icon>
                                            </div>
                                            <h5 class="mb-3">
                                                Welcome to the Shoprite Employment Application Procedure!
                                            </h5>
                                            <p class="text-muted">
                                                You are on this Shoprite Recruitment platform, because you are applying for an 
                                                employment position at the Shoprite Group. Therefore, your answering of these 
                                                questions replace the need to hand in a physical CV document in store.
                                            </p>
                                            <p class="text-muted">
                                                You will be asked a series of questions to obtain information defined as personal 
                                                information in the Protection of Personal Information Act 4 of 2013 (POPIA) from 
                                                you, and you hereby grant the OTB Group (Operator) permission to process such 
                                                personal information on behalf of the Shoprite Group of Companies (Responsible Party).
                                                By selecting "Start", you confirm that you have read, understood and accept the POPIA 
                                                Ts&Cs available at: 
                                                <a href="{{ route('terms') }}" class="text-primary text-decoration-underline fst-normal fw-medium">
                                                    Terms of Use
                                                </a>
                                            </p>
                                            <p class="text-muted">
                                                By typing "Start", you confirm that you have read, understood and accepted the 
                                                POPIA T's & C's available at:
                                                <a href="{{ route('terms') }}" class="text-primary text-decoration-underline fst-normal fw-medium">
                                                    Terms of Use
                                                </a>
                                                and authorize OTB to conduct verification checks on behalf of the Shoprite Group of 
                                                Companies in respect of your Identification number, credit record, drivers license, 
                                                qualifications, fraud check, bank accounts & criminal record.
                                            </p>
                                            <p class="text-muted">
                                                Please read each question carefully, and answer to the best of your ability. 
                                                Your information will only be submitted once you have completed ALL of the questions.
                                            </p>
                                            <p class="text-muted">
                                                Once you have started the process, you cannot exit and start over, so please set 
                                                out 30 minutes to complete the process.
                                            </p>
                                            <button type="button" class="btn btn-secondary btn-label waves-effect waves-light rounded-pill nexttab nexttab" data-nexttab="v-pills-personal-tab">
                                                <i class="ri-play-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                Start !
                                            </button>
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
                                                </div>

                                                <!-- Fistname -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="firstname" class="form-label">
                                                            First name(s) as per your ID document:
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Enter first name(s)" value="{{ $user->applicant ? $user->applicant->firstname : ($user->firstname ? $user->firstname : '') }}" required />
                                                        <div class="invalid-feedback">
                                                            Please enter your firstname(s)
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Lastname -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="lastname" class="form-label">
                                                            Last name(s) as per your ID document:
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Enter last name(s)" value="{{ $user->applicant ? $user->applicant->lastname : ($user->lastname ? $user->lastname : '') }}" required />
                                                        <div class="invalid-feedback">
                                                            Please enter your lastname(s)
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- ID Number -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="idNumber" class="form-label">
                                                            ID number
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="idNumber" name="id_number" placeholder="Enter ID number" value="{{ $user->applicant ? $user->applicant->id_number : ($user->id_number ? $user->id_number : '') }}" required />
                                                        <div class="invalid-feedback">
                                                            Please enter your ID number
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Phone Number -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="phone" class="form-label">
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
                                                                Please enter your phone number
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
                                                        <label for="location" class="form-label">
                                                            Address
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="location" name="location" placeholder="Enter physical address" value="{{ $user->applicant ? $user->applicant->location : '' }}" required />
                                                        <div class="invalid-feedback">
                                                            Please enter your physical address
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Gender -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="gender" class="form-label">
                                                            Gender
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="gender" name="gender_id" data-choices data-choices-search-false required>
                                                            <option value="">Select gender</option>
                                                            @foreach ($genders as $gender)
                                                                <option value="{{ $gender->id }}" {{ ($user->applicant ? $user->applicant->gender_id == $gender->id : $user->gender_id == $gender->id) ? 'selected' : '' }}>{{ $gender->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select your gender</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Ethnicity -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="race" class="form-label">
                                                            Ethnicity
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="race" name="race_id" data-choices data-choices-search-false required>
                                                            <option value="">Select ethnicity</option>
                                                            @foreach ($races as $race)
                                                                <option value="{{ $race->id }}" {{ ($user->applicant && $user->applicant->race_id == $race->id) ? 'selected' : '' }}>{{ $race->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select your ethnicity</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Email -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="email" class="form-label">
                                                            Email
                                                            <span class="badge bg-secondary-subtle text-secondary badge-border">
                                                                Optional
                                                            </span>
                                                        </label>
                                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" value="{{ $user->applicant ? $user->applicant->email : ($user->email ? $user->email : '') }}" />
                                                    </div>                                                        
                                                </div>

                                                <!-- Tax Number -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="idNumber" class="form-label">
                                                            Tax number
                                                            <span class="badge bg-secondary-subtle text-secondary badge-border">
                                                                Optional
                                                            </span>
                                                        </label>
                                                        <input type="text" class="form-control" id="taxNumber" name="tax_number" placeholder="Enter tax number" value="{{ $user->applicant ? $user->applicant->tax_number : '' }}" />
                                                    </div>                                                        
                                                </div>

                                                <!-- Citizenship -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="citizen" class="form-label">
                                                            Do you have South African citizenship?
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="citizen" name="citizen" data-choices data-choices-search-false required>
                                                            <option value="">Select option</option>
                                                            <option value="No" {{ ($user->applicant ? ($user->applicant->citizen == 'No') : ($user->resident == null || $user->resident == '0')) ? 'selected' : '' }}>No</option>
                                                            <option value="Yes" {{ ($user->applicant ? ($user->applicant->citizen == 'Yes') : ($user->resident == 1)) ? 'selected' : '' }}>Yes</option>
                                                        </select>
                                                        <div class="invalid-feedback">Please select an option</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Criminal Record -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="criminal" class="form-label">
                                                            Do you have a criminal record?
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="criminal" name="criminal" data-choices data-choices-search-false required>
                                                            <option value="">Select option</option>
                                                            <option value="No" {{ ($user->applicant && $user->applicant->criminal == 'No') ? 'selected' : '' }}>No</option>
                                                            <option value="Yes" {{ ($user->applicant && $user->applicant->criminal == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                        </select>
                                                        <div class="invalid-feedback">Please select an option</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Position -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="position" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please choose the position you are applying for">
                                                            I am applying for position:
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="position" name="position_id" data-choices data-choices-search-true required>
                                                            <option value="">Select position</option>
                                                            @foreach ($positions as $position)
                                                                <option value="{{ $position->id }}" {{ ($user->applicant && $user->applicant->position_id == $position->id) ? 'selected' : '' }}>{{ $position->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select the position you are applying for</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Specify Position -->
                                                <div class="col-md-12 d-none" id="positionSpecifyColumn">
                                                    <div class="mb-3">
                                                        <label for="positionSpecify" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please specify the position you are applying for">
                                                            Specify the position
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="positionSpecify" name="position_specify" placeholder="Enter position applying for" value="{{ $user->applicant ? $user->applicant->position_specify : '' }}" />
                                                        <div class="invalid-feedback">
                                                            Please enter a position you are applying for
                                                        </div>
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
                                                data-nexttab="v-pills-qualifications-tab">
                                                <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                Continue
                                            </button>
                                        </div>
                                    </div>
                                    <!-- end tab pane -->

                                    <!-------------------------------------------------------------------------------------
                                        Qualifications
                                    -------------------------------------------------------------------------------------->

                                    <div class="tab-pane fade" id="v-pills-qualifications" role="tabpanel" aria-labelledby="v-pills-qualifications-tab">
                                        <div>
                                            <h5>Qualifications</h5>
                                            <p class="text-muted">
                                                List down your educational qualifications and any certifications that make you suitable for the job.
                                            </p>
                                        </div>

                                        <div>
                                            <div class="row gy-3">

                                                <!-- High School -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="school" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Which High School/Secondary School did you attend?">
                                                            High school attended?
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="school" name="school" placeholder="Enter high school" value="{{ $user->applicant ? $user->applicant->school : '' }}" required />
                                                        <div class="invalid-feedback">
                                                            Please enter your high school attended
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Education -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="education" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Which of these numbers represents your HIGHEST COMPLETED qualification?">
                                                            Education Level
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="education" name="education_id" data-choices data-choices-search-false required>
                                                            <option value="">Select education Level</option>
                                                            @foreach ($educations as $education)
                                                                <option value="{{ $education->id }}" {{ ($user->applicant && $user->applicant->education_id == $education->id) ? 'selected' : '' }}>{{ $education->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select your ethnicity</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Training -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="training" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Are you currently busy with any studies/training programmes?">
                                                            Currently busy with any studies/training programmes?
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="training" name="training" data-choices data-choices-search-false required>
                                                            <option value="">Select option</option>
                                                            <option value="No" {{ ($user->applicant && $user->applicant->training == 'No') ? 'selected' : '' }}>No</option>
                                                            <option value="Yes" {{ ($user->applicant && $user->applicant->training == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                        </select>
                                                        <div class="invalid-feedback">Please select an option</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Other Achievements -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="otherTraining" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Are there any other courses/certificates/achievements we should know about? Please briefly type below:">
                                                            Other Achievements
                                                            <span class="badge bg-secondary-subtle text-secondary badge-border">
                                                                Optional
                                                            </span>
                                                        </label>
                                                        <input type="text" class="form-control" id="otherTraining" name="other_training" placeholder="Enter other achievements" value="{{ $user->applicant ? $user->applicant->other_training : '' }}" />
                                                    </div>
                                                </div>

                                                <!-- Driver's Licence -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="driversLicenseCode" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="If you have a valid driver's licence, please briefly type below:">
                                                            Driver's Licence
                                                            <span class="badge bg-secondary-subtle text-secondary badge-border">
                                                                Optional
                                                            </span>
                                                        </label>
                                                        <select class="form-control" id="driversLicenseCode" name="drivers_license_code" data-choices data-choices-search-false required>
                                                            <option value="">Select driver's licence</option>
                                                            <option value="A" {{ ($user->applicant && $user->applicant->drivers_license_code == 'A') ? 'selected' : '' }}>A</option>
                                                            <option value="B" {{ ($user->applicant && $user->applicant->drivers_license_code == 'B') ? 'selected' : '' }}>B</option>
                                                            <option value="C1" {{ ($user->applicant && $user->applicant->drivers_license_code == 'C1') ? 'selected' : '' }}>C1</option>
                                                            <option value="C" {{ ($user->applicant && $user->applicant->drivers_license_code == 'C') ? 'selected' : '' }}>C</option>
                                                            <option value="EB, EC1, EC" {{ ($user->applicant && $user->applicant->drivers_license_code == 'EB, EC1, EC') ? 'selected' : '' }}>EB, EC1, EC</option>
                                                        </select>
                                                    </div>                                                        
                                                </div>

                                                <!-- Read -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="read" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Which of the following languages can you read/understand?">
                                                            Languages read/understand:
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="read" name="read[]" data-choices multiple data-choices-search-true data-choices-removeItem required>
                                                            <option value="">Select language</option>
                                                            @foreach ($languages as $readLanguage)
                                                                <option value="{{ $readLanguage->id }}" {{ $user->applicant && in_array($readLanguage->id, array_column($user->applicant->readLanguages->toArray(), 'id')) ? 'selected' : '' }}>{{ $readLanguage->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select a language</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Speak -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="speak" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Which of the following languages can you speak?">
                                                            Languages spoken:
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="speak" name="speak[]" data-choices multiple data-choices-search-true data-choices-removeItem required>
                                                            <option value="">Select language</option>
                                                            @foreach ($languages as $speakLanguage)
                                                                <option value="{{ $speakLanguage->id }}" {{ $user->applicant && in_array($speakLanguage->id, array_column($user->applicant->speakLanguages->toArray(), 'id')) ? 'selected' : '' }}>{{ $speakLanguage->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select a language</div>
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
                                                data-nexttab="v-pills-experience-tab">
                                                <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                Continue
                                            </button>
                                        </div>
                                    </div>
                                    <!-- end tab pane -->

                                    <!-------------------------------------------------------------------------------------
                                        Experience
                                    -------------------------------------------------------------------------------------->

                                    <div class="tab-pane fade" id="v-pills-experience" role="tabpanel" aria-labelledby="v-pills-experience-tab">
                                        <div>
                                            <h5>Experience</h5>
                                            <p class="text-muted">
                                                Detail your professional experience, past roles, and key responsibilities to showcase your expertise.
                                            </p>
                                        </div>

                                        <div>
                                            <div class="row gy-3">

                                                <!-- Job Previous -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="jobPrevious" class="form-label">
                                                            Have you previously worked for any employer?
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="jobPrevious" name="job_previous" data-choices data-choices-search-false required>
                                                            <option value="">Select option</option>
                                                            <option value="No" {{ ($user->applicant && $user->applicant->job_previous == 'No') ? 'selected' : '' }}>No</option>
                                                            <option value="Yes" {{ ($user->applicant && $user->applicant->job_previous == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                        </select>
                                                        <div class="invalid-feedback">Please select an option</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Job Previous Column -->
                                                <div class="col-md-12 d-none" id="jobPreviousColumn">
                                                    <div class="row">

                                                        <!-- Job Leave Reason -->
                                                        <div class="col-md-12">
                                                            <div class="mb-3">
                                                                <label for="reason" class="form-label">
                                                                    Why did you leave your previous job?
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <select class="form-control" id="reason" name="reason_id" data-choices data-choices-search-true>
                                                                    <option value="">Select reason</option>
                                                                    @foreach ($reasons as $reason)                                                                            
                                                                        <option value="{{ $reason->id }}" {{ ($user->applicant && $user->applicant->reason_id == $reason->id) ? 'selected' : '' }}>{{ $reason->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="invalid-feedback">Please select a reason</div>
                                                            </div>                                                        
                                                        </div>

                                                        <!-- Specify Position -->
                                                        <div class="col-md-12 d-none" id="jobleaveSpecifyColumn">
                                                            <div class="mb-3">
                                                                <label for="jobleaveSpecify" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please specify the reason you left your previous job">
                                                                    Specify the reason
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" class="form-control" id="jobleaveSpecify" name="job_leave_specify" placeholder="Please specify the reaon" value="{{ $user->applicant ? $user->applicant->job_leave_specify : '' }}" />
                                                                <div class="invalid-feedback">
                                                                    Please enter a reason
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Job Previous Bussiness -->
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="jobBusiness" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What was the name of the last business you worked for?">
                                                                    Business name
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" class="form-control" id="jobBusiness" name="job_business" placeholder="Enter business name" value="{{ $user->applicant ? $user->applicant->job_business : '' }}" />
                                                                <div class="invalid-feedback">
                                                                    Please enter a business name
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Job Previous Position -->
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="jobPosition" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What was your role/position at your previous job?">
                                                                    Position
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" class="form-control" id="jobPosition" name="job_position" placeholder="Enter position name" value="{{ $user->applicant ? $user->applicant->job_position : '' }}" />
                                                                <div class="invalid-feedback">
                                                                    Please enter a position
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Job Previous Duration -->
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="duration" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="How long did you work for the previous employer?">
                                                                    Duration worked for previous employer?
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <select class="form-control" id="duration" name="duration_id" data-choices data-choices-search-false>
                                                                    <option value="">Select duration</option>
                                                                    @foreach ($durations as $duration)
                                                                        <option value="{{ $duration->id }}" {{ ($user->applicant && $user->applicant->duration_id == $duration->id) ? 'selected' : '' }}>{{ $duration->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="invalid-feedback">Please select a reason</div>
                                                            </div>                                                        
                                                        </div>

                                                        <!-- Job Previous Salary -->
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="jobSalary" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What was your monthly salary at your previous job?">
                                                                    Monthly Salary
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" class="form-control" id="jobSalary" name="job_salary" placeholder="Enter monthly salary" value="{{ $user->applicant ? $user->applicant->job_salary : '' }}" />
                                                                <div class="invalid-feedback">
                                                                    Please enter a salary amount
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Job Reference Name -->
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="jobReferenceName" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Type the name and surname of a person of reference from your previous job">
                                                                    Job Reference Name
                                                                    <span class="badge bg-secondary-subtle text-secondary badge-border">
                                                                        Optional
                                                                    </span>
                                                                </label>
                                                                <input type="text" class="form-control" id="jobReferenceName" name="job_reference_name" placeholder="Enter firstname and lastname" value="{{ $user->applicant ? $user->applicant->job_reference_name : '' }}" />
                                                                <div class="invalid-feedback">
                                                                    Please enter a name
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Job Reference Name -->
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="jobReferencePhone" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please provide the contact number of the reference person mentioned">
                                                                    Phone Number 
                                                                    <span class="badge bg-secondary-subtle text-secondary badge-border">
                                                                        Optional
                                                                    </span>
                                                                </label>
                                                                <div class="input-group" data-input-flag>
                                                                    <button class="btn btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <img src="{{URL::asset('build/images/flags/za.svg')}}" alt="flag img" height="20" class="country-flagimg rounded">
                                                                        <span class="ms-2 country-codeno" id="refrenceCountry">+ 27</span>
                                                                    </button>
                                                                    <input type="text" class="form-control rounded-end flag-input" id="jobReferencePhone" name="job_reference_phone" placeholder="Enter phone number" value="{{ $user->applicant ? ltrim(str_replace('+27', '', $user->applicant->job_reference_phone), '0') : '' }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^0+/, '').replace(/(\..*?)\..*/g, '$1');" />
                                                                    <div class="invalid-feedback">
                                                                        Please enter a phone number
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

                                                    </div>
                                                </div>

                                                <!-- Retrenchment -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="retrenchment" class="form-label">
                                                            Have you ever been dismissed or retrenched?
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="retrenchment" name="retrenchment_id" data-choices data-choices-search-false required>
                                                            <option value="">Select option</option>
                                                            @foreach ($retrenchments as $retrenchment)                                                                    
                                                                <option value="{{ $retrenchment->id }}" {{ ($user->applicant && $user->applicant->retrenchment_id == $retrenchment->id) ? 'selected' : '' }}>{{ $retrenchment->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select an option</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Job Retrenched Specify -->
                                                <div class="col-md-12 d-none" id="jobRetrenchedSpecifyColumn">
                                                    <div class="mb-3">
                                                        <label for="jobRetrenchedSpecify" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please specify what the dismissal or retrenchment was for">
                                                            Specify the reason
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="jobRetrenchedSpecify" name="job_retrenched_specify" placeholder="Please specify the reaon" value="{{ $user->applicant ? $user->applicant->job_retrenched_specify : '' }}" />
                                                        <div class="invalid-feedback">
                                                            Please enter a reason
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Brand -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="brand" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="If you have previously been employed by any company within the Shoprite group of companies, please indicate below, otherwise leave blank.">
                                                            Have you previously been employed by any company within the Shoprite group of companies?
                                                            <span class="badge bg-secondary-subtle text-secondary badge-border">
                                                                Optional
                                                            </span>
                                                        </label>
                                                        <select class="form-control" id="brand" name="brand_id" data-choices data-choices-search-true>
                                                            <option value="">Select company</option>
                                                            @foreach ($brands as $brand)
                                                                <option value="{{ $brand->id }}" {{ ($user->applicant && $user->applicant->brand_id == $brand->id) ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select a company</div>
                                                    </div>                                                        
                                                </div>

                                                <div class="col-md-12" id="jobPreviousShopriteColumn">
                                                    <div class="row">

                                                        <!-- Previous Job Shoprite Position -->
                                                        <div class="col-md-12">
                                                            <div class="mb-3">
                                                                <label for="previousJobPosition" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What was your job title at this Shoprite group of companies?">
                                                                    Previous Position
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <select class="form-control" id="previousJobPosition" name="previous_job_position_id" data-choices data-choices-search-true>
                                                                    <option value="">Select position</option>
                                                                    @foreach ($positions as $position)
                                                                        <option value="{{ $position->id }}" {{ ($user->applicant && $user->applicant->previous_job_position_id == $position->id) ? 'selected' : '' }}>{{ $position->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="invalid-feedback">Please select the position you are applying for</div>
                                                            </div>                                                        
                                                        </div>

                                                        <!-- Job Shoprite Position Specify -->
                                                        <div class="col-md-12 d-none" id="jobShopritePositionSpecify">
                                                            <div class="mb-3">
                                                                <label for="positionSpecify" class="form-label">
                                                                    Specify the position
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" class="form-control" id="jobShopritePositionSpecify" name="job_shoprite_position_specify" placeholder="Enter previous position" value="{{ $user->applicant ? $user->applicant->job_shoprite_position_specify : '' }}" />
                                                                <div class="invalid-feedback">
                                                                    Please enter a the posisition you previously had
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Job Shoprite Position Specify -->
                                                        <div class="col-md-12">
                                                            <div class="mb-3">
                                                                <label for="jobShopriteLeave" class="form-label">
                                                                    Please specify why you left Shoprite
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" class="form-control" id="jobShopriteLeave" name="job_shoprite_leave" placeholder="Enter a reason" value="{{ $user->applicant ? $user->applicant->job_shoprite_leave : '' }}" />
                                                                <div class="invalid-feedback">
                                                                    Please enter a reason
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start gap-3 mt-4">
                                            <button type="button" class="btn btn-light btn-label previestab"
                                                data-previous="v-pills-qualifications-tab">
                                                <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                Back
                                            </button>
                                            <button type="button"
                                                class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                data-nexttab="v-pills-punctuality-tab">
                                                <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                Continue
                                            </button>
                                        </div>
                                    </div>
                                    <!-- end tab pane -->

                                    <!-------------------------------------------------------------------------------------
                                        Punctuality
                                    -------------------------------------------------------------------------------------->

                                    <div class="tab-pane fade" id="v-pills-punctuality" role="tabpanel" aria-labelledby="v-pills-punctuality-tab">
                                        <div>
                                            <h5>Punctuality</h5>
                                            <p class="text-muted">
                                                Punctuality is a reflection of your respect for others' time and commitment to your responsibilities. Please share how you ensure to be on time for commitments and the importance you place on punctuality in your professional life.
                                            </p>
                                        </div>

                                        <div>
                                            <div class="row gy-3">

                                                <!-- Transport -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="transport" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="How will you get to work?">
                                                            Transport
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="transport" name="transport_id" data-choices data-choices-search-true required>
                                                            <option value="">Select transport</option>
                                                            @foreach ($transports as $transport)                                                                            
                                                                <option value="{{ $transport->id }}" {{ ($user->applicant && $user->applicant->transport_id == $transport->id) ? 'selected' : '' }}>{{ $transport->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Please select a trasnport
                                                        </div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Transport Specify -->
                                                <div class="col-md-12 d-none" id="transportSpecifyColumn">
                                                    <div class="mb-3">
                                                        <label for="transportSpecify" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please specify how you will you get to work">
                                                            Specify transport
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="transportSpecify" name="transport_specify" placeholder="Enter transport" value="{{ $user->applicant ? $user->applicant->transport_specify : '' }}" />
                                                        <div class="invalid-feedback">
                                                            Please enter transport
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Disability -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="disability" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="The position demands of you to work in cold areas, lift, carry & shelve heavy boxes, as well as standing on your feet for long hours. Do you suffer from any of the below?">
                                                            Disability
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="disability" name="disability_id" data-choices data-choices-search-true required>
                                                            <option value="">Select disability</option>
                                                            @foreach ($disabilities as $disability)                                                                            
                                                                <option value="{{ $disability->id }}" {{ ($user->applicant && $user->applicant->disability_id == $disability->id) ? 'selected' : '' }}>{{ $disability->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select a reason</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Illness Specify -->
                                                <div class="col-md-12 d-none" id="illnessSpecifyColumn">
                                                    <div class="mb-3">
                                                        <label for="illnessSpecify" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please specify your illness, disease or disability">
                                                            Specify illness/disability
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="illnessSpecify" name="illness_specify" placeholder="Enter illness/disability" value="{{ $user->applicant ? $user->applicant->illness_specify : '' }}" />
                                                        <div class="invalid-feedback">
                                                            Please enter a illness/disability
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Commencement Date -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="commencement" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="When can you commence duties?">
                                                            Start Date
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control flatpickr-input active" id="commencement" name="commencement"  placeholder="Enter date" data-provider="flatpickr" data-date-format="d M, Y" value="{{ $user->applicant ? date('d M, Y', strtotime($user->applicant->commencement)) : '' }}" readonly="readonly" required>
                                                        <div class="invalid-feedback">
                                                            Please enter a date
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start gap-3 mt-4">
                                            <button type="button" class="btn btn-light btn-label previestab"
                                                data-previous="v-pills-experience-tab">
                                                <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                Back
                                            </button>
                                            <button type="button"
                                                class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                data-nexttab="v-pills-reason-tab">
                                                <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                Continue
                                            </button>
                                        </div>
                                    </div>
                                    <!-- end tab pane -->

                                    <!-------------------------------------------------------------------------------------
                                        Reason for Application
                                    -------------------------------------------------------------------------------------->

                                    <div class="tab-pane fade" id="v-pills-reason" role="tabpanel" aria-labelledby="v-pills-reason-tab">
                                        <div>
                                            <h5>Reason for Application</h5>
                                            <p class="text-muted">
                                                Share with us what motivated you to apply for this position. This could include your career aspirations, interest in the company, or the desire to take on new challenges.
                                            </p>
                                        </div>

                                        <div>
                                            <div class="row gy-3">

                                                <!-- Type -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="type" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What is your reason for applying for a position at Shoprite Group?">
                                                            Job Type
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="type" name="type_id" data-choices data-choices-search-true required>
                                                            <option value="">Select job type</option>
                                                            @foreach ($types as $type)                                                                            
                                                                <option value="{{ $type->id }}" {{ ($user->applicant && $user->applicant->type_id == $type->id) ? 'selected' : '' }}>{{ $type->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select a reason</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Application Reason Specify -->
                                                <div class="col-md-12 d-none" id="applicationReasonSpecifyColumn">
                                                    <div class="mb-3">
                                                        <label for="applicationReasonSpecify" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please specify the reason for applying for a position at Shoprite Group">
                                                            Specify reason
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="applicationReasonSpecify" name="application_reason_specify" placeholder="Enter reason for application" value="{{ $user->applicant ? $user->applicant->application_reason_specify : '' }}" />
                                                        <div class="invalid-feedback">
                                                            Please enter a reason
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Relocate -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="relocate" class="form-label">
                                                            Are you willing to relocate?
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="relocate" name="relocate" data-choices data-choices-search-false required>
                                                            <option value="">Select option</option>
                                                            <option value="No" {{ ($user->applicant && $user->applicant->relocate == 'No') ? 'selected' : '' }}>No</option>
                                                            <option value="Yes" {{ ($user->applicant && $user->applicant->relocate == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                        </select>
                                                        <div class="invalid-feedback">Please select an option</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Relocate Town -->
                                                <div class="col-md-12 d-none" id="relocateTownColumn">
                                                    <div class="mb-3">
                                                        <label for="relocateTown" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What is the preferred town/suburb that you wish to work in?">
                                                            Relocation Town
                                                            <span class="badge bg-secondary-subtle text-secondary badge-border">
                                                                Optional
                                                            </span>
                                                        </label>
                                                        <input type="text" class="form-control" id="relocateTown" name="relocate_town" placeholder="Enter town name" value="{{ $user->applicant ? $user->applicant->relocate_town : '' }}" />
                                                        <div class="invalid-feedback">
                                                            Please enter town name
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Vacancy -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="vacancy" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="If there is no vacancy in your preferred position, are you prepared to accept a lower position?">
                                                            Prepared to accept a lower position?
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="vacancy" name="vacancy" data-choices data-choices-search-false required>
                                                            <option value="">Select option</option>
                                                            <option value="No" {{ ($user->applicant && $user->applicant->vacancy == 'No') ? 'selected' : '' }}>No</option>
                                                            <option value="Yes" {{ ($user->applicant && $user->applicant->vacancy == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                        </select>
                                                        <div class="invalid-feedback">Please select an option</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Shift -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="shift" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Are you prepared to work on a shift basis, which includes Sundays and Public Holidays?">
                                                            Prepared to work on a shift basis?
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="shift" name="shift" data-choices data-choices-search-false required>
                                                            <option value="">Select option</option>
                                                            <option value="No" {{ ($user->applicant && $user->applicant->shift == 'No') ? 'selected' : '' }}>No</option>
                                                            <option value="Yes" {{ ($user->applicant && $user->applicant->shift == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                        </select>
                                                        <div class="invalid-feedback">Please select an option</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Bank -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="bank" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="If you have a bank account, at which bank is your account?">
                                                            Bank
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select class="form-control" id="bank" name="bank_id" data-choices data-choices-search-true required>
                                                            <option value="">Select bank</option>
                                                            @foreach ($banks as $bank)                                                                            
                                                                <option value="{{ $bank->id }}" {{ ($user->applicant && $user->applicant->bank_id == $bank->id) ? 'selected' : '' }}>{{ $bank->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Please select a bank</div>
                                                    </div>                                                        
                                                </div>

                                                <!-- Bank Specify -->
                                                <div class="col-md-12 d-none" id="bankSpecifyColumn">
                                                    <div class="mb-3">
                                                        <label for="bankSpecify" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please specify the bank">
                                                            Specify bank
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="bankSpecify" name="bank_specify" placeholder="Enter bank name" value="{{ $user->applicant ? $user->applicant->bank_specify : '' }}" />
                                                        <div class="invalid-feedback">
                                                            Please enter a bank name
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Bank Number -->
                                                <div class="col-md-12 d-none" id="bankNumberColumn">
                                                    <div class="mb-3">
                                                        <label for="bankNumber" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please type your bank account number below">
                                                            Bank account number
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="bankNumber" name="bank_number" placeholder="Enter bank account number" value="{{ $user->applicant ? $user->applicant->bank_number : '' }}" />
                                                        <div class="invalid-feedback">
                                                            Please enter a bank name
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Expected Salary -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="expectedSalary" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What is your expected annual salary?">
                                                            Expected salary
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="number" class="form-control" id="expectedSalary" name="expected_salary" placeholder="Enter salary amount" value="{{ $user->applicant ? $user->applicant->expected_salary : '' }}" />
                                                        <div class="invalid-feedback">
                                                            Please enter salary amount
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start gap-3 mt-4">
                                            <button type="button" class="btn btn-light btn-label previestab"
                                                data-previous="v-pills-punctuality-tab">
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
                                        Literacy
                                    -------------------------------------------------------------------------------------->

                                    <div class="tab-pane fade" id="v-pills-literacy" role="tabpanel" aria-labelledby="v-pills-literacy-tab">
                                        <div>
                                            <h5>Literacy Test</h5>
                                            <p class="text-muted">
                                                A literacy test assesses your reading comprehension, writing skills, and ability to communicate effectively. It's an opportunity to demonstrate your proficiency in understanding and using written language in a workplace context.
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
                                                data-previous="v-pills-reason-tab">
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
                                        Numeracy
                                    -------------------------------------------------------------------------------------->

                                    <div class="tab-pane fade" id="v-pills-numeracy" role="tabpanel" aria-labelledby="v-pills-numeracy-tab">
                                        <div>
                                            <h5>Numeracy Test</h5>
                                            <p class="text-muted">
                                                The numeracy test evaluates your ability to work with numbers. It includes tasks such as basic arithmetic, interpreting data, and problem-solving with quantitative elements, reflecting the practical math skills required in everyday job tasks.
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
                                        @if ($user->applicant && Auth::id() != 3)
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
                                            <div class="text-center pt-4 pb-2 {{ $user->applicant && Auth::id() != 3 ? 'd-none' : '' }}" id="confirm">
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
@if ($user->applicant && Auth::id() != 3)
    <script src="{{URL::asset('build/js/pages/home.init.js')}}"></script>
@else
    <script src="{{ URL::asset('build/libs/@simonwep/pickr/pickr.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/quill/quill.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/application.init.js') }}"></script>

    <!-- input flag init -->
    <script src="{{URL::asset('build/js/pages/flag-input.init.js')}}"></script>
@endif
<script src="{{ URL::asset('build/js/pages/vacancy-save.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
