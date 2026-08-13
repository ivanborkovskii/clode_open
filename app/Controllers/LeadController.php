<?php
/**
 * Приём заявок с форм.
 *
 * Форма работает двумя способами:
 *  - с JavaScript: отправка через fetch, ответ в JSON, страница не перезагружается;
 *  - без JavaScript: обычный POST, результат кладётся в сессию и показывается после редиректа.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Validator;

final class LeadController extends Controller
{
    public function store(): void
    {
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

        if (!Csrf::check($_POST['_token'] ?? null)) {
            $this->respond($isAjax, 'error', ['_form' => 'Сессия устарела. Обновите страницу и попробуйте ещё раз.']);
            return;
        }

        $validator = new Validator($_POST);

        // Скрытое поле заполняется только ботами: отвечаем успехом, заявку не создаём.
        if ($validator->value('company_site') !== '') {
            $this->respond($isAjax, 'success', []);
            return;
        }

        $validator
            ->required('name', 'Укажите, как к вам обращаться')
            ->length('name', 2, 100, 'Имя должно быть от 2 до 100 символов')
            ->required('phone', 'Укажите телефон для связи')
            ->phone('phone', 'Проверьте номер телефона')
            ->email('email', 'Проверьте адрес электронной почты')
            ->length('message', 0, 2000, 'Сообщение длиннее 2000 символов')
            ->required('privacy', 'Без согласия на обработку данных мы не сможем связаться');

        if (!$validator->passes()) {
            $this->respond($isAjax, 'error', $validator->errors(), $_POST);
            return;
        }

        $this->save([
            'name'    => $validator->value('name'),
            'phone'   => $validator->value('phone'),
            'email'   => $validator->value('email'),
            'message' => $validator->value('message'),
        ]);

        $this->respond($isAjax, 'success', []);
    }

    /**
     * Сохранение заявки. Сейчас — строка в лог-файле.
     *
     * TODO: подключить реальный приёмник — создание сделки в Битрикс24 / amoCRM
     * через вебхук или отправку письма. Менять нужно только этот метод.
     *
     * @param array<string, string> $data
     */
    private function save(array $data): void
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $data['time'] = date('c');

        file_put_contents(
            $dir . '/leads.log',
            json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed>  $values
     */
    private function respond(bool $isAjax, string $status, array $errors, array $values = []): void
    {
        if ($isAjax) {
            $this->json(['status' => $status, 'errors' => $errors], $status === 'success' ? 200 : 422);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
        }

        // При успехе значения не возвращаем — форма должна очиститься.
        $_SESSION['form_flash'] = [
            'status' => $status,
            'errors' => $errors,
            'values' => $status === 'success' ? [] : array_map('strval', array_diff_key($values, ['_token' => ''])),
        ];

        $this->redirect('/#zayavka', 303);
    }
}
