<?php
/**
 * Шапка внутренней страницы: крошки, заголовок, лид.
 *
 * Намеренно короткая, в отличие от первого экрана главной. На внутреннюю
 * страницу приходят с готовым вопросом, и большой экран-заставка только
 * отодвигает ответ вниз.
 *
 * @var array $hero   title, lead, text
 * @var array $crumbs Хлебные крошки
 */

use App\Core\View;
?>
<section class="page-hero">
    <div class="container">
        <?php $view->partial('partials/breadcrumbs', ['crumbs' => $crumbs]); ?>

        <div class="page-hero__inner">
            <div>
                <h1><?= View::e($hero['title']) ?></h1>
                <p class="page-hero__lead"><?= View::e($hero['lead']) ?></p>
            </div>

            <?php if (!empty($hero['text'])): ?>
                <p class="page-hero__text"><?= View::e($hero['text']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
