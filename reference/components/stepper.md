# x-stepper

Multi-step process visualisation — sign-up flow, checkout, wizard. Numbered (or dotted) circles connected by a line, with each item in one of three states (`done` / `current` / `upcoming`). Items array-driven, horizontal (default) or vertical orientation.

**Playground page**: [`pinion-ui-playground/resources/views/pages/stepper.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/stepper.blade.php) — full variant matrix and live demos.

## When to use

- Multi-step forms or wizards where the user benefits from seeing total scope and current position.
- Checkout flows: cart → address → payment → confirm.
- Onboarding: account → verify → invite team → done.
- For a free-flowing log of events, prefer [`<x-timeline>`](./timeline.md) — stepper is for **bounded sequential progress**, timeline is for **append-only history**.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | `array<array{label: string, state?: 'done'\|'current'\|'upcoming', desc?: string}>` | `[]` | The steps. `label` shows under (horizontal) or right of (vertical) each circle. `state` drives circle fill / connector colour. Missing state defaults to `upcoming`. `desc` is an optional second line. |
| `orientation` | `'horizontal' \| 'vertical'` | `'horizontal'` | Layout direction. Horizontal stacks `[circle / label]` columns separated by connectors; vertical stacks `[circle | label/desc]` rows with vertical connector segments. |
| `variant` | `'numbered' \| 'dotted'` | `'numbered'` | Circle style. `numbered` (default) shows step number / done check inside a 36×36 circle. `dotted` shows tiny 12×12 dots without text — useful for compact horizontal progress (e.g. carousel position indicators). |

All other attributes pass through to the root `<ol>`.

## Slots

None — array-driven.

## Examples

### Horizontal numbered (default)

```blade
<x-stepper :items="[
    ['label' => 'Sign up', 'state' => 'done'],
    ['label' => 'Verify email', 'state' => 'current'],
    ['label' => 'Invite team', 'state' => 'upcoming'],
]" />
```

### With descriptions

```blade
<x-stepper :items="[
    ['label' => 'Cart',    'desc' => '3 items',  'state' => 'done'],
    ['label' => 'Address', 'desc' => 'Confirmed','state' => 'done'],
    ['label' => 'Payment', 'desc' => 'Visa ····','state' => 'current'],
    ['label' => 'Confirm', 'desc' => 'Review',   'state' => 'upcoming'],
]" />
```

### Vertical

```blade
<x-stepper orientation="vertical" :items="[
    ['label' => '要件定義', 'desc' => 'クライアントヒアリング完了', 'state' => 'done'],
    ['label' => '設計',     'desc' => 'API・データモデル確定',     'state' => 'done'],
    ['label' => '実装',     'desc' => 'コア機能の開発',           'state' => 'current'],
    ['label' => 'リリース', 'desc' => '本番デプロイ',             'state' => 'upcoming'],
]" />
```

### Dotted (compact)

```blade
<x-stepper variant="dotted" :items="[
    ['state' => 'done'],
    ['state' => 'done'],
    ['state' => 'current'],
    ['state' => 'upcoming'],
    ['state' => 'upcoming'],
]" />
```

### Vertical + dotted

```blade
<x-stepper orientation="vertical" variant="dotted" :items="[
    ['label' => 'Step 1', 'state' => 'done'],
    ['label' => 'Step 2', 'state' => 'current'],
    ['label' => 'Step 3', 'state' => 'upcoming'],
]" />
```

## Class composition

See [`src/Compose/StepperComposer.php`](../../src/Compose/StepperComposer.php). Returns `root`, `item`, `circle`, `connector`, `label`, `desc`, `stateColors`, `stateConnectors`, `orientation`, `variant`. Pipe-joined `stateColors` / `stateConnectors` maps are consumed via the helper `StepperComposer::pick($map, $state)` (same pattern as `<x-timeline>`).

State → circle colours:
- **done**: `bg-primary border-primary text-primary-content` + checkmark icon
- **current**: `bg-base-100 border-primary text-primary` + number
- **upcoming**: `bg-base-100 border-base-content/20 text-base-content/40` + number

State of an item drives the colour of the connector **after** it: a `done` step gets a primary-filled connector to the next; `current` / `upcoming` get the muted `bg-base-content/20`.

## Related

- [`<x-timeline>`](./timeline.md) — append-only history; use when steps aren't predetermined.
- [`<x-progress>`](./progress.md) — single-value linear progress; use when count is too high to bother with discrete steps.
- [`<x-tabs>`](./tabs.md) — for content panels per step (combine the two: stepper as the visual indicator, tabs to show the active step's content).

## Notes

- Connectors take whatever space remains between circles (`flex-1` horizontally, fixed `h-6` vertically). For tightly-packed steppers in narrow containers, switch to `variant="dotted"` so circles take less width.
- `desc` is optional; without it the label sits closer to the circle.
- The check icon for done steps is hardcoded. To use a custom icon per step, fork the blade view — no `icon` field exists on the item array (yet).
- Horizontal stepper assumes English-style left-to-right reading order. For RTL layouts the connector flow direction follows the document's writing mode.
