@props(['size' => 'md'])

@php
    use SparrowhawkLabs\PinionUi\Compose\CalendarComposer;

    // The month-grid markup, shared by <x-calendar> (standalone) and the <x-sheet> date
    // editor. It is rendered INSIDE an `x-data="pinionCalendar(...)"` scope (Alpine scope
    // follows DOM ancestry, not Blade boundaries), so its x-on/x-bind resolve against the
    // calendar factory.  Each x-for template has a SINGLE root (blank cells render an
    // invisible day button) — Alpine's x-if/x-for ignore extra roots.
    $c = CalendarComposer::compose(['size' => $size]);
    $svg = fn (string $b) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $b . '</svg>';
    $prev = $svg('<path d="m15 18-6-6 6-6"/>');
    $next = $svg('<path d="m9 18 6-6-6-6"/>');
@endphp

<div class="{{ $c['panel'] }}">
    <div class="{{ $c['header'] }}">
        <button type="button" class="{{ $c['navBtn'] }}" x-on:click="prev()" aria-label="前へ">{!! $prev !!}</button>
        {{-- clickable label: days→years (pick a year), years→back to days --}}
        <button type="button" class="{{ $c['monthLabel'] }} px-2 py-0.5 rounded-[calc(var(--radius-box)*0.4)] hover:bg-base-content/[0.06] cursor-pointer transition-colors" x-on:click="toggleYears()" x-text="headerLabel"></button>
        <button type="button" class="{{ $c['navBtn'] }}" x-on:click="next()" aria-label="次へ">{!! $next !!}</button>
    </div>

    {{-- days view --}}
    <template x-if="mode === 'days'">
        <div>
            <div class="{{ $c['weekRow'] }} mb-0.5">
                <template x-for="w in weekdays" x-bind:key="w">
                    <span class="{{ $c['weekday'] }}" x-text="w"></span>
                </template>
            </div>
            <div class="flex flex-col gap-0.5">
                <template x-for="(week, wi) in weeks" x-bind:key="wi">
                    <div class="{{ $c['weekRow'] }}">
                        <template x-for="(cell, ci) in week" x-bind:key="ci">
                            <button
                                type="button"
                                class="{{ $c['day'] }}"
                                x-bind:class="{ '{{ $c['daySelected'] }}': cell && cell.selected, '{{ $c['dayToday'] }}': cell && cell.today && !cell.selected, 'invisible pointer-events-none': !cell }"
                                x-bind:disabled="!cell"
                                x-on:click="cell && pick(cell.iso)"
                                x-text="cell ? cell.d : ''"
                            ></button>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- years view (click the header label) --}}
    <template x-if="mode === 'years'">
        <div class="{{ $c['yearRow'] }}">
            <template x-for="yr in years" x-bind:key="yr.y">
                <button
                    type="button"
                    class="{{ $c['year'] }}"
                    x-bind:class="{ '{{ $c['daySelected'] }}': yr.selected, '{{ $c['dayToday'] }}': yr.current && !yr.selected }"
                    x-on:click="pickYear(yr.y)"
                    x-text="yr.y"
                ></button>
            </template>
        </div>
    </template>

    <div class="{{ $c['footer'] }}">
        <button type="button" class="{{ $c['footerBtn'] }}" x-on:click="goToday()">今日</button>
        <button type="button" class="{{ $c['footerBtn'] }}" x-on:click="clear()">クリア</button>
    </div>
</div>
