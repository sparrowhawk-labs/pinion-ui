<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * EditorComposer — class strings for the <x-editor> rich-text component.
 *
 * INVARIANT (load-bearing, see CLAUDE.md §Architecture invariants 1): this is a
 * PURE function. `compose($props)` returns a FLAT dict of CSS class strings only
 * — no markup, no JS, no array values, no side effects. The editor's *behavior*
 * lives entirely in the opt-in JS module (src/resources/js/editor.js); the prose
 * rules that can't be utilities live in the bundled stylesheet (editor.css).
 * Everything here is just the chrome around the editable surface, composed from
 * plain Tailwind + daisyUI *color* classes + tune tokens (--radius-box, --space-*)
 * so the editor inherits the theme × tune cascade like every other component.
 *
 * Returned keys:
 *   root           — outer card (border + radius + surface)
 *   toolbar        — toolbar surface row (border-b, padded)
 *   toolbarGroup   — flex wrapper for a logical button cluster
 *   button         — a toolbar button in its resting state
 *   buttonActive   — extra classes layered on when the mark/node is active
 *   divider        — thin vertical rule between toolbar groups
 *   counter        — char-count text pushed to the toolbar's far end
 *   body           — padded host that Tiptap mounts .ProseMirror into
 *   prose          — the contenteditable host class (paired with editor.css .pn-prose)
 *   footer         — optional hint/status row under the body
 */
class EditorComposer
{
    public static function compose(array $props): array
    {
        $size     = $props['size'] ?? 'md';
        $disabled = $props['disabled'] ?? false;

        return [
            'root'         => self::root($disabled),
            'toolbar'      => self::toolbar(),
            'toolbarGroup' => 'flex flex-wrap items-center gap-0.5',
            'button'       => self::button($size),
            'buttonActive' => 'bg-primary text-primary-content',
            'divider'      => 'self-stretch w-px bg-base-content/10 mx-1',
            'counter'      => 'ml-auto text-[length:var(--text-field-xs)] text-base-content/40 pr-1',
            'body'         => self::body($size),
            'prose'        => self::prose($size),
            'footer'       => self::footer(),
        ];
    }

    private static function root(bool $disabled): string
    {
        $disabledClass = $disabled ? 'opacity-60 pointer-events-none' : '';

        return FieldVariants::join(
            'bg-base-100 text-base-content',
            'border-[length:var(--border)] border-base-300',
            'rounded-[var(--radius-box)] overflow-hidden',
            'focus-within:border-base-content/40 transition-colors',
            $disabledClass,
        );
    }

    private static function toolbar(): string
    {
        return FieldVariants::join(
            'flex flex-wrap items-center gap-1',
            'p-[var(--space-compact)]',
            'border-b border-base-content/10 bg-base-200/50',
        );
    }

    private static function button(string $size): string
    {
        // Square-ish tappable target whose corner radius follows the tune box
        // radius (so tune=brutal squares the toolbar like the rest of the page).
        // Resting state is muted ink; the active state is layered on in Blade
        // via `buttonActive`. NOT a daisyUI `.btn` (excluded from the build) —
        // a pinion-ui surface, per the spike.
        $dims = match ($size) {
            'sm'    => 'min-w-7 h-7 px-1.5 text-[length:var(--text-field-xs)]',
            'lg'    => 'min-w-9 h-9 px-2.5 text-[length:var(--text-field-md)]',
            default => 'min-w-8 h-8 px-2 text-[length:var(--text-field-sm)]',
        };

        return FieldVariants::join(
            $dims,
            'inline-flex items-center justify-center',
            'rounded-[calc(var(--radius-box)*0.6)]',
            'text-base-content/80 hover:bg-base-content/10',
            'transition-colors cursor-pointer select-none',
            'disabled:opacity-40 disabled:cursor-not-allowed',
        );
    }

    private static function body(string $size): string
    {
        $pad = match ($size) {
            'sm'    => 'p-[var(--space-compact)]',
            'lg'    => 'p-[var(--space-element)]',
            default => 'p-[var(--space-text)]',
        };

        return FieldVariants::join($pad);
    }

    private static function prose(string $size): string
    {
        // `.pn-prose` is OUR class — its block rules live in editor.css (bundled
        // by the preset). Here we only set the base text size off the tune scale
        // and the min editable height so an empty editor still has a click target.
        $text = match ($size) {
            'sm'    => 'text-[length:var(--text-field-sm)]',
            'lg'    => 'text-[length:var(--text-field-lg)]',
            default => 'text-[length:var(--text-field-md)]',
        };

        $minH = match ($size) {
            'sm'    => 'min-h-[8rem]',
            'lg'    => 'min-h-[16rem]',
            default => 'min-h-[12rem]',
        };

        return FieldVariants::join('pn-prose', $text, $minH, 'focus:outline-none');
    }

    private static function footer(): string
    {
        return FieldVariants::join(
            'flex items-center gap-2',
            'px-[var(--space-text)] py-[var(--space-compact)]',
            'border-t border-base-content/10',
            'text-[length:var(--text-field-xs)] text-base-content/40',
        );
    }
}
