# x-input-group

Generic horizontal joiner for form-shaped children. Renders any combination of `<input>` / `<select>` / `<textarea>` / `<button>` / `<span class="$addon">` side-by-side with shared borders, single rounded ends, and inner-border collapse — same technique as [`<x-button-group>`](./button-group.md) and [`<x-input-number>`](./input-number.md).

Use this when you need a multi-element row that isn't centred on a single primary `<x-input>` (where you'd reach for [`<x-input>`'s `append` slot](./input.md) instead). Typical patterns: country-code select + phone input, URL protocol select + path input, two equally-weighted name inputs, search input + submit button.

**Playground page**: [`pinion-ui-playground/resources/views/pages/input-group.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/input-group.blade.php) — full variant matrix and live demos.

## When to use

- A `<select>` + `<input>` row (country code + phone, currency + amount).
- A pair of equal-weight inputs (first name + last name).
- An `<input>` + `<button>` search bar where the button is a peer rather than an inline accessory.
- For "$ amount .00" style text decorators around a single primary `<input>`, prefer [`<x-input prefix="$" suffix=".00">`](./input.md) — input-group is for cases where the children themselves are real form elements.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | `string \| null` | `null` | Visible label above the row. |
| `hint` | `string \| null` | `null` | Helper text below the row; shadowed by `error`. |
| `error` | `string \| null` | `null` | Error message; flips label/hint colour to `text-error` and replaces `hint`. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg'` | `'md'` | Height / padding / text size for `$c['addon']` text spans (see Slots). Does **not** automatically apply to child inputs — pass `size="..."` on each `<x-input>` / `<x-select>` child too so heights line up. |

All other attributes pass through to the wrapper `<div>` (e.g. `class`, `x-data`, `role="group"`).

## Slots

- **default** — the children. Any form-shaped element. Direct `<input>` / `<select>` / `<textarea>` children automatically get `flex-1 min-w-0` so they stretch to fill the row.

The composer also exposes an `addon` class string for plain-text decorators rendered as spans. Use it inside the slot:

```blade
<x-input-group size="md">
    <span class="{{ $addon }}">https://</span>
    <x-input type="text" name="domain" />
</x-input-group>
```

(Convention: pull `$addon = $c['addon']` from a parent if you compose multiple, or call the composer once and pass the dict down. Most demos read it from the composed dict.)

Or — most ergonomic — let the user inline the addon class via the helper:

```blade
@php $addon = SparrowhawkLabs\PinionUi\Compose\InputGroupComposer::compose(['size' => 'md'])['addon']; @endphp
<x-input-group size="md">
    <span class="{{ $addon }}">$</span>
    <x-input type="number" name="amount" :value="0" />
    <span class="{{ $addon }}">.00</span>
</x-input-group>
```

## Examples

### Two-input pair (first + last name)

```blade
<x-input-group label="Full name">
    <x-input name="first" placeholder="First" />
    <x-input name="last"  placeholder="Last" />
</x-input-group>
```

### Country code + phone

```blade
<x-input-group label="Phone">
    <x-select name="country">
        <option value="+81">JP +81</option>
        <option value="+1">US +1</option>
    </x-select>
    <x-input type="tel" name="phone" placeholder="90-1234-5678" />
</x-input-group>
```

### URL protocol + path

```blade
@php $c = SparrowhawkLabs\PinionUi\Compose\InputGroupComposer::compose([]); @endphp
<x-input-group label="Website">
    <span class="{{ $c['addon'] }}">https://</span>
    <x-input name="url" placeholder="example.com" />
</x-input-group>
```

### Search bar (input + submit)

```blade
<x-input-group>
    <x-input type="search" name="q" placeholder="Search…" />
    <x-button type="submit" appearance="solid" color="primary">Go</x-button>
</x-input-group>
```

### Size variants

```blade
<x-input-group size="sm">
    <x-input size="sm" placeholder="Compact" />
    <x-button size="sm">Send</x-button>
</x-input-group>

<x-input-group size="lg">
    <x-input size="lg" placeholder="Roomy" />
    <x-button size="lg">Send</x-button>
</x-input-group>
```

### Error state

```blade
<x-input-group label="Email" error="Domain not recognised">
    <x-input name="local" placeholder="user" />
    <span class="{{ $c['addon'] }}">@</span>
    <x-input name="domain" placeholder="example.com" />
</x-input-group>
```

## Class composition

See [`src/Compose/InputGroupComposer.php`](../../src/Compose/InputGroupComposer.php). Returns `wrapper`, `addon`, `labelColor`, `hintColor`.

- **`wrapper`** uses `inline-flex w-full` + Tailwind arbitrary descendant variants to: zero every child's border-radius, restore on first/last children, collapse the inner border between adjacent items, and stretch direct `<input>` / `<select>` / `<textarea>` children via `flex-1 min-w-0`.
- **`addon`** is the helper class for text decorator `<span>`s — `bg-base-200` background, `--h-field-{size}` height, `--px-field-{size}` padding, `--text-field-{size}` text size. Use with `whitespace-nowrap` baked in so multi-word addons (`USD/month`) don't wrap.

## Related

- [`<x-input>`](./input.md) — single-input row with `prefix` / `suffix` / `append` slots; covers most "input + decorator" cases without reaching for input-group.
- [`<x-button-group>`](./button-group.md) — same join technique for button-only rows.
- [`<x-input-number>`](./input-number.md) — specialised quantity selector using the same join wrapper.

## Notes

- The wrapper does **not** automatically sync the `size` prop to its child `<x-input>` / `<x-select>` etc. — pass `size="..."` on each child so heights line up. (Auto-sync via a Blade context would require a global state pass; opt-in is simpler and lets you mix sizes on purpose.)
- Direct `<input>` / `<select>` / `<textarea>` children get `flex-1 min-w-0`. Wrapping a child in a `<div>` defeats this — keep form elements as direct children.
- For "addon span" decorators, prefer the `$c['addon']` helper class over hand-styled spans so the heights stay in sync with field tokens across tunes.
- daisyUI's `.join` and `.join-item` classes are intentionally **not** used here — the same specificity issue that bit button-group ([`<x-button-group>` rewrite in v0.3.3](./button-group.md)) applies. The Tailwind arbitrary variant approach wins cleanly.
