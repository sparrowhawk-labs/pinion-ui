# x-avatar

User avatar with priority fallback chain: `src` (image) → `initials` → `icon` → default slot. Five sizes × three shapes × eight colors × four appearances, plus an optional presence dot in the bottom-right corner.

**Playground page**: [`pinion-ui-playground/resources/views/pages/avatar.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/avatar.blade.php) — full variant matrix and live demos.

## When to use

- Showing a user / org identity in headers, lists, comments, mentions.
- Pair multiple instances via `<x-avatar-group>` for member lists / contributor rows.
- For decorative figures without identity semantics, prefer a plain styled `<img>` or `<x-i>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `src` | `string \| null` | `null` | Image URL. Wins over `initials` / `icon` / slot when set. |
| `alt` | `string` | `''` | `alt` text for the `<img>` — set this when `src` is used. |
| `initials` | `string \| null` | `null` | 1–3 character fallback (e.g. `'AK'`). Rendered when `src` is null. |
| `icon` | `string \| null` | `null` | Pinion-icons name used when both `src` and `initials` are null. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'neutral'` | Semantic color for the fallback chip (initials / icon). Ignored when `src` is set. |
| `appearance` | `'solid' \| 'soft' \| 'outline'` | `'soft'` | Fallback chip style. `solid` = filled; `soft` = tinted bg + colored text; `outline` = transparent with colored border. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg' \| 'xl'` | `'md'` | Pixel size: xs=24, sm=32, md=40, lg=56, xl=80. |
| `shape` | `'circle' \| 'rounded' \| 'square'` | `'circle'` | Mask shape — `circle` = `rounded-full`; `rounded` = field radius; `square` = box radius (large rounded square). |
| `status` | `'online' \| 'offline' \| 'busy' \| 'away' \| null` | `null` | Presence dot in the bottom-right. Maps to success / neutral / error / warning. |

All other attributes pass through to the outer `<span>`.

## Slots

- **default** — custom fallback content when `src`, `initials`, and `icon` are all null.

## Examples

### Basic

```blade
<x-avatar src="/users/akihiko.jpg" alt="Akihiko" />
<x-avatar initials="AK" color="primary" />
<x-avatar icon="user" color="neutral" />
```

### Sizes

```blade
<x-avatar initials="A" size="xs" />
<x-avatar initials="A" size="sm" />
<x-avatar initials="A" size="md" />
<x-avatar initials="A" size="lg" />
<x-avatar initials="A" size="xl" />
```

### Shapes and appearances

```blade
<x-avatar initials="AK" shape="square" appearance="solid" color="primary" />
<x-avatar initials="AK" shape="rounded" appearance="outline" color="accent" />
```

### With status

```blade
<x-avatar src="/users/akihiko.jpg" alt="Akihiko" status="online" />
<x-avatar initials="AK" status="busy" />
```

## Class composition

Avatar composes classes **inline** in [`src/resources/views/components/avatar.blade.php`](../../src/resources/views/components/avatar.blade.php) — it predates the Composer pattern used by form components. Class strings are stable across patch versions. Override with `class="..."` (Tailwind classes merge naturally via the attribute bag).

## Related

- [`<x-avatar-group>`](./avatar-group.md) — overlapping row of avatars with optional `+N` overflow.
- [`<x-indicator>`](./indicator.md) — anchor an arbitrary badge (not just the four `status` colors) at a corner.
- [`<x-i>`](https://github.com/sparrowhawk-labs/pinion-icons) — the icon component used for the `icon` fallback.

## Notes

- The fallback chain is strict and short-circuiting: if `src` is set, `initials` / `icon` are never evaluated even if also passed.
- The status dot uses `ring-2 ring-base-100` so it sits cleanly on any surface — if you place the avatar on a `bg-base-200` container, the dot still gets a base-100 hairline ring (this is intentional and matches daisyUI's pattern).
- `outline` appearance pulls `tune-border` so border width tracks the active tune (`default` / `tech` / etc.).
