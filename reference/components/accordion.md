# x-accordion

Group of expandable items composed of a parent `<x-accordion>` and nested `<x-accordion-item>` children — each child contributes one header button and one disclosure region via its default slot. The parent owns a single Alpine `open` state (a string for single-open, an array for `multiple`); children read/write it via Alpine scope inheritance. Uses `x-collapse` for height animation and emits `aria-expanded` on each header button.

**Playground page**: [`pinion-ui-playground/resources/views/pages/accordion.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/accordion.blade.php) — full variant matrix and live demos.

> **v0.4.0 BC** — the previous `:items="[[title, content], …]"` array API was removed in favour of the nested-component form below. See [`SEMVER.md`](../../SEMVER.md). The motivation: pass any Blade markup (links, lists, components, multi-paragraph copy) into a panel without escaping through `{!! !!}`.

## When to use

- FAQ pages, settings groups, or any list of disclosure regions where most are closed at rest.
- Set `multiple` when items are independent (e.g. filter panels); leave default for mutually exclusive sections.
- For a single disclosure use [`<x-collapse>`](./collapse.md); for tabbed (always-one-visible) navigation use [`<x-tabs>`](./tabs.md).

## Props

### `<x-accordion>` (parent)

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `multiple` | `bool` | `false` | If `true`, multiple items can be open simultaneously (state is an array). If `false`, opening one closes the others (state is a single key or `null`). |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Drives `--h-field-*`, `--px-field-*`, `--text-field-*` tune tokens for header height / padding / text size. Passed down to each `<x-accordion-item>` via `@aware`. |

All other attributes pass through to the root `<div>` (`id`, `wire:ignore`, etc.).

### `<x-accordion-item>` (child)

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | `string` | `''` | Header button text. |
| `name` | `string \| null` | auto | Identifier for the item in the parent's `open` state. When `null`, a per-render `item_<8 hex>` is generated. Provide an explicit `name` if you want to control which item is open from the outside (e.g. server-side default), or to make the open state deterministic across renders. |

`size` and `multiple` are not declared on `<x-accordion-item>` as props; they're inherited from the parent via Blade `@aware`.

## Slots

- **`<x-accordion>` default slot** — one or more `<x-accordion-item>` children.
- **`<x-accordion-item>` default slot** — the body of the disclosure region. Any Blade markup is fine: links, lists, `<x-...>` components, multi-paragraph copy, even nested forms.

## Examples

### Basic (single-open)

```blade
<x-accordion>
    <x-accordion-item title="What is Pinion UI?">
        A Blade component kit for Laravel.
    </x-accordion-item>
    <x-accordion-item title="Does it require Alpine?">
        Only dynamic components need Alpine.
    </x-accordion-item>
    <x-accordion-item title="How do I theme it?">
        Use daisyUI themes via <code>data-theme</code>.
    </x-accordion-item>
</x-accordion>
```

### Multiple open

```blade
<x-accordion :multiple="true">
    <x-accordion-item title="Privacy">
        <p>We collect minimal telemetry.</p>
    </x-accordion-item>
    <x-accordion-item title="Cookies">
        <p>Only session cookies are used.</p>
    </x-accordion-item>
</x-accordion>
```

### Rich body content

The child slot is a normal Blade slot, so any markup works — including other Pinion components.

```blade
<x-accordion>
    <x-accordion-item title="Install">
        <p class="mb-2">Add the package:</p>
        <pre><code>composer require sparrowhawk-labs/pinion-ui</code></pre>
        <p>Then run <code>php artisan ui:install</code>.</p>
    </x-accordion-item>
    <x-accordion-item title="Useful links">
        <ul class="list-disc list-inside space-y-1">
            <li><a href="/docs" class="link link-primary">Documentation</a></li>
            <li><a href="/changelog" class="link link-primary">Changelog</a></li>
        </ul>
    </x-accordion-item>
</x-accordion>
```

### Looping with Blade

`<x-accordion-item>` is a regular anonymous component, so `@foreach` works as expected. Pass an explicit `name` when you need open-state stability across renders.

```blade
<x-accordion>
    @foreach ($faq as $entry)
        <x-accordion-item :name="$entry->slug" :title="$entry->question">
            {!! $entry->answer_html !!}
        </x-accordion-item>
    @endforeach
</x-accordion>
```

### Large size

```blade
<x-accordion size="lg">
    <x-accordion-item title="Section 1">…</x-accordion-item>
    <x-accordion-item title="Section 2">…</x-accordion-item>
</x-accordion>
```

## Class composition

Class strings are built by [`AccordionComposer::compose($props)`](../../src/Compose/AccordionComposer.php). The composer is called from **both** parent and child Blades with the same `size` and returns:

| Key | Where it's applied |
|---|---|
| `root` | Parent `<div>` — `divide-y divide-base-300 radius-box tune-border border-base-300 overflow-hidden`. |
| `header` | Child `<button>` — height/padding/text from `sizeClasses`, plus `flex items-center justify-between` and hover state. |
| `icon` | The chevron `<svg>` — `w-4 h-4 transition-transform`. Rotates 180° when the item is open (driven by an Alpine `x-bind:class`). |
| `content` | The expanded body wrapper — vertical padding via `--space-compact`, padding-x from `sizeClasses`. |
| `sizeClasses` | The shared `min-h-*` / `px-*` / `text-*` triple. Exposed for downstream composition. |

## Related

- [`<x-collapse>`](./collapse.md) — single disclosure region (no shared open-state coordinator).
- [`<x-tabs>`](./tabs.md) — choose tabs when content swaps in place rather than expanding inline.

## Notes

- **State is local to the parent's Alpine scope.** Multiple `<x-accordion>` on the same page don't interfere; each owns its own `x-data="{ open }"`.
- **Children require `x-collapse`.** Wired automatically by `ui:install` (registers `@alpinejs/collapse`); if you bypass `ui:install`, register it yourself in `resources/js/app.js`.
- **Open-state identifier**: when you omit `name`, a fresh `item_<hex>` is generated each render. That's fine for static content, but if items are reordered or re-rendered (e.g. Livewire) the open state will reset. Provide an explicit `name` (slug, model id, anything stable) to anchor the state.
- **Single-open click-the-open-item closes it** — there is no always-one-open enforcement. Toggle logic is `open === '<key>' ? null : '<key>'`.
- **Alpine inside `<x-accordion-item>` slot content**: same trap as anywhere else inside `<x-...>` — use the long form `x-bind:` / `x-on:`, not Blade's `:` / `@` shorthand. See [AGENTS.md](../../AGENTS.md#alpine-inside-x--components--use-the-full-prefix).
- **Blade `@verbatim` for code samples**: literal `<x-accordion>` / `<x-accordion-item>` inside `<pre>`, `<code>`, or `@section` strings must be wrapped with `@verbatim` and `&lt;` entities, otherwise Blade tries to mount them.
