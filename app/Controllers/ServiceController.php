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

use App\Core\Schema;

final class ServiceController extends Controller
{
    /**
     * Разработанные страницы услуг: адрес → файл текстов и данные для поиска.
     *
     * @var array<string, array{content:string, title:string, description:string, crumb:string}>
     */
    /**
     * Страницы услуг.
     *
     * Это единственный список услуг на сайте: из него строится и раздел
     * «Услуги», и выпадающее меню в шапке, и меню на телефоне. Второго
     * места, где они перечислены, нет намеренно — иначе однажды страница
     * появится, а в меню её не будет.
     *
     * Ключи записи:
     *   content     — файл с текстом страницы;
     *   crumb       — короткое имя: хлебные крошки и меню;
     *   menu        — ещё короче, только для меню, если crumb длинноват.
     *                 Необязательный: без него в меню идёт crumb;
     *   group       — колонка выпадающей панели: bitrix24, amocrm
     *                 или obshchie. Услуги без группы попадают в общие;
     *   title, description — то, что видно в поисковой выдаче.
     */
    private const PAGES = [
        'vnedrenie-bitrix24' => [
            'content'     => 'service-bitrix24',
            'crumb'       => 'Внедрение Битрикс24',
            'menu'        => 'Внедрение',
            'group'       => 'bitrix24',
            'title'       => 'Внедрение Битрикс24 под процессы компании',
            'description' => 'Внедрение Битрикс24: анализ бизнес-процессов, настройка воронок '
                . 'и полей, автоматизация, подключение телефонии, сайта, мессенджеров '
                . 'и почты, интеграция с 1С и Мой склад, обучение и сопровождение.',
        ],
        'vnedrenie-amocrm' => [
            'content'     => 'service-amocrm',
            'crumb'       => 'Внедрение amoCRM',
            'menu'        => 'Внедрение',
            'group'       => 'amocrm',
            'title'       => 'Внедрение amoCRM для отдела продаж',
            'description' => 'Внедрение amoCRM: анализ работы отдела продаж, настройка '
                . 'воронок и этапов, автоматизация, подключение телефонии, сайта, '
                . 'WhatsApp и Telegram, синхронизация с Мой склад, обучение и сопровождение.',
        ],
        'nastroyka-i-dorabotka-crm' => [
            'content'     => 'service-dorabotka',
            'crumb'       => 'Настройка и доработка CRM',
            'group'       => 'obshchie',
            'title'       => 'Настройка и доработка действующей CRM',
            'description' => 'Аудит уже работающей CRM, новые воронки и направления '
                . 'продаж, корректировка автоматизаций, доработка карточек, отчётов '
                . 'и полей, подключение телефонии и мессенджеров, обучение сотрудников.',
        ],
        'integracii' => [
            'content'     => 'service-integracii',
            'crumb'       => 'Интеграции',
            'group'       => 'obshchie',
            'title'       => 'Интеграции CRM с телефонией, мессенджерами, 1С и сайтом',
            'description' => 'Подключаем к CRM телефонию, мессенджеры и соцсети, заявки '
                . 'с сайта и почту, 1С и Мой склад через готовые приложения, Честный знак, '
                . 'сквозную аналитику Roistat, отчёты в Google Таблицах и любые системы '
                . 'с открытым REST API.',
        ],
        'soprovozhdenie-crm' => [
            'content'     => 'service-soprovozhdenie',
            'crumb'       => 'Сопровождение CRM',
            'group'       => 'obshchie',
            'title'       => 'Сопровождение CRM: пакеты от 2 часов в месяц',
            'description' => 'Ежемесячное сопровождение CRM: консультации и обучение '
                . 'сотрудников, исправление ошибок, новые автоматизации, настройка отчётов '
                . 'и прав доступа, донастройка интеграций с 1С и Мой склад. '
                . 'Стоимость часа от 3 870 ₽.',
        ],
    ];

    /**
     * Названия колонок выпадающей панели. Порядок здесь — порядок колонок.
     */
    private const GROUPS = [
        'bitrix24' => 'Битрикс24',
        'amocrm'   => 'amoCRM',
        'obshchie' => 'Независимо от системы',
    ];

    /**
     * Услуги для меню, разложенные по колонкам.
     *
     * Шапке нужен список услуг, но лезть в PAGES напрямую она не должна:
     * там служебные поля и тексты для поисковой выдачи. Поэтому наружу
     * отдаётся только то, что нужно меню.
     *
     * У пункта два имени, и это не прихоть. Под заголовком колонки
     * «Битрикс24» достаточно слова «Внедрение» — система и так названа
     * сверху. А в общем списке без заголовков то же слово превращается
     * в загадку: внедрение чего? Поэтому рядом лежит и полное имя,
     * и вид меню выбирает подходящее.
     *
     * Пустые колонки не возвращаются: пока услуг по amoCRM одна,
     * колонка из одного пункта не появится.
     *
     * @return array<string, array{title: string, items: list<array{label: string, full: string, href: string}>}>
     */
    public static function menu(): array
    {
        $byGroup = [];

        foreach (self::PAGES as $slug => $page) {
            $byGroup[$page['group'] ?? 'obshchie'][] = [
                'label' => $page['menu'] ?? $page['crumb'],
                'full'  => $page['crumb'],
                'href'  => '/uslugi/' . $slug,
            ];
        }

        $menu = [];

        foreach (self::GROUPS as $key => $title) {
            if (!empty($byGroup[$key])) {
                $menu[$key] = ['title' => $title, 'items' => $byGroup[$key]];
            }
        }

        return $menu;
    }

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
                // Состав раздела: какие услуги в нём и по каким адресам.
                'page_type' => 'CollectionPage',
                'jsonld'    => [Schema::itemList(
                    $this->config['base_url'],
                    $this->url('/uslugi'),
                    array_map(
                        static fn (array $item): array => [
                            'name' => $item['title'],
                            'href' => $item['href'],
                        ],
                        $this->content('services')['items'],
                    ),
                )],
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
                // Услуга как услуга, а не просто страница: поисковик видит,
                // что именно оказывается и кем.
                'jsonld' => [[
                    '@type' => 'Service',
                    '@id'   => $this->url('/uslugi/' . $slug) . '#service',
                    'name'        => $meta['crumb'],
                    'description' => $meta['description'],
                    'serviceType' => $meta['crumb'],
                    'provider'    => ['@id' => $this->config['base_url'] . '/#organization'],
                    'url'         => $this->url('/uslugi/' . $slug),
                ]],
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
