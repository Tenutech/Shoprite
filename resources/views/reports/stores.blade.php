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

@component('components.breadcrumb')
        @slot('li_1')
            Reports
        @endslot
        @slot('title')
            Stores
        @endslot
    @endcomponent


<div class="row">
    <div class="col">

        <div class="h-100">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <button type="button" id="exportReport" class="btn btn-success btn-label">
                                <i class="ri-file-excel-2-fill label-icon align-middle fs-16 me-2"></i>
                                Export Report
                            </button>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <div class="row g-3 mb-0 align-items-center">
                                <div class="col-sm-auto">
                                    <div class="hstack gap-1">                                            
                                        <!-- Filter Button -->
                                        <button class="btn btn-secondary btn-label" id="filterBtn" data-bs-toggle="offcanvas" href="#filters-canvas" aria-controls="member-overview">
                                            <i class="ri-equalizer-line label-icon align-middle fs-16 me-2"></i>
                                            Filters
                                        </button>
                                        <!-- Refresh Button with Tooltip and a gap (margin-left) -->
                                        <button class="btn btn-info ms-2" id="refreshBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh data" onclick="location.reload();">
                                            <i class="ri-refresh-line align-bottom"></i>
                                        </button>
                                    </div>
                                </div> <!--end col-->
                            </div> <!--end row -->                 
                        </div>
                    </div><!-- end card header -->
                </div> <!--end col -->
            </div> <!--end row -->

            <!-------------------------------------------------------------------------------------
                Interviews
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Total Applicants Appointed -->
                <div class="col-xl-4 col-md-4 d-flex">
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
                                        Succesfull Placements
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalApplicantsAppointedValue"  class="counter-value" data-target="{{ $totalApplicantsAppointed }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_applicants_appointed" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            
                <!-- Total Interviews Conducted -->
                <div class="col-xl-4 col-md-4 d-flex">
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
                                        Interviews Conducted
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalInterviewsCompletedValue" class="counter-value" data-target="{{ $totalInterviewsCompleted }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_interviews_completed" data-colors='["--vz-success"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->

                <!-- Hire to Interview Ratio -->
                <div class="col-xl-4 col-md-4 d-flex">
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
                                        Hire to Interview Ratio
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="hireToInterviewRatioValue">
                                            {{ $hireToInterviewRatioDisplay }}
                                        </span>
                                    </h4>
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
                <div class="col-xl-6 col-md-6" id="averageTimeToShortlistColumn">
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
                <div class="col-xl-6 col-md-6" id="averageTimeToHireColumn">
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
            </div> <!-- end row -->

            <!-------------------------------------------------------------------------------------
                Proximity
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Average Proximity Appointed -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate">
                        <div class="card-header bg-secondary">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1 text-white">
                                    Average Proximity (Succesfull Placements)
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                    <span id="averageDistanceApplicantsAppointedValue" class="counter-value"  data-target="{{ $averageDistanceApplicantsAppointed }}">
                                        0
                                    </span>km 
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Average distance for succesfull placements
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->

                <!-- Average Assessment Score Appointed -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate">
                        <div class="card-header bg-success">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1 text-white">
                                    Average Assessment Score (Succesfull Placements)
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                    <span id="averageAssessmentScoreApplicantsAppointedValue" class="counter-value"  data-target="{{ $averageAssessmentScoreApplicantsAppointed }}">
                                        0
                                    </span>% 
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Average assessments score of succesfull placements
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->

        </div> <!-- end .h-100 -->

        <!-------------------------------------------------------------------------------------
            Off Canvas
        -------------------------------------------------------------------------------------->

        <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="filters-canvas">
            <form id="formFilters" enctype="multipart/form-data">
                @csrf
                <div class="offcanvas-body profile-offcanvas d-flex flex-column p-0" style="height: 100vh;">
                    <!-- Main content that can scroll if necessary -->
                    <div class="flex-grow-1">
                        <div class="team-cover">
                            <img src="{{ URL::asset('build/icons/auth-two-bg.jpg') }}" alt="" class="img-fluid" />
                        </div>
                        <div class="p-5"></div>
                        <div class="p-3 mt-4 text-center">
                            <div class="mt-3">
                                <h5 class="fs-15 profile-name">
                                    Store Filters
                                </h5>
                            </div>
                        </div>
                        <div class="row g-0 p-3">
                            <!-- Date -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="date" class="form-label">
                                        Date Range
                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="date" name="date" class="form-control border-0 dash-filter-picker shadow" required>
                                        <div class="input-group-text bg-secondary border-secondary text-white">
                                            <i class="ri-calendar-2-line"></i>
                                        </div>
                                        <div class="invalid-feedback">
                                            Please select a date range!
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <!-- Provinces -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="brand" class="form-label">
                                        Brand
                                    </label>
                                    <select class="form-control" id="brand" name="brand_id">
                                        <option value="" selected>Select brand</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a brand!
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <!-- Provinces -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="province" class="form-label">
                                        Province
                                    </label>
                                    <select class="form-control" id="province" name="province_id">
                                        <option value="" selected>Select province</option>
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a province!
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <!-- Town -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="town" class="form-label">
                                        Town
                                    </label>
                                    <select class="form-control" id="town" name="town_id">
                                        <option value="" selected>Select town</option>
                                        @foreach ($towns as $town)
                                            <option value="{{ $town->id }}" province-id="{{ $town->province_id }}">{{ $town->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a town!
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <!-- Division -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="division" class="form-label">
                                        Division
                                    </label>
                                    <select class="form-control" id="division" name="division_id">
                                        <option value="" selected>Select division</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a division!
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <!-- Region -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="region" class="form-label">
                                        Region
                                    </label>
                                    <select class="form-control" id="region" name="region_id">
                                        <option value="" selected>Select region</option>
                                        @foreach ($regions as $region)
                                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a region!
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <!-- Store -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="store" class="form-label">
                                        Store
                                    </label>
                                    <select class="form-control" id="store" name="store_id">
                                        <option value="" selected>Select store</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}" 
                                                brand-id="{{ $store->brand_id }}" 
                                                province-id="{{ optional($store->town)->province_id }}" 
                                                town-id="{{ $store->town_id }}" 
                                                division-id="{{ $store->division_id }}" 
                                                region-id="{{ $store->region_id }}">{{ $store->code }} - {{ optional($store->brand)->name }} ({{ $store->name }})</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a store!
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                    </div>
                    <!-- end main content -->
                    
                    <!-- Sticky footer at the bottom -->
                    <div class="offcanvas-footer p-3 hstack gap-3 text-center position-absolute w-100 bg-white" style="bottom: 0;">
                        <button class="btn btn-light btn-label w-100" id="clearFilters">
                            <i class="ri-delete-bin-fill label-icon align-middle fs-16 me-2"></i> 
                            Clear Filters
                        </button>

                        <button type="submit" class="btn btn-secondary btn-label w-100" id="filter">
                            <i class="ri-equalizer-fill label-icon align-middle fs-16 me-2"></i> 
                            Filter
                        </button>                        
                    </div>
                </div>
            </form>
        </div>
        <!-- end offcanvas--> 

    </div> <!-- end col -->
</div> <!-- end row -->


@endsection
@section('script')
    <script>
        var talentPoolApplicants = @json($talentPoolApplicants);
        var totalApplicantsAppointed = @json($totalApplicantsAppointed);
        var totalInterviewsScheduled = @json($totalInterviewsScheduled);
        var totalInterviewsCompleted = @json($totalInterviewsCompleted);
    </script>
    <!-- sweet alert -->
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
    <!-- dashboard init -->
    <script src="{{URL::asset('build/js/pages/stores-report.init.js')}}?v={{ filemtime(public_path('build/js/pages/stores-report.init.js')) }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection