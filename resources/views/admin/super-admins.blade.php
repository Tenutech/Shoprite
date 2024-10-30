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
            Super Admins
        @endslot
    @endcomponent
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="flex-grow-1">
                            <button class="btn btn-info add-btn" data-bs-toggle="modal" data-bs-target="#usersModal">
                                <i class="ri-add-fill me-1 align-bottom"></i> 
                                Add Super Admin
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
            <div class="card" id="userList">
                <div class="card-header">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="search-box">
                                <input type="text" class="form-control search" placeholder="Search for user...">
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
                                    <option value="{{count($users)}}">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive table-card mb-3">
                            <table class="table align-middle table-nowrap mb-0" id="userTable">
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
                                        <th class="sort d-none" data-sort="id_number" scope="col">ID Number</th>
                                        <th class="sort d-none" data-sort="id_verified" scope="col">Verified</th>
                                        <th class="sort d-none" data-sort="birth_date" scope="col">Birth Date</th>
                                        <th class="sort d-none" data-sort="age" scope="col">Age</th>
                                        <th class="sort d-none" data-sort="gender" scope="col">Gender</th>
                                        <th class="sort d-none" data-sort="role" scope="col">Role</th>
                                        <th class="sort" data-sort="store" scope="col">Store</th>
                                        <th class="sort d-none" data-sort="division" scope="col">Division</th>
                                        <th class="sort d-none" data-sort="region" scope="col">Region</th>                                        
                                        <th class="sort d-none" data-sort="brand" scope="col">Brand</th>
                                        <th class="sort" data-sort="status" scope="col">Status</th>                          
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all" style="height:200px;">
                                    @if($users && count($users) > 0)
                                        @foreach ($users as $key => $user)
                                            <tr style="vertical-align:top;">
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="chk_child" value="option1">
                                                    </div>
                                                </th>
                                                <td class="id d-none">{{ Crypt::encryptstring($user->id) }}</td>
                                                <td class="name">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <img src="{{ URL::asset('storage/images/' . $user->avatar) }}" alt="" class="avatar-xs rounded-circle">
                                                        </div>
                                                        <div class="flex-grow-1 ms-2 name">{{ $user->firstname }} {{ $user->lastname }}</div>
                                                    </div>
                                                </td>
                                                <td class="email">{{ $user->email }}</td>
                                                <td class="phone">{{ $user->phone }}</td>
                                                <td class="id_number d-none">{{ $user->id_number }}</td>
                                                <td class="id_verified d-none">{{ $user->id_verified }}</td>
                                                <td class="birth_date d-none">{{ $user->birth_date ? date('d M, Y', strtotime($user->birth_date)) : '' }}</td>
                                                <td class="age d-none">{{ $user->age }}</td>
                                                <td class="gender d-none">{{ $user->gender ? $user->gender->name : '' }}</td>
                                                <td class="role d-none">{{ $user->role ? $user->role->name : '' }}</td>
                                                <td class="store" style="white-space: pre-wrap;">{{ $user->store ? optional($user->store->brand)->name.' ('.optional($user->store)->name.')' : '' }}</td>
                                                <td class="division d-none">{{ $user->division ? $user->division->name : '' }}</td>
                                                <td class="region d-none">{{ $user->region ? $user->region->name : '' }}</td>
                                                <td class="brand d-none">{{ $user->brand ? $user->brand->name : '' }}</td>
                                                <td class="status">
                                                    <span class="badge bg-{{ $user->status->color }}-subtle text-{{ $user->status->color }} text-uppercase">
                                                        {{ $user->status->name }}
                                                    </span>
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
                                                                    <li>
                                                                        <a class="dropdown-item password-item-btn" data-bs-toggle="modal" href="#resetPasswordModal">
                                                                            <i class="ri-lock-fill align-bottom me-2 text-muted"></i>
                                                                            Password
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
                                            <td class="id_number d-none"></td>
                                            <td class="id_verified d-none"></td>
                                            <td class="birth_date d-none"></td>
                                            <td class="age d-none"></td>
                                            <td class="gender d-none"></td>
                                            <td class="role d-none"></td>
                                            <td class="store" style="white-space: pre-wrap;"></td>                                            
                                            <td class="division d-none"></td>
                                            <td class="region d-none"></td>
                                            <td class="brand d-none"></td>
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
                                                                <li>
                                                                    <a class="dropdown-item password-item-btn" data-bs-toggle="modal" href="#resetPasswordModal">
                                                                        <i class="ri-lock-fill align-bottom me-2 text-muted"></i>
                                                                        Password
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

                    <!-- Modal Super Admin -->
                    <div class="modal fade zoomIn" id="usersModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content border-0">
                                <div class="modal-header p-3 bg-soft-primary-rainbow">
                                    <h5 class="modal-title" id="exampleModalLabel">
                                        Add Super Admin
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                </div>
                                <form id="formUser" action="post" enctype="multipart/form-data">
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
                                                        <option value="" selected>Select Gender</option>
                                                        @foreach ($genders as $gender)
                                                            <option value="{{ $gender->id }}">{{ $gender->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="store" class="form-label">
                                                        Store
                                                    </label>
                                                    <select id="store" name="store_id" class="form-control">
                                                        <option value="" selected>Select Store</option>
                                                        @foreach ($stores as $store)
                                                            <option value="{{ $store->id }}">{{ optional($store->brand)->name }} ({{ $store->name }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="region"class="form-label">
                                                        Region
                                                    </label>
                                                    <select id="region" name="region_id" class="form-control">
                                                        <option value="" selected>Select Region</option>
                                                        @foreach ($regions as $region)
                                                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!-- end col -->                                                                                                                      
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
                                                    <label for="age" class="form-label">
                                                        Age
                                                    </label>
                                                    <input type="number" id="age" name="age" class="form-control" placeholder="Enter age" max="100" min="16"/>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="role" class="form-label">
                                                        Role
                                                    </label>
                                                    <select id="role" name="role_id" class="form-control">
                                                        <option value="" selected>Select Role</option>
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col-->
                                                <div class="col-lg-12 mb-3">
                                                    <label for="division" class="form-label">
                                                        Division
                                                    </label>
                                                    <select id="division" name="division_id" class="form-control">
                                                        <option value="" selected>Select Division</option>
                                                        @foreach ($divisions as $division)
                                                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end col--> 
                                                <div class="col-lg-12 mb-3">
                                                    <label for="brand" class="form-label">
                                                        Brand
                                                    </label>
                                                    <select id="brand" name="brand_id" class="form-control">
                                                        <option value="" selected>Select Brand</option>
                                                        @foreach ($brands as $brand)
                                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                        @endforeach
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
                                            <button type="submit" class="btn btn-success" id="add-btn">Add Super Admin</button>
                                            <button type="button" class="btn btn-success" id="edit-btn">Update Super Admin</button>
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

                    <!-- Reset Password Modal -->
                    <div class="modal fade zoomIn" id="resetPasswordModal" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" id="resetPassword-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                                </div>
                                <div class="modal-body p-5 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/sjoccsdj.json" trigger="loop" style="width:90px;height:90px"></lord-icon>
                                    <div class="mt-4 text-center">
                                        <h4 class="fs-semibold">
                                            You are about to reset this user's password!
                                        </h4>
                                    </div>
                                    <form id="formPassword" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" id="password-id" name="password_id"/>
                                        <div class="mt-4">
                                            <div class="col-lg-12">
                                                <!-- Password -->
                                                <div class="mb-2">
                                                    <div class="position-relative auth-pass-inputgroup mb-3">
                                                        <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror" name="password" id="password" placeholder="Enter new password" autocomplete="off" required>
                                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon">
                                                            <i class="ri-eye-fill align-middle"></i>
                                                        </button>
                                                        @error('password')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ e($message) }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-12">
                                                <!-- Confirm Password -->
                                                <div class="mb-2">
                                                    <div class="position-relative auth-pass-inputgroup mb-3">
                                                        <input type="password" class="form-control pe-5 password-input-confirmation @error('password_confirmation') is-invalid @enderror" name="password_confirmation" id="input-password-confirmation" placeholder="Confirm new password" autocomplete="off" required>
                                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon-confirmation">
                                                            <i class="ri-eye-fill align-middle"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>                                        

                                            <div class="hstack gap-2 justify-content-center remove">
                                                <button class="btn btn-light" data-bs-dismiss="modal" id="resetPassword-close">
                                                    <i class="ri-close-line me-1 align-middle"></i>
                                                    Close
                                                </button>
                                                <button class="btn btn-success" id="password-reset">
                                                    Reset Password!
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end password modal -->
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
                                    <td class="fw-medium" scope="row">Store</td>
                                    <td></td>
                                </tr>                                
                                <tr>
                                    <td class="fw-medium" scope="row">Division</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Region</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Brand</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium" scope="row">Role</td>
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
    <script src="{{ URL::asset('build/js/pages/super-admins.init.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
