<?php
/**
 * Страница отдельной услуги.
 *
 * Один шаблон на все пять услуг: отличается только набор текстов.
 * Все блоки необязательны и пропускаются, если данных нет: у интеграций
 * работы не идут по этапам, у сопровождения нет отдельного кейса,
 * а цена подтверждена только для сопровождения.
 *
 * Порядок: кому это нужно → что и в каком порядке делается →
 * что подключается → доказательство → что дальше → заявка.
 *
 * @var App\Core\View $view
 * @var array $page
 * @var array $seo
 * @var array $form
 */
?>
<?php $view->partial('sections/page-hero', [
    'hero'   => $page['hero'],
    'crumbs' => $seo['breadcrumbs'],
]); ?>

<?php // Главная мысль услуги, если она есть. Стоит сразу под шапкой:
      // такое утверждение задаёт, как читать всё остальное, и растворять
      // его в перечне карточек нельзя. ?>
<?php if (!empty($page['principle'])): ?>
    <?php $view->partial('sections/service-aside', ['aside' => $page['principle']]); ?>
<?php endif; ?>

<?php if (!empty($page['fit'])): ?>
    <?php $view->partial('sections/service-fit', ['fit' => $page['fit']]); ?>
<?php endif; ?>

<?php // «Что входит» — перечень без нумерации: у сопровождения работы
      // делаются по необходимости, а не одна за другой, а у аудита
      // порядок проверок читателю неважен.
      //
      // Стоит перед этапами намеренно: человек сначала хочет понять,
      // что именно делают, и только потом — в каком порядке. Ни одна
      // страница не выводит оба блока сразу, так что на остальных
      // порядок ничего не меняет. ?>
<?php if (!empty($page['included'])): ?>
    <?php $view->partial('sections/approach', ['approach' => $page['included']]); ?>
<?php endif; ?>

<?php // Где услуга находит потери. Блок со значками, тот же, что «что
      // подключаем» у внедрения: перечень равнозначных пунктов, порядок
      // которых читателю неважен. Стоит до этапов — это содержательная
      // часть, а этапы только объясняют порядок работы. ?>
<?php if (!empty($page['losses'])): ?>
    <?php $view->partial('sections/service-connect', ['connect' => $page['losses']]); ?>
<?php endif; ?>

<?php // Разбор с исходами: что с найденным делать. Карточки, как и всюду. ?>
<?php if (!empty($page['plan'])): ?>
    <?php $view->partial('sections/approach', ['approach' => $page['plan']]); ?>
<?php endif; ?>

<?php
// Дополнительные блоки услуги, в заданном ею порядке.
//
// Именованных мест выше хватает большинству страниц, но у иных услуг
// разделов больше, и заводить под каждый свой ключ в шаблоне — значит
// растить его без конца. Здесь услуга сама перечисляет блоки: чем
// рисовать и что показать.
//
// Новых секций это не изобретает — только переставляет уже имеющиеся.
// Список допустимых задан явно: имя секции приходит из файла с текстами,
// и подставлять его в путь без проверки нельзя.
$blockViews = [
    'approach'        => 'approach',
    'service-connect' => 'connect',
    'service-aside'   => 'aside',
    'service-table'   => 'table',
];
?>
<?php foreach ($page['blocks'] ?? [] as $block): ?>
    <?php if (isset($blockViews[$block['view'] ?? ''])): ?>
        <?php $view->partial('sections/' . $block['view'], [$blockViews[$block['view']] => $block]); ?>
    <?php endif; ?>
<?php endforeach; ?>

<?php if (!empty($page['stages'])): ?>
    <?php $view->partial('sections/service-stages', ['stages' => $page['stages']]); ?>
<?php endif; ?>

<?php if (!empty($page['price'])): ?>
    <?php $view->partial('sections/service-price', ['price' => $page['price']]); ?>
<?php endif; ?>

<?php if (!empty($page['connect'])): ?>
    <?php $view->partial('sections/service-connect', ['connect' => $page['connect']]); ?>
<?php endif; ?>

<?php if (!empty($page['cases']['items'])): ?>
    <?php $view->partial('sections/service-cases', ['cases' => $page['cases']]); ?>
<?php endif; ?>

<?php // Итог услуги — что остаётся у заказчика на руках. Тот же блок,
      // что и «что входит», только с другим содержимым: заводить ради
      // этого отдельную вёрстку незачем. ?>
<?php if (!empty($page['results'])): ?>
    <?php $view->partial('sections/approach', ['approach' => $page['results']]); ?>
<?php endif; ?>

<?php // Разбор, какая из похожих услуг нужна. Карточки со ссылками —
      // тот же блок, что выше, только с переходами. Нужен там, где
      // рядом есть близкие услуги и человек может выбрать не ту. ?>
<?php if (!empty($page['choice'])): ?>
    <?php $view->partial('sections/approach', ['approach' => $page['choice']]); ?>
<?php endif; ?>

<?php if (!empty($page['next'])): ?>
    <?php $view->partial('sections/service-next', ['next' => $page['next']]); ?>
<?php endif; ?>

<?php // Отсылка к смежной услуге, если человек пришёл не на ту страницу. ?>
<?php if (!empty($page['aside'])): ?>
    <?php $view->partial('sections/service-aside', ['aside' => $page['aside']]); ?>
<?php endif; ?>

<?php // Вопросы и ответы — там, где они у услуги заведены. Стоят перед
      // формой: человек дочитывает возражения и сразу видит, где оставить
      // заявку. ?>
<?php if (!empty($page['faq']['items'])): ?>
    <?php $view->partial('sections/service-faq', ['faq' => $page['faq']]); ?>
<?php endif; ?>

<?php $view->partial('sections/contact', ['form' => $page['form'], 'state' => $form]); ?>
