<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureLog extends Model
{
    use HasFactory;

    protected $fillable = ['signature_file_id', 'event', 'user_email', 'ip_address', 'timestamp'];

    public function file()
    {
        return $this->belongsTo(SignatureFile::class);
    }
}