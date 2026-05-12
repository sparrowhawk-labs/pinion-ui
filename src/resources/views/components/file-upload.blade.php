@props([
    'name' => null,
    'accept' => null,
    'multiple' => false,
    'value' => null,
    'label' => null,
    'description' => null,
    'helper' => null,
    'placeholder' => 'Drop a file here or browse',
    'color' => 'neutral',
    'appearance' => 'outline',
    'size' => 'md',
    'previewLayout' => 'horizontal',
    'error' => null,
    'disabled' => false,
    'simulate' => false,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\FileUploadComposer;

    $fileId = $attributes->get('id', ($name ? $name . '_' : 'file_') . uniqid());
    $isDropzone = $appearance === 'dropzone';
    $isGrid = $previewLayout === 'grid';
    $c = FileUploadComposer::compose([
        'color' => $color,
        'appearance' => $appearance,
        'size' => $size,
        'error' => $error,
        'disabled' => $disabled,
        'previewLayout' => $previewLayout,
    ]);
@endphp

<label
    for="{{ $fileId }}"
    class="{{ $c['wrapper'] }}"
    x-data="{
        items: [],
        simulate: {{ $simulate ? 'true' : 'false' }},
        fmt(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1024 / 1024).toFixed(1) + ' MB';
        },
        onChange(event) {
            this.releaseAll();
            const list = Array.from(event.target.files || []);
            this.items = list.map(f => {
                const isImage = (f.type || '').startsWith('image/');
                return {
                    name: f.name,
                    size: f.size,
                    type: f.type,
                    thumb: isImage ? URL.createObjectURL(f) : null,
                    progress: this.simulate ? 0 : 100,
                    complete: !this.simulate,
                };
            });
            if (this.simulate) this.runSimulation();
        },
        runSimulation() {
            this.items.forEach(it => {
                const tick = () => {
                    if (it.progress >= 100) { it.complete = true; return; }
                    it.progress = Math.min(100, it.progress + 8 + Math.random() * 14);
                    setTimeout(tick, 220 + Math.random() * 280);
                };
                setTimeout(tick, 80 + Math.random() * 200);
            });
        },
        remove(index) {
            const it = this.items[index];
            if (it && it.thumb) URL.revokeObjectURL(it.thumb);
            this.items.splice(index, 1);
            if (this.items.length === 0 && this.$refs.input) this.$refs.input.value = '';
        },
        releaseAll() {
            for (const it of this.items) if (it.thumb) URL.revokeObjectURL(it.thumb);
        },
    }"
    x-init="() => { $watch('items', () => {}); }"
>
    @if($label)
        <span class="{{ $c['labelText'] }}">{{ $label }}</span>
    @endif

    @if($isDropzone)
        <div class="{{ $c['field'] }}">
            <svg class="{{ $c['dropzoneIcon'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <div>
                <span class="{{ $c['browseLink'] }}">Browse</span>
                <span class="text-base-content/60"> or drag and drop here</span>
            </div>
            @if($description)
                <small class="{{ $c['dropzoneHint'] }}">{{ $description }}</small>
            @endif
            <input
                x-ref="input"
                type="file"
                id="{{ $fileId }}"
                class="{{ $c['inputClass'] }}"
                @change="onChange($event)"
                @if($name) name="{{ $name }}{{ $multiple ? '[]' : '' }}" @endif
                @if($accept) accept="{{ $accept }}" @endif
                @if($multiple) multiple @endif
                @if($disabled) disabled @endif
                {{ $attributes->whereStartsWith('wire:') }}
                {{ $attributes->whereDoesntStartWith('wire:')->whereDoesntStartWith('class') }}
            />
        </div>
    @else
        <div class="{{ $c['field'] }}">
            <input
                x-ref="input"
                type="file"
                id="{{ $fileId }}"
                class="{{ $c['inputClass'] }}"
                @change="onChange($event)"
                @if($name) name="{{ $name }}{{ $multiple ? '[]' : '' }}" @endif
                @if($accept) accept="{{ $accept }}" @endif
                @if($multiple) multiple @endif
                @if($disabled) disabled @endif
                {{ $attributes->whereStartsWith('wire:') }}
                {{ $attributes->whereDoesntStartWith('wire:')->whereDoesntStartWith('class') }}
            />
        </div>
        @if($description && !$error)
            <small class="{{ $c['hint'] }}">{{ $description }}</small>
        @endif
    @endif

    <ul class="{{ $c['previewList'] }}" x-show="items.length > 0" x-cloak>
        <template x-for="(item, index) in items" :key="index">
            @if($isGrid)
                <li class="{{ $c['previewItem'] }}">
                    <template x-if="item.thumb">
                        <img class="{{ $c['previewThumb'] }}" :src="item.thumb" :alt="item.name" />
                    </template>
                    <template x-if="!item.thumb">
                        <div class="{{ $c['previewIconBox'] }}">
                            <svg class="{{ $c['previewIconSize'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m6.75 12-3-3m0 0-3 3m3-3v6m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                    </template>
                    <span class="{{ $c['previewName'] }}" x-text="item.name"></span>
                    <button type="button" class="{{ $c['previewRemove'] }}" @click.prevent="remove(index)" aria-label="Remove file">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <div class="{{ $c['progressTrack'] }}" x-show="!item.complete">
                        <div class="{{ $c['progressFill'] }}" :style="`width: ${item.progress}%`"></div>
                    </div>
                </li>
            @else
                <li class="{{ $c['previewItem'] }}">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <template x-if="item.thumb">
                                <img class="{{ $c['previewThumb'] }}" :src="item.thumb" :alt="item.name" />
                            </template>
                            <template x-if="!item.thumb">
                                <div class="{{ $c['previewIconBox'] }}">
                                    <svg class="{{ $c['previewIconSize'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m6.75 12-3-3m0 0-3 3m3-3v6m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                            </template>
                            <div class="flex flex-col min-w-0">
                                <span class="{{ $c['previewName'] }}" x-text="item.name"></span>
                                <span class="text-[length:var(--text-field-xs)] text-base-content/50" x-text="fmt(item.size)"></span>
                            </div>
                        </div>
                        <button type="button" class="{{ $c['previewRemove'] }}" @click.prevent="remove(index)" aria-label="Remove file">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="{{ $c['progressTrack'] }}">
                        <div class="{{ $c['progressFill'] }}" :class="item.complete ? 'bg-success!' : ''" :style="`width: ${item.progress}%`"></div>
                    </div>
                </li>
            @endif
        </template>
    </ul>

    @if($error)
        <small class="{{ $c['hint'] }}">{{ $error }}</small>
    @endif

    @if($helper && !$error)
        <small class="{{ $c['hint'] }}">{{ $helper }}</small>
    @endif
</label>
