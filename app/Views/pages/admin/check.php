<?php
/**
 * Проверка настроек хостинга.
 *
 * Каждая строка — результат настоящего действия, а не предположения:
 * файл действительно записывается, комментарий действительно вставляется
 * в базу. Поэтому по этой странице видно не «должно работать»,
 * а работает или нет.
 *
 * @var array $checks
 * @var array $comments
 */

use App\Core\Text;
use App\Core\View;

$failed = array_filter($checks, static fn (array $check): bool => !$check['ok']);
?>
<div class="apage apage--narrow">
    <div class="ahead">
        <h1>Проверка настроек</h1>
        <a class="alink" href="/admin">← К списку статей</a>
    </div>

    <?php if ($failed === []): ?>
        <p class="aalert">Всё в порядке: картинки сохраняются, комментарии записываются.</p>
    <?php else: ?>
        <p class="aalert aalert--error">
            Нашлось <?= count($failed) ?> <?= View::e(
                Text::plural(count($failed), 'препятствие', 'препятствия', 'препятствий')
            ) ?>. Подробности ниже, в строках с пометкой «нет».
        </p>
    <?php endif; ?>

    <table class="atable">
        <thead>
            <tr><th>Что проверяем</th><th>Итог</th></tr>
        </thead>
        <tbody>
            <?php foreach ($checks as $check): ?>
                <tr>
                    <td>
                        <b><?= View::e($check['title']) ?></b>
                        <div class="amuted asmall"><?= View::e($check['hint']) ?></div>
                    </td>
                    <td>
                        <span class="atag <?= $check['ok'] ? 'atag--ok' : 'atag--warn' ?>">
                            <?= $check['ok'] ? 'да' : 'нет' ?>
                        </span>
                        <div class="asmall abreak"><?= View::e((string) $check['value']) ?></div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2 class="ah2">Последние комментарии в базе</h2>

    <?php if ($comments === []): ?>
        <p class="amuted">
            Ни одного комментария в базе нет. Если вы оставляли комментарий
            на сайте и его здесь не видно — значит, он не дошёл до базы,
            и причина в строках выше.
        </p>
    <?php else: ?>
        <p class="amuted asmall">
            Комментарий со стороны сайта появляется здесь сразу, но на странице
            статьи — только после того, как вы его опубликуете
            в разделе «Комментарии».
        </p>

        <ul class="acomments">
            <?php foreach ($comments as $comment): ?>
                <li class="acomment">
                    <div class="acomment__head">
                        <b><?= View::e($comment['name']) ?></b>
                        <span class="amuted asmall">
                            <?= View::e(Text::date((string) $comment['created_at'])) ?>
                        </span>
                        <span class="atag<?= $comment['status'] === 'approved' ? ' atag--ok' : '' ?>">
                            <?= View::e(match ($comment['status']) {
                                'approved' => 'опубликован',
                                'rejected' => 'отклонён',
                                default    => 'ждёт проверки',
                            }) ?>
                        </span>
                    </div>
                    <p class="acomment__body"><?= View::e($comment['body']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
