<?php
/**
 * Интеграции: что подключается к CRM.
 * Названия систем — из списка заказчика, ничего не дописано.
 *
 * @var array $integrations
 */

use App\Core\View;
?>
<section class="section section--pattern" id="integracii">
    <div class="container">
        <div class="section-head">
            <p class="label">Интеграции</p>
            <h2><?= View::e($integrations['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($integrations['lead']) ?></p>
        </div>

        <div class="integrations__grid">
            <?php foreach ($integrations['groups'] as $group): ?>
                <article class="integrations__cell">
                    <span class="icon-box">
                        <svg width="26" height="26" viewBox="0 0 24 24" aria-hidden="true"><use href="#<?= View::e($group['icon']) ?>"/></svg>
                    </span>

                    <h3><?= View::e($group['title']) ?></h3>
                    <p><?= View::e($group['text']) ?></p>

                    <ul class="tags">
                        <?php foreach ($group['tags'] as $tag): ?>
                            <li class="tag"><?= View::e($tag) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
