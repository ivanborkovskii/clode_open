<?php
/**
 * Работа с текстом статей: адрес из заголовка, текст без разметки,
 * оценка времени чтения и безопасный вывод написанного в админке.
 */

declare(strict_types=1);

namespace App\Core;

final class Text
{
    /** Кириллица в латиницу — по той же схеме, что уже в адресах сайта. */
    private const TRANSLIT = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];

    /**
     * Теги, разрешённые в тексте статьи.
     *
     * Список закрытый: даже если в админку кто-то попадёт, через текст
     * статьи нельзя будет вставить на сайт чужой скрипт или рамку.
     */
    private const ALLOWED = '<p><br><strong><b><em><i><u><s><h2><h3><h4>'
        . '<ul><ol><li><blockquote><a><img><figure><figcaption>'
        . '<table><thead><tbody><tr><th><td><hr><code><pre>';

    public static function slug(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, self::TRANSLIT);
        $value = preg_replace('/[^a-z0-9]+/u', '-', $value) ?? '';

        return trim($value, '-');
    }

    /** Текст без разметки: для поиска, анонса и подсчёта времени чтения. */
    public static function plain(string $html): string
    {
        $text = preg_replace('#<(script|style)\b.*?</\1>#is', ' ', $html) ?? $html;
        // Теги заменяем пробелом, а не убираем: иначе конец абзаца
        // склеивается с началом следующего — «продажи.Что дальше».
        $text = preg_replace('/<[^>]*>/', ' ', $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Оценка времени чтения в минутах.
     *
     * Взята привычная скорость чтения — около 180 слов в минуту.
     * Это подсказка автору: в админке значение можно поправить руками.
     */
    public static function readingTime(string $html): int
    {
        $words = preg_split('/\s+/u', self::plain($html)) ?: [];

        return max(1, (int) ceil(count(array_filter($words)) / 180));
    }

    /** Короткий анонс из текста статьи, если автор не написал свой. */
    public static function excerpt(string $html, int $limit = 220): string
    {
        $text = self::plain($html);

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut   = mb_substr($text, 0, $limit);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space === false ? $cut : mb_substr($cut, 0, $space), " ,.;:—-") . '…';
    }

    /**
     * Текст статьи для вывода на страницу: остаются только разрешённые
     * теги, а из них убираются обработчики событий и ссылки вида
     * javascript: — то, чем разметку превращают в исполняемый код.
     */
    public static function safeHtml(string $html): string
    {
        $html = strip_tags($html, self::ALLOWED);
        $html = preg_replace('/\s on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*(\2)/i', '$1=$2#$2', $html) ?? $html;

        return $html;
    }

    /**
     * Ссылки в тексте статьи открываются в новой вкладке.
     *
     * Человек читает статью; уводить его с неё по ссылке — значит
     * прервать чтение. В новой вкладке он посмотрит, что там, и вернётся
     * к тексту, который остался на месте.
     *
     * Не трогаются три вида ссылок:
     *   * якоря (href="#...") — это переход внутри той же страницы,
     *     например кнопка «Оставить заявку» до формы внизу. Новая
     *     вкладка сломала бы её;
     *   * почта и телефон — их открывает не браузер, а почтовая
     *     программа или звонилка, пустая вкладка осталась бы висеть;
     *   * ссылки, которым вкладку уже указали руками в админке.
     *
     * rel="noopener" — чтобы открытая страница не получила доступ
     * к вкладке, из которой её открыли.
     */
    public static function newTab(string $html): string
    {
        return preg_replace_callback('/<a\b[^>]*>/i', static function (array $m): string {
            $tag = $m[0];

            if (preg_match('/\btarget\s*=/i', $tag)) {
                return $tag;
            }

            if (!preg_match('/\bhref\s*=\s*("|\')(.*?)\1/is', $tag, $href)) {
                return $tag;
            }

            $address = trim(html_entity_decode($href[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($address === '' || str_starts_with($address, '#')
                || preg_match('/^(mailto|tel):/i', $address)
            ) {
                return $tag;
            }

            $tag = preg_replace('/\s*\/?>$/', '', $tag) ?? $tag;

            // Если у ссылки уже есть rel, дописываем в него, а не рядом:
            // два одинаковых атрибута — сломанная разметка.
            if (preg_match('/\brel\s*=\s*("|\')(.*?)\1/is', $tag, $rel)) {
                if (!preg_match('/\bnoopener\b/i', $rel[2])) {
                    $tag = str_replace($rel[0], 'rel="' . trim($rel[2] . ' noopener') . '"', $tag);
                }

                return $tag . ' target="_blank">';
            }

            return $tag . ' target="_blank" rel="noopener">';
        }, $html) ?? $html;
    }

    /**
     * Тема статьи так, как она звучит после слова «по»: из заголовка
     * «Интеграция Битрикс24 и Телеграм» получается «по интеграции
     * Битрикс24 и Телеграм».
     *
     * Склонять русские слова по правилам приложение не умеет и не должно —
     * для этого нужна морфология. Поэтому берётся только первое слово
     * заголовка, и только если оно есть в списке (он лежит в текстах
     * раздела и пополняется без правки кода). Всё, что после первого
     * слова, переносится как есть — там обычно названия систем, которые
     * не склоняются: «Битрикс24 и Телеграм».
     *
     * Если первое слово незнакомое, возвращается пустая строка: лучше
     * показать обычный заголовок формы, чем неграмотный.
     *
     * @param array<string, string> $words первое слово => форма после «по»
     */
    public static function topic(string $title, array $words): string
    {
        // Двоеточие и тире отделяют тему от пояснения:
        // «Битрикс24: что это такое и для чего он бизнесу».
        $topic = trim((string) preg_split('/\s*[:—–]\s*/u', trim($title))[0]);

        if ($topic === '') {
            return '';
        }

        $parts = preg_split('/\s+/u', $topic, 2) ?: [];
        $first = mb_strtolower($parts[0] ?? '');
        $rest  = trim($parts[1] ?? '');

        if (!isset($words[$first])) {
            return '';
        }

        return trim($words[$first] . ' ' . $rest);
    }

    /**
     * Кусок текста вокруг первого найденного слова — для подсказок поиска.
     *
     * Нужен, когда слово встретилось только в тексте статьи: показывать
     * анонс, в котором искомого слова нет, значит оставить человека
     * гадать, почему статья попала в список.
     *
     * @param array<int, string> $words
     */
    public static function snippet(string $text, array $words, int $radius = 70): string
    {
        $at = false;

        foreach ($words as $word) {
            $found = mb_stripos($text, $word);

            if ($found !== false && ($at === false || $found < $at)) {
                $at = $found;
            }
        }

        if ($at === false) {
            return self::excerpt($text, $radius * 2);
        }

        $from   = max(0, $at - $radius);
        $length = $radius * 2 + mb_strlen($words[0] ?? '');
        $piece  = mb_substr($text, $from, $length);

        // Обрезаем по границам слов: обрывок «…лку. Что происходит»
        // читается как ошибка вёрстки.
        if ($from > 0) {
            $space = mb_strpos($piece, ' ');
            $piece = $space === false ? $piece : mb_substr($piece, $space + 1);
        }

        if ($from + $length < mb_strlen($text)) {
            $space = mb_strrpos($piece, ' ');
            $piece = $space === false ? $piece : mb_substr($piece, 0, $space);
        }

        return ($from > 0 ? '…' : '') . trim($piece)
            . ($from + $length < mb_strlen($text) ? '…' : '');
    }

    /**
     * Число со словом в нужной форме: 1 оценка, 2 оценки, 5 оценок.
     *
     * Правило русского языка, а не «оценок(и)» в скобках: на странице
     * это видно и читается как небрежность.
     */
    public static function plural(int $count, string $one, string $few, string $many): string
    {
        $mod100 = $count % 100;
        $mod10  = $count % 10;

        if ($mod100 >= 11 && $mod100 <= 14) {
            return $many;
        }

        return match (true) {
            $mod10 === 1 => $one,
            $mod10 >= 2 && $mod10 <= 4 => $few,
            default => $many,
        };
    }

    /**
     * Цена по-русски: 13990 → «13 990 ₽».
     *
     * Разряды разделены неразрывным пробелом, и перед знаком рубля он же:
     * иначе строка переносится посреди числа — «13» на одной строке,
     * «990 ₽» на следующей.
     */
    public static function money(int $rub): string
    {
        return number_format($rub, 0, '', "\u{00A0}") . "\u{00A0}₽";
    }

    /** Дата по-русски: 16 апреля 2026. */
    public static function date(string $date): string
    {
        static $months = [
            1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
            'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
        ];

        $time = strtotime($date);

        if ($time === false) {
            return $date;
        }

        return (int) date('j', $time) . ' ' . $months[(int) date('n', $time)] . ' ' . date('Y', $time);
    }

    /**
     * Автор статьи разбирается на имя и должность.
     *
     * В админке автор пишется одной строкой: «Иван Борковский, основатель
     * компании». Отдельных полей нет намеренно — заполнять два поля ради
     * одной строки неудобно. Разделяет первая запятая: в должности она
     * встречается, в имени — нет.
     *
     * Разбор нужен в двух местах — под заголовком статьи и в микроразметке.
     * Поэтому он здесь, а не в каждом по отдельности.
     *
     * @return array{name: string, role: string}
     */
    public static function person(string $author): array
    {
        $author = trim($author);
        $comma  = mb_strpos($author, ',');

        if ($comma === false) {
            return ['name' => $author, 'role' => ''];
        }

        return [
            'name' => trim(mb_substr($author, 0, $comma)),
            'role' => trim(mb_substr($author, $comma + 1)),
        ];
    }
}
