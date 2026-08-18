<?php
/**
 * Общий предок хранилищ: подготовленный запрос и разбор результата.
 *
 * Все запросы к базе идут только отсюда и только через подготовленные
 * выражения с параметрами — данные из адреса и форм в текст запроса
 * не попадают.
 */

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOStatement;

abstract class Repository
{
    public function __construct(protected readonly PDO $db)
    {
    }

    /**
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    protected function all(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    protected function one(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    /** @param array<string, mixed> $params */
    protected function value(string $sql, array $params = []): mixed
    {
        $value = $this->run($sql, $params)->fetchColumn();

        return $value === false ? null : $value;
    }

    /** @param array<string, mixed> $params */
    protected function run(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /**
     * Экранирует спецсимволы шаблона LIKE.
     *
     * Без этого знак процента, введённый в поиске, превратился бы
     * в «любой текст» и находил бы вообще всё.
     */
    protected static function like(string $value): string
    {
        return '%' . addcslashes($value, '%_\\') . '%';
    }
}
