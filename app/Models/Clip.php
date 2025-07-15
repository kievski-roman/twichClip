<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clip extends Model
{
    //
    protected $fillable = [
        'uuid', 'slug', 'url',
        'video_path', 'wav_path', 'srt_path',
        'lang', 'status', 'user_id',
    ];
}
