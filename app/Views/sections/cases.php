<?php
/**
 * Кейсы. Структура одинаковая: задача → что было → результат.
 * Тексты взяты из описаний проектов заказчика.
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
                            <svg width="16" height="12" viewBox="0 0 16 12" fill="none" aria-hidden="true">
                                <path d="M10 1l5 5-5 5M15 6H0" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
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
