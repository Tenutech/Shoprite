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
                        <a class="nav-link menu-link" href="<?php echo e(route('users.index')); ?>">
                            <i class="ri-group-line"></i> 
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarSettings" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSettings">
                            <i class="ri-settings-5-line"></i> 
                            <span>Settings</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarSettings">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('settings.index')); ?>">
                                        Application
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('roles.index')); ?>">
                                        Roles
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('positions.index')); ?>">
                                        Positions
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('brands.index')); ?>">
                                        Brands
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('towns.index')); ?>">
                                        Towns
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('stores.index')); ?>">
                                        Stores
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('provinces.index')); ?>">
                                        Provinces
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('banks.index')); ?>">
                                        Banks
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('disabilities.index')); ?>">
                                        Disabilities
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('genders.index')); ?>">
                                        Genders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('races.index')); ?>">
                                        Races
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('durations.index')); ?>">
                                        Duration
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('educations.index')); ?>">
                                        Education
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('languages.index')); ?>">
                                        Languages
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('reasons.index')); ?>">
                                        Reasons
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('transports.index')); ?>">
                                        Transport
                                    </a>
                                </li>
                            </ul>
                        </div>
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