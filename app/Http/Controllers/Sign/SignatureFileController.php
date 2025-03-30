<?php

namespace App\Http\Controllers\Sign;

use App\Http\Controllers\Controller;
use App\Models\SignatureFile;
use App\Models\Signer;
use App\Models\SignatureLog;
use App\Services\Sign\PDFService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Notifications\Sign\SignatureRequestNotification;

class SignatureFileController extends Controller
{
    // List all signature files
    public function index()
    {
        $files = SignatureFile::latest()->paginate(10);
        return view('signature-files.index', compact('files'));
    }

    // Show the form to create a new signature file
    public function create()
    {
        return view('signature-files.create');
    }

    // Store a new signature file
    public function store(Request $request, PDFService $pdfService)
    {  
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf',
        ]);

        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput();
        }
       
        // Save the uploaded PDF
        $path = $request->file('file')->store('signatures');

        $signatureFile = SignatureFile::create([
            'uuid' => Str::uuid(),
            'user_id' => auth()->id(),
            'file_path' => $path,
            'status' => 'prepared',
        ]);

        return redirect()->route('signature-files.index')
            ->with('success', 'Document uploaded successfully!');
    }

    // Display a specific signature file
    public function show(SignatureFile $signatureFile)
    {
        return view('signature-files.show', compact('signatureFile'));
    }

    // Send the document to the signers
    public function send(Request $request, SignatureFile $signatureFile)
    {
        $validated = $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'email',
        ]);

        foreach ($validated['emails'] as $email) {
            $signer = Signer::create([
                'signature_file_id' => $signatureFile->id,
                'email' => $email,
                'status' => 'pending',
            ]);

            // Send notification to each signer
            $signer->notify(new SignatureRequestNotification($signer));
        }

        $signatureFile->update(['status' => 'sent']);

        return redirect()->route('signature-files.index')->with('success', 'Document sent to signers!');
    }
}