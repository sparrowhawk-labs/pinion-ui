# x-avatar-group

Stacked / overlapping row of `<x-avatar>` children, built on daisyUI's `avatar-group` utility. Useful for member lists, contributor rows, and "+N more" overflow patterns.

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

- **default** — one or more `<x-avatar>` children. The visual ring around each avatar is provided by `<x-avatar>` itself; this wrapper only handles the negative-margin stacking.

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

See [`src/Compose/AvatarGroupComposer.php`](../../src/Compose/AvatarGroupComposer.php). The composer returns a single `root` class string of the form `avatar-group {spacing}`. The daisyUI `avatar-group` utility is responsible for the ring-on-avatar styling that makes overlap legible.

## Related

- [`<x-avatar>`](./avatar.md) — the individual avatar; required as a child.
- [`<x-indicator>`](./indicator.md) — alternative for badging a single avatar's corner without stacking.

## Notes

- Children should be `<x-avatar>` for the daisyUI ring outline to render correctly — wrapping with another tag will break the `:not(.avatar)` selector chain in daisyUI's CSS.
- The visual stacking order in the DOM is left-to-right, but z-index goes the other way (last avatar sits on top). Reorder your loop if you need a different stacking direction.
- Per `docs/daisyui/pages/avatar.md`, the `avatar-group` utility expects `.avatar` children to apply the ring — if you customise the inner classes of `<x-avatar>`, keep that base class intact.
