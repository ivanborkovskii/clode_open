<?php
/**
 * Текст статьи и её темы.
 *
 * Разметка приходит из админки и перед выводом чистится: остаются только
 * разрешённые теги, без обработчиков событий и ссылок вида javascript:.
 * Экранировать текст целиком нельзя — тогда на странице будут видны сами
 * теги вместо заголовков и списков.
 *
 * @var array $article
 * @var array $texts
 */

use App\Core\Text;
use App\Core\View;
?>
<section class="section section--tight">
    <div class="container container--narrow">
        <div class="prose">
            <?= Text::safeHtml((string) $article['body']) ?>
        </div>

        <?php if ($article['tags'] !== []): ?>
            <div class="article-tags">
                <p class="article-tags__title"><?= View::e($texts['article']['tags']) ?></p>
                <ul class="chips">
                    <?php foreach ($article['tags'] as $tag): ?>
                        <li>
                            <a class="chip" href="/stati?tegi=<?= View::e($tag['slug']) ?>">
                                <?= View::e($tag['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</section>
