<?php
/**
 * Поиск и отбор статей: строка поиска, категории, темы.
 *
 * Фильтры — обычные ссылки, а поиск — обычная форма: без JavaScript
 * раздел работает так же, только без подсказок при наборе.
 *
 * @var array $page   search, categories, tags, reset, texts
 */

use App\Core\View;

$texts   = $page['texts'];
$search  = $page['search'];
$filters = $texts['filters'];

// Выбранные темы переносим в форму поиска скрытым полем: иначе поиск
// внутри отобранных статей сбрасывал бы отбор.
$activeTags = [];

foreach ($page['tags'] as $tag) {
    if ($tag['active']) {
        $activeTags[] = $tag['slug'];
    }
}
?>
<section class="section section--tight article-filters">
    <div class="container">
        <?php // Текст «ничего не нашлось» отдаём скрипту: слова хранятся
              // в одном месте — в текстах раздела. ?>
        <form class="asearch" role="search" method="get"
              action="<?= View::e($search['action']) ?>" data-search
              data-empty="<?= View::e($texts['search']['empty']) ?>">
            <label class="asearch__label" for="article-search">
                <?= View::e($texts['search']['label']) ?>
            </label>

            <div class="asearch__row">
                <?php
                // autocomplete="off" — иначе браузер поверх наших подсказок
                // покажет свой список прошлых запросов.
                ?>
                <input class="asearch__input" type="search" id="article-search" name="q"
                       value="<?= View::e($search['value']) ?>"
                       placeholder="<?= View::e($texts['search']['placeholder']) ?>"
                       autocomplete="off" data-search-input
                       aria-controls="article-suggest" aria-expanded="false">

                <button class="btn btn--primary asearch__submit" type="submit">
                    <?= View::e($texts['search']['submit']) ?>
                </button>
            </div>

            <?php if ($activeTags !== []): ?>
                <input type="hidden" name="tegi" value="<?= View::e(implode(',', $activeTags)) ?>">
            <?php endif; ?>

            <p class="asearch__hint"><?= View::e($texts['search']['hint']) ?></p>

            <?php
            // Список подсказок заполняет скрипт. Пустой и скрытый он не мешает
            // ни чтению с экрана, ни разметке.
            ?>
            <div class="asearch__drop" id="article-suggest" role="listbox"
                 data-search-drop hidden></div>
        </form>

        <div class="afilter">
            <p class="afilter__title" id="filter-categories"><?= View::e($filters['categories']) ?></p>
            <ul class="chips" aria-labelledby="filter-categories">
                <?php foreach ($page['categories'] as $chip): ?>
                    <li>
                        <a class="chip<?= $chip['active'] ? ' chip--on' : '' ?>"
                           href="<?= View::e($chip['href']) ?>"
                           <?= $chip['active'] ? 'aria-current="true"' : '' ?>>
                            <?= View::e($chip['name']) ?>
                            <?php if ($chip['count'] !== null): ?>
                                <span class="chip__count"><?= (int) $chip['count'] ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($page['tags'] !== []): ?>
                <p class="afilter__title" id="filter-tags">
                    <?= View::e($filters['tags']) ?>
                    <span class="afilter__hint"><?= View::e($filters['tags_hint']) ?></span>
                </p>

                <ul class="chips" aria-labelledby="filter-tags">
                    <?php foreach ($page['tags'] as $chip): ?>
                        <li>
                            <a class="chip<?= $chip['active'] ? ' chip--on' : '' ?>"
                               href="<?= View::e($chip['href']) ?>"
                               <?= $chip['active'] ? 'aria-current="true"' : '' ?>>
                                <?= View::e($chip['name']) ?>
                                <span class="chip__count"><?= (int) $chip['count'] ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($page['reset'] !== ''): ?>
                <p class="afilter__reset">
                    <a class="link-arrow" href="<?= View::e($page['reset']) ?>">
                        <?= View::e($filters['reset']) ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>
