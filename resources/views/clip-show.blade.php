@extends('layouts.app')

@section('content')
    <h2 class="text-xl mb-4">{{ $clip->slug }}</h2>

    {{-- ===== відео + редактор SRT ================================================= --}}
    <div class="row gx-4 gy-4">
        <div class="col-12 col-md-7">
            <video class="w-100 border rounded" controls>
                <source src="{{ Storage::url($clip->video_path) }}" type="video/mp4">
            </video>
        </div>

        <div class="col-12 col-md-5 d-flex flex-column">
            <h3 class="h5 mb-2">Субтитри (SRT)</h3>
            <div x-data="editor('{{ route('clips.srt', $clip) }}', @js($subs))" class="d-flex flex-column">
            <textarea x-model="text" @input="scheduleSave"
                      class="form-control flex-grow-1 mb-2" style="min-height:300px">{{$subs}}</textarea>
                <small x-show="saving" class="text-muted">Зберігаю…</small>
                <small x-show="saved"  class="text-success">✓ збережено</small>
            </div>
        </div>
    </div>

    {{-- ===== кнопка Generate / Generating / Download ================================= --}}
    @php
        $generateUrl = route('clips.hardsubs', $clip);
        $downloadUrl = route('clips.download',  $clip);
        $statusUrl   = route('api.clips.status', $clip);
    @endphp

    <div x-data="hardSub({{ $clip->id }}, '{{ $generateUrl }}', '{{ $downloadUrl }}',
                     '{{ $statusUrl }}', '{{ $clip->status }}', '{{ csrf_token() }}')"
         x-init="init()"
         class="mt-4">

        {{-- queued  --}}
        <template x-if="status==='ready'">
            <button @click="generate" class="btn btn-primary">
                🎞️ Generate video Hard‑sub
            </button>
        </template>

        {{-- processing --}}
        <template x-if="status==='hard_processing'">
            <button class="btn btn-secondary" disabled>
                ⏳ Generating…
            </button>
        </template>

        {{-- hard_done --}}
        <template x-if="status==='hard_done'">
            <a :href="downloadUrl" class="btn btn-success" download>
                📥 Download MP4 with Hard‑sub
            </a>
        </template>
    </div>

    {{-- ======================= JS =================================================== --}}
    <script>
        /* редактор SRT (ваш код) */
        /* ===== редактор SRT ===== */
        function editor(url, initialText) {
            return {
                // ► стан textarea
                text:   initialText, // ← початковий вміст SRT, який Blade передав як@js($subs)
                saving: false,       // true –коли йде HTTP PUT
                saved:  false,       // true –коли PUT завершився
                timer:  null,        // id таймера для debounce

                // ► викликається на кожен input у <textarea>
                scheduleSave() {
                    clearTimeout(this.timer);               // 👉 скидаємо попередній
                    this.timer = setTimeout(() => this.save(), 1000); // 👉 чекаємо 1с
                },

                // ► надсилає PUT/clips/{id}/srt
                async save() {
                    this.saving = true;   // показує «Зберігаю…»
                    this.saved  = false;

                    await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Blade вставив токен
                        },
                        body: JSON.stringify({ srt: this.text })
                    });

                    this.saving = false;
                    this.saved  = true;   // показує «✓ збережено»
                    setTimeout(() => this.saved = false, 1500); // через 1.5с ховає
                }
            }
        }

        /* ===== керування Hard‑sub ===== */
        function hardSub(id, generateUrl, downloadUrl, statusUrl, initialStatus, csrf) {
            return {
                // ► поточний статус кліпу: queued | hard_processing | hard_done
                status:      initialStatus,
                // ► URL для завантаження (поки null, заповниться, коли hard_done)
                downloadUrl: downloadUrl,
                poller:      null,        // id setInterval

                /* запускається відразу, коли Alpine ініціалізує компонент */
                init() {
                    // якщо сторінку відкрили, коли кліп уже генерується
                    if (this.status === 'hard_processing') this.startPolling();
                },

                /* кнопка "Generate" викликає цей метод */
                async generate() {
                    // 1. одразу перемикнемо UI на «Generating…»
                    this.status = 'hard_processing';
                    this.startPolling();

                    // 2. відправимо POST/clips/{id}/hardsubs (CSRF в headers)
                    await fetch(generateUrl, {
                        method:  'POST',
                        headers: { 'X-CSRF-TOKEN': csrf }
                    });
                    // відповіді чекати не потрібно — статус пишемо у БД усередині Job
                },

                /* опитування /api/clips/{id}/status кожні 5с */
                startPolling() {
                    if (this.poller) return;          // щоб не запустити двічі
                    this.poller = setInterval(async () => {
                        // GET→ { status: 'hard_done', url: '/clips/28/download' }
                        const res = await fetch(statusUrl).then(r => r.json());
                        this.status      = res.status; // оновлюємо статус
                        this.downloadUrl = res.url;    // і лінк, коли буде

                        // як тільки hard_done —зупиняємо інтервал
                        if (this.status === 'hard_done') clearInterval(this.poller);
                    }, 5000);
                }
            }
        }

    </script>
@endsection
