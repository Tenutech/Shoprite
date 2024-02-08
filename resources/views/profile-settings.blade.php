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
                            <img src="@if (Auth::user()->avatar != '') {{ URL::asset('images/' . Auth::user()->avatar) }} @else {{ URL::asset('build/images/users/avatar-1.jpg') }} @endif" class="rounded-circle avatar-xl img-thumbnail user-profile-image" alt="user-profile-image">
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
                            {{ $user->position->name }}
                        </p>
                    </div>
                </div>
            </div>
            <!--end card-->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-5">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">
                                Complete Your Profile
                            </h5>
                        </div>
                    </div>
                    <div class="progress animated-progress custom-progress progress-label">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $completionPercentage }}%" aria-valuenow="{{ $completionPercentage }}"
                            aria-valuemin="0" aria-valuemax="100">
                            <div class="label">{{ $completionPercentage }}%</div>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
        <!--end col-->
        <div class="col-xxl-9">
            <div class="card mt-xxl-n5">
                <div class="card-header">
                    <ul class="nav nav-tabs nav-tabs-custom rounded card-header-tabs border-bottom-0" id="profileSettingsTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                                <i class="fas fa-home"></i>
                                Personal Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                                <i class="far fa-user"></i>
                                Change Password
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#notifications" role="tab">
                                <i class="far fa-user"></i>
                                Notifications
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                            <form id="formUser" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="firstname" class="form-label">
                                                First Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control @error('firstname') is-invalid @enderror" name="firstname" id="firstname" placeholder="Enter your firstname" value="{{ $user->firstname }}" required/>
                                            @error('firstname')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter firstname
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="lastname" class="form-label">
                                                Last Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control @error('lastname') is-invalid @enderror" name="lastname" id="lastname" placeholder="Enter your lastname" value="{{ $user->lastname }}" required/>
                                            @error('lastname')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter lastname
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">
                                                Email Address <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" placeholder="Enter your email" value="{{ $user->email }}" required/>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter email address
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="phonet" class="form-label">
                                                Phone Number <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" placeholder="Enter your phone number" value="{{ $user->phone }}" required/>
                                            @error('phone')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter phone number
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="company" class="form-label">
                                                Company <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control @error('company') is-invalid @enderror" name="company" id="company" placeholder="Enter the name of your company" value="{{ $user->company ? $user->company->name : '' }}" required/>
                                            @error('company')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter company name
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="position" class="form-label">
                                                Position <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control @error('position') is-invalid @enderror" name="position" id="position" placeholder="Enter your current position" value="{{ $user->position ? $user->position->name : '' }}" required/>
                                            @error('position')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter current position
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="website" class="form-label">
                                                Website
                                            </label>
                                            <input type="text" class="form-control @error('website') is-invalid @enderror" name="website" id="website" placeholder="www.example.com" value="{{ $user->website ? $user->website : '' }}" />
                                            @error('website')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter a valid URL
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    
                                    <div class="col-lg-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                Update
                                            </button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                        <!--end tab-pane-->
                        <div class="tab-pane" id="changePassword" role="tabpanel">
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
                                            <a href="javascript:void(0);" class="link-primary text-decoration-underline">
                                                Forgot Password ?
                                            </a>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary">
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
                        <div class="tab-pane" id="notifications" role="tabpanel">
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
                                        @if ($user->role_id < 4)
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
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/profile-settings.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
