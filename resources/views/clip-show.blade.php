@extends('layouts.app')

@section('content')
    <h2 class="text-xl mb-4">{{ $clip->slug }}</h2>
    <div>
        Video URL: {{ $videoUrl }}
    </div>

    {{-- ⬇︎ одна «row», усередині дві «col» --}}
    <div class="row gx-4 gy-4">


        <div class="col-12 col-md-7">
            <video class="w-100 border rounded" controls>
                {{-- відео – зліва / зверху на мобілці <source src="{{ $videoUrl }}" type="video/mp4">--}}
                <source src="{{ Storage::url($clip->video_path) }}" type="video/mp4">
            </video>
        </div>

        {{-- редактор сабів – справа / під відео на мобілці --}}
        <div class="col-12 col-md-5 d-flex flex-column">

            <h3 class="h5 mb-2">Субтитри (SRT)</h3>
            <div x-data="editor('{{ route('clips.srt', $clip) }}', @js($subs))" class="d-flex flex-column">
    <textarea x-model="text"
              @input="scheduleSave"
              class="form-control flex-grow-1 mb-2"
              style="min-height: 300px">{{$subs}}</textarea>

                <small x-show="saving" class="text-muted">Зберігаю…</small>
                <small x-show="saved"  class="text-success">✓ збережено</small>
            </div>
            <small id="savedMsg" class="text-success mt-1 d-none">✓ Saved</small>
        </div>
        <form action="{{ route('clips.hardsubs', $clip) }}" method="POST">
            @csrf
            <button class="btn btn-primary">🎞️ Generate video Hard-sub</button>
        </form>
        @if($clip->status === \App\Models\Clip::STATUS_HARD_DONE && $clip->hard_path)
            <a href="{{ Storage::url($clip->hard_path) }}" download class="btn btn-success mt-3">
                📥 Download MP4 with Hard-sub
            </a>
        @elseif($clip->status === \App\Models\Clip::STATUS_HARD_PROCESSING)
            <p>⏳ Generating Hard-sub, wait...</p>
        @endif



    </div>

    <script>
        function editor(url, initialText) {
            return {
                text: initialText,
                saving: false,
                saved:  false,
                timer:  null,

                scheduleSave() {
                    clearTimeout(this.timer)
                    this.timer = setTimeout(() => this.save(), 1000)   // 1 с debounce
                },

                async save() {
                    this.saving = true
                    this.saved  = false

                    await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ srt: this.text })
                    })

                    this.saving = false
                    this.saved  = true
                    setTimeout(() => this.saved = false, 1500)         // згасає через 1½ с
                }
            }
        }
    </script>
@endsection



