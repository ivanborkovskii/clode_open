<?php
/**
 * Подвал: навигация, контакты, реквизиты.
 *
 * @var array $config
 */

use App\Core\View;

$company = $config['company'];

$columns = [
    'Услуги' => [
        ['label' => 'Внедрение Битрикс24',       'href' => '/uslugi/vnedrenie-bitrix24'],
        ['label' => 'Внедрение amoCRM',          'href' => '/uslugi/vnedrenie-amocrm'],
        ['label' => 'Настройка и доработка CRM', 'href' => '/uslugi/nastroyka-i-dorabotka-crm'],
        ['label' => 'Интеграции',                'href' => '/uslugi/integracii'],
        ['label' => 'Сопровождение CRM',         'href' => '/uslugi/soprovozhdenie-crm'],
    ],
    'Решения' => [
        ['label' => 'Продажи',                 'href' => '/resheniya/prodazhi'],
        ['label' => 'Коммуникации',            'href' => '/resheniya/kommunikacii'],
        ['label' => 'Аналитика',               'href' => '/resheniya/analitika'],
        ['label' => 'Управление сотрудниками', 'href' => '/resheniya/upravlenie-sotrudnikami'],
    ],
    'Компания' => [
        ['label' => 'Кейсы',      'href' => '/keysy'],
        ['label' => 'Статьи',     'href' => '/stati'],
        ['label' => 'О компании', 'href' => '/o-kompanii'],
        ['label' => 'Контакты',   'href' => '/kontakty'],
    ],
];
?>
<footer class="footer">
    <div class="container">
        <div class="footer__top">
            <div>
                <a class="logo" href="/">
                    <span class="logo__mark">//</span>
                    <span><?= View::e($company['brand']) ?></span>
                </a>

                <div class="footer__contact">
                    <a class="footer__phone" href="tel:<?= View::e($company['phone_href']) ?>">
                        <?= View::e($company['phone']) ?>
                    </a>
                    <a href="mailto:<?= View::e($company['email']) ?>"><?= View::e($company['email']) ?></a>
                    <address style="font-style: normal;"><?= View::e($company['address']) ?></address>
                </div>
            </div>

            <?php foreach ($columns as $title => $links): ?>
                <div>
                    <div class="footer__title"><?= View::e($title) ?></div>
                    <ul class="footer__list">
                        <?php foreach ($links as $link): ?>
                            <li>
                                <?php // Раздел ещё не разработан — показываем название без ссылки. ?>
                                <?php if ($view->exists($link['href'])): ?>
                                    <a href="<?= View::e($link['href']) ?>"><?= View::e($link['label']) ?></a>
                                <?php else: ?>
                                    <span class="footer__soon"><?= View::e($link['label']) ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="footer__bottom">
            <div class="footer__legal">
                <span><?= View::e($company['legal_name']) ?></span>
                <span>ОГРН <?= View::e($company['ogrn']) ?> · ИНН <?= View::e($company['inn']) ?></span>
            </div>
            <nav class="footer__legal-links" aria-label="Правовые документы">
                <a href="/privacy">Политика конфиденциальности</a>
                <a href="/soglasie">Согласие на обработку данных</a>
            </nav>

            <span>© <?= date('Y') ?></span>
        </div>
    </div>
</footer>
