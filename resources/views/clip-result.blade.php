<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Кліпи Twitch</title>
</head>
<body>
<h1>🎬 Кліпи стрімера: {{ $username }}</h1>

@foreach($clips as $clip)
    <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 30px;">
        <h3>{{ $clip['title'] }}</h3>
        <a href="{{ $clip['url'] }}" target="_blank">🔗 Перейти на Twitch</a><br><br>
        <img src="{{ $clip['thumbnail_url'] }}" width="300"><br>

        @php
            $path = 'videos/' . $clip['filename'];
            $fullPath = storage_path('app/public/' . $path);
        @endphp

        @if (file_exists($fullPath))
            <video width="640" height="360" controls style="margin-top: 10px;">
                <source src="{{ asset('storage/' . $path) }}" type="video/mp4">
            </video>
        @else
            <form action="{{ route('clip.download') }}" method="POST" style="margin-top: 10px;">
                @csrf
                <input type="hidden" name="url" value="{{ $clip['url'] }}">
                <input type="hidden" name="username" value="{{ $username }}">
                <button type="submit">⬇️ Завантажити відео</button>
            </form>
        @endif
    </div>
@endforeach

</body>
</html>




