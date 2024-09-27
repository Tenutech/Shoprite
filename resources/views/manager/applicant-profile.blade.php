@extends('layouts.master')
@section('title')
    @lang('translation.profile')
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}">
    <style>
        .nav-link.done {
            background-color: #67b173 !important;
        }

        .progress-bar {
            background-color: #67b173 !important;
        }
    </style>
@endsection
@php
    // Sample ID
    $id = '900610';

    // Extract year, month, and day
    $year = substr($id, 0, 2);
    $month = substr($id, 2, 2);
    $day = substr($id, 4, 2);

    // Adjust year to be in the 1900s or 2000s
    $year = (intval($year) > date('y')) ? '19' . $year : '20' . $year;

    // Create a DateTime object from the extracted date
    $birthDate = new DateTime($year . '-' . $month . '-' . $day);

    // Calculate the age
    $now = new DateTime();
    $age = $now->diff($birthDate)->y;
@endphp

@section('content')
    <div class="profile-foreground position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg">
            <img src="{{ URL::asset('build/images/profile-bg.jpg') }}" alt="" class="profile-wid-img" />
        </div>
    </div>
    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
        <div class="row g-4">
            <div class="col-auto">
                <div class="col-auto" style="height: 6rem; width: 6rem; border-radius: 50%; overflow: hidden; display: inline-block;">
                    <img src="{{ URL::asset($applicant->avatar ?? 'images/avatar.jpg') }}" alt="applicant-img" class="img-thumbnail rounded-circle" style="width: 100%; height: 100%; object-fit: cover; object-position: center;" />
                </div>
            </div>
            <!--end col-->

            <div class="col">
                <div class="p-2">
                    <h3 class="text-white mb-1">{{ $applicant->firstname }} {{ $applicant->lastname }}</h3>
                    <p class="text-white text-opacity-75">
                        {{ optional($applicant->role)->name ?? 'N/A' }}
                    </p>
                    <div class="hstack text-white-50 gap-1">
                        <div class="me-2">
                            <i class="ri-user-2-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                            {{ optional($vacancy->position)->name ?? 'N/A' }}
                        </div>
                        <div>
                            <i class="ri-building-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                            {{ optional($vacancy->store->brand)->name ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
            <!--end col-->

        </div>
        <!--end row-->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div>
                <div class="d-flex profile-wrapper">
                    <!-- Nav tabs -->
                    <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link fs-14 applicant-tab active" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                <i class="ri-airplay-fill d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">Overview</span>
                            </a>
                        </li>
                        @if ($authUser->role_id <= 2)
                            <li class="nav-item">
                                <a class="nav-link fs-14 applicant-tab" data-bs-toggle="tab" href="#messaging-tab" role="tab">
                                    <i class="ri-price-tag-line d-inline-block d-md-none"></i> 
                                    <span class="d-none d-md-inline-block">Messaging</span>
                                </a>
                            </li>                        
                            <li class="nav-item">
                                <a class="nav-link fs-14 applicant-tab" data-bs-toggle="tab" href="#assessments-tab" role="tab">
                                    <i class="ri-folder-4-line d-inline-block d-md-none"></i> 
                                    <span class="d-none d-md-inline-block">Assessments</span>
                                </a>
                            </li>                        
                            <li class="nav-item">
                                <a class="nav-link fs-14 applicant-tab" data-bs-toggle="tab" href="#documents-tab" role="tab">
                                    <i class="ri-folder-4-line d-inline-block d-md-none"></i> 
                                    <span class="d-none d-md-inline-block">Documents</span>
                                </a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link fs-14 applicant-tab" data-bs-toggle="tab" href="#interview-tab" role="tab">
                                <i class="ri-folder-4-line d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">Interview</span>
                            </a>
                        </li>
                    </ul>
                    <div class="flex-shrink-0">
                        <a href="{{ route('shortlist.index') }}?id={{ Crypt::encryptString($vacancyId) }}" class="btn btn-success">
                            <i class="ri-arrow-left-line align-bottom"></i> 
                            Shortlist
                        </a>
                    </div>
                </div>
                <!-- Tab panes -->
                <div class="tab-content pt-4 text-muted">

                    <!-------------------------------------------------------------------------------------
                        Overview
                    -------------------------------------------------------------------------------------->

                    <div class="tab-pane active" id="overview-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-xxl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">Applicant Tracker</h5>
                                        <div id="custom-progress-bar" class="progress-nav">
                                            <div class="progress" style="height: 1px;">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $progressBarWidth }}%;" aria-valuenow="{{ $progressBarWidth }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                
                                            <ul class="nav nav-pills progress-bar-tab custom-nav" role="tablist">
                                                <li class="nav-item">
                                                    <button class="nav-link rounded-pill {{ $progressBarWidth >= 25 ? 'done' : '' }}" data-progressbar="custom-progress-bar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Application Submitted">
                                                        <i class="ri-profile-line align-bottom"></i>
                                                    </button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link rounded-pill {{ $progressBarWidth >= 45 ? 'done' : '' }}" data-progressbar="custom-progress-bar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Shortlisted">
                                                        <i class="ri-list-check-2 align-bottom"></i>
                                                    </button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link rounded-pill {{ $progressBarWidth >= 75 ? 'done' : '' }}" data-progressbar="custom-progress-bar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Interview Scheduled">
                                                        <i class="ri-calendar-check-line align-bottom"></i>
                                                    </button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link rounded-pill {{ $progressBarWidth >= 100 ? 'done' : '' }}" data-progressbar="custom-progress-bar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Interview Complete">
                                                        <i class="ri-group-line align-bottom"></i>
                                                    </button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link rounded-pill {{ $progressBarWidth >= 100 && $applicant->vacanciesFilled && $applicant->vacanciesFilled->count() > 0 ? 'done' : '' }}" data-progressbar="custom-progress-bar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Appointed">
                                                        <i class="ri-open-arm-line align-bottom"></i>
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">Application Progress</h5>
                                        <div class="progress animated-progress custom-progress progress-label">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                                <div class="label">
                                                    100%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Info</h5>
                                        <div class="table-responsive">
                                            <table class="table table-borderless mb-0">
                                                <tbody>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Name :</th>
                                                        <td class="text-muted">{{ $applicant->firstname ?? 'N/A' }} {{ $applicant->lastname }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Mobile :</th>
                                                        <td class="text-muted">{{ $applicant->phone ?? 'N/A' }}</td>
                                                    </tr>
                                                    @if ($applicant->email )
                                                        <tr>
                                                            <th class="ps-0" scope="row">Email :</th>
                                                            <td class="text-muted">{{ $applicant->email ?? 'N/A' }}</td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <th class="ps-0" scope="row">Brand :</th>
                                                        <td class="text-muted">{{ optional($applicant->brand)->name ?? 'N/A' }}</td>
                                                    </tr>                                                    
                                                    <tr>
                                                        <th class="ps-0" scope="row">Role :</th>
                                                        <td class="text-muted">{{ optional($applicant->role)->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Applied</th>
                                                        <td class="text-muted">{{ date('d M Y', strtotime($applicant->created_at)) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->                                
                            </div>
                            <!--end col-->
                            <div class="col-xxl-9"> 
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-header align-items-center d-flex">
                                                <h4 class="card-title mb-0  me-2">
                                                    Application
                                                </h4>
                                            </div>
                                            <div class="card-body">
                                                <!-- Accordions Bordered -->
                                                <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-secondary" id="accordionBordered">
                                                    
                                                    <!-------------------------------------------------------------------------------------
                                                        Personal Information
                                                    -------------------------------------------------------------------------------------->

                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="accordionborderedExample1">
                                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accor_borderedExamplecollapse1" aria-expanded="true" aria-controls="accor_borderedExamplecollapse1">
                                                                Personal Information
                                                            </button>
                                                        </h2>
                                                        <div id="accor_borderedExamplecollapse1" class="accordion-collapse collapse show" aria-labelledby="accordionborderedExample1" data-bs-parent="#accordionBordered">
                                                            <div class="accordion-body">
                                                                <div class="row">
                                                                    <div class="col-lg-4">
                                                                        <!-- Full Name -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-6">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Full Name
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                {{ $applicant->firstname ?? 'N/A' }} {{ $applicant->lastname }}
                                                                            </div>
                                                                        </div>

                                                                        <!-- ID Number -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-6">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    ID Number
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                {{ $applicant->id_number ?? 'N/A' }}
                                                                            </div>
                                                                        </div>

                                                                        <!-- Contact Number -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-6">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Contact Number
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                {{ $applicant->contact_number ?? 'N/A' }}
                                                                            </div>
                                                                        </div>

                                                                        <!-- Email Address -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-6">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Email Address
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                {{ $applicant->email ?? 'N/A' }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <!-- Address -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-6">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Address
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                {{ $applicant->location ?? 'N/A' }}
                                                                            </div>
                                                                        </div>

                                                                        <!-- Town -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-6">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Town
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                {{ optional($applicant->town)->name ?? 'N/A' }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <!-- Gender -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-6">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Gender
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                {{ optional($applicant->gender)->name ?? 'N/A' }}
                                                                            </div>
                                                                        </div>

                                                                        <!-- Ethnicity -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-6">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Ethnicity
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                {{ optional($applicant->race)->name ?? 'N/A' }}
                                                                            </div>
                                                                        </div>                                                                
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-------------------------------------------------------------------------------------
                                                        Job Information
                                                    -------------------------------------------------------------------------------------->

                                                    <div class="accordion-item mt-2">
                                                        <h2 class="accordion-header" id="accordionborderedExample2">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_borderedExamplecollapse2" aria-expanded="false" aria-controls="accor_borderedExamplecollapse2">
                                                                Job Information
                                                            </button>
                                                        </h2>
                                                        <div id="accor_borderedExamplecollapse2" class="accordion-collapse collapse" aria-labelledby="accordionborderedExample2" data-bs-parent="#accordionBordered">
                                                            <div class="accordion-body">
                                                                <div class="row">
                                                                    <div class="col-lg-6">
                                                                        <!-- Highest Qualification -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-3">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Highest Qualification
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-9">
                                                                                {{ optional($applicant->education)->name ?? 'N/A' }}
                                                                            </div>
                                                                        </div>

                                                                        <!-- Retail Experience -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-3">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Retail Experience
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-9">
                                                                                {{ optional($applicant->duration)->name ?? 'N/A' }}
                                                                            </div>
                                                                        </div>

                                                                        <!-- Type of Store -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-3">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Type of Store
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-9">
                                                                                @if($applicant->brands && $applicant->brands->isNotEmpty())
                                                                                    {{ $applicant->brands->pluck('name')->implode(', ') }}
                                                                                @else
                                                                                    N/A
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-lg-6">
                                                                        <!-- Rotational Shift -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-3">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Rotational Shift 
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-9">
                                                                                {{ optional($applicant)->public_holidays ?? 'N/A' }}
                                                                            </div>
                                                                        </div>

                                                                        <!-- Heavy Lifting -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-3">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Heavy Lifting 
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-9">
                                                                                {{ optional($applicant)->environment ?? 'N/A' }}
                                                                            </div>
                                                                        </div>

                                                                        <!-- Disability -->
                                                                        <div class="row mb-3">
                                                                            <div class="col-lg-3">
                                                                                <h6 class="fs-15 mb-0">
                                                                                    Disability 
                                                                                </h6>
                                                                            </div>
                                                                            <div class="col-lg-9">
                                                                                {{ optional($applicant)->disability ?? 'N/A' }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>                                                
                                            </div><!-- end card body -->
                                        </div><!-- end card -->
                                    </div><!-- end col -->
                                </div><!-- end row -->
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    
                    <!-------------------------------------------------------------------------------------
                        Messaging Tab
                    -------------------------------------------------------------------------------------->

                    @if ($authUser->role_id <= 2)
                        <div class="tab-pane fade" id="messaging-tab" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="chat-wrapper d-lg-flex gap-1 mx-n4 mt-n4 p-1">
                                            <!-- Start User chat -->
                                            <div class="user-chat w-100 overflow-hidden">
                                        
                                                <div class="chat-content d-lg-flex">
                                                    <!-- start chat conversation section -->
                                                    <div class="w-100 overflow-hidden position-relative">
                                                        <!-- conversation user -->
                                                        <div class="position-relative">
                                        
                                        
                                                            <div class="position-relative" id="users-chat">
                                                                <div class="p-3 user-chat-topbar">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 col-8">
                                                                            <div class="d-flex align-items-center">
                                                                                <div class="flex-shrink-0 d-block d-lg-none me-3">
                                                                                    <a href="javascript: void(0);" class="user-chat-remove fs-18 p-1">
                                                                                        <i class="ri-arrow-left-s-line align-bottom"></i>
                                                                                    </a>
                                                                                </div>
                                                                                <div class="flex-grow-1 overflow-hidden">
                                                                                    <div class="d-flex align-items-center">
                                                                                        <div class="flex-shrink-0 chat-user-img {{ $applicant->status_id == 1 ? 'online' : 'away' }} user-own-img align-self-center me-3 ms-0">
                                                                                            <img src="{{ URL::asset($applicant->avatar ?? 'images/avatar.jpg') }}" class="rounded-circle avatar-xs" alt="">
                                                                                            <span class="user-status"></span>
                                                                                        </div>
                                                                                        <div class="flex-grow-1 overflow-hidden">
                                                                                            <h5 class="text-truncate mb-0 fs-16">
                                                                                                <a class="text-reset username">
                                                                                                    {{ $applicant->firstname ?? 'N/A' }} {{ $applicant->lastname ?? 'N/A' }}
                                                                                                </a>
                                                                                            </h5>
                                                                                            <p class="text-truncate text-muted mb-0 userStatus">
                                                                                                <small>
                                                                                                    {{ $applicant->status ? $applicant->status->name : 'Offline' }}
                                                                                                </small>
                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                        
                                                                </div>
                                                                <!-- end chat user head -->
                                                                <div class="chat-conversation p-3 p-lg-4 " id="chat-conversation" data-simplebar>
                                                                    <ul class="list-unstyled chat-conversation-list" id="users-conversation">
                                        
                                                                    </ul>
                                                                    <!-- end chat-conversation-list -->
                                                                </div>
                                                                <div class="alert alert-warning alert-dismissible copyclipboard-alert px-4 fade show " id="copyClipBoard" role="alert">
                                                                    Message copied
                                                                </div>
                                                            </div>
                                                                                                
                                                            <div class="chat-input-section p-3 p-lg-4">
                                        
                                                                <form class="d-none" id="chatinput-form" enctype="multipart/form-data">
                                                                    <div class="row g-0 align-items-center">
                                                                        <div class="col-auto">
                                                                            <div class="chat-input-links me-2">
                                                                                <div class="links-list-item">
                                                                                    <button type="button" class="btn btn-link text-decoration-none emoji-btn" id="emoji-btn">
                                                                                        <i class="bx bx-smile align-middle"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                        
                                                                        <div class="col">
                                                                            <div class="chat-input-feedback">
                                                                                Please Enter a Message
                                                                            </div>
                                                                            <input type="text" class="form-control chat-input bg-light border-light" id="chat-input" placeholder="Type your message..." autocomplete="off">
                                                                        </div>
                                                                        <div class="col-auto">
                                                                            <div class="chat-input-links ms-2">
                                                                                <div class="links-list-item">
                                                                                    <button type="submit" class="btn btn-success chat-send waves-effect waves-light">
                                                                                        <i class="ri-send-plane-2-fill align-bottom"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                        
                                                                    </div>
                                                                </form>
                                                            </div>
                                        
                                                            <div class="replyCard">
                                                                <div class="card mb-0">
                                                                    <div class="card-body py-3">
                                                                        <div class="replymessage-block mb-0 d-flex align-items-start">
                                                                            <div class="flex-grow-1">
                                                                                <h5 class="conversation-name"></h5>
                                                                                <p class="mb-0"></p>
                                                                            </div>
                                                                            <div class="flex-shrink-0">
                                                                                <button type="button" id="close_toggle" class="btn btn-sm btn-link mt-n2 me-n3 fs-18">
                                                                                    <i class="bx bx-x align-middle"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end chat-wrapper -->                               
                                    </div>
                                    <!--end row-->
                                </div>
                                <!--end card-body-->
                            </div>
                            <!--end card-->
                        </div>
                        <!--end tab-pane-->
                    @endif

                    <!-------------------------------------------------------------------------------------
                        Assessments
                    -------------------------------------------------------------------------------------->

                    @if ($authUser->role_id <= 2)
                        <div class="tab-pane fade" id="assessments-tab" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4">
                                        <h5 class="card-title flex-grow-1 mb-0">
                                            Assessments
                                        </h5>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-title mb-0">Literacy Score</h4>
                                                </div><!-- end card header -->
                                
                                                <div class="card-body">
                                                    <div id="literacy_chart" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
                                                </div><!-- end card-body -->
                                            </div><!-- end card -->
                                        </div>
                                        <!-- end col -->                                    

                                        <div class="col-xl-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-title mb-0">Numeracy Score</h4>
                                                </div><!-- end card header -->
                                
                                                <div class="card-body">
                                                    <div id="numeracy_chart" data-colors='["--vz-secondary"]' class="apex-charts" dir="ltr"></div>
                                                </div><!-- end card-body -->
                                            </div><!-- end card -->
                                        </div>
                                        <!-- end col -->

                                        <div class="col-xl-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-title mb-0">Situational Score</h4>
                                                </div><!-- end card header -->
                                
                                                <div class="card-body">
                                                    <div id="situational_chart" data-colors='["--vz-danger"]' class="apex-charts" dir="ltr"></div>
                                                </div><!-- end card-body -->
                                            </div><!-- end card -->
                                        </div>
                                        <!-- end col -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end tab-pane-->
                    @endif

                    <!-------------------------------------------------------------------------------------
                        Documents Tab
                    -------------------------------------------------------------------------------------->

                    @if ($authUser->role_id <= 2)
                        <div class="tab-pane fade" id="documents-tab" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4">
                                        <h5 class="card-title flex-grow-1 mb-0">
                                            Documents
                                        </h5>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="table-responsive">
                                                <table class="table align-middle table-nowrap mb-0" id="fileTable">
                                                    <thead class="table-active">
                                                        <tr>
                                                            <th scope="col">Name</th>
                                                            <th scope="col">Type</th>
                                                            <th scope="col">Size</th>
                                                            <th scope="col">Upload Date</th>
                                                            <th scope="col" class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="file-list">
                                                        @foreach ($documents as $file)
                                                            @php
                                                                $fileIcon = '';
                                                            @endphp
                                                            
                                                            @switch($file->type)
                                                                @case('png')
                                                                @case('jpg')
                                                                @case('jpeg')
                                                                    @php
                                                                        $fileIcon = '<i class="ri-gallery-fill align-bottom text-success"></i>';
                                                                    @endphp
                                                                    @break
                                                            
                                                                @case('pdf')
                                                                    @php
                                                                        $fileIcon = '<i class="ri-file-pdf-fill align-bottom text-danger"></i>';
                                                                    @endphp
                                                                    @break
                                                            
                                                                @case('docx')
                                                                    @php
                                                                        $fileIcon = '<i class="ri-file-word-2-fill align-bottom text-primary"></i>';
                                                                    @endphp
                                                                    @break
                                                            
                                                                @case('xls')
                                                                @case('xlsx')
                                                                    @php
                                                                        $fileIcon = '<i class="ri-file-excel-2-fill align-bottom text-success"></i>';
                                                                    @endphp
                                                                    @break
                                                            
                                                                @case('csv')
                                                                    @php
                                                                        $fileIcon = '<i class="ri-file-excel-fill align-bottom text-success"></i>';
                                                                    @endphp
                                                                    @break
                                                            
                                                                @case('txt')
                                                                @default
                                                                    @php
                                                                        $fileIcon = '<i class="ri-file-text-fill align-bottom text-secondary"></i>';
                                                                    @endphp
                                                            @endswitch
                                                            <tr data-file-id="{{ $file->id }}">
                                                                <td>
                                                                    <a href="{{ route('document.view', ['id' => Crypt::encryptString($file->id)]) }}" target="_blank">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="flex-shrink-0 fs-17 me-2 filelist-icon">{!! $fileIcon !!}</div>
                                                                            <div class="flex-grow-1 filelist-name">{{ substr($file->name, 0, strrpos($file->name, '-')) }}</div>
                                                                        </div>
                                                                    </a>
                                                                </td>
                                                                <td>
                                                                    {{ $file->type }}
                                                                </td>
                                                                @php
                                                                    $fileSizeInMB = $file->size / (1024 * 1024);
                                                                    if ($fileSizeInMB < 0.1) {
                                                                        $fileSizeInKB = number_format($file->size / 1024, 1);
                                                                        $fileSizeText = "{$fileSizeInKB} KB";
                                                                    } else {
                                                                        $fileSizeInMB = number_format($fileSizeInMB, 1);
                                                                        $fileSizeText = "{$fileSizeInMB} MB";
                                                                    }
                                                                @endphp
                                                                <td class="filelist-size">                                            
                                                                    {{ $fileSizeText }}
                                                                </td>
                                                                <td class="filelist-create">
                                                                    {{ date('d M Y', strtotime($file->created_at)) }}
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex gap-3 justify-content-center">
                                                                        <div class="dropdown">
                                                                            <button class="btn btn-light btn-icon btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                                <i class="ri-more-fill align-bottom"></i>
                                                                            </button>
                                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                                <li>
                                                                                    <a class="dropdown-item viewfile-list" href="{{ route('document.view', ['id' => Crypt::encryptString($file->id)]) }}" target="_blank">
                                                                                        View
                                                                                    </a>
                                                                                </li>
                                                                                <li class="dropdown-divider"></li>
                                                                                <li>
                                                                                    <a class="dropdown-item downloadfile-list" href="{{ route('document.download', ['id' => Crypt::encryptString($file->id)]) }}">
                                                                                        Download
                                                                                    </a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end row-->
                                </div>
                                <!--end card-body-->
                            </div>
                            <!--end card-->
                        </div>
                        <!--end tab-pane-->
                    @endif

                    <!-------------------------------------------------------------------------------------
                        Interview Guide
                    -------------------------------------------------------------------------------------->

                    <div class="tab-pane fade" id="interview-tab" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-1">
                                    <h5 class="card-title flex-grow-1 mb-0">
                                        Interview Guide
                                    </h5>
                                
                                    @if (!$applicant->appointed_id)
                                        @if(count($applicant->interviews) <= 0 || (count($applicant->interviews) > 0 && !$applicant->interviews[0]->score))
                                            <button class="btn btn-secondary ms-3" id="interviewBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="Schedule an interview with {{ $applicant->firstname }}">
                                                <i class="ri-calendar-todo-fill align-bottom me-1"></i> 
                                                Interview
                                            </button>
                                            
                                            <button class="btn btn-light ms-3" id="noShowBtn" data-bs-toggle="modal" data-bs-target="#interviewNoShowModal" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $applicant->firstname }} did not show up for their interview">
                                                <i class="ri-calendar-todo-fill align-bottom me-1"></i> 
                                                No Show
                                            </button>
                                        @endif
                                    @endif
                                </div>
                                
                                <!-- New row for the alert -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div id="interviewAlert">
                                            @if ($applicant->interviews->count() > 0)
                                                @php
                                                    // Define the status mapping similar to your JavaScript object
                                                    $statusMapping = [
                                                        'Scheduled' => [
                                                            'class' => 'warning',
                                                            'icon' => 'ri-calendar-todo-fill'
                                                        ],
                                                        'Confirmed' => [
                                                            'class' => 'success',
                                                            'icon' => 'ri-calendar-check-fill'
                                                        ],
                                                        'Declined' => [
                                                            'class' => 'danger',
                                                            'icon' => 'ri-calendar-2-fill'
                                                        ],
                                                        'Reschedule' => [
                                                            'class' => 'info',
                                                            'icon' => 'ri-calendar-event-fill'
                                                        ],
                                                        'Completed' => [
                                                            'class' => 'success',
                                                            'icon' => 'ri-calendar-check-fill'
                                                        ],
                                                        'Cancelled' => [
                                                            'class' => 'dark',
                                                            'icon' => 'ri-calendar-2-fill'
                                                        ],
                                                        'No Show' => [
                                                            'class' => 'danger',
                                                            'icon' => 'ri-user-unfollow-fill'
                                                        ]
                                                    ];
                                                
                                                    // Get the status of the interview
                                                    $status = $applicant->interviews[0]->status;
                                                
                                                    // Get the corresponding color and icon for the status
                                                    $statusColor = $statusMapping[$status]['class'] ?? 'warning'; // Default to 'warning' if status not found
                                                    $statusIcon = $statusMapping[$status]['icon'] ?? 'ri-calendar-todo-fill'; // Default to 'ri-calendar-todo-fill' if status not found
                                                @endphp
                                                
                                                <div class="alert alert-{{ $statusColor }} alert-dismissible alert-label-icon rounded-label fade show mb-0" role="alert"
                                                    data-status-color="{{ $statusColor }}" data-status-icon="{{ $statusIcon }}">
                                                    <i class="{{ $statusIcon }} label-icon"></i>
                                                    <strong>{{ $status }}:</strong> {{ $applicant->interviews[0]->scheduled_date->format('d M') }} at {{ $applicant->interviews[0]->start_time->format('H:i') }}-{{ $applicant->interviews[0]->end_time->format('H:i') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xxl-12 mb-4">
                                        <strong>Welcome to Shoprite</strong>, where precision in talent acquisition meets innovative assessment. As a manager, your discernment is critical in shaping the future of our team. We are committed to providing you with an intuitive and robust platform to evaluate applicants seamlessly. Our structured rating system is designed to capture the nuanced performance of candidates, ensuring that your hiring decisions are informed, fair, and aligned with our organizational benchmarks.
                                        <br><br>
                                        Each question below is an opportunity to gauge the candidate's potential fit within our corporate ecosystem. We rely on your expert judgment to rate the candidate's responses on a scale from 1 to 5 stars, with 1 star indicating a need for significant improvement, and 5 stars representing exceptional alignment with the role's requirements and our company values.
                                        <br><br>
                                        Please take into account not only the content of the responses but also the candidate's problem-solving abilities, adaptability, collaborative spirit, leadership potential, communication clarity, customer orientation, integrity, time management skills, and overall compatibility with our company culture.
                                        <br><br>
                                        We value your insight and look forward to your contributions to our collective success.
                                    </div>
                                    <!-- Interview Form Placeholder -->
                                    <div id="interviewFormContainer">
                                        @if ($applicant->interviews->count() > 0)
                                            @php
                                                $interview = $applicant->interviews[0];
                                                $validStatuses = ['Scheduled', 'Confirmed', 'Reschedule', 'Completed'];
                                            @endphp

                                            @if (in_array($interview->status, $validStatuses))
                                                @if ($applicant->appointed_id)
                                                    <div class="alert alert-success mb-xl-0 text-center" role="alert">
                                                        <strong>This applicant has been appointed!</strong>
                                                    </div>
                                                @else
                                                    @if ($applicant->interviews[0]->score)
                                                        <h1 class="display-2 coming-soon-text text-center">
                                                            {{ $applicant->interviews[0]->score }}
                                                        </h1>
                                                    @else
                                                        @if ($questions->isEmpty())
                                                            <div class="alert alert-danger mb-xl-0 text-center" role="alert">
                                                                <strong>Sorry, no interview template has been loaded</strong> for this position. Please <b>contact your administrator</b>
                                                            </div>
                                                        @else
                                                            <form class="mt-3" id="formInterview" enctype="multipart/form-data">
                                                                <input type="hidden" id="interviewID" name="interview_id" value="{{ Crypt::encryptstring($applicant->interviews[0]->id) }}"/>
                                                                @foreach ($questions as $question)
                                                                    <div class="form-group mb-4">
                                                                        <label class="form-label fs-16" style="width:100%;">
                                                                            <div class="row" style="width:100%;">
                                                                                <div class="col-sm-1">
                                                                                    {{ $question->id }}.) 
                                                                                </div>
                                                                                <div class="col-sm-11">
                                                                                    {!! $question->question !!}
                                                                                </div>
                                                                            </div>
                                                                        </label>
                                                                        <div class="col-sm-11 offset-sm-1">
                                                                            <div class="d-flex">
                                                                                @if ($question->type == 'text')
                                                                                    <input type="text" class="form-control" name="answers[{{$question->id}}]" required>
                                                                                @elseif ($question->type == 'number')
                                                                                    <input type="number" class="form-control" name="answers[{{$question->id}}]" required>
                                                                                @elseif ($question->type == 'rating')
                                                                                    <div class="form-check">
                                                                                        <input class="form-check-input d-none" type="hidden" name="answers[{{$question->id}}]" id="rating-{{$question->id}}" required>
                                                                                        @for ($i = 1; $i <= 5; $i++)
                                                                                            <label class="form-check-label" for="rating-{{$question->id}}-{{$i}}" style="cursor: pointer; margin-right:20px;">
                                                                                                <i class="ri-star-line" id="star-{{$question->id}}-{{$i}}" style="font-size: 1.5em; color: grey;"></i>
                                                                                            </label>
                                                                                        @endfor
                                                                                        <span class="invalid-feedback" role="alert" style="display:none">
                                                                                            <strong>Please select a rating</strong>
                                                                                        </span>
                                                                                    </div>
                                                                                    <script>
                                                                                        document.addEventListener('DOMContentLoaded', function() {
                                                                                            let stars = document.querySelectorAll('[id^="star-{{$question->id}}-"]');
                                                                                            stars.forEach(star => {
                                                                                                star.addEventListener('click', function() {
                                                                                                    let rating = parseInt(star.id.split('-').pop());
                                                                                                    for (let i = 1; i <= rating; i++) {
                                                                                                        document.querySelector('#star-{{$question->id}}-' + i).classList.remove('ri-star-line');
                                                                                                        document.querySelector('#star-{{$question->id}}-' + i).classList.add('ri-star-fill');
                                                                                                        document.querySelector('#star-{{$question->id}}-' + i).style.color = 'gold';
                                                                                                    }
                                                                                                    for (let i = rating + 1; i <= 5; i++) {
                                                                                                        document.querySelector('#star-{{$question->id}}-' + i).classList.remove('ri-star-fill');
                                                                                                        document.querySelector('#star-{{$question->id}}-' + i).classList.add('ri-star-line');
                                                                                                        document.querySelector('#star-{{$question->id}}-' + i).style.color = 'grey';
                                                                                                    }
                                                                                                    document.querySelector('#rating-{{$question->id}}').value = rating;
                                                                                                });
                                                                                            });
                                                                                        });
                                                                                    </script>
                                                                                @elseif ($question->type == 'textarea')
                                                                                    <textarea class="form-control" name="answers[{{$question->id}}]" rows="5" required></textarea>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                                <div class="d-grid gap-2">
                                                                    <button class="btn btn-success" type="submit">
                                                                        Submit
                                                                    </button>
                                                                </div>
                                                            </form>
                                                            <h1 class="display-2 coming-soon-text text-center" id="scoreDisplay" style="display: none;">
                                                                <!-- The score will be injected here -->
                                                            </h1>
                                                        @endif
                                                    @endif
                                                @endif
                                            @endif
                                        @endif
                                    </div>                   
                                </div>
                                <!--end row-->
                            </div>
                            <!--end card-body-->
                        </div>
                        <!--end card-->
                    </div>
                    <!--end tab-pane-->
                </div>
                <!--end tab-content-->
            </div>

            @if ($vacancyId)
                @include('manager.partials.interview-modal', ['authUser' => $authUser, 'vacancyId' => $vacancyId, 'vacancy' => $vacancy, 'applicantId' => $applicant->id])

                @if(isset($applicant->interviews) && $applicant->interviews->isNotEmpty())
                    @include('manager.partials.noshow-modal', ['vacancyId' => $vacancyId, 'applicantId' => $applicant->id, 'interviewId' => $applicant->interviews[0]->id])
                @endif
            @endif

        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
@section('script')
    @if ($authUser->role_id <= 2)
        <script>
            var literacyScore = {{ $applicant->literacy_score ?? 0 }};            
            var literacyQuestions = {{ $applicant->literacy_questions ?? 10 }};
            var literacy = "{{ $applicant->literacy  ?? 0/10 }}";

            var numeracyScore = {{ $applicant->numeracy_score ?? 0 }};
            var numeracyQuestions = {{ $applicant->numeracy_questions  ?? 10 }};
            var numeracy = "{{ $applicant->numeracy ?? 0/10 }}";

            var situationalScore = {{ $applicant->situational_score ?? 0 }};
            var situationalQuestions = {{ $applicant->situational_questions ?? 5 }};
            var situational = "{{ $applicant->situational ?? 0/5 }}";

            var chatsData = @json($applicant->chats);
        </script>
    @endif

    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/applicant-profile.init.js') }}?v={{ filemtime(public_path('build/js/pages/applicant-profile.init.js')) }}"></script>

    <!-- Chat-->
    <script src="{{ URL::asset('build/libs/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/fg-emoji-picker/fgEmojiPicker.js') }}"></script>
    @if ($authUser->role_id <= 2)
        <script src="{{ URL::asset('build/js/pages/applicant-chat.init.js') }}"></script>
    @endif
    
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
