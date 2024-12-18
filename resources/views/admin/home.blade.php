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
                                Here's what's happening with Shoprite today.
                            </p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-center">
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <input type="text" id="dateFilter" class="form-control border-0 dash-filter-picker shadow">
                                            <div class="input-group-text bg-primary border-primary text-white">
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
                                            Nationwide Average
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
                                            Nationwide Average
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
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Placement Rate
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

            <!-------------------------------------------------------------------------------------
                Proximity
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Average Proximity Talent Pool -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    Average Proximity (Talent Pool)
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                    <span id="averageDistanceTalentPoolApplicantsValue" class="counter-value"  data-target="{{ $averageDistanceTalentPoolApplicants }}">
                                        0
                                    </span>km 
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
                    <div class="card card-animate">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    Average Proximity (Successful Placements)
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
                                        Average distance for successful placements
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->

            <!-------------------------------------------------------------------------------------
                Overall Scores
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Average Score Talent Pool -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    Average Score (Talent Pool)
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                        <span id="averageScoreTalentPoolApplicantsValue" class="counter-value"  data-target="{{ $averageScoreTalentPoolApplicants }}">
                                            0
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Average score of talent pool
                                    </p>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div> <!-- end col -->

                <!-- Average Score Successful Placements -->
                <div class="col-xl-6 col-md-6">
                    <div class="card card-animate">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    Average Score (Successful Placements)
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="mt-4 ff-primary fw-bold">
                                        <span id="averageScoreApplicantsAppointedValue" class="counter-value"  data-target="{{ $averageScoreApplicantsAppointed }}">
                                            0
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
            </div> <!-- end row -->

            <!-------------------------------------------------------------------------------------
                Assessment Scores
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Average Literacy Score Talent Pool -->
                <div class="col-xl-4 col-md-4">
                    <div class="card card-animate">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Average Literacy Score</h4>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div id="literacy_chart" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->

                <!-- Average Numeracy Score Talent Pool -->
                <div class="col-xl-4 col-md-4">
                    <div class="card card-animate">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Average Numeracy Score</h4>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div id="numeracy_chart" data-colors='["--vz-info"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->

                <!-- Average Situational Score Talent Pool -->
                <div class="col-xl-4 col-md-4">
                    <div class="card card-animate">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Average Situational Score</h4>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div id="situational_chart" data-colors='["--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->

            <!-------------------------------------------------------------------------------------
                Vacancies
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Total Created Vacancies -->
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
                                        Total Created Vacancies
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
            
                <!-- Total Interviews Conducted -->
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
                                        Interviews Conducted
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
                <!-- Total Candidates Selected -->
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
                                        Candidates Selected
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
                Applicants
            -------------------------------------------------------------------------------------->       

            <div class="row g-3">
                <div class="col-xl-12 col-md-12">
                    <div class="card card-animate">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Talent Pool</h4>
                        </div><!-- end card header -->

                        <div class="card-header p-0 border-0 bg-white bg-opacity-10">
                            <div class="row g-0 text-center">
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1">
                                            <span id="talentPoolApplicantsValue" class="counter-value" data-target="{{ $talentPoolApplicants }}">
                                                0
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
                                            <span id="applicantsAppointedValue" class="counter-value" data-target="{{ $applicantsAppointed }}">
                                                0
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

            <!-------------------------------------------------------------------------------------
                Application Channels
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Total WhatsApp -->
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
                                        Total WhatsApp Applications
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalWhatsAppApplicantsValue" class="counter-value" data-target="{{ $totalWhatsAppApplicants }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_whatsapp_applicants" data-colors='["--vz-success"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            
                <!-- Total Website -->
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
                                        Total Website Applications
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalWebsiteApplicantsValue" class="counter-value" data-target="{{ $totalWebsiteApplicants }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_website_applicants" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            </div> <!--end row -->

            <!-------------------------------------------------------------------------------------
                Completion & Drop Off State
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Completion Rate -->
                <div class="col-xl-6 col-md-6" id="completionRateColumn">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Completion Rate
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="completionRateValue">
                                            {{ $completionRate }}%
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span class="badge bg-light text-success mb-0">
                                            Nationwide Average
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle rounded-circle fs-2">
                                            <i data-feather="check-circle" class="text-success"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->

                <!-- Drop Off State -->
                <div class="col-xl-6 col-md-6" id="dropOffStateColumn">
                    <div class="card card-animate" data-bs-toggle="tooltip" data-bs-placement="top" data-html="true" title="{!! $dropOffChat ? $dropOffChat->message : '' !!}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-semibold text-muted mb-0">
                                        Average Drop Off State
                                    </p>
                                    <h2 class="mt-4 ff-secondary fw-bold">
                                        <span id="dropOffStateValue">
                                            {{ $dropOffState }}
                                        </span>
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Average chatbot state where applicants drop off
                                    </p>
                                </div>
                                <div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-danger-subtle rounded-circle fs-2">
                                            <i data-feather="user-x" class="text-danger"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->

            <!-------------------------------------------------------------------------------------
                Stores & Re-Employment
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Total Stores Using Solution -->
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
                                        Total Stores Using Solution
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalsStoresUsingSolutionValue" class="counter-value" data-target="{{ $totalStoresUsingSolution }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_stores_using_solution" data-colors='["--vz-danger"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            
                <!-- Total Re-Employed Applicants -->
                <div class="col-xl-6 col-md-6 d-flex">
                    <div class="card card-animate overflow-hidden w-100">
                        <div class="position-absolute start-0" style="z-index: 0;">
                            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120" width="200" height="120">
                                <path id="Shape 8" class="s4" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                            </svg>
                        </div>
                        <div class="card-body" style="z-index:1 ;">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="fw-semibold text-muted text-truncate mb-3">
                                        Total Re-Employed Candidates
                                    </p>
                                    <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                        <span id="totalReEmployedApplicantsValue" class="counter-value" data-target="{{ $totalReEmployedApplicants }}">
                                            0
                                        </span>
                                    </h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div id="total_re_employed_applicants" data-colors='["--vz-warning"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div> <!--end col -->
            </div> <!--end row -->

            <!-------------------------------------------------------------------------------------
                Demographic Information
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Talent Pool Demographic -->
                <div class="col-xl-4 col-md-4">
                    <div class="card card-animate">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                Demographic (Talent Pool)
                            </h4>
                        </div><!-- end card header -->
            
                        <div class="card-body">
                            <div id="talent_pool_applicants_demographic" data-colors='["--vz-primary", "--vz-info", "--vz-danger", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->

                <!-- Interviewed Demographic -->
                <div class="col-xl-4 col-md-4">
                    <div class="card card-animate">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                Demographic (Interviewed)
                            </h4>
                        </div><!-- end card header -->
            
                        <div class="card-body">
                            <div id="interviewed_applicants_demographic" data-colors='["--vz-primary", "--vz-info", "--vz-danger", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->

                <!-- Appointed Demographic -->
                <div class="col-xl-4 col-md-4">
                    <div class="card card-animate">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                Demographic (Appointed)
                            </h4>
                        </div><!-- end card header -->
            
                        <div class="card-body">
                            <div id="appointed_applicants_demographic" data-colors='["--vz-primary", "--vz-info", "--vz-danger", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->

            <!-------------------------------------------------------------------------------------
                Gender Information
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Talent Pool Gender -->
                <div class="col-xl-4 col-md-4">
                    <div class="card card-animate">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                Gender (Talent Pool)
                            </h4>
                        </div><!-- end card header -->
            
                        <div class="card-body">
                            <div id="talent_pool_applicants_gender" data-colors='["--vz-danger", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->

                <!-- Interviewed Gender -->
                <div class="col-xl-4 col-md-4">
                    <div class="card card-animate">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                Gender (Interviewed)
                            </h4>
                        </div><!-- end card header -->
            
                        <div class="card-body">
                            <div id="interviewed_applicants_gender" data-colors='["--vz-danger", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->

                <!-- Appointed Gender -->
                <div class="col-xl-4 col-md-4">
                    <div class="card card-animate">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                Gender (Appointed)
                            </h4>
                        </div><!-- end card header -->
            
                        <div class="card-body">
                            <div id="appointed_applicants_gender" data-colors='["--vz-danger", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->

            <!-------------------------------------------------------------------------------------
                Province Information
            -------------------------------------------------------------------------------------->

            <div class="row g-3">
                <!-- Talent Pool Province -->
                <div class="col-xl-12 col-md-12">
                    <div class="card card-animate">
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                Location (Talent Pool)
                            </h4>
                        </div><!-- end card header -->
            
                        <div class="card-body">
                            <div id="talent_pool_applicants_province" data-colors='["--vz-primary", "--vz-secondary", "--vz-info", "--vz-success", "--vz-warning", "--vz-danger", "--vz-pink", "--vz-gray", "--vz-purple"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card-body -->
                    </div><!-- end card -->
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
    var totalVacancies = @json($totalVacancies);
    var totalVacanciesFilled = @json($totalVacanciesFilled);
    var totalInterviewsScheduled = @json($totalInterviewsScheduled);
    var totalInterviewsCompleted = @json($totalInterviewsCompleted);
    var totalApplicantsAppointed = @json($totalApplicantsAppointed);
    var totalApplicantsRegretted = @json($totalApplicantsRegretted);
    var talentPoolApplicants = @json($talentPoolApplicants);
    var talentPoolApplicantsByMonth = @json($talentPoolApplicantsByMonth);
    var applicantsAppointedByMonth = @json($applicantsAppointedByMonth);
    var literacyQuestionsCount = @json($literacyQuestionsCount);
    var averageLiteracyScoreTalentPoolApplicants = @json($averageLiteracyScoreTalentPoolApplicants);
    var numeracyQuestionsCount = @json($numeracyQuestionsCount);
    var averageNumeracyScoreTalentPoolApplicants = @json($averageNumeracyScoreTalentPoolApplicants);
    var situationalQuestionsCount = @json($situationalQuestionsCount);
    var averageSituationalScoreTalentPoolApplicants = @json($averageSituationalScoreTalentPoolApplicants);
    var totalWhatsAppApplicants = @json($totalWhatsAppApplicants);
    var totalWebsiteApplicants = @json($totalWebsiteApplicants);
    var talentPoolApplicantsDemographic = @json($talentPoolApplicantsDemographic);
    var interviewedApplicantsDemographic = @json($interviewedApplicantsDemographic);
    var appointedApplicantsDemographic = @json($appointedApplicantsDemographic);
    var talentPoolApplicantsGender = @json($talentPoolApplicantsGender);
    var interviewedApplicantsGender = @json($interviewedApplicantsGender);
    var appointedApplicantsGender = @json($appointedApplicantsGender);
    var talentPoolApplicantsProvince = @json($talentPoolApplicantsProvince);
    var totalStoresUsingSolution = @json($totalStoresUsingSolution);
    var totalStores = @json($totalStores);
    var totalReEmployedApplicants = @json($totalReEmployedApplicants);
    var totalAppointedApplicants = @json($totalAppointedApplicants);
</script>
<!-- sweet alert -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- apexcharts -->
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
<!-- dashboard init -->
<script src="{{URL::asset('build/js/pages/admin.init.js')}}?v={{ filemtime(public_path('build/js/pages/admin.init.js')) }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection