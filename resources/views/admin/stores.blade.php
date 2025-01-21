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
            Stores
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="flex-grow-1">
                            <button class="btn btn-info add-btn" data-bs-toggle="modal" data-bs-target="#storeModal">
                                <i class="ri-add-fill me-1 align-bottom"></i> 
                                Add Store
                            </button>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="hstack text-nowrap gap-2">
                                <button class="btn btn-soft-danger" onClick="deleteMultiple()">
                                    <i class="ri-delete-bin-2-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
        <div class="col-xxl-12">
            <div class="card" id="storeList">
                <div class="card-header">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="search-box">
                                <input type="text" class="form-control search" placeholder="Search for store...">
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
                                    <option value="{{count($stores)}}">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive table-card mb-3">
                            <table class="table align-middle table-nowrap mb-0" id="storeTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                            </div>
                                        </th>
                                        <th class="sort d-none" data-sort="id" scope="col">ID</th>
                                        <th class="sort" data-sort="code" scope="col">Alpha Code</th>
                                        <th class="sort" data-sort="code5" scope="col">Code (5)</th>
                                        <th class="sort" data-sort="code6" scope="col">Code (6)</th>
                                        <th class="sort" data-sort="brand" scope="col">Brand</th>                                        
                                        <th class="sort" data-sort="town" scope="col">Town</th>
                                        <th class="sort" data-sort="region" scope="col">Region</th>
                                        <th class="sort" data-sort="division" scope="col">Division</th>
                                        <th class="sort" data-sort="address" scope="col">Address</th>
                                        <th class="sort" data-sort="coordinates" scope="col">Coordinates</th>              
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all" style="height:200px;">
                                    @if($stores && count($stores) > 0)
                                        @foreach ($stores as $store)
                                            <tr style="vertical-align:top;">
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="chk_child" value="option1">
                                                    </div>
                                                </th>
                                                <td class="id d-none">{{ Crypt::encryptstring($store->id) }}</td>
                                                <td class="code">{{ $store->code }}</td>
                                                <td class="code5">{{ $store->code_5 }}</td>
                                                <td class="code6">{{ $store->code_6 }}</td>
                                                <td class="brand">{{ optional($store->brand)->name }}</td>
                                                <td class="town">{{ optional($store->town)->name }}</td>
                                                <td class="region">{{ optional($store->region)->name }}</td>
                                                <td class="division">{{ optional($store->division)->name }}</td>
                                                <td class="address" style="white-space: pre-wrap;">{{ $store->address }}</td>
                                                <td class="coordinates">{{ $store->coordinates }}</td>
                                                <td>
                                                    <ul class="list-inline hstack gap-2 mb-0">
                                                        <li class="list-inline-item">
                                                            <div class="dropdown">
                                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="ri-more-fill align-middle"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li>
                                                                        <a class="dropdown-item edit-item-btn" href="#storeModal" data-bs-toggle="modal">
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
                                        @endforeach
                                    @else
                                        <tr style="vertical-align:top;">
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="chk_child" value="option1">
                                                </div>
                                            </th>
                                            <td class="id d-none"></td>
                                            <td class="code"></td>
                                            <td class="code5"></td>
                                            <td class="code6"></td>
                                            <td class="brand"></td>
                                            <td class="town"></td>
                                            <td class="region"></td>
                                            <td class="division"></td>
                                            <td class="address"></td>
                                            <td class="coordinates"></td>
                                            <td>
                                                <ul class="list-inline hstack gap-2 mb-0">
                                                    <li class="list-inline-item">
                                                        <div class="dropdown">
                                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="ri-more-fill align-middle"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a class="dropdown-item edit-item-btn" href="#storeModal" data-bs-toggle="modal">
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
                                    <h5 class="mt-2">
                                        Sorry! No Result Found
                                    </h5>
                                    <p class="text-muted mb-0">
                                        We've searched all the stores. We did not find any stores for you search.
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

                    <!-- Modal Store -->
                    <div class="modal fade zoomIn" id="storeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0">
                                <div class="modal-header bg-light p-3">
                                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                </div>
                                <form id="formStore" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" id="field-id" name="field_id"/>
                                    <div class="modal-body">
                                        <div class="col-lg-12 mb-3">  
                                            <div class="mb-3">
                                                <label for="code" class="form-label">
                                                    Alpha Branch Code
                                                </label>
                                                <input type="text" id="code" name="code" class="form-control" 
                                                       placeholder="Enter 4 digit branch code" 
                                                       required 
                                                       pattern="\d{4}" 
                                                       title="Please enter a 4-digit branch code. It can start with 0."/>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="code-5" class="form-label">
                                                    Branch Code With Check Digit (5)
                                                </label>
                                                <input type="text" id="code-5" name="code_5" class="form-control" 
                                                       placeholder="Enter 5 digit branch code" 
                                                       required 
                                                       pattern="\d{5}" 
                                                       title="Please enter a 5-digit branch code. It can start with 0."/>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="code-6" class="form-label">
                                                    Branch Code With Check Digit (6)
                                                </label>
                                                <input type="text" id="code-6" name="code_6" class="form-control" 
                                                       placeholder="Enter 6 digit branch code" 
                                                       required 
                                                       pattern="\d{6}" 
                                                       title="Please enter a 6-digit branch code. It can start with 0."/>
                                            </div>

                                            <div class="mb-3">
                                                <label for="brand" class="form-label">
                                                    Brand
                                                </label>
                                                <select id="brand" name="brand" class="form-control" required>
                                                    <option value="" selected>Select Brand</option>
                                                    @foreach ($brands as $brand)
                                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="town" class="form-label">
                                                    Town
                                                </label>
                                                <select id="town" name="town" class="form-control" required>
                                                    <option value="" selected>Select Town</option>
                                                    @foreach ($towns as $town)
                                                        <option value="{{ $town->id }}">{{ $town->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-lg-12 mb-3">
                                                <label for="region"class="form-label">
                                                    Region
                                                </label>
                                                <select id="region" name="region" class="form-control">
                                                    <option value="" selected>Select Region</option>
                                                    @foreach ($regions as $region)
                                                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="division" class="form-label">
                                                    Division
                                                </label>
                                                <select id="division" name="division" class="form-control">
                                                    <option value="" selected>Select Division</option>
                                                    @foreach ($divisions as $division)
                                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="address" class="form-label">
                                                    Address
                                                </label>
                                                <input type="text" id="address" name="address" class="form-control" placeholder="Enter store address" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="code" class="form-label">
                                                    Coordinates
                                                </label>
                                                <input type="text" id="coordinates" name="coordinates" class="form-control" placeholder="Enter store coordinates" required/>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <div class="modal-footer">                                        
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success" id="add-btn">Add Store</button>
                                            <button type="button" class="btn btn-success" id="edit-btn">Update</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!--end modal-->

                    <!-- Delete Modal -->
                    <div class="modal fade zoomIn" id="deleteRecordModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" id="deleteRecord-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                                </div>
                                <div class="modal-body p-5 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:90px;height:90px"></lord-icon>
                                    <div class="mt-4 text-center">
                                        <h4 class="fs-semibold">
                                            You are about to delete this store ?
                                        </h4>
                                        <p class="text-muted fs-14 mb-4 pt-1">
                                            Deleting this store will remove all of the information from the database.
                                        </p>
                                        <div class="hstack gap-2 justify-content-center remove">
                                            <button class="btn btn-danger" data-bs-dismiss="modal" id="deleteRecord-close">
                                                <i class="ri-close-line me-1 align-middle"></i>
                                                Close
                                            </button>
                                            <button class="btn btn-primary" id="delete-position">
                                                Yes, Delete!!
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end delete modal -->

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
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/quill/quill.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/stores.init.js') }}?v={{ filemtime(public_path('build/js/pages/stores.init.js')) }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
