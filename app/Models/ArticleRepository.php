<?php
/**
 * Статьи: выборка для списка, страница статьи, похожие материалы,
 * подсказки поиска и запись из админки.
 *
 * Поиск идёт по колонке search_text — это заголовок, анонс и текст статьи
 * без разметки, склеенные при сохранении. Обычный LIKE, без полнотекстового
 * индекса: он находит совпадения и по части слова, а это ровно то, что нужно
 * подсказкам при наборе. Если статей когда-нибудь станет столько, что поиск
 * начнёт заметно тормозить, здесь появится FULLTEXT — на структуре раздела
 * это не отразится.
 */

declare(strict_types=1);

namespace App\Models;

final class ArticleRepository extends Repository
{
    /** Поля, которых достаточно карточке в списке: тело статьи не тянем. */
    private const CARD = 'a.id, a.slug, a.title, a.excerpt, a.cover, a.cover_alt,
            a.cover_width, a.cover_height, a.author, a.reading_time, a.published_at,
            c.slug AS category_slug, c.name AS category_name';

    /**
     * Страница списка с учётом фильтров.
     *
     * @param  array{category?:string, tags?:array<int,string>, q?:string} $filter
     * @return array{items:array<int,array<string,mixed>>, total:int, page:int, pages:int}
     */
    public function paginate(array $filter, int $page, int $perPage): array
    {
        $params = [];
        $where  = $this->where($filter, $params);

        $total = (int) $this->value(
            "SELECT COUNT(*) FROM articles a
               JOIN categories c ON c.id = a.category_id
              WHERE {$where}",
            $params,
        );

        $pages  = max(1, (int) ceil($total / $perPage));
        $page   = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $items = $this->all(
            'SELECT ' . self::CARD . "
               FROM articles a
               JOIN categories c ON c.id = a.category_id
              WHERE {$where}
              ORDER BY a.published_at DESC, a.id DESC
              LIMIT {$perPage} OFFSET {$offset}",
            $params,
        );

        return [
            'items' => $this->withTags($items),
            'total' => $total,
            'page'  => $page,
            'pages' => $pages,
        ];
    }

    /**
     * Дописывает каждой статье её теги одним запросом.
     *
     * Отдельный запрос на статью в списке из девяти карточек — это девять
     * лишних обращений к базе на каждой странице.
     *
     * @param  array<int, array<string, mixed>> $articles
     * @return array<int, array<string, mixed>>
     */
    public function withTags(array $articles): array
    {
        if ($articles === []) {
            return [];
        }

        $ids  = array_column($articles, 'id');
        $in   = implode(', ', array_fill(0, count($ids), '?'));
        $rows = $this->all(
            "SELECT at.article_id, t.slug, t.name
               FROM article_tag at
               JOIN tags t ON t.id = at.tag_id
              WHERE at.article_id IN ({$in})
              ORDER BY t.position, t.name",
            array_values($ids),
        );

        $byArticle = [];

        foreach ($rows as $row) {
            $byArticle[(int) $row['article_id']][] = [
                'slug' => $row['slug'],
                'name' => $row['name'],
            ];
        }

        foreach ($articles as &$article) {
            $article['tags'] = $byArticle[(int) $article['id']] ?? [];
        }

        return $articles;
    }

    /** @return array<string, mixed>|null */
    public function find(string $slug, bool $onlyPublished = true): ?array
    {
        $status  = $onlyPublished ? "AND a.status = 'published'" : '';
        $article = $this->one(
            "SELECT a.*, c.slug AS category_slug, c.name AS category_name
               FROM articles a
               JOIN categories c ON c.id = a.category_id
              WHERE a.slug = :slug {$status}",
            ['slug' => $slug],
        );

        if ($article === null) {
            return null;
        }

        return $this->withTags([$article])[0];
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $article = $this->one(
            'SELECT a.*, c.slug AS category_slug, c.name AS category_name
               FROM articles a
               JOIN categories c ON c.id = a.category_id
              WHERE a.id = :id',
            ['id' => $id],
        );

        return $article === null ? null : $this->withTags([$article])[0];
    }

    /**
     * «Вам может быть интересно».
     *
     * Порядок отбора задан заказчиком: сначала статьи с общими тегами
     * (чем больше совпало, тем выше), затем статьи той же категории,
     * затем любые свежие. Всё это один запрос: сортировка по трём
     * выражениям и делает нужную очерёдность, а LIMIT добирает остаток
     * из «прочих», если по тегам и категории набралось мало.
     *
     * @param  array<string, mixed> $article
     * @return array<int, array<string, mixed>>
     */
    public function related(array $article, int $limit = 4): array
    {
        $tagSlugs = array_column($article['tags'] ?? [], 'slug');
        $params   = [
            'id'       => (int) $article['id'],
            'category' => (int) $article['category_id'],
        ];

        // Пустой список тегов в SQL записать нельзя — подставляем условие,
        // которое просто никогда не выполнится, и остаётся отбор по категории.
        $shared = '0';

        if ($tagSlugs !== []) {
            $marks = [];

            foreach (array_values($tagSlugs) as $i => $slug) {
                $marks[] = ":rt{$i}";
                $params["rt{$i}"] = $slug;
            }

            $shared = '(SELECT COUNT(*)
                          FROM article_tag at
                          JOIN tags t ON t.id = at.tag_id
                         WHERE at.article_id = a.id
                           AND t.slug IN (' . implode(', ', $marks) . '))';
        }

        $items = $this->all(
            'SELECT ' . self::CARD . ", {$shared} AS shared_tags
               FROM articles a
               JOIN categories c ON c.id = a.category_id
              WHERE a.status = 'published' AND a.id <> :id
              ORDER BY shared_tags DESC,
                       (a.category_id = :category) DESC,
                       a.published_at DESC, a.id DESC
              LIMIT {$limit}",
            $params,
        );

        return $this->withTags($items);
    }

    /**
     * Подсказки для поиска по мере набора.
     *
     * Возвращает обычный текст без разметки: подсветку совпадений делает
     * скрипт в браузере. Готовый HTML отсюда пришлось бы вставлять
     * в страницу как есть — это лишний риск на ровном месте.
     *
     * @return array<int, array<string, mixed>>
     */
    public function suggest(string $query, int $limit = 8): array
    {
        $words = self::words($query);

        if ($words === []) {
            return [];
        }

        $params = [];
        $where  = $this->where(['q' => $query], $params);

        // search_text нужен, чтобы показать кусок текста вокруг найденного
        // слова, если в заголовке и анонсе его нет.
        return $this->all(
            "SELECT a.slug, a.title, a.excerpt, a.search_text, c.name AS category_name
               FROM articles a
               JOIN categories c ON c.id = a.category_id
              WHERE {$where}
              ORDER BY a.published_at DESC, a.id DESC
              LIMIT {$limit}",
            $params,
        );
    }

    /**
     * Все адреса опубликованных статей и дата последнего изменения —
     * для карты сайта.
     *
     * @return array<int, array<string, mixed>>
     */
    public function published(): array
    {
        return $this->all(
            "SELECT slug, updated_at, published_at
               FROM articles
              WHERE status = 'published'
              ORDER BY published_at DESC, id DESC"
        );
    }

    /**
     * Список для админки: черновики тоже видны.
     *
     * @return array<int, array<string, mixed>>
     */
    public function adminListing(string $query = '', int $limit = 200): array
    {
        $limit  = max(1, min(500, $limit));
        $params = [];
        $where  = [];

        foreach (self::words($query) as $i => $word) {
            $where[] = "a.search_text LIKE :q{$i}";
            $params["q{$i}"] = self::like($word);
        }

        $filter = $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);

        return $this->all(
            "SELECT a.id, a.slug, a.title, a.status, a.published_at, a.updated_at,
                    a.reading_time, c.name AS category_name,
                    (SELECT COUNT(*) FROM comments cm
                      WHERE cm.article_id = a.id AND cm.status = 'new') AS new_comments
               FROM articles a
               JOIN categories c ON c.id = a.category_id
               {$filter}
              ORDER BY a.published_at DESC, a.id DESC
              LIMIT {$limit}",
            $params,
        );
    }

    /**
     * Создаёт или обновляет статью вместе с тегами.
     *
     * Всё в одной транзакции: статья без тегов или теги без статьи —
     * это наполовину сохранённая запись, разбираться с которой потом
     * пришлось бы руками в базе.
     *
     * @param  array<string, mixed> $data
     * @param  array<int, int>      $tagIds
     * @return int Идентификатор статьи
     */
    public function save(?int $id, array $data, array $tagIds): int
    {
        $fields = [
            'slug', 'category_id', 'title', 'excerpt', 'body', 'search_text',
            'cover', 'cover_alt', 'cover_width', 'cover_height', 'author',
            'reading_time', 'published_at', 'status', 'meta_title', 'meta_description',
            'form_topic',
        ];

        $this->db->beginTransaction();

        try {
            if ($id === null) {
                $columns = implode(', ', $fields);
                $marks   = ':' . implode(', :', $fields);

                $this->run(
                    "INSERT INTO articles ({$columns}) VALUES ({$marks})",
                    $this->only($data, $fields),
                );

                $id = (int) $this->db->lastInsertId();
            } else {
                $set = [];

                foreach ($fields as $field) {
                    $set[] = "{$field} = :{$field}";
                }

                $this->run(
                    'UPDATE articles SET ' . implode(', ', $set) . ' WHERE id = :id',
                    $this->only($data, $fields) + ['id' => $id],
                );
            }

            $this->run('DELETE FROM article_tag WHERE article_id = :id', ['id' => $id]);

            foreach (array_unique($tagIds) as $tagId) {
                $this->run(
                    'INSERT INTO article_tag (article_id, tag_id) VALUES (:article, :tag)',
                    ['article' => $id, 'tag' => $tagId],
                );
            }

            $this->db->commit();
        } catch (\Throwable $error) {
            $this->db->rollBack();

            throw $error;
        }

        return $id;
    }

    public function delete(int $id): void
    {
        // Комментарии, оценки и связи с тегами удалит сама база:
        // внешние ключи объявлены с ON DELETE CASCADE.
        $this->run('DELETE FROM articles WHERE id = :id', ['id' => $id]);
    }

    /** Занят ли адрес другой статьёй. */
    public function slugTaken(string $slug, ?int $exceptId = null): bool
    {
        $params = ['slug' => $slug];
        $except = '';

        if ($exceptId !== null) {
            $except = 'AND id <> :id';
            $params['id'] = $exceptId;
        }

        return $this->value(
            "SELECT COUNT(*) FROM articles WHERE slug = :slug {$except}",
            $params,
        ) > 0;
    }

    /**
     * Оставляет из массива только перечисленные ключи — чтобы в запрос
     * не попало лишнее поле из формы.
     *
     * @param  array<string, mixed> $data
     * @param  array<int, string>   $fields
     * @return array<string, mixed>
     */
    private function only(array $data, array $fields): array
    {
        $result = [];

        foreach ($fields as $field) {
            $result[$field] = $data[$field] ?? '';
        }

        return $result;
    }

    /**
     * Условие выборки по фильтрам. Параметры дописываются в $params.
     *
     * @param array{category?:string, tags?:array<int,string>, q?:string} $filter
     * @param array<string, mixed> $params
     */
    private function where(array $filter, array &$params): string
    {
        $where = ["a.status = 'published'"];

        if (($filter['category'] ?? '') !== '') {
            $where[] = 'c.slug = :category';
            $params['category'] = $filter['category'];
        }

        $tags = array_values($filter['tags'] ?? []);

        if ($tags !== []) {
            // Отмеченные теги сужают выборку: нужны статьи, у которых есть
            // сразу все отмеченные, а не любой один из них.
            $marks = [];

            foreach ($tags as $i => $slug) {
                $marks[] = ":tag{$i}";
                $params["tag{$i}"] = $slug;
            }

            $where[] = 'a.id IN (SELECT at.article_id
                                   FROM article_tag at
                                   JOIN tags t ON t.id = at.tag_id
                                  WHERE t.slug IN (' . implode(', ', $marks) . ')
                                  GROUP BY at.article_id
                                 HAVING COUNT(DISTINCT t.id) = ' . count($tags) . ')';
        }

        foreach (self::words($filter['q'] ?? '') as $i => $word) {
            $where[] = "a.search_text LIKE :q{$i}";
            $params["q{$i}"] = self::like($word);
        }

        return implode(' AND ', $where);
    }

    /**
     * Разбирает поисковую строку на слова.
     *
     * Слова соединяются через И: «воронка продаж» находит статьи, где есть
     * и то, и другое. Однобуквенные обрывки отбрасываем — они совпадают
     * почти со всем и подсказки от них бесполезны.
     *
     * @return array<int, string>
     */
    public static function words(string $query): array
    {
        $parts = preg_split('/\s+/u', trim($query)) ?: [];
        $words = [];

        foreach ($parts as $part) {
            if (mb_strlen($part) >= 2) {
                $words[] = $part;
            }
        }

        return array_slice($words, 0, 6);
    }
}
