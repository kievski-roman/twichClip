@foreach($clips as $clip)
    <div style="margin-bottom: 20px">
        <p><strong>{{ $clip['title'] }}</strong></p>
        <a href="{{ $clip['url'] }}" target="_blank">{{ $clip['url'] }}</a><br>
        <img src="{{ $clip['thumbnail_url'] }}" width="300">

        {{-- посилання на плеєр (можна поки жорстко написати clip.mp4) --}}
        <br>
        <a href="{{ route('video.show', ['filename' => 'clip.mp4']) }}">🎬 Переглянути mp4</a>
    </div>
@endforeach
