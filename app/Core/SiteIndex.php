<?php
/**
 * Сборка поискового указателя по страницам сайта.
 *
 * Тексты всех постоянных страниц лежат в config/content — там же, откуда
 * их берёт сам сайт. Поэтому указатель собирается не обходом страниц
 * по HTTP, а чтением этих файлов: каждый из них разворачивается в одну
 * длинную строку текста, по которой потом и идёт поиск.
 *
 * Второго списка страниц здесь нет намеренно. Какая страница каким файлом
 * управляется, знают реестры разделов; сюда этот список приходит готовым —
 * тем же, по которому строится карта сайта. Новая услуга появляется
 * в поиске сама, править этот файл не нужно.
 *
 * Статьи сюда не попадают: они лежат в базе, и их забирает оттуда
 * SearchRepository при обновлении указателя.
 */

declare(strict_types=1);

namespace App\Core;

final class SiteIndex
{
    /**
     * Ключи, под которыми лежит не текст, а служебные данные: адреса,
     * имена файлов, значки, размеры картинок. В указателе им не место —
     * иначе страница находилась бы по куску собственного адреса.
     *
     * Описания картинок (alt) тоже пропускаем: они повторяют то, что уже
     * сказано в тексте рядом, и на каждый снимок добавляют в указатель
     * одни и те же слова.
     */
    private const SKIP = [
        'href', 'url', 'src', 'file', 'icon', 'origin', 'slug', 'view',
        'key', 'kind', 'shot', 'cols', 'alt', 'id', 'anchor', 'class',
        'group', 'parent', 'content', 'phone_href', 'mail_href',
    ];

    /**
     * Подпись раздела и вес по началу адреса. Вес решает порядок выдачи
     * при равном совпадении: страница услуги отвечает на вопрос точнее,
     * чем статья о том же.
     */
    private const BRANCHES = [
        '/uslugi/'    => ['Услуги', 40],
        '/resheniya/' => ['Решения', 35],
        '/tarify/'    => ['Тарифы', 32],
        '/keysy/'     => ['Кейсы', 30],
    ];

    /** То же для страниц, у которых адрес один и ниже ничего нет. */
    private const SINGLES = [
        '/'           => ['Страница', 15],
        '/uslugi'     => ['Раздел', 25],
        '/resheniya'  => ['Раздел', 25],
        '/keysy'      => ['Раздел', 25],
        '/stati'      => ['Раздел', 25],
        '/o-kompanii' => ['Страница', 15],
        '/kontakty'   => ['Страница', 15],
        '/privacy'    => ['Документ', 5],
        '/soglasie'   => ['Документ', 5],
    ];

    /**
     * Страницы, которые делят один файл текстов. Политика и согласие
     * лежат в legal.php двумя разделами; в указатель каждая идёт своим
     * разделом, иначе по любому слову находились бы обе сразу.
     */
    private const PARTS = [
        '/privacy'  => 'privacy',
        '/soglasie' => 'consent',
    ];

    /**
     * Строки указателя по постоянным страницам сайта.
     *
     * @param  string                $dir      каталог config/content
     * @param  array<string, string> $files    адрес → файл текстов
     * @param  array<string, string> $sections адрес → подпись раздела,
     *                                         если она точнее общей
     * @return array<int, array{url:string, section:string, title:string,
     *                          excerpt:string, body:string, weight:int}>
     */
    public static function pages(string $dir, array $files, array $sections): array
    {
        $rows = [];

        foreach ($files as $url => $file) {
            $path = $dir . '/' . $file . '.php';

            if (!is_file($path)) {
                continue;
            }

            $page = require $path;

            if (!is_array($page)) {
                continue;
            }

            // Страница делит файл с другой — берём только свой раздел.
            if (isset(self::PARTS[$url])) {
                $page = $page[self::PARTS[$url]] ?? [];
            }

            $title = (string) ($page['hero']['title'] ?? $page['hero']['h1'] ?? $page['title'] ?? '');

            // Без заголовка строку выдачи не на чем построить: такую
            // страницу в указатель не берём.
            if ($title === '') {
                continue;
            }

            [$section, $weight] = self::place($url);

            $rows[] = [
                'url'     => $url,
                'section' => $sections[$url] ?? $section,
                'title'   => $title,
                'excerpt' => (string) ($page['hero']['lead'] ?? $page['description'] ?? ''),
                'body'    => self::text($page),
                'weight'  => $weight,
            ];
        }

        return $rows;
    }

    /**
     * Подпись раздела и вес для адреса.
     *
     * @return array{0:string, 1:int}
     */
    private static function place(string $url): array
    {
        foreach (self::BRANCHES as $prefix => $place) {
            if (str_starts_with($url, $prefix)) {
                return $place;
            }
        }

        return self::SINGLES[$url] ?? ['Страница', 10];
    }

    /**
     * Весь текст страницы одной строкой.
     *
     * Куски склеиваются через точку, а не через пробел. В файле текстов
     * лежат заголовки, подписи и абзацы вперемешку, и у заголовка точки
     * на конце нет: без неё в куске выдачи получалось «парк, а не воронка
     * В обычной CRM смотрят» — читается как ошибка. Точка ставится
     * только там, где строка кончается буквой или цифрой.
     *
     * @param array<mixed> $page
     */
    private static function text(array $page): string
    {
        $pieces = [];

        foreach (self::strings($page) as $piece) {
            $piece = rtrim($piece);

            $pieces[] = preg_match('~[\p{L}\p{N}»)]$~u', $piece) === 1 ? $piece . '.' : $piece;
        }

        return Text::plain(implode(' ', $pieces));
    }

    /**
     * Все строки текста из файла, вложенность любой глубины.
     *
     * @param  array<mixed> $data
     * @return array<int, string>
     */
    private static function strings(array $data): array
    {
        $found = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array($key, self::SKIP, true)) {
                continue;
            }

            if (is_array($value)) {
                $found = [...$found, ...self::strings($value)];
                continue;
            }

            // Числа и признаки «да/нет» искать незачем.
            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            // Одно «слово» латиницей без пробелов — почти наверняка адрес,
            // имя файла или название стиля под ключом, которого нет
            // в списке выше. В тексте страницы такого не бывает.
            if (preg_match('~^[a-z0-9][a-z0-9._/#-]*$~', $value) === 1) {
                continue;
            }

            $found[] = $value;
        }

        return $found;
    }
}
