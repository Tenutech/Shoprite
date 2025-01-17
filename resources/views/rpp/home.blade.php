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
                            <h4 class="fs-16 mb-1">
                                Hello, {{ Auth::user()->firstname }}!
                            </h4>
                            <p class="text-muted mb-0">
                                Here's what's happening with the
                                @if (isset($region))
                                    ({{ $region->name }})
                                @endif region today.
                            </p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-center">
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <input type="text" id="dateFilter" class="form-control border-0 dash-filter-picker shadow">
                                            <div class="input-group-text bg-primary border-primary text-white" id="calendarBtn">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
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
                Time
            -------------------------------------------------------------------------------------->

            <div class="row g-3" id="timeRow">
                <!-- Time to Shortlist -->
                <div class="col-xl-4 col-md-4" id="averageTimeToShortlistColumn">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="The time from when a vacancy is created until the shortlist is generated.">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Time to Shortlist
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="averageTimeToShortlistValue">
                                            <div class="spinner-border text-secondary" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0">
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
                    </div> <!-- end card -->
                </div> <!-- end col -->

                <!-- Time to Hire -->
                <div class="col-xl-4 col-md-4" id="averageTimeToHireColumn">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="The time from when a vacancy is created until a candidate is successfully placed.">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Time to Hire
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="averageTimeToHireValue">
                                            <div class="spinner-border text-secondary" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-secondary mb-0">
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
                    </div> <!-- end card -->
                </div> <!-- end col -->

                <!-- Placement Rate -->
                <div class="col-xl-4 col-md-4" id="adoptionRateColumn">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="The percentage of vacancies successfully filled.">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Placement Rate
                                    </p>
                                    <h2 class="mt-4 ff-success fw-bold">
                                        <span id="adoptionRateValue">
                                            <div class="spinner-border text-success" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
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

            <!-------------------------------------------------------------------------------------
                Proximity
            -------------------------------------------------------------------------------------->

            <div class="row g-3" id="proximityRow">
                <!-- Average Proximity Talent Pool -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="The average distance between the candidates in the talent pool and the store.">
                        <div class="card-header bg-secondary">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1 text-white">
                                    Average Proximity (Talent Pool)
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                        <span id="averageDistanceTalentPoolApplicantsValue">
                                            <div class="spinner-border text-body" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Average distance of talent pool
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->

                <!-- Average Proximity Appointed -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="The average distance between successfully appointed candidates and the store.">
                        <div class="card-header bg-secondary">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1 text-white">
                                    Average Proximity (Successful Placements)
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                        <span id="averageDistanceApplicantsAppointedValue">
                                            <div class="spinner-border text-body" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Average distance for successful placements
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->
            </div>

            <!-------------------------------------------------------------------------------------
                Scores
            -------------------------------------------------------------------------------------->

            <div class="row g-3" id="averageScoresRow">
                <!-- Average Score -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="The average overall score of candidates successfully placed.">
                        <div class="card-header bg-success">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1 text-white">
                                    Average Score
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                        <span id="averageScoreApplicantsAppointedValue">
                                            <div class="spinner-border text-body" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Average score for successful placements
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->

                <!-- Average Assessment Score -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="The average assessment score of candidates successfully placed.">
                        <div class="card-header bg-success">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1 text-white">
                                    Average Assessment Score
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                        <span id="averageAssessmentScoreApplicantsAppointedValue">
                                            <div class="spinner-border text-body" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Average assessment score for successful placements
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->

            <!-------------------------------------------------------------------------------------
                Vacancies
            -------------------------------------------------------------------------------------->

            <div class="row g-3" id="vacanciesRow">
                <!-- Total Created Vacancies -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="The total number of vacancies created.">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s0" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Total Created Vacancies
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalVacanciesValue">
                                            <div class="spinner-border text-primary" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </span>
                                    </h4>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->

                <!-- Total Vacancies Filled -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="The total number of vacancies successfully filled.">
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
                                        <span id="totalVacanciesFilledValue">
                                            <div class="spinner-border text-primary" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
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

            <div class="row g-3" id="interviewsRow">
                <!-- Total Interviews Scheduled -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="The total number of interviews scheduled.">
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
                                        <span id="totalInterviewsScheduledValue">
                                            <div class="spinner-border text-secondary" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </span>
                                    </h4>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->

                <!-- Total Interviews Conducted -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="The total number of interviews successfully conducted.">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s1" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Interviews Conducted
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalInterviewsCompletedValue">
                                            <div class="spinner-border text-secondary" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
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

            <div class="row g-3" id="applicantsRow">
                <!-- Total Candidates Selected -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="The total number of candidates selected for a position. This also indicates the percentage compared to the total scheduled interviews.">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s2" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Candidates Selected
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalApplicantsAppointedValue">
                                            <div class="spinner-border text-success" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
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
                    <div class="card card-animate overflow-hidden w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="The total number of candidates who recieved a regret notification. This also indicates the percentage compared to the total scheduled interviews.">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s3" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Candidates Regretted
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalApplicantsRegrettedValue">
                                            <div class="spinner-border text-danger" role="status" style="font-size: 12px;">
                                                <span class="sr-only">Loading...</span>
                                            </div>
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
                Demographic Information
            -------------------------------------------------------------------------------------->

            <div class="row g-3" id="demographicRow">
                <!-- Talent Pool Demographic -->
                <div class="col-xl-4 col-md-4" id="talent_pool_applicants_demographic_container">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="This chart displays the demographic distribution of candidates in the talent pool.">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                Demographic (Talent Pool)
                            </h4>
                            <div class="spinner-border text-primary" role="status" style="width:1.5rem; height:1.5rem; font-size: 12px;">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div id="talent_pool_applicants_demographic" data-colors='["--vz-primary", "--vz-info", "--vz-danger", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                            <div class="row text-center" id="talent_pool_applicants_demographic_totals"></div><!-- end -->
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->

                <!-- Interviewed Demographic -->
                <div class="col-xl-4 col-md-4" id="interviewed_applicants_demographic_container">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="This chart displays the demographic distribution of candidates who have been interviewed.">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                Demographic (Interviewed)
                            </h4>
                            <div class="spinner-border text-primary" role="status" style="width:1.5rem; height:1.5rem; font-size: 12px;">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div id="interviewed_applicants_demographic" data-colors='["--vz-primary", "--vz-info", "--vz-danger", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                            <div class="row text-center" id="interviewed_pool_applicants_demographic_totals"></div><!-- end -->
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->

                <!-- Appointed Demographic -->
                <div class="col-xl-4 col-md-4" id="appointed_applicants_demographic_container">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="This chart displays the demographic distribution of candidates who have been successfully placed.">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                Demographic (Appointed)
                            </h4>
                            <div class="spinner-border text-primary" role="status" style="width:1.5rem; height:1.5rem; font-size: 12px;">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div id="appointed_applicants_demographic" data-colors='["--vz-primary", "--vz-info", "--vz-danger", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                            <div class="row text-center" id="appointed_pool_applicants_demographic_totals"></div><!-- end -->
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->

            <!-------------------------------------------------------------------------------------
                Applicants
            -------------------------------------------------------------------------------------->

            <div class="row g-3" id="talentPoolRow">
                <div class="col-xl-12 col-md-12">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" title="This graph displays the total candidates that joined the talent pool compared to the total candidates appointed on a month-to-month basis.">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Talent Pool</h4>
                        </div><!-- end card header -->

                        <div class="card-header p-0 border-0 bg-white bg-opacity-10">
                            <div class="row g-0 text-center">
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1">
                                            <span id="talentPoolApplicantsValue">
                                                <div class="spinner-border text-body" role="status" style="width:1.5rem; height:1.5rem; font-size: 10px;">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </span>
                                        </h5>
                                        <p class="text-muted mb-0">
                                            Total Talent Pool
                                        </p>
                                    </div>
                                </div> <!--end col -->
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1">
                                            <span id="applicantsAppointedValue">
                                                <div class="spinner-border text-body" role="status" style="width:1.5rem; height:1.5rem; font-size: 10px;">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </span>
                                        </h5>
                                        <p class="text-muted mb-0">
                                            Total Appointed
                                        </p>
                                    </div>
                                </div> <!--end col -->
                            </div>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div id="talent_pool_by_month" data-colors='["--vz-primary", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                        </div> <!-- end card-body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->

            @if ($shortlist)
                @include('manager.partials.shortlist-modal', ['shortlist' => $shortlist])
            @endif

        </div> <!-- end .h-100 -->

    </div> <!-- end col -->
</div> <!-- end row -->


@endsection
@section('script')
<script>
    var shortlist = @json($shortlist);
    var region = @json($region);
</script>
<!-- sweet alert -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- apexcharts -->
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
<!-- dashboard init -->
<script src="{{URL::asset('build/js/pages/rpp.init.js')}}?v={{ filemtime(public_path('build/js/pages/rpp.init.js')) }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection