<?php
/**
 * Страница статьи.
 *
 * Порядок блоков: текст → похожие статьи → оценка → комментарии → заявка.
 * Заявка стоит последней намеренно: сначала человек получает ответ
 * на свой вопрос, и только потом ему предлагают разобрать его задачу.
 *
 * Похожие статьи идут сразу за текстом: дочитавшему предлагается,
 * что почитать дальше, пока тема ещё в голове. Оценка и комментарии
 * после них — это разговор о самой статье, он никуда не торопит.
 *
 * @var App\Core\View $view
 * @var array $article
 * @var array $rating
 * @var array $comments
 * @var array|null $replyTo
 * @var array $related
 * @var array $texts
 * @var array $seo
 * @var array $author Имя, должность и портрет автора
 * @var array $state  Результат отправки комментария
 * @var array $form   Результат отправки заявки
 */
?>
<?php $view->partial('sections/article-head', [
    'article' => $article,
    'author'  => $author,
    'crumbs'  => $seo['breadcrumbs'],
]); ?>

<?php $view->partial('sections/article-body', ['article' => $article, 'texts' => $texts]); ?>

<?php $view->partial('sections/article-related', ['related' => $related, 'texts' => $texts]); ?>

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

<?php // Тексты формы приходят готовыми: в них уже названа тема статьи. ?>
<?php $view->partial('sections/contact', ['form' => $leadForm, 'state' => $form]); ?>
