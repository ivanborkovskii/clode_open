<?php
/**
 * Юридический документ: заголовок и разделы.
 * Ширина колонки ограничена — длинный текст должен читаться.
 *
 * @var array $doc
 */

use App\Core\View;
?>
<section class="section legal">
    <div class="container">
        <div class="legal__head">
            <p class="label">Документ</p>
            <h1><?= View::e($doc['title']) ?></h1>
        </div>

        <div class="legal__body">
            <?php foreach ($doc['sections'] as $section): ?>
                <section class="legal__section">
                    <h2><?= View::e($section['h']) ?></h2>

                    <?php foreach ($section['p'] ?? [] as $paragraph): ?>
                        <p><?= View::e($paragraph) ?></p>
                    <?php endforeach; ?>

                    <?php if (!empty($section['list'])): ?>
                        <ol class="legal__list">
                            <?php foreach ($section['list'] as $item): ?>
                                <li><?= View::e($item) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</section>
