<!-- ========== PHP ========== -->
<?php
    $user = Auth::user();
    switch ($user->role_id) {
        case 1:
        case 2:
            $url = 'admin/';
            break;
        case 3:
            $url = 'manager/';
            break;
        default:
            $url = '/';
            break;
    }
?>

<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-dark.png')); ?>" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-light.png')); ?>" alt="" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span><?php echo app('translator')->get('translation.menu'); ?></span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?php echo e(url($url.'home')); ?>">
                        <i class="ri-home-3-line"></i> 
                        <span>Home</span>
                    </a>
                </li>                
                <?php if($user->role_id > 3): ?>
                    <?php if(!$user->applicant): ?>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="<?php echo e(route('application.index')); ?>">
                                <i class="ri-add-line"></i> 
                                <span>Application</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if($user->applicant): ?>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="<?php echo e(route('vacancies.index')); ?>">
                                <i class="ri-briefcase-line"></i> 
                                <span>Vacancies</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="<?php echo e(route('interviews.index')); ?>">
                                <i class="ri-briefcase-line"></i> 
                                <span>Interviews</span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarVacancies" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarVacancies">
                            <i class="ri-briefcase-line"></i> 
                            <span>Vacancies</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarVacancies">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('vacancies.index')); ?>">
                                        List
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('vacancy.index')); ?>">
                                        New Vacancy
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="<?php echo e(route('applicants.index')); ?>">
                            <i class="ri-profile-line"></i> 
                            <span>Candidates</span>
                        </a>
                    </li> 
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="<?php echo e(route('shortlist.index')); ?>">
                            <i class="ri-list-check-2"></i> 
                            <span>Shortlist</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="<?php echo e(route('interviews.index')); ?>">
                            <i class="ri-briefcase-line"></i> 
                            <span>Interviews</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="/chat">
                            <i class="ri-chat-1-line"></i> 
                            <span>Chat</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="/calendar">
                            <i class="ri-calendar-line"></i> 
                            <span>Calendar</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if($user->role_id == 1 || $user->role_id == 2): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarApprovals" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarApprovals">
                            <i class="ri-shield-check-line"></i> 
                            <span>Approvals</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarApprovals">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('approvals.index')); ?>">
                                        Vacancies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('applicant-approvals.index')); ?>">
                                        Applicants
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <a class="nav-link menu-link" href="#sidebarTemplates" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTemplates">
                        <i class="ri-slideshow-line"></i> 
                        <span>Templates</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarTemplates">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('chats.index')); ?>">
                                    Shoops
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('email.index')); ?>">
                                    Email
                                </a>
                            </li>
                        </ul>
                    </div>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarAssessments" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAssessments">
                            <i class="ri-survey-line"></i> 
                            <span>Assessments</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarAssessments">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('literacy.index')); ?>">
                                        Literacy
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('numeracy.index')); ?>">
                                        Numeracy
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="<?php echo e(route('weighting.index')); ?>">
                            <i class="ri-medal-line"></i> 
                            <span>Weightings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#sidebarjobs" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarjobs">
                            <i class="ri-briefcase-line"></i> 
                            <span>Jobs</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarjobs">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="apps-job-statistics" class="nav-link" > <?php echo app('translator')->get('translation.statistics'); ?> </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#sidebarJoblists" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarJoblists">
                                        <?php echo app('translator')->get('translation.job-lists'); ?>
                                    </a>
                                    <div class="collapse menu-dropdown" id="sidebarJoblists">
                                        <ul class="nav nav-sm flex-column">
                                            <li class="nav-item">
                                                <a href="apps-job-lists" class="nav-link">
                                                    <?php echo app('translator')->get('translation.list'); ?>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="apps-job-grid-lists" class="nav-link">
                                                    <?php echo app('translator')->get('translation.grid'); ?>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="apps-job-details" class="nav-link">
                                                    <?php echo app('translator')->get('translation.overview'); ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <a href="#sidebarCandidatelists" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCandidatelists">
                                        <?php echo app('translator')->get('translation.candidate-lists'); ?>
                                    </a>
                                    <div class="collapse menu-dropdown" id="sidebarCandidatelists">
                                        <ul class="nav nav-sm flex-column">
                                            <li class="nav-item">
                                                <a href="apps-job-candidate-lists" class="nav-link">
                                                    <?php echo app('translator')->get('translation.list-view'); ?>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="apps-job-candidate-grid" class="nav-link">
                                                    <?php echo app('translator')->get('translation.grid-view'); ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <a href="apps-job-application" class="nav-link">
                                        <?php echo app('translator')->get('translation.application'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="apps-job-new" class="nav-link">
                                        <?php echo app('translator')->get('translation.new-job'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="apps-job-companies-lists" class="nav-link">
                                        <?php echo app('translator')->get('translation.companies-list'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="apps-job-categories" class="nav-link">
                                        <?php echo app('translator')->get('translation.job-categories'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="<?php echo e(route('users.index')); ?>">
                            <i class="ri-group-line"></i> 
                            <span>Users</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
<?php /**PATH C:\xampp\htdocs\Recruitment\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>