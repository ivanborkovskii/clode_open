<?php
/**
 * Таблица в две-три колонки на странице услуги.
 *
 * Нужна там, где содержание само по себе парное: что было слева, чем
 * становится справа. Списком такое читается хуже — глаз теряет пару.
 *
 * Своей вёрстки не имеет: берёт оформление таблицы сравнения тарифов,
 * вместе с боковой прокруткой на узком экране. Полоса прокрутки
 * принадлежит таблице, а не странице, поэтому сама страница вбок
 * не едет.
 *
 * @var array $table label, title, lead, head (заголовки колонок),
 *                   rows (строки, по ячейке на колонку), note
 */

use App\Core\View;
?>
<section class="section section--tight tarify-compare">
    <div class="container">
        <div class="section-head">
            <?php if (!empty($table['label'])): ?>
                <p class="label"><?= View::e($table['label']) ?></p>
            <?php endif; ?>
            <h2><?= View::e($table['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($table['lead']) ?></p>
        </div>

        <?php // tabindex — чтобы таблицу можно было прокрутить вбок
              // и с клавиатуры, не только пальцем или мышью. ?>
        <div class="tarif-table__scroll" tabindex="0" role="region"
             aria-label="<?= View::e($table['title']) ?>">
            <?php // Разновидность «с текстом»: без неё колонки сжимаются
                  // до узких и встают по центру — так сделано для сравнения
                  // тарифов, где в них галочки, а не предложения. ?>
            <table class="tarif-table tarif-table--text">
                <thead>
                    <tr>
                        <?php foreach ($table['head'] as $cell): ?>
                            <th scope="col"><?= View::e($cell) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($table['rows'] as $row): ?>
                        <tr>
                            <?php foreach ($row as $i => $cell): ?>
                                <?php if ($i === 0): ?>
                                    <th scope="row"><?= View::e($cell) ?></th>
                                <?php else: ?>
                                    <td><?= View::e($cell) ?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($table['note'])): ?>
            <p class="section-head__lead"><?= View::e($table['note']) ?></p>
        <?php endif; ?>
    </div>
</section>
