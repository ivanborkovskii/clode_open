<?php
/**
 * «Вам может быть интересно»: четыре статьи после текста.
 *
 * Порядок отбора задаёт запрос: сначала статьи с общими темами, затем той
 * же категории, затем любые свежие. Карточка здесь короче, чем в списке
 * раздела: без обложки во всю ширину и без тегов.
 *
 * @var array $related
 * @var array $texts
 */

use App\Core\Text;
use App\Core\View;

if ($related === []) {
    return;
}
?>
<section class="section section--tight article-related">
    <div class="container">
        <div class="section-head section-head--center">
            <h2><?= View::e($texts['article']['related']) ?></h2>
        </div>

        <div class="related">
            <?php foreach ($related as $article): ?>
                <?php $href = '/stati/' . $article['slug']; ?>

                <article class="rcard">
                    <?php if ($article['cover'] !== ''): ?>
                        <a class="rcard__cover" href="<?= View::e($href) ?>"
                           tabindex="-1" aria-hidden="true">
                            <img src="<?= View::e($article['cover']) ?>"
                                 alt="<?= View::e($article['cover_alt']) ?>"
                                 <?php if ((int) $article['cover_width'] > 0): ?>
                                 width="<?= (int) $article['cover_width'] ?>"
                                 height="<?= (int) $article['cover_height'] ?>"
                                 <?php endif; ?>
                                 loading="lazy" decoding="async">
                        </a>
                    <?php endif; ?>

                    <h3 class="rcard__title">
                        <a href="<?= View::e($href) ?>"><?= View::e($article['title']) ?></a>
                    </h3>

                    <p class="rcard__meta">
                        <time datetime="<?= View::e($article['published_at']) ?>">
                            <?= View::e(Text::date((string) $article['published_at'])) ?>
                        </time>
                        <?php if ((int) $article['reading_time'] > 0): ?>
                            <span><?= (int) $article['reading_time'] ?> мин</span>
                        <?php endif; ?>
                        <span><?= View::e($article['category_name']) ?></span>
                    </p>

                    <?php if ($article['excerpt'] !== ''): ?>
                        <p class="rcard__excerpt"><?= View::e(Text::excerpt((string) $article['excerpt'], 140)) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
