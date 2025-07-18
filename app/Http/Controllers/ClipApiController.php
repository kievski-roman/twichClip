<?php

namespace App\Http\Controllers;

use App\Models\Clip;
use Illuminate\Http\Request;

class ClipApiController extends Controller
{
    //
    public function status(Clip $clip)
    {
        return [
            'status' => $clip->status,
            // якщо вже готово — даємо готовий URL для скачування
            'url'    => $clip->status === Clip::STATUS_HARD_DONE
                ? route('clips.download', $clip)
                : null,
        ];
    }
}
