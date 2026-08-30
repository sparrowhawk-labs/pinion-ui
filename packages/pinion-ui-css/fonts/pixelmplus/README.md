PixelMplus（ピクセル・エムプラス）
=================================

Copyright (C) 2013 itouhiro  
Copyright (C) 2002-2013 M+ FONTS PROJECT

![on Windows](misc/pixelmplus-20130602124852.png)


Description
-----------

8bit風味のファミコンのビットマップフォントのような感じを出せる
TrueTypeアウトラインフォントです。


Feature
-------

- ビットマップフォントのように見えるが、アウトラインのみのTrueTypeフォント。
  埋め込みビットマップは なし。ボールド体（太字）あり。

- JIS第1・第2水準のすべての漢字を収録。ISO-8859-1(Latin-1)の文字も収録。
  そのほかにもいくつかの記号を追加。

- 実体は単なるTrueType等幅フォント。テキストエディターで使用することもできる。

- ライセンスは自由なM+ FONT LICENSE。

- PixelMplus12 と PixelMplus10　の2種類のフォントがあります。
  元にしたビットマップフォントが 12ピクセルであるか、10ピクセルであるか、の点が違います。


詳しくは以下を参照。  
http://itouhiro.hatenablog.com/entry/20130602/font


Thanks
------

PixelMplusは、ビットマップフォント [M<sup>+</sup> BITMAP FONTS](http://mplus-fonts.sourceforge.jp/mplus-bitmap-fonts/) をアウトライン化したフォントです。
M+ FONTS PROJECTの皆さんありがとうございます。


License
-------

M+ FONT LICENSE

M+ FONT LICENSEについては、配布物に含まれる
[mplus_bitmap_fonts/LICENSE_E](misc/mplus_bitmap_fonts/LICENSE_E)
をご覧ください。

woff2 delivery (pinion-ui)
--------------------------

pinion-ui v0.11.0以降、配信用に TTF から woff2 を生成して同梱しています
（fonttools/pyftsubset による形式変換・グリフは削っていません）。
同梱は **PixelMplus10 のみ**です — pixel tune の書体統一（2026-08-30
オーナー決定）により PixelMplus12 は同梱をやめました（上流
itouhiro/PixelMplus からいつでも取得可能）。元TTF もリポジトリからは
削除済みで、woff2（全グリフ保持）と git 履歴・上流が原本です:

- `*.woff2` — 全グリフ保持（JIS第1・第2水準の全漢字を含む、TTFと同内容）
- `*.latin.woff2` — 欧文サブセット（U+0000-00FF, U+2000-206F のみ・
  unicode-range 分割による遅延読み込み用。全グリフ版と常にペアで宣言され、
  収録範囲外の文字は全グリフ版が描画するため文字が欠けることはない）

いずれも元フォントと同じ M+ FONT LICENSE（同梱の LICENSE）の下で
再配布しています。改変（形式変換・サブセット化）は同ライセンスにより
許諾されています。
