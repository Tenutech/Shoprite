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
                        <div class="flex-grow-1">
                            <a href="{{ route('email.export') }}" type="button" class="btn btn-success btn-label">
                                <i class="ri-file-excel-2-fill label-icon align-middle fs-16 me-2"></i>
                                Export Report
                            </a>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
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
                            </form>                            
                        </div>
                    </div><!-- end card header -->
                </div> <!--end col -->
            </div> <!--end row -->

            <!-------------------------------------------------------------------------------------
                Applicant Totals
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Total Applicants -->
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
                                        Total Applicants
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalApplicantsValue" class="counter-value" data-target="{{ $totalApplicants }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_applicants" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            
                <!-- Total Appointed Applicants -->
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
                                        Total Appointed Applicants
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalAppointedApplicantsValue" class="counter-value" data-target="{{ $totalAppointedApplicants }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_appointed_applicants" data-colors='["--vz-success"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            </div> <!--end row -->

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
                                    Applicant Filters
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
                                        <input type="text" id="date" class="form-control border-0 dash-filter-picker shadow" required>
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

                            <!-- Gender -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">
                                        Gender
                                    </label>
                                    <select class="form-control" id="gender" name="gender_id" data-choices data-choices-search-false>
                                        <option value="" selected>Select gender</option>
                                        @foreach ($genders as $gender)
                                            <option value="{{ $gender->id }}">
                                                {{ $gender->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a gender!
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <!-- Ethnicity -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="race" class="form-label">
                                        Ethnicity
                                    </label>
                                    <select class="form-control" id="race" name="race_id" data-choices data-choices-search-false>
                                        <option value="" selected>Select ethnicity</option>
                                        @foreach ($races as $race)
                                            <option value="{{ $race->id }}">
                                                {{ $race->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select an ethnicity!
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="row g-2 mt-0">
                                <!-- Age -->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="minAge" class="form-label">
                                            Min Age
                                        </label>
                                        <input type="number" id="minAge" name="min_age" class="form-control" min="18" max="80" step="1" placeholder="18">
                                        <div class="invalid-feedback">
                                            Please select a minimum age!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="maxAge" class="form-label">
                                            Max Age
                                        </label>
                                        <input type="number" id="maxAge" name="max_age" class="form-control" min="18" max="80" step="1" placeholder="80">
                                        <div class="invalid-feedback">
                                            Please select a maximum age!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->
                            </div>
                            
                            <!-- Highest Qualification -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="education" class="form-label">
                                        Highest Qualification
                                    </label>
                                    <select class="form-control" id="education" name="education_id" data-choices data-choices-search-false>
                                        <option value="" selected>Select education level</option>
                                        @foreach ($educations as $education)
                                            <option value="{{ $education->id }}">
                                                {{ $education->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select an education level!
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <!-- Experience -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="experience" class="form-label">
                                        Experience
                                    </label>
                                    <select class="form-control" id="experience" name="experience_id" data-choices data-choices-search-false>
                                        <option value="" selected>Select experience level</option>
                                        @foreach ($experiences as $experience)
                                            <option value="{{ $experience->id }}">
                                                {{ $experience->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select an experience level!
                                    </div>
                                </div>
                            </div>
                            <!-- end col-->

                            <div class="row g-2 mt-0">
                                <!-- Literacy Score -->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="minLiteracy" class="form-label">
                                            Min Literacy Score
                                        </label>
                                        <input type="number" id="minLiteracy" name="min_literacy" class="form-control" min="0" max="10" step="1" placeholder="Score out of 10">
                                        <div class="invalid-feedback">
                                            Please select a minimum literacy score!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col-->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="maxLiteracy" class="form-label">
                                            Max Literacy Score
                                        </label>
                                        <input type="number" id="maxLiteracy" name="max_literacy" class="form-control" min="0" max="10" step="1" placeholder="Score out of 10">
                                        <div class="invalid-feedback">
                                            Please select a maximum literacy score!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col-->
                            
                                <!-- Numeracy Score -->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="minNumeracy" class="form-label">
                                            Min Numeracy Score
                                        </label>
                                        <input type="number" id="minNumeracy" name="min_numeracy" class="form-control" min="0" max="10" step="1" placeholder="Score out of 10">
                                        <div class="invalid-feedback">
                                            Please select a minimum numeracy score!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col-->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="maxNumeracy" class="form-label">
                                            Max Numeracy Score
                                        </label>
                                        <input type="number" id="maxNumeracy" name="max_numeracy" class="form-control" min="0" max="10" step="1" placeholder="Score out of 10">
                                        <div class="invalid-feedback">
                                            Please select a maximum numeracy score!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col-->
                            
                                <!-- Situational Score -->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="minSituational" class="form-label">
                                            Min Situational Score
                                        </label>
                                        <input type="number" id="minSituational" name="min_situational" class="form-control" min="0" max="10" step="1" placeholder="Score out of 10">
                                        <div class="invalid-feedback">
                                            Please select a minimum situational score!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col-->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="maxSituational" class="form-label">
                                            Max Situational Score
                                        </label>
                                        <input type="number" id="maxSituational" name="max_situational" class="form-control" min="0" max="10" step="1" placeholder="Score out of 10">
                                        <div class="invalid-feedback">
                                            Please select a maximum situational score!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col-->
                            
                                <!-- Overall Score -->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="minOverall" class="form-label">
                                            Min Overall Score
                                        </label>
                                        <input type="number" id="minOverall" name="min_overall" class="form-control" min="0" max="5" step="0.01" placeholder="Score out of 5">
                                        <div class="invalid-feedback">
                                            Please select a minimum overall score!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col-->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="maxOverall" class="form-label">
                                            Max Overall Score
                                        </label>
                                        <input type="number" id="maxOverall" name="max_overall" class="form-control" min="0" max="5" step="0.01" placeholder="Score out of 5">
                                        <div class="invalid-feedback">
                                            Please select a maximum overall score!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col-->
                            </div>

                            <!-- Employment -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="employment" class="form-label">
                                        Employment Status
                                    </label>
                                    <select class="form-control" id="employment" name="employment" data-choices data-choices-search-false>
                                        <option value="" selected>Select employment status</option>
                                        <option value="A">Active Employee</option>
                                        <option value="B">Blacklisted</option>
                                        <option value="I">Inconclusive</option>
                                        <option value="P">Previously Employed</option>
                                        <option value="N">Not an Employee</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select an employment status!
                                    </div>
                                </div>
                            </div>
                            <!-- end col-->

                            <!-- Shortlisted -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="shortlisted" class="form-label">
                                        Shortlisted
                                    </label>
                                    <select class="form-control" id="shortlisted" name="shortlisted" data-choices data-choices-search-false>
                                        <option value=""selected>Select shortlist status</option>
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a shortlis status!
                                    </div>
                                </div>
                            </div>
                            <!-- end col-->

                            <!-- Interviewed -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="interviewed" class="form-label">
                                        Interviewed
                                    </label>
                                    <select class="form-control" id="interviewed" name="interviewed" data-choices data-choices-search-false>
                                        <option value="" selected>Select interview status</option>
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a interview status!
                                    </div>
                                </div>
                            </div>
                            <!-- end col-->

                            <!-- Appointed -->
                            <div class="col-12 mb-5">
                                <div class="mb-3">
                                    <label for="appointed" class="form-label">
                                        Appointed
                                    </label>
                                    <select class="form-control" id="appointed" name="appointed" data-choices data-choices-search-false>
                                        <option value="" selected>Select appointment status</option>
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a appointment status!
                                    </div>
                                </div>
                            </div>
                            <!-- end col-->
                        </div>
                        <div style="height: 100px;"></div>
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
    var totalApplicants = @json($totalApplicants);
    var totalAppointedApplicants = @json($totalAppointedApplicants);
</script>
<!-- sweet alert -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- apexcharts -->
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
<!-- dashboard init -->
<script src="{{URL::asset('build/js/pages/applicants-report.init.js')}}?v={{ filemtime(public_path('build/js/pages/applicants-report.init.js')) }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection