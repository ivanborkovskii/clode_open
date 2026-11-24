<?php
/**
 * Раздел «Тарифы и лицензии»: три страницы под три разных вопроса.
 *
 *   /tarify/bitriks24                  — какой Битрикс24 купить;
 *   /tarify/tarify-amocrm              — какой тариф amoCRM купить;
 *   /tarify/prodlenie-licenzii-bitrix24 — сколько стоит продлить купленный.
 *
 * Публиковать тарифы требует сам Битрикс24 от партнёров, поэтому раздел
 * заведён отдельными адресами, а не абзацами внутри услуг: их должно быть
 * видно в меню и находить поиском.
 *
 * ПОЧЕМУ АДРЕСА РАЗНОЙ ФОРМЫ. /tarify/bitriks24 придуман для нового сайта,
 * а /tarify/tarify-amocrm достался от старого и уже проиндексирован —
 * менять его ради красоты значит потерять накопленный вес. Адрес
 * продления выбран в этом же разделе: страница новая и нигде
 * не проиндексирована, так что ограничений у неё нет.
 *
 * Три страницы связаны вкладками (partials/tarif-tabs). Содержимое при
 * этом не смешивается: один раздел сайта — три самостоятельных интента.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Schema;

final class TariffController extends Controller
{
    /** Адреса страниц раздела. Нужны и здесь, и в картах сайта, и во вкладках. */
    public const BITRIX    = '/tarify/bitriks24';
    public const AMOCRM    = '/tarify/tarify-amocrm';
    public const PRODLENIE = '/tarify/prodlenie-licenzii-bitrix24';
    public const STOIMOST  = '/tarify/stoimost-crm';

    /**
     * Вкладки раздела. Текущая отмечена active и выводится не ссылкой.
     *
     * Список один на все три страницы: добавится четвёртая — правится
     * только здесь.
     *
     * @return list<array{label: string, href: string, active: bool}>
     */
    public static function tabs(string $current): array
    {
        $tabs = [
            ['label' => 'Стоимость CRM',       'href' => self::STOIMOST],
            ['label' => 'Тарифы Битрикс24',    'href' => self::BITRIX],
            ['label' => 'Тарифы amoCRM',       'href' => self::AMOCRM],
            ['label' => 'Продление Битрикс24', 'href' => self::PRODLENIE],
        ];

        foreach ($tabs as &$tab) {
            $tab['active'] = $tab['href'] === $current;
        }

        return $tabs;
    }

    /** Адреса раздела — для карты сайта и для меню. */
    public static function paths(): array
    {
        return [self::STOIMOST, self::BITRIX, self::AMOCRM, self::PRODLENIE];
    }

    /**
     * Общая стоимость CRM — четвёртый интент раздела.
     *
     * Соседние три страницы отвечают «сколько стоит лицензия вот этого
     * продукта». Эта — «во что обойдётся CRM целиком»: лицензия, работы,
     * сервисы и сопровождение, без привязки к одной системе. Поэтому
     * она первой вкладкой: с неё удобно уходить к конкретным тарифам,
     * а не наоборот.
     */
    public function stoimost(): void
    {
        $page = $this->content('stoimost-crm');

        $jsonld = [[
            '@type'       => 'Service',
            '@id'         => $this->url(self::STOIMOST) . '#service',
            'name'        => 'Расчёт стоимости CRM-системы',
            'description' => 'Расчёт бюджета CRM: лицензия, первоначальная '
                . 'настройка, интеграции и сопровождение.',
            'serviceType' => 'Расчёт стоимости CRM',
            'provider'    => ['@id' => $this->config['base_url'] . '/#organization'],
            'url'         => $this->url(self::STOIMOST),
        ]];

        if (!empty($page['faq']['items'])) {
            $jsonld[] = Schema::faq($this->url(self::STOIMOST), $page['faq']['items']);
        }

        $this->html($this->view->render('service', [
            'styles' => ['css/pages.css'],
            'tabs'   => self::tabs(self::STOIMOST),
            'seo' => [
                'title'       => 'Стоимость CRM-системы — сколько стоит CRM в месяц и год',
                'description' => 'Сколько стоит CRM для бизнеса: лицензия от 700 ₽ '
                    . 'в месяц, настройка, интеграции и сопровождение. Примеры '
                    . 'бюджета на 1, 5, 10 и 20 сотрудников и калькулятор.',
                'canonical'   => $this->url(self::STOIMOST),
                'breadcrumbs' => [
                    ['label' => 'Главная',        'href' => '/'],
                    ['label' => 'Стоимость CRM',  'href' => self::STOIMOST],
                ],
                'jsonld' => $jsonld,
            ],
            'page' => $page,
            'form' => $this->formFlash(),
        ]));
    }

    public function bitrix(): void
    {
        $page = $this->content('tarify-bitrix24');

        $this->html($this->view->render('tarify', [
            'styles' => ['css/pages.css'],
            'tabs'   => self::tabs(self::BITRIX),
            'seo' => [
                'title'       => 'Тарифы Битрикс24: цены на лицензии в облаке',
                'description' => 'Стоимость лицензий Битрикс24: Бесплатный, Базовый '
                    . '2 490 ₽, Стандартный 6 990 ₽ и Профессиональный 13 990 ₽ в месяц. '
                    . 'Что входит в каждый тариф и сколько пользователей. '
                    . 'Лицензия и работы по внедрению оплачиваются отдельно.',
                'canonical'   => $this->url(self::BITRIX),
                'breadcrumbs' => [
                    ['label' => 'Главная',          'href' => '/'],
                    ['label' => 'Тарифы Битрикс24', 'href' => self::BITRIX],
                ],
                'jsonld' => [Schema::products(
                    $this->url(self::BITRIX),
                    'Битрикс24',
                    $page['plans']['items'],
                    $this->url('/assets/img/hero/b24-sdelki.webp'),
                )],
            ],
            'page' => $page,
            'form' => $this->formFlash(),
        ]));
    }

    /**
     * Тарифы amoCRM.
     *
     * Страница собирается тем же шаблоном, что и услуги: набор секций
     * у неё тот же — карточки, таблицы, вопросы, форма. Своего шаблона
     * ради одной страницы заводить незачем.
     */
    public function amocrm(): void
    {
        $page = $this->content('tarify-amocrm');

        $jsonld = [Schema::products(
            $this->url(self::AMOCRM),
            'amoCRM',
            $page['plans'],
            $this->url('/assets/img/hero/amo-voronka.webp'),
        )];

        if (!empty($page['faq']['items'])) {
            $jsonld[] = Schema::faq($this->url(self::AMOCRM), $page['faq']['items']);
        }

        $this->html($this->view->render('service', [
            'styles' => ['css/pages.css'],
            'tabs'   => self::tabs(self::AMOCRM),
            'seo' => [
                'title'       => 'Тарифы amoCRM — цены и стоимость лицензии в 2026 году',
                'description' => 'Актуальные тарифы amoCRM на 03.10.2026: Базовый, '
                    . 'Расширенный и Про. Цены от 599 ₽ за пользователя, сравнение '
                    . 'функций и лимитов, расчёт стоимости лицензии для команды.',
                'canonical'   => $this->url(self::AMOCRM),
                'breadcrumbs' => [
                    ['label' => 'Главная',       'href' => '/'],
                    ['label' => 'Тарифы amoCRM', 'href' => self::AMOCRM],
                ],
                'jsonld' => $jsonld,
            ],
            'page' => $page,
            'form' => $this->formFlash(),
        ]));
    }

    /** Продление лицензии Битрикс24 — третий интент раздела. */
    public function prodlenie(): void
    {
        $page = $this->content('service-prodlenie-bitrix24');

        $jsonld = [[
            '@type'       => 'Service',
            '@id'         => $this->url(self::PRODLENIE) . '#service',
            'name'        => 'Продление лицензии Битрикс24',
            'description' => 'Продление облачного тарифа и коробочной лицензии Битрикс24.',
            'serviceType' => 'Продление лицензии Битрикс24',
            'provider'    => ['@id' => $this->config['base_url'] . '/#organization'],
            'url'         => $this->url(self::PRODLENIE),
        ]];

        if (!empty($page['faq']['items'])) {
            $jsonld[] = Schema::faq($this->url(self::PRODLENIE), $page['faq']['items']);
        }

        $this->html($this->view->render('service', [
            'styles' => ['css/pages.css'],
            'tabs'   => self::tabs(self::PRODLENIE),
            'seo' => [
                'title'       => 'Продление лицензии Битрикс24 — цены облака и коробки',
                'description' => 'Продление лицензии Битрикс24: цены облачных '
                    . 'тарифов и коробочной версии, актуальные на 03.10.2026. '
                    . 'Проверим текущую стоимость, срок лицензии и возможность '
                    . 'льготного продления.',
                'canonical'   => $this->url(self::PRODLENIE),
                'breadcrumbs' => [
                    ['label' => 'Главная',                     'href' => '/'],
                    ['label' => 'Продление лицензии Битрикс24', 'href' => self::PRODLENIE],
                ],
                'jsonld' => $jsonld,
            ],
            'page' => $page,
            'form' => $this->formFlash(),
        ]));
    }
}
