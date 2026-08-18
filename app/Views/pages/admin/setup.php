<?php
/**
 * Первый запуск: создание учётной записи.
 *
 * Страница доступна, только пока в таблице admins пусто. После создания
 * записи она сама закрывается и перенаправляет на вход.
 *
 * @var array $errors
 * @var array $values
 */

use App\Core\View;

$old = static fn (string $field): string => View::e((string) ($values[$field] ?? ''));
?>
<div class="acard-narrow">
    <h1>Установка</h1>
    <p class="amuted">
        Заведите учётную запись для управления статьями. Она создаётся один раз;
        после этого страница установки перестанет открываться.
    </p>

    <form method="post" action="/admin/ustanovka" class="aform">
        <label class="afield">
            <span>Логин</span>
            <input type="text" name="login" value="<?= $old('login') ?>"
                   autocomplete="username" required autofocus>
            <?php if (isset($errors['login'])): ?>
                <em class="aerror"><?= View::e($errors['login']) ?></em>
            <?php endif; ?>
        </label>

        <label class="afield">
            <span>Пароль</span>
            <input type="password" name="password" autocomplete="new-password" required>
            <small>Не короче 10 символов. Лучше длинная фраза, чем короткий набор знаков.</small>
            <?php if (isset($errors['password'])): ?>
                <em class="aerror"><?= View::e($errors['password']) ?></em>
            <?php endif; ?>
        </label>

        <label class="afield">
            <span>Пароль ещё раз</span>
            <input type="password" name="password_repeat" autocomplete="new-password" required>
            <?php if (isset($errors['password_repeat'])): ?>
                <em class="aerror"><?= View::e($errors['password_repeat']) ?></em>
            <?php endif; ?>
        </label>

        <button class="abtn abtn--primary" type="submit">Создать и войти</button>
    </form>
</div>
