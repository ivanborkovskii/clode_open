<?php
/**
 * Галерея экранов проекта.
 *
 * Снимки открываются тем же просмотрщиком, что и сертификаты: клик
 * разворачивает картинку поверх страницы, повторный клик увеличивает
 * до настоящего размера. Мелкий текст на скриншотах CRM иначе не прочесть.
 *
 * @var string $slug    Папка кейса в assets/img/cases
 * @var array  $gallery title, lead, shots
 */

use App\Core\View;
?>
<section class="section section--alt case-gallery">
    <div class="container">
        <div class="section-head">
            <p class="label">Экраны</p>
            <h2><?= View::e($gallery['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($gallery['lead']) ?></p>
        </div>

        <ul class="shots">
            <?php foreach ($gallery['shots'] as $shot): ?>
                <?php $base = '/assets/img/cases/' . $slug . '-' . (int) $shot['n']; ?>
                <li>
                    <a class="shots__item"
                       href="<?= View::e($base) ?>.webp"
                       data-zoom="<?= View::e($shot['alt']) ?>"
                       title="Посмотреть в полном размере">
                        <img src="<?= View::e($base) ?>-sm.webp"
                             srcset="<?= View::e($base) ?>-sm.webp 600w,
                                     <?= View::e($base) ?>.webp 1200w"
                             sizes="(max-width: 767px) 92vw, (max-width: 1023px) 46vw, 30vw"
                             alt="<?= View::e($shot['alt']) ?>"
                             width="1200" height="673" loading="lazy" decoding="async">
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
