<?php
/**
 * Изометрическая схема: три слоя работы системы снизу вверх —
 * источники, CRM, управление.
 *
 * Строится параметрически: каждый слой — ромб (верхняя грань) плюс две
 * боковые грани с затемнением. Подписи вынесены вправо, чтобы плиты
 * не перекрывали текст.
 */

use App\Core\View;

$cx = 150;   // центр плит по горизонтали
$w  = 128;   // половина ширины ромба
$h  = 54;    // половина высоты ромба (изометрия примерно 2:1)
$d  = 16;    // толщина плиты
$lx = 300;   // левый край подписей

// Расстояние между слоями больше высоты плиты — слои не наезжают друг на друга.
$layers = [
    ['y' => 330, 'label' => 'Источники',  'text' => 'Сайт, звонки, мессенджеры, почта, 1С'],
    ['y' => 205, 'label' => 'CRM',        'text' => 'Воронки, карточки, автоматизация', 'accent' => true],
    ['y' => 80,  'label' => 'Управление', 'text' => 'Аналитика, задачи, контроль'],
];
?>
<svg class="iso" viewBox="0 0 560 430" role="img"
     aria-label="Три слоя системы: источники заявок, CRM и управление">

    <?php // Ось, вдоль которой данные идут снизу вверх. ?>
    <path class="iso__axis" d="M<?= $cx ?> <?= 330 - $h ?> V<?= 80 + $h ?>"/>

    <?php foreach ($layers as $layer):
        $y = $layer['y'];
        ?>
        <g class="iso__layer <?= !empty($layer['accent']) ? 'iso__layer--accent' : '' ?>">
            <?php // Боковые грани: левая темнее правой — объём без градиентов. ?>
            <path class="iso__side iso__side--left"
                  d="M<?= $cx - $w ?> <?= $y ?> L<?= $cx ?> <?= $y + $h ?> V<?= $y + $h + $d ?> L<?= $cx - $w ?> <?= $y + $d ?> Z"/>
            <path class="iso__side iso__side--right"
                  d="M<?= $cx + $w ?> <?= $y ?> L<?= $cx ?> <?= $y + $h ?> V<?= $y + $h + $d ?> L<?= $cx + $w ?> <?= $y + $d ?> Z"/>

            <path class="iso__top"
                  d="M<?= $cx ?> <?= $y - $h ?> L<?= $cx + $w ?> <?= $y ?> L<?= $cx ?> <?= $y + $h ?> L<?= $cx - $w ?> <?= $y ?> Z"/>

            <?php // Выноска к подписи справа. ?>
            <path class="iso__leader" d="M<?= $cx + $w ?> <?= $y ?> H<?= $lx - 14 ?>"/>

            <text class="iso__label" x="<?= $lx ?>" y="<?= $y - 2 ?>"><?= View::e($layer['label']) ?></text>
            <text class="iso__text" x="<?= $lx ?>" y="<?= $y + 18 ?>"><?= View::e($layer['text']) ?></text>
        </g>
    <?php endforeach; ?>

    <?php // Точки, поднимающиеся по оси: движение данных снизу вверх. ?>
    <?php foreach ([0, 1.2, 2.4] as $delay): ?>
        <circle class="iso__spark" cx="<?= $cx ?>" cy="<?= 330 - $h ?>" r="3">
            <animate attributeName="cy" from="<?= 330 - $h ?>" to="<?= 80 + $h ?>"
                     dur="3.6s" begin="<?= $delay ?>s" repeatCount="indefinite"/>
            <animate attributeName="opacity" values="0;1;1;0" dur="3.6s"
                     begin="<?= $delay ?>s" repeatCount="indefinite"/>
        </circle>
    <?php endforeach; ?>
</svg>
