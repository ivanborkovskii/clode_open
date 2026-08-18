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
}
