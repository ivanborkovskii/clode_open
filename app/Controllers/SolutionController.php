<?php
/**
 * Раздел «Решения»: общая страница и страницы отдельных задач бизнеса.
 *
 * Страницы задач собираются тем же шаблоном pages/service.php, что и услуги:
 * набор блоков у них одинаковый, отличается только файл с текстами.
 * Чтобы добавить следующую задачу, нужен файл текстов и строка в PAGES.
 */

declare(strict_types=1);

namespace App\Controllers;

final class SolutionController extends Controller
{
    /**
     * Разработанные страницы решений: адрес → файл текстов и данные для поиска.
     *
     * @var array<string, array{content:string, title:string, description:string, crumb:string}>
     */
    private const PAGES = [
        'prodazhi' => [
            'content'     => 'solution-prodazhi',
            'crumb'       => 'Продажи',
            'title'       => 'CRM для продаж: распределение заявок и контроль сделок',
            'description' => 'Что CRM даёт отделу продаж: автоматическое распределение '
                . 'заявок, контроль просроченных и забытых сделок, автозадачи, возврат '
                . 'неактивных клиентов, автоматизация повторных продаж и контроль плана.',
        ],
        'kommunikacii' => [
            'content'     => 'solution-kommunikacii',
            'crumb'       => 'Коммуникации',
            'title'       => 'CRM и каналы связи: звонки, мессенджеры, заявки',
            'description' => 'Телефония с записями разговоров, WhatsApp и Telegram, '
                . 'заявки с сайта и соцсетей, автоматические сообщения — вся история '
                . 'общения с клиентом собирается в карточке сделки.',
        ],
        'analitika' => [
            'content'     => 'solution-analitika',
            'crumb'       => 'Аналитика',
            'title'       => 'Аналитика в CRM: конверсии, источники заявок, план-факт',
            'description' => 'Конверсии воронок, лучшие товары, причины отказов, '
                . 'эффективность менеджеров, источники заявок и рекламы, план-факт '
                . 'по выручке и количество потерянных заявок.',
        ],
        'upravlenie-sotrudnikami' => [
            'content'     => 'solution-sotrudniki',
            'crumb'       => 'Управление сотрудниками',
            'title'       => 'Управление сотрудниками в CRM: задачи, сроки, загрузка',
            'description' => 'Постановка и контроль задач, автоматическое распределение '
                . 'работы, контроль сроков и просрочек, учёт рабочего времени '
                . 'и обмен файлами между сотрудниками.',
        ],
    ];

    public function index(): void
    {
        $this->html($this->view->render('solutions', [
            'styles' => ['css/pages.css'],
            'seo' => [
                'title'       => 'Решения: какие задачи бизнеса закрывает CRM',
                'description' => 'Продажи, коммуникации, аналитика и управление '
                    . 'сотрудниками: что настраивается в CRM под каждую задачу, '
                    . 'и какие готовые модули для этого есть.',
                'canonical'   => $this->url('/resheniya'),
                'breadcrumbs' => [
                    ['label' => 'Главная',  'href' => '/'],
                    ['label' => 'Решения',  'href' => '/resheniya'],
                ],
            ],
            'page' => $this->content('solutions'),
            'form' => $this->formFlash(),
        ]));
    }

    public function show(string $slug): void
    {
        $meta = self::PAGES[$slug] ?? null;

        if ($meta === null) {
            $this->notFound();
            return;
        }

        $this->html($this->view->render('service', [
            'styles' => ['css/pages.css'],
            'seo' => [
                'title'       => $meta['title'],
                'description' => $meta['description'],
                'canonical'   => $this->url('/resheniya/' . $slug),
                'breadcrumbs' => [
                    ['label' => 'Главная',      'href' => '/'],
                    ['label' => 'Решения',      'href' => '/resheniya'],
                    ['label' => $meta['crumb'], 'href' => '/resheniya/' . $slug],
                ],
            ],
            'page' => $this->content($meta['content']),
            'form' => $this->formFlash(),
        ]));
    }

    /** Адреса готовых страниц решений — для карты сайта и меню. */
    public static function paths(): array
    {
        return array_map(
            static fn (string $slug): string => '/resheniya/' . $slug,
            array_keys(self::PAGES),
        );
    }
}
