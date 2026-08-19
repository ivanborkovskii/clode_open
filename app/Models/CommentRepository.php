<?php
/**
 * Комментарии к статьям.
 *
 * Новый комментарий сразу на сайте не появляется: сначала он ждёт
 * проверки в админке. Открытая форма без модерации на сайте с формой
 * заявки — это вопрос времени до первой партии спама со ссылками.
 */

declare(strict_types=1);

namespace App\Models;

final class CommentRepository extends Repository
{
    /**
     * Проверенные комментарии к статье, старые сверху — так читается
     * как разговор.
     *
     * @return array<int, array<string, mixed>>
     */
    public function approved(int $articleId): array
    {
        return $this->all(
            "SELECT id, parent_id, name, body, is_author, created_at
               FROM comments
              WHERE article_id = :id AND status = 'approved'
              ORDER BY created_at, id",
            ['id' => $articleId],
        );
    }

    /**
     * Проверенные комментарии деревом: ответ стоит под тем, кому он написан.
     *
     * Собирается из одной выборки, а не запросом на каждую ветку. Вместе
     * с ответом сохраняется имя того, кому он адресован: на странице это
     * подпись «в ответ». Ответ, чьё начало не опубликовано — например, его
     * отклонили позже, — показывается сам по себе, иначе он молча исчез бы
     * со страницы вместе со всем, что под ним.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tree(int $articleId): array
    {
        $items = [];

        foreach ($this->approved($articleId) as $row) {
            $row['replies']     = [];
            $row['parent_name'] = '';
            $items[(int) $row['id']] = $row;
        }

        $tree = [];

        // Ответ всегда написан позже того, кому отвечает, а выборка
        // упорядочена по времени — значит, родитель уже в списке.
        // Ссылки нужны, чтобы ответ попал внутрь родителя, а не в его копию.
        foreach ($items as $id => &$item) {
            $parent = (int) ($item['parent_id'] ?? 0);

            if ($parent > 0 && isset($items[$parent])) {
                $item['parent_name'] = $items[$parent]['name'];
                $items[$parent]['replies'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }

        unset($item);

        return $tree;
    }

    /**
     * Комментарий по номеру — чтобы проверить, к чему пишут ответ.
     *
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->one('SELECT * FROM comments WHERE id = :id', ['id' => $id]);
    }

    public function add(
        int $articleId,
        string $name,
        string $email,
        string $body,
        ?string $ip,
        ?int $parentId = null,
    ): void {
        $this->run(
            'INSERT INTO comments (article_id, parent_id, name, email, body, ip)
             VALUES (:article, :parent, :name, :email, :body, :ip)',
            [
                'article' => $articleId,
                'parent'  => $parentId,
                'name'    => $name,
                'email'   => $email,
                'body'    => $body,
                // Адрес храним в двоичном виде: он нужен только на случай
                // разбора спама и не показывается нигде на сайте.
                'ip'      => $ip === null ? null : (@inet_pton($ip) ?: null),
            ],
        );
    }

    /**
     * Ответ владельца сайта из админки.
     *
     * Проверять его не у кого — он публикуется сразу и помечается как
     * ответ автора.
     */
    public function addAuthorReply(int $articleId, int $parentId, string $name, string $body): void
    {
        $this->run(
            "INSERT INTO comments (article_id, parent_id, name, body, status, is_author)
             VALUES (:article, :parent, :name, :body, 'approved', 1)",
            [
                'article' => $articleId,
                'parent'  => $parentId,
                'name'    => $name,
                'body'    => $body,
            ],
        );
    }

    /**
     * Список для админки.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listing(string $status = 'new', int $limit = 100): array
    {
        $limit  = max(1, min(500, $limit));
        $filter = $status === 'all' ? '' : 'WHERE c.status = :status';
        $params = $status === 'all' ? [] : ['status' => $status];

        return $this->all(
            // Вместе с комментарием достаём имя того, кому он отвечает,
            // и признак «на этот комментарий уже ответили»: без них
            // в админке не видно, где разговор продолжен, а где нет.
            "SELECT c.id, c.article_id, c.parent_id, c.name, c.email, c.body,
                    c.status, c.is_author, c.created_at,
                    p.name AS parent_name,
                    (SELECT COUNT(*) FROM comments r
                      WHERE r.parent_id = c.id AND r.is_author = 1) AS answered,
                    a.title AS article_title, a.slug AS article_slug
               FROM comments c
               JOIN articles a ON a.id = c.article_id
               LEFT JOIN comments p ON p.id = c.parent_id
               {$filter}
              ORDER BY c.created_at DESC, c.id DESC
              LIMIT {$limit}",
            $params,
        );
    }

    /** Сколько комментариев ждёт проверки — цифра рядом с пунктом меню. */
    public function newCount(): int
    {
        return (int) $this->value("SELECT COUNT(*) FROM comments WHERE status = 'new'");
    }

    public function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['new', 'approved', 'rejected'], true)) {
            return;
        }

        $this->run(
            'UPDATE comments SET status = :status WHERE id = :id',
            ['status' => $status, 'id' => $id],
        );
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM comments WHERE id = :id', ['id' => $id]);
    }
}
