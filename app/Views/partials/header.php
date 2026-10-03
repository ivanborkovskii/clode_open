<?php
/**
 * Шапка сайта. Пункты меню соответствуют разделам архитектуры.
 *
 * @var array $config
 */

use App\Controllers\ServiceController;
use App\Controllers\TariffController;
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
// Вложенные пункты приходят из списка услуг — он один на весь сайт
// (ServiceController::PAGES). Новая страница услуги появляется в обоих
// меню сама, править этот файл не нужно.
//
// Механизм общий: подменю получит любой пункт, у которого есть 'sub'.
// Понадобится оно «Решениям» или «Тарифам» — дописать сюда, остального
// не трогать.
$services = ServiceController::menu();

// Раздел тарифов: три страницы под три разных вопроса. Группа одна,
// её заголовок и называет раздел целиком — в самой шапке на это имя
// места нет: «Тарифы и лицензии» рядом с остальными пунктами переносится
// на две строки и ломает ряд.
$tarify = [
    ['title' => 'Тарифы и лицензии', 'items' => array_map(
        static fn (array $tab): array => ['label' => $tab['label'], 'href' => $tab['href']],
        TariffController::tabs(''),
    )],
];

// key — имя подменю: из него собираются id панели на компьютере
// и имя второго экрана на телефоне. Без него два подменю получили бы
// одинаковые id, и разметка стала бы неправильной.
$menu = [
    ['key' => 'uslugi', 'label' => 'Услуги',  'href' => '/uslugi',  'sub' => $services],
    ['label' => 'Решения',    'href' => '/resheniya'],
    ['key' => 'tarify', 'label' => 'Тарифы', 'href' => TariffController::BITRIX, 'sub' => $tarify],
    ['label' => 'Кейсы',      'href' => '/keysy'],
    ['label' => 'Статьи',     'href' => '/stati'],
    ['label' => 'О компании', 'href' => '/o-kompanii'],
    ['label' => 'Контакты',   'href' => '/kontakty'],
];

// Подсветка раздела тарифов: адреса трёх его страниц общего начала
// не имеют, поэтому проверяется принадлежность списку.
$inTarify = in_array($current, TariffController::paths(), true);
?>
<header class="header">
    <div class="container header__inner">
        <?php $view->partial('partials/logo'); ?>

        <nav class="nav" aria-label="Основная навигация">
            <?php foreach ($menu as $item): ?>
                <?php if (!$view->exists($item['href'])): ?>
                    <span class="nav__link nav__link--soon" title="Раздел в разработке"><?= View::e($item['label']) ?></span>
                <?php continue; endif; ?>

                <?php if (empty($item['sub'])): ?>
                    <a class="nav__link" href="<?= View::e($item['href']) ?>"
                       <?= $isActive($item['href']) ? 'aria-current="page"' : '' ?>><?= View::e($item['label']) ?></a>
                <?php continue; endif; ?>

                <?php
                // Пункт с подменю. Сам он остаётся обычной ссылкой:
                // страница «Услуги» проиндексирована, и ссылка на неё
                // с каждой страницы сайта — главный источник её веса.
                // Раскрытие висит на наведении и на переходе клавишей,
                // переход по ссылке им не мешает.
                //
                // Обёртка нужна ради :hover и :focus-within: они должны
                // охватывать и кнопку, и панель, иначе панель исчезнет,
                // как только курсор сойдёт с надписи.
                ?>
                <?php // Подменю услуг раскладывается колонками во всю ширину
                      // шапки, подменю тарифов — короткий список под своим
                      // пунктом. Отсюда модификатор. ?>
                <?php $narrow = $item['key'] !== 'uslugi'; ?>
                <div class="nav__item<?= $narrow ? ' nav__item--narrow' : '' ?>" data-nav-item>
                    <a class="nav__link nav__link--has-sub" href="<?= View::e($item['href']) ?>"
                       aria-expanded="false" aria-controls="submenu-<?= View::e($item['key']) ?>"
                       <?= $isActive($item['href']) || ($item['key'] === 'tarify' && $inTarify) ? 'aria-current="page"' : '' ?>>
                        <?= View::e($item['label']) ?>
                        <svg class="nav__chev" width="11" height="11" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-chevron-down"/></svg>
                    </a>

                    <div class="submenu<?= $narrow ? ' submenu--narrow' : '' ?>" id="submenu-<?= View::e($item['key']) ?>" data-submenu>
                        <div class="submenu__inner">
                            <?php foreach ($item['sub'] as $group): ?>
                                <div class="submenu__group">
                                    <?php if ($group['title'] !== ''): ?>
                                        <p class="submenu__head"><?= View::e($group['title']) ?></p>
                                    <?php endif; ?>

                                    <?php foreach ($group['items'] as $sub): ?>
                                        <a class="submenu__link" href="<?= View::e($sub['href']) ?>"
                                           <?= $current === $sub['href'] ? 'aria-current="page"' : '' ?>><?= View::e($sub['label']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php // Страховка для тех, кто ничего не выбрал: путь на полный список.
                              // У тарифов такой страницы нет — там и выбирать-то не из чего. ?>
                        <?php if ($item['key'] === 'uslugi'): ?>
                            <div class="submenu__foot">
                                <a class="submenu__all" href="<?= View::e($item['href']) ?>">Все услуги одной страницей</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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

<?php
// Меню на телефоне. Два экрана, а не раскрывающийся список.
//
// Наведения на телефоне нет, поэтому поведение здесь своё. Раскрывать
// услуги прямо в списке (аккордеоном) при дюжине пунктов значит
// вытолкнуть «Кейсы» и «Контакты» далеко за нижний край. Поэтому
// нажатие уводит на второй экран со стрелкой «Назад» — так устроены
// меню в приложениях, и объяснять это никому не нужно.
//
// Оба экрана лежат в разметке сразу: сдвигаются они стилями, и
// без скрипта второй экран остаётся доступен обычной ссылкой.
?>
<div class="mobile-menu" id="mobile-menu" data-open="false" data-screen="main">
    <div class="mobile-menu__screens">

    <div class="mobile-menu__screen" data-screen-main>
    <nav aria-label="Мобильная навигация">
        <?php foreach ($menu as $item): ?>
            <?php if (!$view->exists($item['href'])): ?>
                <span class="mobile-menu__link mobile-menu__link--soon"><?= View::e($item['label']) ?></span>
            <?php elseif (empty($item['sub'])): ?>
                <a class="mobile-menu__link" href="<?= View::e($item['href']) ?>"
                   <?= $isActive($item['href']) ? 'aria-current="page"' : '' ?>><?= View::e($item['label']) ?></a>
            <?php else: ?>
                <?php
                // Ссылка, а не кнопка, и это важно. Без скрипта нажатие
                // откроет страницу «Услуги» — то есть меню остаётся
                // рабочим. Скрипт перехватывает нажатие и вместо перехода
                // сдвигает экран.
                ?>
                <a class="mobile-menu__link mobile-menu__link--has-sub"
                   href="<?= View::e($item['href']) ?>"
                   data-menu-drill="<?= View::e($item['key']) ?>"
                   <?= $isActive($item['href']) || ($item['key'] === 'tarify' && $inTarify) ? 'aria-current="page"' : '' ?>>
                    <?= View::e($item['label']) ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    </div>

    <?php
    // Вторые экраны — по одному на каждый пункт с подменю. Раньше он был
    // один и прямо под услуги; теперь собирается из того же $menu,
    // и третий появится сам, если понадобится.
    //
    // Подпись у блока не нужна: внутри стоит <nav> со своей.
    ?>
    <?php foreach ($menu as $item): ?>
        <?php if (empty($item['sub']) || !$view->exists($item['href'])) { continue; } ?>

        <div class="mobile-menu__screen mobile-menu__screen--sub" data-screen-sub="<?= View::e($item['key']) ?>">
            <button class="mobile-menu__back" type="button" data-menu-back>
                <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-chevron-left"/></svg>
                Назад
            </button>

            <?php if ($item['key'] === 'uslugi'): ?>
                <a class="mobile-menu__title" href="/uslugi">Все услуги</a>
            <?php else: ?>
                <p class="mobile-menu__title"><?= View::e($item['label']) ?></p>
            <?php endif; ?>

            <nav aria-label="<?= View::e($item['label']) ?>">
                <?php foreach ($item['sub'] as $group): ?>
                    <?php if ($group['title'] !== ''): ?>
                        <p class="mobile-menu__head"><?= View::e($group['title']) ?></p>
                    <?php endif; ?>

                    <?php foreach ($group['items'] as $sub): ?>
                        <a class="mobile-menu__sub" href="<?= View::e($sub['href']) ?>"
                           <?= $current === $sub['href'] ? 'aria-current="page"' : '' ?>><?= View::e($sub['label']) ?></a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    <?php endforeach; ?>

    </div>

    <div class="mobile-menu__foot">
        <a class="mobile-menu__phone" href="tel:<?= View::e($company['phone_href']) ?>">
            <?= View::e($company['phone']) ?>
        </a>
        <a class="mobile-menu__mail" href="mailto:<?= View::e($company['email']) ?>">
            <?= View::e($company['email']) ?>
        </a>

        <?php // Мессенджеры и здесь: меню на телефоне — это единственное
              // место, где собраны все способы связи, и уходить за ними
              // в подвал через всю страницу незачем. ?>
        <?php $view->partial('partials/messengers', [
            'modifier' => 'messengers--invert',
        ]); ?>

        <a class="btn btn--primary btn--block"
           href="<?= $hasForm ? '#zayavka' : '/kontakty#zayavka' ?>"
           data-menu-close>Оставить заявку</a>
    </div>
</div>
