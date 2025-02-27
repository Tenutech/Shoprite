@extends('layouts.master')
@section('title') @lang('translation.help') @endsection
@section('content')

@component('components.breadcrumb')
    @slot('li_1') Pages @endslot
    @slot('title') Help @endslot
@endcomponent

@section('css')
    <link href="{{ URL::asset('build/libs/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .image-container {
            text-align: center;
            margin: 10px 0;
        }
        
        .image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection

    <div class="row">
        <div class="col-lg-12">
            <div class="card rounded-0 bg-success-subtle mx-n4 mt-n4 border-top">
                <div class="px-4">
                    <div class="row">
                        <div class="col-xxl-5 align-self-center">
                            <div class="py-4">
                                <h4 class="display-6 coming-soon-text">
                                    Frequently asked questions
                                </h4>
                                <p class="text-success fs-16 mt-3">
                                    If you can not find answer to your question in our FAQ, you can always submit a query. We will respond ASAP!
                                </p>
                                @if ($user->role_id <= 1)
                                    <div class="hstack flex-wrap gap-2">
                                        <button type="button" class="btn btn-primary btn-label rounded-pill" data-bs-toggle="modal" data-bs-target="#queryModal">
                                            <i class="ri-login-circle-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                            Submit a Query
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-xxl-3 ms-auto">
                            <div class="mb-n5 pb-1 faq-img d-none d-xxl-block">
                                <img src="{{ URL::asset('build/images/faq-img.png') }}" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end card body -->
            </div>
            <!-- end card -->

            <div class="row justify-content-evenly mb-4">
                <div class="col-lg-6">
                    <div class="mt-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0 me-1">
                                <i class="ri-question-line fs-24 align-bottom text-success me-1"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fs-17 mb-0 fw-bold">General Questions</h5>
                            </div>
                        </div>

                        <div class="accordion accordion-border-box" id="genques-accordion">
                            @foreach ($generalFaqs as $faq)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="genques-heading{{ $faq->id }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#genques-collapse{{ $faq->id }}" aria-expanded="false" aria-controls="genques-collapse{{ $faq->id }}">
                                            {{ $faq->name }}
                                        </button>
                                    </h2>
                                    <div id="genques-collapse{{ $faq->id }}" class="accordion-collapse collapse" aria-labelledby="genques-heading{{ $faq->id }}" data-bs-parent="#genques-accordion">
                                        <div class="accordion-body">
                                            {!! $faq->description !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div><!--end accordion-->                        
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mt-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0 me-1">
                                <i class="ri-user-settings-line fs-24 align-bottom text-success me-1"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fs-17 mb-0 fw-bold">Account Management</h5>
                            </div>
                        </div>

                        <div class="accordion accordion-border-box" id="manageaccount-accordion">
                            @foreach ($accountFaqs as $faq)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="manageaccount-heading{{ $faq->id }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manageaccount-collapse{{ $faq->id }}" aria-expanded="false" aria-controls="manageaccount-collapse{{ $faq->id }}">
                                            {{ $faq->name }}
                                        </button>
                                    </h2>
                                    <div id="manageaccount-collapse{{ $faq->id }}" class="accordion-collapse collapse" aria-labelledby="manageaccount-heading{{ $faq->id }}" data-bs-parent="#manageaccount-accordion">
                                        <div class="accordion-body">
                                            {!! $faq->description !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div><!--end accordion-->
                    </div>
                </div>

                <div class="row justify-content-evenly mt-4 mb-4">
                    <div class="col-lg-12">
                        <div class="mt-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="flex-shrink-0 me-1">
                                    <i class="ri-shield-keyhole-line fs-24 align-bottom text-primary me-1"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fs-17 mb-0 fw-bold">Past Queries</h5>
                                </div>
                            </div>

                            <div class="accordion accordion-border-box" id="query-accordion">
                                @if($queries && count($queries) > 0)
                                    @foreach ($queries as $query)
                                        @php
                                            // Determine badge class based on status
                                            $badgeClass = 'bg-info';
                                            if ($query->status === 'Pending') {
                                                $badgeClass = 'bg-info';
                                            } elseif ($query->status === 'In Progress') {
                                                $badgeClass = 'bg-primary';
                                            } elseif ($query->status === 'Complete') {
                                                $badgeClass = 'bg-success';
                                            }

                                            // Process the body to wrap <img> tags in a container
                                            $processedBody = preg_replace(
                                                '/<img([^>]+)>/',
                                                '<div class="image-container"><img$1></div>',
                                                $query->body
                                            );
                                        @endphp
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="query-heading{{ $query->id }}">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#query-collapse{{ $query->id }}" aria-expanded="false" aria-controls="query-collapse{{ $query->id }}">
                                                    <div class="container-fluid">
                                                        <div class="row w-100">
                                                            <div class="col">
                                                                <span>{{ $query->subject }}</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <span class="badge {{ $badgeClass }}">{{ $query->status }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="query-collapse{{ $query->id }}" class="accordion-collapse collapse" aria-labelledby="query-heading{{ $query->id }}" data-bs-parent="#query-accordion">
                                                <div class="accordion-body">
                                                    <div>
                                                        <h6>Body:</h6>
                                                        {!! $processedBody !!}
                                                    </div>                                                    
                                                    @if ($query->answer)
                                                        <hr>
                                                        <div>
                                                            <h6>Answer:</h6>
                                                            <p class="text-danger">{!! $query->answer !!}</p>
                                                        </div>
                                                    @endif
                                                    <hr>
                                                    <div class="d-flex justify-content-between text-muted">
                                                        <small>
                                                            Created at: <b>{{ $query->created_at->format('Y/m/d H:i') }}</b> | 
                                                            Updated at: <b>{{ $query->updated_at->format('Y/m/d H:i') }}</b>
                                                        </small>
                                                        <small class="text-end">
                                                            <b>{{ optional($query->category)->name }}</b>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div><!--end accordion-->
                        </div>
                    </div>
                </div>
            </div>
        </div><!--end col-->
    </div><!--end row-->

    @if ($user->role_id <= 1)
        <!-- Modal Query -->
        <div class="modal fade zoomIn" id="queryModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content border-0">
                    <div class="modal-header bg-light p-3">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                    </div>
                    <form id="formQuery" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="field-id" name="field_id"/>
                        <div class="modal-body">
                            <div class="col-lg-12 mb-3">

                                <div class="mb-3">
                                    <label for="category" class="form-label">
                                        Category
                                    </label>
                                    <select class="form-control" id="category" name="category" data-choices data-choices-search-true>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" style="margin-top: -12px;">Please select a category</div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">
                                        Subject
                                    </label>
                                    <input type="text" class="form-control" id="subject" name="subject"/>
                                </div>

                                <div class="mb-3">
                                    <label for="body" class="form-label">
                                        Body
                                    </label>
                                    <div class="snow-editor" id="body" name="body" style="height: 500px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">                                        
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success" id="add-btn">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end modal-->
    @endif
@endsection
@section('script')
<script src="{{ URL::asset('build/libs/quill/quill.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/query.init.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
