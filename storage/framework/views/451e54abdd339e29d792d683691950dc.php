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
                                Here's what's happening with Orient today.
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
                                    <span class="badge bg-light text-<?php echo e($percentMovementApplicationsPerMonth > 0 ? 'success' : 'danger'); ?> mb-0"> 
                                        <i class="ri-arrow-<?php echo e($percentMovementApplicationsPerMonth > 0 ? 'up' : 'down'); ?>-line align-middle"></i> 
                                        <?php echo e(abs($percentMovementApplicationsPerMonth)); ?> % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-<?php echo e($percentMovementApplicationsPerMonth > 0 ? 'success' : 'danger'); ?>" , "--vz-transparent"]' dir="ltr" id="applications_sparkline_chart"></div>
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
                                    <span class="badge bg-light text-<?php echo e($percentMovementInterviewedPerMonth > 0 ? 'success' : 'danger'); ?> mb-0"> 
                                        <i class="ri-arrow-<?php echo e($percentMovementInterviewedPerMonth > 0 ? 'up' : 'down'); ?>-line align-middle"></i> 
                                        <?php echo e(abs($percentMovementInterviewedPerMonth)); ?> % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-<?php echo e($percentMovementInterviewedPerMonth > 0 ? 'success' : 'danger'); ?>" , "--vz-transparent"]' dir="ltr" id="interviewed_sparkline_chart"></div>
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
                                    <span class="badge bg-light text-<?php echo e($percentMovementAppointedPerMonth > 0 ? 'success' : 'danger'); ?> mb-0"> 
                                        <i class="ri-arrow-<?php echo e($percentMovementAppointedPerMonth > 0 ? 'up' : 'down'); ?>-line align-middle"></i> 
                                        <?php echo e(abs($percentMovementAppointedPerMonth)); ?> % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-<?php echo e($percentMovementAppointedPerMonth > 0 ? 'success' : 'danger'); ?>" , "--vz-transparent"]' dir="ltr" id="hired_sparkline_chart"></div>
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
                                    <span class="badge bg-light text-<?php echo e($percentMovementRejectedPerMonth > 0 ? 'success' : 'danger'); ?> mb-0"> 
                                        <i class="ri-arrow-<?php echo e($percentMovementRejectedPerMonth > 0 ? 'up' : 'down'); ?>-line align-middle"></i> 
                                        <?php echo e(abs($percentMovementRejectedPerMonth)); ?> % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-<?php echo e($percentMovementRejectedPerMonth > 0 ? 'success' : 'danger'); ?>", "--vz-transparent"]' dir="ltr" id="rejected_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
            </div> <!-- end row-->

            <!-------------------------------------------------------------------------------------
                Applicants Graph
            -------------------------------------------------------------------------------------->
            
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    Applicants Location
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="applicants_treemap" data-colors='["--vz-danger", "--vz-success", "--vz-warning", "--vz-info","--vz-secondary", "--vz-primary"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-xl-4">
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
                Totals
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
                            <h4 class="card-title mb-0 flex-grow-1">
                                Total Messages
                            </h4>                            
                        </div><!-- end card header -->
                        <div class="card-header p-0 border-0 bg-soft-light">
                            <div class="row g-0 text-center">
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1"><span class="counter-value" data-target="<?php echo e($totalIncomingMessages); ?>">0</span></h5>
                                        <p class="text-muted mb-0">Incoming</p>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-6 col-sm-6">
                                    <div class="p-3 border border-dashed border-start-0">
                                        <h5 class="mb-1"><span class="counter-value" data-target="<?php echo e($totalOutgoingMessages); ?>">0</span></h5>
                                        <p class="text-muted mb-0">Outgoing</p>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                        </div><!-- end card header -->
                        <div class="card-body">
                            <div id="total_messages" data-colors='["--vz-success", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div>
            </div>
            <!--end row-->

            <!-------------------------------------------------------------------------------------
                Jobs Summary
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-xxl-12 col-md-12">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Jobs Summary</h4>
                            <div class="flex-shrink-0">
                                <div class="dropdown card-header-dropdown">
                                    <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="fw-bold text-uppercase fs-12">Sort by: </span><span class="text-muted">Current Year<i class="mdi mdi-chevron-down ms-1"></i></span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">Today</a>
                                        <a class="dropdown-item" href="#">Last Week</a>
                                        <a class="dropdown-item" href="#">Last Month</a>
                                        <a class="dropdown-item" href="#">Current Year</a>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card header -->
                        <div class="card-body px-0">
                            <div id="jobs_chart" data-colors='["--vz-success","--vz-primary", "--vz-info", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->
            </div>

            <!-------------------------------------------------------------------------------------
                Locations
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-xl-8">
                    <!-- card -->
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Applicant Locations
                            </h4>
                        </div><!-- end card header -->
            
                        <!-- card body -->
                        <div class="card-body">
            
                            <div id="applicants-by-locations" data-colors='["#E5E8E8", "--vz-primary", "--vz-success"]' style="height: 269px" dir="ltr"></div>
            
                            <?php
                                // Convert $applicantsPerProvince to a collection for easier manipulation
                                $applicantsPerProvinceCollection = collect($applicantsPerProvince);
                                $totalApplicants = $applicantsPerProvinceCollection->sum('y');
                                $sortedProvinces = $applicantsPerProvinceCollection->sortByDesc('y')->take(3);
                            ?>

                            <div class="px-2 py-2 mt-4">
                                <?php $__currentLoopData = $sortedProvinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                    // Calculate the percentage of total applicants for each province
                                    $percentage = number_format(($province['y'] / $totalApplicants) * 100, 2);
                                    ?>

                                    <p class="mb-1">
                                        <?php echo e($province['x']); ?>

                                        <span class="float-end"><?php echo e($percentage); ?>%</span>
                                    </p>
                                    <div class="progress mt-1 mb-3" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" 
                                            style="width: <?php echo e($percentage); ?>%" aria-valuenow="<?php echo e($percentage); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>

                <!-------------------------------------------------------------------------------------
                    Applicant Positions
                -------------------------------------------------------------------------------------->

                <div class="col-xl-4">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Applicant Positions
                            </h4>
                        </div><!-- end card header -->
                        <div class="card-body">
                            <div id="applicant_positions" data-colors='["#f5b041", "#1abc9c", "#3498db", "#9b59b6", "#34495e", "#f1c40f", "#e67e22", "#e74c3c", "#ecf0f1", "#2ecc71", "#95a5a6"]' class="apex-charts" dir="ltr"></div>
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->
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
                                            <div class="col-9">
                                                <?php echo e($position->name); ?>

                                            </div>
                                            <div class="col-3 text-end">
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
    var applicantsPerProvince = <?php echo json_encode($applicantsPerProvince, 15, 512) ?>;

    var applicantsByRace = <?php echo json_encode($applicantsByRace, 15, 512) ?>;
    applicantsByRace.forEach(race => {
        race.data = race.data.reverse();
    });

    var totalApplicantsPerMonth = <?php echo json_encode($totalApplicantsPerMonth, 15, 512) ?>;

    var incomingMessages = <?php echo json_encode($incomingMessages, 15, 512) ?>;
    var outgoingMessages = <?php echo json_encode($outgoingMessages, 15, 512) ?>;

    var applicantsByPosition = <?php echo json_encode($applicantsByPosition, 15, 512) ?>;

    var applicantData = applicantsPerProvince.reduce((accumulator, currentValue) => {
        accumulator[currentValue.x] = currentValue.y;
        return accumulator;
    }, {});

    var applicationsPerMonth = <?php echo json_encode($applicationsPerMonth, 15, 512) ?>;
    var interviewedPerMonth = <?php echo json_encode($interviewedPerMonth, 15, 512) ?>;
    var appointedPerMonth = <?php echo json_encode($appointedPerMonth, 15, 512) ?>;
    var rejectedPerMonth = <?php echo json_encode($rejectedPerMonth, 15, 512) ?>;
</script>
<!-- sweet alert -->
<script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
<!-- apexcharts -->
<script src="<?php echo e(URL::asset('build/libs/apexcharts/apexcharts.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/jsvectormap/js/jsvectormap2.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/jsvectormap/maps/south-africa.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.js')); ?>"></script>
<!-- dashboard init -->
<script src="<?php echo e(URL::asset('build/js/pages/admin.init.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Recruitment\resources\views/admin/home.blade.php ENDPATH**/ ?>