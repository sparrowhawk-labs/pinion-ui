# x-avatar-group

Stacked / overlapping row of `<x-avatar>` children, built with plain Tailwind (`flex` + negative-margin overlap + a ring on each child) — no daisyUI structural class. Useful for member lists, contributor rows, and "+N more" overflow patterns.

**Playground page**: [`pinion-ui-playground/resources/views/pages/avatar-group.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/avatar-group.blade.php) — full variant matrix and live demos.

## When to use

- Showing 2–6 members on a card or row header.
- Adding a trailing `+N` overflow avatar (use a plain `<x-avatar initials="+3" />`).
- For a single user identity use `<x-avatar>`; for filterable lists use a table or grid instead.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `spacing` | `'tight' \| 'normal' \| 'loose'` | `'normal'` | Horizontal overlap amount. `tight` = `-space-x-6` (heavy overlap); `normal` = `-space-x-4`; `loose` = `-space-x-2` (light overlap). |

All other attributes pass through to the root `<div>`.

## Slots

- **default** — one or more `<x-avatar>` children. The wrapper applies the ring (`[&>*]:ring-2 [&>*]:ring-base-100`) and the negative-margin stacking to its direct children.

## Examples

### Basic

```blade
<x-avatar-group>
    <x-avatar src="/users/a.jpg" />
    <x-avatar src="/users/b.jpg" />
    <x-avatar src="/users/c.jpg" />
</x-avatar-group>
```

### With overflow chip

```blade
<x-avatar-group>
    <x-avatar src="/users/a.jpg" />
    <x-avatar src="/users/b.jpg" />
    <x-avatar src="/users/c.jpg" />
    <x-avatar initials="+5" color="neutral" appearance="soft" />
</x-avatar-group>
```

### Spacing variants

```blade
<x-avatar-group spacing="tight">
    <x-avatar initials="A" />
    <x-avatar initials="B" />
    <x-avatar initials="C" />
</x-avatar-group>

<x-avatar-group spacing="loose">
    <x-avatar initials="A" />
    <x-avatar initials="B" />
    <x-avatar initials="C" />
</x-avatar-group>
```

## Class composition

See [`src/Compose/AvatarGroupComposer.php`](../../src/Compose/AvatarGroupComposer.php). The composer returns a single `root` class string of the form `flex {spacing} [&>*]:ring-2 [&>*]:ring-base-100` — a plain Tailwind flex row, the `-space-x-*` scale for overlap, and an arbitrary-variant selector that puts a `ring-base-100` border (matching `<x-avatar>`'s own status-dot ring color) on every direct child so overlapping avatars stay visually separated. No daisyUI class is emitted (per CLAUDE.md invariant 6).

## Related

- [`<x-avatar>`](./avatar.md) — the individual avatar; required as a child.
- [`<x-indicator>`](./indicator.md) — alternative for badging a single avatar's corner without stacking.

## Notes

- The ring is applied generically to every direct child (`[&>*]:ring-2 [&>*]:ring-base-100`) — any element works as a child, not just `<x-avatar>`, though `<x-avatar>` is the intended use case.
- The visual stacking order in the DOM is left-to-right, but z-index goes the other way (last avatar sits on top). Reorder your loop if you need a different stacking direction.
- Prior to this migration this component wrapped daisyUI's `avatar-group` class (per `docs/daisyui/pages/daisyui-5-components.md`: "Use `avatar-group` for containing multiple avatars"). It's now plain Tailwind per CLAUDE.md invariant 6 (daisyUI classes limited to semantic color utilities).
