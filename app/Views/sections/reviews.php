<?php
/**
 * Отзывы заказчиков: оценка на Авито и ссылка на профиль.
 *
 * Отзывов на сайте нет и не планируется — блок рассказывает, где они
 * лежат и почему именно там. Поэтому здесь нет ни карточек с цитатами,
 * ни имён: только цифра, её дата и ссылка на первоисточник.
 *
 * Микроразметки с рейтингом (aggregateRating) здесь намеренно нет.
 * Поисковик не показывает звёзды для отзывов о самом себе на своём же
 * сайте, то есть пользы от неё никакой, а утверждала бы она неправду:
 * будто отзывы собраны здесь. Связь сайта с профилем даёт поле sameAs —
 * оно собирается из того же списка в настройках (app/Core/Schema.php).
 *
 * @var App\Core\View $view
 * @var array $config
 * @var array $reviews
 */

use App\Core\Text;
use App\Core\View;

// Адрес профиля лежит в настройках сайта один раз. Здесь его не
// переписываем, а находим по ключу: иначе при смене адреса пришлось бы
// помнить про оба места.
$href = $view->socialUrl($reviews['link']['id']);

$count = (int) $reviews['count'];
$stars = (int) $reviews['stars'];

// Звёзды — картинка к цифре, которая уже написана рядом словами.
// Для диктора они пустые, иначе оценка читалась бы дважды.
$score = $reviews['value'] . ' из 5, ' . $count . ' '
    . Text::plural($count, 'отзыв', 'отзыва', 'отзывов');
?>
<section class="section section--tight reviews" id="otzyvy">
    <div class="container">
        <div class="section-head">
            <p class="label"><?= View::e($reviews['label']) ?></p>
            <h2><?= View::e($reviews['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($reviews['lead']) ?></p>
        </div>

        <div class="reviews__card">
            <div class="reviews__score">
                <p class="reviews__value"><?= View::e($reviews['value']) ?></p>

                <div class="reviews__stars" aria-hidden="true">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="reviews__star<?= $i <= $stars ? ' is-on' : '' ?>">
                            <svg width="22" height="22" viewBox="0 0 24 24"><use href="#i-star"/></svg>
                        </span>
                    <?php endfor; ?>
                </div>

                <p class="reviews__count">
                    <?= $count ?>&nbsp;<?= View::e(
                        Text::plural($count, 'отзыв', 'отзыва', 'отзывов')
                    ) ?>
                    <span><?= View::e($reviews['asof']) ?></span>
                </p>
            </div>

            <div class="reviews__body">
                <p><?= View::e($reviews['text']) ?></p>

                <?php if ($href !== ''): ?>
                    <a class="btn btn--outline" href="<?= View::e($href) ?>"
                       target="_blank" rel="noopener"
                       aria-label="<?= View::e($reviews['link']['label']) ?>: оценка <?= View::e($score) ?>">
                        <?= View::e($reviews['link']['label']) ?>
                        <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
