<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ArticleRepository;
use PDOException;

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
            'page'     => $this->content('home'),
            'articles' => $this->latestArticles(),
            // Результат отправки формы, если страница открыта после POST без JavaScript.
            'form' => $this->formFlash(),
        ]));
    }

    /**
     * Три свежие статьи для блока на главной.
     *
     * Единственное место, где главная обращается к базе. Если базы нет
     * или она недоступна, блок показывает состояние «пусто», а страница
     * открывается как обычно — из-за раздела статей главная падать
     * не должна.
     *
     * @return array<int, array<string, mixed>>
     */
    private function latestArticles(): array
    {
        try {
            return (new ArticleRepository($this->db()))->paginate([], 1, 3)['items'];
        } catch (PDOException) {
            return [];
        }
    }
}
