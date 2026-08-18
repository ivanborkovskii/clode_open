<?php
/**
 * Отдельные страницы, у которых нет своего раздела: «О компании» и «Контакты».
 */

declare(strict_types=1);

namespace App\Controllers;

final class PageController extends Controller
{
    public function about(): void
    {
        // Страница переиспользует блоки главной — «Опыт в цифрах», «Кто ведёт
        // проекты» и «Как строится работа», — поэтому к стилям разделов
        // добавляются стили главной. Заводить их копию было бы хуже.
        $this->html($this->view->render('about', [
            'styles' => ['css/home.css', 'css/pages.css'],
            'seo' => [
                'title'       => 'О компании: кто внедряет CRM и как строится работа',
                'description' => 'Внедрением занимается владелец компании лично: '
                    . '13 лет собственного бизнеса, 3 года на внедрении CRM, более '
                    . '70 проектов. Партнёрские статусы Битрикс24, amoCRM и Wazzup.',
                'canonical'   => $this->url('/o-kompanii'),
                'breadcrumbs' => [
                    ['label' => 'Главная',    'href' => '/'],
                    ['label' => 'О компании', 'href' => '/o-kompanii'],
                ],
            ],
            'page' => $this->content('about'),
            'form' => $this->formFlash(),
        ]));
    }

    public function contacts(): void
    {
        $this->html($this->view->render('contacts', [
            'styles' => ['css/pages.css'],
            'seo' => [
                'title'       => 'Контакты: телефон, почта и реквизиты',
                'description' => 'Телефон +7 (915) 179-68-61, почта info@iborkovsky.ru, '
                    . 'Иваново. Реквизиты ИП Борковский Иван Даниялович, ОГРНИП и ИНН. '
                    . 'Форма для заявки на разбор задачи.',
                'canonical'   => $this->url('/kontakty'),
                'breadcrumbs' => [
                    ['label' => 'Главная',  'href' => '/'],
                    ['label' => 'Контакты', 'href' => '/kontakty'],
                ],
            ],
            'page' => $this->content('contacts'),
            'form' => $this->formFlash(),
        ]));
    }
}
