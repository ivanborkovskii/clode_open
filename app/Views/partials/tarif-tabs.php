<?php
/**
 * Вкладки раздела «Тарифы и лицензии».
 *
 * Три страницы раздела отвечают на три разных вопроса — какой Битрикс24
 * купить, какой тариф amoCRM купить и сколько стоит продлить уже
 * купленный Битрикс24. Содержимое у них не пересекается, а вот человек
 * нередко приходит не на ту: за продлением на страницу тарифов, за
 * тарифами amoCRM на тарифы Битрикс24. Полоса вкладок даёт перейти
 * в один щелчок, не возвращаясь в меню.
 *
 * Текущая вкладка выводится не ссылкой: ссылка на саму себя ничего
 * не делает, а экранному диктору сообщает лишнюю цель перехода.
 *
 * На узком экране полоса прокручивается вбок внутри себя — страница
 * вбок не едет.
 *
 * @var list<array{label: string, href: string, active: bool}> $tabs
 */

use App\Core\View;
?>
<nav class="tarif-tabs" aria-label="Тарифы и лицензии">
    <div class="container">
        <div class="tarif-tabs__row">
            <?php foreach ($tabs as $tab): ?>
                <?php if ($tab['active']): ?>
                    <span class="tarif-tabs__tab tarif-tabs__tab--on" aria-current="page">
                        <?= View::e($tab['label']) ?>
                    </span>
                <?php else: ?>
                    <a class="tarif-tabs__tab" href="<?= View::e($tab['href']) ?>">
                        <?= View::e($tab['label']) ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</nav>
