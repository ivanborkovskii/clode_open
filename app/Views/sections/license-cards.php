<?php
/**
 * Карточки программных лицензий.
 *
 * Отличие от карточек облачных тарифов (sections/tarif-plans): там цена
 * за месяц и за год при годовой оплате, здесь — одна сумма за лицензию
 * на срок. Писать «109 000 ₽/мес.» было бы неправдой, а делать вид, что
 * это один и тот же блок, — хуже, чем завести второй.
 *
 * Оформление берётся у тарифов целиком: те же классы .tarifs и .tarif,
 * своих стилей у блока нет. Добавлен только модификатор сетки на три
 * колонки — редакций шесть, и в четыре они встают 4 + 2.
 *
 * Под названием редакции стоит её официальное лицензионное имя. Это
 * не украшение: именно так позиция называется в прайсе и в документах,
 * и именно так её ищут. Текстом, а не картинкой.
 *
 * Кнопка ведёт к форме заявки. Со скриптом в поле «Задача» подставляется
 * название редакции (см. data-licenziya и main.js), без скрипта — просто
 * переход к форме, как у любой другой кнопки на сайте.
 *
 * @var array $cards label, title, lead, cta, items, note
 */

use App\Core\Text;
use App\Core\View;
?>
<section class="section tarify">
    <div class="container">
        <div class="section-head">
            <p class="label"><?= View::e($cards['label'] ?? 'Лицензии') ?></p>
            <h2><?= View::e($cards['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($cards['lead']) ?></p>
        </div>

        <div class="tarifs tarifs--three">
            <?php foreach ($cards['items'] as $card): ?>
                <article class="tarif">
                    <h3 class="tarif__name"><?= View::e($card['name']) ?></h3>
                    <p class="tarif__users"><?= View::e($card['users']) ?></p>

                    <p class="tarif__price">
                        <?= View::e(Text::money($card['price'])) ?>
                        <span class="tarif__per"><?= View::e($card['term']) ?></span>
                    </p>

                    <p class="tarif__year"><?= View::e($card['official']) ?></p>

                    <p class="tarif__title">Когда подходит</p>

                    <ul class="tarif__list">
                        <?php foreach ($card['add'] as $feature): ?>
                            <li><?= View::e($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <a class="btn btn--primary" href="#zayavka"
                       data-licenziya="<?= View::e($card['request']) ?>">
                        <?= View::e($cards['cta']) ?>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($cards['note'])): ?>
            <p class="tarif-note"><?= View::e($cards['note']) ?></p>
        <?php endif; ?>
    </div>
</section>
