@props([
    'name' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'cornerHint' => null,
    'placeholder' => null,
    'color' => 'neutral',
    'appearance' => 'outline',
    'size' => 'md',
    'multiple' => false,
    'rows' => null,
    'floating' => false,
    'required' => false,
    'disabled' => false,
    'custom' => true,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\SelectComposer;

    $selectId = $attributes->get('id', ($name ? $name . '_' : 'select_') . uniqid());
    $hintText = $error ?: $hint;

    // Custom mode replaces the native trigger with an Alpine-driven UI; it
    // supports both single and multiple, so it short-circuits the native
    // `size=` listbox / floating-label paths. Custom + floating is rejected
    // because the floating label pattern depends on the visible native <select>.
    $useCustom = $custom && !$floating;
    $isList    = !$useCustom && ($multiple || $rows !== null);

    $c = SelectComposer::compose([
        'color'      => $color,
        'appearance' => $appearance,
        'size'       => $size,
        'error'      => $error,
        'floating'   => $floating,
        'hasLabel'   => (bool) $label,
        'isList'     => $isList,
        'disabled'   => $disabled,
        'custom'     => $useCustom,
        'multiple'   => $multiple,
    ]);
@endphp

<div class="w-full">
    @if($label && !$floating)
        <div class="flex items-baseline justify-between mb-1.5">
            <label for="{{ $selectId }}" class="block text-[length:var(--text-field-sm)] font-medium {{ $c['labelColor'] }}">
                {{ $label }}
                @if($required)<span class="text-error ml-0.5">*</span>@endif
            </label>
            @if($cornerHint)
                <span class="text-[length:var(--text-field-xs)] text-base-content/50">{{ $cornerHint }}</span>
            @endif
        </div>
    @endif

    @if($useCustom)
        <div class="relative w-full"
             x-data="{
                 open: false,
                 multi: {{ $multiple ? 'true' : 'false' }},
                 placeholder: @js($placeholder ?? '選択'),
                 values: [],
                 options: [],
                 init() {
                     const sel = this.$refs.native;
                     this.options = Array.from(sel.options)
                         .filter(o => !o.hidden)
                         .map(o => ({ value: o.value, label: o.text, disabled: o.disabled }));
                     this.values = Array.from(sel.selectedOptions).map(o => o.value).filter(v => v !== '');
                 },
                 toggle(value, isDisabled) {
                     if (isDisabled) return;
                     if (this.multi) {
                         const i = this.values.indexOf(value);
                         if (i >= 0) this.values.splice(i, 1); else this.values.push(value);
                     } else {
                         this.values = [value];
                         this.open = false;
                     }
                     this.sync();
                 },
                 remove(value) {
                     this.values = this.values.filter(v => v !== value);
                     this.sync();
                 },
                 sync() {
                     const sel = this.$refs.native;
                     Array.from(sel.options).forEach(o => o.selected = this.values.includes(o.value));
                     sel.dispatchEvent(new Event('change', { bubbles: true }));
                 },
                 isSelected(v) { return this.values.includes(v); },
                 optionFor(v) { return this.options.find(o => o.value === v); },
             }"
             @keydown.escape.window="open = false"
             @click.outside="open = false"
        >
            {{-- Native <select> drives form submission and provides initial state.
                 sr-only keeps it accessible to screen readers while invisible. --}}
            <select
                x-ref="native"
                id="{{ $selectId }}"
                class="sr-only"
                tabindex="-1"
                aria-hidden="true"
                @if($name) name="{{ $name }}{{ $multiple ? '[]' : '' }}" @endif
                @if($multiple) multiple @endif
                @if($required) required @endif
                @if($disabled) disabled @endif
                {{ $attributes->whereStartsWith('wire:') }}
                {{ $attributes->whereDoesntStartWith('wire:')->whereDoesntStartWith('class') }}
            >
                @if($placeholder && !$multiple)
                    <option value="" disabled selected hidden>{{ $placeholder }}</option>
                @endif
                {{ $slot }}
            </select>

            <button
                type="button"
                class="{{ $c['trigger'] }}"
                @click="open = !open"
                :aria-expanded="open"
                @if($disabled) disabled @endif
            >
                <span class="{{ $c['triggerInner'] }}">
                    {{-- single mode: show label or placeholder --}}
                    <template x-if="!multi">
                        <span :class="values.length ? '{{ $c['triggerText'] }}' : '{{ $c['triggerText'] }} {{ $c['placeholder'] }}'"
                              x-text="values.length ? optionFor(values[0])?.label : placeholder"></span>
                    </template>
                    {{-- multi mode: chips, or placeholder when empty --}}
                    <template x-if="multi && values.length === 0">
                        <span class="{{ $c['triggerText'] }} {{ $c['placeholder'] }}" x-text="placeholder"></span>
                    </template>
                    <template x-if="multi && values.length > 0">
                        <template x-for="v in values" :key="v">
                            <span class="{{ $c['chip'] }}">
                                <span x-text="optionFor(v)?.label"></span>
                                <button type="button" class="{{ $c['chipRemove'] }}" @click.stop="remove(v)" aria-label="Remove">
                                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </span>
                        </template>
                    </template>
                </span>
                <svg class="{{ $c['chevron'] }}" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd"/>
                </svg>
            </button>

            <ul x-show="open" x-cloak x-transition.opacity.duration.100ms class="{{ $c['dropdown'] }}" role="listbox" :aria-multiselectable="multi">
                <template x-for="opt in options" :key="opt.value">
                    <li class="{{ $c['option'] }}"
                        :class="(isSelected(opt.value) ? '{{ $c['optionSelected'] }} ' : '') + (opt.disabled ? '{{ $c['optionDisabled'] }}' : '')"
                        role="option"
                        :aria-selected="isSelected(opt.value)"
                        @click="toggle(opt.value, opt.disabled)"
                    >
                        <span x-text="opt.label"></span>
                        <svg x-show="isSelected(opt.value)" class="{{ $c['optionCheck'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </li>
                </template>
            </ul>
        </div>
    @else
        <div class="{{ $c['wrapper'] }}">
            @if($floating && $label && !$isList)
                <label for="{{ $selectId }}" class="{{ $c['floatingLabel'] }}">
                    {{ $label }}
                    @if($required)<span class="text-error ml-0.5">*</span>@endif
                </label>
            @endif

            <select
                id="{{ $selectId }}"
                @if($name) name="{{ $name }}{{ $multiple ? '[]' : '' }}" @endif
                @if($multiple) multiple @endif
                @if($rows) size="{{ $rows }}" @endif
                {{ $attributes->whereStartsWith('wire:') }}
                {{ $attributes->whereDoesntStartWith('wire:')->merge(['class' => $c['selectClass']]) }}
                @if($required) required @endif
                @if($disabled) disabled @endif
            >
                @if($placeholder && !$multiple)
                    <option value="" disabled selected hidden>{{ $placeholder }}</option>
                @endif
                {{ $slot }}
            </select>

            @if(!$isList)
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-base-content/40 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </span>
            @endif
        </div>
    @endif

    @if($hintText)
        <p class="mt-1.5 text-[length:var(--text-field-sm)] {{ $c['hintColor'] }}">{{ $hintText }}</p>
    @endif
</div>
