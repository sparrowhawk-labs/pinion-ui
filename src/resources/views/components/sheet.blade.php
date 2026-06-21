@props([
    'columns' => [],
    'rows' => [],
    'size' => 'md',
    'height' => null,
    'editable' => true,
    'selectableRange' => true,
    'sortable' => true,
    'rowNumbers' => true,
    'movableRows' => false,
    'movableColumns' => false,
    'resizableColumns' => true,
    'contextMenu' => true,
    'toolbar' => true,
    'sync' => 'debounce:400',
    'addRow' => true,
    'addColumn' => true,
    'addRowLabel' => '行を追加',
    'addColumnLabel' => '列を追加',
])

@php
    use SparrowhawkLabs\PinionUi\Compose\SheetComposer;
    use SparrowhawkLabs\PinionUi\Compose\SelectComposer;

    // <x-sheet> — the Locality-of-Behavior spreadsheet (no Tabulator). S1: the
    // `pinionSheet` Alpine factory owns reactive rows/cols; the table is a declarative
    // x-for, edits round-trip through the hidden wire:model carrier as a JSON string.
    // The grid host carries wire:ignore so Livewire's morphdom never reconciles Alpine's
    // x-for DOM; a Livewire host re-seeds by bumping its :key. See sheet.js + sheet.md.
    $c = SheetComposer::compose(['size' => $size]);
    // reuse the pinion <x-select> custom-mode look for the select-cell dropdown.
    $sel = SelectComposer::compose(['size' => $size, 'custom' => true]);

    // Inline-SVG icons (Lucide/Feather style — same closure pattern as <x-editor>).
    $svg = fn (string $body) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
    $icon = [
        'rowPlus' => $svg('<path d="M4 6h16"/><path d="M4 11h16"/><path d="M4 16h7"/><path d="M16 16h5"/><path d="M18.5 13.5v5"/>'),
        'colPlus' => $svg('<path d="M6 4v16"/><path d="M11 4v16"/><path d="M16 4v7"/><path d="M16 16h5"/><path d="M18.5 13.5v5"/>'),
        'help'    => $svg('<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.8.4-1.5 1-1.5 2"/><path d="M12 17h.01"/>'),
        'close'   => $svg('<path d="M18 6 6 18"/><path d="m6 6 12 12"/>'),
    ];

    // wire:model → a dedicated hidden <input> (mirrors <x-data-grid> / <x-editor>);
    // detected via whereStartsWith so the component works without Livewire installed.
    $wireModel    = $attributes->whereStartsWith('wire:model');
    $hasWireModel = $wireModel->isNotEmpty();

    // x-data config — the JS factory deep-clones these out of Alpine's proxy at init.
    $config = [
        'columns'  => array_values($columns),
        'rows'     => array_values($rows),
        'editable' => (bool) $editable,
        'sortable' => (bool) $sortable,
        'movableRows' => (bool) $movableRows,
        'movableColumns' => (bool) $movableColumns,
        'sync'     => $sync,
    ];
@endphp

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class([$c['shell']]) }}
    x-data="pinionSheet({{ \Illuminate\Support\Js::from($config) }})"
    x-on:destroy="destroy()"
    @if($movableRows || $movableColumns)
    x-on:dragover.window="onWinDragOver($event)"
    x-on:drop.window="onWinDrop($event)"
    @endif
>
    {{-- Toolbox of icon-only ops (add-row / add-col, minimal) + row count. The add
         buttons mutate the local reactive buffer (standalone convenience); Livewire
         hosts own structure and pass :toolbar="false". On a phone a "?" opens a guide
         modal naming each icon-only op (desktop relies on the title tooltips). --}}
    @if($toolbar)
        <div class="{{ $c['toolbar'] }}">
            <div class="{{ $c['toolbox'] }}" role="toolbar" aria-label="表の操作">
                @if($editable && $addRow)
                    <button type="button" class="{{ $c['iconBtn'] }}" x-on:click="addRow()" aria-label="{{ $addRowLabel }}" title="{{ $addRowLabel }}">{!! $icon['rowPlus'] !!}</button>
                @endif
                @if($editable && $addColumn)
                    <button type="button" class="{{ $c['iconBtn'] }}" x-on:click="addColumn()" aria-label="{{ $addColumnLabel }}" title="{{ $addColumnLabel }}">{!! $icon['colPlus'] !!}</button>
                @endif
                @isset($actions){{ $actions }}@endisset
            </div>

            <span class="{{ $c['count'] }}"><span x-text="rowCount"></span> 行</span>

            @if($editable && ($addRow || $addColumn))
                <div class="relative sm:hidden ml-auto" x-data="{ help: false }" x-on:keydown.escape.window="help = false">
                    <button type="button" class="{{ $c['iconBtn'] }} text-base-content/45" x-on:click="help = true" aria-label="操作の説明" title="操作の説明">{!! $icon['help'] !!}</button>

                    <div x-show="help" x-cloak x-transition.opacity.duration.150ms x-on:click="help = false" class="fixed inset-0 z-40 flex items-end justify-center bg-base-content/30 p-4">
                        <div x-on:click.stop role="dialog" aria-modal="true" aria-label="表の操作の説明" class="w-full max-w-sm bg-base-100 rounded-[var(--radius-box)] ring-1 ring-base-content/10 shadow-lg p-4 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-base-content">表の操作</h3>
                                <button type="button" class="{{ $c['iconBtn'] }} text-base-content/45" x-on:click="help = false" aria-label="閉じる">{!! $icon['close'] !!}</button>
                            </div>
                            @if($addRow)
                                <div class="flex items-center gap-3 text-sm text-base-content">
                                    <span class="{{ $c['iconBtn'] }} pointer-events-none">{!! $icon['rowPlus'] !!}</span>
                                    <span>{{ $addRowLabel }}</span>
                                </div>
                            @endif
                            @if($addColumn)
                                <div class="flex items-center gap-3 text-sm text-base-content">
                                    <span class="{{ $c['iconBtn'] }} pointer-events-none">{!! $icon['colPlus'] !!}</span>
                                    <span>{{ $addColumnLabel }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Grid host. wire:ignore: Livewire's morphdom must never reconcile Alpine's x-for
         DOM (a non-skipRender render would otherwise reset selection / kill an open
         editor). A Livewire host re-seeds by bumping its :key (replacement, not morph).
         tabindex=0 makes the grid focusable so arrow-key nav works. --}}
    <div
        class="{{ $c['grid'] }}"
        wire:ignore
        x-ref="grid"
        tabindex="0"
        role="grid"
        x-on:keydown="onKey($event)"
        x-on:mouseup.window="endDrag(); fillEnd(); resizeEnd()"
        @if($resizableColumns)
        x-on:mousemove.window="resizeMove($event)"
        @endif
        {{-- when widths are frozen the grid hugs the table (fit-content, capped at the parent → it
             shrinks with narrowed columns instead of staying full-width, and still scrolls when the
             table is wider than the parent). Height (if set) rides along in both branches. --}}
        x-bind:style="widthsFrozen ? 'width: fit-content{{ $height ? '; max-height: '.$height : '' }}' : '{{ $height ? 'max-height: '.$height : '' }}'"
    >
        <table class="{{ $c['table'] }}" x-bind:class="widthsFrozen ? 'table-fixed' : ''" x-bind:style="widthsFrozen ? `width:${frozenTableWidth}px` : ''">
            <colgroup>
                @if($rowNumbers)<col x-bind:style="gutterWidth ? `width:${gutterWidth}px` : ''" />@endif
                <template x-for="(col, c) in cols" x-bind:key="col.key">
                    <col x-bind:style="col.width ? `width:${col.width}px` : ''" />
                </template>
            </colgroup>
            <thead>
                <tr role="row">
                    @if($rowNumbers)
                        <th class="{{ $c['gutterCorner'] }}"></th>
                    @endif
                    <template x-for="(col, c) in cols" x-bind:key="col.key">
                        {{-- Header BODY click = column-select (S2). The sort caret (right) = toggle sort
                             (S3), x-on:click.stop so it never also selects. The two gestures coexist. --}}
                        <th
                            class="{{ $c['headerCell'] }} group/th relative {{ $movableColumns ? 'cursor-grab' : 'cursor-pointer' }}"
                            x-bind:style="headerSelStyle(c)"
                            x-bind:class="{ 'opacity-40': dragCol === c, 'pn-dropcol-before': dragCol !== null && dropCol === c, 'pn-dropcol-after': dragCol !== null && dropCol === c + 1 && c === cols.length - 1 }"
                            role="columnheader"
                            x-bind:aria-sort="sortDir(c) === 'asc' ? 'ascending' : (sortDir(c) === 'desc' ? 'descending' : 'none')"
                            x-on:click="selectCol(c, $event)"
                            @if($contextMenu) x-on:contextmenu.prevent="openHeaderMenu(c, $event)" @endif
                            @if($movableColumns)
                            draggable="true"
                            x-on:dragstart="colDragStart(c, $event)"
                            x-on:dragend="clearColDrag()"
                            @endif
                        >
                            <span class="flex items-center gap-1">
                                <span class="flex-1 truncate" x-text="col.title ?? col.key"></span>
                                {{-- restore-to-original (↺). ALWAYS rendered for sortable columns so the header
                                     width is identical sorted vs unsorted — reserving its box (visibility-toggled,
                                     not x-if-inserted) is what stops the column from widening on sort. Visible +
                                     clickable only while THIS column is sorted; click resets the pre-sort order. --}}
                                <template x-if="colSortable(c)">
                                    <button type="button" draggable="false" class="{{ $c['sortCaret'] }}" x-bind:class="sortDir(c) ? '' : 'invisible pointer-events-none'" title="元に戻す" aria-label="並べ替えを元に戻す" x-on:click.stop="clearSort()" x-on:mousedown.stop>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="text-base-content/55"><path d="M3 12a9 9 0 1 0 2.6-6.4L3 8"/><path d="M3 3v5h5"/></svg>
                                    </button>
                                </template>
                                {{-- sort toggle (asc ⇄ desc). IDLE = a faint sort glyph revealed on header HOVER only;
                                     ACTIVE = a solid primary triangle (▲ asc / ▼ desc), always visible. --}}
                                <template x-if="colSortable(c)">
                                    <button
                                        type="button"
                                        draggable="false"
                                        class="{{ $c['sortCaret'] }}"
                                        x-bind:class="sortDir(c) ? '' : 'opacity-0 group-hover/th:opacity-100 transition-opacity duration-100'"
                                        x-on:click.stop="toggleSort(c)"
                                        x-on:mousedown.stop
                                        x-bind:aria-label="(sortDir(c) === 'asc' ? '降順に並べ替え' : '昇順に並べ替え') + '：' + (col.title ?? col.key)"
                                    >
                                        <template x-if="!sortDir(c)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="text-base-content/50"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg>
                                        </template>
                                        <template x-if="sortDir(c)">
                                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="text-primary" x-bind:style="sortDir(c) === 'asc' ? 'transform: rotate(180deg)' : ''"><path d="M6 9h12l-6 7z"/></svg>
                                        </template>
                                    </button>
                                </template>
                            </span>
                            @if($resizableColumns)
                                {{-- column-resize grip on the header's right edge (S3d). mousedown.stop.prevent so it
                                     starts a resize, not a column drag / text selection / column-select click. --}}
                                <span class="{{ $c['resizeHandle'] }}" draggable="false" x-on:mousedown.stop.prevent="resizeStart(c, $event)" x-on:click.stop x-on:dblclick.stop aria-hidden="true"></span>
                            @endif
                        </th>
                    </template>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, r) in rows" x-bind:key="row.id ?? r">
                    <tr
                        role="row"
                        x-bind:class="{ 'opacity-40': dragRow === r, 'pn-drop-before': dragRow !== null && dropRow === r, 'pn-drop-after': dragRow !== null && dropRow === r + 1 && r === rows.length - 1 }"
                    >
                        @if($rowNumbers)
                            {{-- Gutter click selects the whole row (S2); Shift extends the block; drag = reorder (S3). --}}
                            <td
                                class="{{ $c['rowNumGutter'] }} {{ $movableRows ? 'cursor-grab' : 'cursor-pointer' }}"
                                x-bind:style="gutterSelStyle(r)"
                                x-on:click="selectRow(r, $event)"
                                @if($movableRows)
                                draggable="true"
                                x-on:dragstart="rowDragStart(r, $event)"
                                x-on:dragend="clearRowDrag()"
                                @endif
                                x-text="r + 1"
                            ></td>
                        @endif
                        <template x-for="(col, c) in cols" x-bind:key="col.key">
                            <td
                                x-bind:data-r="r"
                                x-bind:data-c="c"
                                class="{{ $c['cell'] }} group/cell"
                                x-bind:class="{ '{{ $c['cellEditing'] }}': isEd(r, c), '{{ $c['cellInRange'] }}': inRange(r, c) && hasRange() && !isEd(r, c), 'pn-fill-target': inFillPreview(r, c), 'opacity-40': dragCol === c, 'pn-dropcol-before': dragCol !== null && dropCol === c, 'pn-dropcol-after': dragCol !== null && dropCol === c + 1 && c === cols.length - 1 }"
                                x-bind:style="isEd(r, c) ? '' : cellSelStyle(r, c)"
                                x-on:mousedown="startSelect(r, c, $event)"
                                x-on:mouseenter="onCellEnter(r, c)"
                                x-on:click="onCellClick(r, c, $event)"
                                x-on:dblclick="beginEdit(r, c)"
                                @if($contextMenu) x-on:contextmenu.prevent="openCellMenu(r, c, $event)" @endif
                                role="gridcell"
                            >
                                {{-- One <template x-if> per state×type. They are SIBLINGS (each x-if
                                     wraps a single root element) — Alpine's x-if honours only one root,
                                     so these must NOT be nested under a shared isEd/display wrapper. --}}
                                {{-- inline editor (one cell at a time). NOTE: select has NO edit mode — it
                                     is ALWAYS a live <select> in the display branch (no text entry for it). --}}
                                <template x-if="isEd(r, c) && (col.type === 'text' || col.type === 'number')">
                                    {{-- type=text (not number) so a partial value like "-" / "." isn't dropped
                                         mid-entry; castValue() coerces to a Number on commit. inputmode brings
                                         up the numeric keypad on mobile. --}}
                                    <input
                                        class="pn-sheet-editor"
                                        x-model="editValue"
                                        type="text"
                                        x-bind:inputmode="col.type === 'number' ? 'decimal' : null"
                                        x-bind:class="col.type === 'number' ? 'text-right tabular-nums' : ''"
                                        x-init="$nextTick(() => { $el.focus(); if (selectOnFocus && $el.select) $el.select(); else if ($el.setSelectionRange) { const n = $el.value.length; $el.setSelectionRange(n, n); } })"
                                        x-on:keydown="editorKey($event)"
                                        x-on:blur="commitEdit()"
                                        x-on:click.stop
                                        x-on:mousedown.stop
                                    >
                                </template>

                                {{-- date editor = the <x-calendar> month grid in a fixed popover (fixed
                                     escapes the grid's overflow clip). The nested pinionCalendar dispatches
                                     `calendar-select` on pick; this wrapper (in pinionSheet scope) catches it
                                     to set editValue + commit. anchorTo() positions it under the cell. --}}
                                <template x-if="isEd(r, c) && col.type === 'date'">
                                    {{-- `ready` gates click.outside until after first paint, so the dblclick /
                                         Enter that OPENED the editor can't immediately close it. --}}
                                    <div x-data="{ ready: false }" x-init="$nextTick(() => ready = true)" x-on:mousedown.stop x-on:calendar-select="editValue = $event.detail.value; commitEdit()" x-on:keydown.escape.window="cancelEdit()">
                                        <div
                                            class="fixed z-40"
                                            x-data="pinionCalendar({ value: editValue })"
                                            x-init="anchorTo($root.parentElement.closest('td'))"
                                            x-bind:style="px !== null ? `top:${py}px; left:${px}px` : 'visibility:hidden'"
                                            x-on:click.stop
                                            x-on:click.outside="ready && $dispatch('calendar-select', { value: value })"
                                        >
                                            <x-calendar-grid />
                                        </div>
                                    </div>
                                </template>

                                {{-- display (when not editing) --}}
                                <template x-if="!isEd(r, c) && col.type === 'checkbox'">
                                    <span class="{{ $c['checkCell'] }}" x-bind:class="truthy(row[col.key]) ? 'is-checked' : ''" role="checkbox" x-bind:aria-checked="truthy(row[col.key]) ? 'true' : 'false'"></span>
                                </template>
                                <template x-if="!isEd(r, c) && col.type === 'number'">
                                    {{-- Value right-aligned; larger −/＋ steppers on the LEFT, revealed on
                                         cell hover via OPACITY (space reserved → no layout shift). The
                                         steppers stop propagation so they don't select/edit; dblclick the
                                         cell still opens the text editor. --}}
                                    <div class="flex items-center gap-1.5">
                                        <span class="flex items-center gap-0.5 shrink-0 opacity-0 group-hover/cell:opacity-100 transition-opacity duration-100">
                                            <button type="button" class="{{ $c['numStepper'] }}" tabindex="-1" aria-label="減らす" x-on:mousedown.stop x-on:dblclick.stop x-on:click.stop="step(r, c, -1)">−</button>
                                            <button type="button" class="{{ $c['numStepper'] }}" tabindex="-1" aria-label="増やす" x-on:mousedown.stop x-on:dblclick.stop x-on:click.stop="step(r, c, 1)">＋</button>
                                        </span>
                                        <span class="flex-1 text-right tabular-nums" x-text="fmt(row[col.key])"></span>
                                    </div>
                                </template>
                                {{-- select cell: a custom trigger (value + chevron) opening the pinion-styled
                                     dropdown (a single sheet-level <ul>, below). NO native <select> (which loses
                                     its options on Alpine re-render). click.stop keeps the open-click from the
                                     dropdown's click.outside. --}}
                                <template x-if="col.type === 'select'">
                                    <button type="button" class="w-full flex items-center justify-between gap-1 text-left" x-bind:class="editableCol(c) ? 'cursor-pointer' : 'cursor-default'" x-bind:disabled="!editableCol(c)" x-on:mousedown.stop x-on:click.stop="$event.shiftKey ? extendTo(r, c) : toggleSelect(r, c)" x-on:dblclick.stop>
                                        <span class="truncate" x-bind:class="row[col.key] ? 'text-base-content' : 'text-base-content/40'" x-text="row[col.key] || '—'"></span>
                                        <svg class="{{ $sel['chevron'] }}" x-bind:class="isSelOpen(r, c) ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                                    </button>
                                </template>
                                <template x-if="!isEd(r, c) && col.type !== 'checkbox' && col.type !== 'number' && col.type !== 'select'">
                                    <span x-text="fmt(row[col.key])"></span>
                                </template>

                                @if($selectableRange)
                                    {{-- fill handle (S3c): only on the range's bottom-right cell. mousedown.stop.prevent
                                         so it starts a FILL drag, not a selection drag / text selection. Cells call
                                         onCellEnter() (→ fillMoveTo while filling), window mouseup → fillEnd(). --}}
                                    <template x-if="isFillCorner(r, c) && editableCol(c)">
                                        <span class="{{ $c['fillHandle'] }} z-10" x-on:mousedown.stop.prevent="fillStart()" aria-hidden="true"></span>
                                    </template>
                                @endif
                            </td>
                        </template>
                    </tr>
                </template>

                {{-- empty state --}}
                <tr x-show="rows.length === 0">
                    <td class="{{ $c['cell'] }} text-center text-base-content/40" x-bind:colspan="cols.length + {{ $rowNumbers ? 1 : 0 }}">行がありません</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Single select-cell dropdown (pinion <x-select> look). Driven by `openSel`; one
         instance for the whole sheet (avoids the multi-x-show click.outside race). fixed-
         positioned via toggleSelect() so the grid overflow never clips it. --}}
    <ul
        x-show="openSel"
        x-cloak
        x-transition.opacity.duration.100ms
        x-on:click.outside="openSel = null"
        x-on:keydown.escape.window="openSel = null"
        x-bind:style="openSel ? `top:${selPy}px; left:${selPx}px; min-width:${selW}px` : ''"
        class="fixed z-40 max-h-60 overflow-auto bg-base-100 tune-border border-base-300 rounded-[var(--radius-field)] shadow-lg p-1"
        role="listbox"
    >
        <li class="{{ $sel['option'] }}" x-on:click="chooseOption(openSel.r, openSel.c, '')"><span class="text-base-content/50">（なし）</span></li>
        <template x-for="o in (openSel ? (cols[openSel.c].options || []) : [])" x-bind:key="o">
            <li
                class="{{ $sel['option'] }}"
                x-bind:class="rows[openSel.r][cols[openSel.c].key] === o ? '{{ $sel['optionSelected'] }}' : ''"
                x-on:click="chooseOption(openSel.r, openSel.c, o)"
                role="option"
                x-bind:aria-selected="rows[openSel.r][cols[openSel.c].key] === o"
            >
                <span x-text="o"></span>
                <svg x-show="rows[openSel.r][cols[openSel.c].key] === o" class="{{ $sel['optionCheck'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </li>
        </template>
    </ul>

    @if($contextMenu)
        {{-- Right-click context menu (S3e). Single sheet-level instance, driven by `menu`,
             fixed-positioned (set in menuAt() with viewport clamp) so the grid overflow never
             clips it. Copy is always available; everything else is gated by editable. --}}
        <ul
            x-show="menu"
            x-cloak
            x-transition.opacity.duration.100ms
            x-on:click.outside="closeMenu()"
            x-on:keydown.escape.window="closeMenu()"
            x-on:contextmenu.prevent
            x-bind:style="menu ? `top:${menu.y}px; left:${menu.x}px` : ''"
            class="fixed z-50 min-w-52 bg-base-100 tune-border border-base-300 rounded-[var(--radius-field)] shadow-lg p-1 text-[length:var(--text-field-sm)]"
            role="menu"
        >
            @if($editable)
                {{-- 列の型 — hover flyout submenu; converts the right-clicked column's type (coerces cells). --}}
                <li class="group/typesub relative">
                    <div class="{{ $sel['option'] }} justify-between" role="menuitem">
                        <span>列の型</span>
                        <svg class="w-3.5 h-3.5 -mr-0.5 text-base-content/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </div>
                    <ul class="hidden group-hover/typesub:block absolute left-full top-0 min-w-36 bg-base-100 tune-border border-base-300 rounded-[var(--radius-field)] shadow-lg p-1" role="menu">
                        <template x-for="t in colTypeOptions" x-bind:key="t.value">
                            <li class="{{ $sel['option'] }}" x-bind:class="colType(menu?.c) === t.value ? '{{ $sel['optionSelected'] }}' : ''" role="menuitemradio" x-bind:aria-checked="colType(menu?.c) === t.value" x-on:click="convertColumn(menu?.c, t.value)">
                                <span x-text="t.label"></span>
                                <svg x-show="colType(menu?.c) === t.value" class="{{ $sel['optionCheck'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </li>
                        </template>
                    </ul>
                </li>
                <li class="my-1 border-t border-base-200" role="separator"></li>
            @endif

            <li class="{{ $sel['option'] }} justify-between" role="menuitem" x-on:click="copyRange(); closeMenu()"><span>コピー</span><span class="text-base-content/35 text-xs tabular-nums">⌘C</span></li>
            @if($editable)
                <li class="{{ $sel['option'] }} justify-between" role="menuitem" x-on:click="pasteRange(); closeMenu()"><span>貼り付け</span><span class="text-base-content/35 text-xs tabular-nums">⌘V</span></li>
                <li class="{{ $sel['option'] }} justify-between" role="menuitem" x-on:click="clearRange(); closeMenu()"><span>クリア</span><span class="text-base-content/35 text-xs tabular-nums">Del</span></li>
                <li class="my-1 border-t border-base-200" role="separator"></li>
                <li class="{{ $sel['option'] }}" role="menuitem" x-on:click="insertRow(menu?.r)"><span>行を上に挿入</span></li>
                <li class="{{ $sel['option'] }}" role="menuitem" x-on:click="insertRow((menu?.r ?? 0) + 1)"><span>行を下に挿入</span></li>
                <li class="{{ $sel['option'] }}" role="menuitem" x-on:click="deleteRows()"><span>行を削除</span></li>
                <li class="{{ $sel['option'] }}" role="menuitem" x-on:click="insertColumn(menu?.c)"><span>列を左に挿入</span></li>
                <li class="{{ $sel['option'] }}" role="menuitem" x-on:click="insertColumn((menu?.c ?? 0) + 1)"><span>列を右に挿入</span></li>
                <li class="{{ $sel['option'] }}" role="menuitem" x-on:click="deleteColumns()"><span>列を削除</span></li>
            @endif
        </ul>
    @endif

    {{-- wire:model carrier. sheet.js writes the row-array JSON string here and dispatches
         `input` so Livewire syncs per the sync cadence (a hidden input transmits a string;
         bind it to a `public string` prop and json_decode — never type the prop `array`). --}}
    @if($hasWireModel)
        <input type="hidden" x-ref="model" {{ $wireModel }} />
    @else
        <input type="hidden" x-ref="model" />
    @endif
</div>
