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
                <?php
                // Кнопка и её панель стоят рядом, парами: на десктопе это
                // выглядит вкладками, на мобильном — аккордеоном.
                //
                // Раньше здесь было role="tab" с role="tabpanel", и это
                // не проходило проверку: по стандарту все вкладки должны
                // лежать в общем контейнере role="tablist", а панели — вне
                // его. У нас они чередуются, иначе аккордеон не собрать.
                //
                // Поэтому объявление приведено к тому, что здесь на самом
                // деле есть: кнопка, раскрывающая свой блок. Для человека
                // со скринридером это понятнее прежнего — ему больше
                // не обещают вкладок, которых в разметке нет.
                ?>
                <button class="services__tab"
                        type="button"
                        id="tab-<?= View::e($item['slug']) ?>"
                        aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                        aria-controls="panel-<?= View::e($item['slug']) ?>"
                        data-tab>
                    <?= View::e($item['title']) ?>
                </button>

                <div class="services__panel"
                     id="panel-<?= View::e($item['slug']) ?>"
                     role="region"
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
