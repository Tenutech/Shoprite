<?php

namespace App\Http\Controllers\Sign;

use App\Http\Controllers\Controller;
use App\Models\SignatureFile;
use App\Models\Signer;
use Illuminate\Http\Request;
use App\Services\Sign\SignatureService;
use App\Notifications\Sign\DocumentSignedNotification;

class SigningController extends Controller
{
    protected $signatureService;

    public function __construct(SignatureService $signatureService)
    {
        $this->signatureService = $signatureService;
    }

    public function sign(Request $request, SignatureFile $signatureFile, Signer $signer)
    {
        $signaturePath = $this->signatureService->storeSignature($request, $signatureFile, $signer);

        if (!$signaturePath) {
            return back()->withErrors(['error' => 'Failed to store signature.']);
        }

        if ($signatureFile->signers()->where('status', '!=', 'signed')->count() === 0) {
            $signatureFile->update(['status' => 'signed']);
            $signatureFile->user->notify(new DocumentSignedNotification($signatureFile));
        }

        return redirect()->route('signature-files.index')->with('success', 'Document signed successfully.');
    }
}