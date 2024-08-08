@extends('layouts.master')
@section('title') @lang('translation.help') @endsection
@section('content')

@component('components.breadcrumb')
    @slot('li_1') Pages @endslot
    @slot('title') Help @endslot
@endcomponent

@section('css')
    <link href="{{ URL::asset('build/libs/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" />
@endsection

    <div class="row">
        <div class="col-lg-12">
            <div class="card rounded-0 bg-success-subtle mx-n4 mt-n4 border-top">
                <div class="px-4">
                    <div class="row">
                        <div class="col-xxl-5 align-self-center">
                            <div class="py-4">
                                <h4 class="display-6 coming-soon-text">Frequently asked questions</h4>
                                <p class="text-success fs-16 mt-3">If you can not find answer to your question in our FAQ, you can always contact us or email us. We will answer you shortly!</p>
                                <div class="hstack flex-wrap gap-2">
                                    <button type="button" class="btn btn-primary btn-label rounded-pill" data-bs-toggle="modal" data-bs-target="#queryModal">
                                        <i class="ri-mail-line label-icon align-middle rounded-pill fs-16 me-2"></i> 
                                        Send a Query
                                    </button>
                                </div>
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
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="genques-headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#genques-collapseOne" aria-expanded="flase" aria-controls="genques-collapseOne">
                                        What is Lorem Ipsum ?
                                    </button>
                                </h2>
                                <div id="genques-collapseOne" class="accordion-collapse collapse" aria-labelledby="genques-headingOne" data-bs-parent="#genques-accordion">
                                    <div class="accordion-body">
                                        If several languages coalesce, the grammar of the resulting language is more simple and regular than that of the individual languages. The new common language will be more simple and regular than the existing European languages. It will be as simple their most common words.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="genques-headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#genques-collapseTwo" aria-expanded="false" aria-controls="genques-collapseTwo">
                                        Why do we use it ?
                                    </button>
                                </h2>
                                <div id="genques-collapseTwo" class="accordion-collapse collapse" aria-labelledby="genques-headingTwo" data-bs-parent="#genques-accordion">
                                    <div class="accordion-body">
                                        The new common language will be more simple and regular than the existing European languages. It will be as simple as Occidental; in fact, it will be Occidental. To an English person, it will seem like simplified English, as a skeptical Cambridge friend of mine told me what Occidental is.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="genques-headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#genques-collapseThree" aria-expanded="false" aria-controls="genques-collapseThree">
                                        Where does it come from ?
                                    </button>
                                </h2>
                                <div id="genques-collapseThree" class="accordion-collapse collapse" aria-labelledby="genques-headingThree" data-bs-parent="#genques-accordion">
                                    <div class="accordion-body">
                                        he wise man therefore always holds in these matters to this principle of selection: he rejects pleasures to secure other greater pleasures, or else he endures pains to avoid worse pains.But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="genques-headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#genques-collapseFour" aria-expanded="false" aria-controls="genques-collapseFour">
                                        Where can I get some ?
                                    </button>
                                </h2>
                                <div id="genques-collapseFour" class="accordion-collapse collapse" aria-labelledby="genques-headingFour" data-bs-parent="#genques-accordion">
                                    <div class="accordion-body">
                                        Cras ultricies mi eu turpis hendrerit fringilla. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Nam pretium turpis et arcu arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis aliquam ultrices mauris.
                                    </div>
                                </div>
                            </div>
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
                                <h5 class="fs-17 mb-0 fw-bold">Manage Account</h5>
                            </div>
                        </div>

                        <div class="accordion accordion-border-box" id="manageaccount-accordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="manageaccount-headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manageaccount-collapseOne" aria-expanded="false" aria-controls="manageaccount-collapseOne">
                                        Where can I get some ?
                                    </button>
                                </h2>
                                <div id="manageaccount-collapseOne" class="accordion-collapse collapse" aria-labelledby="manageaccount-headingOne" data-bs-parent="#manageaccount-accordion">
                                    <div class="accordion-body">
                                        If several languages coalesce, the grammar of the resulting language is more simple and regular than that of the individual languages. The new common language will be more simple and regular than the existing European languages. It will be as simple their most common words.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="manageaccount-headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manageaccount-collapseTwo"  aria-expanded="false" aria-controls="manageaccount-collapseTwo">
                                        Where does it come from ?
                                    </button>
                                </h2>
                                <div id="manageaccount-collapseTwo" class="accordion-collapse collapse" aria-labelledby="manageaccount-headingTwo" data-bs-parent="#manageaccount-accordion">
                                    <div class="accordion-body">
                                        The new common language will be more simple and regular than the existing European languages. It will be as simple as Occidental; in fact, it will be Occidental. To an English person, it will seem like simplified English, as a skeptical Cambridge friend of mine told me what Occidental is.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="manageaccount-headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manageaccount-collapseThree" aria-expanded="false" aria-controls="manageaccount-collapseThree">
                                        Why do we use it ?
                                    </button>
                                </h2>
                                <div id="manageaccount-collapseThree" class="accordion-collapse collapse" aria-labelledby="manageaccount-headingThree" data-bs-parent="#manageaccount-accordion">
                                    <div class="accordion-body">
                                        he wise man therefore always holds in these matters to this principle of selection: he rejects pleasures to secure other greater pleasures, or else he endures pains to avoid worse pains.But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="manageaccount-headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manageaccount-collapseFour" aria-expanded="false" aria-controls="manageaccount-collapseFour">
                                        What is Lorem Ipsum ?
                                    </button>
                                </h2>
                                <div id="manageaccount-collapseFour" class="accordion-collapse collapse" aria-labelledby="manageaccount-headingFour" data-bs-parent="#manageaccount-accordion">
                                    <div class="accordion-body">
                                        Cras ultricies mi eu turpis hendrerit fringilla. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Nam pretium turpis et arcu arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis aliquam ultrices mauris.
                                    </div>
                                </div>
                            </div>
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
                                                        {!! $query->body !!}
                                                    </div>                                                    
                                                    @if ($query->answer)
                                                        <hr>
                                                        <div>
                                                            <h6>Answer:</h6>
                                                            <p class="text-danger">{!! $query->answer !!}</p>
                                                        </div>
                                                    @endif
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



@endsection
@section('script')
<script src="{{ URL::asset('build/libs/quill/quill.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/query.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
