# x-settings-switcher

**Theme × tune × lang consolidated into ONE trigger + panel** — for tight chrome (mobile navbars) where the separate [`<x-theme-tune-switcher>`](./theme-tune-switcher.md) + [`<x-lang-switcher>`](./lang-switcher.md) pair would wrap onto extra rows. The trigger is a compact sliders-glyph button with a live color-dots chip previewing the active theme; the panel stacks a Theme section (grouped v0.7.0 lineup + sun/moon light/dark toggle), a Tune section, and an optional Lang section. Pure inline Alpine; no `ui:install`.

Theme semantics mirror `<x-theme-tune-switcher>`: grouped lineup from `pn_theme_groups()` (Brand / Mood / SaaS / Industry with colored group dots), light/dark **pairs** flipped by the mode toggle, same localStorage keys — share `storage-key` when both are on the page (the usual pattern: desktop switchers `hidden lg:flex`, this behind `lg:hidden`; both then read/write the same persisted choice).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `locales` | `array<array{code?:string,label?:string,href?:string,active?:bool}>` | `[]` | Lang section entries — same shape as `<x-lang-switcher>`. Empty array hides the Lang section entirely. Rendered as server-side `<a href>` links (crawlable). |
| `current` | `string\|null` | `null` | The active locale's `code`. |
| `drop` | `'down' \| 'up'` | `'down'` | Panel direction. Use `'up'` when the trigger sits at the bottom of the screen. |
| `width` | Tailwind width class | `'w-72'` | Panel width. |
| `attribution` | `bool` | `true` | Show the pinion-ui attribution link, pinned faint/small at the panel's **top-right** (outside the scrollable body, so it stays visible while the sections scroll). Opt out with `:attribution="false"`. |
| `link` | `'github' \| 'site' \| URL` | `'github'` | Attribution link target: `'github'` = the pinion-ui repo (default), `'site'` = pinion-ui.dev, or any URL string. |
| `storage` | `bool` | `true` | Persist theme/tune to `localStorage`. |
| `storageKey` | `string` | `'pn'` | localStorage key prefix (`{key}-theme` / `{key}-tune`). |
| `themes` | `array \| null` | grouped lineup | Override with a FLAT list of literal shipped theme ids. Disables grouping and the light/dark mode toggle. |
| `tunes` | `array \| null` | all tunes | Override the tune list. |

## Behavior

On select it sets `document.documentElement.dataset.theme` / `.tune` (and persists if `storage`) — the panel stays open so several axes can be adjusted in one visit; click-outside or ESC closes it. Locale rows are plain links (navigation closes the page anyway). The panel is `absolute` and right-aligned to the trigger; the panel body scrolls (`max-h-[70vh]`) while the attribution link stays pinned.

## Example

```blade
{{-- responsive navbar: separate switchers on desktop, consolidated panel on mobile --}}
<nav class="flex items-center gap-4 px-6 py-3">
    <a href="/">Brand</a>

    <x-settings-switcher class="lg:hidden ml-auto" storage-key="pinion"
        :current="$locale" :locales="$localeLinks" />

    <div class="hidden lg:flex items-center gap-3 lg:ml-auto">
        <x-theme-tune-switcher position="inline" storage-key="pinion" />
        <x-lang-switcher :current="$locale" :locales="$localeLinks" />
    </div>
</nav>
```

## Class composition

Fully utility-composed (no Composer), matching the switcher family: chip trigger with `rounded-[var(--radius-field)]` + `tune-border`, panel with `rounded-[var(--radius-box)]` + `shadow-[var(--shadow-box)]`, sections divided by `divide-base-200`, rows as semantic-color buttons/links. Never daisyUI component classes.

## Related

- [`<x-theme-tune-switcher>`](./theme-tune-switcher.md) — the desktop-oriented separate theme/tune dropdowns this consolidates.
- [`<x-lang-switcher>`](./lang-switcher.md) — standalone locale picker in the same control family.
