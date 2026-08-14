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
            <svg width="18" height="16" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-arrow"/></svg>
        </a>
    </div>
</section>
