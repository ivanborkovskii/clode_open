<?php
/**
 * Разбор одного кейса.
 *
 * Порядок: что за компания → задача и исходные данные → что делали →
 * что изменилось → как это выглядит → к какой услуге относится → заявка.
 *
 * @var App\Core\View $view
 * @var array $page
 * @var array $seo
 * @var array $form
 * @var string $slug
 */
?>
<?php $view->partial('sections/page-hero', [
    'hero'   => $page['hero'],
    'crumbs' => $seo['breadcrumbs'],
]); ?>

<?php $view->partial('sections/case-facts',  ['facts' => $page['facts']]); ?>
<?php $view->partial('sections/service-stages', ['stages' => $page['stages']]); ?>

<?php // Таблица «что настроили → что это дало». Нужна там, где направлений
      // работ несколько и списком связь между ними теряется. У кейсов
      // попроще её нет — тогда блок просто не выводится. ?>
<?php if (!empty($page['table'])): ?>
    <?php $view->partial('sections/service-table', ['table' => $page['table']]); ?>
<?php endif; ?>

<?php $view->partial('sections/case-result', ['result' => $page['result']]); ?>

<?php if (!empty($page['gallery']['shots'])): ?>
    <?php $view->partial('sections/case-gallery', ['gallery' => $page['gallery'], 'slug' => $slug]); ?>
<?php endif; ?>

<?php $view->partial('sections/service-next', ['next' => $page['next']]); ?>
<?php $view->partial('sections/contact',      ['form' => $page['form'], 'state' => $form]); ?>
