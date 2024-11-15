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
                Total Applicants Placed
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Total Applicants Placed -->
                <div class="col-xl-4 col-md-4" id="totalApplicantsPlacedColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                    Total Applicants Placed
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="totalApplicantsPlacedValue">
                                            {{ $totalApplicantsPlaced }}
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0">
                                            Total
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
                <div class="col-xl-4 col-md-4" id="averageTimetoHireColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Average Time to Hire 
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="averageTimetoHireValue">
                                            {{ $averageTimetoHire }}
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0">
                                            Total
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

               <!-- Total Vacancies Filled -->
                <div class="col-xl-4 col-md-4" id="averageAssementScoreColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Average Assessment Score 
                                    </p>
                                    <h2 class="mt-4 ff-success fw-bold">
                                        <span id="averageAssementScoreValue">
                                            {{ $averageAssementScore }}%
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-success mb-0" id="averageAssementScore">
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
            
            </div> <!--end row -->

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

                <!-- Average Proximity Appointed -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate">
                        <div class="card-header bg-secondary">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1 text-white">
                                    Shortlist-to-Hire Ratio (Number of Shortlisted vs Number of Hired)
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                    <span id="shortlistToHireRatioValue" class="counter-value"  data-target="{{ $shortlistToHireRatio }}">
                                        0
                                    </span>%
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Ratio
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->
            

            <!-------------------------------------------------------------------------------------
                Interview-to-Hire Ratio
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Interview-to-Hire Ratio -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate">
                        <div class="card-header bg-success">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1 text-white">
                                    Interview-to-Hire Ratio (Number Interviewed vs Number Hired)
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                    <span id="interviewToHireRatioValue" class="counter-value"  data-target="{{ $interviewToHireRatio }}">
                                        0
                                    </span>%
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Ratio
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->

                <!-- Average Time from Vacancy Creation to Shortlist -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate">
                        <div class="card-header bg-success">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1 text-white">
                                    Average Time from Vacancy Creation to Shortlist
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                    <span id="interviewToHireRatioValue" class="counter-value"  data-target="{{ $interviewToHireRatio }}">
                                        0
                                    </span>%
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Ratio
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->

        </div> <!-- end .h-100 -->

        @include('admin.reports.stores.partials.filters')

    </div> <!-- end col -->
</div> <!-- end row -->


@endsection
@section('script')
<script>
    var totalApplicantsPlaced = @json($totalApplicantsPlaced);
    var averageTimetoHire = @json($averageTimetoHire);
    var averageAssementScore = @json($averageAssementScore);
    var averageDistanceApplicantsAppointed = @json($averageDistanceApplicantsAppointed);
    var shortlistToHireRatio = @json($shortlistToHireRatio);
    var interviewToHireRatio = @json($interviewToHireRatio);

    const defaultStartDate = '{{ $startDate }}';
        const defaultEndDate = '{{ $endDate }}';
        flatpickr("#date_range", {
            mode: "range",
            dateFormat: "Y-m-d",
            defaultDate: [defaultStartDate, defaultEndDate]
        });
</script>
<!-- sweet alert -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- apexcharts -->
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
<!-- dashboard init -->
<script src="{{URL::asset('build/js/pages/reports/stores.init.js')}}?v={{ filemtime(public_path('build/js/pages/admin.init.js')) }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection