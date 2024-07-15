@extends('layouts.master')
@section('title')
    @lang('translation.list-view')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Applicant
    @endslot
    @slot('title')
        Approvals
    @endslot
@endcomponent

<div class="row">
    <div class="col-xxl-3 col-sm-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-semibold text-muted mb-0">
                            Total Applicants
                        </p>
                        <h2 class="mt-4 ff-secondary fw-semibold">
                            <span class="counter-value" data-target="3.24">
                                0
                            </span>k
                        </h2>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-light text-success mb-0">
                                <i class="ri-arrow-up-line align-middle"></i> 
                                20.50 %
                            </span> vs. previous month
                        </p>                        
                    </div>
                    <div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle text-info rounded-circle fs-4">
                                <i class="ri-briefcase-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div><!-- end card body -->
        </div> <!-- end card-->
    </div>
    <!--end col-->
    <div class="col-xxl-3 col-sm-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-semibold text-muted mb-0">
                            Pending Applicants
                        </p>
                        <h2 class="mt-4 ff-secondary fw-semibold">
                            <span class="counter-value" data-target="280">
                                0
                            </span>
                        </h2>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-light text-success mb-0">
                                <i class="ri-arrow-up-line align-middle"></i> 
                                15 %
                            </span> vs. previous month
                        </p>
                    </div>
                    <div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-4">
                                <i class="mdi mdi-timer-sand"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div><!-- end card body -->
        </div>
    </div>
    <!--end col-->
    <div class="col-xxl-3 col-sm-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-semibold text-muted mb-0">
                            Approved Applicants
                        </p>
                        <h2 class="mt-4 ff-secondary fw-semibold">
                            <span class="counter-value" data-target="2.96">
                                0
                            </span>k
                        </h2>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-light text-success mb-0">
                                <i class="ri-arrow-up-line align-middle"></i> 
                                25.86 %
                            </span> vs. previous month
                        </p>
                    </div>
                    <div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                                <i class="ri-checkbox-circle-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div><!-- end card body -->
        </div>
    </div>
    <!--end col-->
    <div class="col-xxl-3 col-sm-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-semibold text-muted mb-0">
                            Amend Applicants
                        </p>
                        <h2 class="mt-4 ff-secondary fw-semibold">
                            <span class="counter-value" data-target="0">
                                0
                            </span>
                        </h2>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-light text-warning mb-0">
                                <i class="ri-arrow-right-line align-middle"></i> 
                                0 %
                            </span> vs. previous month
                        </p>
                    </div>
                    <div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-4">
                                <i class="ri-edit-box-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div><!-- end card body -->
        </div>
    </div>
    <!--end col-->
</div>
<!--end row-->

<div class="row">
    <div class="col-lg-12">
        <div class="card" id="applicantsList">
            <div class="card-header border-0">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">All Applicants</h5>
                    <div class="flex-shrink-0">
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-secondary" id="remove-actions" onClick="deleteMultiple()">
                                <i class="ri-delete-bin-2-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border border-dashed border-end-0 border-start-0">
                <form>
                    <div class="row g-3">
                        <div class="col-xxl-3 col-sm-12">
                            <div class="search-box">
                                <input type="text" class="form-control search bg-light border-light" placeholder="Search for applicant...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                        <!--end col-->

                        <div class="col-xxl-2 col-sm-4">
                            <div class="input-light">
                                <select class="form-control" name="position" id="positionFilter" data-choices data-choices-search-true>
                                    <option value="all" selected>Select Position</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!--end col-->

                        <div class="col-xxl-2 col-sm-4">
                            <div class="input-light">
                                <select class="form-control" name="gender" id="genderFilter" data-choices data-choices-search-true>
                                    <option value="all" selected>Select Gender</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender->name }}">{{ $gender->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!--end col-->
    
                        <div class="col-xxl-2 col-sm-4">
                            <div class="input-light">
                                <select class="form-control" name="race" id="raceFilter" data-choices data-choices-search-true>
                                    <option value="all" selected>Select Ethnincity</option>
                                    @foreach ($races as $race)
                                        <option value="{{ $race->id }}">{{ $race->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!--end col-->
    
                        <div class="col-xxl-2 col-sm-4">
                            <div class="input-light">
                                <select class="form-control" name="town" id="townFilter" data-choices data-choices-search-true>
                                    <option value="all" selected>Select Town</option>
                                    @foreach ($towns as $town)
                                        <option value="{{ $town->id }}">{{ $town->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!--end col-->

                        <div class="col-xxl-1 col-sm-4">
                            <button type="button" class="btn btn-primary w-100" onclick="SearchData();">
                                <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                Filter
                            </button>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </form>
            </div>
            <!--end card-body-->
            <div class="card-body">
                <div class="table-responsive table-card mb-4">
                    <table class="table align-middle table-nowrap mb-0" id="applicantsTable">
                        <thead class="table-light text-muted">
                            <tr>
                                <th scope="col" style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                    </div>
                                </th>
                                <th class="sort d-none" data-sort="id">ID</th>
                                <th class="sort" data-sort="name">Name</th>
                                <th class="sort" data-sort="email">Email</th>
                                <th class="sort" data-sort="position">Position</th>
                                <th class="sort d-none" data-sort="gender">Gender</th>
                                <th class="sort d-none" data-sort="race">Ethnicity</th>
                                <th class="sort" data-sort="location">Location</th>
                                <th class="sort" data-sort="education">Education</th>
                                <th class="sort" data-sort="reason">Reason</th>
                                <th class="sort" data-sort="date">Date</th>
                                <th class="sort" data-sort="status">Status</th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all">
                            @if($applicants && count($applicants) > 0)
                                @foreach ($applicants as $applicant)
                                    <tr>
                                        <th scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="chk_child" value="{{ Crypt::encryptString($applicant->id) }}">
                                            </div>
                                        </th>
                                        <td class="id d-none">{{ Crypt::encryptString($applicant->id) }}</td>
                                        <td class="name">
                                            <a href="{{ route('applicant-profile.index', ['id' => Crypt::encryptString($applicant->id)]) }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <img src="{{ $applicant->avatar ?? 'images/avatar.jpg'}}" alt="" class="avatar-xs rounded-circle">
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 name">
                                                        {{ $applicant->firstname  ?? 'N/A'}} {{ $applicant->lastname ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="email">{{ $applicant->email ?? 'N/A' }}</td>
                                        <td class="position">
                                            <div class="d-flex">
                                                <div class="flex-grow-1 tasks_name">
                                                    {{ $applicant->position ? ($applicant->position->name == 'Other' ? $applicant->position_specify : $applicant->position->name) : 'N/A' }}
                                                </div>
                                                <div class="flex-shrink-0 ms-4">
                                                    <ul class="list-inline tasks-list-menu mb-0">
                                                        <li class="list-inline-item">
                                                            <a href="{{ route('applicant-profile.index', ['id' => Crypt::encryptString($applicant->id)]) }}">
                                                                <i class="ri-eye-fill align-bottom me-2 text-info"></i>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="#approveApplicant" class="approve-item-btn" data-bs-toggle="modal">
                                                                <i class="ri-check-double-fill align-bottom me-2 text-success"></i>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="#amendApplicant" class="amend-item-btn" data-bs-toggle="modal">
                                                                <i class="ri-edit-box-fill align-bottom me-2 text-warning"></i>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="#declineApplicant" class="decline-item-btn" data-bs-toggle="modal">
                                                                <i class="ri-indeterminate-circle-fill align-bottom me-2 text-danger"></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="gender d-none">{{ $applicant->gender->name ?? 'N/A' }}</td>
                                        <td class="race d-none">{{ $applicant->race->name ?? 'N/A' }}</td>
                                        <td class="location">{{ $applicant->town->name  ?? 'N/A' }}</td>
                                        <td class="education">{{ $applicant->education->name  ?? 'N/A' }}</td>
                                        <td class="reason">{{ $applicant->reason ? ($applicant->reason->name == 'Other' ? $applicant->application_reason_specify : $applicant->reason->name) : 'N/A' }}</td>                                        
                                        <td class="date">{{ date('d M Y', strtotime($applicant->created_at)) ?? 'N/A' }}</td>
                                        <td class="status">
                                            <span class="badge bg-success-subtle text-success text-uppercase">
                                                Approved
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <th scope="row">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="chk_child" value="option1">
                                        </div>
                                    </th>
                                    <td class="id d-none"></td>
                                    <td class="name"></td>
                                    <td class="email"></td>
                                    <td class="position"></td>
                                    <td class="gender"></td>
                                    <td class="race"></td>
                                    <td class="location"></td>
                                    <td class="education"></td>
                                    <td class="reason"></td>
                                    <td class="date"></td>
                                    <td class="status"></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    <!--end table-->
                    <div class="noresult" style="display: none">
                        <div class="text-center">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px"></lord-icon>
                            <h5 class="mt-2">Sorry! No Result Found</h5>
                            <p class="text-muted mb-0">
                                We've searched all your applicants. We did not find any applicants for you search.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    <div class="pagination-wrap hstack gap-2">
                        <a class="page-item pagination-prev disabled">
                            Previous
                        </a>
                        <ul class="pagination listjs-pagination mb-0"></ul>
                        <a class="page-item pagination-next">
                            Next
                        </a>
                    </div>
                </div>
            </div>
            <!--end card-body-->
        </div>
        <!--end card-->
    </div>
    <!--end col-->
</div>
<!--end row--> 

<!--
|--------------------------------------------------------------------------
| Modals
|--------------------------------------------------------------------------
-->

<!--Approve Modal -->
<div class="modal fade flip" id="approveApplicant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/lupuorrc.json" trigger="loop" colors="primary:#121331,secondary:#08a88a" style="width:90px;height:90px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4>
                        Approve this applicant ?
                    </h4>
                    <p class="text-muted fs-14 mb-4">
                        By approving this applicant, you are confirming that all the information the associated applicant is correct.
                    </p>
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="approveApplicant-close">
                            <i class="ri-close-line me-1 align-middle"></i> 
                            Close
                        </button>
                        <button class="btn btn-success" id="approve-applicant" data-action="approve">
                            Yes, Approve It
                        </button>
                        <div class="spinner-border text-success d-none" role="status" id="loading-approve">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end approve modal -->

<!--Amend Modal -->
<div class="modal fade flip" id="amendApplicant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/wloilxuq.json" trigger="loop" colors="primary:#FFC84B,secondary:#F17171" style="width:90px;height:90px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4>
                        Amend this applicant ?
                    </h4>
                    <p class="text-muted fs-14 mb-4">
                        Please specify what needs to be amended:
                    </p>
                    <div class="mb-4">
                        <div class="snow-editor" id="amend" style="height: 300px;"></div>
                        <div class="alert alert-danger alert-border-left alert-dismissible fade show mb-xl-0 invalid-feedback" role="alert">
                            <i class="ri-error-warning-line me-3 align-middle fs-16"></i>
                            Please enter a description
                        </div>
                    </div>
                    <div class="hstack gap-2 justify-content-center remove"> 
                        <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="amendApplicant-close">
                            <i class="ri-close-line me-1 align-middle"></i> 
                            Close
                        </button>
                        <button class="btn btn-warning" id="amend-applicant" data-action="ammend">
                            Yes, Amend It
                        </button>
                        <div class="spinner-border text-warning d-none" role="status" id="loading-amend">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end amend modal -->

<!--Decline Modal -->
<div class="modal fade flip" id="declineApplicant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/hrqwmuhr.json" trigger="loop" colors="primary:#F17171,secondary:#FFC84B" style="width:90px;height:90px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4>
                        Decline this applicant ?
                    </h4>
                    <p class="text-muted fs-14 mb-4">
                        By declining this applicant, you are confirming the permanent rejection of the associated applicant post.
                    </p>
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="declineApplicant-close">
                            <i class="ri-close-line me-1 align-middle"></i> 
                            Close
                        </button>
                        <button class="btn btn-danger" id="decline-applicant" data-action="decline">
                            Yes, Decline It
                        </button>
                        <div class="spinner-border text-danger d-none" role="status" id="loading-decline">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end decline modal -->
@endsection
@section('script')
<script src="{{ URL::asset('build/libs/quill/quill.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/applicant-approval.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
