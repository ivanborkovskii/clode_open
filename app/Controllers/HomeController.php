<?php

declare(strict_types=1);

namespace App\Controllers;

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
        if (session_status() === PHP_SESSION_NONE) {
            session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
        }

        $flash = $_SESSION['form_flash'] ?? [];
        unset($_SESSION['form_flash']);

        return [
            'status' => $flash['status'] ?? '',
            'errors' => $flash['errors'] ?? [],
            'values' => $flash['values'] ?? [],
        ];
    }
}
