# x-tabs

Tabbed content panel composed of a parent `<x-tabs>` and nested `<x-tab>` children — each child contributes one tab button **and** one panel via its default slot. The parent owns a single Alpine `activeTab` state; the children read and write it via Alpine scope inheritance. Three visual variants and three sizes; the active panel cross-fades on switch.

**Playground page**: [`pinion-ui-playground/resources/views/pages/tabs.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/tabs.blade.php) — full variant matrix and live demos.

> **v0.4.0 BC** — the previous `:tabs="[key => [label, content, icon?]]"` array API was removed in favour of the nested-component form below. See [`SEMVER.md`](../../SEMVER.md). The motivation: pass any Blade markup (other components, slots, partials) into a panel without going through `{!! !!}`.

## When to use

- Switching between related views inside the same surface — settings sections, product detail pages, dashboard panels.
- When all panels share the same context and only one is visible at a time. For independent collapsible regions use [`<x-accordion>`](./accordion.md) or [`<x-collapse>`](./collapse.md).
- For top-level page navigation prefer [`<x-menu-item>`](./menu-item.md) inside your own nav, not tabs.

## Props

### `<x-tabs>` (parent)

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | `'underline' \| 'boxed' \| 'pill'` | `'underline'` | Visual style. `underline` shows a baseline rule and a colored underline under the active tab; `boxed` renders each button as a chip and fills the active one with `bg-primary`; `pill` is similar but uses a soft `bg-base-200` chip. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Tab button height / horizontal padding / text size via `--h-field-*`, `--px-field-*`, `--text-field-*` tune tokens. Passed down to each `<x-tab>` via `@aware`. |
| `default` | `string \| null` | `null` | `name` of the tab open on first render. When `null`, the parent picks the first child's `name` at mount via `x-init`. |

All other attributes pass through to the root `<div>` (`id`, `wire:ignore`, etc.).

### `<x-tab>` (child)

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string` (required) | — | Identifier compared against `activeTab`. Must be unique within the parent `<x-tabs>`. |
| `label` | `string \| null` | `null` | Button text. Falls back to `name` when omitted. |
| `icon` | `string \| null` | `null` | Raw inline HTML (typically SVG) rendered before the label inside an `inline-flex items-center gap-xs` wrapper. Output via `{!! !!}` — must be trusted markup. |

`variant` and `size` are not declared on `<x-tab>` as props; they're inherited from the parent via Blade `@aware`.

## Slots

- **`<x-tabs>` default slot** — one or more `<x-tab>` children. Other elements are technically allowed but won't participate in the tab list / panel switching.
- **`<x-tab>` default slot** — the panel body. Any Blade markup is fine; the slot is escaped by default, so HTML must be opted in the usual Blade way.

## Examples

### Basic

```blade
<x-tabs>
    <x-tab name="overview" label="Overview">
        <p>Overview body.</p>
    </x-tab>
    <x-tab name="specs" label="Specs">
        <p>Specs body.</p>
    </x-tab>
    <x-tab name="reviews" label="Reviews">
        <p>★★★★☆ Reviews body.</p>
    </x-tab>
</x-tabs>
```

### Boxed variant with explicit default

```blade
<x-tabs variant="boxed" default="specs">
    <x-tab name="overview" label="Overview">…</x-tab>
    <x-tab name="specs"    label="Specs">…</x-tab>
    <x-tab name="reviews"  label="Reviews">…</x-tab>
</x-tabs>
```

### With per-tab icon

`icon` is raw HTML (typically inline SVG, or `<x-i>` rendered to a string). It is emitted before `label` inside the same flex row.

```blade
@php
    $svg = fn ($d) =>
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">'.$d.'</svg>';
    $home    = $svg('<path d="M3 11l9-7 9 7M5 10v10h14V10" stroke-linejoin="round" stroke-linecap="round"/>');
    $profile = $svg('<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7" stroke-linejoin="round" stroke-linecap="round"/>');
@endphp

<x-tabs variant="boxed">
    <x-tab name="home"    label="Home"    :icon="$home">…</x-tab>
    <x-tab name="profile" label="Profile" :icon="$profile">…</x-tab>
</x-tabs>
```

### Pill variant, small size, many tabs (wraps)

The parent uses `flex flex-wrap`, so a long tab list wraps naturally; each wrapped panel still occupies its own row (CSS `order` keeps panels after the button row).

```blade
<x-tabs variant="pill" size="sm">
    <x-tab name="t1" label="Inbox">…</x-tab>
    <x-tab name="t2" label="Drafts">…</x-tab>
    <x-tab name="t3" label="Sent">…</x-tab>
    <x-tab name="t4" label="Spam">…</x-tab>
    <x-tab name="t5" label="Trash">…</x-tab>
</x-tabs>
```

### Looping with Blade

`<x-tab>` is a regular anonymous component, so `@foreach` works the same as anywhere else:

```blade
<x-tabs>
    @foreach ($sections as $key => $section)
        <x-tab :name="$key" :label="$section['label']">
            {!! $section['body'] !!}
        </x-tab>
    @endforeach
</x-tabs>
```

## Class composition

Class strings are built by [`TabsComposer::compose($props)`](../../src/Compose/TabsComposer.php). The composer is called from **both** parent and child Blades with the same `variant`/`size` and returns:

| Key | Where it's applied |
|---|---|
| `root` | Parent `<div role="tablist">` — flex wrapper with variant-specific spacing and (underline only) the `::after` rule that extends the baseline to the row end. |
| `tabBase` | Each child `<button role="tab">` — size/typography/positioning shared across variants. |
| `tabActive` / `tabIdle` | Active vs idle button class, swapped per button via Alpine `x-bind:class`. |
| `iconWrap` | Wrapper around `icon + label` when `icon` is set. |
| `panel` | Each child `<div role="tabpanel">` — `order-1 basis-full mt-sm` so panels sit below the button row inside the same flex container. |

> **v0.4.0 BC**: the previous `tabList` and `panels` keys were removed — the new layout puts buttons and panels as siblings inside a single flex-wrap container, so there is no separate tablist wrapper element.

## Related

- [`<x-accordion>`](./accordion.md) — multiple regions visible at once.
- [`<x-collapse>`](./collapse.md) — single collapsible region.
- [`<x-menu-item>`](./menu-item.md) — for navigational links rather than panel switching.

## Notes

- **`name` must be unique per `<x-tabs>` instance.** Duplicates collapse to the same panel, since `activeTab` matches `name` directly.
- **State is local to the parent's Alpine scope.** Multiple `<x-tabs>` on the same page don't interfere; each owns its own `x-data="{ activeTab }"`.
- **Layout uses CSS `order`** — buttons get `order-0` (via `tabBase`) and panels get `order-1` (via `panel`), inside the parent's `flex flex-wrap`. That's how the panel always renders **after** the button row even though `<x-tab>` interleaves the two in source order.
- **Underline indicator** is drawn with `border-b-2` on the active button itself plus an `::after` pseudo-element on the root that extends the baseline rule across the unused row width. No separate moving indicator.
- **Panels use `x-show` + `x-cloak`**. Include `[x-cloak]{display:none}` in your CSS (`ui:install` does this) to avoid a flash of all panels on first paint.
- **Alpine inside `<x-tab>` slot content**: same trap as anywhere else inside `<x-...>` — use the long form `x-bind:` / `x-on:`, not Blade's `:` / `@` shorthand. See [AGENTS.md](../../AGENTS.md#alpine-inside-x--components--use-the-full-prefix).
- **Blade `@verbatim` for code samples**: literal `<x-tabs>` / `<x-tab>` inside `<pre>`, `<code>`, or `@section` strings must be wrapped with `@verbatim` and `&lt;` entities, otherwise Blade tries to mount them.
