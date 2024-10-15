@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection
@section('css')
<link href="{{ URL::asset('build/libs/jsvectormap/css/jsvectormap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css')}}" rel="stylesheet" type="text/css" />
<style>
.applicantsView:hover {
    cursor: pointer; 
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
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">
                                Hello, {{ Auth::user()->firstname }}!
                            </h4>
                            <p class="text-muted mb-0">
                                Here's what's happening with your store
                                @if (isset($store) && isset($store->brand))
                                    ({{ $store->brand->name }} {{ $store->name }})
                                @endif
                                today.
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
                                    <span class="badge bg-light text-{{ $percentMovementApplicationsPerMonth > 0 ? 'success' : 'danger' }} mb-0"> 
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
                                    <span class="badge bg-light text-{{ $percentMovementInterviewedPerMonth > 0 ? 'success' : 'danger' }} mb-0"> 
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
                                    <span class="badge bg-light text-{{ $percentMovementAppointedPerMonth > 0 ? 'success' : 'danger' }} mb-0"> 
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
                                    <span class="badge bg-light text-{{ $percentMovementRejectedPerMonth > 0 ? 'success' : 'danger' }} mb-0"> 
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

            <div class="row">
                <div class="col-md-4" id="storeAverageTimeToShortlistColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Time to Shortlist (days)
                                    </p>
                                    @php
                                        $totalDays = $storeAverageTimeToShortlist;
                                        $totalMinutes = $totalDays * 24 * 60; // Convert days to minutes
                                        $interval = \Carbon\CarbonInterval::minutes($totalMinutes);
                                        $formattedInterval = $interval->cascade()->format('%dD %hH %iM');
                                    @endphp
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="storeAverageTimeToShortlistValue">{{ $formattedInterval }}</span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0" id="storeAverageTimeToShortlist">
                                            Store Average
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
                    </div> <!-- end card-->
                </div> <!-- end col-->

                <div class="col-md-4" id="storeAverageTimeToHireColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Time to Hire (days)
                                    </p>
                                    @php
                                        $totalDays = $storeAverageTimeToHire;
                                        $totalMinutes = $totalDays * 24 * 60; // Convert days to minutes
                                        $interval = \Carbon\CarbonInterval::minutes($totalMinutes);
                                        $formattedInterval = $interval->cascade()->format('%dD %hH %iM');
                                    @endphp
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="storeAverageTimeToHireValue">{{ $formattedInterval }}</span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0" id="storeAverageTimeToHire">
                                            Store Average
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
                    </div> <!-- end card-->
                </div> <!-- end col-->
            </div> <!-- end row-->

            <div class="row">
                <div class="col-md-4" id="adoptionRateColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Adoption Rate
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="adoptionRateValue">{{ $adoptionRate }}%</span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0" id="adoptionRate">
                                           Percentage
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
                    </div> <!-- end card-->
                </div> <!-- end col-->

                <div class="col-md-4" id="avgLiteracyScoreColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Average Store Literacy Score
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="avgLiteracyScoreValue">{{ $averageScores['avg_literacy_score'] ?? 'N/A' }}%</span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0" id="avgLiteracyScore">
                                           Percentage
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
                    </div> <!-- end card-->
                </div> <!-- end col-->
                
                <div class="col-md-4" id="avgNumeracyScoreColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Average Store Numeracy Score
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="avgNumeracyScoreValue">{{ $averageScores['avg_numeracy_score'] ?? 'N/A' }}%</span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0" id="avgNumeracyScore">
                                           Percentage
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
                    </div> <!-- end card-->
                </div> <!-- end col-->
                
            </div> <!-- end row-->
            <!-------------------------------------------------------------------------------------
                Proximity
            -------------------------------------------------------------------------------------->

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
                                    <a class="text-reset dropdown-btn">
                                        <span class="fw-bold text-uppercase fs-12">Absorption Rate: </span>
                                        <span class="text-muted">
                                            {{ $totalApplications > 0 ? round($totalAppointed / $totalApplications * 100) : 0 }}%
                                            <i class="mdi mdi-briefcase ms-1"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div><!-- end card header -->
                        <div class="card-body px-0">
                            <div id="jobs_chart" data-colors='["--vz-success","--vz-primary", "--vz-info", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->
            </div>

            @if ($shortlist)
                @include('manager.partials.shortlist-modal', ['shortlist' => $shortlist])
            @endif

        </div> <!-- end .h-100-->

    </div> <!-- end col -->
</div>


@endsection
@section('script')
<script>
    var shortlist = @json($shortlist);
    var applicationsPerMonth = @json($applicationsPerMonth);
    var interviewedPerMonth = @json($interviewedPerMonth);
    var appointedPerMonth = @json($appointedPerMonth);
    var rejectedPerMonth = @json($rejectedPerMonth);
</script>
<!-- sweet alert -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- apexcharts -->
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{URL::asset('build/libs/jsvectormap/js/jsvectormap2.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/jsvectormap/maps/south-africa.js') }}"></script>
<script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
<!-- dashboard init -->
<script src="{{URL::asset('build/js/pages/manager.init.js')}}?v={{ filemtime(public_path('build/js/pages/manager.init.js')) }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
