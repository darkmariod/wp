@extends('layouts.app')

@section('title', 'Propiedades en Riobamba')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- Header --}}
    <div class="mb-10">
        <h1 class="text-4xl font-bold text-navy tracking-tight">Propiedades</h1>
        <p class="text-slate-500 mt-2 text-lg">Encontrá la propiedad ideal en Riobamba y sus alrededores</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-10">
        {{-- Filtros --}}
        <aside class="lg:w-72 shrink-0">
            <form id="filtros-form" method="GET" action="{{ route('properties.index') }}">
                <div class="bg-warm rounded-2xl border border-white/80 shadow-sm p-6 space-y-5 sticky top-24">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-navy">Filtros</h2>
                        <a href="{{ route('properties.index') }}" class="text-xs text-terracota hover:text-terracota-dark font-medium transition">Limpiar</a>
                    </div>

                    {{-- Código --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Código</label>
                        <input type="text" name="code" value="{{ request('code') }}"
                               class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5"
                               placeholder="Ej: INM-0001">
                    </div>

                    {{-- Ciudad --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Ciudad</label>
                        <select name="city" id="filter-city"
                                class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5">
                            <option value="">Todas</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Zona --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Zona / Barrio</label>
                        <select name="sector" id="filter-sector"
                                class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5">
                            <option value="">Todas</option>
                            @foreach ($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ request('sector') == $sector->id ? 'selected' : '' }}
                                        data-city="{{ $sector->city_id }}">
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tipo --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Tipo</label>
                        <select name="type" class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5">
                            <option value="">Todos</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Operación --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Operación</label>
                        <select name="operation" class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5">
                            <option value="">Todas</option>
                            @foreach ($operations as $op)
                                <option value="{{ $op->id }}" {{ request('operation') == $op->id ? 'selected' : '' }}>
                                    {{ $op->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Rango precio --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Precio</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="price_min" value="{{ request('price_min') }}"
                                   class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5"
                                   placeholder="Min">
                            <input type="number" name="price_max" value="{{ request('price_max') }}"
                                   class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5"
                                   placeholder="Max">
                        </div>
                    </div>

                    {{-- Dormitorios / Baños --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Dorm.</label>
                            <select name="bedrooms" class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5">
                                <option value="">Cualquier</option>
                                @foreach (range(1, 6) as $n)
                                    <option value="{{ $n }}" {{ request('bedrooms') == $n ? 'selected' : '' }}>{{ $n }}+</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Baños</label>
                            <select name="bathrooms" class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5">
                                <option value="">Cualquier</option>
                                @foreach (range(1, 4) as $n)
                                    <option value="{{ $n }}" {{ request('bathrooms') == $n ? 'selected' : '' }}>{{ $n }}+</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Área --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Área (m²)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="area_min" value="{{ request('area_min') }}"
                                   class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5"
                                   placeholder="Min">
                            <input type="number" name="area_max" value="{{ request('area_max') }}"
                                   class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5"
                                   placeholder="Max">
                        </div>
                    </div>

                    {{-- Parqueadero --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Parqueadero</label>
                        <select name="parking" class="w-full rounded-xl border-slate-200 bg-white text-sm px-4 py-2.5">
                            <option value="">Cualquier</option>
                            @foreach (range(1, 4) as $n)
                                <option value="{{ $n }}" {{ request('parking') == $n ? 'selected' : '' }}>{{ $n }}+</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </aside>

        {{-- Resultados --}}
        <div class="flex-1 min-w-0">
            {{-- Barra de resultados --}}
            <div class="flex items-center justify-between mb-6">
                <p class="text-sm text-slate-500">
                    <span class="font-semibold text-navy">{{ $properties->total() }}</span>
                    propiedad{{ $properties->total() !== 1 ? 'es' : '' }} encontrada{{ $properties->total() !== 1 ? 's' : '' }}
                </p>
            </div>

            @if ($properties->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach ($properties as $property)
                        <x-property-card :property="$property" />
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $properties->links() }}
                </div>
            @else
                <div class="text-center py-20 bg-warm rounded-2xl border border-white/80">
                    <div class="text-5xl mb-4 opacity-30">🏠</div>
                    <p class="text-slate-500 text-lg mb-1">No se encontraron propiedades</p>
                    <p class="text-slate-400 text-sm">Probá ajustando los filtros o volvé a intentar más tarde.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#filtros-form');
    if (!form) return;

    const citySelect = document.getElementById('filter-city');
    const sectorSelect = document.getElementById('filter-sector');

    if (citySelect && sectorSelect) {
        const allOptions = Array.from(sectorSelect.options);

        function filterSectors() {
            const cityId = citySelect.value;
            while (sectorSelect.options.length > 1) {
                sectorSelect.remove(1);
            }
            let selectedLost = true;
            allOptions.forEach(function (opt) {
                if (opt.value === '') return;
                if (!cityId || opt.dataset.city === cityId) {
                    const clone = opt.cloneNode(true);
                    sectorSelect.appendChild(clone);
                    if (opt.selected) {
                        sectorSelect.value = opt.value;
                        selectedLost = false;
                    }
                }
            });
            if (selectedLost && sectorSelect.options.length > 0) {
                sectorSelect.selectedIndex = 0;
            }
        }

        citySelect.addEventListener('change', filterSectors);
        filterSectors();
    }

    // Auto-submit en selects
    form.querySelectorAll('select').forEach(function (el) {
        el.addEventListener('change', function () {
            form.requestSubmit();
        });
    });

    // Debounce para inputs
    let debounceTimer;
    form.querySelectorAll('input[type="text"], input[type="number"]').forEach(function (el) {
        el.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                form.requestSubmit();
            }, 400);
        });
    });

    form.querySelectorAll('input').forEach(function (el) {
        el.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.requestSubmit();
            }
        });
    });
});
</script>
@endpush
