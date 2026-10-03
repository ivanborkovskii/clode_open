<?php
/**
 * Калькулятор стоимости лицензии.
 *
 * Статические таблицы «3 пользователя, 5, 7, 10, 20, 30» решают задачу
 * плохо: нужного числа в них всё равно нет, а страница распухает. Здесь
 * человек ставит своё число и сразу видит сумму.
 *
 * РАБОТАЕТ БЕЗ СКРИПТА. Поля — обычные select и number, а результат
 * посчитан на сервере по значениям по умолчанию. Без JavaScript блок
 * остаётся осмысленным примером расчёта; со скриптом цифры меняются
 * на лету. Формы здесь нет намеренно: отправлять нечего, это
 * справочный блок.
 *
 * Цены уходят в разметку одним data-атрибутом: скрипту не нужно знать
 * ни тарифов, ни сроков, он только перемножает выбранное.
 *
 * @var array $calc label, title, lead, plans, terms, default, note
 */

use App\Core\Text;
use App\Core\View;

$plans = $calc['plans'];
$terms = $calc['terms'];

$planIndex = $calc['default']['plan'];
$termKey   = $calc['default']['term'];
$users     = $calc['default']['users'];

$perMonth = $plans[$planIndex]['prices'][$termKey];
$total    = $perMonth * $users * $termKey;

// Таблица цен для скрипта: тариф → срок → цена за пользователя в месяц.
$rates = array_map(
    static fn (array $plan): array => $plan['prices'],
    $plans,
);
?>
<section class="section section--tight calc" data-calc
         data-calc-rates="<?= View::e(json_encode($rates, JSON_UNESCAPED_UNICODE)) ?>">
    <div class="container">
        <div class="section-head">
            <?php if (!empty($calc['label'])): ?>
                <p class="label"><?= View::e($calc['label']) ?></p>
            <?php endif; ?>
            <h2><?= View::e($calc['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($calc['lead']) ?></p>
        </div>

        <div class="calc__grid">
            <div class="calc__fields">
                <p class="calc__field">
                    <label class="calc__label" for="calc-users">Пользователей</label>
                    <input class="calc__input" id="calc-users" name="calc-users"
                           type="number" inputmode="numeric"
                           min="1" max="500" step="1"
                           value="<?= (int) $users ?>" data-calc-users>
                </p>

                <p class="calc__field">
                    <label class="calc__label" for="calc-plan">Тариф</label>
                    <select class="calc__input" id="calc-plan" name="calc-plan" data-calc-plan>
                        <?php foreach ($plans as $i => $plan): ?>
                            <option value="<?= (int) $i ?>" <?= $i === $planIndex ? 'selected' : '' ?>>
                                <?= View::e($plan['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p class="calc__field">
                    <label class="calc__label" for="calc-term">Срок лицензии</label>
                    <select class="calc__input" id="calc-term" name="calc-term" data-calc-term>
                        <?php foreach ($terms as $months => $label): ?>
                            <option value="<?= (int) $months ?>" <?= $months === $termKey ? 'selected' : '' ?>>
                                <?= View::e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
            </div>

            <?php // aria-live — чтобы экранный диктор сообщил новую сумму,
                  // когда она пересчиталась без перезагрузки страницы. ?>
            <div class="calc__result" aria-live="polite">
                <p class="label">Ориентировочно</p>
                <p class="calc__total" data-calc-total><?= View::e(Text::money($total)) ?></p>

                <ul class="calc__breakdown">
                    <li>
                        <span>За одного пользователя в месяц</span>
                        <b data-calc-rate><?= View::e(Text::money($perMonth)) ?></b>
                    </li>
                    <li>
                        <span>Пользователей</span>
                        <b data-calc-count><?= (int) $users ?></b>
                    </li>
                    <li>
                        <span>Срок лицензии</span>
                        <b data-calc-months><?= View::e($terms[$termKey]) ?></b>
                    </li>
                </ul>
            </div>
        </div>

        <?php if (!empty($calc['note'])): ?>
            <p class="section-head__lead calc__note"><?= View::e($calc['note']) ?></p>
        <?php endif; ?>
    </div>
</section>
