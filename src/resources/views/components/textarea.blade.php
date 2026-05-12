@props([
    'name' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'cornerHint' => null,
    'color' => 'neutral',
    'appearance' => 'outline',
    'size' => 'md',
    'rows' => 3,
    'maxlength' => null,
    'counter' => false,
    'autoresize' => false,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\TextareaComposer;

    $textareaId   = $attributes->get('id', ($name ? $name . '_' : 'textarea_') . uniqid());
    $hintText     = $error ?: $hint;
    $showCounter  = $counter || $maxlength !== null;
    $needsAlpine  = $autoresize || $showCounter;
    $initialValue = trim($slot->toHtml());
    $initialCount = mb_strlen($initialValue);

    $c = TextareaComposer::compose([
        'color'      => $color,
        'appearance' => $appearance,
        'size'       => $size,
        'error'      => $error,
        'autoresize' => $autoresize,
        'disabled'   => $disabled,
    ]);
@endphp

<div class="w-full"
    @if($needsAlpine)
        x-data="{
            count: {{ $initialCount }},
            init() {
                @if($autoresize)
                    this.$nextTick(() => this.resize());
                @endif
            },
            onInput(e) {
                @if($showCounter)
                    this.count = e.target.value.length;
                @endif
                @if($autoresize)
                    this.resize();
                @endif
            },
            @if($autoresize)
            resize() {
                const ta = this.$refs.ta;
                ta.style.height = 'auto';
                ta.style.height = ta.scrollHeight + 'px';
            },
            @endif
        }"
    @endif
>
    @if($label)
        <div class="flex items-baseline justify-between mb-1.5">
            <label for="{{ $textareaId }}" class="block text-[length:var(--text-field-sm)] font-medium {{ $c['labelColor'] }}">
                {{ $label }}
                @if($required)<span class="text-error ml-0.5">*</span>@endif
            </label>
            @if($cornerHint)
                <span class="text-[length:var(--text-field-xs)] text-base-content/50">{{ $cornerHint }}</span>
            @endif
        </div>
    @endif

    <div class="{{ $c['wrapper'] }}">
        <textarea
            id="{{ $textareaId }}"
            rows="{{ $rows }}"
            @if($name) name="{{ $name }}" @endif
            @if($maxlength) maxlength="{{ $maxlength }}" @endif
            @if($needsAlpine) x-ref="ta" @input="onInput($event)" @endif
            {{ $attributes->whereStartsWith('wire:') }}
            {{ $attributes->whereDoesntStartWith('wire:')->merge(['class' => $c['textareaClass']]) }}
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
        >{{ $slot }}</textarea>
    </div>

    @if($hintText || $showCounter)
        <div class="mt-1.5 flex items-baseline gap-3">
            @if($hintText)
                <p class="text-[length:var(--text-field-sm)] {{ $c['hintColor'] }}">{{ $hintText }}</p>
            @endif
            @if($showCounter)
                <span class="ml-auto shrink-0 text-[length:var(--text-field-xs)] text-base-content/50">
                    <span x-text="count">{{ $initialCount }}</span>@if($maxlength) / {{ $maxlength }}@endif
                </span>
            @endif
        </div>
    @endif
</div>
