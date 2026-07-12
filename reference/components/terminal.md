# x-terminal

A fake terminal window with a typewriter reveal — for demoing a CLI step (`artisan tinker`, a
seeder run, a build command, …) without an actual screen/terminal recording. Commands animate
character-by-character; output lines appear instantly, the way real stdout would.

## When to use

- Showing "what an engineer would type" inside a demo/marketing page or an onboarding walkthrough,
  without recording a real terminal (real terminal recording steals window focus and is brittle to
  automate).
- Illustrating a CLI-only step that has no UI equivalent, right next to the UI flow it leads into.
- Not for actual interactive terminals — this is a scripted, one-shot animation, not a shell.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `lines` | `array<int, array{prompt?: string, text: string, typed?: bool}>` | `[]` | The script. `prompt` defaults to `'$'`. `typed` (default `true`) animates the line character-by-character; set `false` for an output/response line that should just appear instantly. |
| `title` | `string \| null` | `null` | Optional label shown next to the traffic-light dots (e.g. a fake window title). |
| `autoplay` | `bool` | `true` | Starts the animation on mount (`x-init`). Set `false` to trigger `run()` yourself (e.g. from a parent's Alpine scope or on an intersection observer). |
| `speed` | `int` | `28` | Milliseconds per character for `typed` lines. |
| `lineDelay` | `int` | `350` | Milliseconds to pause after each line (both typed and instant) before the next one starts. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | Color of the `prompt` glyph (`$`, `>>>`, …). |

All other attributes pass through to the root element.

## Slots

- **default** — revealed (with a fade transition) once the whole script finishes playing. A natural
  place for a "continue" CTA (e.g. a link into the real UI flow the terminal step was demonstrating).

## Events

- **`terminal-done`** — dispatched on the root element when the last line finishes. Listen with
  `@terminal-done` on a parent if you need to react outside the default slot.

## Examples

### Basic

```blade
<x-terminal :lines="[
    ['prompt' => '$', 'text' => 'php artisan tinker'],
    ['prompt' => '>>>', 'text' => 'User::factory()->create();'],
    ['prompt' => '=>', 'text' => 'App\\Models\\User {#1}', 'typed' => false],
    ['prompt' => '>>>', 'text' => 'exit'],
]" />
```

### With a reveal-on-done CTA

```blade
<x-terminal title="~/project/demo-app" :lines="$scriptLines">
    <x-button href="/login" color="primary">
        &rarr; See the result
    </x-button>
</x-terminal>
```

### Custom pacing

```blade
<x-terminal :lines="$scriptLines" :speed="18" :line-delay="600" color="success" />
```

## Class composition

Terminal composes classes **inline** in
[`src/resources/views/components/terminal.blade.php`](../../src/resources/views/components/terminal.blade.php)
— it predates the Composer pattern used by form components, matching `<x-alert>` / `<x-card>`.

## Related

- [`<x-card>`](./card.md) — for a static (non-animated) surface container.
- [`<x-kbd>`](./kbd.md) — inline keyboard-key display, useful inside a real (non-terminal) UI.
- [`<x-stepper>`](./stepper.md) — for a multi-step process indicator instead of a scripted CLI reveal.

## Notes

- The animation is pure Alpine (`x-data`/`x-init`/`setTimeout` chains) — no additional JS dependency,
  no opt-in `ui:install` flag required.
- This is presentational only. If the page also needs the *real* side effect the script describes
  (e.g. actually seeding a database for a demo), trigger that server-side (e.g. in the hosting
  Livewire component's `mount()`) — `<x-terminal>` never executes anything, it only animates text.
- Long lines wrap (`whitespace-pre-wrap break-all`) rather than causing horizontal scroll, so the
  window stays a fixed, predictable size regardless of script content.
