<?php

namespace App\Http\Controllers\SignatureFiles;

use Illuminate\Http\Request;
use App\Models\SignatureFile;
use App\Models\Signer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf',
            'title' => 'required|string|max:255',
        ]);

        $path = $request->file('file')->store('files');

        $file = SignatureFile::create([
            'title' => $request->title,
            'file_path' => $path,
            'status' => 'prepared',
        ]);

        return response()->json(['message' => 'Document uploaded successfully.', 'file' => $file], 201);
    }

    public function addSigners(Request $request, $fileId)
    {
        $request->validate([
            'signers' => 'required|array',
            'signers.*.email' => 'required|email',
            'signers.*.name' => 'required|string',
        ]);

        $file = SignatureFile::findOrFail($fileId);

        foreach ($request->signers as $signer) {
            Signer::create([
                'file_id' => $file->id,
                'name' => $signer['name'],
                'email' => $signer['email'],
                'status' => 'pending',
            ]);
        }

        return response()->json(['message' => 'Signers added successfully.', 'file' => $file], 200);
    }

    public function send(Request $request, $fileId)
    {
        $file = SignatureFile::findOrFail($documentId);
        $signers = $file->signers;

        foreach ($signers as $signer) {
            $signedUrl = route('signers.sign', ['token' => $signer->createSignedUrl()]);
            Mail::to($signer->email)->send(new \App\Mail\SignatureFileSignRequest($signedUrl, $file));

            $signer->update(['status' => 'sent']);
        }

        $file->update(['status' => 'in_progress']);

        return response()->json(['message' => 'File sent to signers.', 'file' => $file], 200);
    }
}
