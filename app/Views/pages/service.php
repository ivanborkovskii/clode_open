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

<?php if (!empty($page['stages'])): ?>
    <?php $view->partial('sections/service-stages', ['stages' => $page['stages']]); ?>
<?php endif; ?>

<?php // «Что входит» — перечень без нумерации: у сопровождения работы
      // делаются по необходимости, а не одна за другой. ?>
<?php if (!empty($page['included'])): ?>
    <?php $view->partial('sections/approach', ['approach' => $page['included']]); ?>
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

<?php if (!empty($page['next'])): ?>
    <?php $view->partial('sections/service-next', ['next' => $page['next']]); ?>
<?php endif; ?>

<?php $view->partial('sections/contact', ['form' => $page['form'], 'state' => $form]); ?>
