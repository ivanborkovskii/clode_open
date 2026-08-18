<?php
/**
 * Комментарии к статье: форма и опубликованные записи.
 *
 * Форма устроена как форма заявки: работает без JavaScript обычным POST,
 * со скриптом отправляется без перезагрузки. Новый комментарий появляется
 * на странице после проверки в админке, о чём прямо сказано в ответе.
 *
 * @var array $article
 * @var array $comments
 * @var array $texts
 * @var array $state   Результат предыдущей отправки: status, errors, values
 */

use App\Core\Csrf;
use App\Core\Text;
use App\Core\View;

$labels  = $texts['article']['comments'];
$errors  = $state['errors'] ?? [];
$values  = $state['values'] ?? [];
$success = ($state['status'] ?? '') === 'success';

$old = static fn (string $field): string => View::e($values[$field] ?? '');
?>
<section class="section section--alt" id="kommentarii">
    <div class="container container--narrow">
        <h2 class="comments__title"><?= View::e($labels['title']) ?></h2>

        <div class="form-card comments__form">
            <?php if ($success): ?>
                <div class="form__success" data-form-success>
                    <h3><?= View::e($labels['form']) ?></h3>
                    <p><?= View::e($labels['moderation']) ?></p>
                </div>
            <?php else: ?>
                <h3><?= View::e($labels['form']) ?></h3>
                <p class="comments__lead"><?= View::e($labels['lead']) ?></p>

                <form class="form" method="post"
                      action="/stati/<?= View::e($article['slug']) ?>/kommentariy" data-form
                      data-success-title="<?= View::e($labels['form']) ?>"
                      data-success-text="<?= View::e($labels['moderation']) ?>">
                    <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">

                    <?php // Ловушка для ботов: людям поле не видно. ?>
                    <div class="honeypot" aria-hidden="true">
                        <label>Оставьте это поле пустым
                            <input type="text" name="pole_2" tabindex="-1"
                                   autocomplete="off" data-trap>
                        </label>
                    </div>

                    <p class="form__status form__status--error"
                       data-form-status role="alert"
                       <?= empty($errors['_form']) ? 'hidden' : '' ?>><?= View::e($errors['_form'] ?? '') ?></p>

                    <div class="form__grid">
                        <div class="form__row">
                            <div class="field">
                                <label class="field__label" for="c-name">
                                    <?= View::e($labels['name']) ?> <span class="field__req">*</span>
                                </label>
                                <input class="field__control" type="text" id="c-name" name="name"
                                       value="<?= $old('name') ?>" autocomplete="name" required
                                       <?= isset($errors['name']) ? 'aria-invalid="true"' : '' ?>>
                                <p class="field__error" data-error-for="name"><?= View::e($errors['name'] ?? '') ?></p>
                            </div>

                            <div class="field">
                                <label class="field__label" for="c-email"><?= View::e($labels['email']) ?></label>
                                <input class="field__control" type="email" id="c-email" name="email"
                                       value="<?= $old('email') ?>" autocomplete="email"
                                       aria-describedby="c-email-note"
                                       <?= isset($errors['email']) ? 'aria-invalid="true"' : '' ?>>
                                <p class="field__note" id="c-email-note"><?= View::e($labels['email_note']) ?></p>
                                <p class="field__error" data-error-for="email"><?= View::e($errors['email'] ?? '') ?></p>
                            </div>
                        </div>

                        <div class="field">
                            <label class="field__label" for="c-body">
                                <?= View::e($labels['body']) ?> <span class="field__req">*</span>
                            </label>
                            <textarea class="field__control" id="c-body" name="body" required
                                      <?= isset($errors['body']) ? 'aria-invalid="true"' : '' ?>><?= $old('body') ?></textarea>
                            <p class="field__error" data-error-for="body"><?= View::e($errors['body'] ?? '') ?></p>
                        </div>

                        <div class="form__foot">
                            <label class="checkbox">
                                <input type="checkbox" name="privacy" value="1" required
                                       <?= isset($values['privacy']) ? 'checked' : '' ?>>
                                <span>
                                    <?= View::e($labels['privacy']) ?> на условиях
                                    <a href="/soglasie" target="_blank" rel="noopener">согласия</a>
                                    и <a href="/privacy" target="_blank" rel="noopener">политики конфиденциальности</a>
                                </span>
                            </label>
                            <p class="field__error" data-error-for="privacy"><?= View::e($errors['privacy'] ?? '') ?></p>

                            <button class="btn btn--primary btn--block" type="submit" data-submit>
                                <span data-submit-label><?= View::e($labels['submit']) ?></span>
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <?php if ($comments === []): ?>
            <p class="comments__empty"><?= View::e($labels['empty']) ?></p>
        <?php else: ?>
            <ol class="comments">
                <?php foreach ($comments as $comment): ?>
                    <li class="comment">
                        <p class="comment__head">
                            <b class="comment__name"><?= View::e($comment['name']) ?></b>
                            <time class="comment__date"
                                  datetime="<?= View::e(substr((string) $comment['created_at'], 0, 10)) ?>">
                                <?= View::e(Text::date((string) $comment['created_at'])) ?>
                            </time>
                        </p>
                        <?php
                        // Текст комментария выводится экранированным и с переносами
                        // строк: разметку посетителей на страницу не пускаем.
                        ?>
                        <p class="comment__body"><?= nl2br(View::e($comment['body'])) ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</section>
