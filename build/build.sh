#!/usr/bin/env bash
# PHPサイトを静的HTMLに変換して public/ に出力する。
# 使い方: BASE_URL=https://example.com bash build/build.sh
set -euo pipefail

cd "$(dirname "$0")/.."
ROOT="$(pwd)"
# 出力先。デプロイ(wrangler.jsonc の assets.directory)が ./public を見るのでCIでは既定のまま。
# ローカルで確認ビルドするときは OUT_DIR=/tmp/... を渡してリポジトリ内を汚さない。
OUT="${OUT_DIR:-$ROOT/public}"
PORT=8123
BASE_URL="${BASE_URL:?BASE_URL を指定してください (例: https://karin-archive.example.workers.dev)}"
BASE_URL="${BASE_URL%/}"
OLD_URL="https://karin-archive.com"

rm -rf "$OUT"
mkdir -p "$OUT"

# ---- PHP ビルトインサーバー起動 ----
php -S "127.0.0.1:$PORT" -t "$ROOT" "$ROOT/build/router.php" &>/tmp/php-server.log &
PHP_PID=$!
trap 'kill $PHP_PID 2>/dev/null || true' EXIT
for i in $(seq 1 50); do
  curl -sf -o /dev/null "http://127.0.0.1:$PORT/" && break
  sleep 0.2
done

# ---- ページ一覧(パーシャルを除く全 .php) ----
# .claude/ はClaude Codeの作業用worktree(リポジトリの複製)が入るので除外する
pages=$(find . -name '*.php' \
  ! -path './.claude/*' \
  ! -path './build/*' \
  ! -path './Spreadsheet/*' \
  ! -path './Generated/*' \
  ! -path './common/header.php' \
  ! -path './common/icons.php' \
  ! -path './common/footer.php' \
  ! -path './common/feedback.php' \
  ! -path './common/video_cards.php' \
  ! -path './common/birthday_footer.php' \
  ! -path './Birthday/birthday_generated.php' \
  ! -name '404.php' ! -name 'index.php' \
  | sed 's|^\./||; s|\.php$||' | sort)

# ---- レンダリング ----
fetch() { # $1=リクエストパス $2=出力ファイル
  mkdir -p "$(dirname "$2")"
  curl -s --fail-with-body "http://127.0.0.1:$PORT$1" -o "$2" \
    || { echo "NG: $1"; cat "$2" 2>/dev/null | head -5; exit 1; }
}

fetch "/" "$OUT/index.html"
curl -s "http://127.0.0.1:$PORT/404.php" -o "$OUT/404.html"
count=2
while IFS= read -r p; do
  fetch "/$p" "$OUT/$p.html"
  count=$((count+1))
done <<< "$pages"
echo "rendered $count pages"

# ---- 静的アセット ----
cp -r css img Timeline data "$OUT/"
cp robot.txt "$OUT/robots.txt"   # 正しいファイル名に直して配置

# ---- sitemap.xml 生成(旧sitemap.ymlのロジックを移植: common/news/404を除外) ----
{
  echo '<?xml version="1.0" encoding="UTF-8"?>'
  echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
  today=$(date +%Y-%m-%d)
  printf '  <url>\n    <loc>%s/</loc>\n    <lastmod>%s</lastmod>\n  </url>\n' "$BASE_URL" "$today"
  while IFS= read -r p; do
    case "$p" in
      news|common/*) continue ;;
    esac
    printf '  <url>\n    <loc>%s/%s</loc>\n    <lastmod>%s</lastmod>\n  </url>\n' "$BASE_URL" "$p" "$today"
  done <<< "$pages"
  echo '</urlset>'
} > "$OUT/sitemap.xml"

# ---- IndexNow キーファイル(キーが設定されているときだけ) ----
# Secret に改行や空白が紛れているとファイル名が壊れて Bing が取得できず422になるので削っておく
if [ -n "${INDEXNOW_KEY:-}" ]; then
  KEY=$(printf '%s' "$INDEXNOW_KEY" | tr -d '[:space:]')
  printf '%s' "$KEY" > "$OUT/$KEY.txt"
  echo "IndexNow キーファイルを出力した (${#KEY}文字)"
else
  echo "INDEXNOW_KEY が空なのでキーファイルを出力しない"
fi

# ---- CSSキャッシュバスティング(コミットIDをバージョンとして付与) ----
VER=$(git rev-parse --short HEAD 2>/dev/null || date +%s)
grep -rl --include='*.html' -F '/css/' "$OUT" \
  | xargs -r sed -i -E "s#(/css/[a-z-]+[.]css)\"#\1?v=$VER\"#g"   # style.css と feedback.css

# ---- ベースURL差し替え ----
grep -rl --include='*.html' --include='*.xml' --include='*.txt' -F "$OLD_URL" "$OUT" \
  | xargs -r sed -i "s|$OLD_URL|$BASE_URL|g"

# ---- _redirects 生成 ----
{
  # 旧 .htaccess 由来のレガシーURL(移動後のパスに向ける)
  echo "/karinhello.php      /X/karin-hello        301"
  echo "/karinrec.php        /YouTube/karin-rec    301"
  echo "/karinvocalo.php     /X/karin-vocalo       301"
  echo "/mlinemc.php         /YouTube/karin-mc     301"
  echo "/girlsnightoutch.php /YouTube/karin-mc     301"
  echo "/karininsta.php      /instagram/karin-insta 301"
  echo "/eyecatch.php        /YouTube/karin-eyecatch 301"
  echo "/karinsong.php       /YouTube/karin-song   301"
  echo "/index.php / 301"
  echo "/index.html / 301"
  # 改名したディレクトリ(URLに = が入ると %3D にエスケープされるため)
  # :splat を使うと、それ以降の全ルールが「動的ルール」扱いになり上限100件を超えて
  # デプロイが失敗する(Birthday の .php 転送だけで100件超)。静的ルール(上限2000)のまま
  # にするため、ワイルドカードを使わずページごとに列挙する。
  while IFS= read -r p; do
    case "$p" in
      JuiceJuice/*)
        echo "/Juice=Juice/${p##*/}.php /$p 301"
        echo "/Juice=Juice/${p##*/} /$p 301"
        ;;
    esac
  done <<< "$pages"
  # 改名したページ(実態に合わせて「インタビュー」→「インタビュー・Web記事」へ)
  echo "/hello-interview     /hello-articles       301"
  echo "/hello-interview.php /hello-articles       301"
  # 改名したページ(「テック仕事」→「バイブコーディング」へ)
  echo "/karin-tech          /karin-vibecoding     301"
  echo "/karin-tech.php      /karin-vibecoding     301"
  # 外部サイトへ移転したツール(ページ本体はリポジトリから削除済み)
  echo "/Tool/post-context     https://tools.yxmxgxn.workers.dev/post-context/ 301"
  echo "/Tool/post-context.php https://tools.yxmxgxn.workers.dev/post-context/ 301"
  # 全ページ: .php付きURL → クリーンURL
  while IFS= read -r p; do
    echo "/$p.php /$p 301"
  done <<< "$pages"
  # ディレクトリ整理で移動したページ: 旧フラットURL → 新URL(.php付きも)
  while IFS= read -r p; do
    case "$p" in
      YouTube/*|Radio/*|JuiceJuice/*|X/*|instagram/*|Tool/*)
        base="${p##*/}"
        echo "/$base /$p 301"
        echo "/$base.php /$p 301"
        ;;
    esac
  done <<< "$pages"
} > "$OUT/_redirects"

# ---- _headers 生成(旧 .htaccess の Expires 設定を再現) ----
cat > "$OUT/_headers" <<'EOF'
/css/*
  Cache-Control: public, max-age=604800
/img/*
  Cache-Control: public, max-age=604800
/Timeline/*
  Cache-Control: public, max-age=604800
EOF

kill $PHP_PID 2>/dev/null || true
trap - EXIT
echo "build complete: $OUT"
