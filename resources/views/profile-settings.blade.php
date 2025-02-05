@extends('layouts.master')
@section('title')
    @lang('translation.settings')
@endsection
@section('content')
    <div class="position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg profile-setting-img">
            <img src="{{ URL::asset('build/images/profile-bg.jpg') }}" class="profile-wid-img" alt="">
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-3">
            <div class="card mt-n5">
                <div class="card-body p-4">
                    <div class="text-center">
                        <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                            <img src="@if (Auth::user()->avatar != '') {{ URL::asset('storage/images/' . Auth::user()->avatar) }} @else {{ URL::asset('storage/images/avatar.jpg') }} @endif" class="rounded-circle avatar-xl img-thumbnail user-profile-image" alt="user-profile-image">
                            <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                <input id="avatar" type="file" class="profile-img-file-input" accept=".jpg, .jpeg, .png">
                                <label for="avatar" class="profile-photo-edit avatar-xs">
                                    <span class="avatar-title rounded-circle bg-body text-body">
                                        <i class="ri-camera-fill"></i>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <h5 class="mb-1" id="user-name">
                            {{ $user->firstname }} {{ $user->lastname }}
                        </h5>
                        <p class="text-muted mb-0" id="user-position">
                            {{ optional($user->role)->name ?? 'Applicant' }}
                        </p>
                    </div>
                </div>
            </div>
            <!--end card-->           
        </div>
        <!--end col-->
        <div class="col-xxl-9">
            <div class="card mt-xxl-n5">
                <div class="card-header">
                    <ul class="nav nav-tabs nav-tabs-custom rounded card-header-tabs border-bottom-0" id="profileSettingsTab" role="tablist">
                        @if (!($user->role_id >= 7 && !$user->applicant))
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                                    <i class="fas fa-home"></i>
                                    Personal Details
                                </a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link {{ ($user->role_id >= 7 && !$user->applicant) ? 'active' : '' }}" data-bs-toggle="tab" href="#changePassword" role="tab">
                                <i class="far fa-user"></i>
                                Change Password
                            </a>
                        </li>
                        <li class="nav-item d-none">
                            <a class="nav-link" data-bs-toggle="tab" href="#notifications" role="tab">
                                <i class="far fa-user"></i>
                                Notifications
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">

                        <!-------------------------------------------------------------------------------------
                            Personal Details
                        -------------------------------------------------------------------------------------->

                        @if (!($user->role_id >= 7 && !$user->applicant))
                            <div class="tab-pane active" id="personalDetails" role="tabpanel">
                                <form id="formUser" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <!-- First Name -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label for="firstname" class="form-label">
                                                    First Name 
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control @error('firstname') is-invalid @enderror" name="firstname" id="firstname" placeholder="Enter your firstname" value="{{ $user->role_id < 7 ? $user->firstname : optional($user->applicant)->firstname }}" {{ $user->role_id >= 7 ? 'readonly' : 'required' }}/>
                                                @error('firstname')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                                <div class="invalid-feedback">
                                                    Please enter your firstname!
                                                </div>
                                            </div>
                                        </div>
                                        <!--end col-->

                                        <!-- Last Name -->
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label for="lastname" class="form-label">
                                                    Last Name 
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control @error('lastname') is-invalid @enderror" name="lastname" id="lastname" placeholder="Enter your lastname" value="{{ $user->role_id < 7 ? $user->lastname : optional($user->applicant)->lastname }}" {{ $user->role_id >= 7 ? 'readonly' : 'required' }}/>
                                                @error('lastname')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                                <div class="invalid-feedback">
                                                    Please enter your lastname!
                                                </div>
                                            </div>
                                        </div>
                                        <!--end col-->                                        

                                        <!-- ID Number -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="idNumber" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Please provide your South African ID number.">
                                                    ID Number
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" id="idNumber" name="id_number" placeholder="Enter ID number" value="{{ $user->role_id < 7 ? $user->id_number : optional($user->applicant)->id_number }}" readonly />
                                                <div class="invalid-feedback">
                                                    Please enter your ID number!
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Phone Number -->
                                        <div class="col-lg-6">                                            
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
                                                    <input type="text" class="form-control  @error('phone') is-invalid @enderror rounded-end flag-input" id="phone" name="phone" placeholder="Enter phone number" value="{{ $user->role_id < 7 ? ltrim(str_replace('+27', '', $user->phone), '0') : ltrim(str_replace('+27', '', optional($user->applicant)->phone), '0') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^0+/, '').replace(/(\..*?)\..*/g, '$1');" required/>
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

                                        <!-- Email -->
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">
                                                    Email Address 
                                                    @if ($user->role_id < 7)
                                                        <span class="text-danger">*</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary badge-border">Optional</span>
                                                    @endif
                                                </label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" placeholder="Enter your email" value="{{ $user->role_id < 7 ? $user->email : optional($user->applicant)->email }}" {{ $user->role_id < 7 ? 'required' : '' }}/>
                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                                <div class="invalid-feedback">
                                                    Please enter your email address!
                                                </div>
                                            </div>
                                        </div>
                                        <!--end col-->

                                        @if ($user->role_id >= 7 )
                                            <!-- Address -->
                                            <div class="col-lg-12">                                            
                                                <div class="mb-3">
                                                    <label for="address" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What is your current home address where you stay/live 🏡? Please type every detail. (e.g. street number, street name, suburb, town, postal code).">
                                                        Address <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="address" class="form-control @error('location') is-invalid @enderror" name="location" id="location" placeholder="Enter your home address" value="{{ $user->role_id < 7 ? $user->address : optional($user->applicant)->location }}" data-google-autocomplete autocomplete="off" {{ $user->role_id >= 7 ? 'required' : '' }}>
                                                    @error('emaaddressil')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ e($message) }}</strong>
                                                        </span>                                                        
                                                    @enderror
                                                    <div class="invalid-feedback">
                                                        Please enter your home address!
                                                    </div>
                                                    @php
                                                        $coordinates = $user->role_id < 7 ? '' : optional($user->applicant)->coordinates;

                                                        // Initialize default values
                                                        $latitude = '';
                                                        $longitude = '';

                                                        // Check if coordinates are present and properly formatted
                                                        if ($coordinates && str_contains($coordinates, ',')) {
                                                            $parts = explode(',', $coordinates);

                                                            // Assign values if both latitude and longitude are available
                                                            $latitude = $parts[0] ?? '';
                                                            $longitude = $parts[1] ?? '';
                                                        }
                                                    @endphp
                                                    <input type="hidden" id="latitude" name="latitude" value="{{ $latitude }}">
                                                    <input type="hidden" id="longitude" name="longitude" value="{{ $longitude }}">
                                                </div>
                                            </div>

                                            <!-- Ethnicity -->
                                            <div class="col-lg-12">
                                                <div class="mb-3">
                                                    <label for="race" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What is your ethnicity/race?">
                                                        Ethnicity
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control" id="race" name="race_id" {{ $user->role_id >= 7 ? 'required' : '' }}>
                                                        <option value="">Select ethnicity</option>
                                                        @foreach ($races as $race)
                                                            <option value="{{ $race->id }}" {{ ($user->applicant && $user->applicant->race_id == $race->id) ? 'selected' : '' }}>{{ $race->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">Please select your ethnicity!</div>
                                                </div>                                                        
                                            </div>

                                            <!-- Education -->
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="education" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What is your highest completed educational qualification 🎓?">
                                                        Highest Qualification
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control" id="education" name="education_id" {{ $user->role_id >= 7 ? 'required' : '' }}>
                                                        <option value="">Select education Level</option>
                                                        @foreach ($educations as $education)
                                                            <option value="{{ $education->id }}" 
                                                                {{ ($user->applicant && $user->applicant->education_id == $education->id) ? 'selected' : '' }}>
                                                                {{ $education->id == 2 ? 'Grade 10 / Grade 11' : $education->name }}
                                                            </option>
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
                                                    <select class="form-control" id="duration" name="duration_id" {{ $user->role_id >= 7 ? 'required' : '' }}>
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
                                                    <select class="form-control" id="publicHolidays" name="public_holidays" {{ $user->role_id >= 7 ? 'required' : '' }}>
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
                                                    <select class="form-control" id="environment" name="environment" {{ $user->role_id >= 7 ? 'required' : '' }}>
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
                                                        Type of Store/Brand
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control" id="brands" name="brands[]" data-choices multiple data-choices-search-true data-choices-removeItem>
                                                        <option value="">Select brand</option>
                                                        @foreach ($brands as $brand)
                                                            <option value="{{ $brand->id }}" {{ $user->applicant && in_array($brand->id, array_column($user->applicant->brands->toArray(), 'id')) ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">Please select a brand!</div>
                                                </div>                                                        
                                            </div>

                                            <!-- Disability -->
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="disability" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Do you have a disability? This will not affect your application status.">
                                                        Disability
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control" id="disability" name="disability" {{ $user->role_id >= 7 ? 'required' : '' }}>
                                                        <option value="">Select your answer</option>
                                                        <option value="Yes" {{ ($user->applicant && $user->applicant->disability == 'Yes') ? 'selected' : '' }}>Yes</option>
                                                        <option value="No" {{ ($user->applicant && $user->applicant->disability == 'No') ? 'selected' : '' }}>No</option>
                                                    </select>
                                                    <div class="invalid-feedback">Please select a answer!</div>
                                                </div>                                                        
                                            </div>
                                        @endif
                                        
                                        <div class="col-lg-12">
                                            <div class="hstack gap-2 justify-content-end">
                                                <button type="submit" class="btn btn-primary" id="profileUpdateBtn">
                                                    Update Profile
                                                </button>
                                            </div>
                                        </div>
                                        <!--end col-->
                                    </div>
                                    <!--end row-->
                                </form>
                            </div>
                            <!--end tab-pane-->
                        @endif

                        <!-------------------------------------------------------------------------------------
                            Password
                        -------------------------------------------------------------------------------------->

                        <div class="tab-pane {{ ($user->role_id >= 7 && !$user->applicant) ? 'active' : '' }}" id="changePassword" role="tabpanel">
                            <form id="formPassword" action="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="oldPassword" class="form-label">
                                                Old Password <span class="text-danger">*</span>
                                            </label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control pe-5 password-input @error('oldPassword') is-invalid @enderror" name="oldPassword" id="oldPassword" placeholder="Enter current password" required/>
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon-1">
                                                    <i class="ri-eye-fill align-middle"></i>
                                                </button>
                                                @error('oldPassword')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                                <div class="invalid-feedback">
                                                    Please enter old password
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="newPassword" class="form-label">
                                                New Password <span class="text-danger">*</span>
                                            </label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control pe-5 password-input @error('newPassword') is-invalid @enderror" name="newPassword" id="newPassword" placeholder="Enter new password" required/>
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon-2">
                                                    <i class="ri-eye-fill align-middle"></i>
                                                </button>
                                                @error('newPassword')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                                <div class="invalid-feedback">
                                                    Please enter new password
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="confirmPassword" class="form-label">
                                                Confirm Password <span class="text-danger">*</span>
                                            </label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control pe-5 password-input @error('confirmPassword') is-invalid @enderror" name="newPassword_confirmation" id="confirmPassword" placeholder="Confirm password" required/>
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon-3">
                                                    <i class="ri-eye-fill align-middle"></i>
                                                </button>
                                                @error('confirmPassword')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                                <div class="invalid-feedback">
                                                    Please confirm password
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <a href="{{ route('password.request') }}" class="link-primary text-decoration-underline">
                                                Forgot Password ?
                                            </a>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary" id="passwordUpdateBtn">
                                                Change Password
                                            </button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>                            
                        </div>
                        <!--end tab-pane-->

                        <!-------------------------------------------------------------------------------------
                            Notifications
                        -------------------------------------------------------------------------------------->

                        <div class="tab-pane d-none" id="notifications" role="tabpanel">
                            <form id="formNotifications" action="post" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <h5 class="card-title text-decoration-underline mb-3">
                                        Application Notifications:
                                    </h5>
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-flex">
                                            <div class="flex-grow-1">
                                                <label for="receiveEmailNotifications" class="form-check-label fs-15">
                                                    Receive Email Notifications
                                                </label>
                                                <p class="text-muted">
                                                    Choose this option to receive notifications via email. Adjust this setting based on your preference for email alerts.
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="receiveEmailNotifications" name="receive_email_notifications" {{ $userSettings ? ($userSettings->receive_email_notifications ? 'checked' : '') : 'checked' }} />
                                                </div>
                                            </div>
                                        </li>
                                        <li class="d-flex">
                                            <!--
                                            <div class="flex-grow-1">
                                                <label for="receiveWhatsappNotifications" class="form-check-label fs-15">
                                                    Receive WhatsApp Notifications
                                                </label>
                                                <p class="text-muted">
                                                    Enable this to get notifications on WhatsApp. This is useful for immediate updates and alerts.
                                                </p>
                                            </div>                                            
                                            <div class="flex-shrink-0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="receiveWhatsappNotifications" name="receive_whatsapp_notifications" {{ $userSettings ? ($userSettings->receive_whatsapp_notifications ? 'checked' : '') : 'checked' }} />
                                                </div>
                                            </div>
                                            -->
                                        </li>
                                        <li class="d-flex">
                                            <div class="flex-grow-1">
                                                <label for="notifyApplicationSubmitted" class="form-check-label fs-15">
                                                    Notify When Application Submitted
                                                </label>
                                                <p class="text-muted">
                                                    Get notified when your application is successfully submitted. Keep track of your application status from submission to decision.
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="notifyApplicationSubmitted" name="notify_application_submitted" {{ $userSettings ? ($userSettings->notify_application_submitted ? 'checked' : '') : 'checked' }} />
                                                </div>
                                            </div>
                                        </li>
                                        <li class="d-flex">
                                            <div class="flex-grow-1">
                                                <label for="notifyApplicationStatus" class="form-check-label fs-15">
                                                    Notify on Application Status Change
                                                </label>
                                                <p class="text-muted">
                                                    Stay informed about your application’s progress, including updates on review status, acceptance, or rejection.
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="notifyApplicationStatus" name="notify_application_status" {{ $userSettings ? ($userSettings->notify_application_status ? 'checked' : '') : 'checked' }} />
                                                </div>
                                            </div>
                                        </li>
                                        <li class="d-flex">
                                            <div class="flex-grow-1">
                                                <label for="notifyShortlisted" class="form-check-label fs-15">
                                                    Notify When Shortlisted
                                                </label>
                                                <p class="text-muted">
                                                    Receive alerts if you are shortlisted for an opportunity, keeping you promptly informed about your application’s status.
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="notifyShortlisted" name="notify_shortlisted" {{ $userSettings ? ($userSettings->notify_shortlisted ? 'checked' : '') : 'checked' }} />
                                                </div>
                                            </div>
                                        </li>
                                        <li class="d-flex">
                                            <div class="flex-grow-1">
                                                <label for="notifyInterviewScheduled" class="form-check-label fs-15">
                                                    Notify When Interview is Scheduled
                                                </label>
                                                <p class="text-muted">
                                                    Be promptly informed about the scheduling of interviews, including time and location details.
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="notifyInterviewScheduled" name="notify_interview" {{ $userSettings ? ($userSettings->notify_interview ? 'checked' : '') : 'checked' }} />
                                                </div>
                                            </div>
                                        </li>
                                        @if ($user->role_id < 7)
                                            <li class="d-flex">
                                                <div class="flex-grow-1">
                                                    <label for="notifyVacancyStatus" class="form-check-label fs-15">
                                                        Notify on Vacancy Status Change (Managers)
                                                    </label>
                                                    <p class="text-muted">
                                                        Managers are notified about status updates of the vacancies they posted, including approvals, updates, or rejections.
                                                    </p>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="notifyVacancyStatus" name="notify_vacancy_status" {{ $userSettings ? ($userSettings->notify_vacancy_status ? 'checked' : '') : 'checked' }} />
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="d-flex">
                                                <div class="flex-grow-1">
                                                    <label for="notifyNewApplication" class="form-check-label fs-15">
                                                        Notify When New Application is Received (Managers)
                                                    </label>
                                                    <p class="text-muted">
                                                        Get alerted when a new application is received for a vacancy. This helps managers to keep track of applicant interest.
                                                    </p>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="notifyNewApplication" name="notify_new_application" {{ $userSettings ? ($userSettings->notify_new_application ? 'checked' : '') : 'checked' }} />
                                                    </div>
                                                </div>
                                            </li>
                                        @endif                                                                  
                                    </ul>
                                </div>
                                <div class="col-lg-12">
                                    <div class="hstack gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            Save Settings
                                        </button>
                                    </div>
                                </div>
                                <!--end col-->
                            </form>                    
                        </div>
                        <!--end tab-pane-->
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
@section('script')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googlemaps.key') }}&libraries=places"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/profile-settings.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
