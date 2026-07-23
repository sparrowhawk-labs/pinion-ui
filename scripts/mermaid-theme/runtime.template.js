// このファイルは runtime.template.js（テンプレート）。sync.mjs が生成する
// mermaid-theme.js には、この内容の前に「生成物・手編集禁止」ヘッダが別途付与される。
// 正典は tokens.json（データ）+ core.mjs（展開ロジック）+ 本ファイル（ランタイム雛形）の3点セット。
// 手で直してよいのはこのテンプレートの「ランタイムのロジック」部分のみ（プレースホルダ行は触らない）。
//
// 仕組み: theme 'base' + placeholder 色（#f1/f2/f3/f4/f6 系）で mermaid に描画させ、
// 生成 SVG の placeholder を daisyUI 変数（var(--color-*)）へ文字列置換する。これで
// data-theme / data-tune の切替に図の配色が追従する。
// 記事側は flowchart / stateDiagram のノードに `:::pnPrimary` 等を付けるだけ
// （classDef は自動注入・未使用なら無害・ソースは素の mermaid としても有効なまま）。
// 使い分けルールの正典: ~/claude/docs/mermaid-design.md
//
// このファイルは各テンプレート／nonblok blade の module script 内
// （`import mermaid` の直後）にインライン展開される。
// __MM_DATA__ は sync.mjs が core.mjs の expand(tokens) 結果（JSON）で埋め込む。
// 埋め込みと展開ロジックを分離してあるのは、生成物が「バイト一致」ではなく
// 「挙動一致」で検証できるようにするため（node sync.mjs --verify）。

const __MM_DATA__ = "__MM_DATA_JSON_PLACEHOLDER__";
// __MM_DATA_END__

// core.mjs の safeOrderedPairs と同じアルゴリズム（キー長降順）。実行時の文字列置換で
// 短い placeholder が長い placeholder の部分文字列を誤って壊さないための整列
// （例: "#000" が "#000000" より先に置換されると後者が壊れる）。
function mmSafeOrderedPairs(pairs) {
  return [...pairs].sort((a, b) => b[0].length - a[0].length);
}

const MM_COLOR_MAP = mmSafeOrderedPairs(__MM_DATA__.colorMapPairs);
const MM_THEME_VARS = __MM_DATA__.themeVariables;
const MM_CLASS_DEFS = __MM_DATA__.classDefs;
const MM_POST_REPLACE = __MM_DATA__.postReplace;

// flowchart / graph / stateDiagram に pn* classDef を自動注入（未使用なら無害）
function mmInject(code) {
  if (!/^\s*(flowchart|graph|stateDiagram)/.test(code)) return code;
  return code + '\n' + MM_CLASS_DEFS.join('\n');
}

function mmEscapeRegExp(s) {
  return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function mmReplaceColors(el) {
  let h = el.innerHTML;
  for (const [k, c] of MM_COLOR_MAP) h = h.split(k).join(c);
  for (const [k, v] of MM_POST_REPLACE) h = h.replace(new RegExp(mmEscapeRegExp(k), 'gi'), v);
  el.innerHTML = h;
}

mermaid.initialize({ startOnLoad: false, theme: 'base', themeVariables: MM_THEME_VARS });

// pie のスライス上ラベル（%）: base-content のままだと彩度の高いスライス上で
// コントラスト不足＆weight 400 で細い → 白・太字＋薄い暗色縁取り（paint-order）で
// どのテーマ色・light/dark でも読めるようにする（inline 指定に勝つため !important）
{
  const st = document.createElement('style');
  st.textContent = '.mermaid svg text.slice{fill:#fff !important;font-weight:700 !important;paint-order:stroke;stroke:rgba(0,0,0,.35);stroke-width:2px;}';
  document.head.appendChild(st);
}

// 核: 渡されたノード群を 注入 → 描画 → 色置換
window.__renderMermaidNodes = async (nodes) => {
  if (!nodes.length) return;
  for (const n of nodes) n.textContent = mmInject(n.textContent.trim());
  try { await mermaid.run({ nodes }); } catch (e) {}
  for (const n of nodes) mmReplaceColors(n);
};
// 便利形: scope 内の未描画ノード全部
window.__renderMermaid = (scope) =>
  window.__renderMermaidNodes([...(scope || document).querySelectorAll('.mermaid:not([data-processed])')]);

window.dispatchEvent(new Event('mermaid-ready'));
