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
        <?php
        // Заголовок полосы — <h2>, а не <p>.
        //
        // Текст здесь и так был заголовком раздела по смыслу, но помечен
        // как обычный абзац. Для человека со скринридером это значит,
        // что полоса логотипов не имеет названия и при переходе
        // по заголовкам просто пропускается. Валидатор W3C предупреждал
        // ровно об этом.
        //
        // Внешний вид не меняется: размер, начертание, разрядка, цвет
        // и отступ заданы классом, а высота строки дописана в него же —
        // иначе заголовок взял бы свою, более плотную.
        ?>
        <h2 class="clients__title"><?= View::e($clients['title']) ?></h2>

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
