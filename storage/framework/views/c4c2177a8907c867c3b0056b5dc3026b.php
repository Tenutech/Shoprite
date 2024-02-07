<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.profile'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.css')); ?>">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="profile-foreground position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg">
            <img src="<?php echo e(URL::asset('build/images/profile-bg.jpg')); ?>" alt="" class="profile-wid-img" />
        </div>
    </div>
    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
        <div class="row g-4">
            <div class="col-auto">
                <div class="avatar-lg">
                    <img src="<?php echo e(URL::asset('images/' . Auth::user()->avatar)); ?>" alt="user-img" class="img-thumbnail rounded-circle" />
                </div>
            </div>
            <!--end col-->

            <div class="col">
                <div class="p-2">
                    <h3 class="text-white mb-1"><?php echo e($user->firstname); ?> <?php echo e($user->lastname); ?></h3>
                    <p class="text-white text-opacity-75"><?php echo e($user->role->name); ?></p>
                    <div class="hstack text-white-50 gap-1">
                        <div class="me-2">
                            <i class="ri-user-2-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                            <?php echo e($user->position->name); ?>

                        </div>
                        <div>
                            <i class="ri-building-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                            <?php echo e($user->company->name); ?>

                        </div>
                    </div>
                </div>
            </div>
            <!--end col-->

        </div>
        <!--end row-->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div>
                <div class="d-flex profile-wrapper">
                    <!-- Nav tabs -->
                    <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                <i class="ri-airplay-fill d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">Overview</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-14" data-bs-toggle="tab" href="#application-tab" role="tab">
                                <i class="ri-price-tag-line d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">My Application</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-14" data-bs-toggle="tab" href="#assessments-tab" role="tab">
                                <i class="ri-folder-4-line d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">My Assessments</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-14" data-bs-toggle="tab" href="#applications-tab" role="tab">
                                <i class="ri-price-tag-line d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">My Job Applications</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-14" data-bs-toggle="tab" href="#documents-tab" role="tab">
                                <i class="ri-folder-4-line d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">My Documents</span>
                            </a>
                        </li>
                    </ul>
                    <div class="flex-shrink-0">
                        <a href="profile-settings" class="btn btn-success">
                            <i class="ri-edit-box-line align-bottom"></i> 
                            Edit Profile
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#profileDeleteModal">
                            <i class="ri-delete-bin-6-line align-bottom"></i> 
                            Delete Profile
                        </button>
                    </div>
                </div>
                <!-- Tab panes -->
                <div class="tab-content pt-4 text-muted">

                    <!-------------------------------------------------------------------------------------
                        Overview
                    -------------------------------------------------------------------------------------->

                    <div class="tab-pane active" id="overview-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-xxl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-5">Complete Your Profile</h5>
                                        <div class="progress animated-progress custom-progress progress-label">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e($completion); ?>%" aria-valuenow="<?php echo e($completion); ?>" aria-valuemin="0" aria-valuemax="100">
                                                <div class="label">
                                                    <?php echo e($completion); ?>%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-------------------------------------------------------------------------------------
                                    Info
                                -------------------------------------------------------------------------------------->

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">
                                            Info
                                        </h5>
                                        <div class="table-responsive">
                                            <table class="table table-borderless mb-0">
                                                <tbody>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Full Name :</th>
                                                        <td class="text-muted"><?php echo e($user->firstname); ?> <?php echo e($user->lastname); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Mobile :</th>
                                                        <td class="text-muted"><?php echo e($user->phone); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">E-mail :</th>
                                                        <td class="text-muted"><?php echo e($user->email); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Company :</th>
                                                        <td class="text-muted"><?php echo e($user->company->name); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Position :</th>
                                                        <td class="text-muted"><?php echo e($user->position->name); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Role :</th>
                                                        <td class="text-muted"><?php echo e($user->role->name); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Joining Date</th>
                                                        <td class="text-muted"><?php echo e(date('d M Y', strtotime($user->created_at))); ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->

                                <!-------------------------------------------------------------------------------------
                                    Popular Vacancies
                                -------------------------------------------------------------------------------------->

                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-0">
                                                    Popular Vacancies
                                                </h5>
                                            </div>                                            
                                        </div>
                                        <?php $__currentLoopData = $topVacancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>  
                                            <div class="d-flex mb-4">
                                                <div class="avatar-sm flex-shrink-0">
                                                    <span class="avatar-title bg-<?php echo e($vacancy->position->color); ?>-subtle text-<?php echo e($vacancy->position->color); ?> rounded-circle fs-4">
                                                        <i class="<?php echo e($vacancy->position->icon); ?>"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3 overflow-hidden">
                                                    <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)])); ?>">
                                                        <h6 class="text-truncate fs-15">
                                                            <?php echo e($vacancy->position->name); ?> (<?php echo e($vacancy->store->town->name); ?>)
                                                        </h6>
                                                    </a>
                                                    <p class="text-muted mb-0">
                                                        <?php echo e(date('d M Y', strtotime($vacancy->created_at))); ?>

                                                    </p>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <!--end card-body-->
                                </div>
                                <!--end card-->
                            </div>
                            <!--end col-->

                            <!-------------------------------------------------------------------------------------
                                Recent Activity
                            -------------------------------------------------------------------------------------->

                            <div class="col-xxl-9">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-header align-items-center d-flex">
                                                <h4 class="card-title mb-0  me-2">Recent Activity</h4>
                                                <div class="flex-shrink-0 ms-auto">
                                                    <ul class="nav justify-content-end nav-tabs-custom rounded card-header-tabs border-bottom-0"
                                                        role="tablist">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" data-bs-toggle="tab"
                                                                href="#today" role="tab">
                                                                Today
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#weekly"
                                                                role="tab">
                                                                Weekly
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#monthly"
                                                                role="tab">
                                                                Monthly
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="tab-content text-muted">
                                                    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabID => $tabInfo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="tab-pane <?php echo e($tabInfo['active'] ? 'active' : ''); ?>" id="<?php echo e($tabID); ?>" role="tabpanel">
                                                            <div class="profile-timeline" data-simplebar style="max-height: 485px;">
                                                                <div class="accordion accordion-flush" id="<?php echo e($tabID); ?>Example">
                                                                    <?php
                                                                        $activitiesSubset = $tabInfo['start'] 
                                                                            ? $activities->whereBetween('created_at', [$tabInfo['start'], $tabInfo['end']])
                                                                            : $activities->where('created_at', '<', $tabInfo['end']);
                                                                    ?>
                                                                    <?php $__currentLoopData = $activitiesSubset; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                                                                                        $positionDescription = $vacancy ? optional($vacancy->position)->description : 'N/A';
                                                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                                                    ?>
                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle <?php echo e($bgClass); ?>">
                                                                                                                <i class="<?php echo e($iconClass); ?>"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-primary">
                                                                                                                <?php echo e($positionName); ?>

                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Posted <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-map-pin-line"></i> <?php echo e($brandName); ?> (<?php echo e($townName); ?>)
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-flag-line"></i> <?php echo e($typeName); ?>

                                                                                                </p>
                                                                                                <p class="text-muted mb-1">
                                                                                                    <?php echo $positionDescription; ?>

                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
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
                                                                                        $avatar = isset($activityAttributes['attributes']['avatar']) ? $activityAttributes['attributes']['avatar'] : URL::asset('images/avatar.jpg');
                                                                                    ?>

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <img src="<?php echo e($avatar); ?>" alt="" class="avatar-xs rounded-circle" />
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-success">
                                                                                                                Application
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Submitted <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-user-2-line"></i> <?php echo e($firstname); ?> <?php echo e($lastname); ?>

                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-briefcase-line"></i> <?php echo e($applicantPositionName); ?>

                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                <?php elseif($activity->subject_type === "App\Models\Message"): ?>
                                                                                    <?php
                                                                                        $message = isset($activityAttributes['attributes']['message']) ? $activityAttributes['attributes']['message'] : 'N/A';
                                                                                        $userFrom = $activity->subject->from ?? null;
                                                                                        $userTo = $activity->subject->to ?? null;
                                                                                        $userFromName = $userFrom ? $userFrom->firstname . ' ' . $userFrom->lastname : 'N/A';                                                                                    
                                                                                        $userToName = $userTo ? $userTo->firstname . ' ' . $userTo->lastname : 'N/A';
                                                                                        $userFromAvatar = $userFrom ? URL::asset('images/' . $userFrom->avatar) : URL::asset('images/avatar.jpg');
                                                                                        $userToAvatar = $userTo ? URL::asset('images/' . $userTo->avatar) : URL::asset('images/avatar.jpg');
                                                                                    ?>

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <?php if($activity->causer_id == Auth::id()): ?>
                                                                                                            <img src="<?php echo e($userToAvatar); ?>" alt="" class="avatar-xs rounded-circle" />
                                                                                                        <?php else: ?>
                                                                                                            <img src="<?php echo e($userFromAvatar); ?>" alt="" class="avatar-xs rounded-circle" />
                                                                                                        <?php endif; ?>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-success">
                                                                                                                <?php if($activity->causer_id == Auth::id()): ?>
                                                                                                                    <?php echo e($userToName); ?>

                                                                                                                <?php else: ?>
                                                                                                                    <?php echo e($userFromName); ?>

                                                                                                                <?php endif; ?>
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <?php if($activity->causer_id == Auth::id()): ?>
                                                                                                            <small class="text-muted">
                                                                                                                Sent message <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                            </small>
                                                                                                        <?php else: ?>
                                                                                                            <small class="text-muted">
                                                                                                                Recieved message <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                            </small>
                                                                                                        <?php endif; ?>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <?php echo $message; ?>

                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                <?php elseif($activity->subject_type === "App\Models\Application"): ?>
                                                                                    <?php
                                                                                        $vacancy = $activity->subject->vacancy ?? null;
                                                                                        $positionName = $vacancy ? optional($vacancy->position)->name : 'N/A';
                                                                                        $positionDescription = $vacancy ? optional($vacancy->position)->description : 'N/A';
                                                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                                                        $applicationUser = $activity->subject->user ?? null;
                                                                                        $applicationUserName = $applicationUser ? $applicationUser->firstname . ' ' . $applicationUser->lastname : 'N/A';
                                                                                        $applicationUserAvatar = $applicationUser ? URL::asset('images/' . $applicationUser->avatar) : URL::asset('images/avatar.jpg');
                                                                                        $vacancyUser = $activity->subject->user ?? null;
                                                                                        $vacancyUserName = $vacancyUser ? $vacancyUser->firstname . ' ' . $vacancyUser->lastname : 'N/A';
                                                                                        $vacancyUserAvatar = $vacancyUser ? URL::asset('images/' . $vacancyUser->avatar) : URL::asset('images/avatar.jpg');
                                                                                    ?>

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <?php if($activity->causer_id == Auth::id()): ?>
                                                                                                            <img src="<?php echo e($vacancyUserAvatar); ?>" alt="" class="avatar-xs rounded-circle" />
                                                                                                        <?php else: ?>
                                                                                                            <img src="<?php echo e($applicationUserAvatar); ?>" alt="" class="avatar-xs rounded-circle" />
                                                                                                        <?php endif; ?>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-secondary">
                                                                                                                <?php if($activity->causer_id == Auth::id()): ?>
                                                                                                                    <?php echo e($vacancyUserName); ?>

                                                                                                                <?php else: ?>
                                                                                                                    <?php echo e($applicationUserName); ?>

                                                                                                                <?php endif; ?>
                                                                                                        </span>
                                                                                                        </h6>
                                                                                                        <?php if($activity->causer_id == Auth::id()): ?>
                                                                                                            <small class="text-muted">
                                                                                                                Applied <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                            </small>
                                                                                                        <?php else: ?>
                                                                                                            <small class="text-muted">
                                                                                                                Recieved application request <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                            </small>
                                                                                                        <?php endif; ?>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <?php if($activity->subject): ?>
                                                                                                    <h6 class="fs-14 mb-2">
                                                                                                        <span class="text-primary">
                                                                                                            <?php echo e($positionName); ?>

                                                                                                        </span>
                                                                                                    </h6>
                                                                                                    <p class="text-muted mb-3">
                                                                                                        <i class="ri-map-pin-line"></i> <?php echo e($brandName); ?> (<?php echo e($townName); ?>)
                                                                                                    </p>
                                                                                                    <p class="text-muted mb-3">
                                                                                                        <i class="ri-flag-line"></i> <?php echo e($typeName); ?>

                                                                                                    </p>
                                                                                                    <p class="text-muted mb-1">
                                                                                                        <?php echo $positionDescription; ?>

                                                                                                    </p>
                                                                                                <?php endif; ?>
                                                                                            </div>
                                                                                        </div>
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
                                                                                        $positionDescription = $vacancy ? optional($vacancy->position)->description : 'N/A';
                                                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                                                    ?>

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle <?php echo e($bgClass); ?>">
                                                                                                                <i class="<?php echo e($iconClass); ?>"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-warning">
                                                                                                                <?php echo e($positionName); ?>

                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Updated <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-map-pin-line"></i> <?php echo e($brandName); ?> (<?php echo e($townName); ?>)
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-flag-line"></i> <?php echo e($typeName); ?>

                                                                                                </p>
                                                                                                <p class="text-muted mb-1">
                                                                                                    <?php echo $positionDescription; ?>

                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
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
                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle <?php echo e($bgClass); ?>">
                                                                                                                <i class="<?php echo e($iconClass); ?>"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-warning">
                                                                                                                Application
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Updated <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-user-2-line"></i> <?php echo e($firstname); ?> <?php echo e($lastname); ?>

                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-briefcase-line"></i> <?php echo e($applicantPositionName); ?>

                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php elseif($activity->subject_type === "App\Models\Application"): ?>
                                                                                    <?php
                                                                                        $activityAttributes = json_decode($activity->properties, true);
                                                                                        $newApprovalStatus = $activityAttributes['attributes']['approved'] ?? null;
                                                                                        $oldApprovalStatus = $activityAttributes['old']['approved'] ?? null;
                                                                            
                                                                                        $applicationUser = $activity->subject->user ?? null;
                                                                                        $applicationUserName = $applicationUser ? $applicationUser->firstname . ' ' . $applicationUser->lastname : 'N/A';
                                                                                        $applicationUserAvatar = $applicationUser ? URL::asset('images/' . $applicationUser->avatar) : URL::asset('images/avatar.jpg');
                                                                                        $vacancyUser = $activity->subject->vacancy->user ?? null;
                                                                                        $vacancyUserName = $vacancyUser ? $vacancyUser->firstname . ' ' . $vacancyUser->lastname : 'N/A';
                                                                                        $vacancyUserAvatar = $vacancyUser ? URL::asset('images/' . $vacancyUser->avatar) : URL::asset('images/avatar.jpg');
                                                                                    ?>

                                                                                    <?php if($newApprovalStatus !== $oldApprovalStatus): ?>
                                                                                        <div class="accordion-item border-0">                                                                            
                                                                                            <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                                <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                    <div class="d-flex">
                                                                                                        <div class="flex-shrink-0">
                                                                                                            <?php if($activity->causer_id == Auth::id()): ?>
                                                                                                                <img src="<?php echo e($vacancyUserAvatar); ?>" alt="" class="avatar-xs rounded-circle" />
                                                                                                            <?php else: ?>
                                                                                                                <img src="<?php echo e($applicationUserAvatar); ?>" alt="" class="avatar-xs rounded-circle" />
                                                                                                            <?php endif; ?>
                                                                                                        </div>
                                                                                                        <div class="flex-grow-1 ms-3">
                                                                                                            <h6 class="fs-14 mb-1">
                                                                                                                <span class="text-secondary">
                                                                                                                    <?php if($activity->causer_id == Auth::id()): ?>
                                                                                                                        <?php echo e($vacancyUserName); ?>

                                                                                                                    <?php else: ?>
                                                                                                                        <?php echo e($applicationUserName); ?>

                                                                                                                    <?php endif; ?>
                                                                                                                </span>
                                                                                                            </h6>
                                                                                                            <?php if($activity->causer_id == Auth::id()): ?>
                                                                                                                <small class="text-muted">
                                                                                                                    <?php echo e($newApprovalStatus === "Yes" ? "Approved" : "Declined"); ?> sent connection request <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                                </small>
                                                                                                            <?php else: ?>
                                                                                                                <small class="text-muted">
                                                                                                                    <?php echo e($newApprovalStatus === "Yes" ? "Approved" : "Declined"); ?> recieved connection request <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                                </small>
                                                                                                            <?php endif; ?>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </a>
                                                                                            </div>
                                                                                            <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                                <div class="accordion-body ms-2 ps-5">
                                                                                                    <?php if($activity->subject): ?>
                                                                                                        <h6 class="fs-14 mb-2">
                                                                                                            <span class="text-primary">
                                                                                                                <?php echo e(optional($activity->subject)->vacancy->position->name ?? 'N/A'); ?>

                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <p class="text-muted mb-3">
                                                                                                            <i class="ri-map-pin-line"></i> <?php echo e(optional($activity->subject)->vacancy->store->brand->name ?? 'N/A'); ?> (<?php echo e(optional($activity->subject)->vacancy->store->town->name ?? 'N/A'); ?>)
                                                                                                        </p>
                                                                                                        <p class="text-muted mb-3">
                                                                                                            <i class="ri-flag-line"></i> <?php echo e(optional($activity->subject)->vacancy->type->name ?? 'N/A'); ?>

                                                                                                        </p>
                                                                                                        <p class="text-muted mb-1">
                                                                                                            <?php echo optional($activity->subject)->vacancy->position->description ?? 'N/A'; ?>

                                                                                                        </p>
                                                                                                        <h6 class="fs-14 mb-2">
                                                                                                            <span class="text-primary">
                                                                                                                <?php echo e(optional($activity->subject)->opportunity->name ?? 'N/A'); ?>

                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <?php echo optional($activity->subject)->opportunity->description ?? 'N/A'; ?>

                                                                                                    <?php endif; ?>
                                                                                                </div>
                                                                                            </div>
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
                                                                                        $positionDescription = $position ? $position->description : 'N/A';
                                                                                        $brandName = $store && $store->brand ? $store->brand->name : 'N/A';
                                                                                        $townName = $store && $store->town ? $store->town->name : 'N/A';
                                                                                        $typeName = $type ? $type->name : 'N/A';
                                                                                    ?>

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle <?php echo e($bgClass); ?>">
                                                                                                                <i class="<?php echo e($iconClass); ?>"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-danger">
                                                                                                                <?php echo e($positionName); ?>

                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Deleted <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-map-pin-line"></i> <?php echo e($brandName); ?> (<?php echo e($townName); ?>)
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-flag-line"></i> <?php echo e($typeName); ?>

                                                                                                </p>
                                                                                                <p class="text-muted mb-1">
                                                                                                    <?php echo $positionDescription; ?>

                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php elseif($activity->subject_type === "App\Models\Message"): ?>
                                                                                    <?php
                                                                                        $message = isset($activityAttributes['old']['message']) ? $activityAttributes['old']['message'] : 'N/A';
                                                                                        $userTo = $activity->userForDeletedMessage;
                                                                                        $userToName = $userTo ? $userTo->firstname . ' ' . $userTo->lastname : 'N/A';
                                                                                        $userToAvatar = $userTo ? URL::asset('images/' . $userTo->avatar) : URL::asset('images/avatar.jpg');
                                                                                    ?>

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <img src="<?php echo e($userToAvatar); ?>" alt="" class="avatar-xs rounded-circle" />
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-danger">
                                                                                                                <?php echo e($userToName); ?>

                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Deleted message <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <?php echo $message; ?>

                                                                                            </div>
                                                                                        </div>
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
                                                                                        $positionDescription = optional($vacancy->position)->description ?? 'N/A';
                                                                                        $brandName = optional($vacancy->store->brand)->name ?? 'N/A';
                                                                                        $townName = optional($vacancy->store->town)->name ?? 'N/A';
                                                                                        $typeName = optional($vacancy->type)->name ?? 'N/A';
                                                                                    ?>

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle <?php echo e($bgClass); ?>">
                                                                                                                <i class="<?php echo e($iconClass); ?>"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-info">
                                                                                                                <?php echo e($positionName); ?>

                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Viewed <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-map-pin-line"></i> <?php echo e($brandName); ?> (<?php echo e($townName); ?>)
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-flag-line"></i> <?php echo e($typeName); ?>

                                                                                                </p>
                                                                                                <p class="text-muted mb-1">
                                                                                                    <?php echo $positionDescription; ?>

                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
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
                                                                                        $avatar = $applicant->avatar ?? URL::asset('images/avatar.jpg');
                                                                                    ?>

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <img src="<?php echo e($avatar); ?>" alt="" class="avatar-xs rounded-circle" />
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-info">
                                                                                                                <?php echo e($firstname); ?> <?php echo e($lastname); ?>

                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Viewed <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity<?php echo e($activity->id); ?>" class="accordion-collapse collapse show" aria-labelledby="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-user-2-line"></i> <?php echo e($firstname); ?> <?php echo e($lastname); ?>

                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-briefcase-line"></i> <?php echo e($applicantPositionName); ?>

                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php else: ?>
                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading<?php echo e($activity->id); ?>">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity<?php echo e($activity->id); ?>" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle <?php echo e($bgClass); ?>">
                                                                                                                <i class="<?php echo e($iconClass); ?>"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-info">
                                                                                                                Entity
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Viewed <?php echo e($activity->created_at->diffForHumans()); ?>

                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php endif; ?>

                                                                            <?php endif; ?>

                                                                        <?php endif; ?>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                                                                
                                                                </div>
                                                                <!--end accordion-->
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                                                    
                                                </div>
                                            </div><!-- end card body -->
                                        </div><!-- end card -->
                                    </div><!-- end col -->
                                </div><!-- end row -->

                                <!-------------------------------------------------------------------------------------
                                    My Applications
                                -------------------------------------------------------------------------------------->

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title <?php echo e($user->appliedVacancies->count() <= 3 ? 'mb-5' : ''); ?>">My Applications</h5>
                                        <!-- Swiper -->
                                        <div class="swiper project-swiper mt-n4">
                                            <div class="d-flex justify-content-end gap-2 mb-2">
                                                <div class="slider-button-prev">
                                                    <div class="avatar-title fs-18 rounded px-1">
                                                        <i class="ri-arrow-left-s-line"></i>
                                                    </div>
                                                </div>
                                                <div class="slider-button-next">
                                                    <div class="avatar-title fs-18 rounded px-1">
                                                        <i class="ri-arrow-right-s-line"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="swiper-wrapper">
                                                <?php $__currentLoopData = $user->appliedVacancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>                                                
                                                    <div class="swiper-slide">
                                                        <div class="card profile-project-card shadow-none profile-project-<?php echo e($vacancy->position->color); ?>">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-15 text-truncate">
                                                                            <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)])); ?>" class="text-body">
                                                                                <?php echo e($vacancy->position->name); ?>

                                                                            </a>
                                                                        </h5>
                                                                        <p class="text-muted text-truncate mb-2">
                                                                            Location : 
                                                                            <span class="fw-semibold text-body">
                                                                                <?php echo e($vacancy->store->brand->name); ?> (<?php echo e($vacancy->store->town->name); ?>)
                                                                            </span>
                                                                        </p>
                                                                        <p class="text-muted text-truncate mb-2">
                                                                            Type : 
                                                                            <span class="fw-semibold text-body">
                                                                                <?php echo e($vacancy->type->name); ?>

                                                                            </span>
                                                                        </p>
                                                                        <p class="text-muted text-truncate mb-0">
                                                                            Posted : 
                                                                            <span class="fw-semibold text-body">
                                                                                <?php echo e($vacancy->created_at->diffForHumans()); ?>

                                                                            </span>
                                                                        </p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge bg-<?php echo e($vacancy->status->color); ?>-subtle text-<?php echo e($vacancy->status->color); ?> fs-12">
                                                                            <?php echo e($vacancy->status->name); ?>

                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-13 text-muted mb-0">Applicants :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <?php $__currentLoopData = $vacancy->applicants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $applicant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                    <div class="avatar-group-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo e($applicant->firstname); ?> <?php echo e($applicant->lastname); ?>">
                                                                                        <div class="avatar-xs">
                                                                                            <img src="<?php echo e(URL::asset('images/' . $applicant->avatar)); ?>" class="rounded-circle img-fluid" />
                                                                                        </div>
                                                                                    </div>
                                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                                                                   
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end card body -->
                                                        </div>
                                                        <!-- end card -->
                                                    </div>                                                
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>

                                    </div>
                                    <!-- end card body -->
                                </div><!-- end card -->

                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>

                    <!-------------------------------------------------------------------------------------
                       My Application
                    -------------------------------------------------------------------------------------->

                    <div class="tab-pane fade" id="application-tab" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <h5 class="card-title flex-grow-1 mb-0">My Application</h5>
                                </div>
                                <?php if($user->applicant): ?>
                                    <div class="row">
                                        <!-- Accordions Bordered -->
                                        <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-secondary" id="accordionBordered">

                                            <!-------------------------------------------------------------------------------------
                                                Personal Information
                                            -------------------------------------------------------------------------------------->

                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="accordionborderedExample1">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#accor_borderedExamplecollapse1" aria-expanded="true" aria-controls="accor_borderedExamplecollapse1">
                                                        Personal Information
                                                    </button>
                                                </h2>
                                                <div id="accor_borderedExamplecollapse1" class="accordion-collapse collapse show" aria-labelledby="accordionborderedExample1" data-bs-parent="#accordionBordered">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-lg-4">
                                                                <!-- Full Name -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Full Name
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e($user->applicant->firstname ?? 'N/A'); ?> <?php echo e($user->applicant->lastname); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- ID Number -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            ID Number
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e($user->applicant->id_number ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Contact Number -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Contact Number
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e($user->applicant->contact_number ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Additional Contact Number -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Additional Contact Number
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e($user->applicant->additional_contact_number ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Email Address -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Email Address
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e($user->applicant->email ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4">
                                                                <!-- Address -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Address
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e($user->applicant->location ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Town -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Town
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e(optional($user->applicant->town)->name ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Gender -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Gender
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e(optional($user->applicant->gender)->name ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Ethnicity -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Ethnicity
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e(optional($user->applicant->race)->name ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4">
                                                                <!-- Tax Number -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Tax Number
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e($user->applicant->tax_number ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Citizenship -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Citizenship
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php if($user->applicant->citizen): ?>
                                                                            <?php echo e($user->applicant->citizen == 'Yes' ? 'Citizen' : 'Foreign National'); ?>

                                                                        <?php else: ?>
                                                                            N/A
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>

                                                                <!-- Criminal Record -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Criminal Record
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php if($user->applicant->criminal): ?>
                                                                            <?php if($user->applicant->criminal == 'Yes'): ?>
                                                                                <span class="badge bg-danger-subtle text-danger">
                                                                                    Yes
                                                                                </span>
                                                                            <?php else: ?>
                                                                                <span class="badge bg-success-subtle text-success">
                                                                                    No
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        <?php else: ?>
                                                                            N/A
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>

                                                                <!-- Position -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Position
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php if(optional($user->applicant->position)->name == 'Other'): ?>
                                                                            <?php echo e($user->applicant->position_specify ?? 'N/A'); ?>

                                                                        <?php else: ?>
                                                                            <?php echo e(optional($user->applicant->position)->name ?? 'N/A'); ?>

                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-------------------------------------------------------------------------------------
                                                Qualifications
                                            -------------------------------------------------------------------------------------->

                                            <div class="accordion-item mt-2">
                                                <h2 class="accordion-header" id="accordionborderedExample2">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_borderedExamplecollapse2" aria-expanded="false" aria-controls="accor_borderedExamplecollapse2">
                                                        Qualifications
                                                    </button>
                                                </h2>
                                                <div id="accor_borderedExamplecollapse2" class="accordion-collapse collapse" aria-labelledby="accordionborderedExample2" data-bs-parent="#accordionBordered">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <!-- High School -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            School
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e($user->applicant->school ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Highest Education -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Highest Education
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e(optional($user->applicant->education)->name ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Currenly Training -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Currenly Training
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e($user->applicant->training ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Additional Achievements -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Additional Achievements
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e($user->applicant->other_training ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Drivers License -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Drivers License
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php if($user->applicant->drivers_license): ?>
                                                                            <?php if($user->applicant->drivers_license == 'Yes'): ?>
                                                                                <?php echo e($user->applicant->drivers_license_code); ?>

                                                                            <?php else: ?>
                                                                                <?php echo e($user->applicant->drivers_license); ?>

                                                                            <?php endif; ?>
                                                                        <?php else: ?>
                                                                            N/A
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>

                                                                <!-- Read Languages -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Read Languages
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php if($user->applicant->readLanguages): ?>
                                                                            <?php $__currentLoopData = $user->applicant->readLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <span class="badge bg-primary-subtle text-primary">
                                                                                    <?php echo e($language->name); ?>

                                                                                </span>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        <?php else: ?>
                                                                            N/A
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>

                                                                <!-- Speak Languages -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Speak Languages
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php if($user->applicant->speakLanguages): ?>
                                                                            <?php $__currentLoopData = $user->applicant->speakLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <span class="badge bg-primary-subtle text-primary">
                                                                                    <?php echo e($language->name); ?>

                                                                                </span>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        <?php else: ?>
                                                                            N/A
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-------------------------------------------------------------------------------------
                                                Experience
                                            -------------------------------------------------------------------------------------->

                                            <div class="accordion-item mt-2">
                                                <h2 class="accordion-header" id="accordionborderedExample3">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_borderedExamplecollapse3" aria-expanded="false" aria-controls="accor_borderedExamplecollapse3">
                                                        Experience
                                                    </button>
                                                </h2>
                                                <div id="accor_borderedExamplecollapse3" class="accordion-collapse collapse" aria-labelledby="accordionborderedExample3" data-bs-parent="#accordionBordered">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-lg-4">
                                                                <!-- Previously Employed -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Previously Employed
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e($user->applicant->job_previous ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <?php if($user->applicant->job_previous == 'Yes'): ?>
                                                                    <!-- Previous Employer -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-6">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Previous Employer
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php echo e($user->applicant->job_business ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>

                                                                    <!-- Previous Position -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-6">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Previous Position
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php echo e($user->applicant->job_position ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>

                                                                    <!-- Previous Duration -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-6">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Previous Job Duration
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php echo e(optional($user->applicant->duration)->name ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>

                                                                    <!-- Previous Salary -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-6">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Previous Salary
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php echo e($user->applicant->job_salary ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>

                                                                    <!-- Employer Reference -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-6">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Employer Reference
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php echo e($user->applicant->job_reference_name ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>

                                                                    <!-- Employer Contact -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-6">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Employer Contact Number
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php echo e($user->applicant->job_reference_phone ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>

                                                                    <!-- Previous Job Leave -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-6">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Previous Job Leave
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php if($user->applicant->reason): ?>
                                                                                <?php if($user->applicant->reason->name == 'Other'): ?>
                                                                                    <?php echo e($user->applicant->job_leave_specify); ?>

                                                                                <?php else: ?>
                                                                                    <?php echo e($user->applicant->reason->name); ?>

                                                                                <?php endif; ?>
                                                                            <?php else: ?>
                                                                                N/A
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="col-lg-4">
                                                                <!-- Dismissal -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Dismissal
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php echo e(optional($user->applicant->retrenchment)->name ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <?php if($user->applicant->retrenchment_id < 3): ?>
                                                                    <!-- Dismissal Details -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-6">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Dismissal Details
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php echo e($user->applicant->job_retrenched_specify ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="col-lg-4">
                                                                <!-- Previously Employed Shoprite-->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Previously Employed Shoprite
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <?php if($user->applicant->brand): ?>
                                                                            <?php if($user->applicant->brand->id > 0): ?>
                                                                                <?php echo e($user->applicant->brand->name); ?>

                                                                            <?php else: ?>
                                                                                No
                                                                            <?php endif; ?>
                                                                        <?php else: ?>
                                                                            N/A
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>

                                                                <?php if($user->applicant->brand_id > 0): ?>
                                                                    <!-- Previous Shoprite Position -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-6">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Previous Shoprite Position
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php if(optional($user->applicant->previousPosition)->name == 'Other'): ?>
                                                                                <?php echo e($user->applicant->job_shoprite_position_specify ?? 'N/A'); ?>

                                                                            <?php else: ?>
                                                                                <?php echo e(optional($user->applicant->previousPosition)->name ?? 'N/A'); ?>

                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Shoprite Leave -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-6">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Shoprite Leave
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-6">
                                                                            <?php echo e($user->applicant->job_shoprite_leave ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-------------------------------------------------------------------------------------
                                                Punctuality
                                            -------------------------------------------------------------------------------------->

                                            <div class="accordion-item mt-2">
                                                <h2 class="accordion-header" id="accordionborderedExample4">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_borderedExamplecollapse4" aria-expanded="false" aria-controls="accor_borderedExamplecollapse4">
                                                        Punctuality
                                                    </button>
                                                </h2>
                                                <div id="accor_borderedExamplecollapse4" class="accordion-collapse collapse" aria-labelledby="accordionborderedExample4" data-bs-parent="#accordionBordered">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <!-- Transport -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Transport
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php if($user->applicant->transport): ?>
                                                                            <?php if($user->applicant->transport->name == 'Other'): ?>
                                                                                <?php echo e($user->applicant->transport_specify); ?>

                                                                            <?php else: ?>
                                                                                <?php echo e($user->applicant->transport->name); ?>

                                                                            <?php endif; ?>
                                                                        <?php else: ?>
                                                                            N/A
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>

                                                                <!-- Disability -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Disability
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e(optional($user->applicant->disability)->name ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <?php if($user->applicant->disability_id < 4): ?>
                                                                    <!-- Disability Details -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-3">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Disability Details
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-9">
                                                                            <?php echo e($user->applicant->illness_specify ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <!-- Commencement -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Commencement
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e($user->applicant->commencement ? date('d M Y', strtotime($user->applicant->commencement)) : 'N/A'); ?>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-------------------------------------------------------------------------------------
                                                Reason for Application
                                            -------------------------------------------------------------------------------------->

                                            <div class="accordion-item mt-2">
                                                <h2 class="accordion-header" id="accordionborderedExample5">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_borderedExamplecollapse5" aria-expanded="false" aria-controls="accor_borderedExamplecollapse5">
                                                        Reason for Application
                                                    </button>
                                                </h2>
                                                <div id="accor_borderedExamplecollapse5" class="accordion-collapse collapse" aria-labelledby="accordionborderedExample5" data-bs-parent="#accordionBordered">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <!-- Reason -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Reason
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php if($user->applicant->type): ?>
                                                                            <?php if($user->applicant->type->name == 'Other'): ?>
                                                                                <?php echo e($user->applicant->application_reason_specify); ?>

                                                                            <?php else: ?>
                                                                                <?php echo e($user->applicant->type->name); ?>

                                                                            <?php endif; ?>
                                                                        <?php else: ?>
                                                                            N/A
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>

                                                                <!-- Relocate -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Relocate
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e($user->applicant->relocate ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <?php if($user->applicant->relocate == 'Yes'): ?>
                                                                    <!-- Relocate Town -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-3">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Relocate Town
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-9">
                                                                            <?php echo e($user->applicant->relocate_town ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <!-- Lower Position -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Lower Position
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e($user->applicant->vacancy ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Shift Basis -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Shift Basis
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e($user->applicant->shift ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <!-- Bank Account -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Bank Account
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e($user->applicant->has_bank_account ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>

                                                                <?php if($user->applicant->has_bank_account == 'Yes'): ?>
                                                                    <!-- Bank -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-3">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Bank
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-9">
                                                                            <?php if($user->applicant->bank): ?>
                                                                                <?php if($user->applicant->bank->name == 'Other'): ?>
                                                                                    <?php echo e($user->applicant->bank_specify); ?>

                                                                                <?php else: ?>
                                                                                    <?php echo e($user->applicant->bank->name); ?>

                                                                                <?php endif; ?>
                                                                            <?php else: ?>
                                                                                N/A
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Bank Number -->
                                                                    <div class="row mb-3">
                                                                        <div class="col-lg-3">
                                                                            <h6 class="fs-15 mb-0">
                                                                                Account Number
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-lg-9">
                                                                            <?php echo e($user->applicant->bank_number ?? 'N/A'); ?>

                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <!-- Expected Salary -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Expected Salary
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        <?php echo e($user->applicant->expected_salary ?? 'N/A'); ?>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>                                                                
                                                    </div>
                                                </div>
                                            </div>    
                                        </div>                               
                                    </div>
                                    <!--end row-->
                                <?php endif; ?>
                            </div>
                            <!--end card-body-->
                        </div>
                        <!--end card-->
                    </div>
                    <!--end tab-pane-->

                    <!-------------------------------------------------------------------------------------
                       Assessments
                    -------------------------------------------------------------------------------------->

                    <div class="tab-pane fade" id="assessments-tab" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <h5 class="card-title flex-grow-1 mb-0">My Assessments</h5>
                                </div>
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Literacy Score</h4>
                                            </div><!-- end card header -->
                            
                                            <div class="card-body">
                                                <div id="literacy_chart" data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
                                            </div><!-- end card-body -->
                                        </div><!-- end card -->
                                    </div>
                                    <!-- end col -->                                    

                                    <div class="col-xl-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Numeracy Score</h4>
                                            </div><!-- end card header -->
                            
                                            <div class="card-body">
                                                <div id="numeracy_chart" data-colors='["--vz-secondary"]' class="apex-charts" dir="ltr"></div>
                                            </div><!-- end card-body -->
                                        </div><!-- end card -->
                                    </div>
                                    <!-- end col -->
                                </div>
                                <!--end row-->
                            </div>
                            <!--end card-body-->
                        </div>
                        <!--end card-->
                    </div>
                    <!--end tab-pane-->
                    
                    <!-------------------------------------------------------------------------------------
                       Job Applications
                    -------------------------------------------------------------------------------------->

                    <div class="tab-pane fade" id="applications-tab" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <h5 class="card-title flex-grow-1 mb-0">Job Applications</h5>
                                </div>
                                <div class="row">
                                    <?php $__currentLoopData = $user->appliedVacancies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vacancy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $statusInfo = $user->getApplicationStatusAndColor($vacancy->pivot->approved);
                                        ?>

                                        <div class="col-xxl-3 col-sm-6">
                                            <div class="card profile-project-card shadow-none profile-project-<?php echo e($vacancy->position->color); ?>">
                                                <div class="card-body p-4">
                                                    <div class="d-flex">
                                                        <div class="flex-grow-1 text-muted overflow-hidden">
                                                            <h5 class="fs-15 text-truncate">
                                                                <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)])); ?>" class="text-body">
                                                                    <?php echo e($vacancy->position->name); ?>

                                                                </a>
                                                            </h5>
                                                            <p class="text-muted text-truncate mb-2">
                                                                Location : 
                                                                <span class="fw-semibold text-body">
                                                                    <?php echo e($vacancy->store->brand->name); ?> (<?php echo e($vacancy->store->town->name); ?>)
                                                                </span>
                                                            </p>
                                                            <p class="text-muted text-truncate mb-2">
                                                                Type : 
                                                                <span class="fw-semibold text-body">
                                                                    <?php echo e($vacancy->type->name); ?>

                                                                </span>
                                                            </p>
                                                            <p class="text-muted text-truncate mb-0">
                                                                Posted : 
                                                                <span class="fw-semibold text-body">
                                                                    <?php echo e($vacancy->created_at->diffForHumans()); ?>

                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="flex-shrink-0 ms-2">
                                                            <div class="badge bg-<?php echo e($statusInfo['color']); ?>-subtle text-<?php echo e($statusInfo['color']); ?> fs-12">
                                                                <?php echo e($statusInfo['name']); ?>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end card body -->
                                            </div>
                                            <!-- end card -->
                                        </div>
                                        <!--end col-->
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                                    
                                </div>
                                <!--end row-->
                            </div>
                            <!--end card-body-->
                        </div>
                        <!--end card-->
                    </div>
                    <!--end tab-pane-->

                    <!-------------------------------------------------------------------------------------
                        Documents
                    -------------------------------------------------------------------------------------->

                    <div class="tab-pane fade" id="documents-tab" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3"> <!-- Flex container -->
                                    <h5 class="fs-17 mb-0" id="filetype-title">
                                        My Documentation
                                    </h5>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#fileUploadModal">
                                        <i class="ri-upload-2-fill me-1 align-bottom"></i> 
                                        Upload File
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-nowrap mb-0" id="fileTable">
                                                <thead class="table-active">
                                                    <tr>
                                                        <th scope="col">Name</th>
                                                        <th scope="col">Type</th>
                                                        <th scope="col">Size</th>
                                                        <th scope="col">Upload Date</th>
                                                        <th scope="col" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="file-list">
                                                    <?php $__currentLoopData = $user->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $fileIcon = '';
                                                        ?>
                                                        
                                                        <?php switch($file->type):
                                                            case ('png'): ?>
                                                            <?php case ('jpg'): ?>
                                                            <?php case ('jpeg'): ?>
                                                                <?php
                                                                    $fileIcon = '<i class="ri-gallery-fill align-bottom text-success"></i>';
                                                                ?>
                                                                <?php break; ?>
                                                        
                                                            <?php case ('pdf'): ?>
                                                                <?php
                                                                    $fileIcon = '<i class="ri-file-pdf-fill align-bottom text-danger"></i>';
                                                                ?>
                                                                <?php break; ?>
                                                        
                                                            <?php case ('docx'): ?>
                                                                <?php
                                                                    $fileIcon = '<i class="ri-file-word-2-fill align-bottom text-primary"></i>';
                                                                ?>
                                                                <?php break; ?>
                                                        
                                                            <?php case ('xls'): ?>
                                                            <?php case ('xlsx'): ?>
                                                                <?php
                                                                    $fileIcon = '<i class="ri-file-excel-2-fill align-bottom text-success"></i>';
                                                                ?>
                                                                <?php break; ?>
                                                        
                                                            <?php case ('csv'): ?>
                                                                <?php
                                                                    $fileIcon = '<i class="ri-file-excel-fill align-bottom text-success"></i>';
                                                                ?>
                                                                <?php break; ?>
                                                        
                                                            <?php case ('txt'): ?>
                                                            <?php default: ?>
                                                                <?php
                                                                    $fileIcon = '<i class="ri-file-text-fill align-bottom text-secondary"></i>';
                                                                ?>
                                                        <?php endswitch; ?>
                                                        <tr data-file-id="<?php echo e($file->id); ?>">
                                                            <td>
                                                                <a href="<?php echo e(route('document.view', ['id' => Crypt::encryptString($file->id)])); ?>" target="_blank">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="flex-shrink-0 fs-17 me-2 filelist-icon"><?php echo $fileIcon; ?></div>
                                                                        <div class="flex-grow-1 filelist-name"><?php echo e(substr($file->name, 0, strrpos($file->name, '-'))); ?></div>
                                                                    </div>
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <?php echo e($file->type); ?>

                                                            </td>
                                                            <?php
                                                                $fileSizeInMB = $file->size / (1024 * 1024);
                                                                if ($fileSizeInMB < 0.1) {
                                                                    $fileSizeInKB = number_format($file->size / 1024, 1);
                                                                    $fileSizeText = "{$fileSizeInKB} KB";
                                                                } else {
                                                                    $fileSizeInMB = number_format($fileSizeInMB, 1);
                                                                    $fileSizeText = "{$fileSizeInMB} MB";
                                                                }
                                                            ?>
                                                            <td class="filelist-size">                                            
                                                                <?php echo e($fileSizeText); ?>

                                                            </td>
                                                            <td class="filelist-create">
                                                                <?php echo e(date('d M Y', strtotime($file->created_at))); ?>

                                                            </td>
                                                            <td>
                                                                <div class="d-flex gap-3 justify-content-center">
                                                                    <div class="dropdown">
                                                                        <button class="btn btn-light btn-icon btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i class="ri-more-fill align-bottom"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                                            <li>
                                                                                <a class="dropdown-item viewfile-list" href="<?php echo e(route('document.view', ['id' => Crypt::encryptString($file->id)])); ?>" target="_blank">
                                                                                    View
                                                                                </a>
                                                                            </li>
                                                                            <li>
                                                                                <a class="dropdown-item downloadfile-list" href="<?php echo e(route('document.download', ['id' => Crypt::encryptString($file->id)])); ?>">
                                                                                    Download
                                                                                </a>
                                                                            </li>
                                                                            <li class="dropdown-divider"></li>
                                                                            <li>
                                                                                <button class="dropdown-item downloadfile-list" href="#fileDeleteModal" data-bs-toggle="modal" data-bs-id="<?php echo e($file->id); ?>">
                                                                                    Delete
                                                                                </button>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end tab-pane-->
                </div>
                <!--end tab-content-->
            </div>
        </div>
        <!--end col-->
    </div>
    <!--end row-->

    <!-------------------------------------------------------------------------------------
        Modals
    -------------------------------------------------------------------------------------->

    <!-- File upload modal -->
    <div id="fileUploadModal" class="modal fade" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 overflow-hidden">
                <div class="modal-header p-3">
                    <h4 class="card-title mb-0">Upload File</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formFile" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" id="positionID" name="position_id" value="<?php echo e(Crypt::encryptString($vacancy->position->id)); ?>"/>
                        <div class="mb-3">
                            <input class="form-control" name="file" type="file" multiple="multiple" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" id="submitBtn" type="button">
                                Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- end file upload modal -->

    <!-- file delete modal -->
    <div class="modal fade flip" id="fileDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-5 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f06548,secondary:#f7b84b" style="width:90px;height:90px"></lord-icon>
                    <div class="mt-4 text-center">
                        <h4>
                            You are about to delete this file ?
                        </h4>
                        <p class="text-muted fs-14 mb-4">
                            Deleting this file will remove all of the information from the database.
                        </p>
                        <div class="hstack gap-2 justify-content-center remove">
                            <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="deleteFile-close">
                                <i class="ri-close-line me-1 align-middle"></i> 
                                Close
                            </button>
                            <button class="btn btn-danger" id="delete-file" data-bs-id="">
                                Yes, Delete It
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end file delete modal -->

    <!-- Profile delete modal -->
    <div class="modal fade flip" id="profileDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-5 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:90px;height:90px"></lord-icon>
                    <div class="mt-4 text-center">
                        <h4>
                            You are about to delete your profile ?
                        </h4>
                        <p class="text-muted fs-14 mb-4">
                            Deleting your profile will remove all of your information from our database.
                        </p>
                        <div class="hstack gap-2 justify-content-center remove">
                            <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="profileDelete-close">
                                <i class="ri-close-line me-1 align-middle"></i> 
                                Close
                            </button>                       
                            <button class="btn btn-danger" id="profile-delete">
                                Yes, Delete It
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end profile delete modal -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script>
        var literacyScore = <?php echo e(optional($user->applicant)->literacy_score ?? 0); ?>;
        var literacyQuestions = <?php echo e(optional($user->applicant)->literacy_questions ?? 10); ?>;
        var literacy = "<?php echo e(optional($user->applicant)->literacy ?? 0/10); ?>";

        var numeracyScore = <?php echo e(optional($user->applicant)->numeracy_score ?? 0); ?>;
        var numeracyQuestions = <?php echo e(optional($user->applicant)->numeracy_questions ?? 10); ?>;
        var numeracy = "<?php echo e(optional($user->applicant)->numeracy ?? 0/10); ?>";
    </script>

    <script src="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/apexcharts/apexcharts.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/pages/profile.init.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Recruitment\resources\views/profile.blade.php ENDPATH**/ ?>