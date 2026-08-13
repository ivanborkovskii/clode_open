<?php
/**
 * Акцентная полоса с призывом к действию.
 * Ставится в середине страницы — для тех, кто уже готов написать.
 *
 * @var array $band
 */

use App\Core\View;
?>
<section class="cta-band">
    <div class="container cta-band__inner">
        <div>
            <h2><?= View::e($band['title']) ?></h2>
            <p><?= View::e($band['text']) ?></p>
        </div>

        <a class="btn btn--dark" href="<?= View::e($band['cta']['href']) ?>">
            <?= View::e($band['cta']['label']) ?>
            <svg width="16" height="12" viewBox="0 0 16 12" fill="none" aria-hidden="true">
                <path d="M10 1l5 5-5 5M15 6H0" stroke="currentColor" stroke-width="1.8"/>
            </svg>
        </a>
    </div>
</section>
