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
                        {{ $vacancy ? 'Update' : 'Post' }} Your Vacancy
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
                                <div class="nav flex-column custom-nav nav-pills" role="tablist"
                                    aria-orientation="vertical">
                                    <button class="nav-link active" id="v-pills-position-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-position" type="button" role="tab"
                                        aria-controls="v-pills-position" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Step 1:
                                        </span>
                                        Job Position
                                    </button>
                                    <button class="nav-link" id="v-pills-store-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-store" type="button" role="tab"
                                        aria-controls="v-pills-store" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Step 2:
                                        </span>
                                        Store
                                    </button>
                                    <button class="nav-link" id="v-pills-type-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-type" type="button" role="tab"
                                        aria-controls="v-pills-type" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Step 3:
                                        </span>
                                        Job Type
                                    </button>
                                    <button class="nav-link" id="v-pills-finish-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-finish" type="button" role="tab"
                                        aria-controls="v-pills-finish" aria-selected="false">
                                        <span class="step-title me-2">
                                            <i class="ri-close-circle-fill step-icon me-2"></i> Step 4:
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
                                                                    <option value="{{$position->id}}" {{ ($vacancy && $vacancy->position_id == $position->id) ? 'selected' : '' }}>{{ $position->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">Please select a position</div>
                                                        </div>                                                       
                                                    </div>   
                                                    
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <label for="openPositions" class="form-label">
                                                                Positions Available
                                                            </label>
                                                            <input type="number" class="form-control" id="openPositions" name="open_positions" placeholder="Enter number of positions available" value="{{ $vacancy ? $vacancy->open_positions : '1' }}" min="0" required />
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
                                                    data-nexttab="v-pills-store-tab">
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
                                                                    <option value="{{$store->id}}" {{ ($vacancy && $vacancy->store_id == $store->id) ? 'selected' : '' }}>{{ $store->brand->name }} ({{ $store->town->name }})</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">Please select a store</div>
                                                        </div>                                                        
                                                    </div>                                                    
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
                                                    data-previous="v-pills-store-tab">
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
                                            Finish
                                        -------------------------------------------------------------------------------------->

                                        <div class="tab-pane fade d-flex align-items-center justify-content-center flex-column" id="v-pills-finish" role="tabpanel" aria-labelledby="v-pills-finish-tab">
                                            @if ($vacancy)
                                                <!-- Update -->
                                                <div class="text-center pt-4 pb-2" id="complete">
                                                    <div class="mb-4">
                                                        <lord-icon src="https://cdn.lordicon.com/nocovwne.json" trigger="loop" colors="primary:#0ab39c,secondary:#405189" id="lordicon" style="width:120px;height:120px"></lord-icon>
                                                    </div>
                                                    <h5 id="completeHeading">Would you like to update this vacancy ?</h5>
                                                    <p class="text-muted" id="completeText">
                                                        You are about to update this vacancy with new information.
                                                    </p>
                                                    <button type="button" id="editBtn" class="btn btn-light btn-label waves-effect waves-light rounded-pill" data-previous="v-pills-position-tab">
                                                        <i class="ri-edit-box-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        Edit
                                                    </button>
                                                    <button type="submit" id="updateBtn" class="btn btn-secondary btn-label waves-effect waves-light rounded-pill">
                                                        <i class="ri-check-double-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        Yes, Update !
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
                                                    <h5>Would you like to create this vacancy ?</h5>
                                                    <p class="text-muted">
                                                        You will receive an email confirmation with details of the new vacancy.
                                                    </p>
                                                    <button type="button" id="cancelBtn" class="btn btn-light btn-label waves-effect waves-light rounded-pill" data-previous="v-pills-position-tab">
                                                        <i class="ri-close-circle-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        No, Cancel
                                                    </button>
                                                    <button type="submit" id="submitBtn" class="btn btn-secondary btn-label waves-effect waves-light rounded-pill">
                                                        <i class="ri-check-double-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        Yes, Create !
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
                                                    <h5 id="completeHeading">New Vacancy Created !</h5>
                                                    <p class="text-muted" id="completeText">
                                                        You will receive an confirmation email with details of the vacancy opportunity.
                                                    </p>
                                                    <button type="button" id="editBtn" class="btn btn-light btn-label waves-effect waves-light rounded-pill" data-previous="v-pills-position-tab">
                                                        <i class="ri-edit-box-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                                        Edit
                                                    </button>
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
