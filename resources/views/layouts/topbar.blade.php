<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="/" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="17">
                        </span>
                    </a>
                    <a href="/" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="17">
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
                        @if($notifications && $notifications->where('read', 'No')->count() > 0)
                            <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger topbarNotificationBadge">
                                {{ $notifications->where('read', 'No')->count() }}
                                <span class="visually-hidden">
                                    New Notifications
                                </span>
                            </span>
                        @endif
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
                                            {{ $notifications->where('read', 'No')->count() }} 
                                            New
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="px-2 pt-2">
                                <ul class="nav nav-tabs dropdown-tabs nav-tabs-custom" data-dropdown-tabs="true" id="notificationItemsTab" role="tablist">
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link active notificationAllCount" data-bs-toggle="tab" href="#all-noti-tab" role="tab" aria-selected="true">
                                            All ({{ $notifications->where('read', 'No')->count() }})
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
                                    @foreach ($notifications as $notification)
                                        @if ($notification->causer)
                                            @if ($notification->subject_type == "App\Models\Application")
                                                <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                    @if ($notification->read == 'No')
                                                        <span class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger p-1 newNotification">
                                                            <span class="visually-hidden">
                                                                Unread Notification
                                                            </span>
                                                        </span>
                                                    @endif                                                                                   
                                                    <div class="d-flex">                                                        
                                                        <img src="{{ URL::asset('images/' . $notification->causer->avatar) }}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                        <div class="flex-grow-1">                                                                                                                       
                                                            <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                                {{ $notification->causer->firstname }} {{ $notification->causer->lastname }}
                                                            </h6>
                                                            @if ($notification->subject)
                                                                <div class="fs-13 text-muted">
                                                                    <p class="mb-1">
                                                                        {{ $notification->notification }} on 
                                                                        <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString(optional($notification->subject)->vacancy->id)]) }}">
                                                                            <b class="text-{{ optional($notification->subject)->vacancy->position->color ?? 'primary'; }}">
                                                                                {{ optional($notification->subject)->vacancy->position->name ?? 'N/A'; }}
                                                                            </b>
                                                                        </a>
                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    <span>
                                                                        <i class="mdi mdi-clock-outline"></i> 
                                                                        {{ $notification->created_at->diffForHumans() }}
                                                                    </span>
                                                                </p>

                                                                <div class="d-flex flex-wrap gap-2 mt-3">
                                                                    <div class="btn-container">
                                                                        @if ($notification->subject->approved == "Pending")
                                                                            <button type="button" data-bs-application="{{ Crypt::encryptString(optional($notification->subject)->id) }}" class="btn btn-sm rounded-pill btn-success waves-effect waves-light applicationApprove">
                                                                                Approve
                                                                            </button>
                                                                            <button type="button" data-bs-application="{{ Crypt::encryptString(optional($notification->subject)->id) }}" class="btn btn-sm rounded-pill btn-danger waves-effect waves-light applicationDecline">
                                                                                Decline
                                                                            </button>
                                                                        @elseif ($notification->subject->approved == "Yes")
                                                                            <a href="{{ route('messages.index', ['id' => Crypt::encryptString($notification->subject->user_id)]) }}" class="btn btn-sm rounded-pill btn-success waves-effect waves-light">
                                                                                Send message
                                                                            </a>
                                                                        @elseif ($notification->subject->approved == "No")
                                                                            <span class="text-danger">Declined!</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="px-2 fs-15">
                                                            <div class="form-check notification-check">
                                                                <input class="form-check-input" type="checkbox" value="{{ Crypt::encryptString($notification->id) }}" id="all-notification-check-{{ $notification->id }}">
                                                                <label class="form-check-label" for="all-notification-check-{{ $notification->id }}"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif ($notification->subject_type == "App\Models\Message")
                                                <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                    @if ($notification->read == 'No')
                                                        <span class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger p-1 newNotification">
                                                            <span class="visually-hidden">
                                                                Unread Notification
                                                            </span>
                                                        </span>
                                                    @endif 
                                                    <div class="d-flex">
                                                        <img src="{{ URL::asset('images/' . $notification->causer->avatar) }}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                                {{ $notification->causer->firstname }} {{ $notification->causer->lastname }}
                                                            </h6>
                                                            @if ($notification->subject)
                                                                <div class="fs-13 text-muted">
                                                                    <p class="mb-1 truncated-text-4-lines">
                                                                        {{ $notification->subject->message }}
                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    <span>
                                                                        <i class="mdi mdi-clock-outline"></i> 
                                                                        {{ $notification->created_at->diffForHumans() }}
                                                                    </span>
                                                                </p>

                                                                <div class="d-flex flex-wrap gap-2 mt-3">
                                                                    <div class="btn-container">
                                                                        <a href="{{ route('messages.index', ['id' => Crypt::encryptString($notification->causer_id)]) }}" class="btn btn-sm rounded-pill btn-success waves-effect waves-light">
                                                                            Reply
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="px-2 fs-15">
                                                            <div class="form-check notification-check">
                                                                <input class="form-check-input" type="checkbox" value="{{ Crypt::encryptString($notification->id) }}" id="all-notification-check-{{ $notification->id }}">
                                                                <label class="form-check-label" for="all-notification-check-{{ $notification->id }}"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif ($notification->subject_type == "App\Models\Vacancy")
                                                @if ($notification->subject)
                                                    <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                        <div class="d-flex">
                                                            <div class="avatar-xs me-3 flex-shrink-0">
                                                                <span class="avatar-title bg-{{ $notification->subject->position->color }}-subtle text-{{ $notification->subject->position->color }} rounded-circle fs-16">
                                                                    <i class="{{ $notification->subject->position->icon }}"></i>
                                                                </span>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($notification->subject->id)]) }}" class="stretched-link">
                                                                    <h6 class="mt-0 mb-2 lh-base">
                                                                        {{ $notification->subject->position->name }}
                                                                    </h6>
                                                                </a>
                                                                <div class="fs-13 text-muted">
                                                                    <p class="mb-1 truncated-text-4-lines">
                                                                        {{ $notification->notification }}
                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    <span>
                                                                        <i class="mdi mdi-clock-outline"></i> 
                                                                        {{ $notification->created_at->diffForHumans() }}
                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <div class="px-2 fs-15">
                                                                <div class="form-check notification-check">
                                                                    <input class="form-check-input" type="checkbox" value="" id="all-notification-check-{{ $notification->id }}">
                                                                    <label class="form-check-label" for="all-notification-check-{{ $notification->id }}"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @elseif ($notification->subject_type == "App\Models\Applicant")
                                                @if ($notification->subject)
                                                    <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                        <div class="d-flex">
                                                            <img src="{{ URL::asset('images/' . $notification->causer->avatar) }}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                            <div class="flex-grow-1">
                                                                <a href="{{ route('profile.index') }}" class="stretched-link">
                                                                    <h6 class="mt-0 mb-2 lh-base">
                                                                        {{ $notification->causer->firstname }} {{ $notification->causer->lastname }}
                                                                    </h6>
                                                                </a>
                                                                <div class="fs-13 text-muted">
                                                                    <p class="mb-1 truncated-text-4-lines">
                                                                        {{ $notification->notification }}
                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    <span>
                                                                        <i class="mdi mdi-clock-outline"></i> 
                                                                        {{ $notification->created_at->diffForHumans() }}
                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <div class="px-2 fs-15">
                                                                <div class="form-check notification-check">
                                                                    <input class="form-check-input" type="checkbox" value="" id="all-notification-check-{{ $notification->id }}">
                                                                    <label class="form-check-label" for="all-notification-check-{{ $notification->id }}"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @elseif ($notification->subject_type == "App\Models\VacancyFill")
                                                @if ($notification->subject)
                                                    <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                        <div class="d-flex">
                                                            <div class="avatar-xs me-3 flex-shrink-0">
                                                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-16">
                                                                    <i class="ri-open-arm-fill"></i>
                                                                </span>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($notification->subject->vacancy->id)]) }}" class="stretched-link">
                                                                    <h6 class="mt-0 mb-2 lh-base">
                                                                        {{ $notification->subject->vacancy->position->name }}
                                                                    </h6>
                                                                </a>
                                                                <div class="fs-13 text-muted">
                                                                    <p class="mb-1 truncated-text-4-lines">
                                                                        {{ $notification->notification }}
                                                                    </p>
                                                                </div>
                                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                    <span>
                                                                        <i class="mdi mdi-clock-outline"></i> 
                                                                        {{ $notification->created_at->diffForHumans() }}
                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <div class="px-2 fs-15">
                                                                <div class="form-check notification-check">
                                                                    <input class="form-check-input" type="checkbox" value="" id="all-notification-check-{{ $notification->id }}">
                                                                    <label class="form-check-label" for="all-notification-check-{{ $notification->id }}"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        @endif
                                    @endforeach 
                                </div>
                            </div>

                            <!-------------------------------------------------------------------------------------
                                Message Tab
                            -------------------------------------------------------------------------------------->

                            <div class="tab-pane fade py-2 ps-2" id="messages-tab" role="tabpanel" aria-labelledby="messages-tab">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    @foreach ($notifications->where('type_id', 2) as $message)
                                        @if ($message->causer)
                                            <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                @if ($notification->read == 'No')
                                                    <span class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger p-1 newNotification">
                                                        <span class="visually-hidden">
                                                            Unread Notification
                                                        </span>
                                                    </span>
                                                @endif 
                                                <div class="d-flex">
                                                    <img src="{{ URL::asset('images/' . $message->causer->avatar) }}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                            {{ $message->causer->firstname }} {{ $message->causer->lastname }}
                                                        </h6>
                                                        @if ($message->subject)
                                                            <div class="fs-13 text-muted">
                                                                <p class="mb-1 truncated-text-4-lines">
                                                                    {{ $message->subject->message }}
                                                                </p>
                                                            </div>
                                                            <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                <span>
                                                                    <i class="mdi mdi-clock-outline"></i> 
                                                                    {{ $message->created_at->diffForHumans() }}
                                                                </span>
                                                            </p>

                                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                                <div class="btn-container">
                                                                    <a href="{{ route('messages.index', ['id' => Crypt::encryptString($message->causer_id)]) }}" class="btn btn-sm rounded-pill btn-success waves-effect waves-light">
                                                                        Reply
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="px-2 fs-15">
                                                        <div class="form-check notification-check">
                                                            <input class="form-check-input" type="checkbox" value="{{ Crypt::encryptString($message->id) }}" id="message-notification-check-{{ $message->id }}">
                                                            <label class="form-check-label" for="message-notification-check-{{ $message->id }}"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    
                                    <div class="my-3 text-center view-all">
                                        <a href="{{ route('messages.index') }}" class="btn btn-soft-secondary waves-effect waves-light">
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
                                    @foreach ($notifications->where('type_id', 1) as $alert)
                                        @if ($alert->subject_type == "App\Models\Application")
                                            <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                @if ($notification->read == 'No')
                                                    <span class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger p-1 newNotification">
                                                        <span class="visually-hidden">
                                                            Unread Notification
                                                        </span>
                                                    </span>
                                                @endif 
                                                <div class="d-flex">
                                                    <img src="{{ URL::asset('images/' . $alert->causer->avatar) }}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                            {{ $alert->causer->firstname }} {{ $alert->causer->lastname }}
                                                        </h6>
                                                        @if ($alert->subject)
                                                            <div class="fs-13 text-muted">
                                                                <p class="mb-1">
                                                                    {{ $alert->notification }} on 
                                                                    <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString(optional($alert->subject)->vacancy->id)]) }}">
                                                                        <b class="text-{{ optional($alert->subject)->vacancy->position->color ?? 'primary'; }}">
                                                                            {{ optional($alert->subject)->vacancy->position->name ?? 'N/A'; }}
                                                                        </b>
                                                                    </a>
                                                                </p>
                                                            </div>
                                                            <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                <span>
                                                                    <i class="mdi mdi-clock-outline"></i> 
                                                                    {{ $alert->created_at->diffForHumans() }}
                                                                </span>
                                                            </p>

                                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                                <div class="btn-container">
                                                                    @if ($alert->subject->approved == "Pending")
                                                                        <button type="button" data-bs-application="{{ Crypt::encryptString(optional($alert->subject)->id) }}" class="btn btn-sm rounded-pill btn-success waves-effect waves-light applicationApprove">
                                                                            Approve
                                                                        </button>
                                                                        <button type="button" data-bs-application="{{ Crypt::encryptString(optional($alert->subject)->id) }}" class="btn btn-sm rounded-pill btn-danger waves-effect waves-light applicationDecline">
                                                                            Decline
                                                                        </button>
                                                                    @elseif ($alert->subject->approved == "Yes")
                                                                        <a href="{{ route('messages.index', ['id' => Crypt::encryptString($alert->subject->user_id)]) }}" class="btn btn-sm rounded-pill btn-success waves-effect waves-light">
                                                                            Send message
                                                                        </a>
                                                                    @elseif ($alert->subject->approved == "No")
                                                                        <span class="text-danger">Declined!</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="px-2 fs-15">
                                                        <div class="form-check notification-check">
                                                            <input class="form-check-input" type="checkbox" value="{{ Crypt::encryptString($alert->id) }}" id="alert-notification-check-{{ $alert->id }}">
                                                            <label class="form-check-label" for="alert-notification-check-{{ $alert->id }}"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($alert->subject_type == "App\Models\Vacancy")
                                            @if ($alert->subject)
                                                <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                    <div class="d-flex">
                                                        <div class="avatar-xs me-3 flex-shrink-0">
                                                            <span class="avatar-title bg-{{ $alert->subject->position->color }}-subtle text-{{ $alert->subject->position->color }} rounded-circle fs-16">
                                                                <i class="{{ $alert->subject->position->icon }}"></i>
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($alert->subject->id)]) }}" class="stretched-link">
                                                                <h6 class="mt-0 mb-2 lh-base">
                                                                    {{ $alert->subject->position->name }}
                                                                </h6>
                                                            </a>
                                                            <div class="fs-13 text-muted">
                                                                <p class="mb-1 truncated-text-4-lines">
                                                                    {{ $alert->notification }}
                                                                </p>
                                                            </div>
                                                            <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                <span>
                                                                    <i class="mdi mdi-clock-outline"></i> 
                                                                    {{ $alert->created_at->diffForHumans() }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="px-2 fs-15">
                                                            <div class="form-check notification-check">
                                                                <input class="form-check-input" type="checkbox" value="" id="all-notification-check-{{ $alert->id }}">
                                                                <label class="form-check-label" for="all-notification-check-{{ $alert->id }}"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @elseif ($alert->subject_type == "App\Models\Applicant")
                                            @if ($alert->subject)
                                                <div class="text-reset notification-item d-block dropdown-item position-relative">
                                                    <div class="d-flex">
                                                        <img src="{{ URL::asset('images/' . $notification->causer->avatar) }}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                        <div class="flex-grow-1">
                                                            <a href="{{ route('profile.index') }}" class="stretched-link">
                                                                <h6 class="mt-0 mb-2 lh-base">
                                                                    {{ $notification->causer->firstname }} {{ $notification->causer->lastname }}
                                                                </h6>
                                                            </a>
                                                            <div class="fs-13 text-muted">
                                                                <p class="mb-1 truncated-text-4-lines">
                                                                    {{ $notification->notification }}
                                                                </p>
                                                            </div>
                                                            <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                                <span>
                                                                    <i class="mdi mdi-clock-outline"></i> 
                                                                    {{ $notification->created_at->diffForHumans() }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="px-2 fs-15">
                                                            <div class="form-check notification-check">
                                                                <input class="form-check-input" type="checkbox" value="" id="all-notification-check-{{ $alert->id }}">
                                                                <label class="form-check-label" for="all-notification-check-{{ $alert->id }}"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    @endforeach
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
                            <img class="rounded-circle header-profile-user" id="topbar-avatar" src="@if (Auth::user()->avatar != ''){{ URL::asset('images/' . Auth::user()->avatar) }}@else{{ URL::asset('build/images/users/user-dummy-img.jpg') }}@endif" alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-semibold user-name-text">{{Auth::user()->firstname}} {{Auth::user()->lastname}}</span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">{{Auth::user()->role->name}}</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome {{Auth::user()->firstname}}!</h6>
                        <a class="dropdown-item" href="profile">
                            <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> 
                            <span class="align-middle">Profile</span>
                        </a>
                        <a class="dropdown-item" href="/messages">
                            @if($messages && $messages->count() > 0)
                                <span class="badge rounded-pill bg-danger mt-1 float-end">
                                    {{$messages->count()}}
                                </span>
                            @endif
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
                            <span key="t-logout">@lang('translation.logout')</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>