@extends('layouts.master')
@section('title')
    @lang('translation.contacts')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pages
        @endslot
        @slot('title')
            Interviews
        @endslot
    @endcomponent
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
                                    <option value="{{ count($interviews) }}">All</option>
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
                                    @if($interviews && count($interviews) > 0)
                                        @foreach ($interviews as $interview)
                                            <tr class="{{ $interview->status == 'Appointed' ? 'border border-success' : '' }}" style="vertical-align:middle; {{ $interview->status == 'Appointed' ? 'border-top: 2px solid rgba(103, 177, 115, 1) !important;' : '' }}">
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="chk_child" value="option1">
                                                    </div>
                                                </th>
                                                <td class="id d-none">{{ Crypt::encryptstring($interview->id) }}</td>
                                                <td class="interview"><span>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            @if ($user->id == $interview->interviewer_id)
                                                                <div class="flex-shrink-0 col-auto">
                                                                    <div class="avatar-sm rounded overflow-hidden">
                                                                        <img src="{{ URL::asset(optional($interview->applicant)->avatar ?? 'images/avatar.jpg') }}" alt="" class="member-img img-fluid d-block rounded">
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="avatar-sm bg-{{ optional(optional($interview->vacancy)->position)->color ?? 'primary' }}-subtle rounded p-1">
                                                                    <span class="avatar-title bg-{{ optional(optional($interview->vacancy)->position)->color ?? 'primary' }}-subtle text-{{ optional(optional($interview->vacancy)->position)->color ?? 'primary' }} fs-4">
                                                                        <i class="{{ optional(optional($interview->vacancy)->position)->icon ?? 'ri-briefcase-line' }}"></i>
                                                                    </span>
                                                                </div>
                                                            @endif                                                            
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            @if ($user->id == $interview->interviewer_id)
                                                                <h5 class="fs-14 mb-1">
                                                                    <a href="{{ route('applicant-profile.index', ['id' => Crypt::encryptString($interview->applicant_id)]) }}" class="text-body">
                                                                        {{ optional($interview->applicant)->firstname ?? 'N/A' }} {{ optional($interview->applicant)->lastname ?? 'N/A' }}
                                                                    </a>
                                                                </h5>
                                                                <p class="text-muted mb-0">
                                                                    {{ optional(optional($interview->vacancy)->position)->name ?? 'N/A' }}
                                                                </p>
                                                            @else
                                                                <h5 class="fs-14 mb-1">
                                                                    <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($interview->vacancy_id)]) }}" class="text-body">
                                                                        {{ optional(optional($interview->vacancy)->position)->name ?? 'N/A' }}
                                                                    </a>
                                                                </h5>
                                                                <p class="text-muted mb-0">
                                                                    {{ optional(optional(optional($interview->vacancy)->store)->brand)->name ?? 'N/A' }} ({{ optional(optional(optional($interview->vacancy)->store)->town)->name ?? 'N/A' }})
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </span></td>
                                                <td class="scheduled_date"><span>{{ $interview->scheduled_date ? date('d M Y', strtotime($interview->scheduled_date)) : 'Not Scheduled' }}<small class="text-muted ms-1">{{ $interview->start_time ? date('h:i A', strtotime($interview->start_time)) : 'No Time Set' }}</small></span></td>
                                                <td class="location">{{ $interview->location }}</td>
                                                <td class="notes" style="white-space: pre-wrap;">{{ $interview->notes }}</td>
                                                <td class="reschedule_date"><span>{!! $interview->reschedule_date ? date('d M Y', strtotime($interview->reschedule_date)) . '<small class="text-muted ms-1">' . date('h:i A', strtotime($interview->reschedule_date)) . '</small>' : '' !!}</span></td>
                                                @php
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
                                                        case 'Appointed':
                                                            $color = 'bg-success-subtle text-success';
                                                            break;
                                                        case 'Regretted':
                                                            $color = 'bg-danger-subtle text-danger';
                                                            break;
                                                        default:
                                                            $color = 'bg-secondary-primary text-primary';
                                                    }
                                                @endphp
                                                <td class="status"><span class="badge {{ $color }} text-uppercase">{{ $interview->status }}</span></td>
                                                <td>
                                                    @if (!in_array($interview->status, ['Completed', 'Appointed', 'Regretted', 'Cancelled', 'No Show']))
                                                        <ul class="list-inline hstack gap-2 mb-0">
                                                            <li class="list-inline-item">
                                                                <div class="dropdown">
                                                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <i class="ri-more-fill align-middle"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                                        @if (($user->role_id <= 6 && $interview->status == 'Reschedule' && $interview->reschedule_by == 'Applicant') || ($user->role_id > 6 && ($interview->status == 'Scheduled' || ($interview->status == 'Reschedule' && $interview->reschedule_by == 'Manager'))))
                                                                            <li>
                                                                                <a class="dropdown-item confirm-item-btn" data-bs-toggle="modal" href="#interviewConfirmModal">
                                                                                    <i class="ri-checkbox-circle-fill align-bottom me-2 text-success"></i>
                                                                                    Confirm
                                                                                </a>
                                                                            </li>
                                                                        @endif
                                                                        @if ($user->role_id > 6 && in_array($interview->status, ['Scheduled', 'Reschedule', 'Confirmed']))
                                                                            <li>
                                                                                <a class="dropdown-item decline-item-btn" data-bs-toggle="modal" href="#interviewDeclineModal">
                                                                                    <i class="ri-close-circle-fill align-bottom me-2 text-danger"></i>
                                                                                    Decline
                                                                                </a>
                                                                            </li>
                                                                        @endif
                                                                        @if (in_array($interview->status, ['Scheduled', 'Reschedule', 'Confirmed']))
                                                                            <li>
                                                                                <a class="dropdown-item reschedule-item-btn" data-bs-toggle="modal" href="#interviewRescheduleModal">
                                                                                    <i class="ri-calendar-event-fill align-bottom me-2 text-info"></i>
                                                                                    Reschedule
                                                                                </a>
                                                                            </li>
                                                                        @endif
                                                                        @if ($user->role_id <= 6)
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
                                                                        @endif
                                                                    </ul>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
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
                                                                @if ($user->role_id > 6)
                                                                    <li>
                                                                        <a class="dropdown-item decline-item-btn" data-bs-toggle="modal" href="#interviewDeclineModal">
                                                                            <i class="ri-close-circle-fill align-bottom me-2 text-danger"></i>
                                                                            Decline
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                <li>
                                                                    <a class="dropdown-item reschedule-item-btn" data-bs-toggle="modal" href="#interviewRescheduleModal">
                                                                        <i class="ri-calendar-event-fill align-bottom me-2 text-info"></i>
                                                                        Reschedule
                                                                    </a>
                                                                </li>
                                                                @if ($user->role_id <= 6)
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
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    @endif
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
                                            You are about to confirm this interview?
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
                                                Yes, Confirm!
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
                                            You are about to decline this interview?
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
                                                Yes, Decline!
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
                                            You are about to reschedule this interview?
                                        </h4>
                                        <p class="text-muted fs-14 mb-2 pt-1">
                                            Please select a new date and time to proceed with rescheduling. This action will notify all relevant parties of the change.
                                        </p>
                                        <input type="text" class="form-control flatpickr-input active" id="rescheduleTime" name="reschedule_time" readonly="readonly" required>
                                        <span class="invalid-feedback d-none" role="alert"><strong>Please select a date and time.</strong></span>
                                        <div class="hstack gap-2 justify-content-center remove mt-4">
                                            <button class="btn btn-light" data-bs-dismiss="modal" id="rescheduleInterview-close">
                                                <i class="ri-close-line me-1 align-middle"></i>
                                                Close
                                            </button>
                                            <button class="btn btn-info" id="reschedule-interview">
                                                Yes, Reschedule!
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end reschedule modal -->

                    @if ($user->role_id <= 6)
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
                                                You are about to cancel this interview?
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
                                                    Yes, Cancel!
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
                                                You are about to mark this interview as a no show?
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
                                                    Yes, No Show!
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end noShow modal -->
                    @endif
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/@simonwep/pickr/pickr.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/interviews.init.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
