{{--
    <x-look-head /> — apply the visitor's theme × tune BEFORE first paint (v0.20.0).

    Put it early in <head>, before @vite. Order of precedence:
      1. ?theme= / ?tune= (allowlist-checked; also remembered)
      2. the choice remembered in localStorage by the switchers
      3. what the server already put on <html> (pn_look())

    Without this, a remembered dark theme shows as the light default for one
    frame and then flips (the switchers only run after Alpine boots).

    Config: `pinion-ui.look` (see config/pinion-ui.php). Props override it.
--}}
@props([
    'themes' => null,
    'tunes' => null,
    'storageKey' => null,
    'query' => null,
])

@php
    try {
        $look = (array) config('pinion-ui.look', []);
    } catch (\Throwable) {
        $look = [];
    }
    $families = $themes ?? ($look['themes'] ?? null);
    if ($families === null) {
        $families = [];
        foreach (pn_theme_groups() as $items) {
            foreach ($items as $t) {
                $families[] = $t['name'];
            }
        }
    }
    $tuneList = $tunes ?? ($look['tunes'] ?? null) ?? pn_tunes();
    $key = $storageKey ?? ($look['storage_key'] ?? 'pn');
    $useQuery = $query ?? ($look['query'] ?? true);
@endphp

<script>
    (function () {
        var FAM = @js(array_values($families)), TUNES = @js(array_values($tuneList));
        var KEY = @js($key), Q = @js((bool) $useQuery);
        var root = document.documentElement;
        var q = Q ? new URLSearchParams(location.search) : null;
        function ok(kind, v) {
            if (!v) return false;
            return kind === 'theme' ? FAM.indexOf(v.replace(/-dark$/, '')) >= 0 : TUNES.indexOf(v) >= 0;
        }
        function pick(kind) {
            var v = q && q.get(kind);
            if (ok(kind, v)) { try { localStorage.setItem(KEY + '-' + kind, v); } catch (e) {} return v; }
            try { v = localStorage.getItem(KEY + '-' + kind); } catch (e) { v = null; }
            return ok(kind, v) ? v : null;
        }
        var theme = pick('theme'), tune = pick('tune');
        if (theme) root.setAttribute('data-theme', theme);
        if (tune) root.setAttribute('data-tune', tune);
    })();
</script>
