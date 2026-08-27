#!/bin/sh
# Сжатие стилей и скриптов для выкладки.
#
# Зачем. В public/assets лежат исходники — с отступами и подробными
# комментариями, их читают люди. Посетителю комментарии не нужны, а весит
# это заметно: даже после gzip сжатые файлы вдвое легче исходных.
#
# Что делает. Кладёт сжатые копии в build/assets. Исходники не трогает —
# в репозитории остаётся то, что можно читать и править.
#
# Когда запускать. Только при сборке обновления для хостинга. Сам сайт
# ничего не собирает и никаких сторонних программ не требует: на сервер
# уезжают готовые файлы.
#
# Что нужно. esbuild — проверенный минификатор. Свой писать не стали:
# ошибка в нём тихо ломает сайт, а выигрыш тот же.
#
#     npm i --no-save esbuild
#
# Использование:
#
#     sh tools/minify.sh            # esbuild из node_modules или из PATH
#     ESBUILD=/путь/к/esbuild sh tools/minify.sh

set -e

root=$(cd "$(dirname "$0")/.." && pwd)
src="$root/public/assets"
out="$root/build/assets"

if [ -z "$ESBUILD" ]; then
    if [ -x "$root/node_modules/.bin/esbuild" ]; then
        ESBUILD="$root/node_modules/.bin/esbuild"
    else
        ESBUILD=$(command -v esbuild || true)
    fi
fi

if [ -z "$ESBUILD" ]; then
    echo "Не найден esbuild. Поставьте: npm i --no-save esbuild" >&2
    exit 1
fi

rm -rf "$out"
mkdir -p "$out/css" "$out/js"

bylo=0
stalo=0

for file in "$src"/css/*.css "$src"/js/*.js; do
    [ -f "$file" ] || continue
    name=$(basename "$file")
    case "$file" in
        */css/*) target="$out/css/$name" ;;
        *)       target="$out/js/$name" ;;
    esac

    "$ESBUILD" "$file" --minify --outfile="$target" --log-level=error

    b=$(wc -c < "$file")
    a=$(wc -c < "$target")
    bylo=$((bylo + b))
    stalo=$((stalo + a))
    printf '  %-16s %7s -> %7s\n' "$name" "$b" "$a"
done

echo
echo "  всего: $bylo -> $stalo байт (минус $(( (bylo - stalo) * 100 / bylo ))%)"
echo "  готово: $out"
echo
echo "  НЕ ЗАБУДЬТЕ: поднять assets_version в config/app.php,"
echo "  иначе браузеры возьмут прежние файлы из своей памяти."
