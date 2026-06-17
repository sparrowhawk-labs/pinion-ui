@props([
    'columns' => [],
    'rows' => [],
    'size' => 'md',
    'height' => null,
    'layout' => 'fitColumns',
    'editable' => true,
    'selectableRange' => true,
    'sortable' => true,
    'rowNumbers' => true,
    'movableRows' => false,
    'movableColumns' => false,
    'toolbar' => true,
    'sync' => 'debounce:400',
    'addRowLabel' => '＋ 行を追加',
])

@php
    use SparrowhawkLabs\PinionUi\Compose\DataGridComposer;

    $c = DataGridComposer::compose(['size' => $size]);

    // wire:model goes on a dedicated hidden <input> (mirrors <x-editor> / pin-input).
    // Detected with whereStartsWith('wire:model') — NOT $attributes->wire('model') —
    // so the component works in apps without Livewire installed (see AGENTS.md).
    $wireModel    = $attributes->whereStartsWith('wire:model');
    $hasWireModel = $wireModel->isNotEmpty();

    // x-data config, JSON-encoded. The JS module clones these out of Alpine's
    // proxy before handing them to Tabulator (which mutates them internally).
    $config = [
        'columns'         => array_values($columns),
        'rows'            => array_values($rows),
        'height'          => $height,
        'layout'          => $layout,
        'editable'        => (bool) $editable,
        'selectableRange' => (bool) $selectableRange,
        'sortable'        => (bool) $sortable,
        'rowNumbers'      => (bool) $rowNumbers,
        'movableRows'     => (bool) $movableRows,
        'movableColumns'  => (bool) $movableColumns,
        'sync'            => $sync,
    ];
@endphp

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class([$c['shell']]) }}
    x-data="pinionDataGrid({{ \Illuminate\Support\Js::from($config) }})"
    x-on:destroy="destroy()"
>
    {{-- Optional toolbar: built-in add-row (generic) + row count + an actions slot
         the host fills (e.g. add-column). Livewire hosts that own add/remove
         server-side typically pass :toolbar="false" and render their own chrome. --}}
    @if($toolbar)
        <div class="{{ $c['toolbar'] }}">
            @if($editable)
                <button type="button" class="{{ $c['toolbarBtn'] }}" x-on:click="addRow()">{{ $addRowLabel }}</button>
            @endif
            <span class="{{ $c['count'] }}"><span x-text="rowCount"></span> 行</span>
            @isset($actions)
                <span class="ml-auto flex items-center gap-2">{{ $actions }}</span>
            @endisset
        </div>
    @endif

    {{-- Tabulator host. wire:ignore keeps Livewire's morphdom from reconciling the
         grid's generated DOM against this empty div on every server render. A
         Livewire host re-seeds by bumping this component's :key (key change forces
         a fresh mount, which wire:ignore does not block). --}}
    <div x-ref="grid" wire:ignore class="{{ $c['grid'] }}"></div>

    {{-- wire:model carrier. data-grid.js writes the row-array JSON here and
         dispatches `input` so Livewire is notified per the sync cadence. --}}
    @if($hasWireModel)
        <input type="hidden" x-ref="model" {{ $wireModel }} />
    @else
        <input type="hidden" x-ref="model" />
    @endif
</div>
