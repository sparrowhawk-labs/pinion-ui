{{-- Menu separator for <x-dropdown> / sidebar nav panels. Full-bleed by design:
     the panel carries only py-1 (no horizontal padding — see AGENTS.md), so a
     plain border-t reaches both panel edges. For a labelled or vertical rule
     use <x-divider>. --}}
<div role="separator" {{ $attributes->merge(['class' => 'my-1 border-t border-base-content/10']) }}></div>
