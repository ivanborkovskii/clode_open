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
