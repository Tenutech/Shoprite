@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection
@section('css')
<link href="{{ URL::asset('build/libs/jsvectormap/css/jsvectormap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')

<div class="row">
    <div class="col">

        <div class="h-100">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">
                                Hello, {{ Auth::user()->firstname }}!
                            </h4>
                            <p class="text-muted mb-0">
                                Here's what's happening with Orient today.
                            </p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-center">
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <input type="text" class="form-control border-0 dash-filter-picker shadow" data-provider="flatpickr" data-range-date="true" data-date-format="d M, Y" data-deafult-date="01 Jan 2022 to 31 Jan 2022">
                                            <div class="input-group-text bg-primary border-primary text-white">
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
            <!--end row-->

            <!-------------------------------------------------------------------------------------
                Information
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Applications
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-success mb-0"> 
                                        <i class="ri-arrow-up-line align-middle"></i> 
                                        16.24 % 
                                    </span> vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-success" , "--vz-transparent"]' dir="ltr" id="applications_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Interviewed
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-success mb-0"> 
                                        <i class="ri-arrow-up-line align-middle"></i> 
                                        34.24 % 
                                    </span> vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-success" , "--vz-transparent"]' dir="ltr" id="interviewed_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Hired
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-success mb-0"> 
                                        <i class="ri-arrow-up-line align-middle"></i> 
                                        6.67 % 
                                    </span> 
                                    vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-success" , "--vz-transparent"]' dir="ltr" id="hired_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Rejected
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-danger mb-0"> 
                                        <i class="ri-arrow-down-line align-middle"></i> 
                                        3.24 % 
                                    </span> vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-danger", "--vz-transparent"]' dir="ltr" id="rejected_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
            </div> <!-- end row-->

            <!-------------------------------------------------------------------------------------
                Applicants Graph
            -------------------------------------------------------------------------------------->
            
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    Applicants Location
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="applicants_treemap" data-colors='["--vz-danger", "--vz-success", "--vz-warning", "--vz-info","--vz-secondary", "--vz-primary"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xl-4">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Applicant Ethnicity</h4>                            
                        </div><!-- end card header -->
                        <div class="card-body">
                            <div id="applicant_race" data-colors='["--vz-warning", "--vz-info", "--vz-primary", "--vz-secondary", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div>
            </div>
            <!--end row-->

            <!-------------------------------------------------------------------------------------
                Totals
            -------------------------------------------------------------------------------------->
            
            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    Total Applicants
                                </h5>
                            </div>
                        </div>                        
                        <div class="card-body">
                            <div id="total_applicants" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xl-6">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Total Messages
                            </h4>
                            <div>
                                <button type="button" class="btn btn-soft-secondary btn-sm">
                                    ALL
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm">
                                    1M
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm">
                                    6M
                                </button>
                                <button type="button" class="btn btn-soft-primary btn-sm">
                                    1Y
                                </button>
                            </div>                            
                        </div><!-- end card header -->
                        <div class="card-header p-0 border-0 bg-soft-light">
                            <div class="row g-0 text-center">
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1"><span class="counter-value" data-target="1675">0</span></h5>
                                        <p class="text-muted mb-0">Incoming</p>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1"><span class="counter-value" data-target="1821">0</span></h5>
                                        <p class="text-muted mb-0">Outgoing</p>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                        </div><!-- end card header -->
                        <div class="card-body">
                            <div id="total_messages" data-colors='["--vz-success", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div>
            </div>
            <!--end row-->

            <!-------------------------------------------------------------------------------------
                Positions
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-xxl-4 col-md-6">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Applicant Positions
                            </h4>
                        </div><!-- end card header -->
                        <div class="card-body pb-0">
                            <div id="applicant_positions" data-colors='["#1abc9c", "#3498db", "#9b59b6", "#34495e", "#f1c40f", "#e67e22", "#e74c3c", "#ecf0f1", "#2ecc71", "#95a5a6"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->
            
                <div class="col-xxl-8 col-md-6">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Jobs Summary</h4>
                            <div class="flex-shrink-0">
                                <div class="dropdown card-header-dropdown">
                                    <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="fw-bold text-uppercase fs-12">Sort by: </span><span class="text-muted">Current Year<i class="mdi mdi-chevron-down ms-1"></i></span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">Today</a>
                                        <a class="dropdown-item" href="#">Last Week</a>
                                        <a class="dropdown-item" href="#">Last Month</a>
                                        <a class="dropdown-item" href="#">Current Year</a>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card header -->
                        <div class="card-body px-0">
                            <div id="jobs_chart" data-colors='["--vz-success","--vz-primary", "--vz-info", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->
            </div>

            <!-------------------------------------------------------------------------------------
                Locations
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-xxl-8">
                    <!-- card -->
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Applicant Locations
                            </h4>
                        </div><!-- end card header -->
            
                        <!-- card body -->
                        <div class="card-body">
            
                            <div id="applicants-by-locations" data-colors='["#E5E8E8", "--vz-primary", "--vz-success"]' style="height: 269px" dir="ltr"></div>
            
                            <div class="px-2 py-2 mt-4">
                                <p class="mb-1">
                                    Eastern Cape 
                                    <span class="float-end">34%</span>
                                </p>
                                <div class="progress mt-1 mb-3" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" style="width: 34%" aria-valuenow="34" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <p class="mb-1">
                                    Guateng 
                                    <span class="float-end">25%</span>
                                </p>
                                <div class="progress mt-1 mb-3" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <p class="mb-1">
                                    Western Cape 
                                    <span class="float-end">15%</span>
                                </p>
                                <div class="progress mt-1 mb-3" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>
                <div class="col-xl-4">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Users by Device
                            </h4>
                        </div><!-- end card header -->
                        <div class="card-body">
                            <div id="applicant_device" data-colors='["--vz-primary", "--vz-warning", "--vz-info"]'
                                class="apex-charts" dir="ltr"></div>
        
                            <div class="table-responsive mt-3">
                                <table class="table table-borderless table-sm table-centered align-middle table-nowrap mb-0">
                                    <tbody class="border-0">
                                        <tr>
                                            <td>
                                                <h4 class="text-truncate fs-14 mb-0">
                                                    <i class="ri-stop-fill align-middle fs-18 text-primary me-2"></i>Desktop
                                                    Users
                                                </h4>
                                            </td>
                                            <td>
                                                <p class="text-muted mb-0">
                                                    <i data-feather="users" class="me-2 icon-sm"></i>
                                                    78.56k
                                                </p>
                                            </td>
                                            <td class="text-end">
                                                <p class="text-success fw-medium fs-13 mb-0">
                                                    <i class="ri-arrow-up-s-fill fs-5 align-middle"></i>
                                                    2.08%
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h4 class="text-truncate fs-14 mb-0">
                                                    <i class="ri-stop-fill align-middle fs-18 text-warning me-2"></i>Mobile
                                                    Users
                                                </h4>
                                            </td>
                                            <td>
                                                <p class="text-muted mb-0">
                                                    <i data-feather="users" class="me-2 icon-sm"></i>
                                                    105.02k
                                                </p>
                                            </td>
                                            <td class="text-end">
                                                <p class="text-danger fw-medium fs-13 mb-0">
                                                    <i class="ri-arrow-down-s-fill fs-5 align-middle"></i>
                                                    10.52%
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h4 class="text-truncate fs-14 mb-0">
                                                    <i class="ri-stop-fill align-middle fs-18 text-info me-2"></i>
                                                    Tablet Users
                                                </h4>
                                            </td>
                                            <td>
                                                <p class="text-muted mb-0">
                                                    <i data-feather="users" class="me-2 icon-sm"></i>
                                                    42.89k
                                                </p>
                                            </td>
                                            <td class="text-end">
                                                <p class="text-danger fw-medium fs-13 mb-0">
                                                    <i class="ri-arrow-down-s-fill fs-5 align-middle"></i>
                                                    7.36%
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->
            </div>

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

                    <div class="p-3 mt-2">
                        <h6 class="text-muted mb-3 text-uppercase fw-bold fs-13">Top 10 Categories
                        </h6>

                        <ol class="ps-3 text-muted">
                            @foreach($positions as $position)
                                <li class="py-1">
                                    <a class="text-muted">
                                        <div class="row">
                                            <div class="col-10">
                                                {{ $position->name }}
                                            </div>
                                            <div class="col-2 text-end">
                                                ({{ $position->users_count }})
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                    
                    <div class="card sidebar-alert bg-light border-0 text-center mx-4 mb-0 mt-3">
                        <div class="card-body">
                            <img src="{{ URL::asset('build/images/user-illustarator-1.png') }}" width="100px" alt="">
                            <div class="mt-4">
                                <h5>
                                    Tutorial Video
                                </h5>
                                <p class="text-muted lh-base">
                                    Need help? Watch the tutorial video now!
                                </p>
                                <a href="https://www.youtube.com/watch?v=glhWGAV5zJI&t=58s" class="btn btn-primary btn-label rounded-pill" target="_blank">
                                    <i class="ri-video-chat-fill label-icon align-middle rounded-pill fs-16 me-2"></i>
                                    Tutorial
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card sidebar-alert bg-light border-0 text-center mx-4 mb-0 mt-3">
                        <div class="card-body">
                            <img src="{{ URL::asset('build/images/giftbox.png') }}" alt="">
                            <div class="mt-4">
                                <h5>
                                    Invite New User
                                </h5>
                                <p class="text-muted lh-base">
                                    Refer a colleague to Orient Recruitment.</p>
                                <a href="mailto:?subject=Invitation%20to%20Opportunity%20Bridge" class="btn btn-primary btn-label rounded-pill">
                                    <i class="ri-mail-fill label-icon align-middle rounded-pill fs-16 me-2"></i>
                                    Invite Now
                                </a>
                            </div>
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
    var applicantData = {
        "Eastern Cape": 85000,
        "Free State": 14250,
        "Gauteng": 62500,
        "KwaZulu-Natal": 12600,
        "Limpopo": 14350,
        "Mpumalanga": 10200,
        "Northern Cape": 3000,
        "North West": 12600,
        "Western Cape": 37500
    }
</script>
<!-- sweet alert -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- apexcharts -->
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{URL::asset('build/libs/jsvectormap/js/jsvectormap2.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/jsvectormap/maps/south-africa.js') }}"></script>
<script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
<!-- dashboard init -->
<script src="{{URL::asset('build/js/pages/admin.init.js')}}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
