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
                Rates
            -------------------------------------------------------------------------------------->

            @include('rpp.dashboard.partials.rates') 

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

    var applicantsByPosition = @json($applicantsByPosition);

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
<script src="{{URL::asset('build/js/pages/rpp.init.js')}}?v={{ filemtime(public_path('build/js/pages/rpp.init.js')) }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection