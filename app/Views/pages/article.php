<?php
/**
 * Страница статьи.
 *
 * Порядок блоков: текст → оценка → комментарии → похожие статьи → заявка.
 * Заявка стоит последней намеренно: сначала человек получает ответ
 * на свой вопрос, и только потом ему предлагают разобрать его задачу.
 *
 * @var App\Core\View $view
 * @var array $article
 * @var array $rating
 * @var array $comments
 * @var array|null $replyTo
 * @var array $related
 * @var array $texts
 * @var array $seo
 * @var array $state  Результат отправки комментария
 * @var array $form   Результат отправки заявки
 */
?>
<?php $view->partial('sections/article-head', [
    'article' => $article,
    'crumbs'  => $seo['breadcrumbs'],
]); ?>

<?php $view->partial('sections/article-body', ['article' => $article, 'texts' => $texts]); ?>

<?php $view->partial('sections/article-rating', [
    'article' => $article,
    'rating'  => $rating,
    'texts'   => $texts,
]); ?>

<?php $view->partial('sections/article-comments', [
    'article'  => $article,
    'comments' => $comments,
    'replyTo'  => $replyTo,
    'texts'    => $texts,
    'state'    => $state,
]); ?>

<?php $view->partial('sections/article-related', ['related' => $related, 'texts' => $texts]); ?>

<?php // Тексты формы приходят готовыми: в них уже названа тема статьи. ?>
<?php $view->partial('sections/contact', ['form' => $leadForm, 'state' => $form]); ?>
