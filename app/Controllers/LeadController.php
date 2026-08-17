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
use App\Core\Session;
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

        // Скрытое поле должно оставаться пустым — его заполняют боты.
        //
        // Заявку по этому признаку не выбрасываем, а только помечаем.
        // Раньше сервер молча отвечал «успешно» и не сохранял ничего:
        // одно ложное срабатывание — и заявка живого человека исчезала
        // бесследно, причём он видел на экране подтверждение отправки.
        // Спам разобрать можно, потерянного клиента — нет.
        $suspicious = $validator->value('pole_2') !== '';

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

        $saved = $this->save([
            'name'    => $validator->value('name'),
            'phone'   => $validator->value('phone'),
            'email'   => $validator->value('email'),
            'message' => $validator->value('message'),
        ], $suspicious);

        // Ни в файл, ни письмом заявка не ушла — сказать «принято» нельзя,
        // иначе человек будет ждать звонка по заявке, которой нигде нет.
        if (!$saved) {
            $phone = $this->config['company']['phone'];

            $this->respond($isAjax, 'error', [
                '_form' => 'Не удалось отправить заявку из-за ошибки на сервере. '
                    . 'Позвоните, пожалуйста: ' . $phone,
            ], $_POST);

            return;
        }

        $this->respond($isAjax, 'success', []);
    }

    /**
     * Сохранение заявки: запись в лог плюс письмо на почту.
     *
     * Возвращает true, если сработал хотя бы один способ. Если не сработал
     * ни один, заявку показывать как принятую нельзя — она нигде не осталась.
     * Для создания сделки сразу в Битрикс24 или amoCRM сюда добавляется
     * вызов вебхука, остальной код не меняется.
     *
     * @param array<string, string> $data
     */
    private function save(array $data, bool $suspicious = false): bool
    {
        $logged = $this->log($data, $suspicious);
        $mailed = $this->mail($data, $suspicious);

        return $logged || $mailed;
    }

    /**
     * Пишет заявку в файл. Возвращает false, если записать не удалось —
     * чаще всего это отсутствие прав на запись в storage/logs на хостинге.
     *
     * @param array<string, string> $data
     */
    private function log(array $data, bool $suspicious = false): bool
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $entry = $data + ['time' => date('c')];

        if ($suspicious) {
            $entry['spam'] = true;
        }

        $written = @file_put_contents(
            $dir . '/leads.log',
            json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );

        return $written !== false;
    }

    /** @param array<string, string> $data */
    private function mail(array $data, bool $suspicious = false): bool
    {
        $to      = $this->config['leads']['mail_to'];
        $from    = $this->config['leads']['mail_from'];
        // Пометка в теме, а не отказ от письма: если сработало ложно,
        // заявка всё равно дойдёт, просто будет видно, что к ней есть вопросы.
        $subject = ($suspicious ? 'Возможно спам. ' : '')
            . 'Заявка с сайта: ' . $data['name'];

        $body = "Имя: {$data['name']}\n"
            . "Телефон: {$data['phone']}\n"
            . 'Почта: ' . ($data['email'] !== '' ? $data['email'] : '—') . "\n"
            . 'Задача: ' . ($data['message'] !== '' ? $data['message'] : '—') . "\n\n"
            . 'Отправлено: ' . date('d.m.Y H:i');

        // Тема письма кириллицей требует MIME-кодирования, иначе почтовые
        // клиенты покажут вместо неё набор символов.
        $headers = [
            'From: =?UTF-8?B?' . base64_encode('Сайт crm.ivanborkovsky.ru') . "?= <{$from}>",
            'Content-Type: text/plain; charset=UTF-8',
            'MIME-Version: 1.0',
        ];

        if ($data['email'] !== '') {
            $headers[] = 'Reply-To: ' . $data['email'];
        }

        return @mail(
            $to,
            '=?UTF-8?B?' . base64_encode($subject) . '?=',
            $body,
            implode("\r\n", $headers),
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

        Session::start();

        // При успехе значения не возвращаем — форма должна очиститься.
        $_SESSION['form_flash'] = [
            'status' => $status,
            'errors' => $errors,
            'values' => $status === 'success' ? [] : array_map('strval', array_diff_key($values, ['_token' => ''])),
        ];

        // Метка в адресе дублирует результат на случай, если сессия на хостинге
        // не сохраняется. Без неё страница после отправки просто перезагрузилась
        // бы с пустой формой, и человек решил бы, что заявка ушла.
        $this->redirect('/?zayavka=' . ($status === 'success' ? 'ok' : 'error') . '#zayavka', 303);
    }
}
