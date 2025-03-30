<!DOCTYPE html>
<html>
<head>
    <title>Signature Document</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .signature { border-top: 1px solid black; width: 200px; text-align: center; margin-top: 30px; }
    </style>
</head>
<body>

<h1>Signature Document</h1>
<p>This document has been electronically signed.</p>

@foreach($data['signers'] as $signer)
    <div>
        <p>Signed by: {{ $signer['email'] }}</p>
        <img src="{{ storage_path('app/' . $signer['signature_path']) }}" width="200">
        <div class="signature">Signature</div>
    </div>
@endforeach

</body>
</html>