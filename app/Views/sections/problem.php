<?php
/**
 * Блок «проблема»: зачем вообще нужна CRM.
 * Идёт до услуг — сначала читатель должен узнать свою ситуацию.
 *
 * @var array $problem
 */

use App\Core\View;
?>
<section class="section problem">
    <div class="container">
        <div class="section-head">
            <p class="label">Ситуация</p>
            <h2><?= View::e($problem['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($problem['lead']) ?></p>
        </div>

        <div class="problem__grid">
            <?php foreach ($problem['items'] as $item): ?>
                <article class="problem__item">
                    <span class="icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><use href="#<?= View::e($item['icon']) ?>"/></svg>
                    </span>
                    <h3><?= View::e($item['title']) ?></h3>
                    <p><?= View::e($item['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
