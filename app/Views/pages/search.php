<?php
/**
 * Страница поиска по сайту: шапка, строка поиска и выдача.
 *
 * Без JavaScript работает так же, только без подсказок при наборе:
 * строка поиска — обычная форма, выдача — обычный список ссылок.
 *
 * @var App\Core\View $view
 * @var array  $page   тексты раздела
 * @var array  $items  найденные страницы
 * @var array  $words  слова запроса — по ним выделяются совпадения
 * @var array  $seo
 * @var string $query  что искали
 * @var string $lead   строка под заголовком
 * @var string $host   адрес сайта без «https://»
 */

use App\Core\Text;
use App\Core\View;

$search = $page['search'];

// Когда не нашлось ничего, вместо выдачи показываем разделы: человек
// пришёл с вопросом, и ему нужно, куда идти дальше.
$empty = $query !== '' && $items === [];
?>
<?php $view->partial('sections/page-hero', [
    'hero'   => ['title' => $page['hero']['title'], 'lead' => $lead],
    'crumbs' => $seo['breadcrumbs'],
]); ?>

<section class="section section--tight">
    <div class="container">
        <?php // Текст «ничего не нашлось» отдаём скрипту: слова хранятся
              // в одном месте — в текстах раздела. ?>
        <form class="asearch" role="search" method="get" action="/poisk"
              data-search data-search-url="/api/poisk"
              data-empty="<?= View::e($search['empty']) ?>">
            <label class="asearch__label" for="site-search">
                <?= View::e($search['label']) ?>
            </label>

            <?php // Почему type="text" с role="combobox", а не type="search":
                  // подробности в комментарии к такой же строке поиска
                  // в разделе «Статьи» (sections/article-filters.php). ?>
            <div class="asearch__row">
                <input class="asearch__input" type="text" id="site-search" name="q"
                       value="<?= View::e($query) ?>"
                       placeholder="<?= View::e($search['placeholder']) ?>"
                       autocomplete="off" data-search-input enterkeyhint="search"
                       role="combobox" aria-autocomplete="list"
                       aria-controls="site-suggest" aria-expanded="false">

                <button class="btn btn--primary asearch__submit" type="submit">
                    <?= View::e($search['submit']) ?>
                </button>
            </div>

            <p class="asearch__hint"><?= View::e($search['hint']) ?></p>

            <div class="asearch__drop" id="site-suggest" role="listbox"
                 data-search-drop hidden></div>
        </form>

        <?php if ($empty): ?>
            <p class="sres__note"><?= View::e($page['none']['note']) ?></p>

            <div class="sres">
                <?php foreach ($page['none']['links'] as $link): ?>
                    <article class="sres__item">
                        <span class="sres__kind"><?= View::e($link['section']) ?></span>
                        <h2 class="sres__title">
                            <a href="<?= View::e($link['href']) ?>"><?= View::e($link['title']) ?></a>
                        </h2>
                        <p class="sres__text"><?= View::e($link['text']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php elseif ($items !== []): ?>
            <div class="sres">
                <?php foreach ($items as $item): ?>
                    <article class="sres__item">
                        <span class="sres__kind"><?= View::e($item['section']) ?></span>
                        <h2 class="sres__title">
                            <a href="<?= View::e($item['url']) ?>"><?= View::e($item['title']) ?></a>
                        </h2>
                        <?php // Text::mark сам экранирует: кроме <b> вокруг
                              // совпадений, других тегов в строке нет. ?>
                        <p class="sres__text"><?= Text::mark($item['text'], $words) ?></p>
                        <p class="sres__url"><?= View::e($host . $item['url']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
