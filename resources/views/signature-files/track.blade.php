@extends('layouts.master')
@section('title', 'Track Document')

@section('content')
<h1>Track Document</h1>

<p><strong>UUID:</strong> {{ $signatureFile->uuid }}</p>

<h3>Signer Progress</h3>
<ul>
    @foreach($signatureFile->signers as $signer)
    <li>{{ $signer->email }} - Status: <strong>{{ ucfirst($signer->status) }}</strong></li>
    @endforeach
</ul>
@endsection