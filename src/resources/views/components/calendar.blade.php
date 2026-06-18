@props([
    'value' => null,
    'size' => 'md',
    'placeholder' => '日付を選択',
])

@php
    use SparrowhawkLabs\PinionUi\Compose\CalendarComposer;

    // <x-calendar> — a minimal date picker: a trigger button showing the chosen date,
    // opening a month-grid popover (calendar-grid). Pure Alpine (pinionCalendar) — opt-in
    // via `ui:install --calendar`. wire:model goes to a hidden <input> (string ISO date),
    // like <x-editor>/<x-sheet>. Inside <x-sheet> the same calendar-grid is reused as the
    // date-cell editor (see sheet.blade.php).
    $c = CalendarComposer::compose(['size' => $size]);
    $cal = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>';

    $wireModel    = $attributes->whereStartsWith('wire:model');
    $hasWireModel = $wireModel->isNotEmpty();
@endphp

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class(['relative inline-block']) }}
    x-data="pinionCalendar({ value: @js($value ?? ''), open: false })"
    x-on:calendar-select="open = false; if ($refs.model) { $refs.model.value = $event.detail.value; $refs.model.dispatchEvent(new Event('input', { bubbles: true })); }"
    x-on:keydown.escape="open = false"
>
    <button type="button" class="{{ $c['trigger'] }} w-[12rem]" x-on:click="open = !open" x-bind:aria-expanded="open">
        <span x-text="value || @js($placeholder)" x-bind:class="value ? '' : 'text-base-content/40'"></span>
        {!! $cal !!}
    </button>

    <div x-show="open" x-cloak x-transition.opacity.duration.100ms x-on:click.outside="open = false" class="absolute z-30 mt-1 left-0">
        <x-calendar-grid :size="$size" />
    </div>

    @if($hasWireModel)
        <input type="hidden" x-ref="model" {{ $wireModel }} />
    @else
        <input type="hidden" x-ref="model" />
    @endif
</div>
