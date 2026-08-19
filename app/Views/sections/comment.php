<?php
/**
 * Одна запись разговора вместе со всеми ответами на неё.
 *
 * Шаблон вызывает сам себя для вложенных ответов — так ветка получается
 * любой глубины. Отступ при этом растёт только до третьего уровня:
 * дальше лесенка не помещалась бы на телефоне, а понимание, кто кому
 * отвечает, даёт подпись «в ответ», а не отступ.
 *
 * @var array $comment
 * @var int   $level   Глубина: 0 — начало разговора
 * @var array $labels
 */

use App\Core\Text;
use App\Core\View;

$level    = $level ?? 0;
$isAuthor = (int) ($comment['is_author'] ?? 0) === 1;
$answers  = (string) ($comment['parent_name'] ?? '');

// Глубже третьего уровня отступ не растёт.
$indent = min($level, 3);
?>
<li class="comment-item">
    <article class="comment<?= $isAuthor ? ' comment--author' : '' ?>"
             id="kommentariy-<?= (int) $comment['id'] ?>">
        <p class="comment__head">
            <b class="comment__name"><?= View::e($comment['name']) ?></b>

            <?php if ($isAuthor): ?>
                <span class="comment__badge"><?= View::e($labels['author']) ?></span>
            <?php endif; ?>

            <?php if ($answers !== ''): ?>
                <?php
                // Кому написан ответ. Ссылка ведёт к самой реплике —
                // в длинной ветке иначе пришлось бы искать её глазами.
                ?>
                <span class="comment__to">
                    <?= View::e($labels['in_reply']) ?>
                    <a href="#kommentariy-<?= (int) $comment['parent_id'] ?>"><?= View::e($answers) ?></a>
                </span>
            <?php endif; ?>

            <time class="comment__date"
                  datetime="<?= View::e(substr((string) $comment['created_at'], 0, 10)) ?>">
                <?= View::e(Text::date((string) $comment['created_at'])) ?>
            </time>
        </p>

        <?php
        // Текст комментария выводится экранированным и с переносами строк:
        // разметку посетителей на страницу не пускаем.
        ?>
        <p class="comment__body"><?= nl2br(View::e($comment['body'])) ?></p>

        <?php
        // Без JavaScript ссылка перезагружает страницу с пометкой, кому
        // пишут ответ. Со скриптом форма просто переезжает сюда.
        ?>
        <a class="comment__reply" href="?otvet=<?= (int) $comment['id'] ?>#comment-form"
           data-reply="<?= (int) $comment['id'] ?>"
           data-reply-name="<?= View::e($comment['name']) ?>">
            <?= View::e($labels['reply']) ?>
        </a>
    </article>

    <?php if ($comment['replies'] !== []): ?>
        <ol class="comments comments--replies comments--l<?= $indent ?>">
            <?php foreach ($comment['replies'] as $reply): ?>
                <?php $view->partial('sections/comment', [
                    'comment' => $reply,
                    'level'   => $level + 1,
                    'labels'  => $labels,
                ]); ?>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</li>
