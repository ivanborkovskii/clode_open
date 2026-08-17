<?php
/**
 * Кейсы по конкретной услуге. Короткая карточка: скриншот, что было
 * до внедрения и что изменилось. Подробный разбор — в разделе «Кейсы»,
 * ссылка появляется, когда раздел разработан.
 *
 * @var array $cases title, items
 */

use App\Core\View;
?>
<section class="section section--alt service-cases">
    <div class="container">
        <div class="section-head">
            <p class="label">Опыт</p>
            <h2><?= View::e($cases['title']) ?></h2>
        </div>

        <?php // Один кейс в сетке из двух колонок занимал бы половину ширины
              // и выглядел бы недоделанным. Поэтому раскладка другая:
              // снимок слева, разбор справа. ?>
        <div class="scase__grid<?= count($cases['items']) === 1 ? ' scase__grid--one' : '' ?>">
            <?php foreach ($cases['items'] as $case): ?>
                <?php
                $href = '/keysy/' . $case['slug'];
                // Какой именно снимок показать. По умолчанию первый, но если
                // он уже стоит в шапке страницы, берётся другой — одна и та же
                // картинка дважды на странице выглядит небрежно.
                $shot = '/assets/img/cases/' . $case['slug'] . '-' . ($case['shot'] ?? 1);
                ?>

                <article class="scase">
                    <img class="scase__shot"
                         src="<?= View::e($shot) ?>.webp"
                         srcset="<?= View::e($shot) ?>-sm.webp 600w,
                                 <?= View::e($shot) ?>.webp 1200w"
                         <?php // Ширина блока: до 1024 px — одна колонка, дальше две,
                               // а на широком экране контейнер упирается в 1280 px
                               // и колонка перестаёт расти. Без последнего значения
                               // браузер грузил версию 1200 px в блок шириной 570 px. ?>
                         sizes="<?= count($cases['items']) === 1
                             ? '(max-width: 1023px) 92vw, 600px'
                             : '(max-width: 1023px) 92vw, (max-width: 1399px) 46vw, 600px' ?>"
                         alt="<?= View::e($case['alt']) ?>"
                         width="1200" height="673" loading="lazy" decoding="async">

                    <div class="scase__body">
                        <p class="scase__company"><?= View::e($case['company']) ?></p>
                        <h3 class="scase__title"><?= View::e($case['title']) ?></h3>

                        <p class="scase__before">
                            <span>Было</span>
                            <?= View::e($case['before']) ?>
                        </p>

                        <p class="scase__result">
                            <span>Стало</span>
                            <?= View::e($case['result']) ?>
                        </p>

                        <?php if ($view->exists($href)): ?>
                            <a class="link-arrow" href="<?= View::e($href) ?>">
                                Разбор проекта
                                <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
