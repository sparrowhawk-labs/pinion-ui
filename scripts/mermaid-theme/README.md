# mermaid-theme — 配色グルーの単一ソース（pinion-ui 同梱）

mermaid 図と自作 SVG の**配色グルー**（pn クラス名・placeholder→CSS変数マップ・
color-mix 式・themeVariables）を1箇所に集約したもの。**ここを直して build すれば、
pinion-ui を利用する全環境（visualize / nonblok / sologentic）へ波及**する。

配色の意味規約＝ `~/claude/docs/mermaid-design.md`、媒体選択（mermaid か SVG か）＝
`~/claude/docs/diagram-medium-choice.md`、md がツールを跨いで可搬になる契約全体＝
`~/claude/docs/content-contract.md`。

## ファイル

| ファイル | 役割 | 手編集 |
|---|---|---|
| `tokens.json` | **ノブ（唯一の真実のデータ）** — roles・色マップ・tint%・themeVariables 等。**「ルールを直す」＝ここを直す** | ✅ ここを編集 |
| `core.mjs` | 展開ロジック（`expand(tokens)` → `{colorMap, themeVariables, classDefs, postReplace}`）。「どう展開するか」を知る唯一のコピー | 稀（仕組み変更時のみ） |
| `runtime.template.js` | ブラウザ/PHP ランタイムの雛形。`__MM_DATA__` プレースホルダを `build.mjs` が埋めて生成物を作る | 稀（ランタイム挙動を変える時のみ） |
| `build.mjs` | tokens + core + template → `dist/` の生成物2つを出力するビルダー | — |

生成物（リポ直下 `dist/`）:

| ファイル | 消費側 |
|---|---|
| `dist/mermaid-theme.js` | visualize（ローカル clone の dist を直読み）＋ nonblok（`vendor/.../dist` を request 時 inline） |
| `dist/mermaid-theme.json` | sologentic（`vendor/.../dist` をビルド時に読む PHP レンダラ） |

`dist/` の2ファイルは**生成物・手編集禁止**。正典は `tokens.json`（データ）+ `core.mjs`
（展開ロジック）+ `runtime.template.js`（ランタイム雛形）の3点セット。

## 波及の流れ（版固定モデル・2026-07-23〜）

```
tokens.json ──expand()──►  dist/mermaid-theme.js   → visualize + nonblok
 (ここを編集)  (core.mjs +   dist/mermaid-theme.json → sologentic（PHP はこれを消費するだけ）
              runtime.template.js)

 node scripts/mermaid-theme/build.mjs = dist/ を再生成
```

以前は「正典のコピーを各リポへ配る」自作機構（sync.mjs / targets.json / --check /
--verify / D8 drift 照合）を持っていたが、複雑さの根因になったため**廃止**した。
消費側は **composer + git tag + lockfile** の世界標準だけで受け取る（visualize は
非 composer のローカルツールなので、ローカル clone の `dist/` を直読みする例外）。

## 運用手順（直す → build → タグ → 各アプリ composer update）

1. `tokens.json` を編集（色・tint%・themeVariables・postReplace 等）。
2. `node scripts/mermaid-theme/build.mjs` を実行 → `dist/mermaid-theme.js` と
   `dist/mermaid-theme.json` を再生成・上書き。
3. pinion-ui を commit → **新しい git tag を打って push**（例: `v0.10.x`）。
4. 各 composer 利用アプリ（nonblok / sologentic）で `composer update sparrowhawk-labs/pinion-ui`
   → lockfile が新タグを固定。visualize はローカル clone を最新 main に置いておけば
   `dist/` 直読みで即反映（build も composer update も不要）。
5. 各環境で実際に mermaid 図を開いて目視確認（visualize=Browser pane、
   nonblok=資料ページ、sologentic=記事プレビュー）。

## 検証（現行挙動の完全再現）

`expand(tokens)` の展開結果を確認：

```sh
node -e "import('./core.mjs').then(m=>{const e=m.expand(m.loadTokens());console.log('colorMap',Object.keys(e.colorMap).length,'themeVars',Object.keys(e.themeVariables).length,'classDefs',e.classDefs.length)})"
# → colorMap 94 / themeVars 68 / classDefs 17
```

## 注意

- `tokens.json` を直したら `node scripts/mermaid-theme/build.mjs` を実行するまで
  `dist/` には反映されない（ビルド時波及・ライブ同期ではない）。
- `dist/mermaid-theme.js` / `dist/mermaid-theme.json` は**生成物**。手編集禁止。
