@extends('layouts.master')
@section('title') Job Overview @endsection
@section('css')
<link href="{{ URL::asset('build/css/custom.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card mt-n4 mx-n4">
            <div class="bg-{{ optional($vacancy->position)->color ?? 'secondary' }}-subtle">
                <div class="card-body px-4 pb-4">
                    <div class="row mb-3">
                        <div class="col-md">
                            <div class="row align-items-center g-3">
                                <div class="col-md-auto">
                                    <div class="avatar-md">
                                        <div class="avatar-title bg-white rounded-circle">
                                            <i class="{{ optional($vacancy->position)->icon ?? 'ri-information-line' }} text-{{ optional($vacancy->position)->color ?? 'secondary' }} fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div>
                                        <h4 class="fw-bold">
                                            {{ optional($vacancy->position)->name ?? 'N/A' }}
                                        </h4>
                                        <div class="hstack gap-3 flex-wrap">
                                            <div>
                                                <i class="ri-building-line align-bottom me-1"></i> 
                                                {{ optional($vacancy->store->brand)->name ?? 'N/A' }}
                                            </div>
                                            <div class="vr"></div>
                                            <div>
                                                <i class="ri-map-pin-2-line align-bottom me-1"></i> 
                                                {{ optional($vacancy->store)->name ?? 'N/A' }}, {{ optional($vacancy->store->town)->name ?? 'N/A' }}
                                            </div>
                                            <div class="vr"></div>
                                            <div>
                                                Posted : 
                                                <span class="fw-semibold">
                                                    {{ $vacancy->created_at ? date('d M, Y', strtotime($vacancy->created_at)) : 'N/A' }}
                                                </span>
                                            </div>
                                            <div class="vr"></div>
                                            <div class="badge rounded-pill bg-{{ optional($vacancy->type)->color ?? 'secondary' }} fs-12">
                                                {{ optional($vacancy->type)->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <div class="hstack gap-1 flex-wrap mt-4 mt-md-0">
                                @if ($user->role_id <= 6 && $vacancy->open_positions > 0)
                                    <a type="button" href="{{ route('vacancy.index', ['id' => Crypt::encryptString($vacancy->id)]) }}" class="btn btn-icon btn-sm btn-ghost-primary fs-16 custom-toggle">
                                        <span class="icon-on">
                                            <i class="ri-edit-box-line"></i>
                                        </span>
                                        <span class="icon-off">
                                            <i class="ri-edit-box-fill"></i>
                                        </span>
                                    </a>
                                @endif
                                @if ($user->role_id == 1 && $vacancy->open_positions > 0)
                                    <button type="button" class="btn btn-icon btn-sm btn-ghost-primary fs-16" href="#vacancyDeleteModal" data-bs-toggle="modal" data-bs-id="{{ Crypt::encryptString($vacancy->id) }}">
                                        <span class="icon-on">
                                            <i class="ri-delete-bin-6-line"></i>
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end card body -->
            </div>
        </div>
        <!-- end card -->
    </div>
    <!--end col-->
</div>
<!--end row-->

<!-------------------------------------------------------------------------------------
    Job Details
-------------------------------------------------------------------------------------->

<div class="row mt-n5">
    <div class="col-xxl-9">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">
                    Job Description
                </h5>
                
                <p class="text-muted mb-4">
                    {!! optional($vacancy->position)->description ?? 'N/A' !!}
                </p>
                
                <div class="mb-4">
                    <h5 class="mb-3">
                        Purpose of a {{ optional($vacancy->position)->name ?? 'N/A' }}
                    </h5>
                    {!! optional(optional($vacancy->position->responsibilities)[0] ?? null)->description ?? 'N/A' !!}
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-3">
                        How You Add Value
                    </h5>
                    {!! optional(optional($vacancy->position->qualifications)[0] ?? null)->description ?? 'N/A' !!}
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-3">
                        What You Do Daily 
                    </h5>
                    {!! optional(optional($vacancy->position->skills)[0] ?? null)->description ?? 'N/A' !!}
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-3">
                        What Will Make You Great
                    </h5>
                    {!! optional(optional($vacancy->position->successFactors)[0] ?? null)->description ?? 'N/A' !!}
                </div>                

                @if ($vacancy->appointed && $vacancy->appointed->count() > 0 || $user->role_id <= 6)
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3"> <!-- Flex container -->
                            <h5 class="fs-17 mb-0" id="filetype-title">
                                Appointed
                            </h5>                            
                        </div>
                        <div class="row gy-2 mb-2">
                            <div data-simplebar style="max-height: 250px;" class="px-3">
                                @foreach ($vacancy->appointed as $applicant)
                                    <div class="col-md-6 col-lg-12">
                                        <div class="card mb-0">
                                            <div class="card-body">
                                                <div class="d-lg-flex align-items-center">
                                                    <div class="flex-shrink-0 col-auto">
                                                        <div class="avatar-sm rounded overflow-hidden">
                                                            {{-- Check if avatar is null, if so use a default image --}}
                                                            <img src="{{ $applicant->avatar ?? URL::asset('images/avatar.jpg') }}" alt="" class="member-img img-fluid d-block rounded">
                                                        </div>
                                                    </div>
                                                    <div class="ms-lg-3 my-3 my-lg-0 col-3 text-start">
                                                        <a href="{{ route('applicant-profile.index', ['id' => Crypt::encryptString($applicant->id ?? '')]) }}">
                                                            <h5 class="fs-16 mb-2">
                                                                {{-- Check if firstname or lastname is null --}}
                                                                {{ $applicant->firstname ?? 'N/A' }} {{ $applicant->lastname ?? 'N/A' }}
                                                            </h5>
                                                        </a>
                                                        <p class="text-muted mb-0">
                                                            {{-- Safely check if race name is null --}}
                                                            {{ optional($applicant->race)->name ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                    <div class="d-flex gap-4 mt-0 text-muted mx-auto col-2">
                                                        <div>
                                                            <i class="{{ optional($applicant->gender)->icon ?? 'ri-men-line' }} text-{{ optional($applicant->gender)->color ?? 'primary' }} me-1 align-bottom"></i>
                                                            {{-- Safely check if gender name is null --}}
                                                            <span class="badge bg-{{ optional($applicant->gender)->color ?? 'primary' }}-subtle text-{{ optional($applicant->gender)->color ?? 'primary' }}">
                                                                {{ optional($applicant->gender)->name ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-2">
                                                        <i class="ri-hashtag text-primary me-1 align-bottom"></i>
                                                        {{ optional($applicant->pivot)->sap_number ?? 'N/A' }}
                                                    </div>                                                                                        
                                                    <div class="d-flex flex-wrap gap-2 align-items-center mx-auto my-3 my-lg-0 col-1">
                                                        <div class="badge text-bg-success">
                                                            <i class="mdi mdi-star me-1"></i>
                                                            {{-- Check if score is null --}}
                                                            {{ $applicant->score ?? 'N/A' }}                                                                                            
                                                        </div>
                                                        @if($applicant->interviews && $applicant->interviews->count() > 0)
                                                            @php
                                                                $latestInterview = $applicant->interviews->last(); // Get the latest interview
                                                            @endphp
                                                            @if($applicant->latestInterview && $applicant->latestInterview->score)
                                                                <div class="badge text-bg-primary">
                                                                    <i class="mdi mdi-star me-1"></i>
                                                                    {{ $applicant->latestInterview->score ?? 'N/A' }}
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <div class="col-2 text-end">
                                                        <a href="{{ route('applicant-profile.index', ['id' => Crypt::encryptString($applicant->id ?? '')]) }}" class="btn btn-soft-primary">
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
                @endif
            </div>
        </div>        
    </div>

    <!-------------------------------------------------------------------------------------
        Job Overview
    -------------------------------------------------------------------------------------->

    <div class="col-xxl-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    Job Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table mb-0">
                        <tbody>
                            @if ($user->role_id <= 6)
                                <tr>
                                    <td class="fw-semibold">
                                        Identifier
                                    </td>
                                    <td>
                                        {{ $vacancy->id ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="fw-semibold">
                                    Title
                                </td>
                                <td>
                                    {{ optional($vacancy->position)->name ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">
                                    Brand
                                </td>
                                <td>
                                    {{ optional($vacancy->store->brand)->name ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">
                                    Location
                                </td>
                                <td>
                                    {{ optional($vacancy->store->town)->name ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">
                                    Type
                                </td>
                                <td>
                                    <span class="badge bg-{{ optional($vacancy->type)->color ?? 'secondary' }}-subtle text-{{ optional($vacancy->type)->color ?? 'secondary' }}">
                                        {{ optional($vacancy->type)->name ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                            @if ($user->role_id <= 6)
                                <tr>
                                    <td class="fw-semibold">
                                        Available
                                    </td>
                                    <td>
                                        {{ $vacancy->open_positions ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">
                                        Filled
                                    </td>
                                    <td>
                                        {{ $vacancy->filled_positions ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="fw-semibold">
                                    Post Date
                                </td>
                                <td>
                                    {{ $vacancy->created_at ? date('d M, Y', strtotime($vacancy->created_at)) : 'N/A' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!--end table-->
                </div>
                
                <!-- Button -->
                <div class="mt-4 pt-2 hstack gap-2">
                    @if ($user->id == $vacancy->user_id && $user->role_id <= 6)
                        <a class="btn btn-success w-100 apply-trigger" href="{{ route('shortlist.index') }}?id={{ Crypt::encryptString($vacancy->id) }}">
                            Shortlist
                        </a>
                    @else
                        @if ($userApplied)
                                <a class="btn btn-success w-100 apply-trigger" href="{{ route('job-overview.index', ['id' => Crypt::encryptString($vacancy->id)]) }}">
                                    Approved
                                </a>
                            @elseif ($userPendingApproval)
                                <button class="btn btn-warning w-100">
                                    Pending Approval
                                </button>
                            @elseif ($userDeclined)
                                <button class="btn btn-danger w-100">
                                    Declined
                                </button>
                            @else
                                <button class="btn btn-secondary w-100 apply-trigger" data-bs-toggle="modal" href="#applyModal" data-bs-id="{{ Crypt::encryptString($vacancy->id) }}">
                                    Apply Now
                                </button>
                            @endif
                        <a href="javascript: void(0);" class="btn btn-soft-danger btn-icon custom-toggle flex-shrink-0 {{ $vacancy->savedBy->isNotEmpty() ? 'active' : '' }} vacancy-save" data-bs-toggle="button" aria-pressed="{{ $vacancy->savedBy->isNotEmpty() ? 'true' : 'false' }}" data-bs-id="{{ Crypt::encryptString($vacancy->id) }}">
                            <span class="icon-on"><i class="ri-bookmark-line align-bottom"></i></span>
                            <span class="icon-off"><i class="ri-bookmark-3-fill align-bottom"></i></span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <!--end card-->
        <div class="card">
            <div class="card-body">
                <div class="avatar-xl mx-auto d-flex justify-content-center align-items-center mt-3 mb-3" style="height: auto;">
                    <img src="{{ URL::asset($vacancy->store->brand->icon) }}" alt="" style="width: 100%;">
                </div>
                <div class="text-center">
                    <p class="text-muted">
                        {{ optional($vacancy->store)->name ?: 'N/A' }}
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-semibold">
                                    Industry Type
                                </td>
                                <td>
                                    Retail
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">
                                    Phone
                                </td>
                                <td>
                                    0800 01 07 09
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">
                                    Email
                                </td>
                                <td>
                                    help@shoprite.co.za
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!--end table-->
                </div>
            </div>
        </div>
        <!--end card-->

        <!-------------------------------------------------------------------------------------
            Job Location
        -------------------------------------------------------------------------------------->

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Job Location</h5>
            </div>
            <div class="card-body">
                <div class="ratio ratio-4x3">
                    <iframe src="https://www.google.com/maps/embed/v1/place?key={{ config('services.googlemaps.key') }}&q={{ urlencode($vacancy->store->address) }}" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <!--end card-->
    </div>
</div>

<!-------------------------------------------------------------------------------------
    Modals
-------------------------------------------------------------------------------------->

<!-- Apply modal -->
<div class="modal fade zoomIn" id="applyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" id="apply-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/zpxybbhl.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:120px;height:120px"></lord-icon>
                <div class="mt-4 text-center">
                    <h4 class="fs-semibold">You are about to apply for this vacancy !</h4>
                    <p class="text-muted fs-14 mb-4 pt-1">Send application request ?</p>
                    <div class="hstack gap-2 justify-content-center remove">
                        <button class="btn btn-danger" data-bs-dismiss="modal" id="apply-close">
                            <i class="ri-close-line me-1 align-middle"></i>
                            Cancel
                        </button>
                        <button class="btn btn-primary vacancy-apply" id="apply" data-bs-id="">
                            Send Request !
                        </button>
                        <div class="spinner-border text-primary d-none" role="status" id="loading-apply">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end apply modal -->

@if ($user->role_id <= 2)
    <!-- File upload modal -->
    <div id="fileUploadModal" class="modal fade" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 overflow-hidden">
                <div class="modal-header p-3">
                    <h4 class="card-title mb-0">Upload File</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formFile" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="positionID" name="position_id" value="{{ Crypt::encryptString($vacancy->position->id) }}"/>
                        <div class="mb-3">
                            <input class="form-control" name="file" type="file" multiple="multiple" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" id="submitBtn" type="button">
                                Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- end file upload modal -->

    <!-- file delete modal -->
    <div class="modal fade flip" id="fileDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-5 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f06548,secondary:#f7b84b" style="width:90px;height:90px"></lord-icon>
                    <div class="mt-4 text-center">
                        <h4>
                            You are about to delete this file ?
                        </h4>
                        <p class="text-muted fs-14 mb-4">
                            Deleting this file will remove all of the information from the database.
                        </p>
                        <div class="hstack gap-2 justify-content-center remove">
                            <button class="btn btn-link btn-ghost-dark fw-medium text-decoration-none" data-bs-dismiss="modal" id="deleteFile-close">
                                <i class="ri-close-line me-1 align-middle"></i> 
                                Close
                            </button>
                            <button class="btn btn-danger" id="delete-file" data-bs-id="">
                                Yes, Delete It
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end file delete modal -->

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
@endif

@endsection
@section('script')
<!-- sweet alert -->
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/job-overview.init.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/vacancy-save.init.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/vacancy-apply.init.js') }}"></script>
<!-- App js -->
<script src="{{URL::asset('build/js/app.js')}}"></script>
@endsection
