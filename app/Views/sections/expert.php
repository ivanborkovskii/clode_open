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
                <img class="expert__photo"
                     src="<?= View::e($expert['photo']) ?>"
                     srcset="<?= View::e($expert['photo_sm']) ?> 400w, <?= View::e($expert['photo']) ?> 720w"
                     sizes="(max-width: 1023px) 100vw, 420px"
                     alt="<?= View::e($expert['name']) ?> — <?= View::e($expert['role']) ?>"
                     width="720" height="960" loading="lazy" decoding="async">

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
                    <h3><?= View::e($expert['certificates']['title']) ?></h3>

                    <ul class="certs">
                        <?php foreach ($expert['certificates']['items'] as $cert): ?>
                            <li class="cert">
                                <a class="cert__link"
                                   href="/assets/img/certificates/<?= View::e($cert['slug']) ?>.webp"
                                   target="_blank" rel="noopener">
                                    <img src="/assets/img/certificates/<?= View::e($cert['slug']) ?>-sm.webp"
                                         alt="<?= View::e($cert['alt']) ?>"
                                         width="520" height="368" loading="lazy" decoding="async">
                                </a>
                                <div class="cert__text">
                                    <b><?= View::e($cert['name']) ?></b>
                                    <span><?= View::e($cert['role']) ?></span>
                                    <?php if ($cert['note'] !== ''): ?>
                                        <small><?= View::e($cert['note']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
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
                            <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
