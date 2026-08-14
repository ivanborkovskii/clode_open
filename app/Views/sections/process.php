<?php
/**
 * Как строится работа: этапы и принцип выбора инструментов.
 *
 * @var array $process
 */

use App\Core\View;
?>
<section class="section section--alt" id="kak-rabotaem">
    <div class="container">
        <div class="section-head">
            <p class="label">Порядок работ</p>
            <h2><?= View::e($process['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($process['lead']) ?></p>
        </div>

        <div class="process__grid">
            <?php foreach ($process['steps'] as $step): ?>
                <article class="process__step">
                    <span class="process__num"><?= View::e($step['num']) ?></span>
                    <h3><?= View::e($step['title']) ?></h3>
                    <p><?= View::e($step['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="approach">
            <p class="approach__title"><?= View::e($process['approach']['title']) ?></p>

            <div class="approach__row">
                <?php foreach ($process['approach']['items'] as $i => $item): ?>
                    <div class="approach__item">
                        <?php
                        // Шкала сложности: закрашено столько делений, какой это шаг.
                        // Видно, что программирование — крайняя мера, а не первый выбор.
                        ?>
                        <span class="approach__scale" aria-hidden="true">
                            <?php for ($n = 0; $n < 3; $n++): ?>
                                <i class="<?= $n <= $i ? 'is-on' : '' ?>"></i>
                            <?php endfor; ?>
                        </span>

                        <span class="approach__label"><?= View::e($item['label']) ?></span>
                        <span class="approach__text"><?= View::e($item['text']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
