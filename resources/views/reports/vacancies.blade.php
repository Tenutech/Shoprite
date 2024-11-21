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
                    </div>

                    <!-------------------------------------------------------------------------------------
                        Vacancies Totals
                    -------------------------------------------------------------------------------------->

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
                                                Total Vacancies Created
                                            </p>
                                            <h4 class="fs-22 fw-bold ff-secondary mb-0">
                                                <span id="totalVacanciesValue" class="counter-value" data-target="{{ $totalVacancies }}">
                                                    0
                                                </span>
                                            </h4>
                                        </div>
                                        <div id="totalVacanciesChart" class="flex-shrink-0 d-none">
                                            <div id="total_vacancies" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
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
                                                <span id="totalVacanciesFilledValue" class="counter-value" data-target="{{ $totalVacanciesFilled }}">
                                                    0
                                                </span>
                                            </h4>
                                        </div>
                                        <div id="totalVacanciesFilledChart" class="flex-shrink-0">
                                            <div id="total_vacancies_filled" data-colors='["--vz-success"]' class="apex-charts" dir="ltr"></div>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </div> <!--end col -->
                    </div> <!--end row -->

                    <!-------------------------------------------------------------------------------------
                        Vacancies By Month
                    -------------------------------------------------------------------------------------->

                    <div class="row">
                        <div class="col-xl-12 col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        Vacancy Statistics
                                    </h4>
                                </div><!-- end card header -->
                    
                                <div class="card-body">
                                    <div id="vacancies_by_month" data-colors='["--vz-primary", "--vz-success", "--vz-danger", "--vz-warning", "--vz-info", "--vz-secondary"]' class="apex-charts" dir="ltr"></div>
                                </div><!-- end card-body -->
                            </div><!-- end card -->
                        </div>
                        <!-- end col -->
                    </div>
                    <!-- end row -->

                    <!-------------------------------------------------------------------------------------
                        Vacancies By Type
                    -------------------------------------------------------------------------------------->

                    <div class="row">
                        <div class="col-xl-12 col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        Vacancy Types
                                    </h4>
                                </div><!-- end card header -->
                    
                                <div class="card-body">
                                    <div id="vacancies_by_type" data-colors='["--vz-danger", "--vz-warning", "--vz-info", "--vz-secondary"]' class="apex-charts" dir="ltr"></div>
                                </div><!-- end card-body -->
                            </div><!-- end card -->
                        </div>
                        <!-- end col -->
                    </div>
                    <!-- end row -->
                </div> <!-- end col -->
            </div> <!-- end col -->

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
                                        Vacancy Filters
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

                                <!-- Position -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="gender" class="form-label">
                                            Position
                                        </label>
                                        <select class="form-control" id="position" name="position_id">
                                            <option value="" selected>Select position</option>
                                            @foreach ($positions as $position)
                                                <option value="{{ $position->id }}">{{ $position->name }} ({{ optional($position->brand)->name ?: 'N/A' }})</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a position!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->

                                <div class="row g-2 mt-0">
                                    <!-- Open Positions -->
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="openPositions" class="form-label">
                                                Open Positions
                                            </label>
                                            <input type="number" id="openPositions" name="open_positions" class="form-control" min="0" max="10" step="1" placeholder="Number of open positions">
                                            <div class="invalid-feedback">
                                                Please select a number of open positions!
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end col-->
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="maxLiteracy" class="form-label">
                                                Filled Positions
                                            </label>
                                            <input type="number" id="filledPositions" name="filled_positions" class="form-control" min="0" max="10" step="1" placeholder="Number of filled positions">
                                            <div class="invalid-feedback">
                                                Please select a number of filled positions!
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end col-->
                                </div>

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
                                                <option value="{{ $store->id }}" division-id="{{ $store->division_id }}" region-id="{{ $store->region_id }}">{{ $store->code }} - {{ optional($store->brand)->name }} ({{ $store->name }})</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a store!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->
                                
                                <!-- User -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="user" class="form-label">
                                            User
                                        </label>
                                        <select class="form-control" id="user" name="user_id">
                                            <option value="" selected>Select user</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->firstname }} {{ $user->lastname }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a user!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->

                                <!-- Type -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="type" class="form-label">
                                            Type
                                        </label>
                                        <select class="form-control" id="type" name="type_id">
                                            <option value="" selected>Select vacancy type</option>
                                            @foreach ($types as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a type!
                                        </div>
                                    </div>
                                </div>
                                <!-- end col-->

                                <!-- Unactioned -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="unactioned" class="form-label">
                                            Vacancy Unactioned
                                        </label>
                                        <select class="form-control" id="unactioned" name="unactioned">
                                            <option value=""selected>Select unactioned status</option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a unactioned status!
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

        </div> <!-- end row -->
    </div> <!-- end col -->
@endsection
@section('script')
    <script>
        var totalVacancies = @json($totalVacancies);
        var totalVacanciesFilled = @json($totalVacanciesFilled);
        var totalVacanciesByMonth = @json($totalVacanciesByMonth);
        var totalVacanciesFilledByMonth = @json($totalVacanciesFilledByMonth);
        var totalVacanciesTypeByMonth = @json($totalVacanciesTypeByMonth);
        var totalVacanciesByType = @json($totalVacanciesByType);
    </script>
    <!-- sweet alert -->
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
    <!-- dashboard init -->
    <script src="{{ URL::asset('build/js/pages/vacancies-report.init.js') }}?v={{ filemtime(public_path('build/js/pages/vacancies-report.init.js')) }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
