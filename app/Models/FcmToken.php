<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcmToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'device_token',
        'device_type',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
} 