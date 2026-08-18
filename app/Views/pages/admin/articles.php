<?php
/**
 * Список статей в админке: черновики видны наравне с опубликованными.
 *
 * @var array  $articles
 * @var string $query
 * @var string $flash
 */

use App\Core\Csrf;
use App\Core\Text;
use App\Core\View;
?>
<div class="apage">
    <div class="ahead">
        <h1>Статьи</h1>
        <a class="abtn abtn--primary" href="/admin/statya">Новая статья</a>
    </div>

    <?php if ($flash !== ''): ?>
        <p class="aalert"><?= View::e($flash) ?></p>
    <?php endif; ?>

    <form class="asearch-row" method="get" action="/admin">
        <input type="search" name="q" value="<?= View::e($query) ?>"
               placeholder="Поиск по заголовку и тексту">
        <button class="abtn" type="submit">Найти</button>
        <?php if ($query !== ''): ?>
            <a class="abtn abtn--ghost" href="/admin">Сбросить</a>
        <?php endif; ?>
    </form>

    <?php if ($articles === []): ?>
        <p class="amuted">
            <?= $query === ''
                ? 'Статей пока нет. Первая создаётся кнопкой «Новая статья».'
                : 'По этому запросу ничего не нашлось.' ?>
        </p>
    <?php else: ?>
        <table class="atable">
            <thead>
                <tr>
                    <th>Заголовок</th>
                    <th>Категория</th>
                    <th>Дата</th>
                    <th>Состояние</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <td>
                            <a class="alink" href="/admin/statya?id=<?= (int) $article['id'] ?>">
                                <?= View::e($article['title']) ?>
                            </a>
                            <div class="amuted asmall">/stati/<?= View::e($article['slug']) ?></div>
                            <?php if ((int) $article['new_comments'] > 0): ?>
                                <a class="atag atag--warn"
                                   href="/admin/kommentarii">
                                    комментариев на проверку:
                                    <?= (int) $article['new_comments'] ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td><?= View::e($article['category_name']) ?></td>
                        <td class="anowrap"><?= View::e(Text::date((string) $article['published_at'])) ?></td>
                        <td>
                            <?php if ($article['status'] === 'published'): ?>
                                <span class="atag atag--ok">опубликована</span>
                            <?php else: ?>
                                <span class="atag">черновик</span>
                            <?php endif; ?>
                        </td>
                        <td class="anowrap">
                            <?php if ($article['status'] === 'published'): ?>
                                <a class="alink" href="/stati/<?= View::e($article['slug']) ?>"
                                   target="_blank" rel="noopener">Открыть</a>
                            <?php endif; ?>

                            <?php
                            // Удаление — отдельная форма с подтверждением:
                            // вместе со статьёй уходят её комментарии и оценки.
                            ?>
                            <form class="ainline" method="post" action="/admin/udalit"
                                  data-confirm="Удалить статью «<?= View::e($article['title']) ?>»? Вместе с ней удалятся её комментарии и оценки.">
                                <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $article['id'] ?>">
                                <button class="alink alink--danger" type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
