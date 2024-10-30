@extends('layouts.master')
@section('title')
    @lang('translation.wizard')
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/classic.min.css') }}" />
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/monolith.min.css') }}" />
    <link rel="stylesheet" href="{{ URL::asset('build/libs/@simonwep/pickr/themes/nano.min.css') }}" />
    <link rel="stylesheet" href="{{ URL::asset('build/libs/quill/quill.snow.css') }}" />
    <link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
    <style>
        .choices {
            margin-bottom: 0px !important;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            {{ $vacancy ? 'Update' : 'Create' }}
        @endslot
        @slot('title')
            {{ $vacancy ? $vacancy->name : 'New Vacancy' }}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        {{ $vacancy ? 'Update' : 'Post' }} Your Vacancy:
                        {{ $store && $store->brand ? $store->brand->name.' '.$store->name : '' }}
                    </h4>
                </div><!-- end card header -->
                <div class="card-body form-steps">
                    <form class="vertical-navs-step" id="{{ $vacancy ? 'formVacancyUpdate' : 'formVacancy' }}"  enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="id" name="id" value="{{ $vacancy ? Crypt::encryptString($vacancy->id) : '' }}"/>
                        <div class="row gy-5">

                            <!-------------------------------------------------------------------------------------
                                Navigation Links
                            -------------------------------------------------------------------------------------->

                            <div class="col-lg-3">
                                <div class="nav flex-column custom-nav nav-pills" role="tablist" aria-orientation="vertical">
                                    <button class="nav-link active" id="v-pills-position-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-position" type="button" role="tab"
                                        aria-controls="v-pills-position" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Step 1:
                                        </span>
                                        Job Position
                                    </button>
                                    <button class="nav-link" id="v-pills-sap-numbers-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-sap-numbers" type="button" role="tab"
                                        aria-controls="v-pills-sap-numbers" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Step 2:
                                        </span>
                                        SAP Numbers
                                    </button>
                                    <button class="nav-link {{ $user->role_id == 6 ? 'd-none' : '' }}" id="v-pills-store-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-store" type="button" role="tab"
                                        aria-controls="v-pills-store" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Step 3:
                                        </span>
                                        Store
                                    </button>
                                    <button class="nav-link" id="v-pills-type-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-type" type="button" role="tab"
                                        aria-controls="v-pills-type" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Step {{ $user->role_id == 6 ? '3' : '4' }}:
                                        </span>
                                        Job Type
                                    </button>
                                    <!--
                                    <button class="nav-link" id="v-pills-advertisement-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-advertisement" type="button" role="tab"
                                        aria-controls="v-pills-advertisement" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Step 5:
                                        </span>
                                        Advertisement
                                    </button>
                                    -->
                                    <button class="nav-link" id="v-pills-finish-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-finish" type="button" role="tab"
                                        aria-controls="v-pills-finish" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Step {{ $user->role_id == 6 ? '4' : '5' }}:
                                        </span>
                                        Finish
                                    </button>
                                </div>
                                <!-- end nav -->
                            </div> <!-- end col-->

                            <!-------------------------------------------------------------------------------------
                                Navigation Tabs
                            -------------------------------------------------------------------------------------->

                            <div class="col-lg-9">
                                <div class="px-lg-4">
                                    <div class="tab-content">

                                        <!-------------------------------------------------------------------------------------
                                            Position
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade show active" id="v-pills-position" role="tabpanel"
                                            aria-labelledby="v-pills-position-tab">
                                            <div>
                                                <h5>Job Position</h5>
                                                <p class="text-muted">
                                                    Choose the job position that best matches the vacancy you're looking for.
                                                </p>
                                            </div>

                                            <div>
                                                <div class="row gy-3">
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <select class="form-control" id="position" name="position_id" data-choices data-choices-search-true required>
                                                                <option value="">Select Position</option>
                                                                @foreach ($positions as $position)
                                                                    <option value="{{ $position->id }}"
                                                                    {{ ($vacancy && $vacancy->position_id == $position->id) ? 'selected' : '' }}>
                                                                    {{ $position->name }}
                                                                    <span class="text-{{ optional($position->brand)->color ?: 'danger' }}">
                                                                        ({{ optional($position->brand)->name ?: 'N/A' }})
                                                                    </span>
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @if ($positions->isEmpty())
                                                                <!-- Display invalid feedback if positions are empty -->
                                                                <div class="invalid-feedback" style="display:block">
                                                                    You have not been assigned to a specific brand. Please contact your administrator for assistance.
                                                                </div>
                                                            @else
                                                                <div class="invalid-feedback">
                                                                    Please select a position
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <label for="openPositions" class="form-label">
                                                                Positions Available
                                                            </label>
                                                            <input type="number" class="form-control" id="openPositions" name="open_positions" placeholder="Enter number of positions available" value="{{ $vacancy ? $vacancy->open_positions : '1' }}" min="0" max="10" required />
                                                            <div class="invalid-feedback">
                                                                Please enter a number
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button"
                                                    class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                    data-nexttab="v-pills-sap-numbers-tab">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Continue
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            SAP Numbers
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade" id="v-pills-sap-numbers" role="tabpanel"
                                            aria-labelledby="v-pills-sap-numbers-tab">
                                            <div>
                                                <h5>SAP Numbers</h5>
                                                <p class="text-muted">
                                                    Enter the SAP number associated with this job position.
                                                </p>
                                            </div>

                                            <div>
                                                <div id="sapNumbersContainer">
                                                    @if ($vacancy && $vacancy->sapNumbers->count() > 0)
                                                        @foreach ($vacancy->sapNumbers as $index => $sapNumber)
                                                            <div class="mb-3">
                                                                <label for="sapNumber{{ $index + 1 }}" class="form-label">
                                                                    {{ $vacancy->position ? $vacancy->position->name . ' ' . ($index + 1) : 'SAP Number ' . ($index + 1) }}
                                                                </label>
                                                                <input type="text" class="form-control" id="sapNumber{{ $index + 1 }}" name="sap_numbers[]" placeholder="Enter 8-digit SAP Number" value="{{ $sapNumber->sap_number }}" pattern="\d{8}" maxlength="8" required />
                                                                <div class="invalid-feedback">
                                                                    Please enter an 8-digit SAP number.
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="mb-3">
                                                            <label for="sapNumber1" class="form-label">SAP Number 1</label>
                                                            <input type="text" class="form-control" id="sapNumber1" name="sap_numbers[]" placeholder="Enter 8-digit SAP Number" pattern="\d{8}" maxlength="8" required />
                                                            <div class="invalid-feedback">
                                                                Please enter an 8-digit SAP number.
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button" class="btn btn-light btn-label previestab"
                                                    data-previous="v-pills-position-tab">
                                                    <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                    Back
                                                </button>
                                                <button type="button"
                                                    class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                    data-nexttab="{{ $user->role_id == 6 ? 'v-pills-type-tab' : 'v-pills-store-tab' }}">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Continue
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            Store
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade" id="v-pills-store" role="tabpanel"
                                            aria-labelledby="v-pills-store-tab">
                                            <div>
                                                <h5>Store</h5>
                                                <p class="text-muted">
                                                    Choose the store that you are creating this vacancy for.
                                                </p>
                                            </div>

                                            <div>
                                                <div class="row gy-3">
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <select class="form-control" id="store" name="store_id" data-choices data-choices-search-true required>
                                                                <option value="">Select Store</option>
                                                                @foreach ($stores as $store)
                                                                    <option value="{{$store->id}}"
                                                                            {{
                                                                                ($vacancy && $vacancy->store_id == $store->id)
                                                                                ? 'selected'
                                                                                : ((!$vacancy && $user && $user->store_id == $store->id) ? 'selected' : '')
                                                                            }}
                                                                            {{
                                                                                ($user && $user->role_id == 6 && $user->store_id != $store->id) ? 'disabled' : ''
                                                                            }}>
                                                                        {{ $store->brand->name }} ({{ $store->name }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">Please select a store</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button" class="btn btn-light btn-label previestab"
                                                    data-previous="v-pills-sap-numbers-tab">
                                                    <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                    Back
                                                </button>
                                                <button type="button"
                                                    class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                    data-nexttab="v-pills-type-tab">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Continue
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            Type
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade" id="v-pills-type" role="tabpanel"
                                            aria-labelledby="v-pills-type-tab">
                                            <div>
                                                <h5>Job Type</h5>
                                                <p class="text-muted">
                                                    Choose the job type that best matches the vacancy you're looking for.
                                                </p>
                                            </div>

                                            <div>
                                                <div class="row g-3">
                                                    @foreach ($types as $type)
                                                        <div class="col-xl-3 col-md-6 d-flex align-items-stretch">
                                                            <div class="form-check card-radio h-100 w-100">
                                                                <div class="card card-animate card-height-100 shadow-lg d-flex flex-column">
                                                                    <input id="type-{{ $type->id }}" name="type_id" type="radio" class="form-check-input" value="{{ $type->id }}" {{ ($vacancy && $vacancy->type_id == $type->id) ? 'checked' : ($loop->first ? 'checked' : '') }} required />
                                                                    <label class="form-check-label d-flex flex-column h-100" for="type-{{ $type->id }}" style="white-space: normal;">
                                                                        <div class="card-body text-center d-flex flex-column justify-content-between">
                                                                            <div class="mb-4 pb-2">
                                                                                <lord-icon
                                                                                    src="{{ $type->lordicon }}"
                                                                                    trigger="loop"
                                                                                    colors="primary:#121331,secondary:#08a88a"
                                                                                    style="width:100px;height:100px">
                                                                                </lord-icon>
                                                                            </div>
                                                                            <a>
                                                                                <h6 class="fs-15 fw-bold">
                                                                                    {{ $type->name }}
                                                                                </h6>
                                                                            </a>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button" class="btn btn-light btn-label previestab"
                                                    data-previous="{{ $user->role_id == 6 ? 'v-pills-sap-numbers-tab' : 'v-pills-store-tab' }}">
                                                    <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                    Back
                                                </button>
                                                <button type="button"
                                                    class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                    data-nexttab="v-pills-finish-tab">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Continue
                                                </button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            Advertisement
                                        -------------------------------------------------------------------------------------->

                                        <!--
                                        <div class="tab-pane fade" id="v-pills-advertisement" role="tabpanel"
                                            aria-labelledby="v-pills-advertisement-tab">
                                            <div>
                                                <h5>Advertisement</h5>
                                                <p class="text-muted">
                                                    Choose to whom you would like to advertisement this job.
                                                </p>
                                            </div>

                                            <div>
                                                <div class="row g-3">
                                                    <div class="col-xl-3 col-md-6 d-flex align-items-stretch">
                                                        <div class="form-check card-radio h-100 w-100">
                                                            <div class="card card-animate card-height-100 shadow-lg d-flex flex-column">
                                                                <input id="advertisement-1" name="advertisement" type="radio" class="form-check-input" value="Any" {{ (empty($vacancy) || !$vacancy->advertisement || $vacancy->advertisement == 'Any') ? 'checked' : '' }} required />
                                                                <label class="form-check-label d-flex flex-column h-100" for="advertisement-1" style="white-space: normal;">
                                                                    <div class="card-body text-center d-flex flex-column justify-content-between">
                                                                        <div class="mb-4 pb-2">
                                                                            <lord-icon
                                                                                src="https://cdn.lordicon.com/pbbsmkso.json"
                                                                                trigger="loop"
                                                                                colors="primary:#121331,secondary:#08a88a"
                                                                                style="width:100px;height:100px">
                                                                            </lord-icon>
                                                                        </div>
                                                                        <a>
                                                                            <h6 class="fs-15 fw-bold">
                                                                                Any Applicants
                                                                            </h6>
                                                                        </a>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-3 col-md-6 d-flex align-items-stretch">
                                                        <div class="form-check card-radio h-100 w-100">
                                                            <div class="card card-animate card-height-100 shadow-lg d-flex flex-column">
                                                                <input id="advertisement-2" name="advertisement" type="radio" class="form-check-input" value="External" {{ ($vacancy && $vacancy->advertisement == 'External') ? 'checked' : '' }} required />
                                                                <label class="form-check-label d-flex flex-column h-100" for="advertisement-2" style="white-space: normal;">
                                                                    <div class="card-body text-center d-flex flex-column justify-content-between">
                                                                        <div class="mb-4 pb-2">
                                                                            <lord-icon
                                                                                src="https://cdn.lordicon.com/rmjnvgsm.json"
                                                                                trigger="loop"
                                                                                colors="primary:#121331,secondary:#08a88a"
                                                                                style="width:100px;height:100px">
                                                                            </lord-icon>
                                                                        </div>
                                                                        <a>
                                                                            <h6 class="fs-15 fw-bold">
                                                                                External Applicants
                                                                            </h6>
                                                                        </a>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-3 col-md-6 d-flex align-items-stretch">
                                                        <div class="form-check card-radio h-100 w-100">
                                                            <div class="card card-animate card-height-100 shadow-lg d-flex flex-column">
                                                                <input id="advertisement-3" name="advertisement" type="radio" class="form-check-input" value="Internal" {{ ($vacancy && $vacancy->advertisement == 'Internal') ? 'checked' : '' }} required />
                                                                <label class="form-check-label d-flex flex-column h-100" for="advertisement-3" style="white-space: normal;">
                                                                    <div class="card-body text-center d-flex flex-column justify-content-between">
                                                                        <div class="mb-4 pb-2">
                                                                            <lord-icon
                                                                                src="https://cdn.lordicon.com/xzalkbkz.json"
                                                                                trigger="loop"
                                                                                colors="primary:#121331,secondary:#08a88a"
                                                                                style="width:100px;height:100px">
                                                                            </lord-icon>
                                                                        </div>
                                                                        <a>
                                                                            <h6 class="fs-15 fw-bold">
                                                                                Internal Applicants
                                                                            </h6>
                                                                        </a>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button" class="btn btn-light btn-label previestab"
                                                    data-previous="v-pills-type-tab">
                                                    <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                    Back
                                                </button>
                                                <button type="button"
                                                    class="btn btn-secondary btn-label right ms-auto nexttab nexttab"
                                                    data-nexttab="v-pills-finish-tab">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Continue
                                                </button>
                                            </div>
                                        </div>
                                        -->
                                        <!-- end tab pane -->

                                        <!-------------------------------------------------------------------------------------
                                            Finish
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade d-flex align-items-center justify-content-center flex-column" id="v-pills-finish" role="tabpanel" aria-labelledby="v-pills-finish-tab">
                                            @if ($vacancy)
                                                <!-- Update -->
                                                <div class="text-center pt-4 pb-2" id="complete">
                                                    <div class="mb-4">
                                                        <lord-icon src="https://cdn.lordicon.com/nocovwne.json" trigger="loop" colors="primary:#0ab39c,secondary:#405189" id="lordicon" style="width:120px;height:120px"></lord-icon>
                                                    </div>
                                                    <h5 id="completeHeading">Would you like to update this vacancy?</h5>
                                                    <p class="text-muted" id="completeText">
                                                        You are about to update this vacancy with new information.
                                                    </p>
                                                    @if ($user->role_id == 1)
                                                        <button type="button" id="editBtn" class="btn btn-light btn-label waves-effect waves-light rounded-pill" data-previous="v-pills-position-tab">
                                                            <i class="ri-edit-box-line label-icon align-middle rounded-pill fs-16 me-2"></i>
                                                            Edit
                                                        </button>
                                                    @endif
                                                    <button type="submit" id="updateBtn" class="btn btn-secondary btn-label waves-effect waves-light rounded-pill">
                                                        <i class="ri-check-double-line label-icon align-middle rounded-pill fs-16 me-2"></i>
                                                        Yes, Update!
                                                    </button>
                                                    <a type="button" href="{{ route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)]) }}" id="view-vacancy" class="btn btn-primary btn-label waves-effect waves-light rounded-pill">
                                                        <i class="ri-organization-chart label-icon align-middle rounded-pill fs-16 me-2"></i>
                                                        View Vacancy
                                                    </a>
                                                </div>

                                                <!-- Loading -->
                                                <div class="text-center pt-4 pb-2 mt-4 d-none" id="loading">
                                                    <div class="spinner-border text-success mb-4" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Confirm -->
                                                <div class="text-center pt-4 pb-2 {{ $vacancy ? 'd-none' : '' }}" id="confirm">
                                                    <div class="mb-4">
                                                        <lord-icon src="https://cdn.lordicon.com/nocovwne.json" trigger="loop" state="hover-2" colors="primary:#0ab39c,secondary:#405189" style="width:120px;height:120px"></lord-icon>
                                                    </div>
                                                    <h5>Would you like to create this vacancy?</h5>
                                                    <p class="text-muted">
                                                        After creating the vacancy, you can proceed to create your shortlist.
                                                    </p>
                                                    <button type="button" id="cancelBtn" class="btn btn-light btn-label waves-effect waves-light rounded-pill" data-previous="v-pills-position-tab">
                                                        <i class="ri-close-circle-line label-icon align-middle rounded-pill fs-16 me-2"></i>
                                                        No, Cancel
                                                    </button>
                                                    <button type="submit" id="submitBtn" class="btn btn-secondary btn-label waves-effect waves-light rounded-pill">
                                                        <i class="ri-check-double-line label-icon align-middle rounded-pill fs-16 me-2"></i>
                                                        Yes, Create!
                                                    </button>
                                                </div>

                                                <!-- Loading -->
                                                <div class="text-center pt-4 pb-2 d-none" id="loading">
                                                    <div class="spinner-border text-success mb-4" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </div>

                                                <!-- Complete -->
                                                <div class="text-center pt-4 pb-2 d-none" id="complete">
                                                    <div class="mb-4">
                                                        <lord-icon src="https://cdn.lordicon.com/lupuorrc.json" trigger="loop" colors="primary:#0ab39c,secondary:#405189" id="lordicon" style="width:120px;height:120px"></lord-icon>
                                                    </div>
                                                    <h5 id="completeHeading">New Vacancy Created!</h5>
                                                    <p class="text-muted" id="completeText">
                                                        The vacancy has been successfully created. You can now proceed to create your shortlist.
                                                    </p>
                                                    @if ($user->role_id == 1)
                                                        <button type="button" id="editBtn" class="btn btn-light btn-label waves-effect waves-light rounded-pill" data-previous="v-pills-position-tab">
                                                            <i class="ri-edit-box-line label-icon align-middle rounded-pill fs-16 me-2"></i>
                                                            Edit
                                                        </button>
                                                    @endif
                                                    <a type="button" id="view-vacancy" class="btn btn-primary btn-label waves-effect waves-light rounded-pill">
                                                        <i class="ri-organization-chart label-icon align-middle rounded-pill fs-16 me-2"></i>
                                                        View Vacancy
                                                    </a>
                                                </div>
                                            @endif

                                            <!-- Danger Alert -->
                                            <div class="alert alert-danger alert-dismissible fade text-center mt-4" role="alert" id="requiredAlert">
                                                <strong>Some fields are missing!</strong> Please make sure that all the required fields are filled out
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        </div>
                                        <!-- end tab pane -->

                                    </div>
                                    <!-- end tab content -->
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </form>
                </div>
            </div>
            <!-- end -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/@simonwep/pickr/pickr.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/quill/quill.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/vacancy.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
