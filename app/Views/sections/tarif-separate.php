<?php
/**
 * Лицензия и работы оплачиваются отдельно.
 *
 * Отдельным блоком, а не сноской мелким шрифтом: на странице с ценами
 * стоимость лицензии проще всего принять за стоимость внедрения,
 * и разбираться с этим потом дороже, чем предупредить здесь.
 *
 * Вторая такая же пара — text и link — выводится из extra, если она есть.
 * Нужна для случая, когда на странице с ценами надо сказать ещё одну
 * вещь: цены здесь для покупки, а для уже купленной лицензии разговор
 * другой. Отдельной секции под одну фразу заводить незачем.
 *
 * @var array $separate label, title, text, link, extra
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

            <?php if (!empty($separate['extra'])): ?>
                <p class="tarif-separate__text"><?= View::e($separate['extra']['text']) ?></p>

                <a class="btn btn--outline" href="<?= View::e($separate['extra']['link']['href']) ?>">
                    <?= View::e($separate['extra']['link']['label']) ?>
                    <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
