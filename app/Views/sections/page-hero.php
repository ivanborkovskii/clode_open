<?php
/**
 * Шапка внутренней страницы: крошки, заголовок, лид и снимок экрана.
 *
 * Намеренно короткая, в отличие от первого экрана главной. На внутреннюю
 * страницу приходят с готовым вопросом, и большой экран-заставка только
 * отодвигает ответ вниз.
 *
 * Снимок необязателен. Если он есть, раскладка становится двухколоночной:
 * слева текст, справа окно со скриншотом. Второй снимок показывается
 * поверх первого со смещением — так на странице раздела видно, что работа
 * идёт с двумя системами.
 *
 * @var array $hero   title, lead, text, media
 * @var array $crumbs Хлебные крошки
 */

use App\Core\View;

$shots = $hero['media']['shots'] ?? [];
?>
<section class="page-hero<?= $shots ? ' page-hero--media' : '' ?>">
    <div class="container">
        <?php $view->partial('partials/breadcrumbs', ['crumbs' => $crumbs]); ?>

        <div class="page-hero__inner">
            <div class="page-hero__text-col">
                <h1><?= View::e($hero['title']) ?></h1>
                <p class="page-hero__lead"><?= View::e($hero['lead']) ?></p>

                <?php if (!empty($hero['text'])): ?>
                    <p class="page-hero__text"><?= View::e($hero['text']) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($shots): ?>
                <div class="page-hero__media">
                    <?php // Окна лежат в отдельной обёртке: подпись под ними
                          // не должна попадать под второе, смещённое окно. ?>
                    <div class="page-hero__frames<?= count($shots) > 1 ? ' page-hero__frames--pair' : '' ?>">
                    <?php foreach ($shots as $i => $shot): ?>
                        <figure class="browser<?= $i > 0 ? ' browser--sub' : '' ?>">
                            <div class="browser__bar">
                                <span class="browser__dots"><i></i><i></i><i></i></span>
                                <span class="browser__url"><?= View::e($shot['url']) ?></span>
                            </div>

                            <?php // Размеры проставлены явно — вёрстка не «прыгает» при загрузке. ?>
                            <img class="browser__shot"
                                 src="/assets/img/hero/<?= View::e($shot['src']) ?>.webp"
                                 srcset="/assets/img/hero/<?= View::e($shot['src']) ?>-sm.webp 600w,
                                         /assets/img/hero/<?= View::e($shot['src']) ?>.webp 1200w"
                                 sizes="(max-width: 1023px) 92vw, 600px"
                                 width="<?= (int) $shot['w'] ?>" height="<?= (int) $shot['h'] ?>"
                                 alt="<?= View::e($shot['alt']) ?>"
                                 <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
                        </figure>
                    <?php endforeach; ?>
                    </div>

                    <?php if (!empty($hero['media']['caption'])): ?>
                        <p class="page-hero__caption"><?= View::e($hero['media']['caption']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
