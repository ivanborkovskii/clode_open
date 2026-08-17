<?php
/**
 * Стоимость услуги.
 *
 * Отдельный блок, а не строка в перечне: у сопровождения цена известна
 * и понятна, и прятать её в текст нет причин. У остальных услуг работа
 * считается по объёму, поэтому блока там нет — «от» для них не выдумываем.
 *
 * @var array $price title, value, unit, items, note
 */

use App\Core\View;
?>
<section class="section section--tight price-block">
    <div class="container">
        <div class="price-block__inner">
            <div class="price-block__figure">
                <p class="label">Стоимость</p>
                <p class="price-block__value"><?= View::e($price['value']) ?></p>
                <p class="price-block__unit"><?= View::e($price['unit']) ?></p>
            </div>

            <div class="price-block__body">
                <h2><?= View::e($price['title']) ?></h2>

                <ul class="price-block__list">
                    <?php foreach ($price['items'] as $item): ?>
                        <li><?= View::e($item) ?></li>
                    <?php endforeach; ?>
                </ul>

                <?php if (!empty($price['note'])): ?>
                    <p class="price-block__note"><?= View::e($price['note']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
