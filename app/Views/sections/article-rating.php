<?php
/**
 * Оценка статьи: пять звёзд.
 *
 * Звёзды — обычные кнопки отправки формы: без JavaScript оценка ставится
 * перезагрузкой страницы, со скриптом — без неё.
 *
 * Кнопки идут в обратном порядке, с пятой по первую, а строка
 * разворачивается обратно средствами CSS. Так подсветка «до наведённой
 * звезды» делается одним правилом: в CSS можно выбрать следующих соседей,
 * но нельзя предыдущих.
 *
 * @var array $article
 * @var array $rating  summary => [average, votes], mine => ?int
 * @var array $texts
 */

use App\Core\Csrf;
use App\Core\Text;
use App\Core\View;

$labels  = $texts['article']['rating'];
$average = $rating['summary']['average'];
$votes   = $rating['summary']['votes'];
$mine    = $rating['mine'];

// Закрашено столько звёзд, сколько поставил сам читатель. Если он ещё
// не голосовал — показываем среднюю оценку.
$filled = $mine ?? (int) round($average);
?>
<section class="section section--tight" id="ocenka">
    <div class="container container--narrow">
        <div class="rating">
            <div class="rating__text">
                <p class="rating__title"><?= View::e($labels['title']) ?></p>
                <p class="rating__lead">
                    <?= View::e($mine === null ? $labels['lead'] : $labels['thanks']) ?>
                </p>
            </div>

            <form class="rating__form" method="post"
                  action="/stati/<?= View::e($article['slug']) ?>/ocenka"
                  data-rating data-value="<?= (int) $filled ?>">
                <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">

                <div class="rating__stars" role="group" aria-label="<?= View::e($labels['title']) ?>">
                    <?php for ($value = 5; $value >= 1; $value--): ?>
                        <button class="rating__star<?= $value <= $filled ? ' is-on' : '' ?>"
                                type="submit" name="value" value="<?= $value ?>"
                                data-star="<?= $value ?>"
                                aria-label="Оценить на <?= $value ?> из 5"
                                <?= $mine === $value ? 'aria-pressed="true"' : '' ?>>
                            <svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true">
                                <use href="#i-star"/>
                            </svg>
                        </button>
                    <?php endfor; ?>
                </div>

                <p class="rating__summary" data-rating-summary>
                    <?php if ($votes > 0): ?>
                        <b><?= View::e(number_format($average, 1, ',', ' ')) ?></b>
                        <span><?= (int) $votes ?>&nbsp;<?= View::e(
                            Text::plural($votes, ...$labels['votes'])
                        ) ?></span>
                    <?php else: ?>
                        <span><?= View::e($labels['none']) ?></span>
                    <?php endif; ?>
                </p>
            </form>
        </div>
    </div>
</section>
