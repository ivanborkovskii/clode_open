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
            "SELECT id, name, body, created_at
               FROM comments
              WHERE article_id = :id AND status = 'approved'
              ORDER BY created_at, id",
            ['id' => $articleId],
        );
    }

    public function add(int $articleId, string $name, string $email, string $body, ?string $ip): void
    {
        $this->run(
            'INSERT INTO comments (article_id, name, email, body, ip)
             VALUES (:article, :name, :email, :body, :ip)',
            [
                'article' => $articleId,
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
            "SELECT c.id, c.name, c.email, c.body, c.status, c.created_at,
                    a.title AS article_title, a.slug AS article_slug
               FROM comments c
               JOIN articles a ON a.id = c.article_id
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
