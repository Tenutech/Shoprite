<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureFile extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'user_id', 'file_path', 'status'];

    public function signers()
    {
        return $this->hasMany(Signer::class);
    }

    public function logs()
    {
        return $this->hasMany(SignatureLog::class);
    }
}