@extends('layouts.master')
@section('title') @lang('translation.signature_files') @endsection
@section('css')

@endsection
@section('content')
    <h1>Audit Trail for {{ $signatureFile->title }}</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Event</th>
                <th>User</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($auditTrails as $audit)
                <tr>
                    <td>{{ $audit->event }}</td>
                    <td>{{ $audit->user }}</td>
                    <td>{{ $audit->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection