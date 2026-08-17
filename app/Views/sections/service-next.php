<?php
/**
 * Что происходит после запуска — переход к смежной услуге.
 * Ссылка выводится, только когда та страница уже разработана.
 *
 * @var array $next title, text, href, label
 */

use App\Core\View;
?>
<section class="section section--tight next-step">
    <div class="container">
        <div class="next-step__inner">
            <h2><?= View::e($next['title']) ?></h2>
            <p><?= View::e($next['text']) ?></p>

            <?php if ($view->exists($next['href'])): ?>
                <a class="link-arrow" href="<?= View::e($next['href']) ?>">
                    <?= View::e($next['label']) ?>
                    <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
