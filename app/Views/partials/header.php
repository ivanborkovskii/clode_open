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
// Показываются все разделы архитектуры. У неразработанных ссылки нет:
// пункт виден, но не кликается — иначе он вёл бы в «страница не найдена».
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
        <?php $view->partial('partials/logo'); ?>

        <nav class="nav" aria-label="Основная навигация">
            <?php foreach ($menu as $item): ?>
                <?php if ($view->exists($item['href'])): ?>
                    <a class="nav__link" href="<?= View::e($item['href']) ?>"
                       <?= $isActive($item['href']) ? 'aria-current="page"' : '' ?>><?= View::e($item['label']) ?></a>
                <?php else: ?>
                    <span class="nav__link nav__link--soon" title="Раздел в разработке"><?= View::e($item['label']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="header__contacts">
            <a class="header__phone" href="tel:<?= View::e($company['phone_href']) ?>">
                <?= View::e($company['phone']) ?>
            </a>
        </div>

        <?php
        // Кнопка заявки видна и на телефоне: шапка едет вместе с прокруткой,
        // и в длинной статье она остаётся единственным способом оставить
        // заявку, не долистав до конца. Телефон и меню на телефоне прячутся,
        // кнопка — нет.
        //
        // Надписи две, показывается одна: на узком экране «Оставить заявку»
        // не помещается рядом с логотипом, и вместо того чтобы резать
        // логотип, режем надпись. Какая именно видна — решают стили.
        //
        // На правовых страницах формы заявки нет, и якорь вёл бы в никуда —
        // там кнопка отправляет на «Контакты».
        $hasForm = !in_array($current, ['/privacy', '/soglasie'], true);
        ?>
        <a class="btn btn--primary header__cta"
           href="<?= $hasForm ? '#zayavka' : '/kontakty#zayavka' ?>">
            <span class="header__cta-long">Оставить заявку</span>
            <span class="header__cta-short">Заявка</span>
        </a>

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
            <?php if ($view->exists($item['href'])): ?>
                <a class="mobile-menu__link" href="<?= View::e($item['href']) ?>"
                   <?= $isActive($item['href']) ? 'aria-current="page"' : '' ?>><?= View::e($item['label']) ?></a>
            <?php else: ?>
                <span class="mobile-menu__link mobile-menu__link--soon"><?= View::e($item['label']) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="mobile-menu__foot">
        <a class="mobile-menu__phone" href="tel:<?= View::e($company['phone_href']) ?>">
            <?= View::e($company['phone']) ?>
        </a>
        <a class="mobile-menu__mail" href="mailto:<?= View::e($company['email']) ?>">
            <?= View::e($company['email']) ?>
        </a>
        <a class="btn btn--primary btn--block"
           href="<?= $hasForm ? '#zayavka' : '/kontakty#zayavka' ?>"
           data-menu-close>Оставить заявку</a>
    </div>
</div>
