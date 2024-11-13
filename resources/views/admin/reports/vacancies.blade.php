@extends('layouts.master')
@section('title')
    @lang('translation.dashboards')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
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
                    <div class="col-lg-12">
                        <div class="mt-3 mt-lg-0">
                            <div class="row g-3 mb-0 float-end">
                                <div class="col-sm-auto">
                                    <div class="hstack gap-1">
                                        <!-- Filter Button -->
                                        <button class="btn btn-secondary btn-label" id="open-filters"
                                            data-bs-toggle="offcanvas" data-bs-target="#filters-canvas"
                                            aria-controls="member-overview">
                                            <i class="ri-equalizer-line label-icon align-middle fs-16 me-2"></i>
                                            Filters
                                        </button>
                                        <!-- Refresh Button with Tooltip and a gap (margin-left) -->
                                        <button class="btn btn-info ms-2" id="refreshBtn" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Refresh data" onclick="location.reload();">
                                            <i class="ri-refresh-line align-bottom"></i>
                                        </button>
                                    </div>
                                </div> <!--end col-->
                            </div> <!--end row -->
                        </div>

                        {{-- CANVAS --}}
                        <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="filters-canvas">
                            <div class="offcanvas-body profile-offcanvas d-flex flex-column p-0" style="height: 100vh;">
                                <!-- Main content that can scroll if necessary -->
                                <div class="flex-grow-1">
                                    <div class="team-cover">
                                        <img src="{{ URL::asset('build/icons/auth-two-bg.jpg') }}" alt=""
                                            class="img-fluid" />
                                    </div>
                                    <div class="p-5"></div>
                                    <div class="p-3 mt-4 text-center">
                                        <div class="mt-3">
                                            <h5 class="fs-15 profile-name">
                                                Applicant Report Filters
                                            </h5>
                                        </div>
                                    </div>

                                    {{-- Position --}}
                                    <div class="row g-0 p-3">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="position" class="form-label">
                                                    Position
                                                </label>
                                                <select name="position_id" id="positionFilter" class="form-control"
                                                    data-choices data-choices-search-false>
                                                    <option value="" selected>Select Position</option>
                                                    @foreach ($positions as $position)
                                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        {{-- end col --}}
                                        {{-- Store --}}
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="store" class="form-label">
                                                    Store
                                                </label>
                                                <select name="store_id" id="storeFilter" class="form-control" data-choices
                                                    data-choices-search-false>
                                                    <option value="" selected>Select Store</option>
                                                    @foreach ($stores as $store)
                                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        {{-- end col --}}
                                        {{-- User --}}
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="user" class="form-label">
                                                    User
                                                </label>
                                                <select name="user_id" id="userFilter" class="form-control" data-choices
                                                    data-choices-search-false>
                                                    <option value="" selected>Select User</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->firstname }}
                                                            {{ $user->lastname }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        {{-- end col --}}
                                        {{-- Type --}}
                                        <div class="col-12 d-none">
                                            <div class="mb-3">
                                                <label for="type" class="form-label">
                                                    Type
                                                </label>
                                                <select name="type_id" id="type_id" class="form-control" data-choices
                                                    data-choices-search-false>
                                                    <option value="" selected>Select Vacancy Type</option>
                                                    @foreach ($types as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        {{-- end col --}}
                                        {{-- Filled Status --}}
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="filled_status" class="form-label">
                                                    Filled Status
                                                </label>
                                                <select name="filled_positions" id="filled_positions" class="form-control"
                                                    data-choices data-choices-search-true>
                                                    <option value="" selected>Filled Status</option>
                                                    <option value='0'>Not Filled</option>
                                                    <option value='1'>Filled</option>
                                                </select>
                                            </div>
                                        </div>
                                        {{-- end col --}}
                                        {{-- Date --}}
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="date" class="form-label">
                                                    Date Range
                                                </label>
                                                <input type="text" id="date_range"
                                                    class="form-control border-0 dash-filter-picker shadow"
                                                    placeholder="Select Date Range">
                                                <div class="input-group-text bg-primary border-primary text-white">
                                                    <i class="ri-calendar-2-line"></i>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- end col --}}
                                        <!-- end main content -->
                                    </div>

                                    <!-- Sticky footer at the bottom -->
                                    <div class="offcanvas-footer p-3 hstack gap-3 text-center position-absolute w-100 bg-white"
                                        style="bottom: 0;">
                                        <button class="btn btn-light btn-label w-100" id="clearFilters">
                                            <i class="ri-delete-bin-fill label-icon align-middle fs-16 me-2"></i>
                                            Clear Filters
                                        </button>

                                        <button type="submit" class="btn btn-secondary btn-label w-100"
                                            id="filter-button">
                                            <i class="ri-equalizer-fill label-icon align-middle fs-16 me-2"></i>
                                            Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- end offcanvas-->
                        </div>
                    </div>

                    {{-- Chart 1 - Stats --}}
                    <div class="row g-3">
                        <!-- Total Full Time -->
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
                                                Total Full Time
                                            </p>
                                            <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                                <span id="totalFullTime" class="counter-value">
                                                    0
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </div> <!--end col -->

                        <!-- Total Part Time -->
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
                                                Total Part Time
                                            </p>
                                            <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                                <span id="totalPartTime" class="counter-value">
                                                    0
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </div> <!--end col -->

                         <!-- Total Fixed Term -->
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
                                                Total Fixed Term
                                            </p>
                                            <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                                <span id="totalFixedTerm" class="counter-value">
                                                    0
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </div> <!--end col -->

                        <!-- Total Peak Season -->
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
                                                Total Peak Season
                                            </p>
                                            <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                                <span id="totalPeakSeason" class="counter-value">
                                                    0
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </div> <!--end col -->
                    </div> <!--end row -->

                    {{-- CHART 1 --}}
                    <div class="row g-3">
                        <div class="col-xl-12 col-md-12">
                            <div class="card">
                                <div class="card-header border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Total Vacancy Types</h4>
                                    <div class="export-buttons">
                                        <button id="exportVacancyTypes" class="btn btn-success btn-label">
                                            <i class="ri-file-excel-2-fill label-icon align-middle fs-16 me-2"></i>
                                            Export Report
                                        </button>
                                    </div>
                                </div><!-- end card header -->
                                <div class="card-body">
                                    <div id="vacancyTypesByMonthChart"></div>
                                </div> <!-- end card-body -->
                            </div> <!-- end card -->
                        </div> <!-- end col -->
                    </div> <!-- end row -->
                    {{-- END CHART 1 --}}

                    {{-- Chart 2 - Stats --}}
                    <div class="row g-3">
                        <!-- Total Vacancies -->
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
                                                Total Vacancies
                                            </p>
                                            <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                                <span id="totalVacancies" class="counter-value">
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
                                        <path id="Shape 8" class="s2" d="m189.5-25.8c0 0 20.1 46.2-26.7 71.4 0 0-60 15.4-62.3 65.3-2.2 49.8-50.6 59.3-57.8 61.5-7.2 2.3-60.8 0-60.8 0l-11.9-199.4z" />
                                    </svg>
                                </div>
                                <div class="card-body" style="z-index:1 ;">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="fw-semibold text-muted text-truncate mb-3">
                                                Total Vacancies Filled
                                            </p>
                                            <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                                <span id="filledVacancies" class="counter-value">
                                                    0
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </div> <!--end col -->
                    </div> <!--end row -->

                    {{-- CHART 2 --}}
                    <div class="row g-3">
                        <div class="col-xl-12 col-md-12">
                            <div class="card">
                                <div class="card-header border-0 align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">Total Vacancies Chart</h4>
                                    <div class="export-buttons">
                                        <button id="exportVacanciesOverTime" class="btn btn-success btn-label">
                                            <i class="ri-file-excel-2-fill label-icon align-middle fs-16 me-2"></i>
                                            Export Report
                                        </button>
                                    </div>
                                </div><!-- end card header -->

                                <div class="card-body">
                                    <div id="vacanciesOverTimeChart"></div>
                                </div> <!-- end card-body -->
                            </div> <!-- end card -->
                        </div> <!-- end col -->
                    </div> <!-- end row -->
                    {{-- END CHART 2 --}}
                </div> <!-- end col -->
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div> <!-- end col -->
@endsection
@section('script')
    <script>
        // Define default dates for Flatpickr
        const defaultStartDate = new Date(new Date().getFullYear(), 0, 1); // Start of the current year
        const defaultEndDate = new Date(); // Today's date

        // Initialize Flatpickr with default date range
        const dateRangePicker = flatpickr("#date_range", {
            mode: "range",
            dateFormat: "Y-m-d",
            defaultDate: [defaultStartDate, defaultEndDate]
        });
    </script>
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/vacancies-report.init.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
