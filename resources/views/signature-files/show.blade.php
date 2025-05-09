@extends('layouts.master')
@section('title', 'Document Details')

@section('content')
<h1>Document Details</h1>

<p><strong>UUID:</strong> {{ $signatureFile->uuid }}</p>
<p><strong>Status:</strong> {{ ucfirst($signatureFile->status) }}</p>

<h3>Signers</h3>
<ul>
    @foreach($signatureFile->signers as $signer)
    <li>{{ $signer->email }} - {{ ucfirst($signer->status) }}</li>
    @endforeach
</ul>

@foreach($signatureFile->signers as $signer)
    <li>
        {{ $signer->email }} - Status: <strong>{{ ucfirst($signer->status) }}</strong>
        @if($signer->status === 'pending')
            <a href="{{ route('signing.form', ['signatureFile' => $signatureFile->id, 'signer' => $signer->id]) }}" class="btn btn-sm btn-primary">
                Sign Document
            </a>
        @endif
    </li>
@endforeach

<a href="{{ route('signers.track', $signatureFile) }}" class="btn btn-primary">Track Progress</a>
@endsection