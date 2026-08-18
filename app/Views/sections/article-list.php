<?php
/**
 * Список статей карточками и постраничная навигация.
 *
 * Карточка нужна, чтобы решить, читать статью или нет: заголовок, дата,
 * время чтения, категория и короткий анонс. Обложка — ссылка на ту же
 * статью, но скрыта от чтения с экрана: два одинаковых перехода подряд
 * только мешают.
 *
 * @var array $list       items, total, page, pages
 * @var array $pagination
 * @var array $texts
 * @var bool  $filtered   Применён ли отбор: от этого зависит текст
 *                        при пустом списке
 */

use App\Core\Text;
use App\Core\View;
?>
<section class="section section--tight">
    <div class="container">
        <?php if ($list['items'] === []): ?>
            <p class="articles__empty">
                <?= View::e($filtered ? $texts['list']['nothing'] : $texts['list']['empty']) ?>
            </p>
        <?php else: ?>
            <div class="articles">
                <?php foreach ($list['items'] as $article): ?>
                    <?php $href = '/stati/' . $article['slug']; ?>

                    <article class="acard">
                        <?php if ($article['cover'] !== ''): ?>
                            <a class="acard__cover" href="<?= View::e($href) ?>"
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

                        <div class="acard__body">
                            <h2 class="acard__title">
                                <a href="<?= View::e($href) ?>"><?= View::e($article['title']) ?></a>
                            </h2>

                            <p class="acard__meta">
                                <time datetime="<?= View::e($article['published_at']) ?>">
                                    <?= View::e(Text::date((string) $article['published_at'])) ?>
                                </time>
                                <?php if ((int) $article['reading_time'] > 0): ?>
                                    <span>Время чтения: <?= (int) $article['reading_time'] ?> мин</span>
                                <?php endif; ?>
                                <a class="acard__category"
                                   href="/stati/kategoriya/<?= View::e($article['category_slug']) ?>">
                                    <?= View::e($article['category_name']) ?>
                                </a>
                            </p>

                            <?php if ($article['author'] !== ''): ?>
                                <p class="acard__author">Автор: <?= View::e($article['author']) ?></p>
                            <?php endif; ?>

                            <?php if ($article['excerpt'] !== ''): ?>
                                <p class="acard__excerpt"><?= View::e($article['excerpt']) ?></p>
                            <?php endif; ?>

                            <?php if ($article['tags'] !== []): ?>
                                <ul class="acard__tags">
                                    <?php foreach ($article['tags'] as $tag): ?>
                                        <li>
                                            <a href="/stati?tegi=<?= View::e($tag['slug']) ?>">
                                                <?= View::e($tag['name']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($pagination['pages'] > 1): ?>
            <nav class="pager" aria-label="Страницы списка статей">
                <?php if ($pagination['prev'] !== ''): ?>
                    <a class="pager__step" href="<?= View::e($pagination['prev']) ?>" rel="prev">Назад</a>
                <?php endif; ?>

                <ul class="pager__list">
                    <?php foreach ($pagination['items'] as $item): ?>
                        <li>
                            <?php if ($item['active']): ?>
                                <span class="pager__page pager__page--on" aria-current="page">
                                    <?= (int) $item['number'] ?>
                                </span>
                            <?php else: ?>
                                <a class="pager__page" href="<?= View::e($item['href']) ?>">
                                    <?= (int) $item['number'] ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($pagination['next'] !== ''): ?>
                    <a class="pager__step" href="<?= View::e($pagination['next']) ?>" rel="next">Дальше</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
