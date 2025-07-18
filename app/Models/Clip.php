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
        'video_path', 'wav_path', 'srt_path', 'hard_path','name_video',
        'lang', 'status', 'user_id',self::STATUS_HARD_DONE,self::STATUS_HARD_PROCESSING
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function scopeFinished($query){
        return $query->whereIn('status', ['ready',self::STATUS_HARD_PROCESSING, self::STATUS_HARD_DONE]);
    }
}
