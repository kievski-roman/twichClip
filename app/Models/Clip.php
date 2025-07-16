<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clip extends Model
{
    //
    // Clip.php або просто в коментарі для себе:
    const STATUS_HARD_PROCESSING = 'hard_processing';
    const STATUS_HARD_DONE       = 'hard_done';

    protected $fillable = [
        'uuid', 'slug', 'url',
        'video_path', 'wav_path', 'srt_path',
        'lang', 'status', 'user_id',self::STATUS_HARD_DONE,self::STATUS_HARD_PROCESSING
    ];
}
