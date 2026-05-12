{{-- Tune Styles (Blade fallback) --}}
{{-- Prefer importing tune.css via your CSS pipeline for better caching. --}}
{{-- This component injects tune presets as inline <style> for quick setup. --}}

@props(['only' => null])

@php
$tunes = [
    'default' => [
        'radius-box' => '0.5rem', 'radius-field' => '0.25rem', 'radius-selector' => '1rem',
        'border' => '1px', 'depth' => '1', 'noise' => '0',
        'size-selector' => '0.25rem', 'size-field' => '0.25rem',
        'space-section' => '5rem', 'space-section-inner' => '3rem',
        'space-element' => '1.5rem', 'space-compact' => '0.75rem',
        'space-text' => '1rem', 'space-inline' => '0.5rem',
    ],
    'sharp' => [
        'radius-box' => '0', 'radius-field' => '0', 'radius-selector' => '0',
        'border' => '1px', 'depth' => '1', 'noise' => '0',
        'size-selector' => '0.25rem', 'size-field' => '0.25rem',
        'space-section' => '4rem', 'space-section-inner' => '2.5rem',
        'space-element' => '1.25rem', 'space-compact' => '0.625rem',
        'space-text' => '0.875rem', 'space-inline' => '0.375rem',
    ],
    'soft' => [
        'radius-box' => '1rem', 'radius-field' => '0.5rem', 'radius-selector' => '1rem',
        'border' => '1px', 'depth' => '1', 'noise' => '0',
        'size-selector' => '0.25rem', 'size-field' => '0.25rem',
        'space-section' => '6rem', 'space-section-inner' => '3.5rem',
        'space-element' => '2rem', 'space-compact' => '1rem',
        'space-text' => '1.25rem', 'space-inline' => '0.625rem',
    ],
    'playful' => [
        'radius-box' => '2rem', 'radius-field' => '1rem', 'radius-selector' => '2rem',
        'border' => '1.5px', 'depth' => '1', 'noise' => '1',
        'size-selector' => '0.3125rem', 'size-field' => '0.3125rem',
        'space-section' => '6rem', 'space-section-inner' => '4rem',
        'space-element' => '2rem', 'space-compact' => '1rem',
        'space-text' => '1.25rem', 'space-inline' => '0.75rem',
    ],
    'corporate' => [
        'radius-box' => '0.25rem', 'radius-field' => '0.25rem', 'radius-selector' => '0.25rem',
        'border' => '1px', 'depth' => '0', 'noise' => '0',
        'size-selector' => '0.25rem', 'size-field' => '0.25rem',
        'space-section' => '4rem', 'space-section-inner' => '2.5rem',
        'space-element' => '1.25rem', 'space-compact' => '0.625rem',
        'space-text' => '0.875rem', 'space-inline' => '0.375rem',
    ],
    'brutal' => [
        'radius-box' => '0', 'radius-field' => '0', 'radius-selector' => '0',
        'border' => '2px', 'depth' => '0', 'noise' => '0',
        'size-selector' => '0.25rem', 'size-field' => '0.25rem',
        'space-section' => '4rem', 'space-section-inner' => '2rem',
        'space-element' => '1.5rem', 'space-compact' => '0.75rem',
        'space-text' => '1rem', 'space-inline' => '0.5rem',
    ],
    'elegant' => [
        'radius-box' => '0.5rem', 'radius-field' => '0.25rem', 'radius-selector' => '0.5rem',
        'border' => '0.5px', 'depth' => '1', 'noise' => '0',
        'size-selector' => '0.25rem', 'size-field' => '0.25rem',
        'space-section' => '6rem', 'space-section-inner' => '4rem',
        'space-element' => '2rem', 'space-compact' => '1rem',
        'space-text' => '1.25rem', 'space-inline' => '0.625rem',
    ],
    'bold' => [
        'radius-box' => '0.5rem', 'radius-field' => '0.5rem', 'radius-selector' => '1rem',
        'border' => '1.5px', 'depth' => '1', 'noise' => '0',
        'size-selector' => '0.3125rem', 'size-field' => '0.3125rem',
        'space-section' => '5rem', 'space-section-inner' => '3rem',
        'space-element' => '1.5rem', 'space-compact' => '0.75rem',
        'space-text' => '1rem', 'space-inline' => '0.5rem',
    ],
];

if ($only !== null) {
    $allowed = array_map('trim', explode(',', $only));
    $tunes = array_intersect_key($tunes, array_flip($allowed));
}
@endphp

@foreach ($tunes as $name => $vars)
<style>
[data-tune="{{ $name }}"] {
@foreach ($vars as $prop => $value)
    --{{ $prop }}: {{ $value }};
@endforeach
}
</style>
@endforeach
