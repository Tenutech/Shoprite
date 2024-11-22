<?php

namespace App\Http\Controllers\SignatureFiles;

use Illuminate\Http\Request;
use App\Models\SignatureFile;
use App\Models\SignatureFileEvent;

class AuditController extends Controller
{
    public function index(Request $request, $fileId)
    {
        $file = SignatureFile::findOrFail($fileId);
        $events = SignatureFileEvent::where('file_id', $file->id)->get();

        return view('audit.index', compact('file', 'events'));
    }
}
