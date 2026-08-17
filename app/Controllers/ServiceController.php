<?php
/**
 * Раздел «Услуги».
 *
 * Пока сделана только общая страница раздела. Страницы отдельных услуг
 * добавляются методом show() — по одной, отдельными задачами.
 */

declare(strict_types=1);

namespace App\Controllers;

final class ServiceController extends Controller
{
    public function index(): void
    {
        $page = $this->content('services');

        $this->html($this->view->render('services', [
            'styles' => ['css/pages.css'],
            'seo' => [
                'title'       => 'Услуги: внедрение, доработка и сопровождение CRM',
                'description' => 'Внедрение Битрикс24 и amoCRM, настройка и доработка '
                    . 'действующей CRM, интеграции с телефонией, сайтом, 1С и Мой склад, '
                    . 'ежемесячное сопровождение.',
                'canonical'   => $this->url('/uslugi'),
                'breadcrumbs' => [
                    ['label' => 'Главная', 'href' => '/'],
                    ['label' => 'Услуги',  'href' => '/uslugi'],
                ],
            ],
            'page' => $page,
            'form' => $this->formFlash(),
        ]));
    }
}
