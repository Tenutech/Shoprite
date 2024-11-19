@extends('layouts.master')
@section('title') @lang('translation.signature_files') @endsection
@section('css')

@endsection
@section('content')
    <h1>Document List</h1>
    <a href="{{ route('signature-files.create') }}" class="btn btn-primary mb-3">Upload New Document</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($signatureFiles as $file)
                <tr>
                    <td>{{ $file->id }}</td>
                    <td>{{ $file->title }}</td>
                    <td>{{ $file->status }}</td>
                    <td>
                        <a href="{{ route('signature-files.show', $file->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('signature-files.edit', $file->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('signature-files.destroy', $file->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No documents found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection