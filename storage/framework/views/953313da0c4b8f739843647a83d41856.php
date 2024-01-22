<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="/" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="<?php echo e(URL::asset('build/images/logo-dark.png')); ?>" alt="" height="17">
                        </span>
                    </a>
                    <a href="/" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="<?php echo e(URL::asset('build/images/logo-light.png')); ?>" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                        <i class='bx bx-bell fs-22'></i>
                        <?php if($notifications && $notifications->where('read', 'No')->count() > 0): ?>
                            <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger topbarNotificationBadge">
                                <?php echo e($notifications->where('read', 'No')->count()); ?>

                                <span class="visually-hidden">
                                    New Notifications
                                </span>
                            </span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">

                        <div class="dropdown-head bg-secondary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white">
                                            Notifications
                                        </h6>
                                    </div>
                                    <div class="col-auto dropdown-tabs">
                                        <span class="badge bg-light-subtle text-body fs-13  notificationNewBadge">
                                            <?php echo e($notifications->where('read', 'No')->count()); ?>

                                            New
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="px-2 pt-2">
                                <ul class="nav nav-tabs dropdown-tabs nav-tabs-custom" data-dropdown-tabs="true" id="notificationItemsTab" role="tablist">
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link active notificationAllCount" data-bs-toggle="tab" href="#all-noti-tab" role="tab" aria-selected="true">
                                            All (<?php echo e($notifications->where('read', 'No')->count()); ?>)
                                        </a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" href="#messages-tab" role="tab" aria-selected="false">
                                            Messages
                                        </a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" href="#alerts-tab" role="tab" aria-selected="false">
                                            Alerts
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content position-relative" id="notificationItemsTabContent">

                            <!-------------------------------------------------------------------------------------
                                Notification Tab
                            -------------------------------------------------------------------------------------->

                            <div class="tab-pane fade show active py-2 ps-2" id="all-noti-tab" role="tabpanel">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($notification->causer): ?>
                                            <?php if($notification->subject_type == "App\Models\Application"): ?>
                                                <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                    <?php if($notification->read == 'No'): ?>
                                                        <span class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger p-1 newNotification">
                                                            <span class="visually-hidden">
                                                                Unread Notification
                                                            </span>
                                                        </span>
                                                    <?php endif; ?>
                                                    <div class="d-flex">
                                                        <img src="<?php echo e(URL::asset('images/' . $notification->causer->avatar)); ?>" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                                <?php echo e($notification->causer->firstname); ?> <?php echo e($notification->causer->lastname); ?>

                                                            </h6>
                                                            <?php if($notification->subject): ?>
                                                                <div class="fs-13 text-muted">
                                                                    <p class="mb-1">
                                                                        <?php echo e($notification->notification); ?> on
                                                                        <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString(optional($notification->subject)->vacancy->id)])); ?>">
                                                                            <b class="text-<?php echo e(optional($notification->subject)->vacancy->position->color ?? 'primary'); ?>">
                                                                                <?php echo e(optional($notification->subject)->vacancy->position->name ?? 'N/A'); ?>

                                                                            </b>
                                                                        </a>
                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    <span>
                                                                        <i class="mdi mdi-clock-outline"></i>
                                                                        <?php echo e($notification->created_at->diffForHumans()); ?>

                                                                    </span>
                                                                </p>

                                                                <div class="d-flex flex-wrap gap-2 mt-3">
                                                                    <div class="btn-container">
                                                                        <?php if($notification->subject->approved == "Pending"): ?>
                                                                            <button type="button" data-bs-application="<?php echo e(Crypt::encryptString(optional($notification->subject)->id)); ?>" class="btn btn-sm rounded-pill btn-success waves-effect waves-light applicationApprove">
                                                                                Approve
                                                                            </button>
                                                                            <button type="button" data-bs-application="<?php echo e(Crypt::encryptString(optional($notification->subject)->id)); ?>" class="btn btn-sm rounded-pill btn-danger waves-effect waves-light applicationDecline">
                                                                                Decline
                                                                            </button>
                                                                        <?php elseif($notification->subject->approved == "Yes"): ?>
                                                                            <a href="<?php echo e(route('messages.index', ['id' => Crypt::encryptString($notification->subject->user_id)])); ?>" class="btn btn-sm rounded-pill btn-success waves-effect waves-light">
                                                                                Send message
                                                                            </a>
                                                                        <?php elseif($notification->subject->approved == "No"): ?>
                                                                            <span class="text-danger">Declined!</span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="px-2 fs-15">
                                                            <div class="form-check notification-check">
                                                                <input class="form-check-input" type="checkbox" value="<?php echo e(Crypt::encryptString($notification->id)); ?>" id="all-notification-check-<?php echo e($notification->id); ?>">
                                                                <label class="form-check-label" for="all-notification-check-<?php echo e($notification->id); ?>"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php elseif($notification->subject_type == "App\Models\Message"): ?>
                                                <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                    <?php if($notification->read == 'No'): ?>
                                                        <span class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger p-1 newNotification">
                                                            <span class="visually-hidden">
                                                                Unread Notification
                                                            </span>
                                                        </span>
                                                    <?php endif; ?>
                                                    <div class="d-flex">
                                                        <img src="<?php echo e(URL::asset('images/' . $notification->causer->avatar)); ?>" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                                <?php echo e($notification->causer->firstname); ?> <?php echo e($notification->causer->lastname); ?>

                                                            </h6>
                                                            <?php if($notification->subject): ?>
                                                                <div class="fs-13 text-muted">
                                                                    <p class="mb-1 truncated-text-4-lines">
                                                                        <?php echo e($notification->subject->message); ?>

                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    <span>
                                                                        <i class="mdi mdi-clock-outline"></i>
                                                                        <?php echo e($notification->created_at->diffForHumans()); ?>

                                                                    </span>
                                                                </p>

                                                                <div class="d-flex flex-wrap gap-2 mt-3">
                                                                    <div class="btn-container">
                                                                        <a href="<?php echo e(route('messages.index', ['id' => Crypt::encryptString($notification->causer_id)])); ?>" class="btn btn-sm rounded-pill btn-success waves-effect waves-light">
                                                                            Reply
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="px-2 fs-15">
                                                            <div class="form-check notification-check">
                                                                <input class="form-check-input" type="checkbox" value="<?php echo e(Crypt::encryptString($notification->id)); ?>" id="all-notification-check-<?php echo e($notification->id); ?>">
                                                                <label class="form-check-label" for="all-notification-check-<?php echo e($notification->id); ?>"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php elseif($notification->subject_type == "App\Models\Vacancy"): ?>
                                                <?php if($notification->subject): ?>
                                                    <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                        <div class="d-flex">
                                                            <div class="avatar-xs me-3 flex-shrink-0">
                                                                <span class="avatar-title bg-<?php echo e($notification->subject->position->color); ?>-subtle text-<?php echo e($notification->subject->position->color); ?> rounded-circle fs-16">
                                                                    <i class="<?php echo e($notification->subject->position->icon); ?>"></i>
                                                                </span>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString($notification->subject->id)])); ?>" class="stretched-link">
                                                                    <h6 class="mt-0 mb-2 lh-base">
                                                                        <?php echo e($notification->subject->position->name); ?>

                                                                    </h6>
                                                                </a>
                                                                <div class="fs-13 text-muted">
                                                                    <p class="mb-1 truncated-text-4-lines">
                                                                        <?php echo e($notification->notification); ?>

                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    <span>
                                                                        <i class="mdi mdi-clock-outline"></i>
                                                                        <?php echo e($notification->created_at->diffForHumans()); ?>

                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <div class="px-2 fs-15">
                                                                <div class="form-check notification-check">
                                                                    <input class="form-check-input" type="checkbox" value="" id="all-notification-check-<?php echo e($notification->id); ?>">
                                                                    <label class="form-check-label" for="all-notification-check-<?php echo e($notification->id); ?>"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php elseif($notification->subject_type == "App\Models\Applicant"): ?>
                                                <?php if($notification->subject): ?>
                                                    <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                        <div class="d-flex">
                                                            <img src="<?php echo e(URL::asset('images/' . $notification->causer->avatar)); ?>" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                            <div class="flex-grow-1">
                                                                <a href="<?php echo e(route('profile.index')); ?>" class="stretched-link">
                                                                    <h6 class="mt-0 mb-2 lh-base">
                                                                        <?php echo e($notification->causer->firstname); ?> <?php echo e($notification->causer->lastname); ?>

                                                                    </h6>
                                                                </a>
                                                                <div class="fs-13 text-muted">
                                                                    <p class="mb-1 truncated-text-4-lines">
                                                                        <?php echo e($notification->notification); ?>

                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    <span>
                                                                        <i class="mdi mdi-clock-outline"></i>
                                                                        <?php echo e($notification->created_at->diffForHumans()); ?>

                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <div class="px-2 fs-15">
                                                                <div class="form-check notification-check">
                                                                    <input class="form-check-input" type="checkbox" value="" id="all-notification-check-<?php echo e($notification->id); ?>">
                                                                    <label class="form-check-label" for="all-notification-check-<?php echo e($notification->id); ?>"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php elseif($notification->subject_type == "App\Models\VacancyFill"): ?>
                                                <?php if($notification->subject): ?>
                                                    <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                        <div class="d-flex">
                                                            <div class="avatar-xs me-3 flex-shrink-0">
                                                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-16">
                                                                    <i class="ri-open-arm-fill"></i>
                                                                </span>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString($notification->subject->vacancy->id)])); ?>" class="stretched-link">
                                                                    <h6 class="mt-0 mb-2 lh-base">
                                                                        <?php echo e($notification->subject->vacancy->position->name); ?>

                                                                    </h6>
                                                                </a>
                                                                <div class="fs-13 text-muted">
                                                                    <p class="mb-1 truncated-text-4-lines">
                                                                        <?php echo e($notification->notification); ?>

                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    <span>
                                                                        <i class="mdi mdi-clock-outline"></i>
                                                                        <?php echo e($notification->created_at->diffForHumans()); ?>

                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <div class="px-2 fs-15">
                                                                <div class="form-check notification-check">
                                                                    <input class="form-check-input" type="checkbox" value="" id="all-notification-check-<?php echo e($notification->id); ?>">
                                                                    <label class="form-check-label" for="all-notification-check-<?php echo e($notification->id); ?>"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>

                            <!-------------------------------------------------------------------------------------
                                Message Tab
                            -------------------------------------------------------------------------------------->

                            <div class="tab-pane fade py-2 ps-2" id="messages-tab" role="tabpanel" aria-labelledby="messages-tab">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php $__currentLoopData = $notifications->where('type_id', 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($message->causer): ?>
                                            <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                <?php if($notification->read == 'No'): ?>
                                                    <span class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger p-1 newNotification">
                                                        <span class="visually-hidden">
                                                            Unread Notification
                                                        </span>
                                                    </span>
                                                <?php endif; ?>
                                                <div class="d-flex">
                                                    <img src="<?php echo e(URL::asset('images/' . $message->causer->avatar)); ?>" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                            <?php echo e($message->causer->firstname); ?> <?php echo e($message->causer->lastname); ?>

                                                        </h6>
                                                        <?php if($message->subject): ?>
                                                            <div class="fs-13 text-muted">
                                                                <p class="mb-1 truncated-text-4-lines">
                                                                    <?php echo e($message->subject->message); ?>

                                                                </p>
                                                            </div>
                                                            <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                <span>
                                                                    <i class="mdi mdi-clock-outline"></i>
                                                                    <?php echo e($message->created_at->diffForHumans()); ?>

                                                                </span>
                                                            </p>

                                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                                <div class="btn-container">
                                                                    <a href="<?php echo e(route('messages.index', ['id' => Crypt::encryptString($message->causer_id)])); ?>" class="btn btn-sm rounded-pill btn-success waves-effect waves-light">
                                                                        Reply
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="px-2 fs-15">
                                                        <div class="form-check notification-check">
                                                            <input class="form-check-input" type="checkbox" value="<?php echo e(Crypt::encryptString($message->id)); ?>" id="message-notification-check-<?php echo e($message->id); ?>">
                                                            <label class="form-check-label" for="message-notification-check-<?php echo e($message->id); ?>"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <div class="my-3 text-center view-all">
                                        <a href="<?php echo e(route('messages.index')); ?>" class="btn btn-soft-secondary waves-effect waves-light">
                                            View All Messages
                                            <i class="ri-arrow-right-line align-middle"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-------------------------------------------------------------------------------------
                                Alert Tab
                            -------------------------------------------------------------------------------------->

                            <div class="tab-pane fade p-4" id="alerts-tab" role="tabpanel" aria-labelledby="alerts-tab">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php $__currentLoopData = $notifications->where('type_id', 1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($alert->subject_type == "App\Models\Application"): ?>
                                            <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                <?php if($notification->read == 'No'): ?>
                                                    <span class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger p-1 newNotification">
                                                        <span class="visually-hidden">
                                                            Unread Notification
                                                        </span>
                                                    </span>
                                                <?php endif; ?>
                                                <div class="d-flex">
                                                    <img src="<?php echo e(URL::asset('images/' . $alert->causer->avatar)); ?>" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                            <?php echo e($alert->causer->firstname); ?> <?php echo e($alert->causer->lastname); ?>

                                                        </h6>
                                                        <?php if($alert->subject): ?>
                                                            <div class="fs-13 text-muted">
                                                                <p class="mb-1">
                                                                    <?php echo e($alert->notification); ?> on
                                                                    <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString(optional($alert->subject)->vacancy->id)])); ?>">
                                                                        <b class="text-<?php echo e(optional($alert->subject)->vacancy->position->color ?? 'primary'); ?>">
                                                                            <?php echo e(optional($alert->subject)->vacancy->position->name ?? 'N/A'); ?>

                                                                        </b>
                                                                    </a>
                                                                </p>
                                                            </div>
                                                            <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                <span>
                                                                    <i class="mdi mdi-clock-outline"></i>
                                                                    <?php echo e($alert->created_at->diffForHumans()); ?>

                                                                </span>
                                                            </p>

                                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                                <div class="btn-container">
                                                                    <?php if($alert->subject->approved == "Pending"): ?>
                                                                        <button type="button" data-bs-application="<?php echo e(Crypt::encryptString(optional($alert->subject)->id)); ?>" class="btn btn-sm rounded-pill btn-success waves-effect waves-light applicationApprove">
                                                                            Approve
                                                                        </button>
                                                                        <button type="button" data-bs-application="<?php echo e(Crypt::encryptString(optional($alert->subject)->id)); ?>" class="btn btn-sm rounded-pill btn-danger waves-effect waves-light applicationDecline">
                                                                            Decline
                                                                        </button>
                                                                    <?php elseif($alert->subject->approved == "Yes"): ?>
                                                                        <a href="<?php echo e(route('messages.index', ['id' => Crypt::encryptString($alert->subject->user_id)])); ?>" class="btn btn-sm rounded-pill btn-success waves-effect waves-light">
                                                                            Send message
                                                                        </a>
                                                                    <?php elseif($alert->subject->approved == "No"): ?>
                                                                        <span class="text-danger">Declined!</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="px-2 fs-15">
                                                        <div class="form-check notification-check">
                                                            <input class="form-check-input" type="checkbox" value="<?php echo e(Crypt::encryptString($alert->id)); ?>" id="alert-notification-check-<?php echo e($alert->id); ?>">
                                                            <label class="form-check-label" for="alert-notification-check-<?php echo e($alert->id); ?>"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif($alert->subject_type == "App\Models\Vacancy"): ?>
                                            <?php if($alert->subject): ?>
                                                <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                    <div class="d-flex">
                                                        <div class="avatar-xs me-3 flex-shrink-0">
                                                            <span class="avatar-title bg-<?php echo e($alert->subject->position->color); ?>-subtle text-<?php echo e($alert->subject->position->color); ?> rounded-circle fs-16">
                                                                <i class="<?php echo e($alert->subject->position->icon); ?>"></i>
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <a href="<?php echo e(route('job-overview.index', ['id' => Crypt::encryptString($alert->subject->id)])); ?>" class="stretched-link">
                                                                <h6 class="mt-0 mb-2 lh-base">
                                                                    <?php echo e($alert->subject->position->name); ?>

                                                                </h6>
                                                            </a>
                                                            <div class="fs-13 text-muted">
                                                                <p class="mb-1 truncated-text-4-lines">
                                                                    <?php echo e($alert->notification); ?>

                                                                </p>
                                                            </div>
                                                            <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                <span>
                                                                    <i class="mdi mdi-clock-outline"></i>
                                                                    <?php echo e($alert->created_at->diffForHumans()); ?>

                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="px-2 fs-15">
                                                            <div class="form-check notification-check">
                                                                <input class="form-check-input" type="checkbox" value="" id="all-notification-check-<?php echo e($alert->id); ?>">
                                                                <label class="form-check-label" for="all-notification-check-<?php echo e($alert->id); ?>"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif($alert->subject_type == "App\Models\Applicant"): ?>
                                            <?php if($alert->subject): ?>
                                                <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                    <div class="d-flex">
                                                        <img src="<?php echo e(URL::asset('images/' . $notification->causer->avatar)); ?>" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                        <div class="flex-grow-1">
                                                            <a href="<?php echo e(route('profile.index')); ?>" class="stretched-link">
                                                                <h6 class="mt-0 mb-2 lh-base">
                                                                    <?php echo e($notification->causer->firstname); ?> <?php echo e($notification->causer->lastname); ?>

                                                                </h6>
                                                            </a>
                                                            <div class="fs-13 text-muted">
                                                                <p class="mb-1 truncated-text-4-lines">
                                                                    <?php echo e($notification->notification); ?>

                                                                </p>
                                                            </div>
                                                            <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                <span>
                                                                    <i class="mdi mdi-clock-outline"></i>
                                                                    <?php echo e($notification->created_at->diffForHumans()); ?>

                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="px-2 fs-15">
                                                            <div class="form-check notification-check">
                                                                <input class="form-check-input" type="checkbox" value="" id="all-notification-check-<?php echo e($alert->id); ?>">
                                                                <label class="form-check-label" for="all-notification-check-<?php echo e($alert->id); ?>"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>

                            <div class="notification-actions" id="notification-actions">
                                <div class="d-flex text-muted justify-content-center">
                                    <div id="select-content" class="text-body fw-semibold px-1">
                                        0
                                    </div>
                                    Selected
                                    <button type="button" class="btn btn-link link-primary p-0 ms-3" id="markAsReadBtn">
                                        Mark as read
                                    </button>
                                    <button type="button" class="btn btn-link link-danger p-0 ms-3" id="removeNotificationsBtn">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user" id="topbar-avatar" src="<?php if(Auth::user()->avatar != ''): ?><?php echo e(URL::asset('images/' . Auth::user()->avatar)); ?><?php else: ?><?php echo e(URL::asset('build/images/users/user-dummy-img.jpg')); ?><?php endif; ?>" alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-semibold user-name-text"><?php echo e(Auth::user()->firstname); ?> <?php echo e(Auth::user()->lastname); ?></span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text"><?php echo e(Auth::user()->role->name); ?></span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome <?php echo e(Auth::user()->firstname); ?>!</h6>
                        <a class="dropdown-item" href="profile">
                            <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Profile</span>
                        </a>
                        <a class="dropdown-item" href="/messages">
                            <?php if($messages && $messages->count() > 0): ?>
                                <span class="badge rounded-pill bg-danger mt-1 float-end">
                                    <?php echo e($messages->count()); ?>

                                </span>
                            <?php endif; ?>
                            <i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Messages</span>
                        </a>
                        <a class="dropdown-item" href="faqs">
                            <i class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Help</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="profile-settings">
                            <span class="badge bg-success-subtle text-success mt-1 float-end">
                                New
                            </span>
                            <i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Settings</span></a>
                        <a class="dropdown-item" href="lockscreen">
                            <i class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Lock screen</span>
                        </a>
                        <a class="dropdown-item " href="javascript:void();" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bx bx-power-off font-size-16 align-middle me-1"></i>
                            <span key="t-logout"><?php echo app('translator')->get('translation.logout'); ?></span>
                        </a>
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
                            <?php echo csrf_field(); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\Recruitment\resources\views/layouts/topbar.blade.php ENDPATH**/ ?>