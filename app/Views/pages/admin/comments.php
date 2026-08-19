<?php
/**
 * Проверка комментариев.
 *
 * Новый комментарий на сайте не показывается, пока его здесь не одобрят.
 * Отклонённые остаются в базе: по ним видно, что именно приходит,
 * и их всегда можно вернуть.
 *
 * @var array  $comments
 * @var string $status
 * @var int    $new
 * @var string $flash
 */

use App\Core\Csrf;
use App\Core\Text;
use App\Core\View;

$tabs = [
    'new'      => 'На проверке',
    'approved' => 'Опубликованные',
    'rejected' => 'Отклонённые',
    'all'      => 'Все',
];
?>
<div class="apage apage--narrow">
    <div class="ahead">
        <h1>Комментарии</h1>
        <a class="alink" href="/admin">← К списку статей</a>
    </div>

    <?php if ($flash !== ''): ?>
        <p class="aalert"><?= View::e($flash) ?></p>
    <?php endif; ?>

    <nav class="atabs">
        <?php foreach ($tabs as $key => $label): ?>
            <a class="atab<?= $status === $key ? ' atab--on' : '' ?>"
               href="/admin/kommentarii?status=<?= View::e($key) ?>">
                <?= View::e($label) ?>
                <?php if ($key === 'new' && $new > 0): ?>
                    <span class="abar__badge"><?= (int) $new ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if ($comments === []): ?>
        <p class="amuted">Здесь пусто.</p>
    <?php else: ?>
        <ul class="acomments">
            <?php foreach ($comments as $comment): ?>
                <li class="acomment">
                    <div class="acomment__head">
                        <b><?= View::e($comment['name']) ?></b>

                        <?php if ($comment['is_author']): ?>
                            <span class="atag atag--ok">ваш ответ</span>
                        <?php elseif ($comment['parent_name'] !== null): ?>
                            <span class="amuted asmall">
                                в ответ: <?= View::e($comment['parent_name']) ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($comment['email'] !== ''): ?>
                            <a class="alink asmall" href="mailto:<?= View::e($comment['email']) ?>">
                                <?= View::e($comment['email']) ?>
                            </a>
                        <?php endif; ?>
                        <span class="amuted asmall">
                            <?= View::e(Text::date((string) $comment['created_at'])) ?>
                        </span>
                        <span class="atag<?= $comment['status'] === 'approved' ? ' atag--ok' : '' ?>">
                            <?= View::e(match ($comment['status']) {
                                'approved' => 'опубликован',
                                'rejected' => 'отклонён',
                                default    => 'на проверке',
                            }) ?>
                        </span>
                    </div>

                    <p class="acomment__body"><?= nl2br(View::e($comment['body'])) ?></p>

                    <p class="amuted asmall">
                        к статье
                        <a class="alink" href="/stati/<?= View::e($comment['article_slug']) ?>"
                           target="_blank" rel="noopener"><?= View::e($comment['article_title']) ?></a>
                    </p>

                    <form class="ainline" method="post" action="/admin/kommentariy">
                        <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                        <input type="hidden" name="back" value="<?= View::e($status) ?>">

                        <?php if ($comment['status'] !== 'approved'): ?>
                            <button class="abtn abtn--small" type="submit" name="do" value="approved">
                                Опубликовать
                            </button>
                        <?php endif; ?>

                        <?php if ($comment['status'] !== 'rejected'): ?>
                            <button class="abtn abtn--small abtn--ghost" type="submit" name="do" value="rejected">
                                Отклонить
                            </button>
                        <?php endif; ?>

                        <button class="alink alink--danger" type="submit" name="do" value="delete"
                                data-confirm="Удалить комментарий насовсем?">
                            Удалить
                        </button>
                    </form>

                    <?php
                    // Ответ пишется здесь же, отдельной формой: он публикуется
                    // сразу и помечается на сайте как ответ автора. Если
                    // комментарий ещё не опубликован, ответ публикует и его —
                    // отвечать на то, чего на сайте нет, незачем.
                    ?>
                    <?php if (!$comment['is_author']): ?>
                        <form class="areply" method="post" action="/admin/kommentariy">
                            <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                            <input type="hidden" name="back" value="<?= View::e($status) ?>">

                            <label class="afield">
                                <span>
                                    <?= $comment['answered']
                                        ? 'Ответить ещё раз'
                                        : 'Ваш ответ' ?>
                                </span>
                                <textarea name="reply" rows="3"
                                          placeholder="Ответ появится на странице статьи сразу"></textarea>
                            </label>

                            <button class="abtn abtn--small abtn--primary" type="submit"
                                    name="do" value="reply">Отправить ответ</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
