<?php
/**
 * Полное сравнение тарифов таблицей.
 *
 * Нужна тому, кто проверяет состав построчно: в карточках выше видна
 * только разница между соседними тарифами.
 *
 * На узком экране таблица прокручивается вбок внутри своей рамки,
 * а не растягивает страницу.
 *
 * @var array $compare label, title, lead, features, head, yes, no
 * @var array $plans   Те же тарифы, что в карточках
 */

use App\Core\Text;
use App\Core\View;
?>
<section class="section section--tight section--alt tarify-compare">
    <div class="container">
        <div class="section-head">
            <p class="label"><?= View::e($compare['label']) ?></p>
            <h2><?= View::e($compare['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($compare['lead']) ?></p>
        </div>

        <?php // tabindex — чтобы таблицу можно было прокрутить вбок
              // и с клавиатуры, не только пальцем или мышью. ?>
        <div class="tarif-table__scroll" tabindex="0" role="region"
             aria-label="<?= View::e($compare['title']) ?>">
            <table class="tarif-table">
                <thead>
                    <tr>
                        <th scope="col"><?= View::e($compare['head']['feature']) ?></th>
                        <?php foreach ($plans as $plan): ?>
                            <th scope="col"><?= View::e($plan['name']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <th scope="row"><?= View::e($compare['head']['price']) ?></th>
                        <?php foreach ($plans as $plan): ?>
                            <td class="tarif-table__price">
                                <?php if ($plan['month'] === 0): ?>
                                    Бесплатно
                                <?php else: ?>
                                    <?= View::e(Text::money($plan['month'])) ?>/мес.
                                    <span class="tarif-table__year">
                                        <?= View::e(Text::money($plan['year'])) ?>/мес. за год
                                    </span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <tr>
                        <th scope="row"><?= View::e($compare['head']['users']) ?></th>
                        <?php foreach ($plans as $plan): ?>
                            <td><?= View::e(str_replace(' пользователей', '', $plan['users'])) ?></td>
                        <?php endforeach; ?>
                    </tr>

                    <?php foreach ($compare['features'] as $feature): ?>
                        <tr>
                            <th scope="row"><?= View::e($feature['name']) ?></th>
                            <?php foreach ($plans as $i => $plan): ?>
                                <?php $has = $i >= $feature['from']; ?>
                                <td class="<?= $has ? 'tarif-table__yes' : 'tarif-table__no' ?>">
                                    <?= View::e($has ? $compare['yes'] : $compare['no']) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
