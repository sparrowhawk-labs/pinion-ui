@props([
    'variant' => 'centered',
    'title' => null,
    'subtitle' => null,
    'badge' => null,
    'size' => 'lg',
    'image' => null,
    'imageAlt' => '',
    'primaryAction' => null,
    'secondaryAction' => null,
    'bgClass' => 'bg-base-100',
])

@php
    $titleSize = match($size) {
        'md' => 'text-3xl md:text-4xl',
        'xl' => 'text-5xl md:text-6xl lg:text-7xl',
        default => 'text-4xl md:text-5xl lg:text-6xl',
    };
@endphp

<section {{ $attributes->merge(['class' => "$bgClass space-section"]) }}>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if($variant === 'centered')
            <div class="text-center max-w-3xl mx-auto flex flex-col items-center gap-section-inner">
                @if($badge)
                    <span class="inline-flex items-center px-3 py-1 text-xs font-medium bg-primary/10 text-primary rounded-[var(--radius-selector)]">
                        {{ $badge }}
                    </span>
                @endif

                @if(isset($heading))
                    {{ $heading }}
                @elseif($title)
                    <h1 class="{{ $titleSize }} font-bold tracking-tight text-base-content">
                        {{ $title }}
                    </h1>
                @endif

                @if($subtitle)
                    <p class="text-lg md:text-xl text-base-content/70">
                        {{ $subtitle }}
                    </p>
                @endif

                @if($primaryAction || $secondaryAction || isset($actions))
                    <div class="flex flex-col sm:flex-row items-center gap-compact">
                        @if(isset($actions))
                            {{ $actions }}
                        @else
                            @if($primaryAction)
                                <x-pinion-ui::button variant="primary" size="lg" :href="$primaryAction['href'] ?? null">
                                    {{ $primaryAction['label'] }}
                                </x-pinion-ui::button>
                            @endif
                            @if($secondaryAction)
                                <x-pinion-ui::button variant="ghost" size="lg" :href="$secondaryAction['href'] ?? null">
                                    {{ $secondaryAction['label'] }}
                                </x-pinion-ui::button>
                            @endif
                        @endif
                    </div>
                @endif

                {{ $slot }}
            </div>

        @elseif($variant === 'split')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-section-inner items-center">
                <div class="flex flex-col gap-element">
                    @if($badge)
                        <span class="inline-flex items-center self-start px-3 py-1 text-xs font-medium bg-primary/10 text-primary rounded-[var(--radius-selector)]">
                            {{ $badge }}
                        </span>
                    @endif

                    @if(isset($heading))
                        {{ $heading }}
                    @elseif($title)
                        <h1 class="{{ $titleSize }} font-bold tracking-tight text-base-content">
                            {{ $title }}
                        </h1>
                    @endif

                    @if($subtitle)
                        <p class="text-lg md:text-xl text-base-content/70">
                            {{ $subtitle }}
                        </p>
                    @endif

                    @if($primaryAction || $secondaryAction || isset($actions))
                        <div class="flex flex-col sm:flex-row items-start gap-compact">
                            @if(isset($actions))
                                {{ $actions }}
                            @else
                                @if($primaryAction)
                                    <x-pinion-ui::button variant="primary" size="lg" :href="$primaryAction['href'] ?? null">
                                        {{ $primaryAction['label'] }}
                                    </x-pinion-ui::button>
                                @endif
                                @if($secondaryAction)
                                    <x-pinion-ui::button variant="ghost" size="lg" :href="$secondaryAction['href'] ?? null">
                                        {{ $secondaryAction['label'] }}
                                    </x-pinion-ui::button>
                                @endif
                            @endif
                        </div>
                    @endif

                    {{ $slot }}
                </div>

                <div>
                    @if(isset($media))
                        {{ $media }}
                    @elseif($image)
                        <img
                            src="{{ $image }}"
                            alt="{{ $imageAlt }}"
                            class="rounded-[var(--radius-box)] w-full object-cover"
                        />
                    @endif
                </div>
            </div>
        @endif

    </div>
</section>
