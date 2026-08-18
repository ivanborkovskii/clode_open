<?php
/**
 * Результат проекта. Отдельным заметным блоком: ради него всё и делалось.
 *
 * @var array $result title, text, note
 */

use App\Core\View;
?>
<section class="section section--dark case-result">
    <div class="container">
        <div class="case-result__inner">
            <p class="label">Результат</p>
            <h2><?= View::e($result['title']) ?></h2>
            <p class="case-result__text"><?= View::e($result['text']) ?></p>

            <?php if (!empty($result['note'])): ?>
                <?php // Трудность проекта. Показывать её честнее, чем гладкую историю. ?>
                <p class="case-result__note"><?= View::e($result['note']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
