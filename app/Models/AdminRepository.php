<?php
/**
 * Учётные записи для входа в админку.
 *
 * Пароль хранится только хэшем: если база когда-нибудь утечёт,
 * из неё нельзя будет прочитать сам пароль.
 */

declare(strict_types=1);

namespace App\Models;

final class AdminRepository extends Repository
{
    /** Есть ли вообще учётные записи. От этого зависит доступ к установке. */
    public function isEmpty(): bool
    {
        return (int) $this->value('SELECT COUNT(*) FROM admins') === 0;
    }

    /** @return array<string, mixed>|null */
    public function byLogin(string $login): ?array
    {
        return $this->one('SELECT * FROM admins WHERE login = :login', ['login' => $login]);
    }

    /** @return array<string, mixed>|null */
    public function byId(int $id): ?array
    {
        return $this->one('SELECT * FROM admins WHERE id = :id', ['id' => $id]);
    }

    public function create(string $login, string $password, string $name = ''): int
    {
        $this->run(
            'INSERT INTO admins (login, password_hash, name) VALUES (:login, :hash, :name)',
            [
                'login' => $login,
                'hash'  => password_hash($password, PASSWORD_DEFAULT),
                'name'  => $name,
            ],
        );

        return (int) $this->db->lastInsertId();
    }

    public function changePassword(int $id, string $password): void
    {
        $this->run(
            'UPDATE admins SET password_hash = :hash WHERE id = :id',
            ['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id],
        );
    }

    public function markLogin(int $id): void
    {
        $this->run('UPDATE admins SET last_login_at = NOW() WHERE id = :id', ['id' => $id]);
    }
}
