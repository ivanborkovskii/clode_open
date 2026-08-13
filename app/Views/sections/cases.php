<?php
/**
 * Кейсы. Скриншот портала, затем задача → что было → результат.
 * Тексты и изображения — из материалов заказчика.
 *
 * @var array $cases
 */

use App\Core\View;
?>
<section class="section" id="keysy">
    <div class="container">
        <div class="section-head">
            <p class="label">Кейсы</p>
            <h2><?= View::e($cases['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($cases['lead']) ?></p>
        </div>

        <div class="cases__grid">
            <?php foreach ($cases['items'] as $case): ?>
                <article class="case">
                    <a class="case__shot" href="<?= View::e($case['href']) ?>" tabindex="-1" aria-hidden="true">
                        <img src="/assets/img/cases/<?= View::e($case['slug']) ?>-1-sm.webp"
                             alt="<?= View::e($case['alt']) ?>"
                             width="600" height="338" loading="lazy" decoding="async">
                    </a>

                    <header class="case__head">
                        <div>
                            <h3 class="case__company"><?= View::e($case['company']) ?></h3>
                            <p class="case__industry"><?= View::e($case['industry']) ?></p>
                        </div>
                        <span class="case__system"><?= View::e($case['system']) ?></span>
                    </header>

                    <div class="case__body">
                        <div class="case__row">
                            <p class="case__row-title">Задача</p>
                            <p><?= View::e($case['task']) ?></p>
                        </div>

                        <div class="case__row">
                            <p class="case__row-title">Было</p>
                            <p><?= View::e($case['before']) ?></p>
                        </div>

                        <div class="case__row case__row--result">
                            <p class="case__row-title">Результат</p>
                            <p><?= View::e($case['result']) ?></p>
                        </div>
                    </div>

                    <div class="case__foot">
                        <a class="link-arrow" href="<?= View::e($case['href']) ?>">
                            Разбор кейса
                            <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="cases__more">
            <a class="btn btn--outline" href="<?= View::e($cases['link']['href']) ?>">
                <?= View::e($cases['link']['label']) ?>
            </a>
        </div>
    </div>
</section>
