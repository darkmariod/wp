@props(['items' => []])

<nav aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-2 text-sm">
        @php($lastIndex = count($items) - 1)

        @foreach ($items as $index => $item)
            <li class="flex items-center gap-2">
                @if (isset($item['url']) && $index < $lastIndex)
                    <a href="{{ $item['url'] }}" class="text-ink-400 transition-base hover:text-green-700 focus-ring">
                        {{ $item['label'] }}
                    </a>

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-ink-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                @else
                    <span class="font-medium text-ink-600" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>