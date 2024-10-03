@extends('layouts.master')
@section('title') Applicant List @endsection
@section('css')
<link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/classic.min.css') }}" />
<link href="{{ URL::asset('build/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
<style>
    .choices__list--dropdown {
        visibility: visible !important;
    }

    .choices {
        margin-bottom: 0px !important;
    }

    #applicantTypeChoice .choices__list--dropdown {
        visibility: hidden !important;
    }

    #applicantTypeChoice .is-active {
        visibility: visible !important;
    }

    #applicantsIntreview .choices__list--dropdown {
        visibility: hidden !important;
    }

    #applicantsIntreview .is-active {
        visibility: visible !important;
    }

    #applicantsContractDiv .choices__list--dropdown {
        visibility: hidden !important;
    }

    #applicantsContractDiv .is-active {
        visibility: visible !important;
    }

    #applicantsVacancyDiv .choices__list--dropdown {
        visibility: hidden !important;
    }

    #applicantsVacancyDiv .is-active {
        visibility: visible !important;
    }

    #vacancyFillDiv .choices__list--dropdown {
        visibility: hidden !important;
    }

    #vacancyFillDiv .is-active {
        visibility: visible !important;
    }
    #sapNumberDiv .choices__list--dropdown {
        visibility: hidden !important;
    }

    #sapNumberDiv .is-active {
        visibility: visible !important;
    }
</style>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Pages @endslot
@slot('title') Shortlist @endslot
@endcomponent

<!-------------------------------------------------------------------------------------
    Shortlist Settings
-------------------------------------------------------------------------------------->

<div class="row g-4 mb-4">
    <div class="col-md-12">
        <div class="mb-3" id="applicantTypeChoice">
            <label for="vacancy" class="form-label">
                Vacancy
            </label>
            <select class="form-control" id="vacancy" name="vacancy_id" required>
                <option value="">Select Vacancy</option>
                @foreach ($vacancies as $vacancyOption)
                    <option value="{{ Crypt::encryptString($vacancyOption->id) }}" {{ ($vacancyID && $vacancyID == $vacancyOption->id) ? 'selected' : '' }}>{{ $vacancyOption->id }}. {{ $vacancyOption->position->name }}: ({{ $vacancyOption->store->brand->name }} - {{ $vacancyOption->store->name }})</option>
                @endforeach
            </select>
            <div class="invalid-feedback">Please select a vacancy</div>
        </div>                                                       
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="number" class="form-label">
                Shortlist Number
            </label>
            <input type="number" class="form-control" id="number" name="number" placeholder="Enter number of applicants" value="{{ ($vacancyID && $shortlistedApplicants) ? count($shortlistedApplicants) : $minShortlistNumber }}" min="{{ $minShortlistNumber }}" max="{{ $maxShortlistNumber }}" required />
            <div class="invalid-feedback">
                Please enter a number above {{ $minShortlistNumber }} and below {{ $maxShortlistNumber}}
            </div>
            <div class="text-muted">
                Please select a minimum of {{ $minShortlistNumber }} and a maximum of {{ $maxShortlistNumber}} appliacnts.
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3" id="applicantTypeChoice">
            <label for="shortlistType" class="form-label">
                Shortlist Type
            </label>
            <select class="form-control" id="shortlistType" name="shortlist_type_id" data-choices data-choices-search-true required>
                <option value="">Select Type</option>
                    <!-- <option value="1">Talent Pool/Candidates</option> -->
                    <!-- <option value="2">Applicants</option> -->
                    <option value="3" selected>Any</option>
                    <option value="4">Saved Applicants</option>
            </select>
            <div class="invalid-feedback">Please select shortlist type</div>
        </div>                                                       
    </div>

    <div class="col-md-6 d-none">
        <div class="mb-3" id="applicantTypeChoice">
            <label for="applicantType" class="form-label">
                Applicant Type
            </label>
            <select class="form-control" id="applicantType" name="applicant_type_id" data-choices data-choices-search-true>
                <option value="">Select Type</option>
                @foreach ($applicantTypes as $applicantType)
                    <option value="{{ $applicantType->id }}" {{ $applicantType->name == 'Any' ? 'selected' : '' }}>
                        {{ $applicantType->name }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback">Please select a applicant type</div>
        </div>                                                       
    </div>

    @if($vacancyID && $vacancy)
        <div class="col-md-12">
            <div class="live-preview">
                <div class="d-grid gap-2">
                    <p class="lead text-muted lh-base mb-4 text-center" id="openPositions">
                        {{ optional($vacancy)->open_positions ?? 0 }} open {{ optional($vacancy)->open_positions == 1 ? 'position' : 'positions' }} available.
                    </p>
                </div>
            </div>
        </div>
        <!--end col-->
    @endif
</div>

<!-------------------------------------------------------------------------------------
    Filters
-------------------------------------------------------------------------------------->

<div class="row g-4 mb-4">
    <div class="col-md-12">
        <label class="form-label">
            Filters
        </label>
        <div class="card mb-0">
            <div class="card-body">
                <button type="button" class="btn btn-light btn-label rounded-pill d-none" data-bs-toggle="modal" data-bs-target="#mapModal">
                    <i class="ri-map-pin-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                    Location
                </button>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill d-none" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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

                <button type="button" class="btn btn-light btn-label rounded-pill filter-button d-none" data-bs-filter="citizen;Yes">
                    <i class="ri-shield-user-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                    Citizen
                </button>

                <button type="button" class="btn btn-light btn-label rounded-pill filter-button d-none" data-bs-filter="foreign_national;Yes">
                    <i class="ri-map-pin-user-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                    Foreign National
                </button>

                <div class="btn-group d-none" role="group">
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
                        Qualifications
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
                        <i class="ri-book-read-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        Experience
                    </button>
                    <ul class="dropdown-menu">
                        @foreach ($durations as $duration)
                            <a class="dropdown-item filter-button" data-bs-filter="duration_id;{{ $duration->id }}">
                                {{ $duration->name }}
                            </a>
                        @endforeach
                    </ul>
                </div>

                <button type="button" class="btn btn-light btn-label rounded-pill filter-button" data-bs-filter="disability;Yes">
                    <i class="ri-wheelchair-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                    Disability
                </button>

                <div class="live-preview mt-4">
                    <div class="d-flex flex-wrap gap-2 align-items-center" id="filterBadges"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-------------------------------------------------------------------------------------
    Verification Checks
-------------------------------------------------------------------------------------->

<div class="row g-4 mb-4 d-none">
    <div class="col-md-12">
        <label class="form-label">
            Verification Checks
        </label>
        <div class="card mb-0">
            <div class="card-body">
                @foreach ($checks as $check)  
                    <button type="button" class="btn btn-light btn-label rounded-pill check-button" data-bs-check="{{ $check->name }};{{ $check->id }}">
                        <i class="{{ $check->icon }} label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        {{ $check->name }}
                    </button>
                @endforeach

                <div class="live-preview mt-4">
                    <div class="d-flex flex-wrap gap-2 align-items-center" id="checkBadges"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-------------------------------------------------------------------------------------
    Generate Shortlist
-------------------------------------------------------------------------------------->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="live-preview">
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" type="button" id="{{ $vacancyID && $vacancy->open_positions == 0 ? 'vacancyFilled-btn' : 'generate-btn' }}">
                            {{ $vacancyID && $vacancy->open_positions == 0 ? 'Vacancy Filled!' : 'Generate Shortlist' }}
                        </button>
                    </div>
                </div>
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div>
    <!--end col-->
</div>

<div class="row g-4 mb-4 d-none">
    <div class="col-md-6">
        <div class="d-md-flex justify-content-sm-start gap-2">
            <div class="search-box ms-md-2 flex-shrink-0 flex-grow-1 mb-3 mb-md-0">
                <input type="text" class="form-control" id="searchApplicant" autocomplete="off" placeholder="Search for applicant...">
                <i class="ri-search-line search-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-------------------------------------------------------------------------------------
    Shortlisted Applicants
-------------------------------------------------------------------------------------->

<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-auto">
                <input class="form-check-input" type="checkbox" id="checkAll" value="option">
            </div>
            <div class="col-md-3">
                <h5 class="mb-0">
                    Shortlisted Applicants
                </h5>                
            </div>
            <!--end col-->
            @if ($vacancyID && $vacancy->open_positions > 0)
                <div class="col-md-auto ms-auto" id="colButtons">
                    <div class="d-flex hstack gap-2 flex-wrap">
                        <!-- Interview Button with Tooltip -->
                        <button class="btn btn-secondary" id="interviewBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="Schedule interview with selected applicants">
                            <i class="ri-calendar-todo-fill align-bottom me-1"></i> 
                            Interview
                        </button>
                    
                        <!-- Fill Vacancy Button with Tooltip -->
                        <button class="btn btn-success" id="vacancyBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="Appoint selected applicants">
                            <i class="ri-open-arm-fill align-bottom me-1"></i> 
                            Fill Vacancy
                        </button>

                        <!-- Refresh Button with Tooltip -->
                        <button class="btn btn-info" id="refreshBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh page" onclick="location.reload();">
                            <i class="ri-refresh-line align-bottom"></i>
                        </button>
                    </div>                
                </div>
                <!--end col-->
            @endif
        </div>
        <!--end row-->
    </div>
</div>

<div class="row gy-2 mb-2" id="candidate-list"></div>
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

<!-------------------------------------------------------------------------------------
    Modals
-------------------------------------------------------------------------------------->

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
                <span id="rangeValue" class="mb-3">Selected Range: {{ $maxDistanceFromStore }}km</span>

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

<!--  Interview Modal -->
<div class="modal fade" id="interviewModal" tabindex="-1" role="dialog" aria-labelledby="interviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-3 bg-secondary-subtle">
                <h5 class="modal-title" id="modal-title">
                    Schedule Interview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formInterview" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="UserID" name="user_id" value="{{ Crypt::encryptstring(Auth::id()) }}"/>
                    <input type="hidden" id="vacancyID" name="vacancy_id" value="{{ $vacancyID ? Crypt::encryptstring($vacancyID) : '' }}"/>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3" id="applicantsIntreview">
                                <label class="form-label" for="applicants">Applicants</label>
                                <select class="form-control" id="applicants" name="applicants[]" multiple required></select>
                                <div class="invalid-feedback">
                                    Please select a applicants
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="date">Interview Date</label>
                                <div class="input-group">
                                    <input type="text" class="form-control flatpickr-input active" id="date" name="date" placeholder="Select date" value="{{ date('d M Y') }}" readonly="readonly" required>
                                    <span class="input-group-text"><i class="ri-calendar-event-line"></i></span>
                                    <div class="invalid-feedback">
                                        Please select a date
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                        <div class="col-12" id="event-time">
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="startTime">Start Time</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control flatpickr-input active" id="startTime" name="start_time" readonly="readonly" required>
                                            <span class="input-group-text"><i class="ri-time-line"></i></span>
                                            <div class="invalid-feedback">
                                                Please select a start time
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="endTime">End Time</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control flatpickr-input active" id="endTime" name="end_time" readonly="readonly" required>
                                            <span class="input-group-text"><i class="ri-time-line"></i></span>
                                            <div class="invalid-feedback">
                                                Please select a end time
                                            </div>
                                        </div>                                        
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="location">Location</label>
                                <div>
                                    <input type="text" class="form-control d-block" id="location" name="location" placeholder="Interview location" value="{{ $vacancy ? optional($vacancy->store)->address : '' }}" {{ $user->role_id >= 6 ? 'readonly' : '' }} required>
                                    <div class="invalid-feedback">
                                        Please enter a location
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="notes">Notes</label>
                                <textarea class="form-control d-block" id="notes" name="notes" placeholder="Enter additional notes" rows="3" spellcheck="true" readonly>Please bring your ID and a copy of your CV.</textarea>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->
                    <div class="hstack gap-2 justify-content-end">
                        <button type="submit" class="btn btn-secondary" id="btn-save-event">
                            Send Invite
                        </button>
                    </div>
                </form>
            </div>
        </div> <!-- end modal-content-->
    </div> <!-- end modal dialog-->
</div>

<!-- Vacancy Fill -->
<div class="modal fade zoomIn" id="vacancyModal" tabindex="-1" role="dialog" aria-labelledby="vacancyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" id="vacancy-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/xzalkbkz.json" trigger="loop" style="width:120px;height:120px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4 class="fs-semibold">You are about to appoint these applicants !</h4>
                    <p class="text-muted fs-14 mb-4 pt-1">Send appointment confirmation ?</p>
                    <form id="formVacancy" enctype="multipart/form-data" novalidate>
                        @csrf
                        <div class="mb-3" id="applicantsVacancyDiv">
                            <label class="form-label" for="applicantsVacancy">Applicants</label>
                            <select class="form-control" id="applicantsVacancy" name="applicants_vacancy[]" multiple required></select>
                        </div>

                        <div class="mb-3" id="vacancyFillDiv">
                            <label for="vacancyFill" class="form-label">
                                Vacancy
                            </label>
                            <select class="form-control" id="vacancyFill" name="vacancy_id_visible" data-choices data-choices-search-true {{ $vacancyID ? 'disabled' : 'required' }}>
                                @if($vacancyID)
                                    @foreach ($vacancies as $vacancy)
                                        @if($vacancyID == $vacancy->id)
                                            <option value="{{ Crypt::encryptString($vacancy->id) }}" selected>{{ $vacancy->position->name }}: ({{ $vacancy->store->brand->name }} - {{ $vacancy->store->name }})</option>
                                        @endif
                                    @endforeach
                                @else
                                    <option value="">Select Vacancy</option>
                                    @foreach ($vacancies as $vacancy)
                                        <option value="{{ Crypt::encryptString($vacancy->id) }}" {{ ($vacancyID && $vacancyID == $vacancy->id) ? 'selected' : '' }}>{{ $vacancy->position->name }}: ({{ $vacancy->store->brand->name }} - {{ $vacancy->store->town->name }})</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="invalid-feedback">Please select a vacancy</div>

                            @if($vacancyID)
                                <input type="hidden" name="vacancy_id" value="{{ Crypt::encryptString($vacancyID) }}">
                            @endif
                        </div>

                        <div class="mb-3" id="sapNumberDiv">
                            <label for="sapNumber" class="form-label">
                                SAP Number
                            </label>
                            <select class="form-control" id="sapNumber" name="sap_number" {{ (isset($vacancy) && $vacancy->availableSapNumbers->count() === 1) ? 'disabled' : 'required' }}>
                                @if(isset($vacancy) && $vacancy->availableSapNumbers->isNotEmpty())
                                    @if($vacancy->availableSapNumbers->count() === 1)
                                        <!-- Automatically select the single SAP Number -->
                                        @foreach ($vacancy->availableSapNumbers as $sapNumber)
                                            <option value="{{ Crypt::encryptString($sapNumber->id) }}" selected>{{ $sapNumber->sap_number }}</option>
                                        @endforeach
                                    @else
                                        <option value="">Select SAP Number</option>
                                        @foreach ($vacancy->availableSapNumbers as $sapNumber)
                                            <option value="{{ Crypt::encryptString($sapNumber->id) }}">{{ $sapNumber->sap_number }}</option>
                                        @endforeach
                                    @endif
                                @else
                                    <option value="">Select SAP Number</option>
                                @endif
                            </select>
                            <div class="invalid-feedback">Please select a SAP Number</div>

                            @if(isset($vacancy) && $vacancy->availableSapNumbers->count() === 1)
                                <input type="hidden" name="sap_number" value="{{ Crypt::encryptString($vacancy->availableSapNumbers[0]->id) }}">
                            @endif
                        </div>

                        <div class="hstack gap-2 justify-content-center remove">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="vacancy-close">
                                <i class="ri-close-line me-1 align-middle"></i>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary vacancy-fill" id="vacancy-fill">
                                Fill Vacancy !
                            </button>
                            <div class="spinner-border text-primary d-none" role="status" id="loading-vacancy">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </form>                    
                </div>
            </div>
        </div>
    </div>
</div>
<!--end vacancy modal -->
@endsection
@section('script')
<script>
    var shortlistedApplicants = @json($shortlistedApplicants);
    var vacancyID = @json(Crypt::encryptString($vacancyID));
    var minShortlistNumber = @json($minShortlistNumber);
    var maxShortlistNumber = @json($maxShortlistNumber);
    var coordinates = @json($vacancy ? optional($vacancy->store)->coordinates : '');
    var maxDistanceFromStore = {{ $maxDistanceFromStore }};
</script>
<script src="{{ URL::asset('build/libs/@simonwep/pickr/pickr.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/nouislider/nouislider.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/wnumb/wNumb.min.js') }}"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googlemaps.key') }}&callback=initMap"></script>
<!-- job-candidate-grid js -->
<script src="{{ URL::asset('build/js/pages/shortlist.init.js') }}?v={{ filemtime(public_path('build/js/pages/shortlist.init.js')) }}"></script>
<script src="{{ URL::asset('build/js/pages/applicant-save.init.js') }}"></script>

<!-- App js -->
<script src="{{URL::asset('build/js/app.js')}}"></script>
@endsection
