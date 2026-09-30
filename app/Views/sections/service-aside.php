<?php
/**
 * Короткая отсылка к смежной услуге.
 *
 * Нужна там, где рядом есть похожая услуга и человек мог прийти не туда:
 * со страницы разбора конкретной системы — на общий аудит, и наоборот.
 * Заголовка нет намеренно: это примечание, а не раздел, и лишний h2
 * сбил бы структуру страницы.
 *
 * Своей вёрстки не имеет — берёт оформление блока «что дальше»: тот же
 * отступ, та же акцентная полоса слева.
 *
 * @var array $aside text, href, label
 */

use App\Core\View;
?>
<?php if ($view->exists($aside['href'])): ?>
<section class="section section--tight next-step">
    <div class="container">
        <div class="next-step__inner">
            <p><?= View::e($aside['text']) ?></p>

            <a class="link-arrow" href="<?= View::e($aside['href']) ?>">
                <?= View::e($aside['label']) ?>
                <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
