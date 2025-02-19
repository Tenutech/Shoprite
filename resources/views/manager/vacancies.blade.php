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
            My Vacancies: {{ $store && $store->brand ? $store->brand->name.' '.$store->name : '' }}
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="flex-grow-1">
                            <a href="{{ route('vacancy.index') }}" class="btn btn-success add-btn">
                                <i class="ri-add-fill me-1 align-bottom"></i> 
                                New Vacancy
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
        <div class="col-xxl-12">
            <div class="card" id="vacancyList">
                <div class="card-header">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="search-box">
                                <input type="text" class="form-control search" placeholder="Search for vacancy...">
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
                                    <option value="{{count($vacancies)}}">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive table-card mb-3">
                            <table class="table align-middle table-nowrap mb-0" id="vacancyTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sort d-none" data-sort="id" scope="col">ID</th>
                                        <th class="sort" data-sort="number" scope="col">ID</th>
                                        <th class="sort" data-sort="position" scope="col">Position</th>
                                        <th class="sort" data-sort="type" scope="col">Type</th>
                                        <th class="sort" data-sort="open" scope="col">Open</th>
                                        <th class="sort" data-sort="filled" scope="col">Filled</th>
                                        <th scope="col">SAP #</th>
                                        <th class="sort" data-sort="date" scope="col">Posted</th>
                                        <th class="sort" data-sort="status" scope="col">Status</th>                         
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                    @if($vacancies && count($vacancies) > 0)
                                        @foreach ($vacancies as $key => $vacancy)
                                            <tr>
                                                <td class="id d-none" data-sort="{{ Crypt::encryptstring($vacancy->id) }}">{{ Crypt::encryptstring($vacancy->id) }}</td>
                                                <td class="number" data-sort="{{ $vacancy->id }}">{{ $vacancy->id }}</td>
                                                <td class="position" data-sort="{{ $vacancy->position->name }}">
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
                                                <td class="type" data-sort="{{ $vacancy->type->name }}">
                                                    <span class="badge bg-{{ $vacancy->type->color }}-subtle text-{{ $vacancy->type->color }}">
                                                        {{ $vacancy->type->name }}
                                                    </span>
                                                </td>
                                                <td class="open" data-sort="{{ $vacancy->open_positions }}">{{ $vacancy->open_positions }}</td>
                                                <td class="filled" data-sort="{{ $vacancy->filled }}">{{ $vacancy->filled_positions }}</td>
                                                <td class="sap">
                                                    @if($vacancy->sapNumbers && $vacancy->sapNumbers->count() > 0)
                                                        @foreach ($vacancy->sapNumbers as $index => $sapNumber)
                                                            {{ $sapNumber->sap_number }} @if($index != $vacancy->sapNumbers->count() - 1),<br>@endif
                                                        @endforeach
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>                                                
                                                <td class="date" data-sort="{{ date('Y-m-d', strtotime($vacancy->created_at)) }}">{{ date('d M Y', strtotime($vacancy->created_at)) }}</td>
                                                <td class="status" data-sort="{{ $vacancy->status->name }}">
                                                    <span class="badge bg-{{ $vacancy->status->color }}-subtle text-{{ $vacancy->status->color }}">
                                                        {{ $vacancy->status->name }}
                                                    </span>
                                                </td>                                                
                                                <td class="action">
                                                    <ul class="list-inline hstack gap-2 mb-0">
                                                        <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="View">
                                                            <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)]) }}">
                                                                <i class="ri-eye-fill align-bottom text-muted"></i>
                                                            </a>
                                                        </li>
                                                        @if ($vacancy->open_positions > 0)
                                                            <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Edit">
                                                                <a href="{{ route('vacancy.index', ['id' => Crypt::encryptString($vacancy->id)]) }}" class="edit-item-btn">
                                                                    <i class="ri-pencil-fill align-bottom text-muted"></i>
                                                                </a>
                                                            </li>
                                                        @endif
                                                        <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Shortlist">
                                                            <a href="{{ route('shortlist.index') }}?id={{ Crypt::encryptString($vacancy->id) }}">
                                                                <i class="ri-list-check align-bottom text-muted"></i>
                                                            </a>
                                                        </li>
                                                        @if ($user->role_id <= 6 
                                                            && $vacancy->filled_positions <= 0 
                                                            && ($vacancy->shortlists->isEmpty() 
                                                                || is_null($vacancy->shortlists->first()?->applicant_ids) 
                                                                || empty(json_decode($vacancy->shortlists->first()?->applicant_ids, true)))
                                                            && $vacancy->interviews->isEmpty()
                                                            && $vacancy->appointed->isEmpty())

                                                            <li class="list-inline-item remove-item-btn" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Delete">
                                                                <a href="#vacancyDeleteModal" data-bs-toggle="modal" data-bs-id="{{ Crypt::encryptString($vacancy->id) }}">
                                                                    <i class="ri-delete-bin-6-fill align-bottom text-muted"></i>
                                                                </a>
                                                            </li>
                                                        @endif
                                                    </ul>
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
                                            <td class="number"></td>
                                            <td class="position"></td>
                                            <td class="type"></td>
                                            <td class="open"></td>
                                            <td class="filled"></td>
                                            <td class="sap"></td>
                                            <td class="sap"></td>
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
                                                        <a>
                                                            <i class="ri-pencil-fill align-bottom text-muted"></i>
                                                        </a>
                                                    </li>
                                                    <li class="list-inline-item remove-item-btn" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Delete">
                                                        <a>
                                                            <i class="ri-delete-bin-6-fill align-bottom text-muted"></i>
                                                        </a>
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
                                        We've searched all the vacancies. We did not find any vacancies for you search.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
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

                    <!-------------------------------------------------------------------------------------
                        Modals
                    -------------------------------------------------------------------------------------->

                    <!-- Vacancy delete modal -->
                    <div class="modal fade flip" id="vacancyDeleteModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-5 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:90px;height:90px"></lord-icon>
                                    <div class="mt-4 text-center">
                                        <h4>
                                            You are about to delete this vacancy?
                                        </h4>
                                        <p class="text-muted fs-14 mb-4">
                                            Deleting this vacancy will remove all of the information from the database.
                                        </p>
                                        <div class="hstack gap-2 justify-content-center remove">
                                            <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="deleteVacancy-close">
                                                <i class="ri-close-line me-1 align-middle"></i> 
                                                Close
                                            </button>                       
                                            <button class="btn btn-danger" id="delete-vacancy">
                                                Yes, Delete It
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end vacancy delete modal -->
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/vacancies.init.js') }}?v={{ filemtime(public_path('build/js/pages/vacancies.init.js')) }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
