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

## 構造的強制 — 要約常駐 ＋ lint（2026-06-13 追加）

「規約がポインタ参照だとスルーされる（"知ってるつもり"で daisyUI コンポーネントクラスを書いてしまう）」問題への恒久対策。**ポインタは自己評価依存で、誤った既知感のときに沈黙する＝重要規約ほどポインタでは守れない**。対策は2点で十分（当初足した "familiarity trap" メタルールは過剰として撤去 — 下記2点があれば心理的メタルールは不要）:

1. **要約常駐**: `ui:install` が `CLAUDE_SNIPPET.md`（規約の要約本体）を host の `CLAUDE.md` に注入 → 常時ロード。要約は非網羅でよく、**ここに無いことは詳細版（`AGENTS.md → "Class vocabulary"`）が正典**とすればよい（要約末尾にその1行だけ明記）。ポインタを"判断で開く"のではなく、規約本体が最初から context にある状態にするのが肝。
2. **lint（`php artisan ui:lint`）= 機械的 backstop**:
   - 実体: `src/Linting/ClassVocabularyLinter.php`（**純PHP・Laravel非依存**でCI/単体テスト可）＋ thin wrapper `src/Commands/UiLint.php`（Provider 登録済み）。
   - 検出: ①excluded daisyUI コンポーネントクラス（`exclude:` リストが真実源。daisyUI パーツ名→実クラス名をマップ：`button`→`btn`、`fileinput`→`file-input` 等）②固定パレット/hex 色（`bg-blue-500`/`bg-[#1d4ed8]` ＝ `data-theme` 無視）。
   - **誤検出回避**（設計の肝）: `select-none/text/all/auto`（Tailwind user-select）、`list-disc` 等（Tailwind）、`table-auto/fixed`（Tailwind）、`collapse`/`filter`（Tailwind/kept）、KEPT daisyUI（progress/timeline/range/stat/indicator/avatar/divider/kbd/loading/skeleton/mask/rating/breadcrumbs/join）は flag しない。`hero`/`footer`/`link`/`label`/`list`/`table` の bare も flag せず、明白な daisyUI 複合形（`hero-content`/`table-zebra`/`list-row` 等）のみ。
   - 対応: static `class="…"`／dynamic `:class`・`@class([…])`／variant prefix（`hover:`/`md:`）。`pinion-lint-ignore`（同行 or 直上行）で抑制。
   - 終了コード非ゼロ → CI / pre-commit / Claude Code PostToolUse hook を gate 可能。`--json` あり。
   - テスト: `tests/Lint/run.php` 38 ケース（衝突・KEPT・bare・ignore・dynamic 網羅）green。`composer test` は Compose 299 + Lint 38、`composer lint` は Lint のみ。
   - **実地検証**: NADI vendor へ一時同期して `ui:lint` 実行 → 15 ビュー中、検出は未使用の `welcome.blade.php`（Laravel 標準・hex 70件）のみ＝**true positive**。私が作った実アプリ 14 ビューは誤検出ゼロ。検証後 vendor は復元済み。

3. **lint-after-edit hook（2026-06-14 追加）= lint結果をメインエージェント context へ自動注入**:
   - **動機**: lint を PostToolUse hook で素朴に走らせるだけだと**結果がモデルに届かない**。Claude Code は PostToolUse hook の出力を、**exit 0 ＋ stdout の JSON（`hookSpecificOutput.additionalContext`）の時だけモデル context に注入**する。非ゼロ終了＋stdout はモデルからは捨てられ、ユーザーにしか出ない（公式仕様、claude-code-guide で確認）。今の `ui:lint` は違反を stdout に出し exit 1 なので、そのまま hook 登録すると私には届かない罠。
   - **実体**: `stubs/hooks/lint-blade.php`（**純PHP・jq不要**）。stdin の `tool_input.file_path` を読み、`.blade.php` のみ対象、`CLAUDE_PROJECT_DIR` 配下で `php artisan ui:lint <file>` 実行、違反あれば **exit 0 ＋ additionalContext JSON** を出力（無ければ無出力 exit 0）。vendor に pinion-ui が無い/`artisan` 不在なら no-op。
   - **設置**: `ui:install` が `installLintHook()` で①`.claude/hooks/lint-blade.php` をコピー②`.claude/settings.json` の `PostToolUse`(`Edit|Write`) に冪等登録。`--ai`/confirm で gate、`--skip-hooks` で抑止。
   - **symlink 罠＋shell ガード**: blueprint-flow の `.claude/settings.json` は共有スキャフォールドへの symlink。`File::put` は辿って共有側に書く（＝全アプリに登録が乗る）。なのでコマンドは `test -f "$CLAUDE_PROJECT_DIR/.claude/hooks/lint-blade.php" && php ... || true` の**shell ガード付き**にし、スクリプト未配置アプリでの毎編集エラーを防止。
   - **実地検証**: ①hook 単体 e2e（NADI welcome.blade.php → exit 0＋valid additionalContext JSON 11406字／クリーンビュー→無出力／非Blade→無出力）。②`ui:install` 実地（NADI の symlink を一時実ファイル化して共有スキャフォールド非汚染で実行 → script 配置・PostToolUse 登録・2回目冪等[entry数1]を確認 → symlink 復元）。

4. **blueprint-flow スキャフォールド統合（2026-06-16）= 新規 bpf アプリへ最初から hook**:
   - bpf の `create_claude_symlinks` は dev/deployed 両方すでに `for item in skills agents hooks CLAUDE.md settings.json` を含み、`if [[ -e scaffold/hooks ]]` でスキャフォールドに `hooks/` があれば app の `.claude/hooks` へ symlink する。**→ bpf 本体は無改修**（編集済み bpf を壊さない）。
   - **両スキャフォールド**に設置（独立 git・自動 deploy 無しのため両方）: `~/project/blueprint-flow/.claude.tall-pinion/`(dev) と `~/.blueprint-flow/.claude.tall-pinion/`(deployed・default `bpf` が使う) の各 `hooks/lint-blade.php`（pinion-ui stub のコピー）＋ `settings.json` に PostToolUse 登録。
   - **実証**: throwaway dir で本物 `bpf init tall-pinion` → `.claude/hooks`＋`.claude/settings.json` が symlink 解決・登録あり。pinion-ui 未 vendoring の fresh app では hook は安全 no-op（exit 0/無出力）。
   - **ui:install 側の共存ガード追加**: `.claude/hooks`（or script）が **symlink なら scaffold 管理とみなし上書きしない**（`is_link()` チェック）。bpf アプリで `ui:install` を再実行しても共有スキャフォールドの `lint-blade.php` を clobber しない。registration も settings symlink 経由で既にあるため `$already` で冪等 skip。
   - **同期注意**: dev↔deployed は手動コピー。片方を再生成する時は `hooks/` と `settings.json` も運ぶ（[[project_blueprint_flow_two_homes]]）。

## 残タスク（2026-06-12 更新）

- ✅ **済（2026-06-13）**: 構造的強制（メタルール＋`ui:lint`）導入（上記）。CHANGELOG Unreleased / README "Linting the class vocabulary" 追記済み。
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
