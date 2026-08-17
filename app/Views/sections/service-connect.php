<?php
/**
 * Что подключается к системе.
 *
 * Шесть групп: название, перечень систем и строка о том, что это даёт.
 * Третья строка не украшение — без неё список названий ничего не сообщает
 * человеку, который не знает, зачем ему Sipuni или Roistat.
 *
 * @var array $connect label, title, lead, groups
 */

use App\Core\View;
?>
<section class="section connect">
    <div class="container">
        <div class="section-head">
            <p class="label"><?= View::e($connect['label'] ?? 'Интеграции') ?></p>
            <h2><?= View::e($connect['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($connect['lead']) ?></p>
        </div>

        <div class="connect__grid">
            <?php foreach ($connect['groups'] as $group): ?>
                <div class="connect__item">
                    <span class="icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                            <use href="#<?= View::e($group['icon']) ?>"/>
                        </svg>
                    </span>

                    <h3><?= View::e($group['title']) ?></h3>
                    <p class="connect__systems"><?= View::e($group['text']) ?></p>
                    <p class="connect__note"><?= View::e($group['note']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
