<?php

namespace App\Services\Sign;

use App\Models\SignatureFile;
use App\Models\Signer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class SignatureService
{
    public function storeSignature(Request $request, SignatureFile $signatureFile, Signer $signer)
    {
        $signaturePath = $request->file('signature')->store('signatures');

        $signer->update([
            'status' => 'signed',
            'signature_path' => $signaturePath,
            'signed_at' => now()
        ]);

        if ($signatureFile->signers()->where('status', '!=', 'signed')->count() === 0) {
            $signatureFile->update(['status' => 'signed']);
        }

        return $signaturePath;
    }
}