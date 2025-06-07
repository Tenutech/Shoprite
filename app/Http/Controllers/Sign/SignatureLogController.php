<?php

namespace App\Http\Controllers\Sign;

use App\Http\Controllers\Controller;
use App\Models\SignatureLog;
use Illuminate\Http\Request;

class SignatureLogController extends Controller
{
    public function store($signatureFileId, $event, $email)
    {
        SignatureLog::create([
            'signature_file_id' => $signatureFileId,
            'event' => $event,
            'user_email' => $email,
            'ip_address' => request()->ip(),
            'timestamp' => now()
        ]);
    }
}