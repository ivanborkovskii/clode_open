<?php
/**
 * Смена пароля.
 *
 * @var array $errors
 * @var bool  $done
 * @var array $admin
 */

use App\Core\Csrf;
use App\Core\View;
?>
<div class="acard-narrow">
    <div class="ahead">
        <h1>Пароль</h1>
        <a class="alink" href="/admin">← К списку статей</a>
    </div>

    <p class="amuted">Учётная запись: <b><?= View::e($admin['login']) ?></b></p>

    <?php if ($done): ?>
        <p class="aalert">Пароль изменён.</p>
    <?php endif; ?>

    <form class="aform" method="post" action="/admin/parol">
        <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">

        <label class="afield">
            <span>Текущий пароль</span>
            <input type="password" name="current" autocomplete="current-password" required>
            <?php if (isset($errors['current'])): ?>
                <em class="aerror"><?= View::e($errors['current']) ?></em>
            <?php endif; ?>
        </label>

        <label class="afield">
            <span>Новый пароль</span>
            <input type="password" name="password" autocomplete="new-password" required>
            <small>Не короче 10 символов.</small>
            <?php if (isset($errors['password'])): ?>
                <em class="aerror"><?= View::e($errors['password']) ?></em>
            <?php endif; ?>
        </label>

        <label class="afield">
            <span>Новый пароль ещё раз</span>
            <input type="password" name="password_repeat" autocomplete="new-password" required>
            <?php if (isset($errors['password_repeat'])): ?>
                <em class="aerror"><?= View::e($errors['password_repeat']) ?></em>
            <?php endif; ?>
        </label>

        <button class="abtn abtn--primary" type="submit">Сменить пароль</button>
    </form>
</div>
