<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Jobs\ProcessDownload;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function index()
    {
        $downloads = Download::where('user_id', auth()->id())->latest()->get();
        return view('admin/downloads.index', compact('downloads'));
    }

    public function download(Download $download)
    {
        if ($download->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($download->status !== 'completed') {
            return redirect()->route('downloads.index')->with('error', 'File not ready for download.');
        }

        return response()->download($download->file_path, $download->file_name);
    }
}
