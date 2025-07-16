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
            <div x-data="editor('{{ route('clips.srt', $clip) }}', @js($subs))" class="d-flex flex-column">
    <textarea x-model="text"
              @input="scheduleSave"
              class="form-control flex-grow-1 mb-2"
              style="min-height: 300px">{{$subs}}</textarea>

                <small x-show="saving" class="text-muted">Зберігаю…</small>
                <small x-show="saved"  class="text-success">✓ збережено</small>
            </div>



            <button id="saveBtn" class="btn btn-primary align-self-start">
                💾 Зберегти
            </button>

            <small id="savedMsg" class="text-success mt-1 d-none">✓ збережено</small>
        </div>
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

