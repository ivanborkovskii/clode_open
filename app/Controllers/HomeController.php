<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->html($this->view->render('home', [
            'seo' => [
                'title'       => 'Внедрение Битрикс24 и amoCRM',
                'description' => 'Внедрение, настройка и сопровождение CRM для отделов продаж: '
                    . 'воронки, автоматизация, телефония, мессенджеры, интеграция с 1С и Мой склад.',
                'canonical'   => $this->url('/'),
            ],
            'page' => $this->content('home'),
            // Результат отправки формы, если страница открыта после POST без JavaScript.
            'form' => $this->formFlash(),
        ]));
    }

    /**
     * Забирает результат отправки формы, сохранённый в сессии.
     *
     * @return array{status:string, errors:array<string,string>, values:array<string,string>}
     */
    private function formFlash(): array
    {
        Session::start();

        $flash = $_SESSION['form_flash'] ?? [];
        unset($_SESSION['form_flash']);

        // Если сессия на хостинге не сохранилась, флеш придёт пустым.
        // Тогда результат берём из метки в адресе, которую поставил
        // контроллер заявок, — иначе человек увидит пустую форму
        // и решит, что заявка отправлена.
        $marker = $_GET['zayavka'] ?? '';
        $status = $flash['status'] ?? match ($marker) {
            'ok'    => 'success',
            'error' => 'error',
            default => '',
        };

        $errors = $flash['errors'] ?? [];

        if ($status === 'error' && $errors === []) {
            $errors['_form'] = 'Заявку отправить не удалось. Заполните поля ещё раз '
                . 'или позвоните: ' . $this->config['company']['phone'];
        }

        return [
            'status' => $status,
            'errors' => $errors,
            'values' => $flash['values'] ?? [],
        ];
    }
}
