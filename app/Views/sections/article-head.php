<?php
/**
 * Шапка статьи: крошки, заголовок, выходные данные и обложка.
 *
 * Дата, время чтения и категория стоят сразу под заголовком — по ним
 * читатель решает, тратить ли на статью десять минут. Автор вынесен
 * из этой строки отдельным блоком: с портретом он в неё не помещался,
 * да и подпись человека — не то же самое, что выходные данные.
 *
 * @var array $article
 * @var array $author  name, role, photo
 * @var array $crumbs
 */

use App\Core\Text;
use App\Core\View;
?>
<section class="page-hero article-head">
    <?php
    // Та же узкая колонка, что и у текста статьи: заголовок и первый абзац
    // должны начинаться от одной линии, иначе страница выглядит собранной
    // из двух разных.
    ?>
    <div class="container container--narrow">
        <?php $view->partial('partials/breadcrumbs', ['crumbs' => $crumbs]); ?>

        <div class="article-head__inner">
            <?php if ($article['status'] !== 'published'): ?>
                <?php // Такую страницу видит только тот, кто вошёл в админку. ?>
                <p class="article-head__draft">
                    Черновик. На сайте он ещё не опубликован — эту страницу
                    видите только вы.
                </p>
            <?php endif; ?>

            <h1><?= View::e($article['title']) ?></h1>

            <?php if ($article['excerpt'] !== ''): ?>
                <p class="article-head__lead"><?= View::e($article['excerpt']) ?></p>
            <?php endif; ?>

            <?php
            // Категория стоит здесь, а не отдельной строкой над заголовком:
            // она уже есть в хлебных крошках, и второй раз крупно повторять
            // её незачем.
            ?>
            <p class="article-head__meta">
                <time datetime="<?= View::e($article['published_at']) ?>">
                    <?= View::e(Text::date((string) $article['published_at'])) ?>
                </time>
                <?php if ((int) $article['reading_time'] > 0): ?>
                    <span>Время чтения: <?= (int) $article['reading_time'] ?> мин</span>
                <?php endif; ?>
                <a class="article-head__category"
                   href="/stati/kategoriya/<?= View::e($article['category_slug']) ?>">
                    <?= View::e($article['category_name']) ?>
                </a>
            </p>

            <?php
            // Подпись автора. Портрет показывается, только если он задан:
            // у чужого автора его не будет, и блок останется просто
            // именем с должностью.
            ?>
            <?php if (($author['name'] ?? '') !== ''): ?>
                <div class="author">
                    <?php if ($author['photo'] !== ''): ?>
                        <img class="author__photo"
                             src="<?= View::e($author['photo']) ?>"
                             width="56" height="56" loading="lazy"
                             alt="<?= View::e($author['name']) ?>">
                    <?php endif; ?>
                    <span class="author__text">
                        <span class="author__name"><?= View::e($author['name']) ?></span>
                        <?php if ($author['role'] !== ''): ?>
                            <span class="author__role"><?= View::e($author['role']) ?></span>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($article['cover'] !== ''): ?>
            <?php
            // Обложка — единственная картинка на первом экране, поэтому
            // грузится в первую очередь, а не лениво.
            ?>
            <figure class="article-head__cover">
                <img src="<?= View::e($article['cover']) ?>"
                     alt="<?= View::e($article['cover_alt']) ?>"
                     <?php if ((int) $article['cover_width'] > 0): ?>
                     width="<?= (int) $article['cover_width'] ?>"
                     height="<?= (int) $article['cover_height'] ?>"
                     <?php endif; ?>
                     fetchpriority="high" decoding="async">
            </figure>
        <?php endif; ?>
    </div>
</section>
