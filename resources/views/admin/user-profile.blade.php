@extends('layouts.master')
@section('title')
    @lang('translation.profile')
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}">
@endsection
@section('content')
    <div class="profile-foreground position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg">
            <img src="{{ URL::asset('build/images/profile-bg.jpg') }}" alt="" class="profile-wid-img" />
        </div>
    </div>
    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
        <div class="row g-4">
            <div class="col-auto">
                <div class="avatar-lg">
                    <img src="{{ URL::asset('storage/images/' . $user->avatar) }}" alt="user-img" class="img-thumbnail rounded-circle" />
                </div>
            </div>
            <!--end col-->

            <div class="col">
                <div class="p-2">
                    <h3 class="text-white mb-1">{{ $user->firstname }} {{ $user->lastname }}</h3>
                    <p class="text-white text-opacity-75">{{ optional($user->role)->name }}</p>
                    <div class="hstack text-white-50 gap-1">
                        <div class="me-2">
                            <i class="ri-global-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                            {{ optional($user->status)->name }}
                        </div>
                        <div>
                            <i class="ri-building-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                            {{ optional($user->company)->name }}
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
                            <a class="nav-link fs-14 user-tab active" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                <i class="ri-airplay-fill d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">Overview</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-14 user-tab" data-bs-toggle="tab" href="#application-tab" role="tab">
                                <i class="ri-price-tag-line d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">My Application</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-14 user-tab" data-bs-toggle="tab" href="#assessments-tab" role="tab">
                                <i class="ri-folder-4-line d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">My Assessments</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-14 user-tab d-none" data-bs-toggle="tab" href="#applications-tab" role="tab">
                                <i class="ri-price-tag-line d-inline-block d-md-none"></i> 
                                <span class="d-none d-md-inline-block">My Job Applications</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-14 user-tab" data-bs-toggle="tab" href="#documents-tab" role="tab">
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
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $completion }}%" aria-valuenow="{{ $completion }}" aria-valuemin="0" aria-valuemax="100">
                                                <div class="label">
                                                    {{ $completion }}%
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
                                                        <td class="text-muted">{{ $user->firstname }} {{ $user->lastname }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Mobile :</th>
                                                        <td class="text-muted">{{ $user->phone }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">E-mail :</th>
                                                        <td class="text-muted">{{ $user->email }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Company :</th>
                                                        <td class="text-muted">{{ $user->company->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Role :</th>
                                                        <td class="text-muted">{{ $user->role->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Joining Date</th>
                                                        <td class="text-muted">{{ date('d M Y', strtotime($user->created_at)) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->                                
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
                                                    @foreach($tabs as $tabID => $tabInfo)
                                                        <div class="tab-pane {{ $tabInfo['active'] ? 'active' : '' }}" id="{{ $tabID }}" role="tabpanel">
                                                            <div class="profile-timeline" data-simplebar style="max-height: 485px;">
                                                                <div class="accordion accordion-flush" id="{{ $tabID }}Example">
                                                                    @php
                                                                        $activitiesSubset = $tabInfo['start'] 
                                                                            ? $activities->whereBetween('created_at', [$tabInfo['start'], $tabInfo['end']])
                                                                            : $activities->where('created_at', '<', $tabInfo['end']);
                                                                    @endphp
                                                                    @foreach($activitiesSubset as $activity)
                                                                        @php
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
                                                                        @endphp
                                                                        @if($showActivity)
                                                                            @php
                                                                                $activityAttributes = json_decode($activity->properties, true);
                                                                            @endphp

                                                                            <!-------------------------------------------------------------------------------------
                                                                                Created
                                                                            -------------------------------------------------------------------------------------->

                                                                            @if($activity->event === "created")
                                                                                @if ($activity->subject_type === "App\Models\Vacancy")
                                                                                    @php                                                        
                                                                                        $vacancy = $activity->subject; // This should be the related Vacancy model with loaded relationships
                                                                                        $positionName = $vacancy ? optional($vacancy->position)->name : 'N/A';
                                                                                        $positionDescription = $vacancy ? optional($vacancy->position)->description : 'N/A';
                                                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                                                    @endphp
                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle {{ $bgClass }}">
                                                                                                                <i class="{{ $iconClass }}"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-primary">
                                                                                                                {{ $positionName }}
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Posted {{ $activity->created_at->diffForHumans() }}
                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-map-pin-line"></i> {{ $brandName }} ({{ $townName }})
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-flag-line"></i> {{ $typeName }}
                                                                                                </p>
                                                                                                <p class="text-muted mb-1">
                                                                                                    {!! $positionDescription !!}
                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @elseif ($activity->subject_type === "App\Models\Applicant")
                                                                                    @php
                                                                                        $applicantPosition = $activity->subject->position ?? null;
                                                                                        $applicantPositionName = $applicantPosition ? $applicantPosition->name : 'N/A';
                                                                                        if ($applicantPositionName === "Other") {
                                                                                            $applicantPositionName = $activityAttributes['attributes']['position_specify'] ?? 'N/A';
                                                                                        }
                                                                                        $firstname = isset($activityAttributes['attributes']['firstname']) ? $activityAttributes['attributes']['firstname'] : 'N/A';
                                                                                        $lastname = isset($activityAttributes['attributes']['lastname']) ? $activityAttributes['attributes']['lastname'] : 'N/A';
                                                                                        $avatar = isset($activityAttributes['attributes']['avatar']) ? $activityAttributes['attributes']['avatar'] : URL::asset('images/avatar.jpg');
                                                                                    @endphp

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <img src="{{ $avatar }}" alt="" class="avatar-xs rounded-circle" />
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-success">
                                                                                                                Application
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Submitted {{ $activity->created_at->diffForHumans() }}
                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-user-2-line"></i> {{ $firstname }} {{ $lastname }}
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-briefcase-line"></i> {{ $applicantPositionName }}
                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                @elseif ($activity->subject_type === "App\Models\Message")
                                                                                    @php
                                                                                        $message = isset($activityAttributes['attributes']['message']) ? $activityAttributes['attributes']['message'] : 'N/A';
                                                                                        $userFrom = $activity->subject->from ?? null;
                                                                                        $userTo = $activity->subject->to ?? null;
                                                                                        $userFromName = $userFrom ? $userFrom->firstname . ' ' . $userFrom->lastname : 'N/A';                                                                                    
                                                                                        $userToName = $userTo ? $userTo->firstname . ' ' . $userTo->lastname : 'N/A';
                                                                                        $userFromAvatar = $userFrom ? URL::asset('storage/images/' . $userFrom->avatar) : URL::asset('images/avatar.jpg');
                                                                                        $userToAvatar = $userTo ? URL::asset('storage/images/' . $userTo->avatar) : URL::asset('images/avatar.jpg');
                                                                                    @endphp

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        @if($activity->causer_id == $user->id)
                                                                                                            <img src="{{$userToAvatar}}" alt="" class="avatar-xs rounded-circle" />
                                                                                                        @else
                                                                                                            <img src="{{$userFromAvatar}}" alt="" class="avatar-xs rounded-circle" />
                                                                                                        @endif
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-success">
                                                                                                                @if($activity->causer_id == $user->id)
                                                                                                                    {{ $userToName }}
                                                                                                                @else
                                                                                                                    {{ $userFromName }}
                                                                                                                @endif
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        @if($activity->causer_id == $user->id)
                                                                                                            <small class="text-muted">
                                                                                                                Sent message {{ $activity->created_at->diffForHumans() }}
                                                                                                            </small>
                                                                                                        @else
                                                                                                            <small class="text-muted">
                                                                                                                Recieved message {{ $activity->created_at->diffForHumans() }}
                                                                                                            </small>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                {!! $message !!}
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                @elseif ($activity->subject_type === "App\Models\Application")
                                                                                    @php
                                                                                        $vacancy = $activity->subject->vacancy ?? null;
                                                                                        $positionName = $vacancy ? optional($vacancy->position)->name : 'N/A';
                                                                                        $positionDescription = $vacancy ? optional($vacancy->position)->description : 'N/A';
                                                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                                                        $applicationUser = $activity->subject->user ?? null;
                                                                                        $applicationUserName = $applicationUser ? $applicationUser->firstname . ' ' . $applicationUser->lastname : 'N/A';
                                                                                        $applicationUserAvatar = $applicationUser ? URL::asset('storage/images/' . $applicationUser->avatar) : URL::asset('images/avatar.jpg');
                                                                                        $vacancyUser = $activity->subject->vacancy->user ?? null;
                                                                                        $vacancyUserName = $vacancyUser ? $vacancyUser->firstname . ' ' . $vacancyUser->lastname : 'N/A';
                                                                                        $vacancyUserAvatar = $vacancyUser ? URL::asset('storage/images/' . $vacancyUser->avatar) : URL::asset('images/avatar.jpg');
                                                                                    @endphp

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        @if($activity->causer_id == $user->id)
                                                                                                            <img src="{{$vacancyUserAvatar}}" alt="" class="avatar-xs rounded-circle" />
                                                                                                        @else
                                                                                                            <img src="{{$applicationUserAvatar}}" alt="" class="avatar-xs rounded-circle" />
                                                                                                        @endif
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-secondary">
                                                                                                                @if($activity->causer_id == $user->id)
                                                                                                                    {{ $vacancyUserName }}
                                                                                                                @else
                                                                                                                    {{ $applicationUserName }}
                                                                                                                @endif
                                                                                                        </span>
                                                                                                        </h6>
                                                                                                        @if($activity->causer_id == $user->id)
                                                                                                            <small class="text-muted">
                                                                                                                Applied {{ $activity->created_at->diffForHumans() }}
                                                                                                            </small>
                                                                                                        @else
                                                                                                            <small class="text-muted">
                                                                                                                Recieved application request {{ $activity->created_at->diffForHumans() }}
                                                                                                            </small>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                @if($activity->subject)
                                                                                                    <h6 class="fs-14 mb-2">
                                                                                                        <span class="text-primary">
                                                                                                            {{ $positionName }}
                                                                                                        </span>
                                                                                                    </h6>
                                                                                                    <p class="text-muted mb-3">
                                                                                                        <i class="ri-map-pin-line"></i> {{ $brandName }} ({{ $townName }})
                                                                                                    </p>
                                                                                                    <p class="text-muted mb-3">
                                                                                                        <i class="ri-flag-line"></i> {{ $typeName }}
                                                                                                    </p>
                                                                                                    <p class="text-muted mb-1">
                                                                                                        {!! $positionDescription !!}
                                                                                                    </p>
                                                                                                @endif
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                @endif

                                                                            <!-------------------------------------------------------------------------------------
                                                                                Updated
                                                                            -------------------------------------------------------------------------------------->

                                                                            @elseif($activity->event === "updated")
                                                                                @if ($activity->subject_type === "App\Models\Vacancy")
                                                                                    @php                                                        
                                                                                        $vacancy = $activity->subject; // This should be the related Vacancy model with loaded relationships
                                                                                        $positionName = $vacancy ? optional($vacancy->position)->name : 'N/A';
                                                                                        $positionDescription = $vacancy ? optional($vacancy->position)->description : 'N/A';
                                                                                        $brandName = $vacancy ? optional($vacancy->store->brand)->name : 'N/A';
                                                                                        $townName = $vacancy ? optional($vacancy->store->town)->name : 'N/A';
                                                                                        $typeName = $vacancy ? optional($vacancy->type)->name : 'N/A';
                                                                                    @endphp

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle {{ $bgClass }}">
                                                                                                                <i class="{{ $iconClass }}"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-warning">
                                                                                                                {{ $positionName }}
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Updated {{ $activity->created_at->diffForHumans() }}
                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-map-pin-line"></i> {{ $brandName }} ({{ $townName }})
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-flag-line"></i> {{ $typeName }}
                                                                                                </p>
                                                                                                <p class="text-muted mb-1">
                                                                                                    {!! $positionDescription !!}
                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @elseif ($activity->subject_type === "App\Models\Applicant")
                                                                                    @php
                                                                                        $applicantPosition = $activity->subject->position ?? null;
                                                                                        $applicantPositionName = $applicantPosition ? $applicantPosition->name : 'N/A';
                                                                                        if ($applicantPositionName === "Other") {
                                                                                            $applicantPositionName = $activityAttributes['attributes']['position_specify'] ?? 'N/A';
                                                                                        }
                                                                                        $firstname = isset($activityAttributes['attributes']['firstname']) ? $activityAttributes['attributes']['firstname'] : 'N/A';
                                                                                        $lastname = isset($activityAttributes['attributes']['lastname']) ? $activityAttributes['attributes']['lastname'] : 'N/A';
                                                                                    @endphp
                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle {{ $bgClass }}">
                                                                                                                <i class="{{ $iconClass }}"></i>
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
                                                                                                            Updated {{ $activity->created_at->diffForHumans() }}
                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-user-2-line"></i> {{ $firstname }} {{ $lastname }}
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-briefcase-line"></i> {{ $applicantPositionName }}
                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @elseif ($activity->subject_type === "App\Models\Application")
                                                                                    @php
                                                                                        $activityAttributes = json_decode($activity->properties, true);
                                                                                        $newApprovalStatus = $activityAttributes['attributes']['approved'] ?? null;
                                                                                        $oldApprovalStatus = $activityAttributes['old']['approved'] ?? null;
                                                                            
                                                                                        $applicationUser = $activity->subject->user ?? null;
                                                                                        $applicationUserName = $applicationUser ? $applicationUser->firstname . ' ' . $applicationUser->lastname : 'N/A';
                                                                                        $applicationUserAvatar = $applicationUser ? URL::asset('storage/images/' . $applicationUser->avatar) : URL::asset('images/avatar.jpg');
                                                                                        $vacancyUser = $activity->subject->vacancy->user ?? null;
                                                                                        $vacancyUserName = $vacancyUser ? $vacancyUser->firstname . ' ' . $vacancyUser->lastname : 'N/A';
                                                                                        $vacancyUserAvatar = $vacancyUser ? URL::asset('storage/images/' . $vacancyUser->avatar) : URL::asset('images/avatar.jpg');
                                                                                    @endphp

                                                                                    @if($newApprovalStatus !== $oldApprovalStatus)
                                                                                        <div class="accordion-item border-0">                                                                            
                                                                                            <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                                <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                    <div class="d-flex">
                                                                                                        <div class="flex-shrink-0">
                                                                                                            @if($activity->causer_id == $user->id)
                                                                                                                <img src="{{ $vacancyUserAvatar }}" alt="" class="avatar-xs rounded-circle" />
                                                                                                            @else
                                                                                                                <img src="{{ $applicationUserAvatar }}" alt="" class="avatar-xs rounded-circle" />
                                                                                                            @endif
                                                                                                        </div>
                                                                                                        <div class="flex-grow-1 ms-3">
                                                                                                            <h6 class="fs-14 mb-1">
                                                                                                                <span class="text-secondary">
                                                                                                                    @if($activity->causer_id == $user->id)
                                                                                                                        {{ $vacancyUserName }}
                                                                                                                    @else
                                                                                                                        {{ $applicationUserName }}
                                                                                                                    @endif
                                                                                                                </span>
                                                                                                            </h6>
                                                                                                            @if($activity->causer_id == $user->id)
                                                                                                                <small class="text-muted">
                                                                                                                    {{ $newApprovalStatus === "Yes" ? "Approved" : "Declined"}} sent connection request {{ $activity->created_at->diffForHumans() }}
                                                                                                                </small>
                                                                                                            @else
                                                                                                                <small class="text-muted">
                                                                                                                    {{ $newApprovalStatus === "Yes" ? "Approved" : "Declined"}} recieved connection request {{ $activity->created_at->diffForHumans() }}
                                                                                                                </small>
                                                                                                            @endif
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </a>
                                                                                            </div>
                                                                                            <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                                <div class="accordion-body ms-2 ps-5">
                                                                                                    @if($activity->subject)
                                                                                                        <h6 class="fs-14 mb-2">
                                                                                                            <span class="text-primary">
                                                                                                                {{ optional($activity->subject)->vacancy->position->name ?? 'N/A' }}
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <p class="text-muted mb-3">
                                                                                                            <i class="ri-map-pin-line"></i> {{ optional($activity->subject)->vacancy->store->brand->name ?? 'N/A' }} ({{ optional($activity->subject)->vacancy->store->town->name ?? 'N/A' }})
                                                                                                        </p>
                                                                                                        <p class="text-muted mb-3">
                                                                                                            <i class="ri-flag-line"></i> {{ optional($activity->subject)->vacancy->type->name ?? 'N/A' }}
                                                                                                        </p>
                                                                                                        <p class="text-muted mb-1">
                                                                                                            {!! optional($activity->subject)->vacancy->position->description ?? 'N/A' !!}
                                                                                                        </p>
                                                                                                        <h6 class="fs-14 mb-2">
                                                                                                            <span class="text-primary">
                                                                                                                {{ optional($activity->subject)->opportunity->name ?? 'N/A' }}
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        {!! optional($activity->subject)->opportunity->description ?? 'N/A' !!}
                                                                                                    @endif
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endif

                                                                                @endif

                                                                            <!-------------------------------------------------------------------------------------
                                                                                Deleted
                                                                            -------------------------------------------------------------------------------------->

                                                                            @elseif($activity->event === "deleted")
                                                                                @if ($activity->subject_type === "App\Models\Vacancy")
                                                                                    @php
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
                                                                                    @endphp

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle {{ $bgClass }}">
                                                                                                                <i class="{{ $iconClass }}"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-danger">
                                                                                                                {{ $positionName }}
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Deleted {{ $activity->created_at->diffForHumans() }}
                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-map-pin-line"></i> {{ $brandName }} ({{ $townName }})
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-flag-line"></i> {{ $typeName }}
                                                                                                </p>
                                                                                                <p class="text-muted mb-1">
                                                                                                    {!! $positionDescription !!}
                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @elseif ($activity->subject_type === "App\Models\Message")
                                                                                    @php
                                                                                        $message = isset($activityAttributes['old']['message']) ? $activityAttributes['old']['message'] : 'N/A';
                                                                                        $userTo = $activity->userForDeletedMessage;
                                                                                        $userToName = $userTo ? $userTo->firstname . ' ' . $userTo->lastname : 'N/A';
                                                                                        $userToAvatar = $userTo ? URL::asset('storage/images/' . $userTo->avatar) : URL::asset('images/avatar.jpg');
                                                                                    @endphp

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <img src="{{$userToAvatar}}" alt="" class="avatar-xs rounded-circle" />
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-danger">
                                                                                                                {{ $userToName }}
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Deleted message {{ $activity->created_at->diffForHumans() }}
                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                {!! $message !!}
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                @endif

                                                                            <!-------------------------------------------------------------------------------------
                                                                                Viewed
                                                                            -------------------------------------------------------------------------------------->

                                                                            @else
                                                                                @if($activity->accessedVacancy)
                                                                                    @php
                                                                                        $vacancy = $activity->accessedVacancy;
                                                                                        $positionName = optional($vacancy->position)->name ?? 'N/A';
                                                                                        $positionDescription = optional($vacancy->position)->description ?? 'N/A';
                                                                                        $brandName = optional($vacancy->store->brand)->name ?? 'N/A';
                                                                                        $townName = optional($vacancy->store->town)->name ?? 'N/A';
                                                                                        $typeName = optional($vacancy->type)->name ?? 'N/A';
                                                                                    @endphp

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle {{ $bgClass }}">
                                                                                                                <i class="{{ $iconClass }}"></i>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-info">
                                                                                                                {{ $positionName }}
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Viewed {{ $activity->created_at->diffForHumans() }}
                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-map-pin-line"></i> {{ $brandName }} ({{ $townName }})
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-flag-line"></i> {{ $typeName }}
                                                                                                </p>
                                                                                                <p class="text-muted mb-1">
                                                                                                    {!! $positionDescription !!}
                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @elseif($activity->accessedApplicant)
                                                                                    @php
                                                                                        $applicant = $activity->accessedApplicant;
                                                                                        $applicantPosition = $applicant->position ?? null;
                                                                                        $applicantPositionName = $applicantPosition ? $applicantPosition->name : 'N/A';
                                                                                        if ($applicantPositionName === "Other") {
                                                                                            $applicantPositionName = $applicant->position_specify ?? 'N/A';
                                                                                        }
                                                                                        $firstname = $applicant->firstname ?? 'N/A';
                                                                                        $lastname = $applicant->lastname ?? 'N/A';
                                                                                        $avatar = $applicant->avatar ?? URL::asset('images/avatar.jpg');
                                                                                    @endphp

                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <img src="{{ $avatar }}" alt="" class="avatar-xs rounded-circle" />
                                                                                                    </div>
                                                                                                    <div class="flex-grow-1 ms-3">
                                                                                                        <h6 class="fs-14 mb-1">
                                                                                                            <span class="text-info">
                                                                                                                {{ $firstname }} {{ $lastname }}
                                                                                                            </span>
                                                                                                        </h6>
                                                                                                        <small class="text-muted">
                                                                                                            Viewed {{ $activity->created_at->diffForHumans() }}
                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div id="activity{{ $activity->id }}" class="accordion-collapse collapse show" aria-labelledby="activityHeading{{ $activity->id }}">
                                                                                            <div class="accordion-body ms-2 ps-5">
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-user-2-line"></i> {{ $firstname }} {{ $lastname }}
                                                                                                </p>
                                                                                                <p class="text-muted mb-3">
                                                                                                    <i class="ri-briefcase-line"></i> {{ $applicantPositionName }}
                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @else
                                                                                    <div class="accordion-item border-0">                                                                            
                                                                                        <div class="accordion-header" id="activityHeading{{ $activity->id }}">
                                                                                            <a class="accordion-button p-2 shadow-none" data-bs-toggle="collapse" href="#activity{{ $activity->id }}" aria-expanded="true">
                                                                                                <div class="d-flex">
                                                                                                    <div class="flex-shrink-0">
                                                                                                        <div class="avatar-xs acitivity-avatar">
                                                                                                            <div class="avatar-title rounded-circle {{ $bgClass }}">
                                                                                                                <i class="{{ $iconClass }}"></i>
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
                                                                                                            Viewed {{ $activity->created_at->diffForHumans() }}
                                                                                                        </small>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif

                                                                            @endif

                                                                        @endif
                                                                    @endforeach                                                                
                                                                </div>
                                                                <!--end accordion-->
                                                            </div>
                                                        </div>
                                                    @endforeach                                                    
                                                </div>
                                            </div><!-- end card body -->
                                        </div><!-- end card -->
                                    </div><!-- end col -->
                                </div><!-- end row -->
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
                                @if ($user->applicant)
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
                                                                        {{ $user->applicant->firstname ?? 'N/A' }} {{ $user->applicant->lastname }}
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
                                                                        {{ $user->applicant->id_number ?? 'N/A' }}
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
                                                                        {{ $user->applicant->contact_number ?? 'N/A' }}
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
                                                                        {{ $user->applicant->email ?? 'N/A' }}
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
                                                                        {{ $user->applicant->location ?? 'N/A' }}
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
                                                                        {{ optional($user->applicant->town)->name ?? 'N/A' }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4">
                                                                <!-- Gender -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-6">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Gender
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        {{ optional($user->applicant->gender)->name ?? 'N/A' }}
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
                                                                        {{ optional($user->applicant->race)->name ?? 'N/A' }}
                                                                    </div>
                                                                </div>                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-------------------------------------------------------------------------------------
                                                Job Information
                                            -------------------------------------------------------------------------------------->

                                            <div class="accordion-item mt-2">
                                                <h2 class="accordion-header" id="accordionborderedExample2">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accor_borderedExamplecollapse2" aria-expanded="false" aria-controls="accor_borderedExamplecollapse2">
                                                        Job Information
                                                    </button>
                                                </h2>
                                                <div id="accor_borderedExamplecollapse2" class="accordion-collapse collapse" aria-labelledby="accordionborderedExample2" data-bs-parent="#accordionBordered">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <!-- Highest Qualification -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Highest Qualification
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        {{ optional($user->applicant->education)->name ?? 'N/A' }}
                                                                    </div>
                                                                </div>

                                                                <!-- Retail Experience -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Retail Experience
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        {{ optional($user->applicant->duration)->name ?? 'N/A' }}
                                                                    </div>
                                                                </div>

                                                                <!-- Type of Store -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Type of Store
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        @if($user->applicant && $user->applicant->brands && $user->applicant->brands->isNotEmpty())
                                                                            {{ $user->applicant->brands->pluck('name')->implode(', ') }}
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-6">
                                                                <!-- Rotational Shift -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Rotational Shift 
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        {{ optional($user->applicant)->public_holidays ?? 'N/A' }}
                                                                    </div>
                                                                </div>

                                                                <!-- Heavy Lifting -->
                                                                <div class="row mb-3">
                                                                    <div class="col-lg-3">
                                                                        <h6 class="fs-15 mb-0">
                                                                            Heavy Lifting 
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-lg-9">
                                                                        {{ optional($user->applicant)->environment ?? 'N/A' }}
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
                                                                        {{ optional($user->applicant)->disability ?? 'N/A' }}
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
                                @endif
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

                                    <div class="col-xl-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Situational Score</h4>
                                            </div><!-- end card header -->
                            
                                            <div class="card-body">
                                                <div id="situational_chart" data-colors='["--vz-danger"]' class="apex-charts" dir="ltr"></div>
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

                    <div class="tab-pane fade d-none" id="applications-tab" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <h5 class="card-title flex-grow-1 mb-0">Job Applications</h5>
                                </div>
                                <div class="row">
                                    @foreach ($user->appliedVacancies as $vacancy)
                                        <div class="col-xxl-3 col-sm-6">
                                            <div class="card profile-project-card shadow-none profile-project-{{ $vacancy->position->color }}">
                                                <div class="card-body p-4">
                                                    <div class="d-flex">
                                                        <div class="flex-grow-1 text-muted overflow-hidden">
                                                            <h5 class="fs-15 text-truncate">
                                                                <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)]) }}" class="text-body">
                                                                    {{ $vacancy->position->name }}
                                                                </a>
                                                            </h5>
                                                            <p class="text-muted text-truncate mb-2">
                                                                Location : 
                                                                <span class="fw-semibold text-body">
                                                                    {{ $vacancy->store->brand->name }} ({{ $vacancy->store->town->name }})
                                                                </span>
                                                            </p>
                                                            <p class="text-muted text-truncate mb-2">
                                                                Type : 
                                                                <span class="fw-semibold text-body">
                                                                    {{ $vacancy->type->name }}
                                                                </span>
                                                            </p>
                                                            <p class="text-muted text-truncate mb-0">
                                                                Posted : 
                                                                <span class="fw-semibold text-body">
                                                                    {{ $vacancy->created_at->diffForHumans() }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="flex-shrink-0 ms-2">
                                                            <div class="badge bg-{{ $vacancy->status->color }}-subtle text-{{ $vacancy->status->color }} fs-12">
                                                                {{ $vacancy->status->name }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex mt-4">
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div>
                                                                    <h5 class="fs-13 text-muted mb-0">Candidates :</h5>
                                                                </div>
                                                                <div class="avatar-group">
                                                                    @foreach($vacancy->applicants as $applicant)
                                                                        <div class="avatar-group-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="{{ $applicant->firstname }} {{ $applicant->lastname }}">
                                                                            <div class="avatar-xs">
                                                                                <img src="{{ URL::asset('storage/images/' . $applicant->avatar) }}" class="rounded-circle img-fluid" />
                                                                            </div>
                                                                        </div>
                                                                    @endforeach                                                                   
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end card body -->
                                            </div>
                                            <!-- end card -->
                                        </div>
                                        <!--end col-->
                                    @endforeach                                    
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
                                                    @foreach ($user->files as $file)
                                                        @php
                                                            $fileIcon = '';
                                                        @endphp
                                                        
                                                        @switch($file->type)
                                                            @case('png')
                                                            @case('jpg')
                                                            @case('jpeg')
                                                                @php
                                                                    $fileIcon = '<i class="ri-gallery-fill align-bottom text-success"></i>';
                                                                @endphp
                                                                @break
                                                        
                                                            @case('pdf')
                                                                @php
                                                                    $fileIcon = '<i class="ri-file-pdf-fill align-bottom text-danger"></i>';
                                                                @endphp
                                                                @break
                                                        
                                                            @case('docx')
                                                                @php
                                                                    $fileIcon = '<i class="ri-file-word-2-fill align-bottom text-primary"></i>';
                                                                @endphp
                                                                @break
                                                        
                                                            @case('xls')
                                                            @case('xlsx')
                                                                @php
                                                                    $fileIcon = '<i class="ri-file-excel-2-fill align-bottom text-success"></i>';
                                                                @endphp
                                                                @break
                                                        
                                                            @case('csv')
                                                                @php
                                                                    $fileIcon = '<i class="ri-file-excel-fill align-bottom text-success"></i>';
                                                                @endphp
                                                                @break
                                                        
                                                            @case('txt')
                                                            @default
                                                                @php
                                                                    $fileIcon = '<i class="ri-file-text-fill align-bottom text-secondary"></i>';
                                                                @endphp
                                                        @endswitch
                                                        <tr data-file-id="{{ $file->id }}">
                                                            <td>
                                                                <a href="{{ route('document.view', ['id' => Crypt::encryptString($file->id)]) }}" target="_blank">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="flex-shrink-0 fs-17 me-2 filelist-icon">{!! $fileIcon !!}</div>
                                                                        <div class="flex-grow-1 filelist-name">{{ substr($file->name, 0, strrpos($file->name, '-')) }}</div>
                                                                    </div>
                                                                </a>
                                                            </td>
                                                            <td>
                                                                {{ $file->type }}
                                                            </td>
                                                            @php
                                                                $fileSizeInMB = $file->size / (1024 * 1024);
                                                                if ($fileSizeInMB < 0.1) {
                                                                    $fileSizeInKB = number_format($file->size / 1024, 1);
                                                                    $fileSizeText = "{$fileSizeInKB} KB";
                                                                } else {
                                                                    $fileSizeInMB = number_format($fileSizeInMB, 1);
                                                                    $fileSizeText = "{$fileSizeInMB} MB";
                                                                }
                                                            @endphp
                                                            <td class="filelist-size">                                            
                                                                {{ $fileSizeText }}
                                                            </td>
                                                            <td class="filelist-create">
                                                                {{ date('d M Y', strtotime($file->created_at)) }}
                                                            </td>
                                                            <td>
                                                                <div class="d-flex gap-3 justify-content-center">
                                                                    <div class="dropdown">
                                                                        <button class="btn btn-light btn-icon btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i class="ri-more-fill align-bottom"></i>
                                                                        </button>
                                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                                        <li>
                                                                            <a class="dropdown-item viewfile-list" href="{{ route('document.view', ['id' => Crypt::encryptString($file->id)]) }}" target="_blank">
                                                                                View
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item downloadfile-list" href="{{ route('document.download', ['id' => Crypt::encryptString($file->id)]) }}">
                                                                                Download
                                                                            </a>
                                                                        </li>
                                                                        <li class="dropdown-divider"></li>
                                                                        <li>
                                                                            <button class="dropdown-item downloadfile-list" href="#fileDeleteModal" data-bs-toggle="modal" data-bs-id="{{ $file->id }}">
                                                                                Delete
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
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
@endsection
@section('script')
    <script>
        var literacyScore = {{ $applicant->literacy_score ?? 0 }};            
        var literacyQuestions = {{ $applicant->literacy_questions ?? 10 }};
        var literacy = "{{ $applicant->literacy  ?? 0/10 }}";

        var numeracyScore = {{ $applicant->numeracy_score ?? 0 }};
        var numeracyQuestions = {{ $applicant->numeracy_questions  ?? 10 }};
        var numeracy = "{{ $applicant->numeracy ?? 0/10 }}";

        var situationalScore = {{ $applicant->situational_score ?? 0 }};
        var situationalQuestions = {{ $applicant->situational_questions ?? 5 }};
        var situational = "{{ $applicant->situational ?? 0/5 }}";
    </script>

    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/user-profile.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
