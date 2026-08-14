<?php
/**
 * Страница ошибки.
 *
 * @var int $code
 * @var string $message
 */

use App\Core\View;
?>
<section class="section section--dark">
    <div class="container" style="min-height: 46vh; display: grid; align-content: center; gap: var(--s-5);">
        <p class="label"><?= (int) $code ?></p>
        <h1 style="font-size: var(--fs-h2);"><?= View::e($message) ?></h1>

        <div>
            <a class="btn btn--primary" href="/">На главную</a>
        </div>
    </div>
</section>
