# x-radio-group

Wrapper that renders a set of `<x-radio>` sharing one `name`, a group label / description, and an error message. Pass options as an `[value => label]` array, or drop `<x-radio>` children into the default slot.

**Playground page**: [`pinion-ui-playground/resources/views/pages/radio.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/radio.blade.php) — `<x-radio-group>` examples sit alongside `<x-radio>`.

## When to use

- One mutually-exclusive choice with a label / hint / error — the common form-field case.
- When the options are data-driven (`[value => label]` map) — pass `:options="$choices"` and you're done.
- For free-form layout (radios scattered through arbitrary markup), use bare [`<x-radio>`](./radio.md) instead.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string \| null` | `null` | Form field name, propagated to every child `<x-radio>`. |
| `label` | `string \| null` | `null` | Group label rendered as `<legend>`. |
| `description` | `string \| null` | `null` | Secondary text below the legend (above the radios). |
| `value` | `mixed` | `null` | Currently-selected value. Compared as string against each option's value to set `checked`. |
| `options` | `array<string\|int, string> \| null` | `null` | Map of `value => label`. When set, the group renders one `<x-radio>` per entry and the default slot is ignored. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | Forwarded to every child `<x-radio>`. |
| `appearance` | `'solid' \| 'soft' \| 'base-100' \| 'base-200' \| 'base-300'` | `'solid'` | Forwarded to every child `<x-radio>`. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Forwarded to every child `<x-radio>`. |
| `error` | `string \| null` | `null` | Error message below the group. Also flips legend + radios to the `error` color. |
| `hint` | `string \| null` | `null` | Helper text below the group. Hidden while `error` is set. |
| `required` | `bool` | `false` | Adds a red `*` next to the legend. (Set `required` on a single child `<x-radio>` for native validation.) |
| `disabled` | `bool` | `false` | Forwarded to every child `<x-radio>`. |
| `orientation` | `'vertical' \| 'horizontal'` | `'vertical'` | Layout: stacked column (default) or wrap-friendly row. |

All other attributes pass through to the wrapping `<fieldset>`.

## Slots

- **default** — `<x-radio>` children. Ignored when `:options` is provided.

## Examples

### Options array

```blade
<x-radio-group
    name="plan"
    label="Plan"
    :options="['free' => 'Free', 'pro' => 'Pro', 'team' => 'Team']"
    value="pro"
/>
```

### Slot-driven (per-radio customization)

```blade
<x-radio-group name="role" label="Role" orientation="horizontal">
    <x-radio name="role" value="admin" label="Admin" />
    <x-radio name="role" value="user"  label="User" description="Default" />
    <x-radio name="role" value="guest" label="Guest" disabled />
</x-radio-group>
```

### Error state

```blade
<x-radio-group
    name="payment"
    label="Payment method"
    :options="['card' => 'Card', 'bank' => 'Bank transfer']"
    :error="$errors->first('payment')"
/>
```

## Class composition

`<x-radio-group>` does **not** have its own composer — it composes layout classes inline (`flex flex-wrap gap-x-6 gap-y-2` or `flex flex-col gap-2`) and delegates the per-radio visuals to [`src/Compose/RadioComposer.php`](../../src/Compose/RadioComposer.php) through child `<x-radio>` components.

## Related

- [`<x-radio>`](./radio.md) — the underlying single-radio component.
- [`<x-checkbox>`](./checkbox.md) — for non-exclusive selection (no built-in group wrapper; assemble manually).
- [`<x-select>`](./select.md) — picker for longer option lists.

## Notes

- The `<fieldset>` carries `role="radiogroup"` (via the inner div) and `aria-labelledby` pointing at the legend — keep the `label` set for accessibility.
- `value` matching is string-cast on both sides (`(string) $value === (string) $optValue`) so numeric / string option keys behave the same.
- `required` on the group renders the visual `*` but does **not** apply native `required` to children — set it on the specific child radio if you need browser validation.
