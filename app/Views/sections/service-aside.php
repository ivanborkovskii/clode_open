<?php
/**
 * Короткий блок в одну мысль: примечание или ключевое утверждение услуги.
 *
 * Используется в двух случаях.
 *
 * Без заголовка — отсылка к смежной услуге. Нужна там, где рядом есть
 * похожая и человек мог прийти не туда: со страницы разбора конкретной
 * системы на общий аудит и наоборот. Заголовка нет намеренно: это
 * примечание, а не раздел, и лишний h2 сбил бы структуру страницы.
 *
 * С заголовком — главная мысль услуги, которую нельзя растворять
 * в перечне карточек. Например, что разбор начинается с бизнес-процесса,
 * а не с настроек.
 *
 * Ссылка необязательна. Если она есть, но та страница ещё не сделана,
 * блок всё равно выводится — пропадает только ссылка.
 *
 * Своей вёрстки не имеет — берёт оформление блока «что дальше»: тот же
 * отступ, та же акцентная полоса слева.
 *
 * @var array $aside title, text, href, label
 */

use App\Core\View;

$hasLink = !empty($aside['href']) && $view->exists($aside['href']);
?>
<?php if ($hasLink || !empty($aside['title'])): ?>
<section class="section section--tight next-step">
    <div class="container">
        <div class="next-step__inner">
            <?php if (!empty($aside['title'])): ?>
                <h2><?= View::e($aside['title']) ?></h2>
            <?php endif; ?>

            <p><?= View::e($aside['text']) ?></p>

            <?php if ($hasLink): ?>
                <a class="link-arrow" href="<?= View::e($aside['href']) ?>">
                    <?= View::e($aside['label']) ?>
                    <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>
