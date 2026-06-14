@props([
    'size' => 'md',
    'placeholder' => null,
    'sync' => 'debounce:800',
    'disabled' => false,
    'editable' => true,
    'toolbar' => true,
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

    // Toolbar button spec — pure data, rendered server-side. Each entry maps a
    // label/icon to a Tiptap chain command and an isActive(name, attrs) check.
    // Defined here (not in JS) to keep the JS module free of presentation.
    $tb = [
        ['t' => 'group', 'items' => [
            ['label' => 'H1', 'run' => "c => c.toggleHeading({level:1})", 'name' => 'heading', 'attrs' => '{level:1}'],
            ['label' => 'H2', 'run' => "c => c.toggleHeading({level:2})", 'name' => 'heading', 'attrs' => '{level:2}'],
            ['label' => 'H3', 'run' => "c => c.toggleHeading({level:3})", 'name' => 'heading', 'attrs' => '{level:3}'],
        ]],
        ['t' => 'group', 'items' => [
            ['label' => 'B',  'run' => "c => c.toggleBold()",      'name' => 'bold'],
            ['label' => 'I',  'run' => "c => c.toggleItalic()",    'name' => 'italic'],
            ['label' => '&lt;/&gt;', 'run' => "c => c.toggleCode()", 'name' => 'code'],
            ['label' => 'HL', 'run' => "c => c.toggleMark('pnHighlight')", 'name' => 'pnHighlight'],
        ]],
        ['t' => 'group', 'items' => [
            ['label' => '•',  'run' => "c => c.toggleBulletList()",  'name' => 'bulletList'],
            ['label' => '1.', 'run' => "c => c.toggleOrderedList()", 'name' => 'orderedList'],
            ['label' => '☑',  'run' => "c => c.toggleTaskList()",    'name' => 'taskList'],
        ]],
        ['t' => 'group', 'items' => [
            ['label' => '“',  'run' => "c => c.toggleBlockquote()", 'name' => 'blockquote'],
            ['label' => '{ }', 'run' => "c => c.toggleCodeBlock()", 'name' => 'codeBlock'],
        ]],
        ['t' => 'link'],
    ];
@endphp

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class([$c['root']]) }}
    x-data="pinionEditor({{ \Illuminate\Support\Js::from($config) }})"
    x-on:destroy="destroy()"
>
    @if($toolbar && $isEditable)
        <div class="{{ $c['toolbar'] }}" role="toolbar" aria-label="Editor toolbar">
            @foreach($tb as $section)
                @if($section['t'] === 'group')
                    <div class="{{ $c['toolbarGroup'] }}">
                        @foreach($section['items'] as $b)
                            <button
                                type="button"
                                class="{{ $c['button'] }}"
                                x-bind:class="isActive('{{ $b['name'] }}'{{ isset($b['attrs']) ? ', '.$b['attrs'] : '' }}) ? '{{ $c['buttonActive'] }}' : ''"
                                x-on:click="cmd({{ $b['run'] }})"
                                aria-label="{{ strip_tags($b['label']) }}"
                            >{!! $b['label'] !!}</button>
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
                        aria-label="Link"
                    >🔗</button>
                @endif
            @endforeach

            <span class="{{ $c['counter'] }}" x-text="chars + ' chars'"></span>
        </div>
    @endif

    {{-- Editable host. Tiptap mounts .ProseMirror here. The prose class is read
         from data-prose-class by the JS so a bare (no-Blade) mount still styles. --}}
    <div class="{{ $c['body'] }}">
        <div x-ref="editor" data-prose-class="{{ $c['prose'] }}"></div>
    </div>

    {{-- Optional footer slot for shortcut hints / status. --}}
    @isset($footer)
        <div class="{{ $c['footer'] }}">{{ $footer }}</div>
    @endisset

    {{-- wire:model carrier. The JS writes the envelope JSON string here and
         dispatches `input` so Livewire is notified per the chosen sync cadence.
         For non-Livewire forms, pass a plain `name` via attributes is NOT
         supported here (the body is JSON, not a scalar form field) — use
         wire:model or read the value via the Alpine scope. --}}
    @if($hasWireModel)
        <input type="hidden" x-ref="model" {{ $wireModel }} />
    @else
        <input type="hidden" x-ref="model" />
    @endif
</div>
