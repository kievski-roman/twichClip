<?php

namespace App\Http\Controllers;

use App\Jobs\DownloadClipJob;
use App\Models\Clip;
use App\Services\TwitchApiService;
use Illuminate\Http\Request;
use App\Services\VideoDownloaderService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClipController extends Controller
{

    protected TwitchApiService $twitch;

    public function __construct(TwitchApiService $twitch)
    {
        $this->twitch = $twitch;
    }
    public function showForm()
{
    return view('clip-form');
}

    public function getClips(string $username)
    {
        $userId = $this->twitch->getUserIdByName($username);

        if (!$userId) {
            return back()->withErrors(['username' => 'Користувача не знайдено']);
        }

        $clips = $this->twitch->getClipsByUserId($userId);

        $clips = collect($clips)->map(function ($clip) {
            $slug = basename($clip['url']);
            $clip['filename'] = $slug . '.mp4';
            return $clip;
        });

        return view('clip-result', compact('clips', 'username'));
    }
    public function searchUserAndRedirect(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        return redirect()->route('clip.result', ['username' => $request->input('username')]);
    }

    // Метод POST /clip/download
    public function download(Request $request)
    {
        $clip = Clip::firstOrCreate(
            ['slug' => basename($request->url)],
            [
                'uuid'   => (string) Str::uuid(),
                'url'    => $request->url,
                'status' => 'queued',
            ]
        );

        DownloadClipJob::dispatch($clip)->onQueue('video');

        return back()->with('flash', 'Кліп додано у чергу! Перевірте через хвилину.');
    }
    public function index(){
        $clips = Clip::where('status', 'ready')
            ->latest()
            ->get();
        return view('clip-index', compact('clips'));
    }

    public function updateSrt(Request $request, Clip $clip)
    {
        $data = $request->validate([
            'srt' => 'required|string',
        ]);

        // 1. Тільки ВІДНОСНИЙ шлях усередині диска 'public'
        $relative = "str/{$clip->uuid}.srt";

        // 2. Пишемо файл у storage/app/public/str/...
        Storage::disk('public')->put($relative, $data['srt']);

        // 3. Запам’ятовуємо без "D:\..." – лиш «public/...»
        $clip->update(['srt_path' => $relative]);

        return response()->noContent();      // 204
    }
    public function show(Clip $clip)
    {
        $videoUrl = Storage::url(Str::after($clip->video_path, 'app/public/'));
        //
        if (! Str::startsWith($clip->srt_path, ['/', 'C:', 'D:'])) {
            $subs = Storage::disk('public')->exists($clip->srt_path)
                ? Storage::disk('public')->get($clip->srt_path)
                : '';
        }
        // 2. Якщо старий запис – абсолютний → читаємо напряму
        else {
            $subs = is_file($clip->srt_path)
                ? file_get_contents($clip->srt_path)
                : '';
        }


        return view('clip-show', compact('clip', 'videoUrl', 'subs'));
    }


}

