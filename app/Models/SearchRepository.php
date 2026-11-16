<?php
/**
 * Поиск по всему сайту.
 *
 * Все страницы и статьи лежат в одной таблице search_index, и поиск
 * не различает, откуда что пришло: запрос к нему один, выдача общая.
 *
 * Ищем обычным LIKE по колонке body — тексту страницы без разметки.
 * Так же, как в поиске по статьям: совпадение находится и по части слова,
 * а это ровно то, что нужно подсказкам при наборе. Полнотекстовый индекс
 * тут ничего не даст — страниц на сайте около сотни.
 *
 * Слова соединяются через И: «воронка продаж» находит страницы, где есть
 * и то, и другое.
 */

declare(strict_types=1);

namespace App\Models;

use Throwable;

final class SearchRepository extends Repository
{
    /** Вес статьи в выдаче. У страниц он приходит из SiteIndex. */
    private const ARTICLE_WEIGHT = 20;

    /**
     * Найденные страницы.
     *
     * @return array<int, array<string, mixed>>
     */
    public function find(string $query, int $limit = 40): array
    {
        $words = self::stems($query);

        if ($words === []) {
            return [];
        }

        $limit  = max(1, min(100, $limit));
        $params = [];
        $where  = $this->conditions($words, $params);

        // Страница, у которой слова запроса стоят в названии, отвечает
        // на вопрос точнее той, где они встретились где-то в середине
        // текста. Считаем, сколько слов нашлось в заголовке, и ставим
        // такие страницы выше.
        //
        // Метки отдельные от тех, что в условии отбора: одно и то же имя
        // дважды в запросе настоящие подготовленные запросы не принимают.
        $score = [];

        foreach ($words as $i => $word) {
            $score[] = "(title LIKE :t{$i})";
            $params["t{$i}"] = self::like($word);
        }

        // Второй признак — насколько густо слова запроса стоят в тексте.
        // Страница, где «аудит» встречается десять раз, про аудит и есть;
        // страница, где он упомянут один раз в перечислении, — нет,
        // и в выдаче она должна быть ниже.
        //
        // Считаем по длине: сколько текста исчезнет, если вырезать из него
        // все вхождения слова. Делим на длину страницы, иначе длинные
        // страницы всегда оказывались бы выше коротких.
        $density = [];

        foreach ($words as $i => $word) {
            $density[] = "(CHAR_LENGTH(body) - CHAR_LENGTH(REPLACE(LOWER(body), :f{$i}, '')))";
            $params["f{$i}"] = $word;
        }

        return $this->all(
            'SELECT url, section, title, excerpt, body
               FROM search_index
              WHERE ' . $where . '
              ORDER BY ' . implode(' + ', $score) . ' DESC,
                       (' . implode(' + ', $density) . ') / CHAR_LENGTH(body) DESC,
                       weight DESC, title ASC
              LIMIT ' . $limit,
            $params,
        );
    }

    /**
     * Сколько всего страниц отвечает запросу.
     *
     * Нужно странице выдачи: показывает она не больше сорока строк,
     * а сказать, сколько нашлось на самом деле, должна честно.
     */
    public function count(string $query): int
    {
        $words = self::stems($query);

        if ($words === []) {
            return 0;
        }

        $params = [];
        $where  = $this->conditions($words, $params);

        return (int) $this->value('SELECT COUNT(*) FROM search_index WHERE ' . $where, $params);
    }

    /**
     * Слова запроса без русских окончаний.
     *
     * «Воронка продаж» и «воронки продаж» — один и тот же вопрос, и
     * отвечать на них сайт должен одинаково. Поиск идёт по части слова,
     * поэтому достаточно отбросить окончание: «воронка» превращается
     * в «воронк» и находится в любом падеже.
     *
     * Это не разбор языка, а отсечение хвоста. От настоящего разбора
     * с словарём здесь толку мало: страниц около сотни, и ошибка такого
     * отсечения стоит одной лишней строки в выдаче, а не неверного
     * ответа. Основа короче четырёх букв не делается — «мид» или «CRM»
     * остаются как есть.
     *
     * @return array<int, string>
     */
    public static function stems(string $query): array
    {
        // Длинные окончания идут первыми: иначе от «ами» отрезалось бы
        // только «и».
        static $endings = [
            'ами', 'ями', 'ого', 'его', 'ому', 'ему', 'ыми', 'ими',
            'ах', 'ях', 'ов', 'ев', 'ей', 'ам', 'ям', 'ом', 'ем',
            'ую', 'юю', 'ая', 'яя', 'ые', 'ие', 'ый', 'ий', 'ой',
            'ею', 'ью', 'их', 'ых',
            'а', 'я', 'о', 'е', 'у', 'ю', 'ы', 'и', 'ь', 'й',
        ];

        $stems = [];

        foreach (ArticleRepository::words($query) as $word) {
            $word = mb_strtolower($word);

            foreach ($endings as $ending) {
                if (str_ends_with($word, $ending)
                    && mb_strlen($word) - mb_strlen($ending) >= 4
                ) {
                    $word = mb_substr($word, 0, -mb_strlen($ending));
                    break;
                }
            }

            $stems[] = $word;
        }

        return $stems;
    }

    /**
     * Условие отбора: все слова запроса должны найтись в тексте.
     *
     * @param  array<int, string>  $words
     * @param  array<string, mixed> $params
     */
    private function conditions(array $words, array &$params): string
    {
        $where = [];

        foreach ($words as $i => $word) {
            $where[] = "body LIKE :q{$i}";
            $params["q{$i}"] = self::like($word);
        }

        return implode(' AND ', $where);
    }

    /**
     * Сколько страниц в указателе и когда его обновляли в последний раз.
     *
     * @return array{total:int, updated:string}
     */
    public function stats(): array
    {
        $row = $this->one('SELECT COUNT(*) AS total, MAX(indexed_at) AS updated FROM search_index');

        return [
            'total'   => (int) ($row['total'] ?? 0),
            'updated' => (string) ($row['updated'] ?? ''),
        ];
    }

    /**
     * Перезаписывает указатель целиком.
     *
     * Целиком, а не по изменённым страницам: удалённая страница должна
     * исчезнуть из поиска, а узнать об удалении по списку существующих
     * файлов нельзя. Строк около сотни, перезапись занимает доли секунды.
     *
     * Всё в одной транзакции: если на середине что-то сорвётся, останется
     * прежний указатель, а не половина нового.
     *
     * @param  array<int, array<string, mixed>> $pages строки от SiteIndex
     * @return int сколько строк в указателе теперь
     */
    public function rebuild(array $pages): int
    {
        $rows = [...$pages, ...$this->articleRows()];
        $now  = date('Y-m-d H:i:s');

        $this->db->beginTransaction();

        try {
            $this->run('DELETE FROM search_index');

            $insert = $this->db->prepare(
                'INSERT INTO search_index (url, section, title, excerpt, body, weight, indexed_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            foreach ($rows as $row) {
                $insert->execute([
                    $row['url'],
                    $row['section'],
                    $row['title'],
                    mb_substr((string) $row['excerpt'], 0, 600),
                    $row['body'],
                    (int) $row['weight'],
                    $now,
                ]);
            }

            $this->db->commit();
        } catch (Throwable $error) {
            $this->db->rollBack();

            throw $error;
        }

        return count($rows);
    }

    /**
     * Статьи для указателя.
     *
     * Колонка search_text — это заголовок, анонс и текст статьи без
     * разметки, склеенные при сохранении. Ровно то же, что для страниц
     * собирает SiteIndex, только собранное заранее.
     *
     * @return array<int, array<string, mixed>>
     */
    private function articleRows(): array
    {
        $rows = [];

        $articles = $this->all(
            "SELECT slug, title, excerpt, search_text
               FROM articles
              WHERE status = 'published'"
        );

        foreach ($articles as $article) {
            $rows[] = [
                'url'     => '/stati/' . $article['slug'],
                'section' => 'Статьи',
                'title'   => (string) $article['title'],
                'excerpt' => (string) $article['excerpt'],
                'body'    => (string) $article['search_text'],
                'weight'  => self::ARTICLE_WEIGHT,
            ];
        }

        return $rows;
    }
}
