<div>
    @if ($esPrevia)
        <div class="mb-6 flex items-center gap-2 rounded-lg border border-dashed border-accent-600 bg-accent-600/5 px-4 py-3 text-sm font-medium text-accent-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            Esto es una vista previa: así lo va a ver la familia y no pueden compartir evidencia desde acá.
        </div>
    @endif

    <x-breadcrumb
        :items="[
            ['label' => 'Inicio', 'url' => route('mi-escuelita.home')],
            ['label' => 'Experiencias', 'url' => route('mi-escuelita.experiencias.index')],
            ['label' => $content->title],
        ]"
    />

    <div class="mt-6 overflow-hidden rounded-lg border border-green-100 bg-white shadow-card">
        <div class="p-6 sm:p-8">
            @if ($content->area)
                <span class="text-xs font-medium uppercase tracking-wide text-accent-600">
                    {{ $content->area->icon }} {{ $content->area->name }}
                </span>
            @endif

            <h1 class="mt-1 text-3xl font-bold text-ink-900">{{ $content->title }}</h1>

            @if ($content->description)
                <div class="mt-5 max-w-none text-ink-600">
                    {!! $content->description !!}
                </div>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                @if ($content->video_url)
                    <a
                        href="{{ $content->video_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-800 px-4 py-2 text-sm font-medium text-white shadow-card-hover transition-base hover:bg-green-700 focus-ring"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z" clip-rule="evenodd" />
                        </svg>
                        Ver video
                    </a>
                @endif

                @if ($content->media->isNotEmpty())
                    <a
                        href="{{ route('media.show', $content->media->first()->id) }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 rounded-lg border border-green-100 bg-white px-4 py-2 text-sm font-medium text-green-800 shadow-card transition-base hover:shadow-card-hover focus-ring"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                        </svg>
                        Archivos adjuntos
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Zona de evidencia: render según el estado real de la experiencia --}}
    @if ($content->requires_evidence)
        @if ($this->mostrarFormulario)
            <div class="mt-8 rounded-lg border border-green-100 bg-white p-6 shadow-card sm:p-8" wire:key="form-evidence">
                <h2 class="text-xl font-semibold text-ink-900">Compartí esta experiencia</h2>
                <p class="mt-1 text-sm text-ink-400">
                    Contanos cómo fue y subí una foto, video o documento de {{ $ninoActual->name ?? 'tu niño' }} en esta experiencia.
                </p>

                <form wire:submit="submit" class="mt-6 space-y-6" novalidate>
                    @if ($this->hijosActivos->count() > 1)
                        <div>
                            <label for="child_id" class="block text-sm font-medium text-ink-700">¿Para qué niño es esta experiencia?</label>
                            <select
                                id="child_id"
                                wire:model="child_id"
                                class="mt-2 block w-full max-w-md rounded-lg border border-green-100 bg-white px-3 py-2 text-sm text-ink-900 shadow-sm focus-ring"
                            >
                                @foreach ($this->hijosActivos as $hijo)
                                    <option value="{{ $hijo->id }}">{{ $hijo->name }}</option>
                                @endforeach
                            </select>
                            @error('child_id')
                                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="space-y-2">
                        @if ($fotos)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($fotos as $index => $foto)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-green-100 bg-green-50 px-3 py-1 text-xs font-medium text-green-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm4.5-9a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25ZM19.5 12.9l-3.75 3.75" />
                                        </svg>
                                        {{ $foto->getClientOriginalName() }} ({{ $this->tamanoHumano($foto->getSize()) }})
                                        <button type="button" wire:click="quitarFoto({{ $index }})" class="text-green-800/60 transition-base hover:text-red-600 focus-ring">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                            <span class="sr-only">Quitar foto</span>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <label for="fotos" class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-green-300 bg-green-50 px-4 py-2 text-sm font-medium text-green-800 transition-base hover:border-green-500 focus-ring">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm4.5-9a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25ZM19.5 12.9l-3.75 3.75" />
                            </svg>
                            Subí fotos
                            <span class="text-xs font-normal">(máx. 5)</span>
                        </label>
                        <input id="fotos" type="file" wire:model="fotos" multiple accept="image/*" class="sr-only" />

                        @error('photos')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <div class="space-y-2">
                            @if ($video)
                                <span class="inline-flex items-center gap-2 rounded-full border border-green-100 bg-green-50 px-3 py-1 text-xs font-medium text-green-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                    {{ $video->getClientOriginalName() }} ({{ $this->tamanoHumano($video->getSize()) }})
                                    <button type="button" wire:click="quitarVideo" class="text-green-800/60 transition-base hover:text-red-600 focus-ring">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                        <span class="sr-only">Quitar video</span>
                                    </button>
                                </span>
                            @endif

                            <label for="video" class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-green-100 bg-white px-4 py-2 text-sm font-medium text-green-800 shadow-card transition-base hover:shadow-card-hover focus-ring">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                Subí un video
                                <span class="text-xs font-normal">(MP4 o MOV)</span>
                            </label>
                            <input id="video" type="file" wire:model="video" accept="video/mp4,video/quicktime" class="sr-only" />

                            @error('video')
                                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            @if ($document)
                                <span class="inline-flex items-center gap-2 rounded-full border border-green-100 bg-green-50 px-3 py-1 text-xs font-medium text-green-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    {{ $document->getClientOriginalName() }} ({{ $this->tamanoHumano($document->getSize()) }})
                                    <button type="button" wire:click="quitarDocumento" class="text-green-800/60 transition-base hover:text-red-600 focus-ring">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                        <span class="sr-only">Quitar documento</span>
                                    </button>
                                </span>
                            @endif

                            <label for="document" class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-green-100 bg-white px-4 py-2 text-sm font-medium text-green-800 shadow-card transition-base hover:shadow-card-hover focus-ring">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                Adjuntá un documento
                                <span class="text-xs font-normal">(PDF, Word o imagen)</span>
                            </label>
                            <input id="document" type="file" wire:model="document" class="sr-only" />

                            @error('document')
                                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="comment" class="block text-sm font-medium text-ink-700">¿Cómo fue esta experiencia?</label>
                        <textarea
                            id="comment"
                            wire:model="comment"
                            rows="4"
                            placeholder="Contanos qué hicieron, cómo se sintió tu niño, qué aprendieron…"
                            class="mt-2 block w-full rounded-lg border border-green-100 bg-white px-3 py-2 text-sm text-ink-900 shadow-sm focus-ring"
                        ></textarea>
                        @error('comment')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-lg bg-green-800 px-6 py-2.5 text-sm font-medium text-white shadow-card-hover transition-base hover:bg-green-700 focus-ring"
                        >
                            Compartir experiencia
                        </button>
                        <span wire:loading wire:target="submit" class="text-sm font-medium text-accent-600">Subiendo…</span>
                    </div>
                </form>
            </div>
        @elseif ($yaEnviada)
            <div class="mt-8 rounded-lg border border-green-100 bg-white p-6 shadow-card sm:p-8" wire:key="ya-enviada">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-50 text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-ink-900">Ya compartiste esta experiencia</h2>
                        <p class="mt-1 text-sm text-ink-400">
                            @if ($this->fechaEnvio)
                                La enviaste el {{ $this->fechaEnvio }}. La guía la va a revisar y vas a ver su respuesta en “Mis experiencias”.
                            @else
                                La guía la va a revisar y vas a ver su respuesta en “Mis experiencias”.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="mt-8 flex items-center justify-between border-t border-green-100 pt-6 text-sm text-ink-400">
        <a href="{{ route('mi-escuelita.experiencias.index') }}" class="inline-flex items-center gap-1 transition-base hover:text-green-700 focus-ring">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Volver a experiencias
        </a>
    </div>
</div>