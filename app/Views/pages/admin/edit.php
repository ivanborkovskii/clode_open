<?php
/**
 * Редактор статьи.
 *
 * Текст пишется разметкой: кнопки над полем вставляют теги вокруг
 * выделенного фрагмента. Это проще визуального редактора и не приносит
 * в статьи чужое оформление, которое обычно тянется вместе со вставкой
 * из Word.
 *
 * @var array|null $article
 * @var array $categories
 * @var array $tags
 * @var array $errors
 * @var array $values  Введённое в прошлый раз, если сохранение не прошло
 * @var array $topicWords Слова, по которым тема заявки составляется сама
 */

use App\Core\Csrf;
use App\Core\Text;
use App\Core\View;

$isNew = $article === null;

/** Значение поля: сначала из неудавшейся отправки, потом из базы. */
$val = static function (string $field, string $default = '') use ($values, $article): string {
    if (array_key_exists($field, $values)) {
        return (string) $values[$field];
    }

    return (string) ($article[$field] ?? $default);
};

$selectedTags = array_key_exists('tags', $values)
    ? array_map('strval', (array) $values['tags'])
    : array_column($article['tags'] ?? [], 'slug');

$flash = $_SESSION['admin_flash'] ?? '';
unset($_SESSION['admin_flash']);
?>
<div class="apage">
    <div class="ahead">
        <h1><?= $isNew ? 'Новая статья' : 'Редактирование' ?></h1>
        <a class="alink" href="/admin">← К списку</a>
    </div>

    <?php if ($flash !== ''): ?>
        <p class="aalert"><?= View::e((string) $flash) ?></p>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <p class="aalert aalert--error">Статья не сохранена: исправьте отмеченные поля.</p>
    <?php endif; ?>

    <form class="aform aform--wide" method="post" action="/admin/statya" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
        <?php if (!$isNew): ?>
            <input type="hidden" name="id" value="<?= (int) $article['id'] ?>">
        <?php endif; ?>

        <div class="agrid">
            <div class="acol">
                <label class="afield">
                    <span>Заголовок</span>
                    <input type="text" name="title" value="<?= View::e($val('title')) ?>"
                           data-title required>
                    <?php if (isset($errors['title'])): ?>
                        <em class="aerror"><?= View::e($errors['title']) ?></em>
                    <?php endif; ?>
                </label>

                <label class="afield">
                    <span>Адрес страницы</span>
                    <span class="aprefix">/stati/<input type="text" name="slug"
                        value="<?= View::e($val('slug')) ?>" data-slug
                        placeholder="составится из заголовка"></span>
                    <small>Латиницей. После публикации адрес лучше не менять:
                        по старому люди и поисковики попадут на «страница не найдена».</small>
                    <?php if (isset($errors['slug'])): ?>
                        <em class="aerror"><?= View::e($errors['slug']) ?></em>
                    <?php endif; ?>
                </label>

                <label class="afield">
                    <span>Анонс</span>
                    <textarea name="excerpt" rows="3"
                              placeholder="Одно-два предложения для списка статей"><?= View::e($val('excerpt')) ?></textarea>
                    <small>Показывается в списке и в подсказках поиска.
                        Если оставить пустым, возьмётся начало текста.</small>
                </label>

                <div class="afield">
                    <span>Текст статьи</span>

                    <?php // Кнопки вставляют теги вокруг выделенного текста. ?>
                    <div class="atoolbar" data-toolbar>
                        <button type="button" data-wrap="h2">Заголовок</button>
                        <button type="button" data-wrap="h3">Подзаголовок</button>
                        <button type="button" data-wrap="p">Абзац</button>
                        <button type="button" data-wrap="strong">Жирный</button>
                        <button type="button" data-wrap="em">Курсив</button>
                        <button type="button" data-list="ul">Список</button>
                        <button type="button" data-list="ol">Нумерованный</button>
                        <button type="button" data-link>Ссылка</button>
                        <button type="button" data-wrap="blockquote">Цитата</button>
                        <button type="button" data-image>Картинка</button>
                    </div>

                    <textarea class="abody" name="body" rows="24" data-body required><?= View::e($val('body')) ?></textarea>
                    <small>Разрешённые теги: p, h2–h4, strong, em, ul, ol, li,
                        a, img, figure, blockquote, table, code, pre, hr.
                        Остальные будут убраны при выводе на сайт.</small>
                    <?php if (isset($errors['body'])): ?>
                        <em class="aerror"><?= View::e($errors['body']) ?></em>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="acol acol--side">
                <div class="abox">
                    <p class="abox__title">Публикация</p>

                    <label class="afield">
                        <span>Состояние</span>
                        <select name="status">
                            <option value="draft" <?= $val('status') !== 'published' ? 'selected' : '' ?>>
                                Черновик
                            </option>
                            <option value="published" <?= $val('status') === 'published' ? 'selected' : '' ?>>
                                Опубликована
                            </option>
                        </select>
                        <small>Черновик виден на сайте только вам, пока вы вошли сюда.</small>
                    </label>

                    <label class="afield">
                        <span>Дата публикации</span>
                        <input type="date" name="published_at"
                               value="<?= View::e($val('published_at', date('Y-m-d'))) ?>">
                    </label>

                    <?php if (!$isNew): ?>
                        <p class="asmall amuted">
                            <a class="alink" href="/stati/<?= View::e((string) $article['slug']) ?>"
                               target="_blank" rel="noopener">Посмотреть на сайте</a>
                        </p>
                    <?php endif; ?>

                    <button class="abtn abtn--primary abtn--block" type="submit">Сохранить</button>
                </div>

                <div class="abox">
                    <p class="abox__title">Категория</p>

                    <?php if (isset($errors['category_id'])): ?>
                        <em class="aerror"><?= View::e($errors['category_id']) ?></em>
                    <?php endif; ?>

                    <?php
                    // Категория одна: переключатели, а не список с галочками, —
                    // выбрать две технически невозможно.
                    ?>
                    <?php
                    // У новой статьи отмечена первая категория: сохранить
                    // статью совсем без категории нельзя, и пустой набор
                    // переключателей приводил бы к ошибке при сохранении.
                    $currentCategory = (int) $val('category_id');
                    ?>
                    <?php foreach ($categories as $index => $category): ?>
                        <label class="acheck">
                            <input type="radio" name="category_id" value="<?= (int) $category['id'] ?>"
                                   required
                                <?= $currentCategory === (int) $category['id']
                                    || ($currentCategory === 0 && $index === 0)
                                    ? 'checked' : '' ?>>
                            <span><?= View::e($category['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="abox">
                    <p class="abox__title">Темы</p>
                    <p class="asmall amuted">Сколько угодно. Они сужают категорию
                        при отборе и по ним подбираются похожие статьи.</p>

                    <?php foreach ($tags as $tag): ?>
                        <label class="acheck">
                            <input type="checkbox" name="tags[]" value="<?= View::e($tag['slug']) ?>"
                                <?= in_array($tag['slug'], $selectedTags, true) ? 'checked' : '' ?>>
                            <span><?= View::e($tag['name']) ?></span>
                        </label>
                    <?php endforeach; ?>

                    <p class="asmall"><a class="alink" href="/admin/temy">Управление темами</a></p>
                </div>

                <div class="abox">
                    <p class="abox__title">Обложка</p>

                    <?php if ($val('cover') !== ''): ?>
                        <img class="acover" src="<?= View::e($val('cover')) ?>" alt="">
                    <?php endif; ?>

                    <input type="hidden" name="cover" value="<?= View::e($val('cover')) ?>">
                    <input type="hidden" name="cover_width" value="<?= View::e($val('cover_width', '0')) ?>">
                    <input type="hidden" name="cover_height" value="<?= View::e($val('cover_height', '0')) ?>">

                    <label class="afield">
                        <span>Загрузить картинку</span>
                        <input type="file" name="cover_file" accept="image/*">
                        <small>Сохранится в WebP шириной до 1600 точек.</small>
                        <?php if (isset($errors['cover'])): ?>
                            <em class="aerror"><?= View::e($errors['cover']) ?></em>
                        <?php endif; ?>
                    </label>

                    <label class="afield">
                        <span>Описание картинки</span>
                        <input type="text" name="cover_alt" value="<?= View::e($val('cover_alt')) ?>"
                               placeholder="Что на картинке">
                        <small>Читают поисковики и программы чтения с экрана.</small>
                    </label>
                </div>

                <div class="abox">
                    <p class="abox__title">Выходные данные</p>

                    <label class="afield">
                        <span>Автор</span>
                        <input type="text" name="author"
                               value="<?= View::e($val('author', 'Иван Борковский, основатель компании')) ?>">
                    </label>

                    <label class="afield">
                        <span>Время чтения, мин</span>
                        <input type="number" name="reading_time" min="0" max="120"
                               value="<?= View::e($val('reading_time', '0')) ?>">
                        <small>Ноль — посчитается по объёму текста.</small>
                    </label>
                </div>

                <div class="abox">
                    <p class="abox__title">Форма заявки под статьёй</p>

                    <?php
                    // Подсказка показывает, что подставится само, если поле
                    // оставить пустым: автору обычно достаточно посмотреть
                    // и согласиться.
                    $suggested = Text::topic($val('title'), $topicWords);
                    ?>
                    <label class="afield">
                        <span>Тема заявки</span>
                        <input type="text" name="form_topic" value="<?= View::e($val('form_topic')) ?>"
                               placeholder="<?= View::e($suggested !== ''
                                   ? $suggested
                                   : 'например: по интеграции Битрикс24 и Телеграм') ?>">
                        <small>
                            Подставится в заголовки: «Разберём вашу задачу …»
                            и «Оставить заявку …». Пишется в том виде, в каком
                            встанет в строку — с предлогом.
                            <?= $suggested !== ''
                                ? 'Оставите пустым — возьмётся «' . View::e($suggested) . '».'
                                : 'Оставите пустым — форма покажет обычный заголовок.' ?>
                        </small>
                    </label>
                </div>

                <div class="abox">
                    <p class="abox__title">Для поисковиков</p>

                    <label class="afield">
                        <span>Заголовок страницы</span>
                        <input type="text" name="meta_title" value="<?= View::e($val('meta_title')) ?>"
                               placeholder="как заголовок статьи">
                    </label>

                    <label class="afield">
                        <span>Описание страницы</span>
                        <textarea name="meta_description" rows="3"
                                  placeholder="как анонс"><?= View::e($val('meta_description')) ?></textarea>
                    </label>
                </div>
            </aside>
        </div>
    </form>
</div>
