<?php
/**
 * Сообщение о недоступной базе данных.
 *
 * @var string $title
 * @var string $message
 * @var string $details
 */

use App\Core\View;
?>
<div class="acard-narrow">
    <h1><?= View::e($title) ?></h1>
    <p><?= View::e($message) ?></p>

    <?php if ($details !== ''): ?>
        <?php // Текст ошибки показывается только при включённой отладке. ?>
        <pre class="apre"><?= View::e($details) ?></pre>
    <?php endif; ?>

    <p class="amuted">
        Остальные страницы сайта работают без базы данных — на них это
        не влияет.
    </p>
</div>
