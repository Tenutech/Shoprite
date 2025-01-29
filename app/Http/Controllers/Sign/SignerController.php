<?php

namespace App\Http\Controllers\Sign;

use App\Http\Controllers\Controller;
use App\Models\SignatureFile;
use App\Models\Signer;
use App\Models\SignatureLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Notifications\Sign\DocumentSignedNotification;

class SignerController extends Controller
{
    public function sign(Request $request, Signer $signer)
    {
        if ($signer->status !== 'pending') {
            return redirect()->route('home')->with('error', 'This document has already been signed or declined.');
        }

        return view('signers.sign', compact('signer'));
    }

    public function track(SignatureFile $signatureFile)
    {
        $signers = $signatureFile->signers;

        return view('signature-files.track', compact('signatureFile', 'signers'));
    }

    public function completeSign(Request $request, Signer $signer)
    {
        $validated = $request->validate([
            'signature' => 'required|string',
        ]);

        $signaturePath = 'signatures/' . Str::uuid() . '.png';
        Storage::put($signaturePath, base64_decode($validated['signature']));

        $signer->update([
            'signature_path' => $signaturePath,
            'status' => 'signed',
            'signed_at' => now(),
        ]);

        $signer->signatureFile->logs()->create([
            'event' => 'signed',
            'user_email' => $signer->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $signer->signatureFile->update(['status' => 'signed']);
        $signer->signatureFile->user->notify(new DocumentSignedNotification($signer->signatureFile));

        return redirect()->route('home')->with('success', 'Document signed successfully!');
    }
}