{{--
    <x-backdrop /> — a page background behind everything (v0.19.0).

    <x-backdrop />                  pattern from config('pinion-ui.look.backdrop') / ?bg=
    <x-backdrop pattern="flow" />   two theme-colored glows drifting (animated)
    <x-backdrop pattern="grid-s" /> pattern + density (s|m|l)
    <x-backdrop pattern="sig" local />  absolute inside a `relative` parent instead of the viewport

    Patterns: pn_backdrops(). Colors come from the theme only. `flow` stops under
    prefers-reduced-motion. Put it once, right after <body>.
--}}
@props([
    'pattern' => null,
    'local' => false,
])

@php
    $value = $pattern ?? pn_look()['backdrop'];
    [$name, $size] = pn_backdrop_parse((string) $value) ?? ['none', 'm'];
@endphp

@if ($name !== 'none')
    <div {{ $attributes->merge(['class' => 'pn-backdrop']) }} data-pattern="{{ $name }}" data-size="{{ $size }}" @if ($local) data-local @endif aria-hidden="true"><i></i><i></i></div>
@endif
