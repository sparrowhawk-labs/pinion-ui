@props([
    'lines' => [],
    'title' => null,
    'autoplay' => true,
    'speed' => 28,
    'lineDelay' => 350,
    'color' => 'primary',
])

@php
    // Each line: ['prompt' => '$', 'text' => '...', 'typed' => true].
    // `typed` (default true) animates char-by-char; set false for output/
    // response lines that should just appear instantly (a real command's
    // stdout doesn't "type itself").
    $normalized = array_map(function ($line) {
        return [
            'prompt' => $line['prompt'] ?? '$',
            'text' => $line['text'] ?? '',
            'typed' => $line['typed'] ?? true,
            'display' => '',
        ];
    }, $lines);

    $promptColorClass = match ($color) {
        'secondary' => 'text-secondary',
        'accent' => 'text-accent',
        'neutral' => 'text-neutral-content',
        'info' => 'text-info',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'error' => 'text-error',
        default => 'text-primary',
    };
@endphp

{{--
    A fake terminal window with a typewriter reveal — for demoing a CLI
    step (`artisan tinker`, a seeder, a build command, …) without an actual
    terminal recording. Commands (`typed: true`) animate character-by-
    character; output lines (`typed: false`) appear instantly, like real
    stdout would. The default slot reveals once the whole sequence finishes
    (`x-show="done"`) — a natural place for a "continue" CTA.
--}}
<div
    x-data="{
        lines: {{ json_encode($normalized) }},
        lineIndex: -1,
        done: false,
        async run() {
            for (this.lineIndex = 0; this.lineIndex < this.lines.length; this.lineIndex++) {
                const line = this.lines[this.lineIndex];
                if (!line.typed) {
                    line.display = line.text;
                    await new Promise(r => setTimeout(r, {{ (int) $lineDelay }}));
                    continue;
                }
                for (let i = 0; i < line.text.length; i++) {
                    line.display = line.text.slice(0, i + 1);
                    await new Promise(r => setTimeout(r, {{ (int) $speed }}));
                }
                await new Promise(r => setTimeout(r, {{ (int) $lineDelay }}));
            }
            this.done = true;
            $dispatch('terminal-done');
        },
    }"
    @if($autoplay) x-init="run()" @endif
    {{ $attributes->class(['pn-terminal']) }}
>
    <div class="overflow-hidden rounded-[var(--radius-box)] bg-neutral shadow-xl">
        <div class="flex items-center gap-1.5 bg-neutral-content/10 px-4 py-2.5">
            <span class="h-3 w-3 rounded-full bg-error/70"></span>
            <span class="h-3 w-3 rounded-full bg-warning/70"></span>
            <span class="h-3 w-3 rounded-full bg-success/70"></span>
            @if($title)
                <span class="ml-2 text-xs text-neutral-content/60">{{ $title }}</span>
            @endif
        </div>
        <div class="p-4 font-mono text-sm leading-relaxed text-neutral-content/90">
            <template x-for="(line, i) in lines" :key="i">
                <div x-show="i <= lineIndex" class="whitespace-pre-wrap break-all">
                    <span class="{{ $promptColorClass }}" x-text="line.prompt"></span>
                    <span x-text="' ' + line.display"></span>
                </div>
            </template>
        </div>
    </div>

    @if($slot->isNotEmpty())
        <div class="mt-6 flex justify-center" x-show="done" x-transition>
            {{ $slot }}
        </div>
    @endif
</div>
