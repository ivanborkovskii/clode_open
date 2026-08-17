<?php
/**
 * Перечень услуг развёрнутыми строками.
 *
 * Не сетка одинаковых карточек: услуг всего пять, и по каждой нужно
 * понять «моя это задача или нет». Поэтому строка во всю ширину —
 * слева номер, название и ситуация, справа состав работ.
 *
 * @var array $items Услуги
 */

use App\Core\View;
?>
<section class="section services-list" id="spisok">
    <div class="container">
        <ol class="srow-list">
            <?php foreach ($items as $i => $item): ?>
                <li class="srow">
                    <div class="srow__head">
                        <p class="srow__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></p>

                        <?php $ready = $view->exists($item['href']); ?>

                        <h2 class="srow__title">
                            <?php if ($ready): ?>
                                <a href="<?= View::e($item['href']) ?>"><?= View::e($item['title']) ?></a>
                            <?php else: ?>
                                <?= View::e($item['title']) ?>
                            <?php endif; ?>
                        </h2>

                        <p class="srow__situation"><?= View::e($item['situation']) ?></p>

                        <?php if (!empty($item['price'])): ?>
                            <p class="srow__price">
                                <b><?= View::e($item['price']['value']) ?></b>
                                <span><?= View::e($item['price']['unit']) ?></span>
                                <small><?= View::e($item['price']['note']) ?></small>
                            </p>
                        <?php endif; ?>

                        <?php if ($ready): ?>
                            <a class="link-arrow" href="<?= View::e($item['href']) ?>">
                                Подробнее об услуге
                                <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="srow__body">
                        <p class="srow__label">Что входит</p>

                        <ul class="srow__points">
                            <?php foreach ($item['points'] as $point): ?>
                                <li><?= View::e($point) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
