<?php
/**
 * Снимки экранов на странице услуги.
 *
 * Отличие от галереи кейса: там кадры нумерованные и лежат в папке
 * проекта, здесь у каждого своё имя в assets/img/uslugi. Услуга живёт
 * дольше кейса, и кадры в ней меняются по одному, а не всей пачкой —
 * нумеровать их значило бы при каждой замене сдвигать остальные.
 *
 * Разметка и классы те же, что у галереи кейса: картинки открываются
 * общим просмотрщиком по data-zoom, сетка .shots уже описана в стилях.
 * Мелкий текст на скриншотах Битрикс24 иначе не прочесть.
 *
 * Адреса идут через View::asset — с номером версии статики. Браузерам
 * велено держать webp полгода, и без версии замена кадра на том же
 * имени полгода не доезжала бы до посетителя.
 *
 * @var array $shots label, title, lead, items[] (file, alt, w, h)
 */

use App\Core\View;
?>
<section class="section section--alt case-gallery">
    <div class="container">
        <div class="section-head">
            <p class="label"><?= View::e($shots['label'] ?? 'Экраны') ?></p>
            <h2><?= View::e($shots['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($shots['lead']) ?></p>
        </div>

        <ul class="shots">
            <?php foreach ($shots['items'] as $shot): ?>
                <?php
                $base  = 'img/uslugi/' . $shot['file'];
                $full  = $view->asset($base . '.webp');
                $small = $view->asset($base . '-sm.webp');
                ?>
                <li>
                    <a class="shots__item"
                       href="<?= View::e($full) ?>"
                       data-zoom="<?= View::e($shot['alt']) ?>"
                       title="Посмотреть в полном размере">
                        <img src="<?= View::e($small) ?>"
                             srcset="<?= View::e($small) ?> 600w,
                                     <?= View::e($full) ?> 1200w"
                             sizes="(max-width: 767px) 92vw, (max-width: 1023px) 46vw, 30vw"
                             alt="<?= View::e($shot['alt']) ?>"
                             width="<?= (int) $shot['w'] ?>" height="<?= (int) $shot['h'] ?>"
                             loading="lazy" decoding="async">
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
