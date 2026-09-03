<?php
/**
 * Страница тарифов.
 *
 * Порядок: карточки → полное сравнение → чем лицензия отличается
 * от работ → заявка. Карточки первыми, потому что по ним человек
 * выбирает; таблица ниже — для того, кто проверяет состав построчно.
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

<?php $view->partial('sections/tarif-plans', [
    'plans' => $page['plans'],
    'note'  => $page['note'],
]); ?>

<?php $view->partial('sections/tarif-compare', [
    'compare' => $page['compare'],
    'plans'   => $page['plans']['items'],
]); ?>

<?php $view->partial('sections/tarif-separate', ['separate' => $page['separate']]); ?>

<?php $view->partial('sections/contact', ['form' => $page['form'], 'state' => $form]); ?>
