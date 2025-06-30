@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $getButtonClasses()]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" :disabled="disabled" {{ $attributes->merge(['class' => $getButtonClasses()]) }}>
        {{ $slot }}
    </button>
@endif
