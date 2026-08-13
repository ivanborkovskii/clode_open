<?php
/**
 * Решения: что именно закрывает CRM по четырём направлениям.
 *
 * @var array $solutions
 */

use App\Core\View;
?>
<section class="section section--dark" id="resheniya">
    <div class="container">
        <div class="section-head">
            <p class="label">Решения</p>
            <h2><?= View::e($solutions['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($solutions['lead']) ?></p>
        </div>

        <div class="solutions__grid">
            <?php foreach ($solutions['items'] as $item): ?>
                <article class="solutions__col">
                    <h3><?= View::e($item['title']) ?></h3>

                    <ul class="solutions__list">
                        <?php foreach ($item['points'] as $point): ?>
                            <li><?= View::e($point) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <a class="link-arrow" href="<?= View::e($item['href']) ?>">
                        Подробнее
                        <svg width="16" height="12" viewBox="0 0 16 12" fill="none" aria-hidden="true">
                            <path d="M10 1l5 5-5 5M15 6H0" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
