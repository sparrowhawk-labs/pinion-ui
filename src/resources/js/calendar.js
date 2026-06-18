// ─────────────────────────────────────────────────────────────────────────────
// pinion-ui · <x-calendar> behavior module  (minimal date picker — pure Alpine)
// ─────────────────────────────────────────────────────────────────────────────
//
// A small, PrelineUI-flavoured month calendar. NO third-party dep — pure Alpine +
// Tailwind. OPT-IN: a consumer imports it (or `php artisan ui:install --calendar`
// wires it) into resources/js/app.js. Used standalone via <x-calendar> (a date input
// with a popover) and as the date-cell editor inside <x-sheet>.
//
//     import { pinionCalendar } from
//       '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/calendar.js';
//     Alpine.data('pinionCalendar', pinionCalendar);
//
// Value contract: an ISO 'YYYY-MM-DD' string (or '' for empty). On pick it sets
// `value` AND dispatches a bubbling `calendar-select` CustomEvent { value } so a host
// (e.g. a <x-sheet> date cell, or a hidden wire:model input) can react.

const pad = (n) => String(n).padStart(2, '0');
const iso = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;     // m is 0-based
const todayISO = () => { const t = new Date(); return iso(t.getFullYear(), t.getMonth(), t.getDate()); };

export function pinionCalendar(opts = {}) {
  return {
    value: opts.value ?? '',
    open: opts.open ?? false,   // popover open state (standalone <x-calendar>; unused inside <x-sheet>)
    mode: 'days',               // 'days' | 'years' — clicking the header label switches to year picking
    viewY: 2000,
    viewM: 0,
    // fixed-position anchor (set by anchorTo) so a popover inside an overflow:auto
    // container is not clipped — position:fixed escapes the clip.
    px: null, py: null,

    init() {
      let base = this.value ? new Date(this.value + 'T00:00:00') : new Date();
      if (isNaN(base)) base = new Date();   // a non-ISO / garbage value → fall back to this month
      this.viewY = base.getFullYear();
      this.viewM = base.getMonth();
    },

    get weekdays() { return ['日', '月', '火', '水', '木', '金', '土']; },
    get monthLabel() { return `${this.viewY}年 ${this.viewM + 1}月`; },

    // A 6×7 grid of day cells (null = blank). Each cell: { d, iso, today, selected }.
    get weeks() {
      const first = new Date(this.viewY, this.viewM, 1);
      const startDow = first.getDay();
      const daysInMonth = new Date(this.viewY, this.viewM + 1, 0).getDate();
      const tISO = todayISO();
      const cells = [];
      for (let i = 0; i < startDow; i++) cells.push(null);
      for (let d = 1; d <= daysInMonth; d++) {
        const dISO = iso(this.viewY, this.viewM, d);
        cells.push({ d, iso: dISO, today: dISO === tISO, selected: dISO === this.value });
      }
      while (cells.length % 7 !== 0) cells.push(null);
      const weeks = [];
      for (let i = 0; i < cells.length; i += 7) weeks.push(cells.slice(i, i + 7));
      return weeks;
    },

    prevMonth() { if (this.viewM === 0) { this.viewM = 11; this.viewY--; } else this.viewM--; },
    nextMonth() { if (this.viewM === 11) { this.viewM = 0; this.viewY++; } else this.viewM++; },
    goToday() { const t = new Date(); this.viewY = t.getFullYear(); this.viewM = t.getMonth(); this.mode = 'days'; },

    // ── year picker (mode === 'years') ──
    // A 4×3 grid of 12 years around the viewed year; click the header label to enter.
    get years() {
      const start = this.viewY - 5;
      const selY = this.value ? +this.value.slice(0, 4) : null;
      const curY = new Date().getFullYear();
      const arr = [];
      for (let y = start; y < start + 12; y++) arr.push({ y, selected: y === selY, current: y === curY });
      return arr;
    },
    get headerLabel() { return this.mode === 'years' ? `${this.viewY - 5} – ${this.viewY + 6}` : this.monthLabel; },
    toggleYears() { this.mode = this.mode === 'years' ? 'days' : 'years'; },
    pickYear(y) { this.viewY = y; this.mode = 'days'; },
    // header prev/next: shift a month in days-mode, a 12-year block in years-mode.
    prev() { if (this.mode === 'years') this.viewY -= 12; else this.prevMonth(); },
    next() { if (this.mode === 'years') this.viewY += 12; else this.nextMonth(); },

    pick(dayISO) {
      this.value = dayISO;
      this.$dispatch('calendar-select', { value: dayISO });
    },
    clear() {
      this.value = '';
      this.$dispatch('calendar-select', { value: '' });
    },

    // Position a fixed popover just under `el` (the anchor cell/trigger), clamped so it
    // stays on-screen. Used by the <x-sheet> date editor (anchor = the editing cell).
    anchorTo(el) {
      if (!el) return;
      const r = el.getBoundingClientRect();
      const W = 248, H = 300, M = 8;
      let left = r.left;
      let top = r.bottom + 4;
      if (left + W > window.innerWidth - M) left = Math.max(M, window.innerWidth - W - M);
      if (top + H > window.innerHeight - M) top = Math.max(M, r.top - H - 4);
      this.px = Math.round(left);
      this.py = Math.round(top);
    },
  };
}

export default pinionCalendar;
