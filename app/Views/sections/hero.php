<?php
/**
 * Первый экран.
 *
 * Справа — реальный скриншот портала в рамке браузера, слегка развёрнутый
 * в перспективе. Показывает результат работы вместо условной иллюстрации.
 *
 * @var array $hero
 */

use App\Core\View;
?>
<section class="hero">
    <div class="container hero__inner">
        <div class="hero__content">
            <p class="hero__eyebrow"><?= View::e($hero['eyebrow']) ?></p>

            <h1><?= View::e($hero['h1']) ?></h1>

            <p class="hero__lead"><?= View::e($hero['lead']) ?></p>
            <p class="hero__note"><?= View::e($hero['note']) ?></p>

            <div class="hero__actions">
                <a class="btn btn--primary" href="<?= View::e($hero['cta']['href']) ?>">
                    <?= View::e($hero['cta']['label']) ?>
                    <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                </a>
                <a class="btn btn--ghost" href="<?= View::e($hero['cta_alt']['href']) ?>">
                    <?= View::e($hero['cta_alt']['label']) ?>
                </a>
            </div>
        </div>

        <div class="hero__figure">
            <figure class="browser">
                <div class="browser__bar">
                    <span class="browser__dots"><i></i><i></i><i></i></span>
                    <span class="browser__url"><?= View::e($hero['screenshot']['url']) ?></span>
                </div>

                <?php // Изображение с явными размерами — верстка не «прыгает» при загрузке. ?>
                <img class="browser__shot"
                     src="/assets/img/hero/portal.webp"
                     srcset="/assets/img/hero/portal-sm.webp 760w, /assets/img/hero/portal.webp 1280w"
                     sizes="(max-width: 1023px) 90vw, 620px"
                     width="1280" height="683"
                     alt="<?= View::e($hero['screenshot']['alt']) ?>"
                     fetchpriority="high" decoding="async">
            </figure>

            <p class="hero__caption"><?= View::e($hero['screenshot']['caption']) ?></p>
        </div>
    </div>
</section>
