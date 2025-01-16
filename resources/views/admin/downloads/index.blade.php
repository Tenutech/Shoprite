@extends('layouts.master')

@section('content')
<div class="container">
    <h1>Your Downloads</h1>
    <table class="table">
        <thead>
            <tr>
                <th>File Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($downloads as $download)
            <tr>
                <td>{{ $download->file_name ?? 'Processing...' }}</td>
                <td>{{ ucfirst($download->type) }}</td>
                <td>{{ ucfirst($download->status) }}</td>
                <td>
                    @if($download->status === 'in_progress')
                        {{ $download->progress }}%
                    @elseif($download->status === 'completed')
                        100%
                    @else
                        ---
                    @endif
                </td>
                <td>
                    @if ($download->status === 'completed')
                        <a href="{{ url('storage/' . $download->file_path) }}" download>Download</a>
                    @elseif ($download->status === 'failed')
                        <span style="color: red;">Failed: {{ $download->error_message }}</span>
                    @else
                        <span>Processing...</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection