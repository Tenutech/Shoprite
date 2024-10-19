<!-------------------------------------------------------------------------------------
    Stats
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
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <div class="d-flex">
                    <h5 class="card-title mb-0 flex-grow-1">
                    Average proximity
                    </h5>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h2 class="mt-4 ff-primary fw-bold">
                        <span class="counter-value"  data-target="{{ $averageDistanceSuccessfulPlacements }}" id="averageDistanceSuccessfulPlacementsValue">
                                {{ $averageDistanceSuccessfulPlacements }}
                            </span>km 
                        </h2>
                        <p class="mb-0 text-muted">
                            Average Distance for Succesfull Placements
                        </p>
                    </div>
                </div>
            </div><!-- end card body -->
        </div>
    </div> <!-- end col-->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <div class="d-flex">
                    <h5 class="card-title mb-0 flex-grow-1">
                    Average proximity
                    </h5>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h2 class="mt-4 ff-primary fw-bold">
                            <span class="counter-value"  data-target="{{ $averageTalentPoolDistance }}" id="averageTalentPoolDistanceValue">
                                {{ $averageTalentPoolDistance}} 
                            </span>km
                        </h2>
                        <p class="mb-0 text-muted">
                            Total Average Distance
                        </p>
                    </div>
                </div>
            </div><!-- end card body -->
        </div>
    </div> <!-- end col-->
</div> <!-- end row-->

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

