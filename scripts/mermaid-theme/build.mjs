#!/usr/bin/env node
// build.mjs — tokens.json + core.mjs + runtime.template.js から配色グルーの生成物を
// ビルドし、pinion-ui の `dist/` へ書き出す。
//
// 使い方:
//   node scripts/mermaid-theme/build.mjs
//     → dist/mermaid-theme.js   （ランタイム JS。visualize / nonblok が読む）
//       dist/mermaid-theme.json （展開済み JSON。sologentic の MermaidRenderer が読む）
//
// これは旧 sync.mjs の「ビルド部分だけ」を移植したもの。配布（targets.json への
// 書き込み）・ドリフト検出（--check）・自己整合診断（--verify）・pinion-ui HEAD 照合
// （D8）は廃止した。消費側は composer + git tag + lockfile（visualize はローカル
// clone の dist/ 直読み）で受け取る＝自作の配布機構は持たない。
//
// 正典は tokens.json（データ）+ core.mjs（展開ロジック）+ runtime.template.js
// （ランタイム雛形）の3点セット。dist/ の2ファイルは生成物なので手編集禁止。

import fs from 'fs';
import path from 'path';
import crypto from 'crypto';
import url from 'url';
import { loadTokens, expand } from './core.mjs';

const HERE = path.dirname(url.fileURLToPath(import.meta.url));
const TOKENS_PATH = path.join(HERE, 'tokens.json');
const TEMPLATE_PATH = path.join(HERE, 'runtime.template.js');
const DIST_DIR = path.join(HERE, '..', '..', 'dist');

const DATA_START_MARKER = 'const __MM_DATA__ = ';

function md5(s) {
  return crypto.createHash('md5').update(s).digest('hex');
}

function buildData() {
  const tokensRaw = fs.readFileSync(TOKENS_PATH, 'utf8');
  const tokens = loadTokens(TOKENS_PATH);
  const data = expand(tokens);
  return { tokens, tokensRaw, data };
}

function buildMermaidThemeJs(data, tokensRaw) {
  const template = fs.readFileSync(TEMPLATE_PATH, 'utf8');
  const placeholderLine = `${DATA_START_MARKER}"__MM_DATA_JSON_PLACEHOLDER__";`;
  if (!template.includes(placeholderLine)) {
    throw new Error(
      `runtime.template.js にプレースホルダ行が見つからない（期待: ${placeholderLine}）`
    );
  }
  const header =
    '// 生成物・手編集禁止。正典= scripts/mermaid-theme/tokens.json\n' +
    '// （+core.mjs=展開ロジック +runtime.template.js=ランタイム雛形）。\n' +
    '// 再生成= node scripts/mermaid-theme/build.mjs\n' +
    `// 生成元 tokens.json md5: ${md5(tokensRaw)}\n`;
  const replaced = template.replace(
    placeholderLine,
    `${DATA_START_MARKER}${JSON.stringify(data)};`
  );
  return header + replaced;
}

function buildGeneratedJson(data, tokensRaw) {
  const payload = {
    _doc:
      '生成物・手編集禁止。正典= scripts/mermaid-theme/tokens.json。' +
      '再生成= node scripts/mermaid-theme/build.mjs',
    colorMap: data.colorMap,
    themeVariables: data.themeVariables,
    classDefs: data.classDefs,
    postReplace: data.postReplace,
    version: md5(tokensRaw),
  };
  return JSON.stringify(payload, null, 2) + '\n';
}

function main() {
  const { data, tokensRaw } = buildData();
  const mermaidThemeJs = buildMermaidThemeJs(data, tokensRaw);
  const generatedJson = buildGeneratedJson(data, tokensRaw);

  fs.mkdirSync(DIST_DIR, { recursive: true });
  const jsPath = path.join(DIST_DIR, 'mermaid-theme.js');
  const jsonPath = path.join(DIST_DIR, 'mermaid-theme.json');
  fs.writeFileSync(jsPath, mermaidThemeJs);
  fs.writeFileSync(jsonPath, generatedJson);

  console.log('ビルドしました:');
  console.log('  ' + jsPath);
  console.log('  ' + jsonPath);
}

main();
