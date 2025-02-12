<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signer extends Model
{
    use HasFactory;

    protected $fillable = ['signature_file_id', 'user_id', 'email', 'status', 'signature_path', 'signed_at'];

    public function file()
    {
        return $this->belongsTo(SignatureFile::class);
    }
}