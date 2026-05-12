@props([
    'href' => null,
    'active' => false,
    'disabled' => false,
    'size' => 'md',
    'icon' => null,
])

@php
    $tag = $href ? 'a' : 'button';

    $sizeClasses = match($size) {
        'sm' => 'min-h-[var(--h-field-xs)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)]',
        'lg' => 'min-h-[var(--h-field-md)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)]',
        default => 'min-h-[var(--h-field-sm)] px-[var(--px-field-md)] text-[length:var(--text-field-md)]',
    };

    $stateClasses = $active
        ? 'bg-primary/10 text-primary font-medium'
        : ($disabled
            ? 'opacity-50 cursor-not-allowed'
            : 'text-base-content hover:bg-base-200 cursor-pointer');
@endphp

<{{ $tag }}
    {{ $attributes->merge([
        'class' => "w-full flex items-center gap-2 transition-colors $sizeClasses $stateClasses",
        'href' => $href,
        'disabled' => ($tag === 'button' && $disabled) ? true : null,
    ]) }}
    @if(!$disabled && $tag === 'button') type="button" @endif
>
    @if($icon)
        <x-i :type="$icon" class="w-4 h-4 shrink-0" />
    @endif
    {{ $slot }}
</{{ $tag }}>
