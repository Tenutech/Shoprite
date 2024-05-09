<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.contacts'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Pages
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Interviews
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>
    <div class="row">
        <div class="col-xxl-12">
            <div class="card" id="interviewList">
                <div class="card-header">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="search-box">
                                <input type="text" class="form-control search" placeholder="Search for interview...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>                        
                        <div class="col-md-auto ms-auto">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted">Display: </span>
                                <select class="form-control mb-0" id="per-page-select" data-choices data-choices-search-false>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="<?php echo e(count($interviews)); ?>">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive table-card mb-3">
                            <table class="table align-middle table-nowrap mb-0" id="interviewTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                            </div>
                                        </th>
                                        <th class="sort d-none" data-sort="id" scope="col">ID</th>
                                        <th class="sort" data-sort="interview" scope="col">Interview</th>
                                        <th class="sort" data-sort="scheduled_date" scope="col">Scheduled Date</th>
                                        <th class="sort" data-sort="location" scope="col">Location</th>
                                        <th class="sort" data-sort="notes" scope="col">Notes</th>
                                        <th class="sort" data-sort="reschedule_date" scope="col">Reschedule Date</th>
                                        <th class="sort" data-sort="status" scope="col">Status</th>                  
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all" style="height:200px;">
                                    <?php if($interviews && count($interviews) > 0): ?>
                                        <?php $__currentLoopData = $interviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $interview): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr style="vertical-align:middle;">
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="chk_child" value="option1">
                                                    </div>
                                                </th>
                                                <td class="id d-none"><?php echo e(Crypt::encryptstring($interview->id)); ?></td>
                                                <td class="interview"><span>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            <?php if($user->id == $interview->interviewer_id): ?>
                                                                <div class="avatar-sm bg-light rounded p-1">
                                                                    <img src="<?php echo e(URL::asset(optional($interview->applicant)->avatar ?? 'images/avatar.jpg')); ?>" alt="" class="img-fluid d-block">
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="avatar-sm bg-<?php echo e(optional(optional($interview->vacancy)->position)->color ?? 'primary'); ?>-subtle rounded p-1">
                                                                    <span class="avatar-title bg-<?php echo e(optional(optional($interview->vacancy)->position)->color ?? 'primary'); ?>-subtle text-<?php echo e(optional(optional($interview->vacancy)->position)->color ?? 'primary'); ?> fs-4">
                                                                        <i class="<?php echo e(optional(optional($interview->vacancy)->position)->icon ?? 'ri-briefcase-line'); ?>"></i>
                                                                    </span>
                                                                </div>
                                                            <?php endif; ?>                                                            
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <?php if($user->id == $interview->interviewer_id): ?>
                                                                <h5 class="fs-14 mb-1">
                                                                    <a href="apps-ecommerce-product-details" class="text-body">
                                                                        <?php echo e(optional($interview->applicant)->firstname ?? 'N/A'); ?> <?php echo e(optional($interview->applicant)->lastname ?? 'N/A'); ?>

                                                                    </a>
                                                                </h5>
                                                                <p class="text-muted mb-0">
                                                                    <?php echo e(optional(optional($interview->vacancy)->position)->name ?? 'N/A'); ?>

                                                                </p>
                                                            <?php else: ?>
                                                                <h5 class="fs-14 mb-1">
                                                                    <a href="apps-ecommerce-product-details" class="text-body">
                                                                        <?php echo e(optional(optional($interview->vacancy)->position)->name ?? 'N/A'); ?>

                                                                    </a>
                                                                </h5>
                                                                <p class="text-muted mb-0">
                                                                    <?php echo e(optional(optional(optional($interview->vacancy)->store)->brand)->name ?? 'N/A'); ?> (<?php echo e(optional(optional(optional($interview->vacancy)->store)->town)->name ?? 'N/A'); ?>)
                                                                </p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </span></td>
                                                <td class="scheduled_date"><span><?php echo e($interview->scheduled_date ? date('d M Y', strtotime($interview->scheduled_date)) : 'Not Scheduled'); ?><small class="text-muted ms-1"><?php echo e($interview->start_time ? date('h:i A', strtotime($interview->start_time)) : 'No Time Set'); ?></small></span></td>
                                                <td class="location"><?php echo e($interview->location); ?></td>
                                                <td class="notes" style="white-space: pre-wrap;"><?php echo e($interview->notes); ?></td>
                                                <td class="reschedule_date"><span><?php echo $interview->reschedule_date ? date('d M Y', strtotime($interview->reschedule_date)) . '<small class="text-muted ms-1">' . date('h:i A', strtotime($interview->reschedule_date)) . '</small>' : ''; ?></span></td>
                                                <?php
                                                    switch($interview->status) {
                                                        case 'Scheduled':
                                                            $color = 'bg-warning-subtle text-warning';
                                                            break;
                                                        case 'Confirmed':
                                                            $color = 'bg-success-subtle text-success';
                                                            break;
                                                        case 'Declined':
                                                            $color = 'bg-danger-subtle text-danger';
                                                            break;
                                                        case 'Reschedule':
                                                            $color = 'bg-info-subtle text-info';
                                                            break;
                                                        case 'Completed':
                                                            $color = 'bg-success-subtle text-success';
                                                            break;
                                                        case 'Cancelled':
                                                            $color = 'bg-dark-subtle text-dark';
                                                            break;
                                                        case 'No Show':
                                                            $color = 'bg-danger-subtle text-danger';
                                                            break;
                                                        default:
                                                            $color = 'bg-secondary-primary text-primary';
                                                    }
                                                ?>
                                                <td class="status"><span class="badge <?php echo e($color); ?> text-uppercase"><?php echo e($interview->status); ?></span></td>
                                                <td>
                                                    <ul class="list-inline hstack gap-2 mb-0">
                                                        <li class="list-inline-item">
                                                            <div class="dropdown">
                                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="ri-more-fill align-middle"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li>
                                                                        <a class="dropdown-item confirm-item-btn" data-bs-toggle="modal" href="#interviewConfirmModal">
                                                                            <i class="ri-checkbox-circle-fill align-bottom me-2 text-success"></i>
                                                                            Confirm
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item decline-item-btn" data-bs-toggle="modal" href="#interviewDeclineModal">
                                                                            <i class="ri-close-circle-fill align-bottom me-2 text-danger"></i>
                                                                            Decline
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item reschedule-item-btn" data-bs-toggle="modal" href="#interviewRescheduleModal">
                                                                            <i class="ri-calendar-event-fill align-bottom me-2 text-info"></i>
                                                                            Reschedule
                                                                        </a>
                                                                    </li>
                                                                    <?php if($user->role_id < 3): ?>
                                                                        <li>
                                                                            <a class="dropdown-item complete-item-btn" data-bs-toggle="modal" href="#interviewCompleteModal">
                                                                                <i class="ri-calendar-check-fill align-bottom me-2 text-success"></i>
                                                                                Complete
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item cancel-item-btn" data-bs-toggle="modal" href="#interviewCancelModal">
                                                                                <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i>
                                                                                Cancel
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item noShow-item-btn" data-bs-toggle="modal" href="#interviewNoShowModal">
                                                                                <i class="ri-user-unfollow-fill align-bottom me-2 text-danger"></i>
                                                                                No Show
                                                                            </a>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <tr style="vertical-align:top;">
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="chk_child" value="option1">
                                                </div>
                                            </th>
                                            <td class="id d-none"></td>
                                            <td class="interview"></td>
                                            <td class="scheduled_date"></td>
                                            <td class="location"></td>
                                            <td class="notes"></td>
                                            <td class="reschedule_date"></td>
                                            <td class="status"></td>
                                            <td>
                                                <ul class="list-inline hstack gap-2 mb-0">
                                                    <li class="list-inline-item">
                                                        <div class="dropdown">
                                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="ri-more-fill align-middle"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a class="dropdown-item confirm-item-btn" data-bs-toggle="modal" href="#interviewConfirmModal">
                                                                        <i class="ri-checkbox-circle-fill align-bottom me-2 text-success"></i>
                                                                        Confirm
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item decline-item-btn" data-bs-toggle="modal" href="#interviewDeclineModal">
                                                                        <i class="ri-close-circle-fill align-bottom me-2 text-danger"></i>
                                                                        Decline
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item reschedule-item-btn" data-bs-toggle="modal" href="#interviewRescheduleModal">
                                                                        <i class="ri-calendar-event-fill align-bottom me-2 text-info"></i>
                                                                        Reschedule
                                                                    </a>
                                                                </li>
                                                                <?php if($user->role_id < 3): ?>
                                                                    <li>
                                                                        <a class="dropdown-item complete-item-btn" data-bs-toggle="modal" href="#interviewCompleteModal">
                                                                            <i class="ri-calendar-check-fill align-bottom me-2 text-success"></i>
                                                                            Complete
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item cancel-item-btn" data-bs-toggle="modal" href="#interviewCancelModal">
                                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i>
                                                                            Cancel
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item noShow-item-btn" data-bs-toggle="modal" href="#interviewNoShowModal">
                                                                            <i class="ri-user-unfollow-fill align-bottom me-2 text-danger"></i>
                                                                            No Show
                                                                        </a>
                                                                    </li>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </td>
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
                                    <h5 class="mt-2">
                                        Sorry! No Result Found
                                    </h5>
                                    <p class="text-muted mb-0">
                                        We've searched all the interviews. We did not find any interviews for you search.
                                    </p>
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

                    <!-- Modal Interview -->
                    <div class="modal fade zoomIn" id="interviewModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                </div>
                                <form id="formInterview" enctype="multipart/form-data">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" id="field-id" name="field_id"/>
                                    <div class="modal-body">
                                        <div class="col-lg-12 mb-3">
                                            <div class="mb-3">
                                                <label for="scoreType" class="form-label">
                                                    Score Type
                                                </label>
                                                <select id="scoreType" name="score_type" class="form-control" required>
                                                    <option value="" selected>Select Message State</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="weight" class="form-label">
                                                    Weight
                                                </label>
                                                <input type="number" class="form-control" id="weight" name="weight" step="0.01" value="0.00" required/>
                                            </div>
    
                                            <div class="mb-3">
                                                <label for="maxValue" class="form-label">
                                                    Max Value
                                                </label>
                                                <input type="number" class="form-control" id="maxValue" name="max_value" value="0.00" step="0.01"/>
                                            </div>
    
                                            <div class="mb-3">
                                                <label for="conditionField" class="form-label">
                                                    Condition Field
                                                </label>
                                                <select id="conditionField" name="condition_field" class="form-control">
                                                    <option value="" selected>Select Condition Field</option>
                                                </select>
                                            </div>
    
                                            <div class="mb-3">
                                                <label for="conditionValue" class="form-label">
                                                    Condition Value
                                                </label>
                                                <input type="text" class="form-control" id="conditionValue" name="condition_value"/>
                                            </div>
    
                                            <div class="mb-3">
                                                <label for="fallbackValue" class="form-label">
                                                    Fallback Value
                                                </label>
                                                <input type="number" class="form-control" id="fallbackValue" name="fallback_value" value="0.00" step="0.01"/>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <div class="modal-footer">                                        
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success" id="add-btn">Add interview</button>
                                            <button type="button" class="btn btn-success" id="edit-btn">Update</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!--end modal-->

                    <!-- Interview Confirm Modal -->
                    <div class="modal fade zoomIn" id="interviewConfirmModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" id="confirmInterview-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                                </div>
                                <div class="modal-body p-5 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/bgebyztw.json" trigger="loop" style="width:90px;height:90px"></lord-icon>
                                    <div class="mt-4 text-center">
                                        <h4 class="fs-semibold">
                                            You are about to confirm this interview ?
                                        </h4>
                                        <p class="text-muted fs-14 mb-4 pt-1">
                                            Confirming this interview will finalize the schedule and notify all relevant parties involved. Please ensure that the date and time are correct before proceeding.
                                        </p>
                                        <div class="hstack gap-2 justify-content-center remove">
                                            <button class="btn btn-light" data-bs-dismiss="modal" id="confirmInterview-close">
                                                <i class="ri-close-line me-1 align-middle"></i>
                                                Close
                                            </button>
                                            <button class="btn btn-success" id="confirm-interview">
                                                Yes, Confirm!!
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end confirm modal -->

                    <!-- Interview Decline Modal -->
                    <div class="modal fade zoomIn" id="interviewDeclineModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" id="declineInterview-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                                </div>
                                <div class="modal-body p-5 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/urmrbzpi.json" trigger="loop" style="width:90px;height:90px"></lord-icon>
                                    <div class="mt-4 text-center">
                                        <h4 class="fs-semibold">
                                            You are about to decline this interview ?
                                        </h4>
                                        <p class="text-muted fs-14 mb-4 pt-1">
                                            Declining this interview will notify all relevant parties that you are unable to attend the scheduled meeting. Are you sure you wish to proceed with declining the interview?
                                        </p>
                                        <div class="hstack gap-2 justify-content-center remove">
                                            <button class="btn btn-light" data-bs-dismiss="modal" id="declineInterview-close">
                                                <i class="ri-close-line me-1 align-middle"></i>
                                                Close
                                            </button>
                                            <button class="btn btn-danger" id="decline-interview">
                                                Yes, Decline!!
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end decline modal -->

                    <!-- Interview Reschedule Modal -->
                    <div class="modal fade zoomIn" id="interviewRescheduleModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" id="rescheduleInterview-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                                </div>
                                <div class="modal-body p-5 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/wzrwaorf.json" trigger="loop" style="width:90px;height:90px"></lord-icon>
                                    <div class="mt-4 text-center">
                                        <h4 class="fs-semibold">
                                            You are about to reschedule this interview ?
                                        </h4>
                                        <p class="text-muted fs-14 mb-2 pt-1">
                                            Please select a new date and time to proceed with rescheduling. This action will notify all relevant parties of the change.
                                        </p>
                                        <input type="datetime-local" class="form-control datetime-input" id="rescheduleTime" name="reschedule_time" />
                                        <span class="invalid-feedback d-none" role="alert"><strong>Please select a date and time.</strong></span>
                                        <div class="hstack gap-2 justify-content-center remove mt-4">
                                            <button class="btn btn-light" data-bs-dismiss="modal" id="rescheduleInterview-close">
                                                <i class="ri-close-line me-1 align-middle"></i>
                                                Close
                                            </button>
                                            <button class="btn btn-info" id="reschedule-interview">
                                                Yes, Reschedule!!
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end reschedule modal -->

                    <!-- Interview Complete Modal -->
                    <div class="modal fade zoomIn" id="interviewCompleteModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" id="completeInterview-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                                </div>
                                <div class="modal-body p-5 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/wzwygmng.json" trigger="loop" style="width:90px;height:90px"></lord-icon>
                                    <div class="mt-4 text-center">
                                        <h4 class="fs-semibold">
                                            You are about to mark this interview as complete ?
                                        </h4>
                                        <p class="text-muted fs-14 mb-4 pt-1">
                                            Confirming this interview as complete will update its status and notify all relevant parties involved. Please ensure all necessary post-interview actions have been taken before proceeding.
                                        </p>                                        
                                        <div class="hstack gap-2 justify-content-center remove">
                                            <button class="btn btn-light" data-bs-dismiss="modal" id="completeInterview-close">
                                                <i class="ri-close-line me-1 align-middle"></i>
                                                Close
                                            </button>
                                            <button class="btn btn-success" id="complete-interview">
                                                Yes, Complete!!
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end complete modal -->

                    <!-- Interview Cancel Modal -->
                    <div class="modal fade zoomIn" id="interviewCancelModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" id="cancelInterview-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                                </div>
                                <div class="modal-body p-5 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/crithpny.json" trigger="loop" style="width:90px;height:90px"></lord-icon>
                                    <div class="mt-4 text-center">
                                        <h4 class="fs-semibold">
                                            You are about to cancel this interview ?
                                        </h4>
                                        <p class="text-muted fs-14 mb-4 pt-1">
                                            Cancelling this interview will remove it from the schedule and notify all relevant parties of the cancellation. Please confirm if you wish to proceed with this action.
                                        </p>                                      
                                        <div class="hstack gap-2 justify-content-center remove">
                                            <button class="btn btn-light" data-bs-dismiss="modal" id="cancelInterview-close">
                                                <i class="ri-close-line me-1 align-middle"></i>
                                                Close
                                            </button>
                                            <button class="btn btn-danger" id="cancel-interview">
                                                Yes, Cancel!!
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end cancel modal -->

                    <!-- Interview NoShow Modal -->
                    <div class="modal fade zoomIn" id="interviewNoShowModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" id="noShowInterview-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                                </div>
                                <div class="modal-body p-5 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/xzybfbcm.json" trigger="loop" style="width:90px;height:90px"></lord-icon>
                                    <div class="mt-4 text-center">
                                        <h4 class="fs-semibold">
                                            You are about to mark this interview as a no show ?
                                        </h4>
                                        <p class="text-muted fs-14 mb-4 pt-1">
                                            Marking the interview as a no show will record the candidate's absence and notify relevant parties. Please confirm if you wish to proceed with marking the interviewee as a no show.
                                        </p>                                   
                                        <div class="hstack gap-2 justify-content-center remove">
                                            <button class="btn btn-light" data-bs-dismiss="modal" id="noShowInterview-close">
                                                <i class="ri-close-line me-1 align-middle"></i>
                                                Close
                                            </button>
                                            <button class="btn btn-danger" id="noShow-interview">
                                                Yes, No Show!!
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end noShow modal -->
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div>
    <!--end row-->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/list.js/list.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/list.pagination.js/list.pagination.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/pages/interviews.init.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/shadley/Development/Sourcecode/tenubah/Orient/resources/views/interviews.blade.php ENDPATH**/ ?>