<?php
/**
 * Страница отдельной услуги.
 *
 * Один шаблон на все пять услуг: отличается только набор текстов.
 * Блоки, которых у услуги нет, пропускаются — например, у сопровождения
 * не будет раздела с кейсами.
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

<?php $view->partial('sections/service-stages', ['stages' => $page['stages']]); ?>

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
