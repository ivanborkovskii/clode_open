<?php
/**
 * Способы связи. Значения берутся из настроек сайта, а не из текстов
 * страницы: телефон и почта не должны существовать в двух местах.
 *
 * @var array $ways   label, title, lead, items
 * @var array $config
 */

use App\Core\View;

$company = $config['company'];

// Что показать и куда вести — зависит от вида связи.
$value = [
    'phone'   => $company['phone'],
    'email'   => $company['email'],
    'address' => $company['address'],
];
$link = [
    'phone' => 'tel:' . $company['phone_href'],
    'email' => 'mailto:' . $company['email'],
];
?>
<section class="section ways">
    <div class="container">
        <div class="section-head">
            <p class="label"><?= View::e($ways['label']) ?></p>
            <h2><?= View::e($ways['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($ways['lead']) ?></p>
        </div>

        <div class="ways__grid">
            <?php foreach ($ways['items'] as $item): ?>
                <?php $kind = $item['kind']; ?>
                <div class="ways__item">
                    <span class="icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                            <use href="#<?= View::e($item['icon']) ?>"/>
                        </svg>
                    </span>

                    <h3><?= View::e($item['title']) ?></h3>

                    <?php if (isset($link[$kind])): ?>
                        <a class="ways__value" href="<?= View::e($link[$kind]) ?>">
                            <?= View::e($value[$kind]) ?>
                        </a>
                    <?php else: ?>
                        <address class="ways__value ways__value--plain">
                            <?= View::e($value[$kind]) ?>
                        </address>
                    <?php endif; ?>

                    <p class="ways__note"><?= View::e($item['note']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php // Мессенджеры — отдельной строкой под способами связи:
              // это ссылки, а не карточки с пояснением. ?>
        <?php $view->partial('partials/messengers', [
            'title'    => $ways['messengers'] ?? '',
            'modifier' => 'messengers--wide',
        ]); ?>
    </div>
</section>
