<?php
/**
 * Калькулятор бюджета CRM.
 *
 * ОТЛИЧИЕ ОТ service-calc. Тот считает одну лицензию: цена за пользователя,
 * число людей, срок. Здесь складывается весь бюджет — лицензия, разовые
 * работы, подписки и сопровождение, — и отдельно показывается, во что
 * обойдётся следующий год, когда первоначальных работ уже не будет.
 *
 * ПОЧЕМУ ВСЁ ВВОДИТСЯ РУКАМИ. Считать стоимость настройки по числу
 * сотрудников нельзя: это главная мысль всей страницы. Поэтому суммы
 * работ человек ставит сам — из примеров выше или из полученного
 * предложения. Калькулятор складывает и делит, но ничего не выдумывает.
 *
 * РАБОТАЕТ БЕЗ СКРИПТА. Поля обычные, результат посчитан на сервере
 * по значениям по умолчанию: без JavaScript блок остаётся готовым
 * примером расчёта. Со скриптом цифры меняются на лету. Формы нет —
 * отправлять нечего, это справочный блок.
 *
 * @var array $budget label, title, lead, fields, note
 */

use App\Core\Text;
use App\Core\View;

$f = $budget['fields'];

// Расчёт по умолчанию — он же пример в разметке. Скрипт повторяет
// ровно эти четыре строки, других правил у него нет.
$perMonth = $f['model']['value'] === 'team'
    ? $f['price']['value']
    : $f['price']['value'] * $f['users']['value'];

$license = $perMonth * $f['term']['value'];
$once    = $f['setup']['value'] + $f['extra']['value'];
$monthly = ($f['services']['value'] + $f['support']['value']) * $f['term']['value'];
$first   = $license + $once + $monthly;
$next    = $license + $monthly;
?>
<section class="section section--tight calc" data-budget>
    <div class="container">
        <div class="section-head">
            <?php if (!empty($budget['label'])): ?>
                <p class="label"><?= View::e($budget['label']) ?></p>
            <?php endif; ?>
            <h2><?= View::e($budget['title']) ?></h2>
            <p class="section-head__lead"><?= View::e($budget['lead']) ?></p>
        </div>

        <div class="calc__grid">
            <div class="calc__fields calc__fields--pair">
                <p class="calc__field">
                    <label class="calc__label" for="bud-users">Сотрудников в CRM</label>
                    <input class="calc__input" id="bud-users" name="bud-users"
                           type="number" inputmode="numeric" min="1" max="500" step="1"
                           value="<?= (int) $f['users']['value'] ?>" data-budget-users>
                </p>

                <p class="calc__field">
                    <label class="calc__label" for="bud-model">Как считается лицензия</label>
                    <select class="calc__input" id="bud-model" name="bud-model" data-budget-model>
                        <option value="user" <?= $f['model']['value'] === 'user' ? 'selected' : '' ?>>За каждого пользователя</option>
                        <option value="team" <?= $f['model']['value'] === 'team' ? 'selected' : '' ?>>За команду целиком</option>
                    </select>
                </p>

                <p class="calc__field">
                    <label class="calc__label" for="bud-price">Лицензия, ₽ в месяц</label>
                    <input class="calc__input" id="bud-price" name="bud-price"
                           type="number" inputmode="numeric" min="0" max="500000" step="10"
                           value="<?= (int) $f['price']['value'] ?>" data-budget-price>
                </p>

                <p class="calc__field">
                    <label class="calc__label" for="bud-term">Период расчёта</label>
                    <select class="calc__input" id="bud-term" name="bud-term" data-budget-term>
                        <?php foreach ($f['term']['options'] as $months => $label): ?>
                            <option value="<?= (int) $months ?>" <?= $months === $f['term']['value'] ? 'selected' : '' ?>>
                                <?= View::e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p class="calc__field">
                    <label class="calc__label" for="bud-setup">Настройка, ₽ разово</label>
                    <input class="calc__input" id="bud-setup" name="bud-setup"
                           type="number" inputmode="numeric" min="0" max="5000000" step="1000"
                           value="<?= (int) $f['setup']['value'] ?>" data-budget-setup>
                </p>

                <p class="calc__field">
                    <label class="calc__label" for="bud-extra">Интеграции, ₽ разово</label>
                    <input class="calc__input" id="bud-extra" name="bud-extra"
                           type="number" inputmode="numeric" min="0" max="5000000" step="1000"
                           value="<?= (int) $f['extra']['value'] ?>" data-budget-extra>
                </p>

                <p class="calc__field">
                    <label class="calc__label" for="bud-services">Сервисы, ₽ в месяц</label>
                    <input class="calc__input" id="bud-services" name="bud-services"
                           type="number" inputmode="numeric" min="0" max="500000" step="100"
                           value="<?= (int) $f['services']['value'] ?>" data-budget-services>
                </p>

                <p class="calc__field">
                    <label class="calc__label" for="bud-support">Сопровождение, ₽ в месяц</label>
                    <input class="calc__input" id="bud-support" name="bud-support"
                           type="number" inputmode="numeric" min="0" max="500000" step="100"
                           value="<?= (int) $f['support']['value'] ?>" data-budget-support>
                </p>
            </div>

            <?php // aria-live — чтобы экранный диктор сообщил новую сумму,
                  // когда она пересчиталась без перезагрузки страницы. ?>
            <div class="calc__result" aria-live="polite">
                <p class="label">За выбранный период</p>
                <p class="calc__total" data-budget-total><?= View::e(Text::money($first)) ?></p>

                <ul class="calc__breakdown">
                    <li>
                        <span>Лицензия</span>
                        <b data-budget-license><?= View::e(Text::money($license)) ?></b>
                    </li>
                    <li>
                        <span>Разовые работы</span>
                        <b data-budget-once><?= View::e(Text::money($once)) ?></b>
                    </li>
                    <li>
                        <span>Сервисы и сопровождение</span>
                        <b data-budget-monthly><?= View::e(Text::money($monthly)) ?></b>
                    </li>
                    <li>
                        <span>Следующий период без настройки</span>
                        <b data-budget-next><?= View::e(Text::money($next)) ?></b>
                    </li>
                </ul>
            </div>
        </div>

        <?php if (!empty($budget['note'])): ?>
            <p class="section-head__lead calc__note"><?= View::e($budget['note']) ?></p>
        <?php endif; ?>
    </div>
</section>
