@extends('layouts.master')
@section('title') @lang('translation.signature_files') @endsection
@section('css')

@endsection
@section('content')
    <h1>Upload Document</h1>
    <form action="{{ route('signature-files.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="file" class="form-label">Upload File</label>
            <input type="file" class="form-control" id="file" name="file" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
@endsection