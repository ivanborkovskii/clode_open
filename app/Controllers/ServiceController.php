<?php
/**
 * Раздел «Услуги»: общая страница и страницы отдельных услуг.
 *
 * Страницы услуг собираются одним шаблоном pages/service.php — отличается
 * только файл с текстами. Чтобы добавить следующую услугу, нужно завести
 * файл текстов и одну строку в $pages ниже.
 */

declare(strict_types=1);

namespace App\Controllers;

final class ServiceController extends Controller
{
    /**
     * Разработанные страницы услуг: адрес → файл текстов и данные для поиска.
     *
     * @var array<string, array{content:string, title:string, description:string, crumb:string}>
     */
    private const PAGES = [
        'vnedrenie-bitrix24' => [
            'content'     => 'service-bitrix24',
            'crumb'       => 'Внедрение Битрикс24',
            'title'       => 'Внедрение Битрикс24 под процессы компании',
            'description' => 'Внедрение Битрикс24: анализ бизнес-процессов, настройка воронок '
                . 'и полей, автоматизация, подключение телефонии, сайта, мессенджеров '
                . 'и почты, интеграция с 1С и Мой склад, обучение и сопровождение.',
        ],
        'vnedrenie-amocrm' => [
            'content'     => 'service-amocrm',
            'crumb'       => 'Внедрение amoCRM',
            'title'       => 'Внедрение amoCRM для отдела продаж',
            'description' => 'Внедрение amoCRM: анализ работы отдела продаж, настройка '
                . 'воронок и этапов, автоматизация, подключение телефонии, сайта, '
                . 'WhatsApp и Telegram, синхронизация с Мой склад, обучение и сопровождение.',
        ],
        'nastroyka-i-dorabotka-crm' => [
            'content'     => 'service-dorabotka',
            'crumb'       => 'Настройка и доработка CRM',
            'title'       => 'Настройка и доработка действующей CRM',
            'description' => 'Аудит уже работающей CRM, новые воронки и направления '
                . 'продаж, корректировка автоматизаций, доработка карточек, отчётов '
                . 'и полей, подключение телефонии и мессенджеров, обучение сотрудников.',
        ],
        'integracii' => [
            'content'     => 'service-integracii',
            'crumb'       => 'Интеграции',
            'title'       => 'Интеграции CRM с телефонией, мессенджерами, 1С и сайтом',
            'description' => 'Подключаем к CRM телефонию, мессенджеры и соцсети, заявки '
                . 'с сайта и почту, 1С и Мой склад через готовые приложения, Честный знак, '
                . 'сквозную аналитику Roistat, отчёты в Google Таблицах и любые системы '
                . 'с открытым REST API.',
        ],
        'soprovozhdenie-crm' => [
            'content'     => 'service-soprovozhdenie',
            'crumb'       => 'Сопровождение CRM',
            'title'       => 'Сопровождение CRM: пакеты от 2 часов в месяц',
            'description' => 'Ежемесячное сопровождение CRM: консультации и обучение '
                . 'сотрудников, исправление ошибок, новые автоматизации, настройка отчётов '
                . 'и прав доступа, донастройка интеграций с 1С и Мой склад. '
                . 'Стоимость часа от 3 870 ₽.',
        ],
    ];

    public function index(): void
    {
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
            'page' => $this->content('services'),
            'form' => $this->formFlash(),
        ]));
    }

    public function show(string $slug): void
    {
        $meta = self::PAGES[$slug] ?? null;

        // Адрес вида /uslugi/что-угодно не должен отдавать пустую страницу
        // с кодом 200: для поиска это дубль, для посетителя — тупик.
        if ($meta === null) {
            $this->notFound();
            return;
        }

        $this->html($this->view->render('service', [
            'styles' => ['css/pages.css'],
            'seo' => [
                'title'       => $meta['title'],
                'description' => $meta['description'],
                'canonical'   => $this->url('/uslugi/' . $slug),
                'breadcrumbs' => [
                    ['label' => 'Главная',        'href' => '/'],
                    ['label' => 'Услуги',         'href' => '/uslugi'],
                    ['label' => $meta['crumb'],   'href' => '/uslugi/' . $slug],
                ],
            ],
            'page' => $this->content($meta['content']),
            'form' => $this->formFlash(),
        ]));
    }

    /** Адреса готовых страниц услуг — для карты сайта. */
    public static function paths(): array
    {
        return array_map(
            static fn (string $slug): string => '/uslugi/' . $slug,
            array_keys(self::PAGES),
        );
    }
}
