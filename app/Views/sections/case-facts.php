<?php
/**
 * Карточка фактов кейса: система, отрасль, задача, что было и почему
 * так получилось. Стоит сразу под шапкой — до того, как читать этапы работ.
 *
 * @var array $facts system, industry, task, before, why
 */

use App\Core\View;
?>
<section class="section section--tight case-facts">
    <div class="container">
        <div class="case-facts__grid">
            <div class="case-facts__side">
                <p class="case-facts__label">Система</p>
                <p class="case-facts__value"><?= View::e($facts['system']) ?></p>

                <p class="case-facts__label">Отрасль</p>
                <p class="case-facts__value"><?= View::e($facts['industry']) ?></p>
            </div>

            <div class="case-facts__main">
                <div class="case-facts__block">
                    <h2>Задача</h2>
                    <p><?= View::e($facts['task']) ?></p>
                </div>

                <div class="case-facts__block">
                    <h2>Что было до</h2>
                    <p><?= View::e($facts['before']) ?></p>
                </div>

                <?php if (!empty($facts['why'])): ?>
                    <p class="case-facts__why"><?= View::e($facts['why']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
