<?php
/**
 * Воронка продаж.
 *
 * Идёт сразу после блока проблем: там сказано «непонятен статус сделок»,
 * здесь показано, как это выглядит, когда система есть.
 *
 * Ширина полосы сужается от этапа к этапу — это узнаваемая форма воронки.
 * Числа и конверсии не выводим: таких данных нет, а на диаграмме они
 * читались бы как реальная статистика.
 *
 * @var array $funnel
 */

use App\Core\View;

$total = count($funnel['stages']);
?>
<section class="section funnel-section" id="voronka">
    <div class="container funnel-section__layout">
        <div class="funnel-section__intro">
            <p class="label">Воронка</p>
            <h2><?= View::e($funnel['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($funnel['lead']) ?></p>
            <p class="funnel-section__note"><?= View::e($funnel['note']) ?></p>
        </div>

        <ol class="funnel">
            <?php foreach ($funnel['stages'] as $i => $stage):
                // От 100 % на первом этапе до 44 % на последнем.
                $width = 100 - $i * (56 / max(1, $total - 1));
                ?>
                <li class="funnel__stage">
                    <span class="funnel__num"><?= sprintf('%02d', $i + 1) ?></span>
                    <span class="funnel__name"><?= View::e($stage) ?></span>
                    <span class="funnel__bar" aria-hidden="true">
                        <i style="width: <?= round($width, 1) ?>%"></i>
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
