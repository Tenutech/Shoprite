@extends('layouts.master')
@section('title') @lang('translation.signature_files') @endsection
@section('css')

@endsection
@section('content')
    <h1>Prepare Document for Signing</h1>
    <embed src="{{ asset('storage/' . $signatureFile->file_path) }}" type="application/pdf" width="100%" height="600px">

    <form action="{{ route('signature-process.send', $signatureFile->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="recipients" class="form-label">Recipients (comma-separated emails)</label>
            <input type="text" class="form-control" id="recipients" name="recipients" required>
        </div>
        <button type="submit" class="btn btn-primary">Send for Signing</button>
    </form>
@endsection