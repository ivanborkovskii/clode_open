<?php
/**
 * Оценки статей: пять звёзд, один голос на посетителя.
 *
 * Полностью защититься от накрутки без регистрации нельзя, но повторный
 * клик и обновление страницы голос не удваивают: пара «статья + отпечаток
 * посетителя» в таблице уникальна, и второй голос заменяет первый.
 */

declare(strict_types=1);

namespace App\Models;

final class RatingRepository extends Repository
{
    /**
     * Средняя оценка и число голосов.
     *
     * @return array{average: float, votes: int}
     */
    public function summary(int $articleId): array
    {
        $row = $this->one(
            'SELECT AVG(value) AS average, COUNT(*) AS votes
               FROM ratings WHERE article_id = :id',
            ['id' => $articleId],
        );

        return [
            'average' => round((float) ($row['average'] ?? 0), 1),
            'votes'   => (int) ($row['votes'] ?? 0),
        ];
    }

    /** Оценка, которую этот посетитель уже поставил, или null. */
    public function of(int $articleId, string $voter): ?int
    {
        $value = $this->value(
            'SELECT value FROM ratings WHERE article_id = :id AND voter = :voter',
            ['id' => $articleId, 'voter' => $voter],
        );

        return $value === null ? null : (int) $value;
    }

    public function vote(int $articleId, int $value, string $voter): void
    {
        $value = max(1, min(5, $value));

        $this->run(
            'INSERT INTO ratings (article_id, value, voter)
             VALUES (:id, :value, :voter)
             ON DUPLICATE KEY UPDATE value = VALUES(value)',
            ['id' => $articleId, 'value' => $value, 'voter' => $voter],
        );
    }
}
