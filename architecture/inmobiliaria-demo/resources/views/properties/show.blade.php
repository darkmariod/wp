@extends('layouts.app')

@section('title', $property->title)

@push('styles')
<style>
.gallery-img { height: 420px; object-fit: cover; width: 100%; border-radius: 0.75rem; }
.thumb-img { height: 80px; width: 100px; object-fit: cover; border-radius: 0.5rem; cursor: pointer; opacity: 0.5; transition: all .25s ease; }
.thumb-img:hover { opacity: 0.85; }
.thumb-img.active { opacity: 1; outline: 2px solid #c1694f; outline-offset: -2px; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- Breadcrumb --}}
    <nav class="text-sm text-slate-400 mb-6">
        <a href="{{ url('/') }}" class="hover:text-terracota transition">Inicio</a>
        <span class="mx-2">/</span>
        <a href="{{ route('properties.index') }}" class="hover:text-terracota transition">Propiedades</a>
        <span class="mx-2">/</span>
        <span class="text-slate-600">{{ $property->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Galería --}}
            <div class="bg-warm rounded-2xl p-2 border border-white/80 shadow-sm">
                @if ($property->images->count())
                    <img id="main-image" src="{{ $property->images->first()->url }}"
                         alt="{{ $property->images->first()->alt_text ?: $property->title }}" class="gallery-img w-full">
                    <div class="flex gap-2 mt-2 overflow-x-auto pb-1">
                        @foreach ($property->images as $img)
                            <img src="{{ $img->url }}"
                                 alt="{{ $img->alt_text ?: $property->title }}" class="thumb-img {{ $loop->first ? 'active' : '' }}"
                                 onclick="document.getElementById('main-image').src = this.src;
                                          document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active'));
                                          this.classList.add('active');">
                        @endforeach
                    </div>
                @else
                    <div class="gallery-img bg-navy/5 flex items-center justify-center text-slate-300">
                        Sin imágenes
                    </div>
                @endif
            </div>

            {{-- Descripción --}}
            <div class="bg-warm rounded-2xl border border-white/80 shadow-sm p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-navy">{{ $property->title }}</h1>
                        <p class="text-slate-500 text-sm mt-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                            {{ $property->address }} — {{ $property->sector?->city?->name }}, {{ $property->sector?->name }}
                        </p>
                    </div>
                    <span class="shrink-0 bg-emerald-50 text-emerald-700 text-xs font-semibold px-4 py-1.5 rounded-full border border-emerald-200/50">
                        Disponible
                    </span>
                </div>

                @if ($property->published_at)
                    <p class="text-xs text-slate-400 mt-2">Publicado el {{ $property->published_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</p>
                @endif

                <div class="prose prose-slate max-w-none mt-6 text-slate-600 leading-relaxed">
                    {!! $property->description !!}
                </div>
            </div>

            {{-- Mapa --}}
            @if ($property->latitude && $property->longitude)
            <div class="bg-warm rounded-2xl border border-white/80 shadow-sm p-7">
                <h2 class="font-semibold text-navy text-lg mb-3">Ubicación</h2>
                <div id="property-map"></div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-6">
            <div class="bg-warm rounded-2xl border border-white/80 shadow-sm p-7 sticky top-24">
                @if ($property->is_featured)
                    <span class="inline-block bg-terracota/10 text-terracota text-xs font-semibold px-3 py-1 rounded-full mb-4">Destacado</span>
                @endif

                <p class="text-4xl font-bold text-terracota tracking-tight">
                    ${{ number_format($property->price, 0) }}
                </p>
                <p class="text-sm text-slate-500 mt-1">{{ $property->operation->name }}</p>

                <hr class="my-5 border-slate-200/60">

                <div class="grid grid-cols-2 gap-y-4 text-sm">
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Código</p>
                        <p class="font-medium text-navy mt-0.5 font-mono">{{ $property->code }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Tipo</p>
                        <p class="font-medium text-navy mt-0.5">{{ $property->propertyType->name }}</p>
                    </div>
                    @if ($property->bedrooms)
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Dormitorios</p>
                        <p class="font-medium text-navy mt-0.5">{{ $property->bedrooms }}</p>
                    </div>
                    @endif
                    @if ($property->bathrooms)
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Baños</p>
                        <p class="font-medium text-navy mt-0.5">{{ $property->bathrooms }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Área</p>
                        <p class="font-medium text-navy mt-0.5">{{ $property->area_m2 }} m²</p>
                    </div>
                    @if ($property->parking_spaces)
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Parqueo</p>
                        <p class="font-medium text-navy mt-0.5">{{ $property->parking_spaces }}</p>
                    </div>
                    @endif
                </div>

                @if ($property->published_at)
                <p class="text-xs text-slate-400 text-center mt-5">
                    Publicado {{ $property->published_at->locale('es')->diffForHumans() }}
                </p>
                @endif

                <hr class="my-5 border-slate-200/60">

                <a href="https://wa.me/{{ config('services.whatsapp.number', '593999999999') }}?text=Hola%2C%20me%20interesa%20la%20propiedad%20{{ urlencode($property->title) }}%20({{ $property->code }}).%20{{ urlencode($property->address) }}%20{{ url()->current() }}"
                   target="_blank"
                   class="block w-full bg-emerald-600 text-white text-center rounded-xl py-3.5 font-medium hover:bg-emerald-700 transition shadow-sm">
                    Contactar por WhatsApp
                </a>
            </div>
        </aside>
    </div>

    {{-- Relacionadas --}}
    @if ($related->count())
    <section class="mt-16">
        <h2 class="text-2xl font-bold text-navy mb-8">Propiedades similares</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($related as $prop)
                <x-property-card :property="$prop" />
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ $property->latitude ?? 'null' }};
    const lng = {{ $property->longitude ?? 'null' }};
    if (lat && lng) {
        const map = L.map('property-map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map)
            .bindPopup(@js($property->title))
            .openPopup();
    }
});
</script>
@endpush
