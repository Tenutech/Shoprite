<!-- ========== PHP ========== -->
@php
    $user = Auth::user();
    switch ($user->role_id) {
        case 1:
        case 2:
            $url = 'admin/';
            break;
        case 3:
            $url = 'rpp/';
            break;
        case 4:
            $url = 'dtdp/';
            break;
        case 5:
            $url = 'dpp/';
            break;
        case 6:
            $url = 'manager/';
            break;
        default:
            $url = '/';
            break;
    }
@endphp

<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="/" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="30">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="25">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="/" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="30">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="25">
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
                <li class="menu-title"><span>@lang('translation.menu')</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ url($url.'home') }}">
                        <i class="ri-home-3-line"></i>
                        <span>Home</span>
                    </a>
                </li>
                @if ($user->role_id > 6)
                    @if ($user->applicant && $user->applicant->public_holidays != 'No' && $user->applicant->environment != 'No')
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="{{ route('interviews.index') }}">
                                <i class="ri-group-2-line"></i>
                                <span>My Interviews</span>
                            </a>
                        </li>
                    @endif
                @endif
                @if (in_array($user->role_id, [1,2,3,4,6]))
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarVacancies" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarVacancies">
                            <i class="ri-briefcase-line"></i>
                            <span>Vacancies</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarVacancies">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('vacancies.index') }}">
                                        My Vacancies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('vacancy.index') }}">
                                        New Vacancy
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('shortlist.index') }}">
                            <i class="ri-list-check-2"></i>
                            <span>My Shortlists</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('interviews.index') }}">
                            <i class="ri-group-2-line"></i>
                            <span>My Interviews</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('applicants.index') }}">
                            <i class="ri-profile-line"></i>
                            <span>Saved Candidates</span>
                        </a>
                    </li>
                @endif
                @if ($user->role_id <= 5)
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarReports" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarReports">
                            <i class="ri-line-chart-line"></i>
                            <span>Reports</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarReports">
                            <ul class="nav nav-sm flex-column">
                                @if ($user->role_id <= 2)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('applicants.reports.index') }}">
                                            Candidates
                                        </a>
                                    </li>
                                @endif
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('stores.reports.index') }}">
                                        Stores
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('vacancies.reports.index') }}">
                                        Vacancies
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if ($user->role_id <= 2)
                    <li class="nav-item d-none">
                        <a class="nav-link menu-link" href="#sidebarApprovals" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarApprovals">
                            <i class="ri-shield-check-line"></i>
                            <span>Approvals</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarApprovals">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('approvals.index') }}">
                                        Vacancies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('applicant-approvals.index') }}">
                                        Candidates
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
                                <a class="nav-link" href="{{ route('email.index') }}">
                                    Email
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('faqs.index') }}">
                                    FAQs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('guide.index') }}">
                                    Interview
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('chats.index') }}">
                                    WhatsApp
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
                                    <a class="nav-link" href="{{ route('literacy.index') }}">
                                        Literacy
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('numeracy.index') }}">
                                        Numeracy
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('situational.index') }}">
                                        Situational Awareness
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('weighting.index') }}">
                            <i class="ri-medal-line"></i>
                            <span>Weightings</span>
                        </a>
                    </li>
                    <a class="nav-link menu-link" href="#sidebarUsers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarUsers">
                        <i class="ri-group-line"></i>
                        <span>Users</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('applicants-table.index') }}">
                                    Candidates
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('users.index') }}">
                                    Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('managers.index') }}">
                                    Managers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dpps.index') }}">
                                    DPPs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dtdps.index') }}">
                                    DTDPs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('rpps.index') }}">
                                    RPPs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admins.index') }}">
                                    Admins
                                </a>
                            </li>
                            @if ($user->role_id == 1)
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('super-admins.index') }}">
                                        Super Admins
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarJobs" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarJobs">
                            <i class="ri-briefcase-3-line"></i>
                            <span>Jobs</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarJobs">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item d-none">
                                    <a class="nav-link" href="{{ route('experience.index') }}">
                                        Experience
                                    </a>
                                </li>
                                <li class="nav-item d-none">
                                    <a class="nav-link" href="{{ route('physical.index') }}">
                                        Physical Requirements
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('positions.index') }}">
                                        Positions
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('qualifications.index') }}">
                                        Qualifications (Value Add)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('responsibilities.index') }}">
                                        Responsibilities (Purpose)
                                    </a>
                                </li>
                                <li class="nav-item d-none">
                                    <a class="nav-link" href="{{ route('salaries.index') }}">
                                        Salary & Benefits
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('skills.index') }}">
                                        Skills (Do Daily)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('success-factors.index') }}">
                                        Success Factors (Make You Great)
                                    </a>
                                </li>
                                <li class="nav-item d-none">
                                    <a class="nav-link" href="{{ route('hours.index') }}">
                                        Working Hours
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarSettings" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSettings">
                            <i class="ri-settings-5-line"></i>
                            <span>Settings</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarSettings">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('settings.index') }}">
                                        Application
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('brands.index') }}">
                                        Brands
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('divisions.index') }}">
                                        Divisions
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('educations.index') }}">
                                        Education
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('durations.index') }}">
                                        Experience
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('genders.index') }}">
                                        Genders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('provinces.index') }}">
                                        Provinces
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('races.index') }}">
                                        Races
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('regions.index') }}">
                                        Regions
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('roles.index') }}">
                                        Roles
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('stores.index') }}">
                                        Stores
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('towns.index') }}">
                                        Towns
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @if ($user->role_id == 1)
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="{{ url('/telescope') }}">
                                <i class="ri-microscope-line"></i>
                                <span>Telescope</span>
                            </a>
                        </li>
                    @endif
                @endif
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
