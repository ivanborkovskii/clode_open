<?php
/**
 * Микроразметка Schema.org для страницы.
 *
 * Собирается одним графом (@graph): компания, сайт, страница, хлебные
 * крошки и, если страница их передала, дополнительные узлы — статья,
 * услуга, список. Узлы ссылаются друг на друга через @id, поэтому
 * сведения о компании не повторяются в каждом блоке, а поисковик видит,
 * что страница, сайт и компания связаны.
 *
 * Здесь нет ни одного придуманного факта: всё берётся из настроек сайта
 * и из данных страницы. Того, чего мы не знаем — часов работы, цен,
 * рейтингов, — в разметке нет. Разметка, не совпадающая с содержимым
 * страницы, для поисковиков хуже, чем её отсутствие.
 */

declare(strict_types=1);

namespace App\Core;

final class Schema
{
    /**
     * @param  array<string, mixed> $config
     * @param  array<string, mixed> $seo
     * @return array<string, mixed>
     */
    public static function graph(array $config, array $seo): array
    {
        $base    = rtrim((string) $config['base_url'], '/');
        $company = $config['company'];
        $url     = (string) ($seo['canonical'] ?? $base . '/');

        $nodes = [
            self::organization($base, $company),
            self::website($base, $company),
            self::page($base, $url, $seo),
        ];

        if (!empty($seo['breadcrumbs'])) {
            $nodes[] = self::breadcrumbs($base, $url, $seo['breadcrumbs']);
        }

        // Узлы конкретной страницы: статья, услуга, список материалов.
        // Внутри графа @context не нужен — он общий, объявлен один раз выше.
        foreach ($seo['jsonld'] ?? [] as $node) {
            unset($node['@context']);
            $nodes[] = $node;
        }

        return ['@context' => 'https://schema.org', '@graph' => $nodes];
    }

    /**
     * Компания. Тип ProfessionalService — это одновременно и организация,
     * и местный бизнес: подходит услугам, которые оказывает ИП в своём городе.
     *
     * @param  array<string, mixed> $company
     * @return array<string, mixed>
     */
    private static function organization(string $base, array $company): array
    {
        $node = [
            '@type'     => 'ProfessionalService',
            '@id'       => $base . '/#organization',
            'name'      => $company['brand'],
            'legalName' => $company['legal_name'],
            'url'       => $base . '/',
            'telephone' => $company['phone'],
            'email'     => $company['email'],
            'taxID'     => $company['inn'],
            'logo' => [
                '@type'  => 'ImageObject',
                '@id'    => $base . '/#logo',
                'url'    => $base . '/assets/img/logo.png',
                'width'  => 512,
                'height' => 512,
            ],
            'image'   => ['@id' => $base . '/#logo'],
            'address' => [
                '@type'           => 'PostalAddress',
                'addressLocality' => $company['locality'],
                'streetAddress'   => $company['address'],
                'addressCountry'  => 'RU',
            ],
            'founder' => [
                '@type' => 'Person',
                'name'  => $company['name'],
            ],
        ];

        // sameAs — страницы этой же компании в других местах: соцсети,
        // карты, справочники. По ним поисковик связывает сайт и эти
        // страницы в одну компанию, а название — с ней. Это главное,
        // что работает на узнавание названия за пределами самого сайта.
        //
        // Пустого поля в разметке быть не должно: добавляем, только если
        // ссылки заданы.
        $sameAs = array_values(array_filter($company['social'] ?? []));

        if ($sameAs !== []) {
            $node['sameAs'] = $sameAs;
        }

        return $node;
    }

    /**
     * Сайт целиком.
     *
     * Здесь был объявлен поиск по статьям (SearchAction). Мы его убрали:
     * поле query-input, без которого поиск в разметке бессмыслен, в самой
     * спецификации schema.org не описано — это было расширение Google для
     * строки поиска в выдаче. Google эту строку убрал, и разметка перестала
     * на что-либо влиять, а Яндекс.Вебмастер выдаёт на неё предупреждение.
     * Пользы ноль, замечание в проверке есть — значит, лишнее.
     *
     * @param  array<string, mixed> $company
     * @return array<string, mixed>
     */
    private static function website(string $base, array $company): array
    {
        return [
            '@type'      => 'WebSite',
            '@id'        => $base . '/#website',
            'url'        => $base . '/',
            'name'       => $company['brand'],
            'inLanguage' => 'ru-RU',
            'publisher'  => ['@id' => $base . '/#organization'],
        ];
    }

    /**
     * Сама страница. Тип уточняется страницей: у контактов — ContactPage,
     * у списков — CollectionPage, у остальных обычный WebPage.
     *
     * @param  array<string, mixed> $seo
     * @return array<string, mixed>
     */
    private static function page(string $base, string $url, array $seo): array
    {
        $node = [
            '@type'      => (string) ($seo['page_type'] ?? 'WebPage'),
            '@id'        => $url . '#webpage',
            'url'        => $url,
            'name'       => (string) ($seo['title'] ?? ''),
            'inLanguage' => 'ru-RU',
            'isPartOf'   => ['@id' => $base . '/#website'],
            'about'      => ['@id' => $base . '/#organization'],
        ];

        if (($seo['description'] ?? '') !== '') {
            $node['description'] = $seo['description'];
        }

        if (($seo['og_image'] ?? '') !== '') {
            $node['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url'   => $seo['og_image'],
            ];
        }

        if (!empty($seo['breadcrumbs'])) {
            $node['breadcrumb'] = ['@id' => $url . '#breadcrumb'];
        }

        return $node;
    }

    /**
     * @param  array<int, array{label:string, href:string}> $crumbs
     * @return array<string, mixed>
     */
    private static function breadcrumbs(string $base, string $url, array $crumbs): array
    {
        return [
            '@type' => 'BreadcrumbList',
            '@id'   => $url . '#breadcrumb',
            'itemListElement' => array_map(
                static fn (int $i, array $crumb): array => [
                    '@type'    => 'ListItem',
                    'position' => $i + 1,
                    'name'     => $crumb['label'],
                    'item'     => $base . $crumb['href'],
                ],
                array_keys($crumbs),
                $crumbs,
            ),
        ];
    }

    /**
     * Список материалов для страниц-перечней: услуги, решения, кейсы,
     * статьи. Поисковик по нему видит состав раздела, не разбирая вёрстку.
     *
     * @param  array<int, array{name:string, href:string}> $items
     * @return array<string, mixed>
     */
    public static function itemList(string $base, string $url, array $items): array
    {
        return [
            '@type' => 'ItemList',
            '@id'   => $url . '#list',
            'itemListElement' => array_map(
                static fn (int $i, array $item): array => [
                    '@type'    => 'ListItem',
                    'position' => $i + 1,
                    'name'     => $item['name'],
                    'url'      => $base . $item['href'],
                ],
                array_keys($items),
                array_values($items),
            ),
        ];
    }
}
