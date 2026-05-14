@props([
    'name' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'length' => 6,
    'type' => 'numeric',
    'size' => 'md',
    'masked' => false,
    'value' => '',
    'autofocus' => false,
    'disabled' => false,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\PinInputComposer;

    $c = PinInputComposer::compose([
        'size'  => $size,
        'error' => $error,
    ]);
    $hintText  = $error ?: $hint;
    $inputType = $masked ? 'password' : 'text';
    $inputmode = $type === 'numeric' ? 'numeric' : 'text';
    $pattern   = $type === 'numeric' ? '[0-9]' : '[0-9a-zA-Z]';
    $allowJs   = $type === 'numeric' ? '/[0-9]/' : '/[0-9a-zA-Z]/';
    $initial   = json_encode(str_split((string) $value));
    $length    = max(1, (int) $length);
@endphp

<div class="w-full"
    x-data="{
        digits: Array.from({ length: {{ $length }} }, (_, i) => ({{ $initial }})[i] ?? ''),
        get combined() { return this.digits.join(''); },
        normalize(c) {
            if (!c) return '';
            const ch = c.slice(-1);
            return {{ $allowJs }}.test(ch) ? ch : '';
        },
        onInput(idx, e) {
            const ch = this.normalize(e.target.value);
            this.digits[idx] = ch;
            if (ch && idx < this.digits.length - 1) {
                this.$nextTick(() => this.$refs['pin' + (idx + 1)]?.focus());
            }
        },
        onKeydown(idx, e) {
            if (e.key === 'Backspace' && !this.digits[idx] && idx > 0) {
                this.$refs['pin' + (idx - 1)]?.focus();
            } else if (e.key === 'ArrowLeft' && idx > 0) {
                e.preventDefault();
                this.$refs['pin' + (idx - 1)]?.focus();
            } else if (e.key === 'ArrowRight' && idx < this.digits.length - 1) {
                e.preventDefault();
                this.$refs['pin' + (idx + 1)]?.focus();
            }
        },
        onPaste(e) {
            e.preventDefault();
            const txt = (e.clipboardData?.getData('text') || '').trim();
            const chars = Array.from(txt).map(c => this.normalize(c)).filter(c => c);
            for (let i = 0; i < this.digits.length; i++) {
                this.digits[i] = chars[i] ?? '';
            }
            let lastFilled = -1;
            for (let i = 0; i < this.digits.length; i++) {
                if (this.digits[i]) lastFilled = i;
            }
            const target = lastFilled === this.digits.length - 1 ? lastFilled : Math.min(lastFilled + 1, this.digits.length - 1);
            if (target >= 0) this.$nextTick(() => this.$refs['pin' + target]?.focus());
        },
    }">
    @if($label)
        <label class="block mb-1.5 text-[length:var(--text-field-sm)] font-medium {{ $c['labelColor'] }}">
            {{ $label }}
        </label>
    @endif

    <div class="{{ $c['wrapper'] }}">
        @for($i = 0; $i < $length; $i++)
            <input
                type="{{ $inputType }}"
                x-model="digits[{{ $i }}]"
                x-ref="pin{{ $i }}"
                pattern="{{ $pattern }}"
                inputmode="{{ $inputmode }}"
                maxlength="1"
                autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                x-on:input="onInput({{ $i }}, $event)"
                x-on:keydown="onKeydown({{ $i }}, $event)"
                @if($i === 0) x-on:paste="onPaste($event)" @endif
                @if($autofocus && $i === 0) autofocus @endif
                @if($disabled) disabled @endif
                aria-label="Digit {{ $i + 1 }}"
                class="{{ $c['box'] }}"
            />
        @endfor
    </div>

    @if($name)
        <input type="hidden" name="{{ $name }}" x-bind:value="combined" />
    @endif

    @if($hintText)
        <p class="mt-1.5 text-[length:var(--text-field-sm)] {{ $c['hintColor'] }}">{{ $hintText }}</p>
    @endif
</div>
