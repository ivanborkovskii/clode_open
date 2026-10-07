<?php
/**
 * Стоимость услуги.
 *
 * Отдельный блок, а не строка в перечне: у сопровождения цена известна
 * и понятна, и прятать её в текст нет причин. У остальных услуг работа
 * считается по объёму, поэтому блока там нет — «от» для них не выдумываем.
 *
 * Срок — вторая цифра того же блока, необязательная. Нужна услугам,
 * которые продаются проектом: там «сколько стоит» и «сколько займёт» —
 * один и тот же вопрос, и разносить их по странице незачем. Цифра срока
 * мельче цены: цена остаётся главной.
 *
 * @var array $price title, value, unit, items, note, term
 */

use App\Core\View;
?>
<section class="section section--tight price-block">
    <div class="container">
        <div class="price-block__inner">
            <div class="price-block__figure">
                <p class="label">Стоимость</p>
                <p class="price-block__value"><?= View::e($price['value']) ?></p>
                <p class="price-block__unit"><?= View::e($price['unit']) ?></p>

                <?php if (!empty($price['term'])): ?>
                    <p class="label price-block__term-label">Срок</p>
                    <p class="price-block__value price-block__value--term"><?= View::e($price['term']['value']) ?></p>
                    <p class="price-block__unit"><?= View::e($price['term']['unit']) ?></p>
                <?php endif; ?>
            </div>

            <div class="price-block__body">
                <h2><?= View::e($price['title']) ?></h2>

                <ul class="price-block__list">
                    <?php foreach ($price['items'] as $item): ?>
                        <li><?= View::e($item) ?></li>
                    <?php endforeach; ?>
                </ul>

                <?php if (!empty($price['note'])): ?>
                    <p class="price-block__note"><?= View::e($price['note']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
