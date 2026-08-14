<?php
/**
 * Блок статей.
 *
 * Раздел будет наполняться из БД (сотни и тысячи материалов).
 * Пока показываем состояние «пусто» — вымышленные заголовки не выводим.
 *
 * @var array $articles
 * @var array $items Список статей; пока всегда пустой.
 */

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

            <a class="btn btn--outline" href="<?= View::e($articles['link']['href']) ?>">
                <?= View::e($articles['link']['label']) ?>
            </a>
        </div>

        <?php if ($items === []): ?>
            <p class="articles__empty"><?= View::e($articles['placeholder']) ?></p>
        <?php else: ?>
            <div class="articles__grid">
                <?php foreach ($items as $item): ?>
                    <article class="article-card">
                        <h3><?= View::e($item['title']) ?></h3>
                        <p><?= View::e($item['excerpt']) ?></p>

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
