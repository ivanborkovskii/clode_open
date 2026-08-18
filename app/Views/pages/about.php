<?php
/**
 * Страница «О компании».
 *
 * Блоки те же, что и на главной, но развёрнуто: цифры опыта, кто ведёт
 * проекты, как строится работа. Данные общие — из config/content/company.php.
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

<?php $view->partial('sections/facts',    ['facts' => $page['facts']]); ?>
<?php $view->partial('sections/expert',   ['expert' => $page['expert']]); ?>
<?php $view->partial('sections/process',  ['process' => $page['process']]); ?>
<?php $view->partial('sections/approach', ['approach' => $page['tech']]); ?>
<?php $view->partial('sections/contact',  ['form' => $page['form'], 'state' => $form]); ?>
