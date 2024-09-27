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
            Applicants
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="flex-grow-1">
                            <button class="btn btn-info add-btn" data-bs-toggle="modal" data-bs-target="#applicantsModal">
                                <i class="ri-add-fill me-1 align-bottom"></i>
                                Add Applicant
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
            <div class="card" id="applicantList">
                <div class="card-header">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="search-box">
                                <input type="text" class="form-control search" placeholder="Search for applicant...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-auto ms-auto">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted">Sort by: </span>
                                <select class="form-control mb-0" data-choices data-choices-search-false id="choices-single-default">
                                    <option value="Name">Name</option>
                                    <option value="Department">Department</option>
                                    <option value="Job">Job</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted">Display: </span>
                                <select class="form-control mb-0" id="per-page-select" data-choices data-choices-search-false>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="{{count($applicants)}}">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive table-card mb-3">
                            <table class="table align-middle table-nowrap mb-0" id="applicantTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                            </div>
                                        </th>
                                        <th class="sort d-none" data-sort="id" scope="col">ID</th>
                                        <th class="sort" data-sort="name" scope="col">Name</th>
                                        <th class="sort" data-sort="email" scope="col">Email</th>
                                        <th class="sort" data-sort="phone" scope="col">Phone</th>
                                        <th class="sort" data-sort="id_number" scope="col">ID Number</th>
                                        <th class="sort d-none" data-sort="id_verified" scope="col">Verified</th>
                                        <th class="sort d-none" data-sort="birth_date" scope="col">Birth Date</th>
                                        <th class="sort d-none" data-sort="town" scope="col">Town</th>
                                        <th class="sort" data-sort="age" scope="col">Age</th>
                                        <th class="sort" data-sort="gender" scope="col">Gender</th>
                                        <th class="sort" data-sort="race" scope="col">Race</th>
                                        <th class="sort d-none" data-sort="disability" scope="col">Disability</th>
                                        <th class="sort d-none" data-sort="role" scope="col">Role</th>
                                        <th class="sort d-none" data-sort="location" scope="col">Location</th>
                                        <th class="sort d-none" data-sort="education" scope="col">Education</th>
                                        <th class="sort d-none" data-sort="duration" scope="col">Duration</th>
                                        <th class="sort d-none" data-sort="applicant_type" scope="col">Applicant Type</th>
                                        <th class="sort d-none" data-sort="source" scope="col">Source</th>
                                        <th class="sort" data-sort="state" scope="col">State</th>
                                        <th class="sort d-none" data-sort="now_show" scope="col">No show</th>
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
                                                            @if (file_exists(URL::asset('images/' . $applicant->avatar)))
                                                                <img src="{{ URL::asset('images/' . $applicant->avatar) }}" alt="" class="avatar-xs rounded-circle">
                                                            @else
                                                                <img src="{{ URL::asset('images/avatar.jpg') }}" alt="" class="avatar-xs rounded-circle">
                                                            @endif
                                                        </div>
                                                        <div class="flex-grow-1 ms-2 name">{{ $applicant->firstname }} {{ $applicant->lastname }}</div>
                                                    </div>
                                                </td>
                                                <td class="email">{{ $applicant->email }}</td>
                                                <td class="phone">{{ $applicant->phone }}</td>
                                                <td class="id_number">{{ $applicant->id_number }}</td>
                                                <td class="id_verified d-none">{{ $applicant->id_verified }}</td>
                                                <td class="birth_date d-none">{{ $applicant->birth_date ? date('d M, Y', strtotime($applicant->birth_date)) : '' }}</td>
                                                <td class="age">{{ $applicant->age }}</td>
                                                <td class="town d-none">{{ $applicant->town_id ? $applicant->town->name : '' }}</td>
                                                <td class="gender">{{ $applicant->gender ? $applicant->gender->name : '' }}</td>
                                                <td class="race">{{ $applicant->race_id ? $applicant->race->name : '' }}</td>
                                                <td class="disability d-none">{{ $applicant->disability }}</td>
                                                <td class="literacy d-none">{{ $applicant->literacy }}</td>
                                                <td class="numeracy d-none">{{ $applicant->numeracy }}</td>
                                                <td class="situational d-none">{{ $applicant->situational }}</td>
                                                <td class="score d-none">{{ $applicant->score }}</td>
                                                <td class="role d-none">{{ $applicant->role ? $applicant->role->name : '' }}</td>
                                                <td class="education d-none">{{ $applicant->education_id ? $applicant->education->name : '' }}</td>
                                                <td class="location d-none">{{ $applicant->location }}</td>
                                                <td class="duration d-none">{{ $applicant->duration_id ? $applicant->duration->name : '' }}</td>
                                                <td class="applicant_type d-none">{{ $applicant->applicant_type_id ? $applicant->applicantType->name : '' }}</td>
                                                <td class="source d-none">{{ $applicant->application_type }}</td>
                                                <td class="state">{{ $applicant->state_id ? $applicant->state->name : '' }}</td>
                                                <td class="no_show d-none">{{ $applicant->no_show }}</td>
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
                                                                        <a class="dropdown-item edit-item-btn" href="#applicantsModal" data-bs-toggle="modal">
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
                                            <td class="email"></td>
                                            <td class="phone"></td>
                                            <td class="id_number"></td>
                                            <td class="id_verified d-none"></td>
                                            <td class="birth_date d-none"></td>
                                            <td class="age"></td>
                                            <td class="town d-none"></td>
                                            <td class="gender"></td>
                                            <td class="race"></td>
                                            <td class="disability d-none"></td>
                                            <td class="literacy d-none"></td>
                                            <td class="numeracy d-none"></td>
                                            <td class="situational d-none"></td>
                                            <td class="score d-none"></td>
                                            <td class="role d-none"></td>
                                            <td class="location d-none"></td>
                                            <td class="education d-none"></td>
                                            <td class="duration d-none"></td>
                                            <td class="applicant_type d-none"></td>
                                            <td class="source d-none"></td>
                                            <td class="state"></td>
                                            <td class="no_show"></td>
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
                                                                    <a class="dropdown-item edit-item-btn" href="#applicantsModal" data-bs-toggle="modal">
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
                                        We've searched all the applicants. We did not find any applicants for you search.
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

                    <!-- Modal Applicant -->
                    <div class="modal fade zoomIn" id="applicantsModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content border-0">
                                <div class="modal-header p-3 bg-soft-primary-rainbow">
                                    <h5 class="modal-title" id="exampleModalLabel">
                                        Add Applicant
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
                                                    </label>
                                                    <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Enter first name" required/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="email" class="form-label">
                                                        Email
                                                    </label>
                                                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address" required/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="idNumber" class="form-label">
                                                        ID Number
                                                    </label>
                                                    <input type="text" id="idNumber" name="id_number" class="form-control" placeholder="Enter id number" required/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="birthDate" class="form-label">
                                                        Birth Date
                                                    </label>
                                                    <input type="date" id="birthDate" name="birth_date" class="form-control" placeholder="Enter date of birth"/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="gender" class="form-label">
                                                        Gender
                                                    </label>
                                                    <select id="gender" name="gender_id" class="form-control">
                                                        <option value="" selected>Select gender</option>
                                                        @foreach ($genders as $gender)
                                                            <option value="{{ $gender->id }}">{{ $gender->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="disability" class="form-label">
                                                        Disability
                                                    </label>
                                                    <select id="disability" name="disability" class="form-control">
                                                        <option value="" selected>Select disability</option>
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
                                                        <option value="" selected>Select state</option>
                                                        @foreach ($states as $state)
                                                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="education" class="form-label">
                                                        Education
                                                    </label>
                                                    <select id="education" name="education_id" class="form-control">
                                                        <option value="" selected>Select education</option>
                                                        @foreach ($educations as $education)
                                                            <option value="{{ $education->id }}">{{ $education->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="applicant_type" class="form-label">
                                                        Applicant Type
                                                    </label>
                                                    <select id="applicant_type" name="applicant_type_id" class="form-control">
                                                        <option value="" selected>Select applicant type</option>
                                                        @foreach ($applicantTypes as $applicantType)
                                                            <option value="{{ $applicantType->id }}">{{ $applicantType->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="noShow" class="form-label">
                                                        No Show
                                                    </label>
                                                    <select id="noShow" name="no_show" class="form-control">
                                                        <option value="" selected>How many times a no show to interview?</option>
                                                        <option value="0">0</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                    </select>
                                                </div>
                                                <!--end col-->
                                            </div>
                                            <!--end col-->

                                            <div class="col-lg-6">
                                                <div class="col-lg-12 mb-3">
                                                    <label for="lastname" class="form-label">
                                                        Lastname
                                                    </label>
                                                    <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Enter last name" required/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="phone" class="form-label">
                                                        Phone
                                                    </label>
                                                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter phone number" required/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="idVerified" class="form-label">
                                                        ID Verified
                                                    </label>
                                                    <select id="idVerified" name="id_verified" class="form-control">
                                                        <option value="" selected>Select Option</option>
                                                        <option value="No">No</option>
                                                        <option value="Yes">Yes</option>
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="town" class="form-label">
                                                        Town
                                                    </label>
                                                    <select id="town" name="town_id" class="form-control">
                                                        <option value="" selected>Select town</option>
                                                        @foreach ($towns as $town)
                                                            <option value="{{ $town->id }}">{{ $town->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="age" class="form-label">
                                                        Age
                                                    </label>
                                                    <input type="number" id="age" name="age" class="form-control" placeholder="Enter age" max="100" min="16"/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="race" class="form-label">
                                                        Race
                                                    </label>
                                                    <select id="race" name="race_id" class="form-control">
                                                        <option value="" selected>Select applicant race</option>
                                                        @foreach ($races as $race)
                                                            <option value="{{ $race->id }}">{{ $race->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="role" class="form-label">
                                                        Role
                                                    </label>
                                                    <select id="role" name="role_id" class="form-control">
                                                        <option value="" selected>Select applicant role</option>
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="duration" class="form-label">
                                                        Duration
                                                    </label>
                                                    <select id="duration" name="duration_id" class="form-control">
                                                        <option value="" selected>Select duration</option>
                                                        @foreach ($durations as $duration)
                                                            <option value="{{ $duration->id }}">{{ $duration->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="source" class="form-label">
                                                        Source
                                                    </label>
                                                    <select id="source" name="application_type" class="form-control">
                                                        <option value="" selected>Select applicant source</option>
                                                        <option value="Website">Website</option>
                                                        <option value="WhatsApp">WhatsApp</option>
                                                    </select>
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
                                            <button type="submit" class="btn btn-success" id="add-btn">Add Applicant</button>
                                            <button type="button" class="btn btn-success" id="edit-btn">Update Applicant</button>
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
                                            You are about to delete this applicant ?
                                        </h4>
                                        <p class="text-muted fs-14 mb-4 pt-1">
                                            Deleting this applicant will remove all of their information from the database.
                                        </p>
                                        <div class="hstack gap-2 justify-content-center remove">
                                            <button class="btn btn-danger" data-bs-dismiss="modal" id="deleteRecord-close">
                                                <i class="ri-close-line me-1 align-middle"></i>
                                                Close
                                            </button>
                                            <button class="btn btn-primary" id="delete-applicant">
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
                                    <td class="fw-medium" scope="row">Race</td>
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
    <script src="{{ URL::asset('build/js/pages/applicants-admin.init.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection