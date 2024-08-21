@extends('layouts.master')
@section('title') Job Overview @endsection
@section('css')
<link href="{{ URL::asset('build/css/custom.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card mt-n4 mx-n4">
            <div class="bg-{{ $vacancy->position->color }}-subtle">
                <div class="card-body px-4 pb-4">
                    <div class="row mb-3">
                        <div class="col-md">
                            <div class="row align-items-center g-3">
                                <div class="col-md-auto">
                                    <div class="avatar-md">
                                        <div class="avatar-title bg-white rounded-circle">
                                            <i class="{{ $vacancy->position->icon }} text-{{ $vacancy->position->color }} fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div>
                                        <h4 class="fw-bold">
                                            {{ $vacancy->position->name }}
                                        </h4>
                                        <div class="hstack gap-3 flex-wrap">
                                            <div>
                                                <i class="ri-building-line align-bottom me-1"></i> 
                                                {{ $vacancy->store->brand->name }}
                                            </div>
                                            <div class="vr"></div>
                                            <div>
                                                <i class="ri-map-pin-2-line align-bottom me-1"></i> 
                                                {{ $vacancy->store->town->name }}, {{ $vacancy->store->town->district }}
                                            </div>
                                            <div class="vr"></div>
                                            <div>
                                                Posted : 
                                                <span class="fw-semibold">
                                                    {{ date('d M, Y', strtotime($vacancy->created_at)) }}
                                                </span>
                                            </div>
                                            <div class="vr"></div>
                                            <div class="badge rounded-pill bg-{{ $vacancy->type->color }} fs-12">
                                                {{ $vacancy->type->name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <div class="hstack gap-1 flex-wrap mt-4 mt-md-0">
                                @if ($user->role_id == 1)
                                    <a type="button" href="{{ route('vacancy.index', ['id' => Crypt::encryptString($vacancy->id)]) }}" class="btn btn-icon btn-sm btn-ghost-primary fs-16 custom-toggle">
                                        <span class="icon-on">
                                            <i class="ri-edit-box-line"></i>
                                        </span>
                                        <span class="icon-off">
                                            <i class="ri-edit-box-fill"></i>
                                        </span>
                                    </a>
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
                    {!! $vacancy->position->description !!}
                </p>

                <div class="mb-4">
                    <h5 class="mb-3">
                        Responsibilities of {{ $vacancy->position->name }}
                    </h5>
                    <p class="text-muted">
                        Provided below are the responsibilities of a {{ $vacancy->position->name }}:
                    </p>
                    <ul class="text-muted vstack gap-2">
                        @foreach ($vacancy->position->responsibilities as $responsibility)
                            <li>
                                {{ $responsibility->description }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mb-4">
                    <h5 class="mb-3">
                        Qualifications
                    </h5>
                    <ul class="text-muted vstack gap-2">
                        @foreach ($vacancy->position->qualifications as $qualification)
                            <li>
                                {{ $qualification->description }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mb-4">
                    <h5 class="mb-3">
                        Skills & Competencies
                    </h5>
                    <ul class="text-muted vstack gap-2">
                        @foreach ($vacancy->position->skills as $skill)
                            <li>
                                {{ $skill->description }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mb-4">
                    <h5 class="mb-3">
                        Requirements
                    </h5>
                    <ul class="text-muted vstack gap-2">
                        @foreach ($vacancy->position->experienceRequirements as $experience)
                            <li>
                                {{ $experience->description }}
                            </li>
                        @endforeach
                        @foreach ($vacancy->position->physicalRequirements as $physical)
                            <li>
                                {{ $physical->description }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mb-4">
                    <h5 class="mb-3">
                        Success & Factors
                    </h5>
                    <ul class="text-muted vstack gap-2">
                        @foreach ($vacancy->position->successFactors as $factor)
                            <li>
                                <b>{{ $factor->name }}:</b> {{ $factor->description }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if ($vacancy->position->files && $vacancy->position->files->count() > 0 || $user->role_id <= 2)
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3"> <!-- Flex container -->
                            <h5 class="fs-17 mb-0" id="filetype-title">
                                Documentation
                            </h5>
                            @if ($user->role_id == 1)
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#fileUploadModal">
                                    <i class="ri-upload-2-fill me-1 align-bottom"></i> 
                                    Upload File
                                </button>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap mb-0" id="fileTable">
                                <thead class="table-active">
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Size</th>
                                        <th scope="col">Upload Date</th>
                                        <th scope="col" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="file-list">
                                    @foreach ($vacancy->position->files as $file)
                                        @php
                                            $fileIcon = '';
                                        @endphp
                                        
                                        @switch($file->type)
                                            @case('png')
                                            @case('jpg')
                                            @case('jpeg')
                                                @php
                                                    $fileIcon = '<i class="ri-gallery-fill align-bottom text-success"></i>';
                                                @endphp
                                                @break
                                        
                                            @case('pdf')
                                                @php
                                                    $fileIcon = '<i class="ri-file-pdf-fill align-bottom text-danger"></i>';
                                                @endphp
                                                @break
                                        
                                            @case('docx')
                                                @php
                                                    $fileIcon = '<i class="ri-file-word-2-fill align-bottom text-primary"></i>';
                                                @endphp
                                                @break
                                        
                                            @case('xls')
                                            @case('xlsx')
                                                @php
                                                    $fileIcon = '<i class="ri-file-excel-2-fill align-bottom text-success"></i>';
                                                @endphp
                                                @break
                                        
                                            @case('csv')
                                                @php
                                                    $fileIcon = '<i class="ri-file-excel-fill align-bottom text-success"></i>';
                                                @endphp
                                                @break
                                        
                                            @case('txt')
                                            @default
                                                @php
                                                    $fileIcon = '<i class="ri-file-text-fill align-bottom text-secondary"></i>';
                                                @endphp
                                        @endswitch
                                        <tr data-file-id="{{ $file->id }}">
                                            <td>
                                                <a href="{{ route('file.view', ['id' => Crypt::encryptString($file->id)]) }}" target="_blank">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 fs-17 me-2 filelist-icon">{!! $fileIcon !!}</div>
                                                        <div class="flex-grow-1 filelist-name">{{ substr($file->name, 0, strrpos($file->name, '-')) }}</div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td>
                                                {{ $file->type }}
                                            </td>
                                            @php
                                                $fileSizeInMB = $file->size / (1024 * 1024);
                                                if ($fileSizeInMB < 0.1) {
                                                    $fileSizeInKB = number_format($file->size / 1024, 1);
                                                    $fileSizeText = "{$fileSizeInKB} KB";
                                                } else {
                                                    $fileSizeInMB = number_format($fileSizeInMB, 1);
                                                    $fileSizeText = "{$fileSizeInMB} MB";
                                                }
                                            @endphp
                                            <td class="filelist-size">                                            
                                                {{ $fileSizeText }}
                                            </td>
                                            <td class="filelist-create">
                                                {{ date('d M Y', strtotime($file->created_at)) }}
                                            </td>
                                            <td>
                                                <div class="d-flex gap-3 justify-content-center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-light btn-icon btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ri-more-fill align-bottom"></i>
                                                        </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item viewfile-list" href="{{ route('file.view', ['id' => Crypt::encryptString($file->id)]) }}" target="_blank">
                                                                View
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item downloadfile-list" href="{{ route('file.download', ['id' => Crypt::encryptString($file->id)]) }}">
                                                                Download
                                                            </a>
                                                        </li>
                                                        @if ($user->role_id == 1)
                                                            <li class="dropdown-divider"></li>
                                                            <li>
                                                                <button class="dropdown-item downloadfile-list" href="#fileDeleteModal" data-bs-toggle="modal" data-bs-id="{{ $file->id }}">
                                                                    Delete
                                                                </button>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-------------------------------------------------------------------------------------
            Related Jobs
        -------------------------------------------------------------------------------------->

        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex align-items-center mb-4">
                    <div class="flex-grow-1">
                        <h5 class="mb-0">Related Jobs</h5>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('vacancies.index') }}" class="btn btn-ghost-secondary">
                            View All 
                            <i class="ri-arrow-right-line ms-1 align-bottom"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                @foreach ($vacancies->take(6) as $relatedVacancy)
                    <div class="col-xl-4 pb-4">
                        <div class="card flex-grow-1 d-flex flex-column h-100"> <!-- Added mb-3 for margin between columns -->
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-4"> <!-- New div for button and avatar -->
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-{{ $relatedVacancy->position->color }}-subtle rounded">
                                            <i class="{{ $relatedVacancy->position->icon }} text-{{ $relatedVacancy->position->color }} fs-1"></i>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-icon btn-soft-primary vacancy-save {{ $relatedVacancy->savedBy->isNotEmpty() ? 'active' : '' }}" data-bs-toggle="button" aria-pressed="{{ $relatedVacancy->savedBy->isNotEmpty() ? 'true' : 'false' }}" data-bs-id="{{ Crypt::encryptString($relatedVacancy->id) }}">
                                        <i class="mdi mdi-bookmark fs-16"></i>
                                    </button>
                                </div>
                                
                                <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($relatedVacancy->id)]) }}">
                                    <h5>
                                        {{ $relatedVacancy->position->name }}
                                    </h5>
                                </a>
                                <p class="text-muted">{{ $relatedVacancy->type->name }}</p>
    
                                <div class="d-flex gap-4 mb-3">
                                    <div>
                                        <i class="ri-store-3-line text-primary me-1 align-bottom"></i> 
                                        {{ $relatedVacancy->store->brand->name }}
                                    </div>
                                    <div>
                                        <i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i> 
                                        {{ $relatedVacancy->store->town->name }}
                                    </div>
                                    <div>
                                        <i class="ri-time-line text-primary me-1 align-bottom"></i> 
                                        {{ date('d M Y', strtotime($relatedVacancy->created_at)) }}
                                    </div>
                                </div>
    
                                <p class="text-muted truncated-text-4-lines">
                                    {{ $relatedVacancy->position->description }}
                                </p>
            
                                <div class="mt-auto"> <!-- Added mt-auto here to push the content to the bottom -->
                                    <div class="hstack gap-2">
                                        @foreach ($relatedVacancy->position->tags as $tag)
                                            <span class="badge bg-{{ $tag->color }}-subtle text-{{ $tag->color }}">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
            
                                    <div class="mt-4 hstack gap-2">
                                        <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($relatedVacancy->id)]) }}" class="btn btn-soft-info w-100">
                                            Overview
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
                            <tr>
                                <td class="fw-semibold">
                                    Title
                                </td>
                                <td>
                                    {{ $vacancy->position->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">
                                    Company Name
                                </td>
                                <td>
                                    {{ $vacancy->store->brand->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">
                                    Location
                                </td>
                                <td>
                                    {{ $vacancy->store->town->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">
                                    Type
                                </td>
                                <td>
                                    <span class="badge bg-{{ $vacancy->type->color }}-subtle text-{{ $vacancy->type->color }}">
                                        {{ $vacancy->type->name }}
                                    </span>
                                </td>
                            </tr>
                            @if ($user->role_id <= 3)                   
                                <tr>
                                    <td class="fw-semibold">
                                        Applications
                                    </td>
                                    <td>
                                        {{ $vacancy->applicants->count() }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">
                                        Available
                                    </td>
                                    <td>
                                        {{ $vacancy->open_positions }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">
                                        Filled
                                    </td>
                                    <td>
                                        {{ $vacancy->filled_positions }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="fw-semibold">
                                    Post Date
                                </td>
                                <td>
                                    {{ date('d M, Y', strtotime($vacancy->created_at)) }}
                                </td>
                            </tr>
                            @if ($user->role_id == 1)
                                <tr>
                                    <td class="fw-semibold">
                                        Salary
                                    </td>
                                    <td>
                                        {{ $vacancy->position->salaryBenefits[0]->salary ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="fw-semibold">
                                    Experience
                                </td>
                                <td>
                                    {{ $vacancy->position->experienceRequirements[0]->description ?? 'N/A' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!--end table-->
                </div>
                <div class="mt-4 pt-2 hstack gap-2">
                    @if ($user->id == $vacancy->user_id && $user->role_id <= 3)
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
                        {{ $vacancy->store->town->name }}
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
                                    Founded in
                                </td>
                                <td>
                                    2016
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">
                                    Phone
                                </td>
                                <td>
                                    +(27) 79 874 9628
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">
                                    Email
                                </td>
                                <td>
                                    info@orient.com
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Social media</td>
                                <td>
                                    <ul class="list-inline mb-0">
                                        <li class="list-inline-item">
                                            <a href="#!"><i class="ri-whatsapp-line"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="#!"><i class="ri-facebook-line"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="#!"><i class="ri-twitter-line"></i></a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="#!"><i class="ri-youtube-line"></i></a>
                                        </li>
                                    </ul>
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
                    <iframe src="https://www.google.com/maps/embed/v1/place?key={{ config('services.googlemaps.key') }}&q={{ urlencode($vacancy->store->brand->name . ' ' . $vacancy->store->town->name) }}" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <!--end card-->

        <!-------------------------------------------------------------------------------------
            Saved Vacancies
        -------------------------------------------------------------------------------------->

        <div class="card">
            <div class="card-body" id="savedVacancies">
                <div class="avatar-sm mx-auto">
                    <div class="avatar-title bg-primary-subtle rounded">
                        <i class="mdi mdi-bookmark text-primary fs-3"></i>
                    </div>
                </div>
                <div class="text-center">
                    <a href="#!">
                        <h5 class="mt-3">Saved Vacancies</h5>
                    </a>
                </div>
                @foreach ($vacancies->take(6) as $vacancySaved)
                    @if ($vacancySaved->savedBy->isNotEmpty())
                        <div class="card card-height-100" id="vacancy-saved-{{ $vacancySaved->id }}">
                            <div class="card-body">
                                <div class="dropdown float-end">
                                    <button type="button" class="btn btn-icon btn-sm btn-ghost-primary fs-7 custom-toggle active vacancy-save" data-bs-toggle="button" aria-pressed="true" data-bs-id="{{ Crypt::encryptString($vacancySaved->id) }}">
                                        <span class="icon-on"><i class="ri-bookmark-line"></i></span>
                                        <span class="icon-off"><i class="ri-bookmark-fill"></i></span>
                                    </button>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="mb-1 pb-2 me-3">
                                        <i class="{{ $vacancySaved->position->icon }} text-{{ $vacancySaved->position->color }} fs-1"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('job-overview.index', ['id' => Crypt::encryptString($vacancySaved->id)]) }}">
                                            <h6 class="fs-15 fw-bold mb-0">
                                                {{ $vacancySaved->position->name }} 
                                                <span class="text-muted fs-13">
                                                    {{ $vacancySaved->type->name }}
                                                </span>
                                            </h6>
                                        </a>
                                        <p class="text-muted mt-1 mb-0">
                                            <i class="ri-bubble-chart-line align-bottom"></i> 
                                            {{ $vacancySaved->store->brand->name }}
                                            <span class="ms-2">
                                                <i class="ri-map-pin-2-line align-bottom"></i> 
                                                {{ $vacancySaved->store->town->name }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <!--end card-->

        <div class="card d-none">
            <div class="card-body" id="savedVacancies">
                <div class="avatar-sm mx-auto">
                    <div class="avatar-title bg-success-subtle rounded">
                        <i class="ri-open-arm-line text-success fs-3"></i>
                    </div>
                </div>
                <div class="text-center">
                    <a href="#!">
                        <h5 class="mt-3">Appointed</h5>
                    </a>
                </div>
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-lg-flex align-items-center">
                            <div class="flex-shrink-0 col-auto">
                                <div class="avatar-sm rounded overflow-hidden">
                                    <img src="/images/Johannes Burger-1700026747.jpg" alt="" class="member-img img-fluid d-block rounded">
                                </div>
                            </div>
                            <div class="ms-lg-3 my-3 my-lg-0 col-12 text-start">
                                <a href="http://127.0.0.1:8000/manager/applicant-profile?id=eyJpdiI6Im5jVUlzZjRZNEJvNU9iV3MrMmQyNEE9PSIsInZhbHVlIjoiczdVN0tZTklNeUFsQWNMRWs5NWMxUT09IiwibWFjIjoiYjYwNDZjMTgwMDBmNTA1ZDg1ODM5ZWJmYTBmYTlkYWIwYmM2Y2VlZjE4ZTBjMzMwODNmYTg5MTQ1OWZkNThlYyIsInRhZyI6IiJ9">
                                    <h5 class="fs-16 mb-2">
                                        Johannes Burger
                                    </h5>
                                </a>
                                <p class="text-muted mb-0">
                                    Baker
                                </p>
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end card-->

        <!-------------------------------------------------------------------------------------
            Contact Us
        -------------------------------------------------------------------------------------->

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Contact Us</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label for="nameInput" class="form-label">Name</label>
                        <input type="text" class="form-control" id="nameInput" placeholder="Enter your name">
                    </div>
                    <div class="mb-3">
                        <label for="emailInput" class="form-label">Email</label>
                        <input type="text" class="form-control" id="emailInput" placeholder="Enter your email">
                    </div>
                    <div class="mb-3">
                        <label for="messageInput" class="form-label">Message</label>
                        <textarea class="form-control" id="messageInput" rows="3" placeholder="Message"></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </div>
                </form>
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
