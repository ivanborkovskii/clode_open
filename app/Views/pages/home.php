<?php
/**
 * Главная страница.
 *
 * Порядок блоков = порядок аргументов: сначала суть и доверие,
 * затем узнавание проблемы, затем услуги и результат, доказательства,
 * и только потом заявка. Призыв к действию встречается трижды:
 * в шапке, в середине страницы и в форме внизу.
 *
 * @var App\Core\View $view
 * @var array $page Тексты из config/content/home.php
 * @var array $form Результат отправки формы
 */
?>
<?php $view->partial('sections/hero',         ['hero' => $page['hero']]); ?>
<?php $view->partial('sections/facts',        ['facts' => $page['facts']]); ?>
<?php $view->partial('sections/clients',      ['clients' => $page['clients']]); ?>
<?php $view->partial('sections/problem',      ['problem' => $page['problem']]); ?>
<?php $view->partial('sections/funnel',       ['funnel' => $page['funnel']]); ?>
<?php $view->partial('sections/services',     ['services' => $page['services']]); ?>
<?php $view->partial('sections/solutions',    ['solutions' => $page['solutions']]); ?>
<?php $view->partial('sections/cta-band',     ['band' => $page['cta_band']]); ?>
<?php $view->partial('sections/integrations', ['integrations' => $page['integrations']]); ?>
<?php $view->partial('sections/process',      ['process' => $page['process']]); ?>
<?php $view->partial('sections/cases',        ['cases' => $page['cases']]); ?>
<?php $view->partial('sections/expert',       ['expert' => $page['expert']]); ?>
<?php $view->partial('sections/articles',     ['articles' => $page['articles'], 'items' => $articles]); ?>
<?php $view->partial('sections/contact',      ['form' => $page['form'], 'state' => $form]); ?>
