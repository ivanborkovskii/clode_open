<?php
/**
 * Принципы работы. Действуют на любой услуге, поэтому вынесены отдельным
 * блоком, а не повторяются в каждой строке перечня.
 *
 * @var array $approach label, title, lead, items
 */

use App\Core\View;
?>
<section class="section section--alt approach-block">
    <div class="container">
        <div class="section-head">
            <p class="label"><?= View::e($approach['label'] ?? 'Подход') ?></p>
            <h2><?= View::e($approach['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($approach['lead']) ?></p>
        </div>

        <div class="approach-grid">
            <?php foreach ($approach['items'] as $item): ?>
                <div class="approach-item">
                    <h3><?= View::e($item['title']) ?></h3>
                    <p><?= View::e($item['text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
