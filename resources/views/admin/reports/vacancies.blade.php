@extends('layouts.master')
@section('title')
    @lang('translation.contacts')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pages
        @endslot
        @slot('title')
            Vacancy - Report
        @endslot
    @endcomponent

    <div class="row mb-3 pb-1">
        <div class="col-lg-12">
            <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                <div class="flex-grow-1">
                    <h1>Reports</h1>
                </div>
                <div class="mt-3 mt-lg-0">
                    <form action="javascript:void(0);">
                        <div class="row g-3 mb-0 align-items-center">
                            <div class="col-sm-auto">
                                <div class="input-group">
                                    {{-- <input type="text" id="dateFilter"
                                        class="form-control border-0 dash-filter-picker shadow">
                                    <div class="input-group-text bg-primary border-primary text-white">
                                        <i class="ri-calendar-2-line"></i>
                                    </div> --}}
                                    <!-- Refresh Button with Tooltip and a gap (margin-left) -->
                                    <button class="btn btn-info ms-2" id="refreshBtn" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Refresh data" onclick="location.reload();">
                                        <i class="ri-refresh-line align-bottom"></i>
                                    </button>
                                </div>
                            </div> <!--end col-->
                        </div> <!--end row -->
                    </form>
                </div>
            </div>
            {{-- Filters --}}
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Filter Form -->
                            <form id="filter-form" class="form-inline">
                                <div class="row g-3">
                                    <div class="col-xxl-2 col-sm-4">
                                        <div class="input-light">
                                            <select name="position_id" id="position_id" class="form-control" data-choices
                                            data-choices-search-true>
                                                <option value="">Select Position</option>
                                                @foreach ($positions as $position)
                                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-xxl-2 col-sm-4">
                                        <div class="input-light">
                                            <select name="store_id" id="store_id" class="form-control" data-choices
                                            data-choices-search-true>
                                                <option value="">Select Store</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-xxl-2 col-sm-4">
                                        <div class="input-light">
                                            <select name="user_id" id="user_id" class="form-control" data-choices
                                            data-choices-search-true>
                                                <option value="">Select User</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->firstname }} {{ $user->lastname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-xxl-2 col-sm-4">
                                        <div class="input-light">
                                            <select name="type_id" id="type_id" class="form-control" data-choices
                                            data-choices-search-true>
                                                <option value="">Select Vacancy Type</option>
                                                @foreach ($types as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-xxl-3 col-sm-5">
                                        <div class="input-group">
                                            <input type="text" id="date_range" class="form-control border-0 dash-filter-picker shadow"
                                            placeholder="Select Date Range">
                                            <div class="input-group-text bg-primary border-primary text-white">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-1 col-sm-2">
                                        {{-- <div class="input-light"> --}}
                                            <button type="button" id="filter-button" class="btn btn-primary">Filter</button>
                                        {{-- </div> --}}
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHART 1 --}}
    <div class="row g-3">
        <div class="col-xl-12 col-md-12">
            <div class="card card-animate">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Total Vacancy Types</h4>
                    <div class="export-buttons">
                        <button onclick="exportVacancyTypes()" class="btn btn-secondary">Export</button>
                    </div>
                </div><!-- end card header -->

                <div class="card-header p-0 border-0 bg-white bg-opacity-10">
                    <div class="row g-0 text-center">
                        <div class="col-3 col-sm-3">
                            <div class="p-3 border border-dashed border-start-0">
                                <h5 class="mb-1">
                                    <span id="totalFullTime" class="counter-value">
                                        0
                                    </span>
                                </h5>
                                <p class="text-muted mb-0">
                                    Total Full Time
                                </p>
                            </div>
                        </div> <!--end col -->
                        <div class="col-3 col-sm-3">
                            <div class="p-3 border border-dashed border-start-0">
                                <h5 class="mb-1">
                                    <span id="totalPartTime" class="counter-value">
                                        0
                                    </span>
                                </h5>
                                <p class="text-muted mb-0">
                                    Total Part Time
                                </p>
                            </div>
                        </div> <!--end col -->
                        <div class="col-3 col-sm-3">
                            <div class="p-3 border border-dashed border-start-0">
                                <h5 class="mb-1">
                                    <span id="totalFixedTerm" class="counter-value">
                                        0
                                    </span>
                                </h5>
                                <p class="text-muted mb-0">
                                    Total Fixed Term
                                </p>
                            </div>
                        </div> <!--end col -->
                        <div class="col-3 col-sm-3">
                            <div class="p-3 border border-dashed border-start-0">
                                <h5 class="mb-1">
                                    <span id="totalPeakSeason" class="counter-value">
                                        0
                                    </span>
                                </h5>
                                <p class="text-muted mb-0">
                                    Total Peak Season
                                </p>
                            </div>
                        </div> <!--end col -->
                    </div>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="vacancyTypesByMonthChart"></div>
                </div> <!-- end card-body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div> <!-- end row -->
    {{-- END CHART 1 --}}

    {{-- CHART 2 --}}
    <div class="row g-3">
        <div class="col-xl-12 col-md-12">
            <div class="card card-animate">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Total Vacancies Chart</h4>
                    <div class="export-buttons">
                        <button onclick="exportVacanciesOverTime()" class="btn btn-secondary">Export</button>
                    </div>
                </div><!-- end card header -->

                <div class="card-header p-0 border-0 bg-white bg-opacity-10">
                    <div class="row g-0 text-center">
                        <div class="col-6 col-sm-6">
                            <div class="p-3 border border-dashed border-start-0">
                                <h5 class="mb-1">
                                    <span id="totalVacancies" class="counter-value">0</span>
                                </h5>
                                <p class="text-muted mb-0">
                                    Total Vacancies
                                </p>
                            </div>
                        </div> <!--end col -->
                        <div class="col-6 col-sm-6">
                            <div class="p-3 border border-dashed border-start-0">
                                <h5 class="mb-1">
                                    <span id="filledVacancies" class="counter-value">
                                        0
                                    </span>
                                </h5>
                                <p class="text-muted mb-0">
                                    Total Vacancies Filled
                                </p>
                            </div>
                        </div> <!--end col -->
                    </div>
                </div><!-- end card header -->

                <div class="card-body">
                    <div id="vacanciesOverTimeChart"></div>
                </div> <!-- end card-body -->
            </div> <!-- end card -->
        </div> <!-- end col -->
    </div> <!-- end row -->
    {{-- END CHART 2 --}}
@endsection
@section('script')
    <script>
        // Initialize Flatpickr with default date range
        const defaultStartDate = '{{ $defaultStartDate }}';
        const defaultEndDate = '{{ $defaultEndDate }}';

        flatpickr("#date_range", {
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
