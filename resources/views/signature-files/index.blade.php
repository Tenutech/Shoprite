@extends('layouts.master')
@section('title', 'Signature Files')

@section('content')
<div class="d-flex justify-content-between">
    <h1>Signature Files</h1>
    <a href="{{ route('signature-files.create') }}" class="btn btn-primary">New Document</a>
</div>

<table class="table mt-3">
    <thead>
        <tr>
            <th>UUID</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($files as $file)
        <tr>
            <td>{{ $file->uuid }}</td>
            <td>{{ ucfirst($file->status) }}</td>
            <td>{{ $file->created_at->format('Y-m-d') }}</td>
            <td>
                <a href="{{ route('signature-files.show', $file) }}" class="btn btn-sm btn-info">View</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $files->links() }}
@endsection