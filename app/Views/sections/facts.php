<?php
/**
 * Полоса фактов под первым экраном.
 * Цифры взяты из данных заказчика, ничего не добавлено.
 *
 * @var array $facts
 */

use App\Core\View;
?>
<section class="facts" aria-label="Опыт в цифрах">
    <div class="container">
        <div class="facts__grid">
            <?php foreach ($facts as $fact): ?>
                <div class="facts__item">
                    <div class="facts__value"><?= View::e($fact['value']) ?></div>
                    <div class="facts__label"><?= View::e($fact['label']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
