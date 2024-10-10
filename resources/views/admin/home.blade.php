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
                        <div class="flex-grow-1" style="width: 60%;">
                            <h4 class="fs-16 mb-1">
                                Hello, {{ Auth::user()->firstname }}!
                            </h4>
                            <p class="text-muted mb-0">
                                Here's what's happening with Orient today.
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
                                    <span class="badge bg-light text-{{ $percentMovementApplicationsPerMonth > 0 ? 'success' : 'danger' }} mb-0" id="percentMovementApplicationsPerMonthBadge"> 
                                        <i class="ri-arrow-{{ $percentMovementApplicationsPerMonth > 0 ? 'up' : 'down' }}-line align-middle"></i> 
                                        {{ abs($percentMovementApplicationsPerMonth) }} % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-{{ $percentMovementApplicationsPerMonth > 0 ? 'success' : 'danger' }}" , "--vz-transparent"]' dir="ltr" id="applications_sparkline_chart"></div>
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
                                    <span class="badge bg-light text-{{ $percentMovementInterviewedPerMonth > 0 ? 'success' : 'danger' }} mb-0" id="percentMovementInterviewedPerMonthBadge"> 
                                        <i class="ri-arrow-{{ $percentMovementInterviewedPerMonth > 0 ? 'up' : 'down' }}-line align-middle"></i> 
                                        {{ abs($percentMovementInterviewedPerMonth) }} % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-{{ $percentMovementInterviewedPerMonth > 0 ? 'success' : 'danger' }}" , "--vz-transparent"]' dir="ltr" id="interviewed_sparkline_chart"></div>
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
                                    <span class="badge bg-light text-{{ $percentMovementAppointedPerMonth > 0 ? 'success' : 'danger' }} mb-0" id="percentMovementHiredPerMonthBadge"> 
                                        <i class="ri-arrow-{{ $percentMovementAppointedPerMonth > 0 ? 'up' : 'down' }}-line align-middle"></i> 
                                        {{ abs($percentMovementAppointedPerMonth) }} % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-{{ $percentMovementAppointedPerMonth > 0 ? 'success' : 'danger' }}" , "--vz-transparent"]' dir="ltr" id="hired_sparkline_chart"></div>
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
                                    <span class="badge bg-light text-{{ $percentMovementRejectedPerMonth > 0 ? 'success' : 'danger' }} mb-0" id="percentMovementRejectedPerMonthBadge"> 
                                        <i class="ri-arrow-{{ $percentMovementRejectedPerMonth > 0 ? 'up' : 'down' }}-line align-middle"></i> 
                                        {{ abs($percentMovementRejectedPerMonth) }} % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-{{ $percentMovementRejectedPerMonth > 0 ? 'success' : 'danger' }}", "--vz-transparent"]' dir="ltr" id="rejected_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
            </div> <!-- end row-->

            <!-------------------------------------------------------------------------------------
                Talent Pool breakdown
            -------------------------------------------------------------------------------------->

            @include('admin.dashboard.partials.rates') 

            <!-------------------------------------------------------------------------------------
                Talent Pool breakdown
            -------------------------------------------------------------------------------------->

            @include('admin.dashboard.partials.demographics_talent_pool_breakdown') 

            <!-------------------------------------------------------------------------------------
                Appointed Candidates
            -------------------------------------------------------------------------------------->

            @include('admin.dashboard.partials.demographics_appointed_candidates_breakdown')

            <!-------------------------------------------------------------------------------------
                Interviewed Candidates
            -------------------------------------------------------------------------------------->

            @include('admin.dashboard.partials.demographics_interviewed_candidates_breakdown')

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                Top 5 Drop-Offs by Question
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="px-2 py-2 mt-4" id="provinceProgress">
                                @foreach($dropOffRates['dropoff_by_stage'] as $key => $value)

                                    <p class="mb-1">
                                        {{ $key }}
                                        <span class="float-end">{{ $value['percentage'] }}%</span>
                                    </p>
                                    <div class="progress mt-1 mb-3" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" 
                                            style="width: {{ $value['percentage'] }}%" aria-valuenow="{{ $value['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                @endforeach
                                </div>
                        </div><!-- end card body -->
                    </div>
                </div>
                <!--end col-->
            </div>

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
                        </div><!-- end card header -->
                        <div class="card-header p-0 border-0 bg-soft-light">
                            <div class="row g-0 text-center">
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1"><span class="counter-value" data-target="{{ $totalIncomingMessages }}" id="totalIncomingCounter">0</span></h5>
                                        <p class="text-muted mb-0">Incoming</p>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1"><span class="counter-value" data-target="{{ $totalOutgoingMessages }}" id="totalOutgoingCounter">0</span></h5>
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
                Jobs Summary
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-xxl-12 col-md-12">
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

            <div class="row">
                <div class="col-xxl-12 col-md-12">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                            Application channels
                            </h4>                            
                        </div><!-- end card header -->
                        <div class="card-header p-0 border-0 bg-soft-light">
                            <div class="row g-0 text-center">
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1"><span class="counter-value" data-target="{{ $channelStats['whatsapp']['count'] }}" id="channleWhatsappCounter">0</span></h5>
                                        <p class="text-muted mb-0">Whatsapp</p>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1"><span class="counter-value" data-target="{{ $channelStats['website']['count'] }}" id="channleWebsiteCounter">0</span></h5>
                                        <p class="text-muted mb-0">Website</p>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                        </div><!-- end card header -->
                        <div class="card-body">
                            <div id="application_channels" data-colors='["--vz-success", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div>
            </div>

            <!-------------------------------------------------------------------------------------
                Locations
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-xl-8">
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
            
                            @php
                                // Convert $applicantsPerProvince to a collection for easier manipulation
                                $applicantsPerProvinceCollection = collect($applicantsPerProvince);
                                $totalApplicants = $applicantsPerProvinceCollection->sum('y');
                                $sortedProvinces = $applicantsPerProvinceCollection->sortByDesc('y')->take(3);
                            @endphp

                            <div class="px-2 py-2 mt-4" id="provinceProgress">
                                @foreach($sortedProvinces as $province)
                                    @php
                                    // Calculate the percentage of total applicants for each province
                                    $percentage = number_format(($province['y'] / $totalApplicants) * 100, 2);
                                    @endphp

                                    <p class="mb-1">
                                        {{ $province['x'] }}
                                        <span class="float-end">{{ $percentage }}%</span>
                                    </p>
                                    <div class="progress mt-1 mb-3" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" 
                                            style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                @endforeach
                            </div>
                            
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>

                <!-------------------------------------------------------------------------------------
                    Applicant Positions
                -------------------------------------------------------------------------------------->

                <div class="col-xl-4">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Applicant Positions
                            </h4>
                        </div><!-- end card header -->
                        <div class="card-body">
                            <div id="applicant_positions" data-colors='["#f5b041", "#1abc9c", "#3498db", "#9b59b6", "#34495e", "#f1c40f", "#e67e22", "#e74c3c", "#ecf0f1", "#2ecc71", "#95a5a6"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->
            </div>

            <div class="row">
                <div class="col-xl-6">
                    <!-- card -->
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Assessment Literacy Scores By Brand
                            </h4>
                        </div><!-- end card header -->
            
                        <!-- card body -->
                        <div class="card-body">
        
                            <div class="px-2 py-2 mt-4" id="provinceProgress">
                                @foreach($averageScoresByBrand as $key => $value)

                                    <p class="mb-1">
                                        {{ $key }}
                                        <span class="float-end">{{ $value['literacy_percentage'] }}%</span>
                                    </p>
                                    <div class="progress mt-1 mb-3" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" 
                                            style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                @endforeach
                            </div>
                            
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>

                <div class="col-xl-6">
                    <!-- card -->
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Assessment Numeracy Scores By Brand
                            </h4>
                        </div><!-- end card header -->
            
                        <!-- card body -->
                        <div class="card-body">
        
                            <div class="px-2 py-2 mt-4" id="provinceProgress">
                                @foreach($averageScoresByBrand as $key => $value)

                                    <p class="mb-1">
                                        {{ $key }}
                                        <span class="float-end">{{ $value['numeracy_percentage'] }}%</span>
                                    </p>
                                    <div class="progress mt-1 mb-3" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" 
                                            style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                @endforeach
                            </div>
                            
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6">
                    <!-- card -->
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Assessment Literacy Scores
                            </h4>
                        </div><!-- end card header -->
            
                        <!-- card body -->
                        <div class="card-body">
        
                            <div class="px-2 py-2 mt-4" id="provinceProgress">
                                @foreach($averageScoresByProvince as $key => $value)

                                    <p class="mb-1">
                                        {{ $key }}
                                        <span class="float-end">{{ $value['literacy_percentage'] }}%</span>
                                    </p>
                                    <div class="progress mt-1 mb-3" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" 
                                            style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                @endforeach
                            </div>
                            
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>

                <div class="col-xl-6">
                    <!-- card -->
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Assessment Numeracy Scores
                            </h4>
                        </div><!-- end card header -->
            
                        <!-- card body -->
                        <div class="card-body">
        
                            <div class="px-2 py-2 mt-4" id="provinceProgress">
                                @foreach($averageScoresByProvince as $key => $value)

                                    <p class="mb-1">
                                        {{ $key }}
                                        <span class="float-end">{{ $value['numeracy_percentage'] }}%</span>
                                    </p>
                                    <div class="progress mt-1 mb-3" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" 
                                            style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                @endforeach
                            </div>
                            
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>
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
    var applicantsPerProvince = @json($applicantsPerProvince);

    var applicantsByRace = @json($applicantsByRace);
    applicantsByRace.forEach(race => {
        race.data = race.data.reverse();
    });

    var totalApplicantsPerMonth = @json($totalApplicantsPerMonth);

    var incomingMessages = @json($incomingMessages);
    var outgoingMessages = @json($outgoingMessages);

    var whatsappChannelCount = @json($channelStats['whatsapp']['count']);
    var websiteChannelCount = @json($channelStats['website']['count']);

    var applicantsByPosition = @json($applicantsByPosition);

    var raceBreakdownPercentages = @json($raceBreakdownPercentages);
    var ageBreakdownPercentages = @json($ageBreakdownPercentages);
    var genderBreakdownPercentages = @json($genderBreakdownPercentages);

    var appointedRaceBreakdownPercentages = @json($appointedRaceBreakdownPercentages);
    var appointedAgeBreakdownPercentages = @json($appointedAgeBreakdownPercentages);
    var appointedGenderBreakdownPercentages = @json($appointedGenderBreakdownPercentages);

    var interviewedRaceBreakdownPercentages = @json($interviewedRaceBreakdownPercentages);
    var interviewedAgeBreakdownPercentages = @json($interviewedAgeBreakdownPercentages);
    var interviewedGenderBreakdownPercentages = @json($interviewedGenderBreakdownPercentages);

    var applicantData = applicantsPerProvince.reduce((accumulator, currentValue) => {
        accumulator[currentValue.x] = currentValue.y;
        return accumulator;
    }, {});

    var applicationsPerMonth = @json($applicationsPerMonth);
    var interviewedPerMonth = @json($interviewedPerMonth);
    var appointedPerMonth = @json($appointedPerMonth);
    var rejectedPerMonth = @json($rejectedPerMonth);
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