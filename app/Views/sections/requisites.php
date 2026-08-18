<?php
/**
 * Реквизиты. Как и контакты, берутся из настроек сайта.
 *
 * @var array $requisites label, title, lead, note
 * @var array $config
 */

use App\Core\View;

$company = $config['company'];

$rows = [
    'Наименование' => $company['legal_name'],
    'ОГРНИП'       => $company['ogrn'],
    'ИНН'          => $company['inn'],
    'Адрес'        => $company['address'],
    'Телефон'      => $company['phone'],
    'Почта'        => $company['email'],
];
?>
<section class="section section--alt requisites">
    <div class="container">
        <div class="requisites__layout">
            <div>
                <p class="label"><?= View::e($requisites['label']) ?></p>
                <h2><?= View::e($requisites['title']) ?></h2>
                <p class="requisites__lead"><?= View::e($requisites['lead']) ?></p>
            </div>

            <div>
                <dl class="requisites__list">
                    <?php foreach ($rows as $name => $value): ?>
                        <div class="requisites__row">
                            <dt><?= View::e($name) ?></dt>
                            <dd><?= View::e($value) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>

                <p class="requisites__note"><?= View::e($requisites['note']) ?></p>
            </div>
        </div>
    </div>
</section>
