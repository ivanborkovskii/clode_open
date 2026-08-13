<?php
/**
 * Услуги.
 *
 * Одна разметка на все экраны: на десктопе — список слева и раскрытая
 * карточка справа, на планшете и мобильном тот же список работает
 * как аккордеон. Раскладку переключает CSS, скрипт только меняет активный пункт.
 *
 * @var array $services
 */

use App\Core\View;
?>
<section class="section section--alt" id="uslugi">
    <div class="container">
        <div class="section-head">
            <p class="label">Услуги</p>
            <h2><?= View::e($services['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($services['lead']) ?></p>
        </div>

        <div class="services__layout" data-tabs>
            <?php foreach ($services['items'] as $i => $item): ?>
                <button class="services__tab"
                        type="button"
                        role="tab"
                        id="tab-<?= View::e($item['slug']) ?>"
                        aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                        aria-controls="panel-<?= View::e($item['slug']) ?>"
                        data-tab>
                    <?= View::e($item['title']) ?>
                </button>

                <div class="services__panel"
                     id="panel-<?= View::e($item['slug']) ?>"
                     role="tabpanel"
                     aria-labelledby="tab-<?= View::e($item['slug']) ?>"
                     data-panel
                     <?= $i === 0 ? '' : 'hidden' ?>>

                    <h3><?= View::e($item['title']) ?></h3>
                    <p class="services__panel-text"><?= View::e($item['text']) ?></p>

                    <ul class="services__points">
                        <?php foreach ($item['points'] as $point): ?>
                            <li><span><?= View::e($point) ?></span></li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (!empty($item['note'])): ?>
                        <p class="services__note"><?= View::e($item['note']) ?></p>
                    <?php endif; ?>

                    <div class="services__panel-actions">
                        <a class="link-arrow" href="<?= View::e($item['href']) ?>">
                            Подробнее об услуге
                            <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
