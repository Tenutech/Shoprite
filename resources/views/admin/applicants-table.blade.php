@extends('layouts.master')
@section('title')
    @lang('translation.contacts')
@endsection
@section('css')
    <style>
        .pac-container {
            z-index: 10000;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pages
        @endslot
        @slot('title')
            Candidates
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="flex-grow-1">
                            <button class="btn btn-info add-btn d-none" data-bs-toggle="modal" data-bs-target="#applicantsTableModal">
                                <i class="ri-add-fill me-1 align-bottom"></i>
                                Add Candidate
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
        <div class="col-xxl-9">
            <div class="card" id="applicantsTableList">
                <div class="card-header">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="search-box">
                                <input type="text" class="form-control" id="search" placeholder="Search for candidate...">
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
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive table-card mb-3">
                            <table class="table align-middle table-nowrap mb-0" id="applicantsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                            </div>
                                        </th>
                                        <th class="sort d-none" data-sort="id" scope="col">ID</th>
                                        <th class="sort" data-sort="name" scope="col">Name</th>
                                        <th class="sort" data-sort="id_number" scope="col">ID Number</th>
                                        <th class="sort" data-sort="phone" scope="col">Phone</th>
                                        <th class="sort" data-sort="employment" scope="col">Employment</th>
                                        <th class="sort" data-sort="state" scope="col">State</th>
                                        <th class="sort d-none" data-sort="email" scope="col">Email</th>
                                        <th class="sort d-none" data-sort="town" scope="col">Town</th>
                                        <th class="sort d-none" data-sort="age" scope="col">Age</th>
                                        <th class="sort d-none" data-sort="gender" scope="col">Gender</th>
                                        <th class="sort d-none" data-sort="race" scope="col">Race</th>
                                        <th class="sort d-none" data-sort="score" scope="col">Score</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all" style="height:200px;">
                                    @if($applicants && count($applicants) > 0)
                                        @foreach ($applicants as $key => $applicant)
                                            <tr style="vertical-align:top;">
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="chk_child" value="option1">
                                                    </div>
                                                </th>
                                                <td class="id d-none">{{ Crypt::encryptstring($applicant->id) }}</td>
                                                <td class="name">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <img src="{{ URL::asset($applicant->avatar) }}" alt="" class="avatar-xs rounded-circle">
                                                        </div>
                                                        <div class="flex-grow-1 ms-2 name">{{ $applicant->firstname }} {{ $applicant->lastname }}</div>
                                                    </div>
                                                </td>
                                                <td class="id_number">{{ $applicant->id_number }}</td>
                                                <td class="phone">{{ $applicant->phone }}</td>
                                                <td class="employment">
                                                    @php
                                                        $employment = 'Inconclusive';
                                                        $status = 'dark';
                                                        switch ($applicant->employment) {
                                                            case 'A':
                                                                $employment = 'Active Employee';
                                                                $status = 'warning';
                                                                break;
                                                            case 'B':
                                                                $employment = 'Blacklisted';
                                                                $status = 'danger';
                                                                break;
                                                            case 'P':
                                                                $employment = 'Previously Employed';
                                                                $status = 'info';
                                                                break;
                                                            case 'N':
                                                                $employment = 'Not an Employee';
                                                                $status = 'success';
                                                                break;
                                                            case 'I':
                                                            default:
                                                                $employment = 'Inconclusive';
                                                                $status = 'dark';
                                                                break;
                                                        }
                                                    @endphp
                                                    <span class="badge bg-{{ $status }}-subtle text-{{ $status }} text-uppercase">
                                                        {{ $employment }}
                                                    </span>                                                    
                                                </td> 
                                                <td class="state">{{ $applicant->state_id ? $applicant->state->name : '' }}</td>
                                                <td class="email d-none">{{ $applicant->email }}</td>
                                                <td class="town d-none">{{ $applicant->town_id ? $applicant->town->name : '' }}</td>
                                                <td class="age d-none">{{ $applicant->age }}</td>
                                                <td class="gender d-none">{{ $applicant->gender_id ? $applicant->gender->name : '' }}</td>
                                                <td class="race d-none">{{ $applicant->race_id ? $applicant->race->name : '' }}</td>
                                                <td class="score d-none">{{ $applicant->score ? $applicant->score : 'N/A' }}</td>
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
                                                                        <a class="dropdown-item edit-item-btn" href="#applicantsTableModal" data-bs-toggle="modal">
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
                                            <td class="name"></td>
                                            <td class="id_number"></td>
                                            <td class="phone"></td>
                                            <td class="employment"></td>
                                            <td class="state"></td>
                                            <td class="email d-none"></td>                                           
                                            <td class="town d-none"></td>
                                            <td class="age d-none"></td>
                                            <td class="gender d-none"></td>
                                            <td class="race d-none"></td>
                                            <td class="score d-none"></td>
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
                                                                    <a class="dropdown-item edit-item-btn" href="#applicantsTableModal" data-bs-toggle="modal">
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
                                        We've searched all the users. We did not find any users for you search.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <div class="pagination-wrap-2 hstack gap-2" style="display: flex;">
                                <a class="page-item pagination-prev disabled" href="#" data-i="1" data-page="10">
                                    Previous
                                </a>
                                <ul class="pagination listjs-pagination mb-0">
                                    <li class="active">
                                        <a class="page" href="#" data-i="1" data-page="10">
                                            1
                                        </a>
                                    </li>
                                    <li>
                                        <a class="page" href="#" data-i="2" data-page="10">
                                            2
                                        </a>
                                    </li>
                                    <li>
                                        <a class="page" href="#" data-i="3" data-page="10">
                                            3
                                        </a>
                                    </li>
                                    <li class="disabled">
                                        <a class="page" href="#">...</a>
                                    </li>
                                </ul>
                                <a class="page-item pagination-next" href="#" data-i="2" data-page="10">
                                    Next
                                </a>
                            </div>                           
                        </div>                        
                    </div>

                    <!-- Modal Applicant -->
                    <div class="modal fade zoomIn" id="applicantsTableModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content border-0">
                                <div class="modal-header p-3 bg-soft-primary-rainbow">
                                    <h5 class="modal-title" id="exampleModalLabel">
                                        Update Candidate
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                </div>
                                <form id="formApplicant" action="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" id="field-id" name="field_id"/>
                                        <div class="row g-3">
                                            <div class="col-lg-12 mb-3 d-flex align-items-center justify-content-center h-100">
                                                <div class="text-left">
                                                    <div class="position-relative d-inline-block">
                                                        <div class="position-absolute  bottom-0 end-0">
                                                            <label for="avatar" class="mb-0"  data-bs-toggle="tooltip" data-bs-placement="right" title="Select Image">
                                                                <div class="avatar-xs cursor-pointer">
                                                                    <div class="avatar-title bg-light border rounded-circle text-muted">
                                                                        <i class="ri-image-fill"></i>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                            <input class="form-control d-none" value="" id="avatar" name="avatar" type="file" accept=".jpg, .jpeg, .png">
                                                        </div>
                                                        <div class="avatar-xg p-1">
                                                            <div class="avatar-title bg-light rounded-circle">
                                                                <img src="{{ URL::asset('build/images/users/user-dummy-img.jpg') }}" alt="" id="profile-img" class="avatar-lg rounded-circle object-cover">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end col-->

                                            <div class="col-lg-6">
                                                <div class="col-lg-12 mb-3">
                                                    <label for="firstname" class="form-label">
                                                        Firstname
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Enter first name" required/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="email" class="form-label">
                                                        Email
                                                    </label>
                                                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address"/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="idNumber" class="form-label">
                                                        ID Number
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" id="idNumber" name="id_number" class="form-control" placeholder="Enter id number" required/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="gender" class="form-label">
                                                        Gender
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="gender" name="gender_id" class="form-control">
                                                        <option value="" selected>Select Gender</option>
                                                        @foreach ($genders as $gender)
                                                            <option value="{{ $gender->id }}">{{ $gender->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->                                              
                                                <div class="col-lg-12 mb-3">
                                                    <label for="location" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="What is your current home address where you stay/live 🏡? Please type every detail. (e.g. street number, street name, suburb, town, postal code).">
                                                        Address
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control" id="location" name="location" placeholder="Enter home address" data-google-autocomplete autocomplete="off" required />
                                                    <div class="invalid-feedback">
                                                        Please enter your home address!
                                                    </div>
                                                    <input type="hidden" id="latitude" name="latitude" value="">
                                                    <input type="hidden" id="longitude" name="longitude" value="">
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="duration" class="form-label">
                                                        Retail Experience
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="duration" name="duration_id" class="form-control">
                                                        <option value="" selected>Select Experience</option>
                                                        @foreach ($durations as $duration)
                                                            <option value="{{ $duration->id }}">{{ $duration->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="publicHolidays" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top" title="Are you prepared to work on a rotational shift basis that may include Sundays and public holidays?">
                                                        Public Holidays
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="publicHolidays" name="public_holidays" class="form-control">
                                                        <option value="" selected>Select Option</option>
                                                        <option value="No">No</option>
                                                        <option value="Yes">Yes</option>
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="disability" class="form-label">
                                                        Disability
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="disability" name="disability" class="form-control">
                                                        <option value="" selected>Select Option</option>
                                                        <option value="No">No</option>
                                                        <option value="Yes">Yes</option>
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="shortlist" class="form-label">
                                                        Shortlist
                                                    </label>
                                                    <input type="number" id="shortlist" name="shortlist_id" class="form-control" placeholder="Enter shortlist ID"/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="appointed" class="form-label">
                                                        Appointed
                                                    </label>
                                                    <input type="number" id="appointed" name="appointed_id" class="form-control" placeholder="Enter appointed ID"/>
                                                </div>
                                                <!--end col-->
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="col-lg-12 mb-3">
                                                    <label for="lastname" class="form-label">
                                                        Lastname
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Enter last name" required/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="phone" class="form-label">
                                                        Phone
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter phone number" required/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="employment" class="form-label">
                                                        Employment Status
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="employment" name="employment" class="form-control">
                                                        <option value="" selected>Select Status</option>
                                                        <option value="A">Active Employee</option>
                                                        <option value="B">Blacklisted</option>
                                                        <option value="P">Previously Employed</option>
                                                        <option value="N">Not an Employee</option>
                                                        <option value="I">Inconclusive</option>
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="race" class="form-label">
                                                        Ethnicity
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="race" name="race_id" class="form-control">
                                                        <option value="" selected>Select Ethnicity</option>
                                                        @foreach ($races as $race)
                                                            <option value="{{ $race->id }}">{{ $race->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->                                      
                                                <div class="col-lg-12 mb-3">
                                                    <label for="education" class="form-label">
                                                        Education
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="education" name="education_id" class="form-control">
                                                        <option value="" selected>Select Education Level</option>
                                                        @foreach ($educations as $education)
                                                            <option value="{{ $education->id }}">{{ $education->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="brand" class="form-label">
                                                        Brand(s)
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control" id="brands" name="brands[]" multiple>
                                                        <option value="">Select brand</option>
                                                        @foreach ($brands as $brand)
                                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="environment" class="form-label">
                                                        Environment
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="environment" name="environment" class="form-control" data-bs-toggle="tooltip" data-bs-placement="top" title="Are you willing to work in an environment that may involve heavy lifting, cold areas, or standing for long periods?">
                                                        <option value="" selected>Select Option</option>
                                                        <option value="No">No</option>
                                                        <option value="Yes">Yes</option>
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="state" class="form-label">
                                                        State
                                                    </label>
                                                    <select id="state" name="state_id" class="form-control">
                                                        <option value="" selected>Select Chatbot State</option>
                                                        @foreach ($states as $state)
                                                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="interview" class="form-label">
                                                        Interview
                                                    </label>
                                                    <input type="number" id="interview" name="interview_id" class="form-control" placeholder="Enter interview ID"/>
                                                </div>
                                                <!--end col-->
                                            </div>
                                            <!--end col-->
                                        </div>
                                        <!--end row-->
                                    </div>
                                    <div class="modal-footer">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-light" id="close-modal" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-success" id="edit-btn">Update Candidate</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!--end modal-->

                    <div class="modal fade zoomIn" id="deleteRecordModal" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" id="deleteRecord-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                                </div>
                                <div class="modal-body p-5 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:90px;height:90px"></lord-icon>
                                    <div class="mt-4 text-center">
                                        <h4 class="fs-semibold">
                                            You are about to delete this user ?
                                        </h4>
                                        <p class="text-muted fs-14 mb-4 pt-1">
                                            Deleting this user will remove all of their information from the database.
                                        </p>
                                        <div class="hstack gap-2 justify-content-center remove">
                                            <button class="btn btn-danger" data-bs-dismiss="modal" id="deleteRecord-close">
                                                <i class="ri-close-line me-1 align-middle"></i>
                                                Close
                                            </button>
                                            <button class="btn btn-primary" id="delete-user">
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
        <div class="col-xxl-3">
            <div class="card" id="contact-view-detail">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block">
                        <img src="{{ URL::asset('build/images/users/user-dummy-img.jpg') }}" alt=""
                            class="avatar-lg rounded-circle img-thumbnail">
                        <span class="contact-active position-absolute rounded-circle bg-success"><span
                                class="visually-hidden"></span>
                    </div>
                    <h5 class="mt-4 mb-1"></h5>
                    <p class="text-muted"></p>
                </div>
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold mb-3">Personal Information</h6>
                    <div class="table-responsive table-card">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-medium" scope="row">Email</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Phone</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">ID Number</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Age</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Gender</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Ethnicity</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Town</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Score</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Employment</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">State</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
    <script src="{{ URL::asset('build/js/pages/applicants-table.init.js') }}?v={{ filemtime(public_path('build/js/pages/applicants-table.init.js')) }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googlemaps.key') }}&libraries=places"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
