<?php
/**
 * Блок статей на главной: три свежие публикации.
 *
 * Статьи приходят из базы. Если их ещё нет — или база недоступна, —
 * блок показывает состояние «пусто»: вымышленных заголовков здесь
 * не бывает.
 *
 * @var array $articles Тексты блока
 * @var array $items    Три последние статьи из базы
 */

use App\Core\Text;
use App\Core\View;

$items = $items ?? [];
?>
<section class="section" id="stati">
    <div class="container">
        <div class="articles__head">
            <div class="section-head">
                <p class="label">Статьи</p>
                <h2><?= View::e($articles['title']) ?></h2>
                <p class="section-head__lead"><?= View::e($articles['lead']) ?></p>
            </div>

            <?php if ($view->exists($articles['link']['href'])): ?>
            <a class="btn btn--outline" href="<?= View::e($articles['link']['href']) ?>">
                <?= View::e($articles['link']['label']) ?>
            </a>
            <?php endif; ?>
        </div>

        <?php if ($items === []): ?>
            <p class="articles__empty"><?= View::e($articles['placeholder']) ?></p>
        <?php else: ?>
            <div class="articles__grid">
                <?php foreach ($items as $item): ?>
                    <?php
                    // Адрес статьи проверять через $view->exists() не нужно:
                    // она пришла из базы, значит страница существует.
                    ?>
                    <article class="article-card">
                        <p class="article-card__meta">
                            <time datetime="<?= View::e($item['published_at']) ?>">
                                <?= View::e(Text::date((string) $item['published_at'])) ?>
                            </time>
                            <span><?= View::e($item['category_name']) ?></span>
                        </p>

                        <h3><?= View::e($item['title']) ?></h3>
                        <p><?= View::e(Text::excerpt((string) $item['excerpt'], 160)) ?></p>

                        <a class="link-arrow" href="/stati/<?= View::e($item['slug']) ?>">
                            Читать
                            <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
