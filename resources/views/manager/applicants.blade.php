@extends('layouts.master')
@section('title') Applicant List @endsection
@section('css')
<link href="{{ URL::asset('build/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
<style>
    .choices__list--dropdown {
        visibility: visible !important;
    }
</style>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pages @endslot
@slot('title') Saved Applicants @endslot
@endcomponent

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="d-md-flex justify-content-sm-start gap-2">
            <div class="search-box ms-md-2 flex-shrink-0 flex-grow-1 mb-3 mb-md-0">
                <input type="text" class="form-control" id="searchApplicant" autocomplete="off" placeholder="Search for applicant...">
                <i class="ri-search-line search-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="d-md-flex justify-content-sm-end gap-2">
            <div class="search-box ms-md-2 flex-shrink-0 mb-3 mb-md-0">
                <button type="button" class="btn btn-secondary btn-label rounded-pill" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
                    <i class="ri-equalizer-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                    Filters
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="collapse" id="collapseFilters">
        <div class="card mb-0">
            <div class="card-body">
                <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="modal" data-bs-target="#mapModal">
                    <i class="ri-map-pin-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                    Location
                </button>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ri-building-2-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        Town
                    </button>
                    <div class="dropdown-menu p-2">
                        <select id="selectTown" class="form-control">
                            @foreach ($towns as $town)
                                <option value="town_id;{{ $town->id }}">{{ $town->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ri-men-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        Gender
                    </button>
                    <ul class="dropdown-menu">
                        @foreach ($genders as $gender)
                            <a class="dropdown-item filter-button" data-bs-filter="gender_id;{{ $gender->id }}">
                                {{ $gender->name }}
                            </a>
                        @endforeach
                    </ul>
                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ri-user-3-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        Race
                    </button>
                    <ul class="dropdown-menu">
                        @foreach ($races as $race)
                            <a class="dropdown-item filter-button" data-bs-filter="race_id;{{ $race->id }}">
                                {{ $race->name }}
                            </a>
                        @endforeach
                    </ul>
                </div>

                <button type="button" class="btn btn-light btn-label rounded-pill filter-button" data-bs-filter="citizen;Yes">
                    <i class="ri-shield-user-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                    Citizen
                </button>

                <button type="button" class="btn btn-light btn-label rounded-pill filter-button" data-bs-filter="foreign_national;Yes">
                    <i class="ri-map-pin-user-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                    Foreign National
                </button>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ri-briefcase-4-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        Position
                    </button>
                    <ul class="dropdown-menu">
                        @foreach ($positions as $position)
                            <a class="dropdown-item filter-button" data-bs-filter="position_id;{{ $position->id }}">
                                {{ $position->name }}
                            </a>
                        @endforeach
                    </ul>
                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ri-book-read-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        Education
                    </button>
                    <ul class="dropdown-menu">
                        @foreach ($educations as $education)
                            <a class="dropdown-item filter-button" data-bs-filter="education_id;{{ $education->id }}">
                                {{ $education->name }}
                            </a>
                        @endforeach
                    </ul>
                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ri-car-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        License
                    </button>
                    <ul class="dropdown-menu">
                        <a class="dropdown-item filter-button" data-bs-filter="drivers_license_code;A">
                            A
                        </a>
                        <a class="dropdown-item filter-button" data-bs-filter="drivers_license_code;B">
                            B
                        </a>
                        <a class="dropdown-item filter-button" data-bs-filter="drivers_license_code;C1">
                            C1
                        </a>
                        <a class="dropdown-item filter-button" data-bs-filter="drivers_license_code;EB, EC1, EC">
                            EB, EC1, EC
                        </a>
                    </ul>
                </div>

                <button type="button" class="btn btn-light btn-label rounded-pill filter-button" data-bs-filter="has_bank_account;Yes">
                    <i class="ri-bank-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                    Bank Account
                </button>

                <button type="button" class="btn btn-light btn-label rounded-pill filter-button" data-bs-filter="literacy_score;literacy"> 
                    <i class="ri-book-open-line label-icon align-middle rounded-pill fs-16 me-2"></i>
                    Literacy
                </button>

                <button type="button" class="btn btn-light btn-label rounded-pill filter-button" data-bs-filter="numeracy_score;numeracy"> 
                    <i class="ri-hashtag label-icon align-middle rounded-pill fs-16 me-2"></i>
                    Numeracy
                </button>

                <div class="live-preview mt-4">
                    <div class="d-flex flex-wrap gap-2 align-items-center" id="filterBadges"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row gy-2 mb-2" id="candidate-list">

</div>
<!-- end row -->

<div class="noresult" style="display: none">
    <div class="text-center">
        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
            trigger="loop" colors="primary:#121331,secondary:#08a88a"
            style="width:75px;height:75px">
        </lord-icon>
        <h5 class="mt-2">
            Sorry! No Result Found
        </h5>
        <p class="text-muted mb-0">
            We've searched all the applicants. We did not find any applicants for you search.
        </p>
    </div>
</div>

<div class="row g-0 justify-content-end mb-4" id="pagination-element">
    <!-- end col -->
    <div class="col-sm-6">
        <div class="pagination-block pagination pagination-separated justify-content-center justify-content-sm-end mb-sm-0">
            <div class="page-item">
                <a href="javascript:void(0);" class="page-link" id="page-prev">Previous</a>
            </div>
            <span id="page-num" class="pagination"></span>
            <div class="page-item">
                <a href="javascript:void(0);" class="page-link" id="page-next">Next</a>
            </div>
        </div>
    </div><!-- end col -->
</div>
<!-- end row -->

<!--  Map Modal -->
<div class="modal fade bs-example-modal-xl" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="myExtraLargeModalLabel">
                    Select Location
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Range Slider -->
                <div id="rangeSlider" class="mb-3" data-rangeslider data-slider-color="primary"></div>
                <span id="rangeValue" class="mb-3">Selected Range: 10km</span>

                <!-- Google Maps -->
                <div id="map" class="mt-3" style="height: 600px;"></div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0);" class="btn btn-link link-light fw-medium" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1 align-middle"></i> 
                    Close
                </a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection
@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/nouislider/nouislider.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/wnumb/wNumb.min.js') }}"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googlemaps.key') }}&callback=initMap"></script>
<!-- job-candidate-grid js -->
<script src="{{URL::asset('build/js/pages/applicants.init.js')}}"></script>
<script src="{{ URL::asset('build/js/pages/applicant-save.init.js') }}"></script>

<!-- App js -->
<script src="{{URL::asset('build/js/app.js')}}"></script>
@endsection
