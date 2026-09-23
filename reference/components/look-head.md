# x-look-head

Applies the visitor's **theme × tune before first paint** (v0.20.0). Without it, a remembered dark theme shows as the server default for one frame and then flips — the switchers only run after Alpine boots. Put it early in `<head>`, **before `@vite`**.

Precedence: `?theme=` / `?tune=` (allowlist-checked, and remembered) → the choice the switchers stored in `localStorage` → what the server put on `<html>`.

A shared link wins over the recipient's remembered choice, so "look at it in carbon" opens in carbon.

## Props

All default to `config('pinion-ui.look')`.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `themes` | `array \| null` | `look.themes` | Allowed FAMILIES (bare names; `glacier` allows `glacier-dark`). null = whole lineup. |
| `tunes` | `array \| null` | `look.tunes` | Allowed tunes. null = all (`pn_tunes()`). |
| `storageKey` | `string \| null` | `look.storage_key` | localStorage prefix — must match the switchers (they default to the same config). |
| `query` | `bool \| null` | `look.query` | Honor `?theme=` / `?tune=`. |

## The server side: `pn_look()`

`pn_look()` resolves the same config against the current request and returns `['theme', 'tune', 'backdrop']`. Anything outside the allowlist falls back to the default, so a hand-typed URL can never put an unknown name into `data-theme` (which would drop every color). It never throws — a stale config cache without the `look` block falls back to built-in defaults.

## Example

```blade
@php($look = pn_look())
<html data-theme="{{ $look['theme'] }}" data-tune="{{ $look['tune'] }}">
<head>
    <x-look-head />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-backdrop />
    <x-settings-switcher />  {{-- lineup narrowed to look.themes, same storage key --}}
```

```php
// config/pinion-ui.php
'look' => [
    'themes' => ['glacier', 'carbon'],
    'theme' => 'glacier-dark',
    'tunes' => ['proto', 'tech'],
    'tune' => 'proto',
    'query' => true,
    'storage_key' => 'pn',
    'backdrop' => 'flow',
    'backdrops' => null,
],
```
