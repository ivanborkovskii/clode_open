<?php

declare(strict_types=1);

namespace App\Controllers;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->html($this->view->render('home', [
            'styles' => ['css/home.css'],
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
}
