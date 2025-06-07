@extends('layouts.master')
@section('title', 'Sign Document')

@section('content')
<h1>Sign Document</h1>

<canvas id="signature-pad" width="400" height="200" style="border: 1px solid black;"></canvas>
<button id="clear-signature" class="btn btn-warning">Clear</button>

<form action="{{ route('signing.process', ['signatureFile' => $signatureFile->id, 'signer' => $signer->id]) }}" method="POST">
    @csrf
    <input type="hidden" name="signature" id="signature-data">
    <button type="submit" class="btn btn-success">Submit Signature</button>
</form>

@section('script')
     <script src="{{ URL::asset('build/js/pgaes/signature-pad.js') }}"></script>
@endsection

@push('scripts')

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let canvas = document.getElementById('signature-pad');

        if (!canvas) {
            console.error("Canvas element not found!");
            return;
        }

        let signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'black'
        });

        console.log("SignaturePad initialized:", signaturePad);

        document.getElementById('clear-signature').addEventListener('click', function () {
            signaturePad.clear();
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert("Please provide a signature before submitting.");
            } else {
                document.getElementById('signature-data').value = signaturePad.toDataURL();
            }
        });
    });
</script>
@endpush
@endsection