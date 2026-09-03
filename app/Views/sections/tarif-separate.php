<?php
/**
 * Лицензия и работы оплачиваются отдельно.
 *
 * Отдельным блоком, а не сноской мелким шрифтом: на странице с ценами
 * стоимость лицензии проще всего принять за стоимость внедрения,
 * и разбираться с этим потом дороже, чем предупредить здесь.
 *
 * @var array $separate label, title, text, link
 */

use App\Core\View;
?>
<section class="section section--tight">
    <div class="container container--narrow">
        <div class="tarif-separate">
            <p class="label"><?= View::e($separate['label']) ?></p>
            <h2><?= View::e($separate['title']) ?></h2>
            <p class="tarif-separate__text"><?= View::e($separate['text']) ?></p>

            <a class="btn btn--outline" href="<?= View::e($separate['link']['href']) ?>">
                <?= View::e($separate['link']['label']) ?>
                <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
            </a>
        </div>
    </div>
</section>
