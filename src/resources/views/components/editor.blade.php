@props([
    'size' => 'md',
    'placeholder' => null,
    'sync' => 'debounce:800',
    'disabled' => false,
    'editable' => true,
    'shortcuts' => true,
    'content' => null,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\EditorComposer;

    $c = EditorComposer::compose([
        'size'     => $size,
        'disabled' => $disabled,
    ]);

    // wire:model goes on a dedicated hidden <input>. We forward only the
    // wire:model* bag onto it (mirrors pin-input). Detected with
    // whereStartsWith('wire:model') — NOT $attributes->wire('model'), so the
    // component works in apps without Livewire installed (see AGENTS.md).
    $wireModel    = $attributes->whereStartsWith('wire:model');
    $hasWireModel = $wireModel->isNotEmpty();

    $isEditable = $editable && ! $disabled;

    // x-data config, JSON-encoded. `content` may be an envelope, a bare
    // ProseMirror doc, or null — the JS module's unwrap() accepts all three.
    $config = [
        'placeholder' => $placeholder,
        'sync'        => $sync,
        'editable'    => $isEditable,
        'content'     => $content,
    ];

    // Lucide-style 16px line icons (stroke=currentColor; sized via the Composer
    // button's [&_svg] rule). H1–H3 read clearer as text than as glyphs.
    $svg = fn (string $body) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$body.'</svg>';
    $icon = [
        'bold'      => $svg('<path d="M6 4h8a4 4 0 0 1 0 8H6z"/><path d="M6 12h9a4 4 0 0 1 0 8H6z"/>'),
        'italic'    => $svg('<line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/>'),
        'code'      => $svg('<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>'),
        'highlight' => $svg('<path d="m9 11-6 6v3h9l3-3"/><path d="m22 12-4.6 4.6a2 2 0 0 1-2.8 0l-5.2-5.2a2 2 0 0 1 0-2.8L14 4"/>'),
        'bullet'    => $svg('<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>'),
        'ordered'   => $svg('<line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-1.5 2-2.5S5 14 4 14.5"/>'),
        'task'      => $svg('<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'),
        'quote'     => $svg('<path d="M17 6H3"/><path d="M21 12H8"/><path d="M21 18H8"/><path d="M3 12v6"/>'),
        'codeblock' => $svg('<path d="M8 3H7a2 2 0 0 0-2 2v5a2 2 0 0 1-2 2 2 2 0 0 1 2 2v5c0 1.1.9 2 2 2h1"/><path d="M16 3h1a2 2 0 0 1 2 2v5a2 2 0 0 0 2 2 2 2 0 0 0-2 2v5a2 2 0 0 1-2 2h-1"/>'),
        'link'      => $svg('<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>'),
        'keyboard'  => $svg('<rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M7 14h10"/>'),
    ];

    // Floating-toolbox button spec — pure data, rendered server-side.
    $tb = [
        ['t' => 'group', 'items' => [
            ['html' => 'H1', 'aria' => 'Heading 1', 'run' => "c => c.toggleHeading({level:1})", 'name' => 'heading', 'attrs' => '{level:1}'],
            ['html' => 'H2', 'aria' => 'Heading 2', 'run' => "c => c.toggleHeading({level:2})", 'name' => 'heading', 'attrs' => '{level:2}'],
            ['html' => 'H3', 'aria' => 'Heading 3', 'run' => "c => c.toggleHeading({level:3})", 'name' => 'heading', 'attrs' => '{level:3}'],
        ]],
        ['t' => 'group', 'items' => [
            ['html' => $icon['bold'],      'aria' => 'Bold',        'run' => "c => c.toggleBold()",   'name' => 'bold'],
            ['html' => $icon['italic'],    'aria' => 'Italic',      'run' => "c => c.toggleItalic()", 'name' => 'italic'],
            ['html' => $icon['code'],      'aria' => 'Inline code', 'run' => "c => c.toggleCode()",    'name' => 'code'],
            ['html' => $icon['highlight'], 'aria' => 'Highlight',   'run' => "c => c.toggleMark('pnHighlight')", 'name' => 'pnHighlight'],
        ]],
        ['t' => 'group', 'items' => [
            ['html' => $icon['bullet'],  'aria' => 'Bullet list',  'run' => "c => c.toggleBulletList()",  'name' => 'bulletList'],
            ['html' => $icon['ordered'], 'aria' => 'Numbered list', 'run' => "c => c.toggleOrderedList()", 'name' => 'orderedList'],
            ['html' => $icon['task'],    'aria' => 'Task list',    'run' => "c => c.toggleTaskList()",    'name' => 'taskList'],
        ]],
        ['t' => 'group', 'items' => [
            ['html' => $icon['quote'],     'aria' => 'Quote',      'run' => "c => c.toggleBlockquote()", 'name' => 'blockquote'],
            ['html' => $icon['codeblock'], 'aria' => 'Code block', 'run' => "c => c.toggleCodeBlock()",  'name' => 'codeBlock'],
        ]],
        ['t' => 'link', 'html' => $icon['link'], 'aria' => 'Link'],
    ];

    $scGroups = [
        ['Text', [['Bold', '⌘B'], ['Italic', '⌘I'], ['Inline code', '⌘E'], ['Highlight', '⌘⇧H']]],
        ['Headings', [['Heading 1', '⌘⌥1'], ['Heading 2', '⌘⌥2'], ['Heading 3', '⌘⌥3']]],
        ['Lists', [['Bullet list', '⌘⇧8'], ['Numbered list', '⌘⇧7'], ['Task list', '⌘⇧9']]],
        ['Blocks', [['Quote', '⌘⇧B'], ['Code block', '⌘⌥C'], ['Divider', '⌘↵']]],
        ['History', [['Undo', '⌘Z'], ['Redo', '⌘⇧Z']]],
        ['Magic', [['Highlight text', '==text=='], ['New line', '⇧↵']]],
    ];
@endphp

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class([$c['shell']]) }}
    x-data="pinionEditor({{ \Illuminate\Support\Js::from($config) }})"
    x-on:destroy="destroy()"
>
    {{-- Editor: NO card, NO persistent toolbar — it is the page. --}}
    <div class="{{ $c['root'] }}">
        <div class="{{ $c['body'] }}">
            <div x-ref="editor" data-prose-class="{{ $c['prose'] }}"></div>
        </div>
    </div>

    {{-- Floating toolbox — shown on selection / right-click by editor.js,
         positioned via menu.top/left. mousedown.prevent keeps the selection. --}}
    @if($isEditable)
        <div
            x-ref="menu"
            x-show="menu.open"
            x-cloak
            x-transition.opacity.duration.100ms
            x-on:mousedown.prevent
            x-bind:style="`top:${menu.top}px; left:${menu.left}px`"
            class="{{ $c['menu'] }}"
            role="toolbar"
            aria-label="Formatting"
        >
            @foreach($tb as $section)
                @if($section['t'] === 'group')
                    <div class="{{ $c['menuGroup'] }}">
                        @foreach($section['items'] as $b)
                            <button
                                type="button"
                                class="{{ $c['button'] }}"
                                x-bind:class="isActive('{{ $b['name'] }}'{{ isset($b['attrs']) ? ', '.$b['attrs'] : '' }}) ? '{{ $c['buttonActive'] }}' : ''"
                                x-on:click="cmd({{ $b['run'] }})"
                                aria-label="{{ $b['aria'] }}"
                                title="{{ $b['aria'] }}"
                            >{!! $b['html'] !!}</button>
                        @endforeach
                    </div>
                    @if(! $loop->last)
                        <span class="{{ $c['divider'] }}" aria-hidden="true"></span>
                    @endif
                @elseif($section['t'] === 'link')
                    <button
                        type="button"
                        class="{{ $c['button'] }}"
                        x-bind:class="isActive('link') ? '{{ $c['buttonActive'] }}' : ''"
                        x-on:click="setLink()"
                        aria-label="{{ $section['aria'] }}"
                        title="{{ $section['aria'] }}"
                    >{!! $section['html'] !!}</button>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Bottom bar: shortcuts (left) + dual character count (right). --}}
    <div class="{{ $c['bottom'] }}">
        @if($shortcuts && $isEditable)
            <div class="relative" x-data="{ sc: false }">
                <button
                    type="button"
                    x-on:click="sc = !sc"
                    x-bind:class="sc ? '{{ $c['buttonActive'] }}' : 'text-base-content/45'"
                    class="inline-flex items-center gap-1.5 h-7 px-2 rounded-[calc(var(--radius-box)*0.6)] hover:text-base-content hover:bg-base-content/[0.06] transition-colors cursor-pointer select-none [&_svg]:w-[0.95rem] [&_svg]:h-[0.95rem] [&_svg]:stroke-[1.75]"
                    aria-label="Keyboard shortcuts"
                    title="Keyboard shortcuts"
                >{!! $icon['keyboard'] !!}<span>ショートカット</span></button>
                <div
                    x-show="sc"
                    x-cloak
                    x-on:click.outside="sc = false"
                    x-transition.opacity.duration.150ms
                    class="absolute bottom-full left-0 mb-2 z-30 w-[19rem] bg-base-100 rounded-[var(--radius-box)] ring-1 ring-base-content/10 shadow-lg overflow-hidden"
                >
                    <div class="px-3 py-2 text-[length:var(--text-field-xs)] font-medium text-base-content/55 border-b border-base-content/[0.07]">Keyboard shortcuts</div>
                    <div class="p-1.5 max-h-[20rem] overflow-auto">
                        @foreach($scGroups as [$group, $rows])
                            <div class="px-2 pt-2 pb-1 text-[length:var(--text-field-xs)] font-medium uppercase tracking-wide text-base-content/35">{{ $group }}</div>
                            @foreach($rows as [$label, $key])
                                <div class="flex items-center justify-between gap-4 px-2 py-1 rounded-[calc(var(--radius-box)*0.5)] hover:bg-base-content/[0.04]">
                                    <span class="text-[length:var(--text-field-sm)] text-base-content/75">{{ $label }}</span>
                                    <kbd class="font-mono text-[length:var(--text-field-xs)] text-base-content/55 bg-base-200 px-1.5 py-0.5 rounded-[calc(var(--radius-box)*0.4)] border border-base-content/10 whitespace-nowrap">{{ $key }}</kbd>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <span></span>
        @endif

        <div class="{{ $c['count'] }}">
            <span>半角 <span x-text="charsHalf"></span></span>
            <span aria-hidden="true" class="text-base-content/25">·</span>
            <span>全角 <span x-text="charsFull"></span></span>
        </div>
    </div>

    {{-- Optional footer slot for status / hints. --}}
    @isset($footer)
        <div class="{{ $c['footer'] }}">{{ $footer }}</div>
    @endisset

    {{-- wire:model carrier. editor.js writes the envelope JSON string here and
         dispatches `input` so Livewire is notified per the sync cadence. --}}
    @if($hasWireModel)
        <input type="hidden" x-ref="model" {{ $wireModel }} />
    @else
        <input type="hidden" x-ref="model" />
    @endif
</div>
