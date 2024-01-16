@extends('layouts.master')
@section('title') @lang('translation.chat') @endsection
@section('css')
<link rel="stylesheet" href="{{ URL::asset('build/libs/glightbox/css/glightbox.min.css')}}">

@endsection
@section('content')

<div class="chat-wrapper d-lg-flex gap-1 mx-n4 mt-n4 p-1">
    <div class="chat-leftsidebar">
        <div class="px-4 pt-4 mb-4">
            <div class="d-flex align-items-start">
                <div class="flex-grow-1">
                    <h5 class="mb-4">Chats</h5>
                </div>
            </div>
            <div class="search-box">
                <input type="text" class="form-control bg-light border-light" placeholder="Search here...">
                <i class="ri-search-2-line search-icon"></i>
            </div>

        </div> <!-- .p-4 -->

        <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#chats" role="tab">
                    Chats
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#contacts" role="tab">
                    Contacts
                </a>
            </li>
        </ul>

        <div class="tab-content text-muted">
            <div class="tab-pane active" id="chats" role="tabpanel">
                <div class="chat-room-list pt-3" data-simplebar>
                    <div class="d-flex align-items-center px-4 mb-2">
                        <div class="flex-grow-1">
                            <h4 class="mb-0 fs-12 text-muted text-uppercase">Direct Messages</h4>
                        </div>
                    </div>

                    <div class="chat-message-list">

                        <ul class="list-unstyled chat-list chat-user-list" id="userList">

                        </ul>
                    </div>

                    <div class="chat-message-list">

                        <ul class="list-unstyled chat-list chat-user-list mb-0" id="channelList">
                        </ul>
                    </div>
                    <!-- End chat-message-list -->
                </div>
            </div>
            <div class="tab-pane" id="contacts" role="tabpanel">
                <div class="chat-room-list pt-3" data-simplebar>
                    <div class="sort-contact">
                    </div>
                </div>
            </div>
        </div>
        <!-- end tab contact -->
    </div>
    <!-- end chat leftsidebar -->
    <!-- Start User chat -->
    <div class="user-chat w-100 overflow-hidden">

        <div class="chat-content d-lg-flex">
            <!-- start chat conversation section -->
            <div class="w-100 overflow-hidden position-relative">
                <!-- conversation user -->
                <div class="position-relative">
                    <div class="position-relative" id="users-chat">
                        <div class="p-3 user-chat-topbar">
                            <div class="row align-items-center">
                                <div class="col-sm-4 col-8">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 d-block d-lg-none me-3">
                                            <a href="javascript: void(0);" class="user-chat-remove fs-18 p-1">
                                                <i class="ri-arrow-left-s-line align-bottom"></i>
                                            </a>
                                        </div>
                                        @if ($contactUser || $user)
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 chat-user-img {{ ($contactUser->status_id ?? $user->status_id) == 1 ? 'online' : 'away' }} user-own-img align-self-center me-3 ms-0">
                                                        <img src="{{ URL::asset(isset($contactUser->avatar) ? 'images/'.$contactUser->avatar : (isset($user->avatar) ? 'images/'.$user->avatar : 'build/images/users/user-dummy-img.jpg')) }}" class="rounded-circle avatar-xs" alt="">
                                                        <span class="user-status"></span>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate mb-0 fs-16">
                                                            <a class="text-reset username" data-bs-toggle="offcanvas" href="#userProfileCanvasExample" aria-controls="userProfileCanvasExample">
                                                                {{ $contactUser->firstname ?? $user->firstname }} {{ $contactUser->lastname ?? $user->lastname }}
                                                            </a>
                                                        </h5>
                                                        <p class="text-truncate text-muted mb-0 userStatus">
                                                            <small>{{ ($contactUser->status_id ?? $user->status_id) == 1 ? 'Online' : 'Offline' }}</small>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 chat-user-img away user-own-img align-self-center me-3 ms-0">
                                                        <img src="build/images/users/user-dummy-img.jpg" class="rounded-circle avatar-xs" alt="">
                                                        <span class="user-status"></span>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate mb-0 fs-16">
                                                            <a class="text-reset username" data-bs-toggle="offcanvas" href="#userProfileCanvasExample" aria-controls="userProfileCanvasExample">
                                                                
                                                            </a>
                                                        </h5>
                                                        <p class="text-truncate text-muted mb-0 userStatus">
                                                            <small></small>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-8 col-4">
                                    <ul class="list-inline user-chat-nav text-end mb-0">
                                        <li class="list-inline-item d-none d-lg-inline-block m-0">
                                            <button type="button" class="btn btn-ghost-secondary btn-icon" data-bs-toggle="offcanvas" data-bs-target="#userProfileCanvasExample" aria-controls="userProfileCanvasExample">
                                                <i data-feather="info" class="icon-sm"></i>
                                            </button>
                                        </li>

                                        <li class="list-inline-item m-0">
                                            <div class="dropdown">
                                                <button class="btn btn-ghost-secondary btn-icon" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" class="icon-sm"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item d-block d-lg-none user-profile-show" href=""><i class="ri-user-2-fill align-bottom text-muted me-2"></i> View Profile</a>
                                                    <a class="dropdown-item" href="javascript:void(0);"><i class="ri-inbox-archive-line align-bottom text-muted me-2"></i> Archive</a>
                                                    <a class="dropdown-item" href="javascript:void(0);"><i class="ri-mic-off-line align-bottom text-muted me-2"></i> Muted</a>
                                                    <a class="dropdown-item" href="javascript:void(0);"><i class="ri-delete-bin-5-line align-bottom text-muted me-2"></i> Delete</a>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        <!-- end chat user head -->
                        <div class="chat-conversation p-3 p-lg-4 " id="chat-conversation" data-simplebar>
                            <div id="elmLoader">
                                <div class="spinner-border text-primary avatar-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <ul class="list-unstyled chat-conversation-list" id="users-conversation">

                            </ul>
                            <!-- end chat-conversation-list -->
                        </div>
                        <div class="alert alert-warning alert-dismissible copyclipboard-alert px-4 fade show " id="copyClipBoard" role="alert">
                            Message copied
                        </div>
                    </div>

                    <div class="position-relative" id="channel-chat">
                        <div class="p-3 user-chat-topbar">
                            <div class="row align-items-center">
                                <div class="col-sm-4 col-8">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 d-block d-lg-none me-3">
                                            <a href="javascript: void(0);" class="user-chat-remove fs-18 p-1"><i class="ri-arrow-left-s-line align-bottom"></i></a>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 chat-user-img online user-own-img align-self-center me-3 ms-0">
                                                    <img src="{{URL::asset('build/images/users/avatar-2.jpg')}}" class="rounded-circle avatar-xs" alt="">
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <h5 class="text-truncate mb-0 fs-16"><a class="text-reset username" data-bs-toggle="offcanvas" href="#userProfileCanvasExample" aria-controls="userProfileCanvasExample">Lisa Parker</a></h5>
                                                    <p class="text-truncate text-muted fs-14 mb-0 userStatus"><small>24 Members</small></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-8 col-4">
                                    <ul class="list-inline user-chat-nav text-end mb-0">
                                        <li class="list-inline-item m-0">
                                            <div class="dropdown">
                                                <button class="btn btn-ghost-secondary btn-icon" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="search" class="icon-sm"></i>
                                                </button>
                                                <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                                                    <div class="p-2">
                                                        <div class="search-box">
                                                            <input type="text" class="form-control bg-light border-light" placeholder="Search here..." onkeyup="searchMessages()" id="searchMessage">
                                                            <i class="ri-search-2-line search-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>

                                        <li class="list-inline-item d-none d-lg-inline-block m-0">
                                            <button type="button" class="btn btn-ghost-secondary btn-icon" data-bs-toggle="offcanvas" data-bs-target="#userProfileCanvasExample" aria-controls="userProfileCanvasExample">
                                                <i data-feather="info" class="icon-sm"></i>
                                            </button>
                                        </li>

                                        <li class="list-inline-item m-0">
                                            <div class="dropdown">
                                                <button class="btn btn-ghost-secondary btn-icon" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" class="icon-sm"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item d-block d-lg-none user-profile-show" href=""><i class="ri-user-2-fill align-bottom text-muted me-2"></i> View Profile</a>
                                                    <a class="dropdown-item" href="javascript: void(0);"><i class="ri-inbox-archive-line align-bottom text-muted me-2"></i> Archive</a>
                                                    <a class="dropdown-item" href="javascript: void(0);"><i class="ri-mic-off-line align-bottom text-muted me-2"></i> Muted</a>
                                                    <a class="dropdown-item" href="javascript: void(0);"><i class="ri-delete-bin-5-line align-bottom text-muted me-2"></i> Delete</a>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        <!-- end chat user head -->
                        <div class="chat-conversation p-3 p-lg-4" id="chat-conversation" data-simplebar>
                            <ul class="list-unstyled chat-conversation-list" id="channel-conversation">
                            </ul>
                            <!-- end chat-conversation-list -->

                        </div>
                        <div class="alert alert-warning alert-dismissible copyclipboard-alert px-4 fade show " id="copyClipBoardChannel" role="alert">
                            Message copied
                        </div>
                    </div>

                    <!-- end chat-conversation -->

                    <div class="chat-input-section p-3 p-lg-4">

                        <form id="chatinput-form" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-0 align-items-center">
                                <div class="col-auto">
                                    <div class="chat-input-links me-2">
                                        <div class="links-list-item">
                                            <button type="button" class="btn btn-link text-decoration-none emoji-btn" id="emoji-btn">
                                                <i class="bx bx-smile align-middle"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="chat-input-feedback">
                                        Please Enter a Message
                                    </div>
                                    <input type="text" class="form-control chat-input bg-light border-light" id="chat-input" data-bs-id="{{ $contactUser->id ?? ($user->id ?? 0) }}" placeholder="Type your message..." autocomplete="off">
                                </div>
                                <div class="col-auto">
                                    <div class="chat-input-links ms-2">
                                        <div class="links-list-item">
                                            <button type="submit" class="btn btn-success chat-send waves-effect waves-light">
                                                <i class="ri-send-plane-2-fill align-bottom"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>

                    <div class="replyCard">
                        <div class="card mb-0">
                            <div class="card-body py-3">
                                <div class="replymessage-block mb-0 d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="conversation-name"></h5>
                                        <p class="mb-0"></p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <button type="button" id="close_toggle" class="btn btn-sm btn-link mt-n2 me-n3 fs-18">
                                            <i class="bx bx-x align-middle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end chat-wrapper -->

<div class="offcanvas offcanvas-end border-0" tabindex="-1" id="userProfileCanvasExample">
    <!--end offcanvas-header-->
    <div class="offcanvas-body profile-offcanvas p-0">
        <div class="team-cover">
            <img src="{{ URL::asset('build/images/small/img-9.jpg') }}" alt="" class="img-fluid" />
        </div>
        <div class="p-1 pb-4 pt-0">
            <div class="team-settings">
                <div class="row g-0">
                    <div class="col">
                        <div class="btn nav-btn">
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end col-->
        </div>
        <div class="p-3 text-center">
            <img src="{{ URL::asset(isset($contactUser->avatar) ? 'images/'.$contactUser->avatar : (isset($user->avatar) ? 'images/'.$user->avatar : 'build/images/users/user-dummy-img.jpg')) }}" alt="" class="avatar-lg img-thumbnail rounded-circle mx-auto profile-img">
            <div class="mt-3">
                <h5 class="fs-16 mb-1">
                    <a href="javascript:void(0);" class="link-primary username">
                        {{ $contactUser->firstname ?? ($user->firstname ?? '') }} {{ $contactUser->lastname ?? ($user->lastname ?? '') }}
                    </a>
                </h5>
                <p class="text-muted userStatus">
                    @php
                        $statusId = $contactUser->status_id ?? ($user->status_id ?? null);
                        $statusText = $statusId === 1 ? 'Online' : 'Offline';
                        $statusColor = $statusId === 1 ? 'success' : 'warning';
                    @endphp
                    <i class="ri-checkbox-blank-circle-fill me-1 align-bottom text-{{ $statusColor }}"></i>
                    {{ $statusText }}
                </p>
            </div>            
            <div class="d-flex gap-3 justify-content-center">
                <p class="text-muted userLastSeen">
                    @if($contactUser && $contactUser->status_id === 2)
                        last seen {{ $contactUser->updated_at->diffForHumans() }}
                    @elseif($user && $user->status_id === 2)
                        last seen {{ $user->updated_at->diffForHumans() }}
                    @endif
                </p>
            </div>            
        </div>

        <div class="border-top border-top-dashed p-3">
            <h5 class="fs-16 mb-3">Personal Details</h5>
            <div class="mb-3">
                <p class="text-muted text-uppercase fw-semibold fs-13 mb-1">Phone Number</p>
                <h6 class="userPhone">{{ $contactUser->phone ?? ($user->phone ?? '') }}</h6>
            </div>
            <div class="mb-3">
                <p class="text-muted text-uppercase fw-semibold fs-13 mb-1">Email</p>
                <h6 class="userEmail">{{ $contactUser->email ?? ($user->email ?? '') }}</h6>
            </div>
            <div class="mb-3">
                <p class="text-muted text-uppercase fw-semibold fs-13 mb-1">Company</p>
                <h6 class="userCompany">{{ $contactUser->company->name ?? ($user->company->name ?? '') }}</h6>
            </div>
            <div class="mb-3">
                <p class="text-muted text-uppercase fw-semibold fs-13 mb-1">Position</p>
                <h6 class="userPosition">{{ $contactUser->posistion->name ?? ($user->position->name ?? '') }}</h6>
            </div>
        </div>

    </div>
    <!--end offcanvas-body-->
</div>
<!--end offcanvas-->
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/glightbox/js/glightbox.min.js') }}"></script>

    <!-- fgEmojiPicker js -->
    <script src="{{ URL::asset('build/libs/fg-emoji-picker/fgEmojiPicker.js') }}"></script>

<!-- chat init js -->
<script src="{{ URL::asset('build/js/pages/message.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
