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
        Vacancy
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
                            Total Vacancies
                        </p>
                        <h2 class="mt-4 ff-secondary fw-semibold">
                            <span class="counter-value" data-target="328">
                                0
                            </span>
                        </h2>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-light text-success mb-0">
                                <i class="ri-arrow-up-line align-middle"></i> 
                                16.24 %
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
                            Pending Vacancies
                        </p>
                        <h2 class="mt-4 ff-secondary fw-semibold">
                            <span class="counter-value" data-target="15">
                                0
                            </span>
                        </h2>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-light text-danger mb-0">
                                <i class="ri-arrow-down-line align-middle"></i> 
                                5.4 %
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
                            Approved Vacancies
                        </p>
                        <h2 class="mt-4 ff-secondary fw-semibold">
                            <span class="counter-value" data-target="313">
                                0
                            </span>
                        </h2>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-light text-success mb-0">
                                <i class="ri-arrow-up-line align-middle"></i> 
                                18 %
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
                            Amend Vacancies
                        </p>
                        <h2 class="mt-4 ff-secondary fw-semibold">
                            <span class="counter-value" data-target="{{ $amendVacancies }}">
                                0
                            </span>
                        </h2>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-light text-{{ $movementAmend == 0 ? 'warning' : ($movementAmend < 0 ? 'danger' : 'success') }} mb-0">
                                <i class="ri-arrow-{{ $movementAmend == 0 ? 'right' : ($movementAmend < 0 ? 'down' : 'up') }}-line align-middle"></i> 
                                {{ $movementAmend }} %
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
        <div class="card" id="vacanciesList">
            <div class="card-header border-0">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">All Vacancies</h5>
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
                                <input type="text" class="form-control search bg-light border-light" placeholder="Search for vacancy...">
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
                                <select class="form-control" name="type" id="typeFilter" data-choices data-choices-search-true>
                                    <option value="all" selected>Select Type</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!--end col-->
    
                        <div class="col-xxl-2 col-sm-4">
                            <div class="input-light">
                                <select class="form-control" name="store" id="storeFilter" data-choices data-choices-search-true>
                                    <option value="all" selected>Select Store</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
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
                            <button type="button" class="btn btn-primary w-100" onclick="SearchData();"> <i class="ri-equalizer-fill me-1 align-bottom"></i>
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
                    <table class="table align-middle table-nowrap mb-0" id="vacanciesTable">
                        <thead class="table-light text-muted">
                            <tr>
                                <th scope="col" style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                    </div>
                                </th>
                                <th class="sort d-none" data-sort="id">ID</th>
                                <th class="sort" data-sort="name">Position</th>
                                <th class="sort" data-sort="type">Type</th>
                                <th class="sort" data-sort="user">Posted By</th>
                                <th class="sort" data-sort="location">Location</th>
                                <th class="sort" data-sort="open">Open</th>
                                <th class="sort" data-sort="filled">Filled</th>
                                <th class="sort" data-sort="date">Date</th>
                                <th class="sort" data-sort="status">Status</th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all">
                            @if($vacancies && count($vacancies) > 0)
                                @foreach ($vacancies as $vacancy)
                                    <tr>
                                        <th scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="chk_child" value="{{ Crypt::encryptString($vacancy->id) }}">
                                            </div>
                                        </th>
                                        <td class="id d-none">{{ Crypt::encryptString($vacancy->id) }}</td>
                                        <td class="name">
                                            <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)]) }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="{{ $vacancy->position->icon }} text-{{ $vacancy->position->color }} avatar-xs rounded-circle fs-3"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2 name">
                                                        {{ $vacancy->position->name }}
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="type">
                                            <span class="badge bg-{{ $vacancy->type->color }}-subtle text-{{ $vacancy->type->color }}">
                                                {{ $vacancy->type->name }}
                                            </span>
                                        </td>
                                        <td class="user">
                                            <div class="d-flex">
                                                <div class="flex-grow-1 tasks_name">
                                                    {{ $vacancy->user->firstname }} {{ $vacancy->user->lastname }}
                                                </div>
                                                <div class="flex-shrink-0 ms-4">
                                                    <ul class="list-inline tasks-list-menu mb-0">
                                                        <li class="list-inline-item">
                                                            <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)]) }}">
                                                                <i class="ri-eye-fill align-bottom me-2 text-info"></i>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="#approveVacancy" class="approve-item-btn" data-bs-toggle="modal">
                                                                <i class="ri-check-double-fill align-bottom me-2 text-success"></i>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="#amendVacancy" class="amend-item-btn" data-bs-toggle="modal">
                                                                <i class="ri-edit-box-fill align-bottom me-2 text-warning"></i>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="#declineVacancy" class="decline-item-btn" data-bs-toggle="modal">
                                                                <i class="ri-indeterminate-circle-fill align-bottom me-2 text-danger"></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="location">{{ $vacancy->store->town->name }}</td>
                                        <td class="open">{{ $vacancy->open_positions }}</td>
                                        <td class="filled">{{ $vacancy->filled_positions }}</td>                                        
                                        <td class="date">{{ date('d M Y', strtotime($vacancy->created_at)) }}</td>
                                        <td class="status">
                                            <span class="badge bg-{{ $vacancy->status->color }}-subtle text-{{ $vacancy->status->color }} text-uppercase">
                                                {{ $vacancy->status->name }}
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
                                    <td class="type"></td>
                                    <td class="user"></td>
                                    <td class="location"></td>
                                    <td class="open"></td>
                                    <td class="filled"></td>
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
                                We've searched all your vacancies. We did not find any vacancies for you search.
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
<div class="modal fade flip" id="approveVacancy" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/lupuorrc.json" trigger="loop" colors="primary:#121331,secondary:#08a88a" style="width:90px;height:90px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4>
                        Approve this vacancy ?
                    </h4>
                    <p class="text-muted fs-14 mb-4">
                        By approving this vacancy, you are confirming that all the information the associated vacancy is correct.
                    </p>
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="approveVacancy-close">
                            <i class="ri-close-line me-1 align-middle"></i> 
                            Close
                        </button>
                        <button class="btn btn-success" id="approve-vacancy" data-action="approve">
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
<div class="modal fade flip" id="amendVacancy" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/wloilxuq.json" trigger="loop" colors="primary:#FFC84B,secondary:#F17171" style="width:90px;height:90px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4>
                        Amend this vacancy ?
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
                        <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="amendVacancy-close">
                            <i class="ri-close-line me-1 align-middle"></i> 
                            Close
                        </button>
                        <button class="btn btn-warning" id="amend-vacancy" data-action="amend">
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
<div class="modal fade flip" id="declineVacancy" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/hrqwmuhr.json" trigger="loop" colors="primary:#F17171,secondary:#FFC84B" style="width:90px;height:90px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4>
                        Decline this vacancy ?
                    </h4>
                    <p class="text-muted fs-14 mb-4">
                        By declining this vacancy, you are confirming the permanent rejection of the associated vacancy post.
                    </p>
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="declineVacancy-close">
                            <i class="ri-close-line me-1 align-middle"></i> 
                            Close
                        </button>
                        <button class="btn btn-danger" id="decline-vacancy" data-action="decline">
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
<script src="{{ URL::asset('build/js/pages/approval.init.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
