<?php
/**
 * Решения: что закрывает CRM по четырём направлениям.
 *
 * Слева — заголовок и изометрическая схема трёх слоёв системы,
 * справа — направления. Так блок не превращается в четыре одинаковых списка.
 *
 * @var App\Core\View $view
 * @var array $solutions
 */

use App\Core\View;
?>
<section class="section section--dark solutions" id="resheniya">
    <div class="container solutions__layout">
        <div class="solutions__aside">
            <p class="label">Решения</p>
            <h2><?= View::e($solutions['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($solutions['lead']) ?></p>

            <?php $view->partial('partials/isometric'); ?>
        </div>

        <div class="solutions__grid">
            <?php foreach ($solutions['items'] as $item): ?>
                <article class="solutions__col">
                    <span class="icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><use href="#<?= View::e($item['icon']) ?>"/></svg>
                    </span>

                    <h3><?= View::e($item['title']) ?></h3>

                    <ul class="solutions__list">
                        <?php foreach ($item['points'] as $point): ?>
                            <li><?= View::e($point) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <a class="link-arrow" href="<?= View::e($item['href']) ?>">
                        Подробнее
                        <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
