<?php
/**
 * Тарифы карточками.
 *
 * В карточке перечислено только то, что тариф добавляет к предыдущему:
 * тарифы накопительные, и повторять один и тот же список четыре раза —
 * стена текста, в которой не видно разницы, а разница и есть то,
 * ради чего сюда пришли.
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
                <article class="tarif">
                    <h3 class="tarif__name"><?= View::e($plan['name']) ?></h3>
                    <p class="tarif__users"><?= View::e($plan['users']) ?></p>

                    <p class="tarif__price">
                        <?php if ($plan['month'] === 0): ?>
                            Бесплатно
                        <?php else: ?>
                            <?= View::e(Text::money($plan['month'])) ?>
                            <span class="tarif__per">/мес.</span>
                        <?php endif; ?>
                    </p>

                    <?php // Строка держит высоту и у бесплатного тарифа —
                          // иначе карточки разъезжаются по высоте. ?>
                    <p class="tarif__year">
                        <?php if ($plan['year'] > 0 && $plan['year'] !== $plan['month']): ?>
                            <?= View::e(Text::money($plan['year'])) ?>/мес. при оплате за год
                        <?php endif; ?>
                    </p>

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
