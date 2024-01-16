
<?php $__env->startSection('title'); ?> <?php echo app('translator')->get('translation.dashboards'); ?> <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<link href="<?php echo e(URL::asset('build/libs/jsvectormap/css/jsvectormap.min.css')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.css')); ?>" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

<div class="row">
    <div class="col">

        <div class="h-100">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">
                                Hello, <?php echo e(Auth::user()->firstname); ?>!
                            </h4>
                            <p class="text-muted mb-0">
                                Here's what's happening with your store today.
                            </p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-center">
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <input type="text" class="form-control border-0 dash-filter-picker shadow" data-provider="flatpickr" data-range-date="true" data-date-format="d M, Y" data-deafult-date="01 Jan 2022 to 31 Jan 2022">
                                            <div class="input-group-text bg-primary border-primary text-white">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-soft-info btn-icon waves-effect waves-light layout-rightside-btn"><i class="ri-pulse-line"></i></button>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                    </div><!-- end card header -->
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <!-------------------------------------------------------------------------------------
                Information
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Applications
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-success mb-0"> 
                                        <i class="ri-arrow-up-line align-middle"></i> 
                                        16.24 % 
                                    </span> vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-success" , "--vz-transparent"]' dir="ltr" id="applications_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Interviewed
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-success mb-0"> 
                                        <i class="ri-arrow-up-line align-middle"></i> 
                                        34.24 % 
                                    </span> vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-success" , "--vz-transparent"]' dir="ltr" id="interviewed_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Hired
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-success mb-0"> 
                                        <i class="ri-arrow-up-line align-middle"></i> 
                                        6.67 % 
                                    </span> 
                                    vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-success" , "--vz-transparent"]' dir="ltr" id="hired_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Rejected
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-danger mb-0"> 
                                        <i class="ri-arrow-down-line align-middle"></i> 
                                        3.24 % 
                                    </span> vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-danger", "--vz-transparent"]' dir="ltr" id="rejected_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
            </div> <!-- end row-->

            <!-------------------------------------------------------------------------------------
                Vacancies
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card" id="vacanciesList">
                        <div class="card-header border-0">
        
                            <div class="row g-4 align-items-center">
                                <div class="col-sm-3">
                                    <div class="search-box">
                                        <input type="text" class="form-control search"
                                            placeholder="Search for...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-sm-auto ms-auto">
                                    <div class="hstack gap-2">
                                        <button class="btn btn-soft-danger" id="remove-actions" onClick="deleteMultiple()">
                                            <i class="ri-delete-bin-2-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-info d-none" data-bs-toggle="offcanvas" href="#offcanvasExample">
                                            <i class="ri-filter-3-line align-bottom me-1"></i> 
                                            Fliters
                                        </button>
                                        <a href="<?php echo e(route('vacancy.index')); ?>" type="button" class="btn btn-success add-btn">
                                            <i class="ri-add-line align-bottom me-1"></i> 
                                            Add Vacancy
                                        </a>
                                        <span class="dropdown d-none">
                                            <button class="btn btn-soft-info btn-icon fs-14" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-settings-4-line"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                <li>
                                                    <a class="dropdown-item" href="#">Copy</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#">Move to pipline</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#">Add to exceptions</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#">Switch to common form view</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#">Reset form view to default</a>
                                                </li>
                                            </ul>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div>
                                <div class="table-responsive table-card">
                                    <table class="table align-middle" id="vacanciesTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                                    </div>
                                                </th>
                                                <th class="sort d-none" data-sort="id" scope="col">ID</th>
                                                <th class="sort" data-sort="name">Position</th>
                                                <th class="sort" data-sort="type">Type</th>
                                                <th class="sort" data-sort="open">Open</th>
                                                <th class="sort" data-sort="filled">Filled</th>
                                                <th class="sort" data-sort="applicants">Applicants</th>
                                                <th class="sort" data-sort="location">Location</th>                                                                                               
                                                <th class="sort" data-sort="tags">Tags</th>
                                                <th class="sort" data-sort="date">Posted</th>
                                                <th class="sort" data-sort="status">Status</th> 
                                                <th class="sort" data-sort="action">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            <?php if($vacancies && count($vacancies) > 0): ?>
                                                <?php $__currentLoopData = $vacancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <!-- Accordion Toggle Row -->
                                                    <tr>
                                                        <th scope="row">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="chk_child" value="<?php echo e(Crypt::encryptstring($vacancy->id)); ?>">
                                                            </div>
                                                        </th>
                                                        <td class="id" style="display:none;"><?php echo e(Crypt::encryptstring($vacancy->id)); ?></td>
                                                        <td class="name">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0">
                                                                    <div class="avatar-sm">
                                                                        <div class="avatar-title bg-light rounded">
                                                                            <i class="<?php echo e($vacancy->position->icon); ?> text-<?php echo e($vacancy->position->color); ?> fs-4"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-2 name">
                                                                    <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)])); ?>" class="fw-medium link-primary">
                                                                        <?php echo e($vacancy->position->name); ?>

                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="type">
                                                            <span class="badge bg-<?php echo e($vacancy->type->color); ?>-subtle text-<?php echo e($vacancy->type->color); ?>">
                                                                <?php echo e($vacancy->type->name); ?>

                                                            </span>
                                                        </td>
                                                        <td class="open"><?php echo e($vacancy->open_positions); ?></td>
                                                        <td class="filled"><?php echo e($vacancy->filled_positions); ?></td>
                                                        <td class="applicants"><?php echo e($vacancy->applicants->count()); ?></td>                                                      
                                                        <td class="location"><?php echo e($vacancy->store->town->name); ?></td>                                                    
                                                        <td class="tags">
                                                            <?php if($vacancy->position->tags): ?>
                                                                <span class="badge bg-<?php echo e($vacancy->position->tags[0]->color); ?>-subtle text-<?php echo e($vacancy->position->tags[0]->color); ?>"><?php echo e($vacancy->position->tags[0]->name); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="date"><?php echo e(date('d M Y', strtotime($vacancy->created_at))); ?></td>
                                                        <td class="status">
                                                            <span class="badge bg-<?php echo e($vacancy->status->color); ?>-subtle text-<?php echo e($vacancy->status->color); ?>">
                                                                <?php echo e($vacancy->status->name); ?>

                                                            </span>
                                                        </td>
                                                        <td class="action">
                                                            <ul class="list-inline hstack gap-2 mb-0">
                                                                <li class="list-inline-item">
                                                                    <a data-bs-toggle="collapse" data-bs-target="#accordion-<?php echo e($vacancy->id); ?>" aria-expanded="false" aria-controls="accordion-<?php echo e($vacancy->id); ?>">
                                                                        <i class="ri-group-fill align-bottom text-muted"></i>
                                                                    </a>
                                                                </li>
                                                                <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="View">
                                                                    <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)])); ?>">
                                                                        <i class="ri-eye-fill align-bottom text-muted"></i>
                                                                    </a>
                                                                </li>
                                                                <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                                    <a  href="<?php echo e(route('vacancy.index', ['id' => Crypt::encryptString($vacancy->id)])); ?>" class="edit-item-btn">
                                                                        <i class="ri-pencil-fill align-bottom text-muted"></i>
                                                                    </a>
                                                                </li>
                                                                <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Delete">
                                                                    <a class="remove-item-btn" data-bs-toggle="modal" href="#vacancyDeleteModal">
                                                                        <i class="ri-delete-bin-fill align-bottom text-muted"></i>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </td>
                                                    </tr>

                                                    <!-- Accordion Content Row -->
                                                    <tr class="accordion-collapse collapse" id="accordion-<?php echo e($vacancy->id); ?>" data-bs-parent="#accordion-<?php echo e($vacancy->id); ?>">
                                                        <td colspan="100%" class="hiddenRow">
                                                            <div class="accordion-body">
                                                                <div class="row gy-2 mb-2">
                                                                    <?php $__currentLoopData = $vacancy->applicants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <div class="col-md-6 col-lg-12">
                                                                            <div class="card mb-0">
                                                                                <div class="card-body">
                                                                                    <div class="d-lg-flex align-items-center">
                                                                                        <div class="flex-shrink-0 col-auto">
                                                                                            <div class="avatar-sm rounded overflow-hidden">
                                                                                                <img src="<?php echo e($user->applicant->avatar); ?>" alt="" class="member-img img-fluid d-block rounded">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="ms-lg-3 my-3 my-lg-0 col-3 text-start">
                                                                                            <a href="<?php echo e(route('applicant-profile.index', ['id' => Crypt::encryptString($user->applicant->id)])); ?>">
                                                                                                <h5 class="fs-16 mb-2">
                                                                                                    <?php echo e($user->applicant->firstname); ?> <?php echo e($user->applicant->lastname); ?>

                                                                                                </h5>
                                                                                            </a>
                                                                                            <p class="text-muted mb-0">
                                                                                                <?php if($user->applicant->position->name == 'Other'): ?>
                                                                                                    <?php echo e($user->applicant->position_specify); ?>

                                                                                                <?php else: ?>
                                                                                                    <?php echo e($user->applicant->position->name); ?>

                                                                                                <?php endif; ?>
                                                                                            </p>
                                                                                        </div>
                                                                                        <div class="d-flex gap-4 mt-0 text-muted mx-auto col-2">
                                                                                            <div>
                                                                                                <i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i>
                                                                                                <?php echo e($user->applicant->town->name); ?> 
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-2">
                                                                                            <i class="ri-briefcase-line text-primary me-1 align-bottom"></i>
                                                                                            <?php if($user->applicant->type->name == 'Other'): ?>
                                                                                                <?php echo e($user->applicant->application_reason_specify); ?>

                                                                                            <?php else: ?>
                                                                                                <?php echo e($user->applicant->type->name); ?>

                                                                                            <?php endif; ?>
                                                                                        </div>
                                                                                        <div class="d-flex flex-wrap gap-2 align-items-center mx-auto my-3 my-lg-0 col-1">
                                                                                            <div class="badge text-bg-success">
                                                                                                <i class="mdi mdi-star me-1"></i>
                                                                                                <?php echo e($user->applicant->score); ?>                                                                                            
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-2 text-end">
                                                                                            <a href="<?php echo e(route('applicant-profile.index', ['id' => Crypt::encryptString($user->applicant->id)])); ?>" class="btn btn-soft-primary">
                                                                                                View Details
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <tr style="vertical-align:top;">
                                                    <th scope="row">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" value="option">
                                                        </div>
                                                    </th>
                                                    <td class="id d-none"></td>
                                                    <td class="name"></td>
                                                    <td class="type"></td>
                                                    <td class="open"></td>
                                                    <td class="filled"></td>
                                                    <td class="applicants"></td>
                                                    <td class="location"></td>
                                                    <td class="tags"></td>
                                                    <td class="date"></td>
                                                    <td class="status"></td>
                                                    <td class="action">
                                                        <ul class="list-inline hstack gap-2 mb-0">
                                                            <li>
                                                                <a>
                                                                    <i class="ri-group-fill align-bottom text-muted"></i>
                                                                </a>
                                                            </li>
                                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="View">
                                                                <a>
                                                                    <i class="ri-eye-fill align-bottom text-muted"></i>
                                                                </a>
                                                            </li>
                                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                                <a class="edit-item-btn">
                                                                    <i class="ri-pencil-fill align-bottom text-muted"></i>
                                                                </a>
                                                            </li>
                                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Delete">
                                                                <a class="remove-item-btn" data-bs-toggle="modal" href="#vacancyDeleteModal">
                                                                    <i class="ri-delete-bin-fill align-bottom text-muted"></i>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </td>
                                                    <td>
                                                        <ul class="list-inline hstack gap-2 mb-0">
                                                            <li class="list-inline-item">
                                                                <div class="dropdown">
                                                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <i class="ri-more-fill align-middle"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                                        <li>
                                                                            <a class="dropdown-item view-item-btn" href="javascript:void(0);">
                                                                                <i class="ri-eye-fill align-bottom me-2 text-muted"></i>
                                                                                View
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item edit-item-btn" href="#usersModal" data-bs-toggle="modal">
                                                                                <i class="ri-pencil-fill align-bottom me-2 text-muted"></i>
                                                                                Edit
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item remove-item-btn" data-bs-toggle="modal" href="#deleteRecordModal">
                                                                                <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i>
                                                                                Delete
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </td>
                                                    <td class="opportunities d-none"></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <div class="noresult" style="display: none">
                                        <div class="text-center">
                                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
                                                trigger="loop" colors="primary:#121331,secondary:#08a88a"
                                                style="width:75px;height:75px">
                                            </lord-icon>
                                            <h5 class="mt-2">Sorry! No Result Found</h5>
                                            <p class="text-muted mb-0">We've searched more than 150+ leads We
                                                did not find any
                                                leads for you search.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <div class="pagination-wrap hstack gap-2">
                                        <a class="page-item pagination-prev disabled" href="#">
                                            Previous
                                        </a>
                                        <ul class="pagination listjs-pagination mb-0"></ul>
                                        <a class="page-item pagination-next" href="#">
                                            Next
                                        </a>
                                    </div>
                                </div>
                            </div>
        
                            <!-- Vacancy delete modal -->
                            <div class="modal fade flip" id="vacancyDeleteModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-body p-5 text-center">
                                            <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:90px;height:90px"></lord-icon>
                                            <div class="mt-4 text-center">
                                                <h4>
                                                    You are about to delete this vacancy ?
                                                </h4>
                                                <p class="text-muted fs-14 mb-4">
                                                    Deleting this vacancy will remove all of the information from the database.
                                                </p>
                                                <div class="hstack gap-2 justify-content-center remove">
                                                    <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="deleteOpportunity-close">
                                                        <i class="ri-close-line me-1 align-middle"></i> 
                                                        Close
                                                    </button>                       
                                                    <button class="btn btn-danger" id="vacancy-delete">
                                                        Yes, Delete It
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end vacancy delete modal -->        
        
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample"
                                aria-labelledby="offcanvasExampleLabel">
                                <div class="offcanvas-header bg-light">
                                    <h5 class="offcanvas-title" id="offcanvasExampleLabel">Leads Fliters</h5>
                                    <button type="button" class="btn-close text-reset"
                                        data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <!--end offcanvas-header-->
                                <form action="" class="d-flex flex-column justify-content-end h-100">
                                    <div class="offcanvas-body">
                                        <div class="mb-4">
                                            <label for="datepicker-range"
                                                class="form-label text-muted text-uppercase fw-semibold mb-3">Date</label>
                                            <input type="date" class="form-control" id="datepicker-range"
                                                data-provider="flatpickr" data-range="true"
                                                placeholder="Select date">
                                        </div>
                                        <div class="mb-4">
                                            <label for="country-select"
                                                class="form-label text-muted text-uppercase fw-semibold mb-3">Country</label>
                                            <select class="form-control" data-choices
                                                data-choices-multiple-remove="true" name="country-select"
                                                id="country-select" multiple>
                                                <option value="">Select country</option>
                                                <option value="Argentina">Argentina</option>
                                                <option value="Belgium">Belgium</option>
                                                <option value="Brazil" selected>Brazil</option>
                                                <option value="Colombia">Colombia</option>
                                                <option value="Denmark">Denmark</option>
                                                <option value="France">France</option>
                                                <option value="Germany">Germany</option>
                                                <option value="Mexico">Mexico</option>
                                                <option value="Russia">Russia</option>
                                                <option value="Spain">Spain</option>
                                                <option value="Syria">Syria</option>
                                                <option value="United Kingdom" selected>United Kingdom</option>
                                                <option value="United States of America">United States of
                                                    America</option>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label for="status-select"
                                                class="form-label text-muted text-uppercase fw-semibold mb-3">Status</label>
                                            <div class="row g-2">
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineCheckbox1" value="option1">
                                                        <label class="form-check-label"
                                                            for="inlineCheckbox1">New Leads</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineCheckbox2" value="option2">
                                                        <label class="form-check-label"
                                                            for="inlineCheckbox2">Old Leads</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineCheckbox3" value="option3">
                                                        <label class="form-check-label"
                                                            for="inlineCheckbox3">Loss Leads</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineCheckbox4" value="option4">
                                                        <label class="form-check-label"
                                                            for="inlineCheckbox4">Follow Up</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label for="leadscore"
                                                class="form-label text-muted text-uppercase fw-semibold mb-3">Lead
                                                Score</label>
                                            <div class="row g-2 align-items-center">
                                                <div class="col-lg">
                                                    <input type="number" class="form-control" id="leadscore"
                                                        placeholder="0">
                                                </div>
                                                <div class="col-lg-auto">
                                                    To
                                                </div>
                                                <div class="col-lg">
                                                    <input type="number" class="form-control" id="leadscore"
                                                        placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="leads-tags"
                                                class="form-label text-muted text-uppercase fw-semibold mb-3">Tags</label>
                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="marketing" value="marketing">
                                                        <label class="form-check-label"
                                                            for="marketing">Marketing</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="management" value="management">
                                                        <label class="form-check-label"
                                                            for="management">Management</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="business" value="business">
                                                        <label class="form-check-label"
                                                            for="business">Business</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="investing" value="investing">
                                                        <label class="form-check-label"
                                                            for="investing">Investing</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="partner" value="partner">
                                                        <label class="form-check-label"
                                                            for="partner">Partner</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="lead" value="lead">
                                                        <label class="form-check-label" for="lead">Leads</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="sale" value="sale">
                                                        <label class="form-check-label" for="sale">Sale</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="owner" value="owner">
                                                        <label class="form-check-label"
                                                            for="owner">Owner</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="banking" value="banking">
                                                        <label class="form-check-label"
                                                            for="banking">Banking</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="banking" value="banking">
                                                        <label class="form-check-label"
                                                            for="banking">Exiting</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="banking" value="banking">
                                                        <label class="form-check-label"
                                                            for="banking">Finance</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="banking" value="banking">
                                                        <label class="form-check-label"
                                                            for="banking">Fashion</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end offcanvas-body-->
                                    <div class="offcanvas-footer border-top p-3 text-center hstack gap-2">
                                        <button class="btn btn-light w-100">Clear Filter</button>
                                        <button type="submit" class="btn btn-success w-100">Filters</button>
                                    </div>
                                    <!--end offcanvas-footer-->
                                </form>
                            </div>
                            <!--end offcanvas-->
        
                        </div>
                    </div>
        
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <!-------------------------------------------------------------------------------------
                Applicants Graph
            -------------------------------------------------------------------------------------->
            
            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    Total Applicants
                                </h5>
                            </div>
                        </div>                        
                        <div class="card-body">
                            <div id="total_applicants" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xl-6">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Applicant Ethnicity</h4>                            
                        </div><!-- end card header -->
                        <div class="card-body">
                            <div id="applicant_race" data-colors='["--vz-warning", "--vz-info", "--vz-primary", "--vz-secondary", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div>
            </div>
            <!--end row-->

            <!-------------------------------------------------------------------------------------
                Vacancies
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body bg-light-subtle">
                            <div class="d-flex align-items-center">
                                <h6 class="card-title mb-0 flex-grow-1 fw-bold">
                                    Search Vacancies
                                </h6>
                                <div class="flex-shrink-0">
                                    <a href="<?php echo e(route('vacancies.index')); ?>" class="btn btn-secondary">
                                        <i class="ri-briefcase-line align-bottom me-1"></i> 
                                        View Vacancies
                                    </a>
                                </div>
                            </div>
            
                            <div class="row mt-3 gy-3">
                                <div class="col-xxl-10 col-md-6">
                                    <div class="search-box">
                                        <input type="text" class="form-control search bg-light border-light" id="searchJob" autocomplete="off" placeholder="Search for jobs or companies...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-md-6">
                                    <div class="input-light">
                                        <select class="form-control" data-choices data-choices-search-false name="choices-single-default" id="idStatus">
                                            <option value="All">All Selected</option>
                                            <option value="Newest" selected>Newest</option>
                                            <option value="Popular">Popular</option>
                                            <option value="Oldest">Oldest</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-12 d-none" id="found-job-alert">
                                    <div class="alert alert-success mb-0 text-center" role="alert">
                                        <strong id="total-result">253</strong> vacancies found
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>                
            
            <div class="row">
                <div class="col-xxl-9">
                    <div id="job-list"></div>
            
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
            
                </div>
                <!--end col-->

                <?php if($vacancies->count() > 0): ?>
                    <div class="col-xxl-3">
                        <div class="card job-list-view-card overflow-hidden" id="job-overview">
                            <img src="<?php echo e(URL::asset($vacancies[0]->position->image)); ?>" alt="" id="cover-img" class="img-fluid background object-fit-cover">
                            <div class="card-body">
                                <div class="avatar-md mt-n5">
                                    <div class="avatar-title bg-light rounded-circle view-opportunity-icon">
                                        <i class="<?php echo e($vacancies[0]->position->icon); ?> text-<?php echo e($vacancies[0]->position->color); ?> fs-1"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h5 class="view-title fw-bold"><?php echo e($vacancies[0]->position->name); ?></h5>
                                    <div class="hstack gap-3 mb-3">
                                        <span class="text-muted">
                                            <i class="ri-building-line me-1 align-bottom"></i> 
                                            <span class="view-companyname"><?php echo e($vacancies[0]->store->brand->name); ?></span>
                                        </span>
                                        <span class="text-muted">
                                            <i class="ri-map-pin-2-line me-1 align-bottom"></i> 
                                            <span class="view-location"><?php echo e($vacancies[0]->store->town->name); ?></span>
                                        </span>
                                    </div>
                                    <p class="text-muted view-desc truncated-text-6-lines"><?php echo $vacancies[0]->position->description; ?></p>
                                    <div class="py-3 border border-dashed border-start-0 border-end-0 mt-4">
                                        <div class="row">
                                            <div class="col-lg-4 col-sm-6">
                                                <div>
                                                    <p class="mb-2 text-uppercase fw-semibold fs-12 text-muted">
                                                        Job Type
                                                    </p>
                                                    <h5 class="fs-14 mb-0 view-type">
                                                        <?php echo e($vacancies[0]->type->name); ?>

                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-sm-6">
                                                <div>
                                                    <p class="mb-2 text-uppercase fw-semibold fs-12 text-muted">
                                                        Post Date
                                                    </p>
                                                    <h5 class="fs-14 mb-0 view-postdate">
                                                        <?php echo e(date("d M", strtotime($vacancies[0]->created_at))); ?>

                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-sm-6">
                                                <div>
                                                    <p class="mb-2 text-uppercase fw-semibold fs-12 text-muted">
                                                        Experience
                                                    </p>
                                                    <h5 class="fs-14 mb-0 view-experience">
                                                        0 - 5 Year
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <h5 class="mb-3">Application Summary</h5>
                
                                    <div>
                                        <div id="vacancy_chart" data-colors='["--vz-success", "--vz-primary", "--vz-danger", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                                    </div>
                                </div>
                
                                <div class="mt-4">
                                    <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString($vacancies[0]->id)])); ?>" type="button" class="btn btn-info w-100">
                                        Apply Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div> <!-- end .h-100-->

    </div> <!-- end col -->

    <!-------------------------------------------------------------------------------------
        Side Bar
    -------------------------------------------------------------------------------------->

    <div class="col-auto layout-rightside-col">
        <div class="overlay"></div>
        <div class="layout-rightside">
            <div class="card h-100 rounded-0">
                <div class="card-body p-0">
                    <div class="p-3">
                        <h6 class="text-muted mb-0 text-uppercase fw-bold fs-13">
                            Recent Activity
                        </h6>
                    </div>
                    <div data-simplebar style="max-height: 410px;" class="p-3 pt-0">
                        <div class="acitivity-timeline acitivity-main">
                            <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $iconClass = "";
                                    $bgClass = "";
                                    $subjectName = "";
                                    $showActivity = true;
                                    
                                    switch($activity->event) {
                                        case 'created':
                                            switch($activity->subject_type) {
                                                case 'App\Models\Vacancy':
                                                    $iconClass = "ri-briefcase-fill";
                                                    $bgClass = "bg-primary-subtle text-primary";
                                                    break;
                                                case 'App\Models\Applicant':
                                                    $iconClass = "ri-profile-fill";
                                                    $bgClass = "bg-success-subtle text-success";
                                                    break;
                                                case 'App\Models\Message':
                                                    $iconClass = "ri-chat-3-line";
                                                    $bgClass = "bg-success-subtle text-success";
                                                    break;
                                                case 'App\Models\Application':
                                                    $iconClass = "ri-user-add-line";
                                                    $bgClass = "bg-secondary-subtle text-secondary";
                                                    break;
                                                case 'App\Models\User':
                                                    $iconClass = "ri-user-line";
                                                    $bgClass = "bg-info-subtle text-info";                                                    
                                                    break;
                                                default:
                                                    $iconClass = "ri-stackshare-line";
                                                    $bgClass = "bg-info-subtle text-info"; 
                                            }  
                                            $subjectName = "Created";                                          
                                            break;
                                        case 'deleted':
                                            $iconClass = "ri-delete-bin-line";
                                            $bgClass = "bg-danger-subtle text-danger";
                                            $subjectName = "Deleted";
                                            break;
                                        case 'updated':
                                            $iconClass = "ri-edit-line";
                                            $bgClass = "bg-warning-subtle text-warning";
                                            $subjectName = "Updated";
                                            break;
                                        case 'accessed':
                                            $iconClass = "ri-eye-line";
                                            $bgClass = "bg-info-subtle text-info"; 
                                            $subjectName = "Viewed";
                                            break;
                                        default:
                                            $showActivity = false;
                                    }
                                ?>

                                <?php if($showActivity): ?>
                                    <div class="acitivity-item d-flex py-2">
                                        <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">                                  
                                            <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                <div class="avatar-title <?php echo e($bgClass); ?> rounded-circle">
                                                    <i class="<?php echo e($iconClass); ?>"></i>
                                                </div>
                                            </div> 
                                        </a>                                   
                                        <div class="flex-grow-1 ms-3">
                                            <?php
                                                $activityAttributes = json_decode($activity->properties, true);
                                            ?>

                                            <!-------------------------------------------------------------------------------------
                                                Created
                                            -------------------------------------------------------------------------------------->

                                            <?php if($activity->event === "created"): ?>                                                
                                                <?php if($activity->subject_type === "App\Models\Vacancy"): ?>
                                                    <?php                                                        
                                                        $vacancy = $activity->subject; // This should be the related Vacancy model with loaded relationships
                                                        $positionName = $vacancy ? optional($vacancy->position)->name : 'N/A';
                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                    ?>
                                                    <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">                                                    
                                                        <h6 class="mb-1 lh-base">                                                
                                                            <?php echo e($subjectName); ?> <?php echo e(strtolower(class_basename($activity->subject_type))); ?>: <span class="text-primary"><?php echo e($positionName); ?></span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity<?php echo e($activity->id); ?>" style="">
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($positionName); ?>

                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($brandName); ?> (<?php echo e($townName); ?>)
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($typeName); ?>

                                                        </p>
                                                    </div>
                                                <?php elseif($activity->subject_type === "App\Models\Applicant"): ?>
                                                    <?php
                                                        $applicantPosition = $activity->subject->position ?? null;
                                                        $applicantPositionName = $applicantPosition ? $applicantPosition->name : 'N/A';
                                                        if ($applicantPositionName === "Other") {
                                                            $applicantPositionName = $activityAttributes['attributes']['position_specify'] ?? 'N/A';
                                                        }
                                                        $firstname = isset($activityAttributes['attributes']['firstname']) ? $activityAttributes['attributes']['firstname'] : 'N/A';
                                                        $lastname = isset($activityAttributes['attributes']['lastname']) ? $activityAttributes['attributes']['lastname'] : 'N/A';
                                                    ?>
                                                    <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">
                                                        <h6 class="mb-1 lh-base">                                                
                                                            Submitted Application: <span class="text-success"><?php echo e($applicantPositionName); ?></span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity<?php echo e($activity->id); ?>">
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($firstname); ?> <?php echo e($lastname); ?>

                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($applicantPositionName); ?>

                                                        </p>
                                                    </div>                                                  
                                                <?php elseif($activity->subject_type === "App\Models\Message"): ?>
                                                    <?php
                                                        $message = isset($activityAttributes['attributes']['message']) ? $activityAttributes['attributes']['message'] : 'N/A';
                                                        $userFrom = $activity->subject->from ?? null;
                                                        $userTo = $activity->subject->to ?? null;
                                                        $userFromName = $userFrom ? $userFrom->firstname . ' ' . $userFrom->lastname : 'N/A';
                                                        $userToName = $userTo ? $userTo->firstname . ' ' . $userTo->lastname : 'N/A';
                                                    ?>

                                                    <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">
                                                        <?php if($activity->causer_id == Auth::id()): ?> 
                                                            <h6 class="mb-1 lh-base">                                                
                                                                Sent <?php echo e(strtolower(class_basename($activity->subject_type))); ?> to: <span class="text-success"><?php echo e($userToName); ?></span>
                                                            </h6>
                                                        <?php else: ?> 
                                                            <h6 class="mb-1 lh-base">                                                
                                                                Received <?php echo e(strtolower(class_basename($activity->subject_type))); ?> from: <span class="text-success"><?php echo e($userFromName); ?></span>
                                                            </h6>
                                                        <?php endif; ?>
                                                    </a>
                                                    <div class="collapse show" id="activity<?php echo e($activity->id); ?>">
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($message); ?>

                                                        </p>
                                                    </div>
                                                <?php elseif($activity->subject_type === "App\Models\Application"): ?>
                                                    <?php
                                                        $vacancy = $activity->subject->vacancy ?? null;
                                                        $positionName = $vacancy ? optional($vacancy->position)->name : 'N/A';
                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                        $applicationUser = $activity->subject->user ?? null;
                                                        $applicationUserName = $applicationUser ? $applicationUser->firstname . ' ' . $applicationUser->lastname : 'N/A';
                                                    ?>
                                                
                                                    <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">
                                                        <?php if($activity->causer_id == Auth::id()): ?> 
                                                            <h6 class="mb-1 lh-base">                                                
                                                                Applied for: <span class="text-secondary"><?php echo e($positionName); ?></span>
                                                            </h6>
                                                        <?php else: ?> 
                                                            <h6 class="mb-1 lh-base">                                                
                                                                Application request from: <span class="text-secondary"><?php echo e($applicationUserName); ?></span>
                                                            </h6>
                                                        <?php endif; ?>
                                                    </a>
                                                    <div class="collapse show" id="activity<?php echo e($activity->id); ?>">
                                                        <?php if($activity->subject): ?>
                                                            <p class="text-muted mb-1">
                                                                <?php echo e($positionName); ?>

                                                            </p>
                                                            <p class="text-muted mb-1">
                                                                <?php echo e($brandName); ?> (<?php echo e($townName); ?>)
                                                            </p>
                                                            <p class="text-muted mb-1">
                                                                <?php echo e($typeName); ?>

                                                            </p>
                                                        <?php endif; ?>
                                                    </div>                                                  
                                                <?php endif; ?>

                                            <!-------------------------------------------------------------------------------------
                                                Updated
                                            -------------------------------------------------------------------------------------->

                                            <?php elseif($activity->event === "updated"): ?>
                                                <?php if($activity->subject_type === "App\Models\Vacancy"): ?>
                                                    <?php                                                        
                                                        $vacancy = $activity->subject; // This should be the related Vacancy model with loaded relationships
                                                        $positionName = $vacancy ? optional($vacancy->position)->name : 'N/A';
                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                    ?>
                                                    <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">                                                    
                                                        <h6 class="mb-1 lh-base">                                                
                                                            <?php echo e($subjectName); ?> <?php echo e(strtolower(class_basename($activity->subject_type))); ?>: <span class="text-warning"><?php echo e($positionName); ?></span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity<?php echo e($activity->id); ?>" style="">
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($positionName); ?>

                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($brandName); ?> (<?php echo e($townName); ?>)
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($typeName); ?>

                                                        </p>
                                                    </div>
                                                <?php elseif($activity->subject_type === "App\Models\Applicant"): ?>
                                                    <?php
                                                        $applicantPosition = $activity->subject->position ?? null;
                                                        $applicantPositionName = $applicantPosition ? $applicantPosition->name : 'N/A';
                                                        if ($applicantPositionName === "Other") {
                                                            $applicantPositionName = $activityAttributes['attributes']['position_specify'] ?? 'N/A';
                                                        }
                                                        $firstname = isset($activityAttributes['attributes']['firstname']) ? $activityAttributes['attributes']['firstname'] : 'N/A';
                                                        $lastname = isset($activityAttributes['attributes']['lastname']) ? $activityAttributes['attributes']['lastname'] : 'N/A';
                                                    ?>
                                                    <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">
                                                        <h6 class="mb-1 lh-base">                                                
                                                            Updated Application: <span class="text-warning"><?php echo e($applicantPositionName); ?></span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity<?php echo e($activity->id); ?>">
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($firstname); ?> <?php echo e($lastname); ?>

                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($applicantPositionName); ?>

                                                        </p>
                                                    </div>
                                                <?php elseif($activity->subject_type === "App\Models\Application"): ?>
                                                    <?php
                                                        $activityAttributes = json_decode($activity->properties, true);
                                                        $newApprovalStatus = $activityAttributes['attributes']['approved'] ?? null;
                                                        $oldApprovalStatus = $activityAttributes['old']['approved'] ?? null;
                                            
                                                        $applicationUser = $activity->subject->user ?? null;
                                                        $vacancyUser = $activity->subject->vacancy->user ?? null;
                                                        $applicationUserName = $applicationUser ? $applicationUser->firstname . ' ' . $applicationUser->lastname : 'N/A';
                                                        $vacancyUserName = $vacancyUser ? $vacancyUser->firstname . ' ' . $vacancyUser->lastname : 'N/A';
                                                    ?>
                                            
                                                    <?php if($newApprovalStatus !== $oldApprovalStatus): ?> 
                                                        <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">
                                                            <?php if($activity->causer_id == Auth::id()): ?> 
                                                                <?php if($newApprovalStatus === "Yes"): ?>
                                                                    <h6 class="mb-1 lh-base">                                                
                                                                        Approved application request from: <span class="text-warning"><?php echo e($applicationUserName); ?></span>
                                                                    </h6>
                                                                <?php else: ?>
                                                                    <h6 class="mb-1 lh-base">                                                
                                                                        Declined application request from: <span class="text-warning"><?php echo e($applicationUserName); ?></span>
                                                                    </h6>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <?php if($newApprovalStatus === "Yes"): ?>
                                                                    <h6 class="mb-1 lh-base">                                                
                                                                        <span class="text-warning"><?php echo e($applicationUserName); ?></span> approved your application request
                                                                    </h6>
                                                                <?php else: ?>
                                                                    <h6 class="mb-1 lh-base">                                                
                                                                        <span class="text-warning"><?php echo e($applicationUserName); ?></span> declined your application request
                                                                    </h6>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </a>

                                                        <div class="collapse show" id="activity<?php echo e($activity->id); ?>">
                                                            <?php if($activity->subject): ?>
                                                                <p class="text-muted mb-1">
                                                                    <?php echo e($activity->subject->vacancy->position->name); ?>

                                                                </p>
                                                                <p class="text-muted mb-1">
                                                                    <?php echo e($activity->subject->vacancy->store->brand->name); ?> (<?php echo e($activity->subject->vacancy->store->town->name); ?>)
                                                                </p>
                                                                <p class="text-muted mb-1">
                                                                    <?php echo e($activity->subject->vacancy->type->name); ?>

                                                                </p>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                            <!-------------------------------------------------------------------------------------
                                                Deleted
                                            -------------------------------------------------------------------------------------->

                                            <?php elseif($activity->event === "deleted"): ?>
                                                <?php if($activity->subject_type === "App\Models\Vacancy"): ?>
                                                    <?php
                                                        // Retrieve the old attributes
                                                        $oldAttributes = $activityAttributes['old'] ?? [];
                                            
                                                        // Find the related models using the IDs from the old attributes
                                                        $position = isset($oldAttributes['position_id']) ? \App\Models\Position::find($oldAttributes['position_id']) : null;
                                                        $store = isset($oldAttributes['store_id']) ? \App\Models\Store::with('brand', 'town')->find($oldAttributes['store_id']) : null;
                                                        $type = isset($oldAttributes['type_id']) ? \App\Models\Type::find($oldAttributes['type_id']) : null;
                                            
                                                        // Get the names or default to 'N/A'
                                                        $positionName = $position ? $position->name : 'N/A';
                                                        $brandName = $store && $store->brand ? $store->brand->name : 'N/A';
                                                        $townName = $store && $store->town ? $store->town->name : 'N/A';
                                                        $typeName = $type ? $type->name : 'N/A';
                                                    ?>
                                                    <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">                                                    
                                                        <h6 class="mb-1 lh-base">                                                
                                                            <?php echo e($subjectName); ?> <?php echo e(strtolower(class_basename($activity->subject_type))); ?>: <span class="text-danger"><?php echo e($positionName); ?></span>
                                                        </h6>
                                                    </a>
                                                    <div class="collapse show" id="activity<?php echo e($activity->id); ?>" style="">
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($brandName); ?> (<?php echo e($townName); ?>)
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($typeName); ?>

                                                        </p>
                                                    </div>                                       
                                                <?php elseif($activity->subject_type === "App\Models\Message"): ?>
                                                    <?php
                                                        $message = isset($activityAttributes['old']['message']) ? $activityAttributes['old']['message'] : 'N/A';
                                                        $userTo = $activity->userForDeletedMessage;
                                                        $userToName = $userTo ? $userTo->firstname . ' ' . $userTo->lastname : 'N/A';
                                                    ?>
                                                
                                                    <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">
                                                        <?php if($activity->causer_id == Auth::id()): ?> 
                                                            <h6 class="mb-1 lh-base">                                                
                                                                <?php echo e($subjectName); ?> <?php echo e(strtolower(class_basename($activity->subject_type))); ?> to: <span class="text-danger"><?php echo e($userToName); ?></span>
                                                            </h6>
                                                        <?php endif; ?>
                                                    </a>
                                                    <div class="collapse show" id="activity<?php echo e($activity->id); ?>">
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($message); ?>

                                                        </p>
                                                    </div> 
                                                <?php endif; ?>

                                            <!-------------------------------------------------------------------------------------
                                                Viewed
                                            -------------------------------------------------------------------------------------->

                                            <?php else: ?>
                                                <?php if($activity->accessedVacancy): ?>
                                                    <?php
                                                        $vacancy = $activity->accessedVacancy;
                                                        $positionName = optional($vacancy->position)->name ?? 'N/A';
                                                        $brandName = optional($vacancy->store->brand)->name ?? 'N/A';
                                                        $townName = optional($vacancy->store->town)->name ?? 'N/A';
                                                        $typeName = optional($vacancy->type)->name ?? 'N/A';
                                                    ?>
                                                    <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">
                                                        <h6 class="mb-1 lh-base"> 
                                                            Viewed vacancy: <span class="text-info"><?php echo e($positionName); ?></span>
                                                        </h6> 
                                                    </a>
                                                    <div class="collapse show" id="activity<?php echo e($activity->id); ?>">
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($positionName); ?>

                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($brandName); ?> (<?php echo e($townName); ?>)
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($typeName); ?>

                                                        </p>
                                                    </div> 
                                                <?php elseif($activity->accessedApplicant): ?>
                                                    <?php
                                                        $applicant = $activity->accessedApplicant;
                                                        $applicantPosition = $applicant->position ?? null;
                                                        $applicantPositionName = $applicantPosition ? $applicantPosition->name : 'N/A';
                                                        if ($applicantPositionName === "Other") {
                                                            $applicantPositionName = $applicant->position_specify ?? 'N/A';
                                                        }
                                                        $firstname = $applicant->firstname ?? 'N/A';
                                                        $lastname = $applicant->lastname ?? 'N/A';
                                                    ?>
                                                    <a data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" role="button" aria-expanded="true" aria-controls="activity<?php echo e($activity->id); ?>">
                                                        <h6 class="mb-1 lh-base"> 
                                                            Viewed applicant: <span class="text-info"><?php echo e($firstname); ?> <?php echo e($lastname); ?></span>
                                                        </h6> 
                                                    </a>
                                                    <div class="collapse show" id="activity<?php echo e($activity->id); ?>">
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($firstname); ?> <?php echo e($lastname); ?>

                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <?php echo e($applicantPositionName); ?>

                                                        </p>
                                                    </div> 
                                                <?php else: ?>
                                                    <h6 class="mb-1 lh-base"> 
                                                        Viewed entity
                                                    </h6>
                                                <?php endif; ?>
                                            <?php endif; ?>                                          
                                            <small class="mb-0 text-muted">
                                                <?php echo e($activity->created_at->format('H:i A - d M Y')); ?>

                                            </small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="p-3 mt-2">
                        <h6 class="text-muted mb-3 text-uppercase fw-bold fs-13">Top 10 Categories
                        </h6>

                        <ol class="ps-3 text-muted">
                            <?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="py-1">
                                    <a class="text-muted">
                                        <div class="row">
                                            <div class="col-10">
                                                <?php echo e($position->name); ?>

                                            </div>
                                            <div class="col-2 text-end">
                                                (<?php echo e($position->users_count); ?>)
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ol>
                    </div>
                    
                    <div class="card sidebar-alert bg-light border-0 text-center mx-4 mb-0 mt-3">
                        <div class="card-body">
                            <img src="<?php echo e(URL::asset('build/images/user-illustarator-1.png')); ?>" width="100px" alt="">
                            <div class="mt-4">
                                <h5>
                                    Tutorial Video
                                </h5>
                                <p class="text-muted lh-base">
                                    Need help? Watch the tutorial video now!
                                </p>
                                <a href="https://www.youtube.com/watch?v=glhWGAV5zJI&t=58s" class="btn btn-primary btn-label rounded-pill" target="_blank">
                                    <i class="ri-video-chat-fill label-icon align-middle rounded-pill fs-16 me-2"></i>
                                    Tutorial
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card sidebar-alert bg-light border-0 text-center mx-4 mb-0 mt-3">
                        <div class="card-body">
                            <img src="<?php echo e(URL::asset('build/images/giftbox.png')); ?>" alt="">
                            <div class="mt-4">
                                <h5>
                                    Invite New User
                                </h5>
                                <p class="text-muted lh-base">
                                    Refer a colleague to Orient Recruitment.</p>
                                <a href="mailto:?subject=Invitation%20to%20Opportunity%20Bridge" class="btn btn-primary btn-label rounded-pill">
                                    <i class="ri-mail-fill label-icon align-middle rounded-pill fs-16 me-2"></i>
                                    Invite Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end card-->
        </div> <!-- end .rightbar-->

    </div> <!-- end col -->
</div>


<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script>
    var applicantData = {
        "Eastern Cape": 85000,
        "Free State": 14250,
        "Gauteng": 62500,
        "KwaZulu-Natal": 12600,
        "Limpopo": 14350,
        "Mpumalanga": 10200,
        "Northern Cape": 3000,
        "North West": 12600,
        "Western Cape": 37500
    }
</script>
<!-- sweet alert -->
<script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
<!-- apexcharts -->
<script src="<?php echo e(URL::asset('build/libs/list.js/list.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/list.pagination.js/list.pagination.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/apexcharts/apexcharts.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/jsvectormap/js/jsvectormap2.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/jsvectormap/maps/south-africa.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.js')); ?>"></script>
<!-- dashboard init -->
<script src="<?php echo e(URL::asset('build/js/pages/manager.init.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Orient\resources\views/manager/home.blade.php ENDPATH**/ ?>