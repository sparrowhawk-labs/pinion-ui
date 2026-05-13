<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class SkeletonComposer
{
    public static function compose(array $props): array
    {
        $shape    = $props['shape']    ?? 'rect';
        $width    = $props['width']    ?? null;
        $height   = $props['height']   ?? null;
        $lines    = max(1, (int) ($props['lines'] ?? 1));
        $radius   = $props['radius']   ?? 'default';
        $animated = array_key_exists('animated', $props) ? (bool) $props['animated'] : true;

        // Shape implies width/height/radius defaults
        if ($shape === 'circle') {
            $width  = $width  ?? 'w-12';
            $height = $height ?? 'h-12';
            // Circle ignores radius prop — always rounded-full
            $radiusClass = 'rounded-full';
        } elseif ($shape === 'text') {
            // text: height fixed to text line height, width default full
            $width  = $width  ?? 'w-full';
            $height = $height ?? 'h-4';
            $radiusClass = self::radiusClass($radius);
        } else {
            // rect
            $width  = $width  ?? 'w-full';
            $height = $height ?? 'h-4';
            $radiusClass = self::radiusClass($radius);
        }

        $baseClass     = $animated ? 'skeleton' : 'bg-base-300';
        $isMultiline   = $shape === 'text' && $lines > 1;

        if ($isMultiline) {
            // Wrapper holds width + vertical spacing; each line gets its own skeleton class.
            $wrapper      = 'space-y-2 '.$width;
            $item         = trim($baseClass.' '.$radiusClass.' h-4 w-full');
            $itemLast     = trim($baseClass.' '.$radiusClass.' h-4 w-2/3');

            return [
                'root'     => $wrapper,
                'item'     => $item,
                'itemLast' => $itemLast,
                'shape'    => $shape,
                'lines'    => (string) $lines,
                'animated' => $animated ? '1' : '0',
            ];
        }

        $root = trim($baseClass.' '.$radiusClass.' '.$width.' '.$height);

        return [
            'root'     => $root,
            'item'     => $root,
            'itemLast' => $root,
            'shape'    => $shape,
            'lines'    => (string) $lines,
            'animated' => $animated ? '1' : '0',
        ];
    }

    private static function radiusClass(string $radius): string
    {
        return match ($radius) {
            'sm'   => 'rounded-sm',
            'md'   => 'rounded-md',
            'lg'   => 'rounded-lg',
            'xl'   => 'rounded-xl',
            'full' => 'rounded-full',
            default => 'rounded',
        };
    }
}
