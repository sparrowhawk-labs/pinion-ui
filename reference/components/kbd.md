# x-kbd

Keyboard-key display, renders semantic `<kbd>` styled with plain Tailwind (no daisyUI `kbd`/`kbd-*` classes — see project rule in CLAUDE.md invariant 6). Five sizes and three appearances (`default` = bordered "key cap" chrome; `soft` = neutral chip; `outline` = transparent with border). Often nested inside `<x-button>` slots or tooltips for shortcut hints.

**Playground page**: [`pinion-ui-playground/resources/views/pages/kbd.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/kbd.blade.php) — full variant matrix and live demos.

## When to use

- Showing shortcut keys (`⌘K`, `Esc`, `Ctrl+C`) inline in docs, tooltips, command palettes.
- Inside a `<x-button>` slot to advertise a hotkey ("Save  ⌘S").
- For body-text strong emphasis use `<strong>`/`<b>`; for code prefer `<code>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg' \| 'xl'` | `'md'` | Box size (height/min-width/font-size), reproducing daisyUI's former `kbd-xs` … `kbd-xl` steps as plain Tailwind scale classes (`h-4`…`h-8`). |
| `appearance` | `'default' \| 'soft' \| 'outline'` | `'default'` | Visual style. `default` = bordered "key cap" chrome (`bg-base-200` + hairline border + thicker bottom border). `soft` = neutral tinted chip with no border. `outline` = transparent with a hairline border. |

All other attributes pass through to the `<kbd>` element.

## Slots

- **default** — key label content (a single character, a symbol like `⌘`, or a short word like `Esc`).

## Examples

### Basic

```blade
<x-kbd>K</x-kbd>
<x-kbd>Esc</x-kbd>
<x-kbd>⌘</x-kbd>
```

### Sizes

```blade
<x-kbd size="xs">⌘</x-kbd>
<x-kbd size="sm">⌘</x-kbd>
<x-kbd size="md">⌘</x-kbd>
<x-kbd size="lg">⌘</x-kbd>
<x-kbd size="xl">⌘</x-kbd>
```

### Appearances

```blade
<x-kbd>Tab</x-kbd>
<x-kbd appearance="soft">Tab</x-kbd>
<x-kbd appearance="outline">Tab</x-kbd>
```

### Shortcut group

```blade
<span class="inline-flex items-center gap-1">
    <x-kbd>⌘</x-kbd> + <x-kbd>K</x-kbd>
</span>
```

### Inside a button

```blade
<x-button>
    Open search
    <x-kbd appearance="soft" size="xs" class="ml-2">⌘K</x-kbd>
</x-button>
```

## Class composition

See [`src/Compose/KbdComposer.php`](../../src/Compose/KbdComposer.php). Returns a single `root` class composed of structural layout (`inline-flex items-center justify-center align-middle`, `rounded-[var(--radius-field)] px-[.5em]`) + a size step (height/min-width/font-size) + an appearance (background/border). No daisyUI `kbd`/`kbd-*` classes are used — the box shape reproduces daisyUI's former computed values (per `docs/daisyui/pages/daisyui-5-components__4.md` `kbd` entry and `demo/node_modules/daisyui/components/kbd.css`: `border-radius: var(--radius-field)`, height/min-width steps of `.25rem × {4,5,6,7,8}`, `padding-inline: .5em`, font-size steps `.625rem/.75rem/.875rem/1rem/1.125rem`) using plain Tailwind utilities.

## Related

- [`<x-button>`](./button.md) — common host for kbd chips advertising shortcuts.
- [`<x-tooltip>`](./tooltip.md) — pair kbd with tooltip text on hover.
- [`<x-badge>`](./badge.md) — visually similar chip with non-keyboard semantics.

## Notes

- `appearance="soft"` strips the daisyUI default border entirely (`border-0`) for a flatter chip suited to dense UI like command palettes. `appearance="outline"` keeps a single-pixel border but drops the inner shadow.
- Renders `<kbd>` (not `<span>`) — keep contents short to preserve the semantic meaning for screen readers and search engines.
- `xl` size requires daisyUI 5; older versions silently fall back to `lg`.
