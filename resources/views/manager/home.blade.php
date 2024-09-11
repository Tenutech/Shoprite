@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection
@section('css')
<link href="{{ URL::asset('build/libs/jsvectormap/css/jsvectormap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css')}}" rel="stylesheet" type="text/css" />
<style>
.applicantsView:hover {
    cursor: pointer; 
}
</style>
@endsection
@section('content')

<div class="row">
    <div class="col">

        <div class="h-100">
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">
                                Hello, {{ Auth::user()->firstname }}!
                            </h4>
                            <p class="text-muted mb-0">
                                Here's what's happening with your store today.
                            </p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-center">
                                    <div class="col-sm-auto">
                                        <div class="input-group">
                                            <input type="text" class="form-control border-0 dash-filter-picker shadow" data-provider="flatpickr" data-range-date="true" data-date-format="d M, Y" data-deafult-date="01 Jan 2022 to 31 Jan 2022">
                                            <div class="input-group-text bg-primary border-primary text-white">
                                                <i class="ri-calendar-2-line"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                    </div><!-- end card header -->
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <!-------------------------------------------------------------------------------------
                Information
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Applications
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-{{ $percentMovementApplicationsPerMonth > 0 ? 'success' : 'danger' }} mb-0"> 
                                        <i class="ri-arrow-{{ $percentMovementApplicationsPerMonth > 0 ? 'up' : 'down' }}-line align-middle"></i> 
                                        {{ abs($percentMovementApplicationsPerMonth) }} % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-{{ $percentMovementApplicationsPerMonth > 0 ? 'success' : 'danger' }}" , "--vz-transparent"]' dir="ltr" id="applications_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->

                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Interviewed
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-{{ $percentMovementInterviewedPerMonth > 0 ? 'success' : 'danger' }} mb-0"> 
                                        <i class="ri-arrow-{{ $percentMovementInterviewedPerMonth > 0 ? 'up' : 'down' }}-line align-middle"></i> 
                                        {{ abs($percentMovementInterviewedPerMonth) }} % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-{{ $percentMovementInterviewedPerMonth > 0 ? 'success' : 'danger' }}" , "--vz-transparent"]' dir="ltr" id="interviewed_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->

                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Hired
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-{{ $percentMovementAppointedPerMonth > 0 ? 'success' : 'danger' }} mb-0"> 
                                        <i class="ri-arrow-{{ $percentMovementAppointedPerMonth > 0 ? 'up' : 'down' }}-line align-middle"></i> 
                                        {{ abs($percentMovementAppointedPerMonth) }} % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-{{ $percentMovementAppointedPerMonth > 0 ? 'success' : 'danger' }}" , "--vz-transparent"]' dir="ltr" id="hired_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->

                <div class="col-xl-3 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">
                                    Rejected
                                </h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-light text-{{ $percentMovementRejectedPerMonth > 0 ? 'success' : 'danger' }} mb-0"> 
                                        <i class="ri-arrow-{{ $percentMovementRejectedPerMonth > 0 ? 'up' : 'down' }}-line align-middle"></i> 
                                        {{ abs($percentMovementRejectedPerMonth) }} % 
                                    </span>vs. previous month
                                </p>
                            </div>
                            <div>
                                <div class="apex-charts" data-colors='["--vz-{{ $percentMovementRejectedPerMonth > 0 ? 'success' : 'danger' }}", "--vz-transparent"]' dir="ltr" id="rejected_sparkline_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
            </div> <!-- end row-->

            <!-------------------------------------------------------------------------------------
                Vacancies
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card" id="vacanciesList">
                        <div class="card-header border-0">
        
                            <div class="row g-4 align-items-center">
                                <div class="col-sm-3">
                                    <div class="search-box">
                                        <input type="text" class="form-control search"
                                            placeholder="Search for...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-sm-auto ms-auto">
                                    <div class="hstack gap-2">
                                        <button class="btn btn-soft-danger" id="remove-actions" onClick="deleteMultiple()">
                                            <i class="ri-delete-bin-2-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-info d-none" data-bs-toggle="offcanvas" href="#offcanvasExample">
                                            <i class="ri-filter-3-line align-bottom me-1"></i> 
                                            Fliters
                                        </button>
                                        <a href="{{ route('vacancy.index') }}" type="button" class="btn btn-success add-btn">
                                            <i class="ri-add-line align-bottom me-1"></i> 
                                            Add Vacancy
                                        </a>                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div>
                                <div class="table-responsive table-card">
                                    <table class="table align-middle" id="vacanciesTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 50px;">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                                    </div>
                                                </th>
                                                <th class="sort d-none" data-sort="id" scope="col">ID</th>
                                                <th class="sort" data-sort="name">Position</th>
                                                <th class="sort" data-sort="type">Type</th>
                                                <th class="sort" data-sort="open">Open</th>
                                                <th class="sort" data-sort="filled">Filled</th>
                                                <th class="sort" data-sort="applicants">Applicants</th>
                                                <th class="sort" data-sort="location">Location</th>
                                                <th class="sort" data-sort="date">Posted</th>
                                                <th class="sort" data-sort="status">Status</th> 
                                                <th class="sort" data-sort="action">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            @if($vacancies && count($vacancies) > 0)
                                                @foreach ($vacancies as $vacancy)
                                                    <!-- Accordion Toggle Row -->
                                                    <tr>
                                                        <th scope="row">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="chk_child" value="{{ Crypt::encryptstring($vacancy->id) }}">
                                                            </div>
                                                        </th>
                                                        <td class="id" style="display:none;">{{ Crypt::encryptstring($vacancy->id) }}</td>
                                                        <td class="name">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0">
                                                                    <div class="avatar-sm">
                                                                        <div class="avatar-title bg-light rounded">
                                                                            <i class="{{ $vacancy->position->icon }} text-{{ $vacancy->position->color }} fs-4"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-2 name">
                                                                    <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)]) }}" class="fw-medium link-primary">
                                                                        {{ $vacancy->position->name }}
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="type">
                                                            <span class="badge bg-{{ $vacancy->type->color }}-subtle text-{{ $vacancy->type->color }}">
                                                                {{ $vacancy->type->name }}
                                                            </span>
                                                        </td>
                                                        <td class="open">{{ $vacancy->open_positions }}</td>
                                                        <td class="filled">{{ $vacancy->filled_positions }}</td>
                                                        <td class="applicants">{{ $vacancy->applicants->count() }}</td>                                                      
                                                        <td class="location">{{ $vacancy->store->town->name }}</td>
                                                        <td class="date">{{ date('d M Y', strtotime($vacancy->created_at)) }}</td>
                                                        <td class="status">
                                                            <span class="badge bg-{{ $vacancy->status->color }}-subtle text-{{ $vacancy->status->color }}">
                                                                {{ $vacancy->status->name }}
                                                            </span>
                                                        </td>
                                                        <td class="action">
                                                            <ul class="list-inline hstack gap-2 mb-0">
                                                                <li class="list-inline-item">
                                                                    <a class="applicantsView" data-bs-toggle="collapse" data-bs-target="#accordion-{{ $vacancy->id }}" aria-expanded="false" aria-controls="accordion-{{ $vacancy->id }}">
                                                                        <i class="ri-group-fill align-bottom text-muted"></i>
                                                                    </a>
                                                                </li>
                                                                <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="View">
                                                                    <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)]) }}">
                                                                        <i class="ri-eye-fill align-bottom text-muted"></i>
                                                                    </a>
                                                                </li>
                                                                <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                                    <a  href="{{ route('vacancy.index', ['id' => Crypt::encryptString($vacancy->id)]) }}" class="edit-item-btn">
                                                                        <i class="ri-pencil-fill align-bottom text-muted"></i>
                                                                    </a>
                                                                </li>
                                                                <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Delete">
                                                                    <a class="remove-item-btn" data-bs-toggle="modal" href="#vacancyDeleteModal">
                                                                        <i class="ri-delete-bin-fill align-bottom text-muted"></i>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </td>
                                                    </tr>

                                                    <!-- Accordion Content Row -->
                                                    <tr class="accordion-collapse collapse" id="accordion-{{ $vacancy->id }}" data-bs-parent="#accordion-{{ $vacancy->id }}">
                                                        <td colspan="100%" class="hiddenRow">
                                                            <div class="accordion-body">
                                                                <div class="row gy-2 mb-2">
                                                                    <div data-simplebar style="max-height: 250px;" class="px-3">
                                                                        @foreach ($vacancy->applicants as $user)
                                                                            <div class="col-md-6 col-lg-12">
                                                                                <div class="card mb-0">
                                                                                    <div class="card-body">
                                                                                        <div class="d-lg-flex align-items-center">
                                                                                            <div class="flex-shrink-0 col-auto">
                                                                                                <div class="avatar-sm rounded overflow-hidden">
                                                                                                    {{-- Check if avatar is null, if so use a default image --}}
                                                                                                    <img src="{{ $user->applicant->avatar ?? URL::asset('images/avatar.jpg') }}" alt="" class="member-img img-fluid d-block rounded">
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="ms-lg-3 my-3 my-lg-0 col-3 text-start">
                                                                                                <a href="{{ route('applicant-profile.index', ['id' => Crypt::encryptString($user->applicant->id ?? '')]) }}">
                                                                                                    <h5 class="fs-16 mb-2">
                                                                                                        {{-- Check if firstname or lastname is null --}}
                                                                                                        {{ optional($user->applicant)->firstname }} {{ optional($user->applicant)->lastname }}
                                                                                                    </h5>
                                                                                                </a>
                                                                                                <p class="text-muted mb-0">
                                                                                                    @if (optional(optional($user->applicant)->position)->name == 'Other')
                                                                                                        {{ optional($user->applicant)->position_specify ?? 'N/A' }}
                                                                                                    @else
                                                                                                        {{ optional(optional($user->applicant)->position)->name ?? 'N/A' }}
                                                                                                    @endif
                                                                                                </p>
                                                                                            </div>
                                                                                            <div class="d-flex gap-4 mt-0 text-muted mx-auto col-2">
                                                                                                <div>
                                                                                                    <i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i>
                                                                                                    {{-- Safely check if town name is null --}}
                                                                                                    {{ optional(optional($user->applicant)->town)->name ?? 'N/A' }}
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-2">
                                                                                                <i class="ri-briefcase-line text-primary me-1 align-bottom"></i>
                                                                                                {{-- Safely check the type and handle 'Other' case --}}
                                                                                                @if (optional(optional($user->applicant)->type)->name == 'Other')
                                                                                                    {{ optional($user->applicant)->application_reason_specify ?? 'N/A' }}
                                                                                                @else
                                                                                                    {{ optional(optional($user->applicant)->type)->name ?? 'N/A' }}
                                                                                                @endif
                                                                                            </div>                                                                                        
                                                                                            <div class="d-flex flex-wrap gap-2 align-items-center mx-auto my-3 my-lg-0 col-1">
                                                                                                <div class="badge text-bg-success">
                                                                                                    <i class="mdi mdi-star me-1"></i>
                                                                                                    {{-- Check if score is null --}}
                                                                                                    {{ $user->applicant->score ?? 'N/A' }}                                                                                            
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-2 text-end">
                                                                                                <a href="{{ route('applicant-profile.index', ['id' => Crypt::encryptString($user->applicant->id ?? '')]) }}" class="btn btn-soft-primary">
                                                                                                    View Details
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr style="vertical-align:top;">
                                                    <th scope="row">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" value="option">
                                                        </div>
                                                    </th>
                                                    <td class="id d-none"></td>
                                                    <td class="name"></td>
                                                    <td class="type"></td>
                                                    <td class="open"></td>
                                                    <td class="filled"></td>
                                                    <td class="applicants"></td>
                                                    <td class="location"></td>
                                                    <td class="date"></td>
                                                    <td class="status"></td>
                                                    <td class="action">
                                                        <ul class="list-inline hstack gap-2 mb-0">
                                                            <li>
                                                                <a>
                                                                    <i class="ri-group-fill align-bottom text-muted"></i>
                                                                </a>
                                                            </li>
                                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="View">
                                                                <a>
                                                                    <i class="ri-eye-fill align-bottom text-muted"></i>
                                                                </a>
                                                            </li>
                                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                                <a class="edit-item-btn">
                                                                    <i class="ri-pencil-fill align-bottom text-muted"></i>
                                                                </a>
                                                            </li>
                                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Delete">
                                                                <a class="remove-item-btn" data-bs-toggle="modal" href="#vacancyDeleteModal">
                                                                    <i class="ri-delete-bin-fill align-bottom text-muted"></i>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </td>
                                                    <td>
                                                        <ul class="list-inline hstack gap-2 mb-0">
                                                            <li class="list-inline-item">
                                                                <div class="dropdown">
                                                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <i class="ri-more-fill align-middle"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                                        <li>
                                                                            <a class="dropdown-item view-item-btn" href="javascript:void(0);">
                                                                                <i class="ri-eye-fill align-bottom me-2 text-muted"></i>
                                                                                View
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item edit-item-btn" href="#usersModal" data-bs-toggle="modal">
                                                                                <i class="ri-pencil-fill align-bottom me-2 text-muted"></i>
                                                                                Edit
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item remove-item-btn" data-bs-toggle="modal" href="#deleteRecordModal">
                                                                                <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i>
                                                                                Delete
                                                                            </a>
                                                                        </li>
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
                                            <h5 class="mt-2">Sorry! No Result Found</h5>
                                            <p class="text-muted mb-0">We've searched more than 150+ leads We
                                                did not find any
                                                leads for you search.</p>
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
        
                            <!-- Vacancy delete modal -->
                            <div class="modal fade flip" id="vacancyDeleteModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-body p-5 text-center">
                                            <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:90px;height:90px"></lord-icon>
                                            <div class="mt-4 text-center">
                                                <h4>
                                                    You are about to delete this vacancy ?
                                                </h4>
                                                <p class="text-muted fs-14 mb-4">
                                                    Deleting this vacancy will remove all of the information from the database.
                                                </p>
                                                <div class="hstack gap-2 justify-content-center remove">
                                                    <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="deleteOpportunity-close">
                                                        <i class="ri-close-line me-1 align-middle"></i> 
                                                        Close
                                                    </button>                       
                                                    <button class="btn btn-danger" id="vacancy-delete">
                                                        Yes, Delete It
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end vacancy delete modal -->        
        
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample"
                                aria-labelledby="offcanvasExampleLabel">
                                <div class="offcanvas-header bg-light">
                                    <h5 class="offcanvas-title" id="offcanvasExampleLabel">Leads Fliters</h5>
                                    <button type="button" class="btn-close text-reset"
                                        data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <!--end offcanvas-header-->
                                <form action="" class="d-flex flex-column justify-content-end h-100">
                                    <div class="offcanvas-body">
                                        <div class="mb-4">
                                            <label for="datepicker-range"
                                                class="form-label text-muted text-uppercase fw-semibold mb-3">Date</label>
                                            <input type="date" class="form-control" id="datepicker-range"
                                                data-provider="flatpickr" data-range="true"
                                                placeholder="Select date">
                                        </div>
                                        <div class="mb-4">
                                            <label for="country-select"
                                                class="form-label text-muted text-uppercase fw-semibold mb-3">Country</label>
                                            <select class="form-control" data-choices
                                                data-choices-multiple-remove="true" name="country-select"
                                                id="country-select" multiple>
                                                <option value="">Select country</option>
                                                <option value="Argentina">Argentina</option>
                                                <option value="Belgium">Belgium</option>
                                                <option value="Brazil" selected>Brazil</option>
                                                <option value="Colombia">Colombia</option>
                                                <option value="Denmark">Denmark</option>
                                                <option value="France">France</option>
                                                <option value="Germany">Germany</option>
                                                <option value="Mexico">Mexico</option>
                                                <option value="Russia">Russia</option>
                                                <option value="Spain">Spain</option>
                                                <option value="Syria">Syria</option>
                                                <option value="United Kingdom" selected>United Kingdom</option>
                                                <option value="United States of America">United States of
                                                    America</option>
                                            </select>
                                        </div>
                                        <div class="mb-4">
                                            <label for="status-select"
                                                class="form-label text-muted text-uppercase fw-semibold mb-3">Status</label>
                                            <div class="row g-2">
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineCheckbox1" value="option1">
                                                        <label class="form-check-label"
                                                            for="inlineCheckbox1">New Leads</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineCheckbox2" value="option2">
                                                        <label class="form-check-label"
                                                            for="inlineCheckbox2">Old Leads</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineCheckbox3" value="option3">
                                                        <label class="form-check-label"
                                                            for="inlineCheckbox3">Loss Leads</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineCheckbox4" value="option4">
                                                        <label class="form-check-label"
                                                            for="inlineCheckbox4">Follow Up</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label for="leadscore"
                                                class="form-label text-muted text-uppercase fw-semibold mb-3">Lead
                                                Score</label>
                                            <div class="row g-2 align-items-center">
                                                <div class="col-lg">
                                                    <input type="number" class="form-control" id="leadscore"
                                                        placeholder="0">
                                                </div>
                                                <div class="col-lg-auto">
                                                    To
                                                </div>
                                                <div class="col-lg">
                                                    <input type="number" class="form-control" id="leadscore"
                                                        placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="leads-tags"
                                                class="form-label text-muted text-uppercase fw-semibold mb-3">Tags</label>
                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="marketing" value="marketing">
                                                        <label class="form-check-label"
                                                            for="marketing">Marketing</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="management" value="management">
                                                        <label class="form-check-label"
                                                            for="management">Management</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="business" value="business">
                                                        <label class="form-check-label"
                                                            for="business">Business</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="investing" value="investing">
                                                        <label class="form-check-label"
                                                            for="investing">Investing</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="partner" value="partner">
                                                        <label class="form-check-label"
                                                            for="partner">Partner</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="lead" value="lead">
                                                        <label class="form-check-label" for="lead">Leads</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="sale" value="sale">
                                                        <label class="form-check-label" for="sale">Sale</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="owner" value="owner">
                                                        <label class="form-check-label"
                                                            for="owner">Owner</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="banking" value="banking">
                                                        <label class="form-check-label"
                                                            for="banking">Banking</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="banking" value="banking">
                                                        <label class="form-check-label"
                                                            for="banking">Exiting</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="banking" value="banking">
                                                        <label class="form-check-label"
                                                            for="banking">Finance</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="banking" value="banking">
                                                        <label class="form-check-label"
                                                            for="banking">Fashion</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end offcanvas-body-->
                                    <div class="offcanvas-footer border-top p-3 text-center hstack gap-2">
                                        <button class="btn btn-light w-100">Clear Filter</button>
                                        <button type="submit" class="btn btn-success w-100">Filters</button>
                                    </div>
                                    <!--end offcanvas-footer-->
                                </form>
                            </div>
                            <!--end offcanvas-->
        
                        </div>
                    </div>
        
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <!-------------------------------------------------------------------------------------
                Jobs Summary
            -------------------------------------------------------------------------------------->

            <div class="row">
                <div class="col-xxl-12 col-md-12">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Jobs Summary</h4>
                            <div class="flex-shrink-0">
                                <div class="dropdown card-header-dropdown">
                                    <a class="text-reset dropdown-btn">
                                        <span class="fw-bold text-uppercase fs-12">Absorption Rate: </span>
                                        <span class="text-muted">
                                            {{ $totalApplications > 0 ? round($totalAppointed / $totalApplications * 100) : 0 }}%
                                            <i class="mdi mdi-briefcase ms-1"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div><!-- end card header -->
                        <div class="card-body px-0">
                            <div id="jobs_chart" data-colors='["--vz-success","--vz-primary", "--vz-info", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->
            </div>

            @if ($shortlist)
                @include('manager.partials.shortlist-modal', ['shortlist' => $shortlist])
            @endif

        </div> <!-- end .h-100-->

    </div> <!-- end col -->
</div>


@endsection
@section('script')
<script>
    var shortlist = @json($shortlist);
    var applicationsPerMonth = @json($applicationsPerMonth);
    var interviewedPerMonth = @json($interviewedPerMonth);
    var appointedPerMonth = @json($appointedPerMonth);
    var rejectedPerMonth = @json($rejectedPerMonth);
</script>
<!-- sweet alert -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- apexcharts -->
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{URL::asset('build/libs/jsvectormap/js/jsvectormap2.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/jsvectormap/maps/south-africa.js') }}"></script>
<script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
<!-- dashboard init -->
<script src="{{URL::asset('build/js/pages/manager.init.js')}}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
