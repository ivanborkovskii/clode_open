<?php
/**
 * Первый экран.
 *
 * Справа — схема: разрозненные источники заявок и учётные системы
 * сводятся в одну CRM. Она объясняет суть услуги без стоковых иллюстраций.
 *
 * @var array $hero
 */

use App\Core\View;

// Геометрия схемы: подписи слева, общий вертикальный провод, ядро справа.
// Ряды расположены симметрично относительно центра ядра (y = 180).
$rows  = [40, 96, 152, 208, 264, 320];
$nodes = $hero['diagram']['nodes'];
?>
<section class="hero">
    <div class="container hero__inner">
        <div class="hero__content">
            <p class="hero__eyebrow"><?= View::e($hero['eyebrow']) ?></p>

            <h1><?= View::e($hero['h1']) ?></h1>

            <p class="hero__lead"><?= View::e($hero['lead']) ?></p>
            <p class="hero__note"><?= View::e($hero['note']) ?></p>

            <div class="hero__actions">
                <a class="btn btn--primary" href="<?= View::e($hero['cta']['href']) ?>">
                    <?= View::e($hero['cta']['label']) ?>
                    <svg width="16" height="12" viewBox="0 0 16 12" fill="none" aria-hidden="true">
                        <path d="M10 1l5 5-5 5M15 6H0" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </a>
                <a class="btn btn--ghost" href="<?= View::e($hero['cta_alt']['href']) ?>">
                    <?= View::e($hero['cta_alt']['label']) ?>
                </a>
            </div>
        </div>

        <div class="hero__figure">
            <svg class="scheme" viewBox="0 0 520 360" role="img"
                 aria-label="<?= View::e($hero['diagram']['title']) ?>">

                <?php foreach ($nodes as $i => $node): $y = $rows[$i]; ?>
                    <?php // Провод: от узла вправо, по общей вертикали к центру и в ядро. ?>
                    <path id="wire-<?= $i ?>" class="scheme__wire"
                          d="M150 <?= $y ?> H235 V180 H295"/>
                    <circle class="scheme__node" cx="150" cy="<?= $y ?>" r="4"/>
                    <text class="scheme__label" x="136" y="<?= $y + 5 ?>" text-anchor="end">
                        <?= View::e($node) ?>
                    </text>

                    <circle class="scheme__pulse" r="2.5">
                        <animateMotion dur="3.4s" begin="<?= $i * 0.45 ?>s" repeatCount="indefinite">
                            <mpath href="#wire-<?= $i ?>"/>
                        </animateMotion>
                    </circle>
                <?php endforeach; ?>

                <?php // Ядро схемы. ?>
                <rect class="scheme__box" x="295" y="128" width="210" height="104"/>
                <text class="scheme__core-label" x="400" y="175" text-anchor="middle">
                    <?= View::e($hero['diagram']['core']) ?>
                </text>
                <text class="scheme__core-sub" x="400" y="200" text-anchor="middle">
                    ОДНА СИСТЕМА
                </text>
            </svg>
        </div>
    </div>
</section>
