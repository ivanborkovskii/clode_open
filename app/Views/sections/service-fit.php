<?php
/**
 * «Когда берут эту услугу» — список ситуаций.
 *
 * Стоит сразу под шапкой страницы: человек должен за несколько секунд
 * понять, про него это или нет, и не читать этапы работ впустую.
 *
 * @var array $fit title, items
 */

use App\Core\View;
?>
<section class="section section--tight fit">
    <div class="container">
        <div class="fit__layout">
            <h2 class="fit__title"><?= View::e($fit['title']) ?></h2>

            <ul class="fit__list">
                <?php foreach ($fit['items'] as $item): ?>
                    <li><?= View::e($item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
