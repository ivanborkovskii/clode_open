<?php
/**
 * Полоса логотипов клиентов.
 * Стоит сразу под фактами: подтверждает цифры конкретными компаниями.
 *
 * @var array $clients
 */

use App\Core\View;
?>
<section class="section--tight clients">
    <div class="container">
        <p class="clients__title"><?= View::e($clients['title']) ?></p>

        <ul class="clients__row">
            <?php foreach ($clients['items'] as $client): ?>
                <li class="clients__item">
                    <img src="/assets/img/logos/<?= View::e($client["slug"]) ?>.webp"
                         alt="Логотип компании <?= View::e($client['name']) ?>"
                         height="44" loading="lazy" decoding="async">
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
