@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection
@section('css')
<link href="{{ URL::asset('build/libs/jsvectormap/css/jsvectormap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css')}}" rel="stylesheet" type="text/css" />
<style>
.s0 {
    opacity: .05;
    fill: var(--vz-primary)
}

.s1 {
    opacity: .05;
    fill: var(--vz-secondary)
}

.s2 {
    opacity: .05;
    fill: var(--vz-success)
}

.s3 {
    opacity: .05;
    fill: var(--vz-danger)
}

.s4 {
    opacity: .05;
    fill: var(--vz-warning)
}

.s5 {
    opacity: .05;
    fill: var(--vz-info)
}
</style>
@endsection
@section('content')

<div class="row">
    <div class="col">

        <div class="h-100">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1" style="width: 60%;">
                            <h4 class="fs-16 mb-1">
                                Hello, {{ Auth::user()->firstname }}!
                            </h4>
                            <p class="text-muted mb-0">
                                Here's what's happening with Shoprite today.
                            </p>
                        </div>
                        <div class="mt-3 mt-lg-0" style="width: 40%;">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-end justify-content-end">
                                    <div class="col-8">
                                        <div class="input-group">
                                            <input type="text" id="dateFilter" class="form-control border-0 dash-filter-picker shadow flatpickr-input">
                                            <div class="input-group-text bg-primary border-primary text-white" id="dateFilterIcon">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-soft-info btn-icon waves-effect waves-light layout-rightside-btn"><i class="ri-pulse-line"></i></button>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                    </div><!-- end card header -->
                </div>
                <!--end col-->
            </div>

            <!-------------------------------------------------------------------------------------
                Vacancies
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Total Vacancies -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s0" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Total Vacancies
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalVacanciesValue" class="counter-value" data-target="{{ $totalVacancies }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            
                <!-- Total Vacancies Filled -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s0" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Total Vacancies Filled
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalVacanciesFilledValue" class="counter-value" data-target="{{ $totalVacanciesFilled }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_vacancies_filled" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            </div> <!--end row -->
            
            <!-------------------------------------------------------------------------------------
                Interviews
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Total Interviews Scheduled -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s1" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Interviews Scheduled
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalInterviewsScheduledValue"  class="counter-value" data-target="{{ $totalInterviewsScheduled }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            
                <!-- Total Interviews Completed -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s1" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Interviews Completed
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalInterviewsCompletedValue" class="counter-value" data-target="{{ $totalInterviewsCompleted }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_interviews_completed" data-colors='["--vz-secondary"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            </div> <!--end row -->

            <!-------------------------------------------------------------------------------------
                Applicants
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Total Applicants Appointed -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s2" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Applicants Appointed
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalApplicantsAppointedValue" class="counter-value" data-target="{{ $totalApplicantsAppointed }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_applicants_appointed" data-colors='["--vz-success"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            
                <!-- Total Applicants Regretted -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s3" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Applicants Regretted
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalApplicantsRegrettedValue" class="counter-value" data-target="{{ $totalApplicantsRegretted }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_applicants_regretted" data-colors='["--vz-danger"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            </div> <!--end row -->

            <!-------------------------------------------------------------------------------------
                Time
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Time to Shortlist -->
                <div class="col-xl-4 col-md-4" id="averageTimeToShortlistColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Time to Shortlist
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="averageTimeToShortlistValue">
                                            {{ $averageTimeToShortlist }}
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0">
                                            Average
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-secondary-subtle rounded-circle fs-2">
                                            <i data-feather="watch" class="text-secondary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->

                <!-- Time to Hire -->
                <div class="col-xl-4 col-md-4" id="averageTimeToHireColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Time to Hire
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="averageTimeToHireValue">
                                            {{ $averageTimeToHire }}
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0">
                                            Average
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-secondary-subtle rounded-circle fs-2">
                                            <i data-feather="watch" class="text-secondary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->

                <!-- Adoption Rate -->
                <div class="col-xl-4 col-md-4" id="adoptionRateColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Adoption Rate
                                    </p>
                                    <h2 class="mt-4 ff-success fw-bold">
                                        <span id="adoptionRateValue">
                                            {{ $adoptionRate }}%
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-success mb-0" id="adoptionRate">
                                           Percentage
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle rounded-circle fs-2">
                                            <i data-feather="user-check" class="text-success"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->


        </div> <!-- end .h-100-->

    </div> <!-- end col -->

    <!-------------------------------------------------------------------------------------
        Side Bar
    -------------------------------------------------------------------------------------->

    <div class="col-auto layout-rightside-col">
        <div class="overlay"></div>
        <div class="layout-rightside">
            <div class="card h-100 rounded-0">
                <div class="card-body p-0">
                    <div class="p-3">
                        <h6 class="text-muted mb-0 text-uppercase fw-bold fs-13">
                            Recent Activity
                        </h6>
                    </div>
                    <div data-simplebar style="max-height: 410px;" class="p-3 pt-0">
                        <div class="acitivity-timeline acitivity-main">
                            @foreach($activities as $activity)
                                @php
                                    $iconClass = "";
                                    $bgClass = "";
                                    $subjectName = "";
                                    $showActivity = true;
                                    
                                    switch($activity->event) {
                                        case 'created':
                                            switch($activity->subject_type) {
                                                case 'App\Models\Vacancy':
                                                    $iconClass = "ri-briefcase-fill";
                                                    $bgClass = "bg-primary-subtle text-primary";
                                                    break;
                                                case 'App\Models\Applicant':
                                                    $iconClass = "ri-profile-fill";
                                                    $bgClass = "bg-success-subtle text-success";
                                                    break;
                                                case 'App\Models\Message':
                                                    $iconClass = "ri-chat-3-line";
                                                    $bgClass = "bg-success-subtle text-success";
                                                    break;
                                                case 'App\Models\Application':
                                                    $iconClass = "ri-user-add-line";
                                                    $bgClass = "bg-secondary-subtle text-secondary";
                                                    break;
                                                case 'App\Models\User':
                                                    $iconClass = "ri-user-line";
                                                    $bgClass = "bg-info-subtle text-info";                                                    
                                                    break;
                                                default:
                                                    $iconClass = "ri-stackshare-line";
                                                    $bgClass = "bg-info-subtle text-info"; 
                                            }  
                                            $subjectName = "Created";                                          
                                            break;
                                        case 'deleted':
                                            $iconClass = "ri-delete-bin-line";
                                            $bgClass = "bg-danger-subtle text-danger";
                                            $subjectName = "Deleted";
                                            break;
                                        case 'updated':
                                            $iconClass = "ri-edit-line";
                                            $bgClass = "bg-warning-subtle text-warning";
                                            $subjectName = "Updated";
                                            break;
                                        case 'accessed':
                                            $iconClass = "ri-eye-line";
                                            $bgClass = "bg-info-subtle text-info"; 
                                            $subjectName = "Viewed";
                                            break;
                                        default:
                                            $showActivity = false;
                                    }
                                @endphp

                                @if($showActivity)
                                    <div class="acitivity-item d-flex py-2">
                                        <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">                                  
                                            <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                <div class="avatar-title {{ $bgClass }} rounded-circle">
                                                    <i class="{{ $iconClass }}"></i>
                                                </div>
                                            </div> 
                                        </a>                                   
                                        <div class="flex-grow-1 ms-3">
                                            @php
                                                $activityAttributes = json_decode($activity->properties, true);
                                            @endphp

                                            <!-------------------------------------------------------------------------------------
                                                Created
                                            -------------------------------------------------------------------------------------->

                                            @if($activity->event === "created")                                                
                                                @if ($activity->subject_type === "App\Models\Vacancy")
                                                    @php                                                        
                                                        $vacancy = $activity->subject; // This should be the related Vacancy model with loaded relationships
                                                        $positionName = $vacancy ? optional($vacancy->position)->name : 'N/A';
                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                    @endphp
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">                                                    
                                                        <h6 class="mb-1 lh-base">                                                
                                                            {{ $subjectName }} {{ strtolower(class_basename($activity->subject_type)) }}: <span class="text-primary">{{ $positionName }}</span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}" style="">
                                                        <p class="text-muted mb-1">
                                                            {{ $positionName }}
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $brandName }} ({{ $townName }})
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $typeName }}
                                                        </p>
                                                    </div>
                                                @elseif ($activity->subject_type === "App\Models\Applicant")
                                                    @php
                                                        $applicantPosition = $activity->subject->position ?? null;
                                                        $applicantPositionName = $applicantPosition ? $applicantPosition->name : 'N/A';
                                                        if ($applicantPositionName === "Other") {
                                                            $applicantPositionName = $activityAttributes['attributes']['position_specify'] ?? 'N/A';
                                                        }
                                                        $firstname = isset($activityAttributes['attributes']['firstname']) ? $activityAttributes['attributes']['firstname'] : 'N/A';
                                                        $lastname = isset($activityAttributes['attributes']['lastname']) ? $activityAttributes['attributes']['lastname'] : 'N/A';
                                                    @endphp
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">
                                                        <h6 class="mb-1 lh-base">                                                
                                                            Submitted Application: <span class="text-success">{{ $applicantPositionName }}</span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}">
                                                        <p class="text-muted mb-1">
                                                            {{ $firstname }} {{ $lastname }}
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $applicantPositionName }}
                                                        </p>
                                                    </div>
                                                @elseif ($activity->subject_type === "App\Models\User")
                                                    @php
                                                        $firstname = isset($activityAttributes['attributes']['firstname']) ? $activityAttributes['attributes']['firstname'] : 'N/A';
                                                        $lastname = isset($activityAttributes['attributes']['lastname']) ? $activityAttributes['attributes']['lastname'] : 'N/A';
                                                        $role = $activity->subject->role ? $activity->subject->role->name : 'N/A';
                                                        $brand = $activity->subject->brand ? $activity->subject->brand->name : 'N/A';
                                                    @endphp
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">
                                                        <h6 class="mb-1 lh-base">                                                
                                                            Created User: <span class="text-success">{{ $firstname }} {{ $lastname }}</span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}">
                                                        <p class="text-muted mb-1">
                                                            {{ $role }}
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $brand }}
                                                        </p>
                                                    </div>                                              
                                                @elseif ($activity->subject_type === "App\Models\Message")
                                                    @php
                                                        $message = isset($activityAttributes['attributes']['message']) ? $activityAttributes['attributes']['message'] : 'N/A';
                                                        $userFrom = $activity->subject->from ?? null;
                                                        $userTo = $activity->subject->to ?? null;
                                                        $userFromName = $userFrom ? $userFrom->firstname . ' ' . $userFrom->lastname : 'N/A';
                                                        $userToName = $userTo ? $userTo->firstname . ' ' . $userTo->lastname : 'N/A';
                                                    @endphp

                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">
                                                        @if($activity->causer_id == Auth::id()) {{-- Message sent by the authenticated user --}}
                                                            <h6 class="mb-1 lh-base">                                                
                                                                Sent {{ strtolower(class_basename($activity->subject_type)) }} to: <span class="text-success">{{ $userToName }}</span>
                                                            </h6>
                                                        @else {{-- Message received by the authenticated user --}}
                                                            <h6 class="mb-1 lh-base">                                                
                                                                Received {{ strtolower(class_basename($activity->subject_type)) }} from: <span class="text-success">{{ $userFromName }}</span>
                                                            </h6>
                                                        @endif
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}">
                                                        <p class="text-muted mb-1">
                                                            {{ $message }}
                                                        </p>
                                                    </div>
                                                @elseif ($activity->subject_type === "App\Models\Application")
                                                    @php
                                                        $vacancy = $activity->subject->vacancy ?? null;
                                                        $positionName = $vacancy ? optional($vacancy->position)->name : 'N/A';
                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                        $applicationUser = $activity->subject->user ?? null;
                                                        $applicationUserName = $applicationUser ? $applicationUser->firstname . ' ' . $applicationUser->lastname : 'N/A';
                                                    @endphp
                                                
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">
                                                        @if($activity->causer_id == Auth::id()) {{-- Connection request sent by the authenticated user --}}
                                                            <h6 class="mb-1 lh-base">                                                
                                                                Applied for: <span class="text-secondary">{{ $positionName }}</span>
                                                            </h6>
                                                        @else {{-- Connection request received by the authenticated user --}}
                                                            <h6 class="mb-1 lh-base">                                                
                                                                Application request from: <span class="text-secondary">{{ $applicationUserName }}</span>
                                                            </h6>
                                                        @endif
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}">
                                                        @if($activity->subject)
                                                            <p class="text-muted mb-1">
                                                                {{ $positionName }}
                                                            </p>
                                                            <p class="text-muted mb-1">
                                                                {{ $brandName }} ({{ $townName }})
                                                            </p>
                                                            <p class="text-muted mb-1">
                                                                {{ $typeName }}
                                                            </p>
                                                        @endif
                                                    </div>                                                  
                                                @endif

                                            <!-------------------------------------------------------------------------------------
                                                Updated
                                            -------------------------------------------------------------------------------------->

                                            @elseif($activity->event === "updated")
                                                @if ($activity->subject_type === "App\Models\Vacancy")
                                                    @php                                                        
                                                        $vacancy = $activity->subject; // This should be the related Vacancy model with loaded relationships
                                                        $positionName = $vacancy ? optional($vacancy->position)->name : 'N/A';
                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                    @endphp
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">                                                    
                                                        <h6 class="mb-1 lh-base">                                                
                                                            {{ $subjectName }} {{ strtolower(class_basename($activity->subject_type)) }}: <span class="text-warning">{{ $positionName }}</span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}" style="">
                                                        <p class="text-muted mb-1">
                                                            {{ $positionName }}
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $brandName }} ({{ $townName }})
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $typeName }}
                                                        </p>
                                                    </div>
                                                @elseif ($activity->subject_type === "App\Models\Applicant")
                                                    @php
                                                        $applicantPosition = $activity->subject->position ?? null;
                                                        $applicantPositionName = $applicantPosition ? $applicantPosition->name : 'N/A';
                                                        if ($applicantPositionName === "Other") {
                                                            $applicantPositionName = $activityAttributes['attributes']['position_specify'] ?? 'N/A';
                                                        }
                                                        $firstname = isset($activityAttributes['attributes']['firstname']) ? $activityAttributes['attributes']['firstname'] : 'N/A';
                                                        $lastname = isset($activityAttributes['attributes']['lastname']) ? $activityAttributes['attributes']['lastname'] : 'N/A';
                                                    @endphp
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">
                                                        <h6 class="mb-1 lh-base">                                                
                                                            Updated Application: <span class="text-warning">{{ $applicantPositionName }}</span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}">
                                                        <p class="text-muted mb-1">
                                                            {{ $firstname }} {{ $lastname }}
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $applicantPositionName }}
                                                        </p>
                                                    </div>
                                                @elseif ($activity->subject_type === "App\Models\User")
                                                    @php
                                                        $firstname = isset($activityAttributes['attributes']['firstname']) ? $activityAttributes['attributes']['firstname'] : 'N/A';
                                                        $lastname = isset($activityAttributes['attributes']['lastname']) ? $activityAttributes['attributes']['lastname'] : 'N/A';
                                                        $role = $activity->subject->role ? $activity->subject->role->name : 'N/A';
                                                        $brand = $activity->subject->brand ? $activity->subject->brand->name : 'N/A';
                                                    @endphp
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">
                                                        <h6 class="mb-1 lh-base">                                                
                                                            Updated User: <span class="text-warning">{{ $firstname }} {{ $lastname }}</span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}">
                                                        <p class="text-muted mb-1">
                                                            {{ $role }}
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $brand }}
                                                        </p>
                                                    </div>
                                                @elseif ($activity->subject_type === "App\Models\Application")
                                                    @php
                                                        $activityAttributes = json_decode($activity->properties, true);
                                                        $newApprovalStatus = $activityAttributes['attributes']['approved'] ?? null;
                                                        $oldApprovalStatus = $activityAttributes['old']['approved'] ?? null;
                                            
                                                        $applicationUser = $activity->subject->user ?? null;
                                                        $vacancyUser = $activity->subject->vacancy->user ?? null;
                                                        $applicationUserName = $applicationUser ? $applicationUser->firstname . ' ' . $applicationUser->lastname : 'N/A';
                                                        $vacancyUserName = $vacancyUser ? $vacancyUser->firstname . ' ' . $vacancyUser->lastname : 'N/A';
                                                    @endphp
                                            
                                                    @if($newApprovalStatus !== $oldApprovalStatus) {{-- Check if approval status changed --}}
                                                        <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">
                                                            @if($activity->causer_id == Auth::id()) {{-- Check if the authenticated user is the one who changed the status --}}
                                                                @if($newApprovalStatus === "Yes")
                                                                    <h6 class="mb-1 lh-base">                                                
                                                                        Approved application request from: <span class="text-warning">{{ $applicationUserName }}</span>
                                                                    </h6>
                                                                @else
                                                                    <h6 class="mb-1 lh-base">                                                
                                                                        Declined application request from: <span class="text-warning">{{ $applicationUserName }}</span>
                                                                    </h6>
                                                                @endif
                                                            @else
                                                                @if($newApprovalStatus === "Yes")
                                                                    <h6 class="mb-1 lh-base">                                                
                                                                        <span class="text-warning">{{ $applicationUserName }}</span> approved your application request
                                                                    </h6>
                                                                @else
                                                                    <h6 class="mb-1 lh-base">                                                
                                                                        <span class="text-warning">{{ $applicationUserName }}</span> declined your application request
                                                                    </h6>
                                                                @endif
                                                            @endif
                                                        </a>

                                                        <div class="collapse show" id="activity{{ $activity->id }}">
                                                            @if($activity->subject)
                                                                <p class="text-muted mb-1">
                                                                    {{ $activity->subject->vacancy->position->name }}
                                                                </p>
                                                                <p class="text-muted mb-1">
                                                                    {{ $activity->subject->vacancy->store->brand->name }} ({{ $activity->subject->vacancy->store->town->name }})
                                                                </p>
                                                                <p class="text-muted mb-1">
                                                                    {{ $activity->subject->vacancy->type->name }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif

                                            <!-------------------------------------------------------------------------------------
                                                Deleted
                                            -------------------------------------------------------------------------------------->

                                            @elseif($activity->event === "deleted")
                                                @if ($activity->subject_type === "App\Models\Vacancy")
                                                    @php
                                                        // Retrieve the old attributes
                                                        $oldAttributes = $activityAttributes['old'] ?? [];
                                            
                                                        // Find the related models using the IDs from the old attributes
                                                        $position = isset($oldAttributes['position_id']) ? \App\Models\Position::find($oldAttributes['position_id']) : null;
                                                        $store = isset($oldAttributes['store_id']) ? \App\Models\Store::with('brand', 'town')->find($oldAttributes['store_id']) : null;
                                                        $type = isset($oldAttributes['type_id']) ? \App\Models\Type::find($oldAttributes['type_id']) : null;
                                            
                                                        // Get the names or default to 'N/A'
                                                        $positionName = $position ? $position->name : 'N/A';
                                                        $brandName = $store && $store->brand ? $store->brand->name : 'N/A';
                                                        $townName = $store && $store->town ? $store->town->name : 'N/A';
                                                        $typeName = $type ? $type->name : 'N/A';
                                                    @endphp
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">                                                    
                                                        <h6 class="mb-1 lh-base">                                                
                                                            {{ $subjectName }} {{ strtolower(class_basename($activity->subject_type)) }}: <span class="text-danger">{{ $positionName }}</span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}" style="">
                                                        <p class="text-muted mb-1">
                                                            {{ $brandName }} ({{ $townName }})
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $typeName }}
                                                        </p>
                                                    </div>                                       
                                                @elseif ($activity->subject_type === "App\Models\Message")
                                                    @php
                                                        $message = isset($activityAttributes['old']['message']) ? $activityAttributes['old']['message'] : 'N/A';
                                                        $userTo = $activity->userForDeletedMessage;
                                                        $userToName = $userTo ? $userTo->firstname . ' ' . $userTo->lastname : 'N/A';
                                                    @endphp
                                                
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">
                                                        @if($activity->causer_id == Auth::id()) {{-- Message sent by the authenticated user --}}
                                                            <h6 class="mb-1 lh-base">                                                
                                                                {{ $subjectName }} {{ strtolower(class_basename($activity->subject_type)) }} to: <span class="text-danger">{{ $userToName }}</span>
                                                            </h6>
                                                        @endif
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}">
                                                        <p class="text-muted mb-1">
                                                            {{ $message }}
                                                        </p>
                                                    </div> 
                                                @endif

                                            <!-------------------------------------------------------------------------------------
                                                Viewed
                                            -------------------------------------------------------------------------------------->

                                            @else
                                                @if($activity->accessedVacancy)
                                                    @php
                                                        $vacancy = $activity->accessedVacancy;
                                                        $positionName = optional($vacancy->position)->name ?? 'N/A';
                                                        $brandName = optional($vacancy->store->brand)->name ?? 'N/A';
                                                        $townName = optional($vacancy->store->town)->name ?? 'N/A';
                                                        $typeName = optional($vacancy->type)->name ?? 'N/A';
                                                    @endphp
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">
                                                        <h6 class="mb-1 lh-base"> 
                                                            Viewed vacancy: <span class="text-info">{{ $positionName }}</span>
                                                        </h6> 
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}">
                                                        <p class="text-muted mb-1">
                                                            {{ $positionName }}
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $brandName }} ({{ $townName }})
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $typeName }}
                                                        </p>
                                                    </div> 
                                                @elseif($activity->accessedApplicant)
                                                    @php
                                                        $applicant = $activity->accessedApplicant;
                                                        $applicantPosition = $applicant->position ?? null;
                                                        $applicantPositionName = $applicantPosition ? $applicantPosition->name : 'N/A';
                                                        if ($applicantPositionName === "Other") {
                                                            $applicantPositionName = $applicant->position_specify ?? 'N/A';
                                                        }
                                                        $firstname = $applicant->firstname ?? 'N/A';
                                                        $lastname = $applicant->lastname ?? 'N/A';
                                                    @endphp
                                                    <a data-bs-toggle="collapse" href="#activity{{ $activity->id }}" role="button" aria-expanded="true" aria-controls="activity{{ $activity->id }}">
                                                        <h6 class="mb-1 lh-base"> 
                                                            Viewed applicant: <span class="text-info">{{ $firstname }} {{ $lastname }}</span>
                                                        </h6> 
                                                    </a>
                                                    <div class="collapse show" id="activity{{ $activity->id }}">
                                                        <p class="text-muted mb-1">
                                                            {{ $firstname }} {{ $lastname }}
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            {{ $applicantPositionName }}
                                                        </p>
                                                    </div> 
                                                @else
                                                    <h6 class="mb-1 lh-base"> 
                                                        Viewed entity
                                                    </h6>
                                                @endif
                                            @endif                                          
                                            <small class="mb-0 text-muted">
                                                {{ $activity->created_at->format('H:i A - d M Y') }}
                                            </small>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div> <!-- end card-->
        </div> <!-- end .rightbar-->

    </div> <!-- end col -->
</div>


@endsection
@section('script')
<script>

    var totalVacancies = @json($totalVacancies);
    var totalVacanciesFilled = @json($totalVacanciesFilled);
    var totalInterviewsScheduled = @json($totalInterviewsScheduled);
    var totalInterviewsCompleted = @json($totalInterviewsCompleted);
    var totalApplicantsAppointed = @json($totalApplicantsAppointed);
    var totalApplicantsRegretted = @json($totalApplicantsRegretted);
    var averageTimeToShortlist = @json($averageTimeToShortlist);
    var averageTimeToHire = @json($averageTimeToHire);
    var adoptionRate = @json($adoptionRate);
    var talentPoolApplicants = @json($talentPoolApplicants);
    var talentPoolApplicantsByMonth = @json($talentPoolApplicantsByMonth);
    var applicantsAppointed = @json($applicantsAppointed);
    var applicantsAppointedByMonth = @json($applicantsAppointedByMonth);

</script>
<!-- sweet alert -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- apexcharts -->
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{URL::asset('build/libs/jsvectormap/js/jsvectormap2.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/jsvectormap/maps/south-africa.js') }}"></script>
<script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
<!-- dashboard init -->
<script src="{{URL::asset('build/js/pages/admin.init.js')}}?v={{ filemtime(public_path('build/js/pages/admin.init.js')) }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection