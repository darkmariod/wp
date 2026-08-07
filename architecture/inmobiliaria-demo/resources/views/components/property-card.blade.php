@props(['property'])
<a href="{{ route('properties.show', $property) }}" class="group block bg-warm rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-white/80 hover:border-terracota/20">
    {{-- Imagen --}}
    <div class="aspect-[4/3] bg-navy/5 overflow-hidden relative">
        @if ($mainImage = $property->images->where('is_main', true)->first())
            <img src="{{ $mainImage->url }}"
                 alt="{{ $mainImage->alt_text ?: $property->title }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500 ease-out">
        @elseif ($firstImage = $property->images->first())
            <img src="{{ $firstImage->url }}"
                 alt="{{ $firstImage->alt_text ?: $property->title }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500 ease-out">
        @else
            <div class="w-full h-full flex items-center justify-center text-slate-300 text-sm">Sin imagen</div>
        @endif

        {{-- Badge operación --}}
        <span class="absolute top-3 right-3 text-xs font-semibold text-white bg-navy/70 backdrop-blur-sm px-3 py-1 rounded-full">
            {{ $property->operation->name }}
        </span>

        {{-- Badge destacado --}}
        @if ($property->is_featured)
            <span class="absolute top-3 left-3 text-xs font-semibold text-white bg-terracota px-3 py-1 rounded-full shadow-sm">
                Destacado
            </span>
        @endif
    </div>

    {{-- Contenido --}}
    <div class="p-5">
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
            <span>{{ $property->propertyType->name }}</span>
            <span>·</span>
            <span>{{ $property->sector?->name ?? 'Sin sector' }}</span>
            @if ($property->published_at)
                <span>·</span>
                <span>{{ $property->published_at->locale('es')->diffForHumans() }}</span>
            @endif
        </div>

        <h3 class="font-semibold text-slate-900 leading-snug group-hover:text-terracota transition-colors duration-200">
            {{ $property->title }}
        </h3>

        <p class="text-sm text-slate-500 mt-1.5 truncate flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
            {{ $property->address }}
        </p>

        {{-- Precio --}}
        <p class="text-2xl font-bold text-terracota mt-3 tracking-tight">
            $ {{ number_format($property->price, 0, ',', '.') }}
        </p>

        {{-- Features --}}
        <div class="flex flex-wrap gap-x-4 gap-y-1.5 mt-3 text-sm text-slate-500">
            @if ($property->bedrooms)
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                    </svg>
                    {{ $property->bedrooms }} dorm.
                </span>
            @endif
            @if ($property->bathrooms)
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    {{ $property->bathrooms }} baños
                </span>
            @endif
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                </svg>
                {{ $property->area_m2 }} m²
            </span>
            @if ($property->parking_spaces)
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                    </svg>
                    {{ $property->parking_spaces }} parq.
                </span>
            @endif
        </div>

        {{-- Código --}}
        <p class="text-xs text-slate-300 font-mono mt-4 pt-3 border-t border-slate-100">{{ $property->code }}</p>
    </div>
</a>
