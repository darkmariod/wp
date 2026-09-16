@props(['items' => []])

<nav aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-1.5 text-xs text-ink-400">
        @php($lastIndex = count($items) - 1)

        @foreach ($items as $index => $item)
            <li class="flex items-center gap-1.5">
                @if (isset($item['url']) && $index < $lastIndex)
                    <a href="{{ $item['url'] }}" class="transition-base hover:text-green-700 focus-ring">
                        {{ $item['label'] }}
                    </a>

                    <span aria-hidden="true">/</span>
                @else
                    <span aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>