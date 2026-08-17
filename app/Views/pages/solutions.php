<?php
/**
 * Страница раздела «Решения».
 *
 * Порядок: что за раздел → четыре задачи бизнеса с перечнем возможностей →
 * готовые модули → ссылка на полный перечень интеграций → заявка.
 *
 * @var App\Core\View $view
 * @var array $page Тексты из config/content/solutions.php
 * @var array $seo
 * @var array $form
 */
?>
<?php $view->partial('sections/page-hero', [
    'hero'   => $page['hero'],
    'crumbs' => $seo['breadcrumbs'],
]); ?>

<?php // Та же раскладка строками, что и в перечне услуг: задач всего четыре,
      // и по каждой нужно понять, про вас она или нет. ?>
<?php $view->partial('sections/service-rows', [
    'items'     => $page['items'],
    'linkLabel' => 'Подробнее о решении',
]); ?>

<?php $view->partial('sections/service-connect', ['connect' => $page['ready']]); ?>
<?php $view->partial('sections/service-next',    ['next' => $page['next']]); ?>
<?php $view->partial('sections/contact',         ['form' => $page['form'], 'state' => $form]); ?>
