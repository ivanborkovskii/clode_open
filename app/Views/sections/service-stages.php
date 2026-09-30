<?php
/**
 * Этапы работ по услуге.
 *
 * Нумерованная дорожка с вертикальной линией: этапы идут строго по порядку,
 * и линия показывает это лучше, чем восемь одинаковых карточек в сетке.
 *
 * У этапа может быть ссылка на смежную услугу — href и label. Нужна она
 * там, где этап сам по себе является отдельной работой: например, аудит
 * входит в доработку, но его можно заказать и отдельно. Ссылка выводится
 * только если та страница уже разработана, как и в блоке «что дальше».
 *
 * @var array $stages title, lead, items
 */

use App\Core\View;
?>
<section class="section section--alt stages" id="etapy">
    <div class="container">
        <div class="section-head">
            <p class="label">Порядок работ</p>
            <h2><?= View::e($stages['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($stages['lead']) ?></p>
        </div>

        <ol class="stage-track">
            <?php foreach ($stages['items'] as $i => $stage): ?>
                <li class="stage">
                    <div class="stage__marker" aria-hidden="true">
                        <?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?>
                    </div>

                    <div class="stage__body">
                        <h3 class="stage__title"><?= View::e($stage['title']) ?></h3>
                        <p class="stage__text"><?= View::e($stage['text']) ?></p>

                        <?php if (!empty($stage['href']) && $view->exists($stage['href'])): ?>
                            <a class="link-arrow" href="<?= View::e($stage['href']) ?>">
                                <?= View::e($stage['label']) ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
