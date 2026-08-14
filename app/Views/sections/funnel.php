<?php
/**
 * Воронка продаж.
 *
 * Идёт сразу после блока проблем: там сказано «непонятен статус сделок»,
 * здесь показано, как это выглядит, когда система есть.
 *
 * Длина полосы равна конверсии этапа, поэтому диаграмма не может
 * разойтись с подписью — обе берутся из одного числа.
 *
 * @var array $funnel
 */

use App\Core\View;

$stages = $funnel['stages'];

// Самый большой провал между соседними этапами. Считается из данных,
// а не задаётся руками: поменяются проценты — метка переедет сама.
$dropIndex = 0;
$dropSize  = 0;

foreach ($stages as $i => $stage) {
    if ($i === 0) {
        continue;
    }

    $delta = $stages[$i - 1]['value'] - $stage['value'];

    if ($delta > $dropSize) {
        $dropSize  = $delta;
        $dropIndex = $i;
    }
}
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
            <?php foreach ($stages as $i => $stage):
                $isDrop = $dropSize > 0 && $i === $dropIndex;
                ?>
                <li class="funnel__stage <?= $isDrop ? 'is-drop' : '' ?>">
                    <span class="funnel__num"><?= sprintf('%02d', $i + 1) ?></span>

                    <span class="funnel__name">
                        <?= View::e($stage['name']) ?>
                        <?php if ($isDrop): ?>
                            <em class="funnel__drop">
                                −<?= $dropSize ?> п.п., <?= View::e($funnel['drop_label']) ?>
                            </em>
                        <?php endif; ?>
                    </span>

                    <span class="funnel__bar">
                        <i style="width: <?= (int) $stage['value'] ?>%"></i>
                    </span>

                    <span class="funnel__value"><?= (int) $stage['value'] ?>%</span>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
