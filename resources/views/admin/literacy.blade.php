@extends('layouts.master')
@section('title')
    @lang('translation.cards')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Assessments
        @endslot
        @slot('title')
            Literacy
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="justify-content-between d-flex align-items-center mt-3 mb-4">
                <h5 class="mb-0 pb-1 text-decoration-underline">
                    Literacy Questions
                </h5>
            </div>
            <div class="row g-4 mb-3">
                <div class="col-sm-auto">
                    <div>
                        <a class="btn btn-primary" id="create-btn" data-bs-toggle="modal" data-bs-target="#messageModal">
                            <i class="ri-add-line align-bottom me-1"></i>
                            Add New
                        </a>
                    </div>
                </div>
            </div>
            <div class="row" id="literacy-test">
                @foreach ($messages as $message)
                    <div class="col-xxl-12 col-lg-12" id="message-{{ $message->id }}">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col d-flex align-items-center">
                                        <div class="flex-shrink-0 me-2">
                                            <h6 class="card-title mb-0" id="question-{{ $message->id }}">
                                                Question {{ $message->sort }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col d-flex align-items-center justify-content-end dropdown">
                                        <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="">
                                            <i class="ri-more-2-fill fs-17"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end" style="">
                                            <li>
                                                <a href="#" class="dropdown-item edit-list" data-bs-toggle="modal" data-bs-target="#messageUpdateModal" data-edit-id="{{ Crypt::encryptstring($message->id) }}">
                                                    <i class="ri-pencil-line me-2 align-bottom text-muted"></i>
                                                    Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#" class="dropdown-item remove-list" data-bs-toggle="modal" data-bs-target="#messageDeleteModal" data-remove-id="{{ Crypt::encryptstring($message->id) }}">
                                                    <i class="ri-delete-bin-5-line me-2 align-bottom text-muted"></i>
                                                    Remove
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title" id="content-{{ $message->id }}">
                                    {!! nl2br(e($message->message)) !!}
                                </h6>
                            </div>
                            <div class="card-footer">
                                <a href="javascript:void(0);" class="link-success float-end" id="state-{{ Crypt::encryptstring($message->id) }}">
                                    {{ $message->state->name }}
                                    <i class="ri-arrow-right-s-line align-middle ms-1 lh-1"></i>
                                </a>
                                <p class="text-muted mb-0" id="answer-{{ $message->id }}">
                                    Answer:
                                    <span class="text-primary">
                                        {{ $message->answer }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endforeach
            </div><!-- end row -->
        </div><!-- end col -->
    </div><!-- end row -->

    <!-- Add Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Add Message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-add-modal"></button>
                </div>
                <form id="formMessage" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="message" class="form-label">
                                Message
                            </label>
                            <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="answe" class="form-label">
                                Answer
                            </label>
                            <select id="answer" name="answer" class="form-control" required>
                                <option value="" selected>Select Answer</option>
                                <option value="a">a</option>
                                <option value="b">b</option>
                                <option value="c">c</option>
                                <option value="d">d</option>
                                <option value="e">e</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="sort" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort" name="sort" required/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-success" id="add-btn">
                                Add Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end modal -->

    <!-- Update Modal -->
    <div class="modal fade" id="messageUpdateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Edit Message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-update-modal"></button>
                </div>
                <form id="formMessageUpdate" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="field-id" name="field_id"/>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="messageUpdate" class="form-label">
                                Message
                            </label>
                            <textarea class="form-control" id="messageUpdate" name="message" rows="6" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="answerUpdate" class="form-label">
                                Answer
                            </label>
                            <select id="answerUpdate" name="answer" class="form-control" required>
                                <option value="" selected>Select Answer</option>
                                <option value="a">a</option>
                                <option value="b">b</option>
                                <option value="c">c</option>
                                <option value="d">d</option>
                                <option value="e">e</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="sortUpdate" class="form-label">
                                Sort Order
                            </label>
                            <input type="number" class="form-control" id="sortUpdate" name="sort" required/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-success" id="update-message">
                                Update Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end modal -->

    <!-- Delete Modal -->
    <div class="modal fade zoomIn" id="messageDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" id="deleteRecord-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                </div>
                <div class="modal-body p-5 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#405189,secondary:#f06548" style="width:90px;height:90px"></lord-icon>
                    <div class="mt-4 text-center">
                        <h4 class="fs-semibold">
                            You are about to delete this message ?
                        </h4>
                        <p class="text-muted fs-14 mb-4 pt-1">
                            Deleting this message will remove all of the information from the database.
                        </p>
                        <div class="hstack gap-2 justify-content-center remove">
                            <button class="btn btn-danger" data-bs-dismiss="modal" id="deleteRecord-close">
                                <i class="ri-close-line me-1 align-middle"></i>
                                Close
                            </button>
                            <button class="btn btn-primary" id="delete-btn">
                                Yes, Delete!!
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end delete modal -->
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/pages/literacy.init.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
