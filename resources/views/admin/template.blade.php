@extends('layouts.master')
@section('title')
    @lang('translation.cards')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Interview
        @endslot
        @slot('title')
            Templates
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="flex-grow-1">
                            <h5 class="mb-0 pb-1 text-decoration-underline">
                                Template {{ $templateID }}
                            </h5>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="hstack text-nowrap gap-2">
                                <a class="btn btn-primary" id="create-btn" data-bs-toggle="modal" data-bs-target="#questionModal">
                                    <i class="ri-add-line align-bottom me-1"></i> 
                                    Add Question
                                </a>
                                <button class="btn btn-soft-danger" data-bs-toggle="modal" data-bs-target="#templateDeleteModal">
                                    <i class="ri-delete-bin-2-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
        
        <div class="col-12">
            <div class="row" id="template-questions">
                @foreach ($questions as $question)
                    <div class="col-xxl-12 col-lg-12" id="message-{{ $question->id }}">
                        <div class="card">
                            <div class="card-header">                               
                                <div class="row">
                                    <div class="col d-flex align-items-center">
                                        <div class="flex-shrink-0 me-2">
                                            <h6 class="card-title mb-0" id="question-{{ $question->id }}">
                                                Question {{ $question->sort }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col d-flex align-items-center justify-content-end dropdown">
                                        <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="">
                                            <i class="ri-more-2-fill fs-17"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end" style="">
                                            <li>
                                                <a class="dropdown-item edit-list" data-bs-toggle="modal" data-bs-target="#questionUpdateModal" data-edit-id="{{ Crypt::encryptstring($question->id) }}">
                                                    <i class="ri-pencil-line me-2 align-bottom text-muted"></i>
                                                    Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item remove-list" data-bs-toggle="modal" data-bs-target="#questionDeleteModal" data-remove-id="{{ Crypt::encryptstring($question->id) }}">
                                                    <i class="ri-delete-bin-5-line me-2 align-bottom text-muted"></i>
                                                    Remove
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <label class="form-label fs-16" style="width:100%;">
                                    <div class="row" style="width:100%;">
                                        <div class="col-sm-12" id="content-{{ $question->id }}">
                                            {!! $question->question !!}
                                        </div>
                                    </div>
                                </label>
                                <div class="col-sm-12" id="answer-container-{{ $question->id }}">
                                    @if($question->type == 'text')
                                        <input type="text" class="form-control" name="answers[{{$question->id}}]">
                                    @elseif($question->type == 'number')
                                        <input type="number" class="form-control" name="answers[{{$question->id}}]">
                                    @elseif($question->type == 'rating')
                                        <div class="form-check">
                                            <input class="form-check-input d-none" type="hidden" name="answers[{{$question->id}}]" id="rating-{{$question->id}}">
                                            <label class="form-check-label" for="rating-{{$question->id}}-1" style="cursor: pointer; margin-right:20px;">
                                                <i class="ri-star-line" id="star-{{$question->id}}-1" style="font-size: 1.5em; color: grey;"></i>
                                            </label>
                                            <label class="form-check-label" for="rating-{{$question->id}}-2" style="cursor: pointer; margin-right:20px;">
                                                <i class="ri-star-line" id="star-{{$question->id}}-2" style="font-size: 1.5em; color: grey;"></i>
                                            </label>
                                            <label class="form-check-label" for="rating-{{$question->id}}-3" style="cursor: pointer; margin-right:20px;">
                                                <i class="ri-star-line" id="star-{{$question->id}}-3" style="font-size: 1.5em; color: grey;"></i>
                                            </label>
                                            <label class="form-check-label" for="rating-{{$question->id}}-4" style="cursor: pointer; margin-right:20px;">
                                                <i class="ri-star-line" id="star-{{$question->id}}-4" style="font-size: 1.5em; color: grey;"></i>
                                            </label>
                                            <label class="form-check-label" for="rating-{{$question->id}}-5" style="cursor: pointer; margin-right:20px;">
                                                <i class="ri-star-line" id="star-{{$question->id}}-5" style="font-size: 1.5em; color: grey;"></i>
                                            </label>
                                            <span class="invalid-feedback" role="alert" style="display:none">
                                                <strong>Please select a rating</strong>
                                            </span>
                                        </div>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                let stars = document.querySelectorAll('[id^="star-{{$question->id}}-"]');
                                                stars.forEach(star => {
                                                    star.addEventListener('click', function() {
                                                        let rating = parseInt(star.id.split('-').pop());
                                                        for (let i = 1; i <= rating; i++) {
                                                            document.querySelector('#star-{{$question->id}}-' + i).classList.remove('ri-star-line');
                                                            document.querySelector('#star-{{$question->id}}-' + i).classList.add('ri-star-fill');
                                                            document.querySelector('#star-{{$question->id}}-' + i).style.color = 'gold';
                                                        }
                                                        for (let i = rating + 1; i <= 5; i++) {
                                                            document.querySelector('#star-{{$question->id}}-' + i).classList.remove('ri-star-fill');
                                                            document.querySelector('#star-{{$question->id}}-' + i).classList.add('ri-star-line');
                                                            document.querySelector('#star-{{$question->id}}-' + i).style.color = 'grey';
                                                        }
                                                        document.querySelector('#rating-{{$question->id}}').value = rating;
                                                    });
                                                });
                                            });
                                        </script>
                                    @elseif($question->type == 'textarea')
                                        <textarea class="form-control" name="answers[{{$question->id}}]" rows="5"></textarea>
                                    @endif                                                       
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="javascript:void(0);" class="link-success float-end" id="type-{{ $question->id }}">
                                    {{ $question->type }} 
                                    <i class="ri-arrow-right-s-line align-middle ms-1 lh-1"></i>
                                </a>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endforeach
            </div><!-- end row -->
        </div><!-- end col -->
    </div><!-- end row -->

    <!-- Add Modal -->
    <div class="modal fade" id="questionModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Add Question
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-add-modal"></button>
                </div>
                <form id="formQuestion" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="template-id" name="template_id" value="{{ Crypt::encryptstring($templateID) }}"/>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="question" class="form-label">
                                Question
                            </label>
                            <div class="snow-editor" id="question" style="height: 200px;"></div>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">
                                Type
                            </label>
                            <select id="type" name="type" class="form-control" required>
                                <option value="" disabled>Select Type</option>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="rating" selected>Rating</option>
                                <option value="textarea">Text Area</option>
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
    <div class="modal fade" id="questionUpdateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Edit Question
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-update-modal"></button>
                </div>
                <form id="formQuestionUpdate" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="field-id" name="field_id"/>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="questionUpdate" class="form-label">
                                Question
                            </label>
                            <div class="snow-editor" id="questionUpdate" style="height: 200px;"></div>
                        </div>

                        <div class="mb-3">
                            <label for="typeUpdate" class="form-label">
                                Answer
                            </label>
                            <select id="typeUpdate" name="type" class="form-control" required>
                                <option value="" disabled>Select Type</option>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="rating" selected>Rating</option>
                                <option value="textarea">Text Area</option>
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
    <div class="modal fade zoomIn" id="questionDeleteModal" tabindex="-1" aria-hidden="true">
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

    <!-- Template Delete Modal -->
    <div class="modal fade zoomIn" id="templateDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" id="deleteRecord-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close"></button>
                </div>
                <div class="modal-body p-5 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f06548,secondary:#ffc84b" style="width:90px;height:90px"></lord-icon>
                    <div class="mt-4 text-center">
                        <h4 class="fs-semibold">
                            You are about to delete this template ?
                        </h4>
                        <p class="text-muted fs-14 mb-4 pt-1">
                            Deleting this message will remove all of the information and associated questions from the database.
                        </p>
                        <div class="hstack gap-2 justify-content-center remove">
                            <button class="btn btn-light" data-bs-dismiss="modal" id="deleteRecord-close">
                                <i class="ri-close-line me-1 align-middle"></i>
                                Close
                            </button>
                            <button class="btn btn-danger" id="delete-template-btn" data-template-id="{{ Crypt::encryptstring($templateID) }}">
                                Yes, Delete!!
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end template delete modal -->
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/quill/quill.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/template.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
