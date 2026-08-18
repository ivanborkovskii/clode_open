<?php
/**
 * Управление темами (тегами).
 *
 * Категории здесь не редактируются намеренно: их всего три и они заданы
 * структурой раздела. Темы, наоборот, добавляются по мере появления новых
 * тем статей.
 *
 * @var array  $tags
 * @var string $flash
 */

use App\Core\Csrf;
use App\Core\View;
?>
<div class="apage apage--narrow">
    <div class="ahead">
        <h1>Темы</h1>
        <a class="alink" href="/admin">← К списку статей</a>
    </div>

    <?php if ($flash !== ''): ?>
        <p class="aalert"><?= View::e($flash) ?></p>
    <?php endif; ?>

    <?php
    // Формы стоят вне таблицы, а поля связаны с ними атрибутом form.
    // Вложить <form> внутрь <tr> нельзя: браузер вынесет её из таблицы,
    // и строка развалится.
    ?>
    <?php foreach ($tags as $tag): ?>
        <form id="tag-<?= (int) $tag['id'] ?>" method="post" action="/admin/temy">
            <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
            <input type="hidden" name="id" value="<?= (int) $tag['id'] ?>">
        </form>
    <?php endforeach; ?>

    <table class="atable">
        <thead>
            <tr><th>Название</th><th>Адрес</th><th>Порядок</th><th>Статей</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($tags as $tag): ?>
                <?php $form = 'tag-' . (int) $tag['id']; ?>
                <tr>
                    <td>
                        <input type="text" name="name" form="<?= $form ?>"
                               value="<?= View::e($tag['name']) ?>">
                    </td>
                    <td class="amuted"><?= View::e($tag['slug']) ?></td>
                    <td>
                        <input class="anum" type="number" name="position" form="<?= $form ?>"
                               value="<?= (int) $tag['position'] ?>">
                    </td>
                    <td><?= (int) $tag['articles'] ?></td>
                    <td class="anowrap">
                        <button class="alink" type="submit" form="<?= $form ?>"
                                name="do" value="rename">Сохранить</button>
                        <button class="alink alink--danger" type="submit" form="<?= $form ?>"
                                name="do" value="delete"
                                data-confirm="Удалить тему «<?= View::e($tag['name']) ?>»? Статьи останутся, тема с них снимется.">
                            Удалить
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2 class="ah2">Новая тема</h2>

    <form class="aform" method="post" action="/admin/temy">
        <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">

        <label class="afield">
            <span>Название</span>
            <input type="text" name="name" required placeholder="Например: Телефония">
        </label>

        <label class="afield">
            <span>Адрес</span>
            <input type="text" name="slug" placeholder="составится из названия">
            <small>Латиницей. Попадает в адрес отбора: /stati?tegi=telefoniya</small>
        </label>

        <label class="afield">
            <span>Порядок</span>
            <input class="anum" type="number" name="position" value="0">
            <small>Чем меньше число, тем левее тема в списке.</small>
        </label>

        <button class="abtn abtn--primary" type="submit" name="do" value="add">Добавить</button>
    </form>
</div>
