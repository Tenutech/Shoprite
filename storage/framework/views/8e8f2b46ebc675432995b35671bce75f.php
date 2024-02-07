
<?php $__env->startSection('title'); ?> Applicant List <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="<?php echo e(URL::asset('build/libs/@simonwep/pickr/themes/classic.min.css')); ?>" />
<link href="<?php echo e(URL::asset('build/libs/nouislider/nouislider.min.css')); ?>" rel="stylesheet">
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
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startComponent('components.breadcrumb'); ?>
<?php $__env->slot('li_1'); ?> Pages <?php $__env->endSlot(); ?>
<?php $__env->slot('title'); ?> Shortlist <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="mb-3" id="applicantTypeChoice">
            <label for="vacancy" class="form-label">
                Vacancy
            </label>
            <select class="form-control" id="vacancy" name="vacancy_id" data-choices data-choices-search-true required>
                <option value="">Select Vacancy</option>
                <?php $__currentLoopData = $vacancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($vacancy->id); ?>" <?php echo e(($vacancyID && $vacancyID == $vacancy->id) ? 'selected' : ''); ?>><?php echo e($vacancy->position->name); ?>: (<?php echo e($vacancy->store->brand->name); ?> - <?php echo e($vacancy->store->town->name); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <div class="invalid-feedback">Please select a vacancy</div>
        </div>                                                       
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="number" class="form-label">
                Shortlist Number
            </label>
            <input type="number" class="form-control" id="number" name="number" placeholder="Enter number of applicants" value="<?php echo e(($vacancyID && $shortlistedApplicants) ? count($shortlistedApplicants) : 1); ?>" min="1" required />
            <div class="invalid-feedback">
                Please enter a number
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
                    <option value="1">Talent Pool/Candidates</option>
                    <option value="2">Applicants</option>
                    <option value="3">Any</option>
            </select>
            <div class="invalid-feedback">Please select shortlist type</div>
        </div>                                                       
    </div>

    <div class="col-md-6">
        <div class="mb-3" id="applicantTypeChoice">
            <label for="applicantType" class="form-label">
                Applicant Type
            </label>
            <select class="form-control" id="applicantType" name="applicant_type_id" data-choices data-choices-search-true required>
                <option value="">Select Type</option>
                <?php $__currentLoopData = $applicantTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $applicantType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($applicantType->id); ?>"><?php echo e($applicantType->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <div class="invalid-feedback">Please select a applicant type</div>
        </div>                                                       
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-12">
        <label class="form-label">
            Filters
        </label>
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
                            <?php $__currentLoopData = $towns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $town): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="town_id;<?php echo e($town->id); ?>"><?php echo e($town->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ri-men-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        Gender
                    </button>
                    <ul class="dropdown-menu">
                        <?php $__currentLoopData = $genders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gender): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="dropdown-item filter-button" data-bs-filter="gender_id;<?php echo e($gender->id); ?>">
                                <?php echo e($gender->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ri-user-3-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        Race
                    </button>
                    <ul class="dropdown-menu">
                        <?php $__currentLoopData = $races; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $race): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="dropdown-item filter-button" data-bs-filter="race_id;<?php echo e($race->id); ?>">
                                <?php echo e($race->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="dropdown-item filter-button" data-bs-filter="position_id;<?php echo e($position->id); ?>">
                                <?php echo e($position->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ri-book-read-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        Education
                    </button>
                    <ul class="dropdown-menu">
                        <?php $__currentLoopData = $educations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $education): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="dropdown-item filter-button" data-bs-filter="education_id;<?php echo e($education->id); ?>">
                                <?php echo e($education->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light btn-label rounded-pill" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ri-wheelchair-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        Disability
                    </button>
                    <ul class="dropdown-menu">
                        <?php $__currentLoopData = $disabilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disability): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="dropdown-item filter-button" data-bs-filter="disability_id;<?php echo e($disability->id); ?>">
                                <?php echo e($disability->name); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

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

<div class="row g-4 mb-4">
    <div class="col-md-12">
        <label class="form-label">
            Verification Checks
        </label>
        <div class="card mb-0">
            <div class="card-body">
                <?php $__currentLoopData = $checks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>  
                    <button type="button" class="btn btn-light btn-label rounded-pill check-button" data-bs-check="<?php echo e($check->name); ?>;<?php echo e($check->id); ?>">
                        <i class="<?php echo e($check->icon); ?> label-icon align-middle rounded-pill fs-16 me-2"></i> 
                        <?php echo e($check->name); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="live-preview mt-4">
                    <div class="d-flex flex-wrap gap-2 align-items-center" id="checkBadges"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="live-preview">
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" type="button" id="generate-btn">
                            Generate Shortlist
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
            <div class="col-md-auto ms-auto">
                <div class="d-flex hastck gap-2 flex-wrap">
                    <button class="btn btn-secondary" id="interviewBtn">
                        <i class="ri-calendar-todo-fill align-bottom me-1"></i> 
                        Interview
                    </button>
                    <button class="btn btn-danger" id="contractBtn">
                        <i class="ri-edit-2-fill align-bottom me-1"></i> 
                        Contract
                    </button>
                    <button class="btn btn-success" id="vacancyBtn">
                        <i class="ri-open-arm-fill align-bottom me-1"></i> 
                        Fill Vacancy
                    </button>
                </div>
            </div>
            <!--end col-->
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
                <form name="interviwForm" id="formInterview" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="UserID" name="user_id" value="<?php echo e(Crypt::encryptstring(Auth::id())); ?>"/>
                    <input type="hidden" id="vacancyID" name="vacancy_id" value="<?php echo e($vacancyID ? $vacancyID : ''); ?>"/>
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
                                    <input type="text" class="form-control flatpickr-input active" id="date" name="date" placeholder="Select date" data-provider="flatpickr" data-date-format="d M, Y"  value="<?php echo e(date('d M Y')); ?>" readonly="readonly" required>
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
                                            <input type="text" class="form-control flatpickr-input active" data-provider="timepickr" id="startTime" name="start_time" data-time-hrs="true" id="timepicker-24hrs" readonly="readonly" required>
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
                                            <input type="text" class="form-control flatpickr-input active" data-provider="timepickr" id="endTime" name="end_time" data-time-hrs="true" id="timepicker-24hrs" readonly="readonly" required>
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
                                    <input type="text" class="form-control d-block" id="location" name="location" placeholder="Interview location" required>
                                    <div class="invalid-feedback">
                                        Please enter a location
                                    </div>
                                </div>
                            </div>
                        </div><!--end col-->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="notes">Notes</label>
                                <textarea class="form-control d-block" id="notes" name="notes" placeholder="Enter additional notes" rows="3" spellcheck="true"></textarea>
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

<!-- Contract Modal -->
<div class="modal fade zoomIn" id="contractModal" tabindex="-1" role="dialog" aria-labelledby="contractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" id="contract-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/wzwygmng.json" trigger="loop" style="width:120px;height:120px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4 class="fs-semibold">You are about to send a contract to these applicants !</h4>
                    <p class="text-muted fs-14 mb-4 pt-1">Send contract for signing ?</p>
                    <form id="formContract" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3" id="applicantsContractDiv">
                            <label class="form-label" for="applicantsContract">Applicants</label>
                            <select class="form-control" id="applicantsContract" name="applicants_contracts[]" multiple required></select>
                        </div>
                        <div class="mb-3">
                            <input class="form-control" name="contract_file" type="file" multiple="multiple" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                        </div>
                        <div class="hstack gap-2 justify-content-center remove">
                            <button class="btn btn-danger" data-bs-dismiss="modal" id="contract-close">
                                <i class="ri-close-line me-1 align-middle"></i>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary contract-send" id="contract-send">
                                Send Contract !
                            </button>
                            <div class="spinner-border text-primary d-none" role="status" id="loading-contract">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </form>                    
                </div>
            </div>
        </div>
    </div>
</div>
<!--end contract modal -->

<!-- Vacancy Fill -->
<div class="modal fade zoomIn" id="vacancyModal" tabindex="-1" role="dialog" aria-labelledby="contractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" id="vacancy-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/wzwygmng.json" trigger="loop" style="width:120px;height:120px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4 class="fs-semibold">You are about to send a contract to these applicants !</h4>
                    <p class="text-muted fs-14 mb-4 pt-1">Send contract for signing ?</p>
                    <form id="formVacancy" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3" id="applicantsVacancyDiv">
                            <label class="form-label" for="applicantsVacancy">Applicants</label>
                            <select class="form-control" id="applicantsVacancy" name="applicants_vacancy[]" multiple required></select>
                        </div>
                        <div class="mb-3" id="vacancyFillDiv">
                            <label for="vacancyFill" class="form-label">
                                Vacancy
                            </label>
                            <select class="form-control" id="vacancyFill" name="vacancy_id" data-choices data-choices-search-true required>
                                <option value="">Select Vacancy</option>
                                <?php $__currentLoopData = $vacancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e(Crypt::encryptString($vacancy->id)); ?>" <?php echo e(($vacancyID && $vacancyID == $vacancy->id) ? 'selected' : ''); ?>><?php echo e($vacancy->position->name); ?>: (<?php echo e($vacancy->store->brand->name); ?> - <?php echo e($vacancy->store->town->name); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="invalid-feedback">Please select a vacancy</div>
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
<!--end contract modal -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">
    var shortlistedApplicants = <?php echo json_encode($shortlistedApplicants, 15, 512) ?>;
    var vacancyID = <?php echo json_encode($vacancyID, 15, 512) ?>;
</script>
<script src="<?php echo e(URL::asset('build/libs/@simonwep/pickr/pickr.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/nouislider/nouislider.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/wnumb/wNumb.min.js')); ?>"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(config('services.googlemaps.key')); ?>&callback=initMap"></script>
<!-- job-candidate-grid js -->
<script src="<?php echo e(URL::asset('build/js/pages/shortlist.init.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/pages/applicant-save.init.js')); ?>"></script>

<!-- App js -->
<script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Recruitment\resources\views/manager/shortlist.blade.php ENDPATH**/ ?>