<?php
/**
 * Кто ведёт проект. Для услуги, которую оказывает конкретный человек,
 * это сильнее любых обещаний «команды профессионалов».
 *
 * @var array $expert
 */

use App\Core\View;
?>
<section class="section section--alt" id="ekspert">
    <div class="container">
        <div class="section-head">
            <p class="label">Эксперт</p>
            <h2><?= View::e($expert['title']) ?></h2>
        </div>

        <div class="expert__layout">
            <div class="expert__card">
                <?php if (!empty($expert['photo'])): ?>
                    <img src="<?= View::e($expert['photo']) ?>"
                         alt="<?= View::e($expert['name']) ?>, <?= View::e($expert['role']) ?>"
                         width="800" height="600" loading="lazy" decoding="async">
                <?php else: ?>
                    <?php // TODO: нужна фотография — заглушка видна намеренно. ?>
                    <div class="expert__photo-slot">Здесь будет фотография — нужен файл от заказчика</div>
                <?php endif; ?>

                <p class="expert__name"><?= View::e($expert['name']) ?></p>
                <p class="expert__role"><?= View::e($expert['role']) ?></p>
                <p class="expert__bio"><?= View::e($expert['bio']) ?></p>

                <div class="expert__stats">
                    <?php foreach ($expert['experience'] as $item): ?>
                        <div class="expert__stat">
                            <b><?= View::e($item['value']) ?></b>
                            <span><?= View::e($item['label']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <div class="expert__block">
                    <h3><?= View::e($expert['industries']['title']) ?></h3>

                    <div class="expert__industries">
                        <?php foreach ($expert['industries']['items'] as $row): ?>
                            <div class="expert__industry">
                                <b><?= (int) $row['count'] ?></b>
                                <span><?= View::e($row['label']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="expert__block">
                    <h3><?= View::e($expert['principles']['title']) ?></h3>

                    <ul class="expert__principles">
                        <?php foreach ($expert['principles']['items'] as $item): ?>
                            <li><span><?= View::e($item) ?></span></li>
                        <?php endforeach; ?>
                    </ul>

                    <p style="margin-top: var(--s-6);">
                        <a class="link-arrow" href="<?= View::e($expert['link']['href']) ?>">
                            <?= View::e($expert['link']['label']) ?>
                            <svg width="16" height="12" viewBox="0 0 16 12" fill="none" aria-hidden="true">
                                <path d="M10 1l5 5-5 5M15 6H0" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
