<?php
/**
 * Одна запись разговора: комментарий или ответ на него.
 *
 * Ответы автора помечены явно — читателю важно видеть, где отвечает
 * владелец сайта, а где другой посетитель.
 *
 * @var array $comment
 * @var bool  $isReply Это ответ внутри ветки
 * @var array $labels
 */

use App\Core\Text;
use App\Core\View;

$isAuthor = (int) ($comment['is_author'] ?? 0) === 1;
?>
<li class="comment<?= $isReply ? ' comment--reply' : '' ?><?= $isAuthor ? ' comment--author' : '' ?>"
    id="kommentariy-<?= (int) $comment['id'] ?>">
    <p class="comment__head">
        <b class="comment__name"><?= View::e($comment['name']) ?></b>

        <?php if ($isAuthor): ?>
            <span class="comment__badge"><?= View::e($labels['author']) ?></span>
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
</li>
