# 設計メモ: daisyUI を「色／テーマだけ」に絞る（コンポーネントクラスを露出させない）

> ステータス: **pinion-ui 側 実装済み（2026-06-12）**。検証結果と最終形を下に記録。visualize 側の残タスクは末尾。
> 作成: 2026-06-12（visualize 開発中に発見）

## 何が問題だったか

pinion-ui の設計思想は「**daisyUI = 色／テーマトークンの供給源、コンポーネントは pinion-ui**」。
ところが導入手順とインストーラ（`UiInstall.php`）が host アプリに daisyUI の**フルプラグイン**を読み込ませていたため、
`.badge` `.btn` `.alert` 等の daisyUI コンポーネントクラスが host で全部使えてしまい、pinion-ui-first 原則が構造的に破れていた。

## 監査結果（当初の前提の訂正）

「pinion-ui 本体は utilities only（theme-only で足りる）」という当初の見立ては**部分的に誤り**。全 46 コンポーネント＋Compose 層＋tune.css を精査した結果:

- **utilities + カラートークンのみ**: button / alert / card / badge / avatar / tabs / checkbox / radio / toggle / dropdown / select / input / textarea / modal / popover / sidebar / tooltip(v0.3.11 以降) ほか大多数
- **daisyUI コンポーネントクラスに実依存（15 components + 1 utility）**:
  `avatar`(avatar-group) / `badge`(IndicatorComposer の badge-\*) / `breadcrumbs` / `collapse` / `divider` / `indicator` / `kbd` / `loading`(spinner) / `mask`+`rating` / `progress` / `range` / `skeleton` / `stat` / `timeline` ＋ utility `join`(pagination)
- **死んだ依存**: pinion-ui.css の `tooltip-light`/`tooltip-base-*` パッチ群（v0.3.11 の tooltip Alpine 化後の遺物）→ 今回削除
- tune.css の daisyUI コンポーネントセレクタ依存: **ゼロ**（確認済み）

→ よって「theme-only（`daisyui/theme` のみ）」案（旧・提案1）は不成立。正解は**フルプラグイン + `exclude:` リスト**。

## 検証結果（旧「最重要要検証」への回答）

daisyUI 5 ソース（`daisyui/index.js` / `functions/pluginOptionsHandler.js`）で確認:

- `include`/`exclude` は base / components / utilities のパーツ名フィルタ。**テーマ適用（addBase）と Tailwind theme 拡張（`--color-*` 登録 → `bg-primary` 等のユーティリティ生成）は `plugin.withOptions` の第2引数で無条件に行われ、include/exclude の影響を受けない**。
  → exclude しても `bg-primary` / `text-primary-content` / 全 35 テーマは完全に残る。実ビルドでも確認済み。
- daisyUI 5 + Tailwind v4 ではコンポーネント CSS も使用検出ベースで出力される（ソースに `.btn` が無ければフルプラグインでも出ない）。exclude の意味は「**host が手書きしても生成されない**」という強制力。

## 実装（pinion-ui repo, 2026-06-12）

1. **`src/resources/css/pinion-ui.css`** — `@plugin "daisyui" { themes: all; exclude: <43 components>; }` を**プリセット自身が宣言**（host の app.css から daisyUI 参照が消える）。exclude リストはパッケージがバージョンと共に管理 — コンポーネントが daisyUI クラスを使い始め/やめたら同一コミットで更新。死んだ tooltip パッチ削除。
2. **`src/Commands/UiInstall.php`** — プラグイン行の「追加」を廃止し、host app.css の standalone `@plugin "daisyui"`（`daisyui/theme` ブロックは温存）を**除去する migration** に変更。冪等。
3. **CLAUDE.md** — Architecture invariant #6 として明文化。**AGENTS.md** — consumer 向け gotcha 先頭に「daisyUI コンポーネントクラスはビルドに存在しない」を追加、stale な tooltip-light 記述を訂正。**README.md** / **SEMVER.md**（v0.4.2 unreleased エントリ）更新。

## 後方互換

二重ロード（host に旧フルプラグイン行が残ったまま新プリセットを読む）は**フルプラグインが勝つ**ことを実ビルドで確認 → composer update だけでは何も壊れない。enforcement は `ui:install` 再実行（または手で行を削除）した時点から効く。

## 検証ログ（pinion-ui-llm-test, 2026-06-12）

- プローブ blade（`.btn` `.card` `.modal` `.navbar` `.toggle` `.tooltip` 等 27 クラス手書き）→ migration 後ビルドで **excluded 13 種すべて CSS 出力ゼロ**
- kept 15 + `join` すべて出力あり。`bg-primary` / `text-primary-content` / `data-theme=dracula`（themes: all）/ `data-theme=pinion` 残存
- `php tests/Compose/run.php` 299 pass / `ui:install` 再実行で冪等 / dashboard 実描画正常（スクショ確認）

## 残タスク（2026-06-12 更新）

- ✅ **済**: IndicatorComposer の `badge-*` をユーティリティ化（badge.blade.php と同じ appearance × color 文法）→ exclude に `badge` 追加。host の `.badge` 手書きも封じた。fixture 再生成・299 pass。
- ✅ **済**: NADI（`~/project/nadi-drone-platform`）grep 監査 — daisyUI コンポーネントクラスの直書き**ゼロ**（`select-none`×2 は Tailwind 標準）。app.css:2 の旧プラグイン行は v0.4.2 リリース後に `ui:install` 再実行で除去（v0.4.0 利用中に手で消すと色ごと消えるので触らない）。
- ⬜ **進行中**: playground の raw daisyUI クラス置換（`btn`×42 等）→ プラグイン行削除（別エージェントで実施中）。
- ⬜ v0.4.2 のタグ・リリース（コミットは c12d59f で main に積み済み。リリースは保留中 — playground 移行完了後に判断）。

## visualize 側の追従（別レイヤーだが同根）

visualize は standalone HTML 生成器（Blade 不可）。pinion-ui と同じ「ユーティリティ＋トークン」流儀に寄せる:
- ✅ **済**: 生成コード内の daisyUI コンポーネントクラス（`lib/eval-app.js` の判定/アクションボタン9個・`templates/report.html` の gallery 選択バー2個）を **Tailwind ユーティリティ＋トークン**へ置換（`bg-primary text-primary-content rounded-lg ...`）。`form.html` は元々クリーン（`btn` は変数名）。`@source "./build/page.html"` のおかげで `<script>` 内 literal のユーティリティも生成される（CSS 追加ゼロ）。
- ✅ **済**: visualize スキル doc から「daisyUI コンポーネント自由併用可」のガイドを撤回。
- ✅ **済（2026-06-12）**: `entry.css` の standalone `@plugin "daisyui"` 行を削除 — entry.css は pinion-ui preset を直接 @import しているので、preset 側の宣言（themes: all + exclude）にそのまま乗る。再ビルドで `.btn` 等の不在・`bg-primary`/全テーマ残存を確認（`.card`/`.badge` の生成 CSS は visualize 固有 bespoke クラスで daisyUI ではない）。
- ⬜ **未（任意・大）**: bespoke CSS（`.card`/`.eval-*`/`.viz-gallery` 等 ~375 行・既にトークン駆動）の段階的ユーティリティ化 or `@apply` 合成。
