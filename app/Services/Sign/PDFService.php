<?php

namespace App\Services\Sign;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PDFService
{
    public function generatePDF($data)
    {
        $pdf = Pdf::loadView('signature-files.pdf-template', ['data' => $data]);
        $path = 'signatures/' . uniqid() . '.pdf';
        Storage::put($path, $pdf->output());

        return $path;
    }
}
