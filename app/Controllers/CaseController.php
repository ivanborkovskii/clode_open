<?php
/**
 * Раздел «Кейсы»: общая страница и разборы проектов.
 *
 * Разборы собираются одним шаблоном pages/case.php — отличается только
 * файл с текстами. Чтобы добавить следующий кейс, нужен файл текстов
 * и строка в PAGES.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Schema;

final class CaseController extends Controller
{
    /**
     * Разработанные разборы: адрес → файл текстов и данные для поиска.
     *
     * @var array<string, array{content:string, title:string, description:string, crumb:string}>
     */
    private const PAGES = [
        'gradus-klimata' => [
            'content'     => 'case-gradus-klimata',
            'crumb'       => 'Градус-Климата',
            'title'       => 'Кейс: тендеры на холодильное оборудование в Битрикс24',
            'description' => 'Компания вела тендеры в Excel и теряла задачи. Разобрали '
                . 'процессы, собрали воронки, настроили автоматическую постановку задач '
                . 'и обучили сотрудников. Стало видно всех клиентов и их стадии.',
        ],
        'neoray' => [
            'content'     => 'case-neoray',
            'crumb'       => 'Neoray',
            'title'       => 'Кейс: amoCRM и Мой склад для торговли оборудованием',
            'description' => 'Было только Мой склад без функций CRM. Настроили восемь '
                . 'воронок в amoCRM, двустороннюю синхронизацию заказов и сделок, '
                . 'телефонию Mango и права доступа сотрудников.',
        ],
        'mid' => [
            'content'     => 'case-mid',
            'crumb'       => 'MID',
            'title'       => 'Кейс: текстильная компания в Битрикс24 с двумя сайтами',
            'description' => 'Четыре воронки через туннель продаж, роботы для повторных '
                . 'продаж, WhatsApp через Wazzup, телефония Mango и передача заявок '
                . 'с двух сайтов на Django через веб-хуки.',
        ],
        'arsenalsnab' => [
            'content'     => 'case-arsenalsnab',
            'crumb'       => 'Арсенал Снаб',
            'title'       => 'Кейс: четыре воронки в amoCRM для торговли инструментом',
            'description' => 'Была одна воронка и amoCRM без каналов связи. Разобрали '
                . 'процессы, собрали четыре воронки под разные типы клиентов, '
                . 'подключили IP-телефонию и WhatsApp внутри системы.',
        ],
        'obrazovatelnyy-centr' => [
            'content'     => 'case-obrazovatelnyy-centr',
            'crumb'       => 'Образовательный центр',
            'title'       => 'Кейс: образовательный центр в amoCRM с мессенджерами',
            'description' => 'Заявки велись непонятно где, переписки и звонки '
                . 'не учитывались. Настроили воронку, подключили заявки с Tilda, '
                . 'WhatsApp, Telegram и телефонию Новофон с записью разговоров.',
        ],
    ];

    public function index(): void
    {
        $this->html($this->view->render('cases', [
            'styles' => ['css/pages.css'],
            'seo' => [
                'title'       => 'Кейсы внедрения Битрикс24 и amoCRM',
                'description' => 'Пять проектов: холодильное оборудование, '
                    . 'электротехника, текстиль, строительный инструмент '
                    . 'и образовательный центр. Что было до внедрения, что сделали '
                    . 'и что изменилось.',
                'canonical'   => $this->url('/keysy'),
                'breadcrumbs' => [
                    ['label' => 'Главная', 'href' => '/'],
                    ['label' => 'Кейсы',   'href' => '/keysy'],
                ],
                'page_type' => 'CollectionPage',
                'jsonld'    => [Schema::itemList(
                    $this->config['base_url'],
                    $this->url('/keysy'),
                    array_map(
                        static fn (array $item): array => [
                            'name' => $item['company'],
                            'href' => $item['href'],
                        ],
                        $this->content('cases')['list']['items'],
                    ),
                )],
            ],
            'page' => $this->content('cases'),
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

        $this->html($this->view->render('case', [
            'styles' => ['css/pages.css'],
            'slug'   => $slug,
            'seo' => [
                'title'       => $meta['title'],
                'description' => $meta['description'],
                'canonical'   => $this->url('/keysy/' . $slug),
                'breadcrumbs' => [
                    ['label' => 'Главная',      'href' => '/'],
                    ['label' => 'Кейсы',        'href' => '/keysy'],
                    ['label' => $meta['crumb'], 'href' => '/keysy/' . $slug],
                ],
                // Разбор проекта. Тип CreativeWork, а не Article: у кейса
                // нет даты публикации, а Article без даты поисковики
                // помечают как неполную разметку.
                'jsonld' => [[
                    '@type' => 'CreativeWork',
                    '@id'   => $this->url('/keysy/' . $slug) . '#case',
                    'name'        => $meta['title'],
                    'description' => $meta['description'],
                    'about'       => $meta['crumb'],
                    'creator'     => ['@id' => $this->config['base_url'] . '/#organization'],
                    'inLanguage'  => 'ru-RU',
                    'url'         => $this->url('/keysy/' . $slug),
                ]],
            ],
            'page' => $this->content($meta['content']),
            'form' => $this->formFlash(),
        ]));
    }

    /** Адреса готовых разборов — для карты сайта и проверки ссылок. */
    public static function paths(): array
    {
        return array_map(
            static fn (string $slug): string => '/keysy/' . $slug,
            array_keys(self::PAGES),
        );
    }
}
