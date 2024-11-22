<?php

namespace App\Http\Controllers\SignatureFiles;

use Illuminate\Http\Request;
use App\Models\SignatureFile;
use App\Models\Signer;
use Illuminate\Support\Facades\Storage;

class SignerController extends Controller
{
    public function sign(Request $request, $token)
    {
        $signer = Signer::where('token', $token)->firstOrFail();
        $file = $signer->file;

        $request->validate([
            'signature' => 'required',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
        ]);

        $signature = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->input('signature')));
        $pdfPath = storage_path('app/' . $file->file_path);

        $signedPdfPath = storage_path('app/signed_files/' . $file->id . '_signed.pdf');
        $this->embedSignature($pdfPath, $signedPdfPath, $signature, $request->x, $request->y);

        $signer->update(['status' => 'signed']);
        $file->checkCompletion();

        return response()->json(['message' => 'File signed successfully.'], 200);
    }

    private function embedSignature($pdfPath, $signedPdfPath, $signature, $x, $y)
    {
    }
}
