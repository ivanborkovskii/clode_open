<?php
/**
 * Кейсы. Скриншот портала и рядом разбор: задача → что было → результат.
 *
 * Раскладка рядами, а не тремя колонками: скриншоты CRM плотные,
 * в узкой карточке они превращались в цветной шум. В ряду картинка
 * получает больше половины ширины, и структура портала читается.
 *
 * @var array $cases
 */

use App\Core\View;

// Уровень заголовка в карточке зависит от того, есть ли над ними
// заголовок раздела.
//
// Блок используется в двух местах. Внутри страницы, где выше стоит
// <h2> раздела, названия компаний — это подзаголовки, то есть <h3>.
// На отдельной странице «Кейсы» заголовка раздела нет: там сразу
// после <h1> страницы идут карточки, и они должны быть <h2>.
//
// Раньше уровень был жёстко проставлен как <h3>, и на странице
// «Кейсы» получался пропуск: <h1>, а следом сразу <h3>. Для человека
// со скринридером это выглядит так, будто раздел потерялся, и
// проверка W3C считает это ошибкой.
$caseLevel = !empty($cases['title']) ? 'h3' : 'h2';
?>
<section class="section" id="keysy">
    <div class="container">
        <?php if (!empty($cases['title'])): ?>
        <div class="section-head">
            <p class="label">Кейсы</p>
            <h2><?= View::e($cases['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($cases['lead']) ?></p>
        </div>
        <?php endif; ?>

        <div class="cases">
            <?php foreach ($cases['items'] as $case): ?>
                <?php // Разбор кейса может быть ещё не готов — тогда ссылки нет. ?>
                <?php $ready = $view->exists($case['href']); ?>

                <article class="case">
                    <?php if ($ready): ?>
                    <a class="case__shot" href="<?= View::e($case['href']) ?>" tabindex="-1" aria-hidden="true">
                    <?php else: ?>
                    <div class="case__shot">
                    <?php endif; ?>
                        <img src="/assets/img/cases/<?= View::e($case['slug']) ?>-1.webp"
                             srcset="/assets/img/cases/<?= View::e($case['slug']) ?>-1-sm.webp 600w,
                                     /assets/img/cases/<?= View::e($case['slug']) ?>-1.webp 1200w"
                             sizes="(max-width: 1023px) 92vw, 52vw"
                             alt="<?= View::e($case['alt']) ?>"
                             width="1200" height="675" loading="lazy" decoding="async">
                    <?= $ready ? '</a>' : '</div>' ?>

                    <div class="case__body">
                        <header class="case__head">
                            <div>
                                <<?= $caseLevel ?> class="case__company"><?= View::e($case['company']) ?></<?= $caseLevel ?>>
                                <p class="case__industry"><?= View::e($case['industry']) ?></p>
                            </div>
                            <span class="case__system"><?= View::e($case['system']) ?></span>
                        </header>

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

                        <?php if ($ready): ?>
                            <a class="link-arrow" href="<?= View::e($case['href']) ?>">
                                Разбор кейса
                                <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($cases['link']) && $view->exists($cases['link']['href'])): ?>
            <div class="cases__more">
                <a class="btn btn--outline" href="<?= View::e($cases['link']['href']) ?>">
                    <?= View::e($cases['link']['label']) ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
