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
 *
 * Aesthetic: "Notion's quiet page". NO card and NO persistent toolbar — the
 * editor IS the page. Formatting appears in a floating toolbox on selection /
 * right-click (positioned by editor.js). Monochrome buttons with a SUBTLE active
 * wash (not a loud filled pill). Composed from plain Tailwind + daisyUI *color*
 * classes + tune tokens (--radius-box, --space-*) so the editor inherits the
 * theme × tune cascade like every other component.
 *
 * Returned keys:
 *   shell          — outer flex-col wrapper (editor + bottom bar)
 *   root           — the page surface (borderless, no card)
 *   body           — padded host that Tiptap mounts .ProseMirror into
 *   prose          — the contenteditable host class (paired with editor.css .pn-prose)
 *   menu           — floating toolbox container (fixed, content-sized, ring+shadow)
 *   menuGroup      — flex wrapper for a logical button cluster in the toolbox
 *   button         — a toolbox button in its resting state
 *   buttonActive   — extra classes layered on when the mark/node is active
 *   divider        — thin vertical rule between toolbox groups
 *   bottom         — bottom bar (shortcuts left / count right), OUTSIDE the editor
 *   count          — dual character-count cluster (半角 / 全角)
 *   footer         — optional hint/status slot row
 */
class EditorComposer
{
    public static function compose(array $props): array
    {
        $size     = $props['size'] ?? 'md';
        $disabled = $props['disabled'] ?? false;

        return [
            'shell'        => 'flex flex-col gap-1.5',
            'root'         => self::root($disabled),
            'body'         => self::body($size),
            'prose'        => self::prose($size),
            'menu'         => 'fixed z-50 flex flex-nowrap items-center gap-0.5 p-1 bg-base-100 rounded-[var(--radius-box)] ring-1 ring-base-content/10 shadow-[var(--shadow-box)]',
            'menuGroup'    => 'flex flex-nowrap items-center gap-0.5 shrink-0',
            'button'       => self::button($size),
            'buttonActive' => 'bg-base-content/10 text-base-content',
            'divider'      => 'self-stretch my-1 w-px bg-base-content/10 mx-1.5 shrink-0',
            'bottom'       => 'flex items-center justify-between gap-3 px-[var(--space-text)] text-[length:var(--text-field-xs)] text-base-content/40',
            'count'        => 'flex items-center gap-3 tabular-nums',
            'footer'       => self::footer(),
        ];
    }

    private static function root(bool $disabled): string
    {
        $disabledClass = $disabled ? 'opacity-60 pointer-events-none' : '';

        // NO card: the editor IS the page (the decisive Notion difference). No
        // border, no ring, no shadow — definition comes only from the toolbar
        // hairline and whitespace.
        return FieldVariants::join(
            'text-base-content',
            $disabledClass,
        );
    }

    private static function button(string $size): string
    {
        // Monochrome, muted resting ink; subtle wash on hover; the active state
        // (layered in Blade via `buttonActive`) is a quiet tint, never a filled
        // primary pill. NOT a daisyUI `.btn` (excluded from the build).
        $dims = match ($size) {
            'sm'    => 'min-w-7 h-7 px-1.5 text-[length:var(--text-field-xs)]',
            'lg'    => 'min-w-9 h-9 px-2.5 text-[length:var(--text-field-md)]',
            default => 'min-w-8 h-8 px-2 text-[length:var(--text-field-sm)]',
        };

        return FieldVariants::join(
            $dims,
            'shrink-0 inline-flex items-center justify-center',
            'rounded-[calc(var(--radius-box)*0.6)]',
            'text-base-content/55 hover:text-base-content hover:bg-base-content/[0.06]',
            'transition-colors duration-100 cursor-pointer select-none',
            'disabled:opacity-40 disabled:cursor-not-allowed',
            '[&_svg]:w-[1.05rem] [&_svg]:h-[1.05rem] [&_svg]:stroke-[1.75]',
        );
    }

    private static function body(string $size): string
    {
        // Generous breathing room — the writing surface should feel open.
        $pad = match ($size) {
            'sm'    => 'px-[var(--space-text)] py-[var(--space-element)]',
            'lg'    => 'px-[var(--space-section-inner)] py-[var(--space-section-inner)]',
            default => 'px-[var(--space-element)] py-[var(--space-element)]',
        };

        return FieldVariants::join($pad);
    }

    private static function prose(string $size): string
    {
        // `.pn-prose` is OUR class — its block rules + reading measure live in
        // editor.css (bundled by the preset). Here we only set the base text
        // size off the tune scale and a min editable height for empty docs.
        $text = match ($size) {
            'sm'    => 'text-[length:var(--text-field-sm)]',
            'lg'    => 'text-[length:var(--text-field-lg)]',
            default => 'text-[length:var(--text-field-md)]',
        };

        $minH = match ($size) {
            'sm'    => 'min-h-[8rem]',
            'lg'    => 'min-h-[18rem]',
            default => 'min-h-[14rem]',
        };

        return FieldVariants::join('pn-prose', $text, $minH, 'focus:outline-none');
    }

    private static function footer(): string
    {
        return FieldVariants::join(
            'flex items-center gap-2',
            'px-[var(--space-element)] py-[var(--space-compact)]',
            'text-[length:var(--text-field-xs)] text-base-content/35',
        );
    }
}
