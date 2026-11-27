<?php
/**
 * Страница тарифов.
 *
 * Порядок: карточки → ступени Энтерпрайза → полное сравнение → чем
 * лицензия отличается от работ → заявка. Карточки первыми, потому что
 * по ним человек выбирает; таблицы ниже — для того, кто проверяет
 * цены и состав построчно.
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

<?php $view->partial('partials/tarif-tabs', ['tabs' => $tabs]); ?>

<?php $view->partial('sections/tarif-plans', [
    'plans' => $page['plans'],
    'note'  => $page['note'],
]); ?>

<?php // Ступени Энтерпрайза. У остальных тарифов цена одна и стоит
      // в карточке, а здесь их двенадцать — таблицей, текстом, без
      // зависимости от скрипта. ?>
<?php if (!empty($page['enterprise'])): ?>
    <?php $view->partial('sections/service-table', ['table' => $page['enterprise']]); ?>
<?php endif; ?>

<?php $view->partial('sections/tarif-compare', [
    'compare' => $page['compare'],
    'plans'   => $page['plans']['items'],
]); ?>

<?php $view->partial('sections/tarif-separate', ['separate' => $page['separate']]); ?>

<?php $view->partial('sections/contact', ['form' => $page['form'], 'state' => $form]); ?>
