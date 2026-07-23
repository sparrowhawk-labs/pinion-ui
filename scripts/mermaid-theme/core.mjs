// mermaid 配色グルーの唯一の展開ロジック（single source of expansion）。
// tokens.json（ノブ）を受け取り、全環境が使う {colorMap, themeVariables, classDefs} に展開する。
// この 1 ファイルだけが「どう展開するか」を知る。JS も PHP も、この出力を消費するだけ。
import fs from 'fs';
import path from 'path';
import url from 'url';

const HERE = path.dirname(url.fileURLToPath(import.meta.url));

export function loadTokens(p) {
  return JSON.parse(fs.readFileSync(p || path.join(HERE, 'tokens.json'), 'utf8'));
}

const hx = (n) => '#f2' + n.toString(16).padStart(4, '0');
const rr = (i) => i.toString(16).padStart(2, '0');

// tokens → 完全展開された配色データ（全環境がこれを消費する正典の真実）
export function expand(t) {
  const roles = t.roles;

  // --- colorMap（placeholder → CSS変数式）。base → semantic → 巡回スケール の順で構築 ---
  const pairs = [];
  for (const [k, v] of Object.entries(t.baseColorMap)) pairs.push([k, v]);
  roles.forEach((role, i) => {
    pairs.push([`#f6${rr(i)}01`, `color-mix(in srgb, var(--color-${role}) ${t.semanticTintPct}%, var(--color-base-100))`]);
    pairs.push([`#f6${rr(i)}02`, `var(--color-${role})`]);
    pairs.push([`#f6${rr(i)}03`, `var(--color-${role})`]);
    pairs.push([`#f6${rr(i)}04`, `var(--color-${role}-content)`]);
  });
  pairs.push(['#f6f001', t.muted.tint]);
  pairs.push(['#f6f002', t.muted.stroke]);
  pairs.push(['#f6f003', t.muted.text]);
  const cycle = roles.map((r) => `var(--color-${r})`);
  cycle.forEach((color, i) => { pairs.push([hx(i), color]); pairs.push([`rgb(242, 0, ${i})`, color]); });
  cycle.forEach((color, i) => {
    const tint = `color-mix(in srgb, ${color} ${t.scaleTintPct}%, var(--color-base-100))`;
    pairs.push([hx(i + 8), tint]);
    pairs.push([`rgb(242, 0, ${i + 8})`, tint]);
  });

  // --- themeVariables（mermaid の themeVariables。base + 巡回スケール） ---
  const themeVariables = { ...t.themeVariablesBase };
  for (let i = 0; i < t.cScaleCount; i++) {
    const ph = hx(i % 16);
    themeVariables['cScale' + i] = ph;
    themeVariables['pie' + (i + 1)] = ph;
  }
  for (const k of t.scaleLabelKeys) themeVariables['cScaleLabel' + k] = t.scaleLabelColor;
  for (const [k, v] of Object.entries(t.scalePeer)) themeVariables['cScalePeer' + k] = v;
  themeVariables['pieOpacity'] = t.pieOpacity;

  // --- classDef（flowchart/graph/stateDiagram に注入する pn* 定義） ---
  const classDefs = [];
  roles.forEach((role, i) => {
    const name = 'pn' + role[0].toUpperCase() + role.slice(1);
    classDefs.push(`classDef ${name} fill:#f6${rr(i)}01,stroke:#f6${rr(i)}02,color:#f10003`);
    classDefs.push(`classDef ${name}Fill fill:#f6${rr(i)}03,stroke:#f6${rr(i)}03,color:#f6${rr(i)}04`);
  });
  classDefs.push('classDef pnMuted fill:#f6f001,stroke:#f6f002,color:#f6f003');

  return {
    colorMapPairs: pairs,              // 順序つき（JS の split/join 置換用。生成時に長い順へ整列）
    colorMap: Object.fromEntries(pairs),
    themeVariables,
    classDefs,
    postReplace: t.postReplace,
  };
}

// 実行時の文字列置換で短い placeholder が長い placeholder を壊さないよう、キー長の降順に整列
export function safeOrderedPairs(pairs) {
  return [...pairs].sort((a, b) => b[0].length - a[0].length);
}
