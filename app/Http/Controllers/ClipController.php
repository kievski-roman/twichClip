<?php

namespace App\Http\Controllers;

use App\Services\TwitchApiService;
use Illuminate\Http\Request;
use App\Services\VideoDownloaderService;

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



    // Метод для GET (через /clips/result/{username})

    // Метод POST /clip/download
    public function download(Request $request)
    {
        $clipUrl = $request->input('url');
        $username = $request->input('username'); // передається у формі

        $slug = basename($clipUrl);
        $filename = $slug . '.mp4';
        $path = storage_path('app/public/videos/' . $filename);

        if (!file_exists($path)) {
            $downloader = new VideoDownloaderService();
            $downloader->downloadClip($clipUrl, $filename);
        }

        return redirect()->route('clip.result', ['username' => $username]);
    }
}



