<?php
/**
 * Тарифы карточками.
 *
 * В карточке перечислено только то, что тариф добавляет к предыдущему:
 * тарифы накопительные, и повторять один и тот же список пять раз —
 * стена текста, в которой не видно разницы, а разница и есть то,
 * ради чего сюда пришли.
 *
 * ШИРОКАЯ КАРТОЧКА. Тариф с ключом 'wide' занимает всю ширину ряда.
 * Нужна Энтерпрайзу: у него не одна цена, а двенадцать ступеней
 * по числу пользователей, и выбор ступени в узкую колонку рядом
 * с остальными тарифами не помещается.
 *
 * ВЫБОР СТУПЕНИ. Со скриптом переключатель пересчитывает цену прямо
 * в карточке. Без скрипта он остаётся обычным списком, в котором
 * рядом с каждой ступенью написана её цена, — то есть цены видны
 * в любом случае. Полная сетка ступеней всё равно стоит таблицей
 * ниже по странице.
 *
 * @var array  $plans label, title, lead, cta, items
 * @var string $note  Примечание об актуальности цен
 */

use App\Core\Text;
use App\Core\View;
?>
<section class="section tarify">
    <div class="container">
        <div class="section-head">
            <p class="label"><?= View::e($plans['label']) ?></p>
            <h2><?= View::e($plans['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($plans['lead']) ?></p>
        </div>

        <div class="tarifs">
            <?php foreach ($plans['items'] as $plan): ?>
                <article class="tarif<?= empty($plan['wide']) ? '' : ' tarif--wide' ?>">
                    <h3 class="tarif__name"><?= View::e($plan['name']) ?></h3>
                    <p class="tarif__users"><?= View::e($plan['users']) ?></p>

                    <p class="tarif__price">
                        <?php if ($plan['month'] === 0): ?>
                            Бесплатно
                        <?php else: ?>
                            <span data-tarif-sum><?= View::e(Text::money($plan['month'])) ?></span>
                            <span class="tarif__per">/мес.</span>
                        <?php endif; ?>
                    </p>

                    <?php // Строка держит высоту и у бесплатного тарифа —
                          // иначе карточки разъезжаются по высоте. ?>
                    <p class="tarif__year">
                        <?php if ($plan['year'] > 0 && $plan['year'] !== $plan['month']): ?>
                            <span data-tarif-year><?= View::e(Text::money($plan['year'])) ?></span>/мес. при оплате за год
                        <?php endif; ?>
                    </p>

                    <?php // Ступени — только у Энтерпрайза. Рядом с каждой
                          // написана её цена: если скрипт не выполнится,
                          // цены всё равно прочитаны. ?>
                    <?php if (!empty($plan['scale'])): ?>
                        <p class="field tarif-pick">
                            <label class="field__label" for="tarif-scale">
                                <?= View::e($plan['pick']) ?>
                            </label>
                            <select class="field__control" id="tarif-scale" data-tarif-scale>
                                <?php foreach ($plan['scale'] as $step): ?>
                                    <option value="<?= View::e((string) $step['users']) ?>"
                                            data-month="<?= View::e(Text::money($step['month'])) ?>"
                                            data-year="<?= View::e(Text::money($step['year'])) ?>">
                                        <?= View::e(number_format($step['users'], 0, '', "\u{00A0}")) ?>
                                        — <?= View::e(Text::money($step['month'])) ?>/мес.
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($plan['note'])): ?>
                        <p class="tarif__note"><?= View::e($plan['note']) ?></p>
                    <?php endif; ?>

                    <p class="tarif__title"><?= View::e($plan['title']) ?></p>

                    <ul class="tarif__list">
                        <?php foreach ($plan['add'] as $feature): ?>
                            <li><?= View::e($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <a class="btn btn--primary" href="#zayavka"><?= View::e($plans['cta']) ?></a>
                </article>
            <?php endforeach; ?>
        </div>

        <p class="tarif-note"><?= View::e($note) ?></p>
    </div>
</section>
