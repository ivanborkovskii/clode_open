<?php
/**
 * Шапка сайта. Пункты меню соответствуют разделам архитектуры.
 *
 * @var array $config
 */

use App\Core\View;

$company = $config['company'];

// Текущий адрес без параметров — по нему подсвечивается активный раздел.
// Подраздел тоже подсвечивает свой пункт: /uslugi/integracii → «Услуги».
$current = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

$isActive = static fn (string $href): bool =>
    $href !== '/' && ($current === $href || str_starts_with($current, $href . '/'));

// Единый источник пунктов меню — используется и в шапке, и в мобильном меню.
$menu = [
    ['label' => 'Услуги',     'href' => '/uslugi'],
    ['label' => 'Решения',    'href' => '/resheniya'],
    ['label' => 'Кейсы',      'href' => '/keysy'],
    ['label' => 'Статьи',     'href' => '/stati'],
    ['label' => 'О компании', 'href' => '/o-kompanii'],
    ['label' => 'Контакты',   'href' => '/kontakty'],
];
?>
<header class="header">
    <div class="container header__inner">
        <a class="logo" href="/">
            <span class="logo__mark">//</span>
            <span><?= View::e($company['brand']) ?></span>
        </a>

        <nav class="nav" aria-label="Основная навигация">
            <?php foreach ($menu as $item): ?>
                <a class="nav__link" href="<?= View::e($item['href']) ?>"
                   <?= $isActive($item['href']) ? 'aria-current="page"' : '' ?>><?= View::e($item['label']) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="header__contacts">
            <a class="header__phone" href="tel:<?= View::e($company['phone_href']) ?>">
                <?= View::e($company['phone']) ?>
            </a>
            <a class="btn btn--primary header__cta" href="#zayavka">Оставить заявку</a>
        </div>

        <button class="burger" type="button"
                aria-label="Открыть меню"
                aria-expanded="false"
                aria-controls="mobile-menu"
                data-menu-toggle>
            <span class="burger__box">
                <span></span><span></span><span></span>
            </span>
        </button>
    </div>
</header>

<div class="mobile-menu" id="mobile-menu" data-open="false">
    <nav aria-label="Мобильная навигация">
        <?php foreach ($menu as $item): ?>
            <a class="mobile-menu__link" href="<?= View::e($item['href']) ?>"
               <?= $isActive($item['href']) ? 'aria-current="page"' : '' ?>><?= View::e($item['label']) ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="mobile-menu__foot">
        <a class="mobile-menu__phone" href="tel:<?= View::e($company['phone_href']) ?>">
            <?= View::e($company['phone']) ?>
        </a>
        <a class="mobile-menu__mail" href="mailto:<?= View::e($company['email']) ?>">
            <?= View::e($company['email']) ?>
        </a>
        <a class="btn btn--primary btn--block" href="#zayavka" data-menu-close>Оставить заявку</a>
    </div>
</div>
