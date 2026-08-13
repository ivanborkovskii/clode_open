<?php
/**
 * Форма заявки и прямые контакты.
 *
 * Форма работает и без JavaScript: обычный POST на /zayavka,
 * результат возвращается через сессию. Со скриптом — отправка без перезагрузки.
 *
 * @var array $form   Тексты формы
 * @var array $state  Результат предыдущей отправки: status, errors, values
 * @var array $config
 */

use App\Core\Csrf;
use App\Core\View;

$company = $config['company'];
$errors  = $state['errors'] ?? [];
$values  = $state['values'] ?? [];
$success = ($state['status'] ?? '') === 'success';

/** Возвращает ранее введённое значение — чтобы при ошибке не заполнять форму заново. */
$old = static fn (string $field): string => View::e($values[$field] ?? '');
?>
<section class="section contact" id="zayavka">
    <div class="container">
        <div class="contact__layout">
            <div>
                <p class="label">Заявка</p>
                <h2><?= View::e($form['title']) ?></h2>
                <p class="contact__lead"><?= View::e($form['lead']) ?></p>

                <div class="contact__direct">
                    <p class="contact__direct-title">Можно связаться напрямую</p>
                    <a class="contact__phone" href="tel:<?= View::e($company['phone_href']) ?>">
                        <?= View::e($company['phone']) ?>
                    </a>
                    <a class="contact__mail" href="mailto:<?= View::e($company['email']) ?>">
                        <?= View::e($company['email']) ?>
                    </a>
                    <address class="contact__address" style="font-style: normal;">
                        <?= View::e($company['address']) ?>
                    </address>
                </div>
            </div>

            <div class="form-card">
                <?php if ($success): ?>
                    <div class="form__success" data-form-success>
                        <h3><?= View::e($form['success']['title']) ?></h3>
                        <p><?= View::e($form['success']['text']) ?></p>
                    </div>
                <?php else: ?>
                    <h3>Оставить заявку</h3>

                    <form class="form" action="/zayavka" method="post" novalidate data-form>
                        <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">

                        <?php // Ловушка для ботов: людям поле не видно. ?>
                        <div class="honeypot" aria-hidden="true">
                            <label>Сайт компании
                                <input type="text" name="company_site" tabindex="-1" autocomplete="off">
                            </label>
                        </div>

                        <?php if (!empty($errors['_form'])): ?>
                            <p class="form__status form__status--error"><?= View::e($errors['_form']) ?></p>
                        <?php endif; ?>

                        <div class="form__grid">
                            <div class="field">
                                <label class="field__label" for="f-name">
                                    Имя <span class="field__req">*</span>
                                </label>
                                <input class="field__control" type="text" id="f-name" name="name"
                                       value="<?= $old('name') ?>"
                                       autocomplete="name" required
                                       <?= isset($errors['name']) ? 'aria-invalid="true"' : '' ?>>
                                <p class="field__error" data-error-for="name"><?= View::e($errors['name'] ?? '') ?></p>
                            </div>

                            <div class="form__row">
                                <div class="field">
                                    <label class="field__label" for="f-phone">
                                        Телефон <span class="field__req">*</span>
                                    </label>
                                    <input class="field__control" type="tel" id="f-phone" name="phone"
                                           value="<?= $old('phone') ?>"
                                           autocomplete="tel" required
                                           <?= isset($errors['phone']) ? 'aria-invalid="true"' : '' ?>>
                                    <p class="field__error" data-error-for="phone"><?= View::e($errors['phone'] ?? '') ?></p>
                                </div>

                                <div class="field">
                                    <label class="field__label" for="f-email">Почта</label>
                                    <input class="field__control" type="email" id="f-email" name="email"
                                           value="<?= $old('email') ?>"
                                           autocomplete="email"
                                           <?= isset($errors['email']) ? 'aria-invalid="true"' : '' ?>>
                                    <p class="field__error" data-error-for="email"><?= View::e($errors['email'] ?? '') ?></p>
                                </div>
                            </div>

                            <div class="field">
                                <label class="field__label" for="f-message">Задача</label>
                                <textarea class="field__control" id="f-message" name="message"
                                          placeholder="Что сейчас не устраивает в работе с клиентами"
                                          <?= isset($errors['message']) ? 'aria-invalid="true"' : '' ?>><?= $old('message') ?></textarea>
                                <p class="field__error" data-error-for="message"><?= View::e($errors['message'] ?? '') ?></p>
                            </div>

                            <div class="form__foot">
                                <label class="checkbox">
                                    <input type="checkbox" name="privacy" value="1" required
                                           <?= isset($values['privacy']) ? 'checked' : '' ?>>
                                    <span>
                                        <?= View::e($form['privacy']) ?>
                                        и с <a href="<?= View::e($form['privacy_href']) ?>">политикой конфиденциальности</a>
                                    </span>
                                </label>
                                <p class="field__error" data-error-for="privacy"><?= View::e($errors['privacy'] ?? '') ?></p>

                                <button class="btn btn--primary btn--block" type="submit" data-submit>
                                    <span data-submit-label><?= View::e($form['submit']) ?></span>
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
