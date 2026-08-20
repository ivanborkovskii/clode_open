<?php
/**
 * Вход в админку.
 *
 * @var string $error
 * @var string $login
 * @var array  $config
 */

use App\Core\Csrf;
use App\Core\View;
?>
<div class="acard-narrow">
    <h1>Вход</h1>
    <p class="amuted">Управление статьями сайта <?= View::e($config['company']['brand']) ?></p>

    <?php if ($error !== ''): ?>
        <p class="aalert aalert--error"><?= View::e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="/admin/vhod" class="aform">
        <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">

        <label class="afield">
            <span>Логин</span>
            <input type="text" name="login" value="<?= View::e((string) $login) ?>"
                   autocomplete="username" required autofocus>
        </label>

        <label class="afield">
            <span>Пароль</span>
            <input type="password" name="password" autocomplete="current-password" required>
        </label>

        <button class="abtn abtn--primary" type="submit">Войти</button>
    </form>
</div>
