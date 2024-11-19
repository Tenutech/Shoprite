@extends('layouts.master')
@section('title') @lang('translation.signature_files') @endsection
@section('css')

@endsection
@section('content')
    <h1>{{ $signatureFile->title }}</h1>
    <embed src="{{ asset('storage/' . $signatureFile->file_path) }}" type="application/pdf" width="100%" height="600px">

    <div class="mt-3">
        <h3>Status: {{ $signatureFile->status }}</h3>
        <a href="{{ route('signature-process.prepare', $signatureFile->id) }}" class="btn btn-primary">Prepare for Signing</a>
    </div>
@endsection