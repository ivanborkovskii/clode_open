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

<?php if (!empty($page['next'])): ?>
    <?php $view->partial('sections/service-next', ['next' => $page['next']]); ?>
<?php endif; ?>

<?php // Вопросы и ответы — там, где они у услуги заведены. Стоят перед
      // формой: человек дочитывает возражения и сразу видит, где оставить
      // заявку. ?>
<?php if (!empty($page['faq']['items'])): ?>
    <?php $view->partial('sections/service-faq', ['faq' => $page['faq']]); ?>
<?php endif; ?>

<?php $view->partial('sections/contact', ['form' => $page['form'], 'state' => $form]); ?>
