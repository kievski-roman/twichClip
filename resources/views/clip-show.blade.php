@extends('layouts.app')

@section('content')
    <h2 class="text-xl mb-4">{{ $clip->slug }}</h2>

    {{-- ⬇︎ одна «row», усередині дві «col» --}}
    <div class="row gx-4 gy-4">

        {{-- відео – зліва / зверху на мобілці --}}
        <div class="col-12 col-md-7">
            <video class="w-100 border rounded" controls>
                <source src="{{ $videoUrl }}" type="video/mp4">
            </video>
        </div>

        {{-- редактор сабів – справа / під відео на мобілці --}}
        <div class="col-12 col-md-5 d-flex flex-column">

            <h3 class="h5 mb-2">Субтитри (SRT)</h3>

            <textarea id="srtEditor"
                      class="form-control flex-grow-1 mb-2"
                      style="min-height: 300px"
                      placeholder="Редагуй тут…">{{ $subs }}</textarea>

            <button id="saveBtn" class="btn btn-primary align-self-start">
                💾 Зберегти
            </button>

            <small id="savedMsg" class="text-success mt-1 d-none">✓ збережено</small>
        </div>
    </div>

    {{-- ⚡ короткий AJAX на чистому JS --}}
    <script>
        document.getElementById('saveBtn').addEventListener('click', () => {
            fetch('{{ route('clips.srt', $clip) }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ srt: document.getElementById('srtEditor').value })
            }).then(() => {
                // показуємо «✓ збережено» на 1,5 с
                const msg = document.getElementById('savedMsg');
                msg.classList.remove('d-none');
                setTimeout(() => msg.classList.add('d-none'), 1500);
            });
        });
    </script>
@endsection

