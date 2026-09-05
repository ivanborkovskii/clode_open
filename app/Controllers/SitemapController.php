<?php
/**
 * Карта сайта.
 *
 * Сразу сделана как индекс из нескольких файлов: при тысячах SEO-статей
 * один файл не подойдёт (лимит поисковых систем — 50 000 URL на файл).
 * Сейчас в индексе два файла: постоянные страницы и статьи.
 *
 * Про даты изменения. Раньше у всех постоянных страниц стояла сегодняшняя
 * дата: сайт каждый день сообщал поисковику, что изменилось всё. Такому
 * полю перестают верить целиком — вместе с честными датами статей.
 * Поэтому дата теперь либо настоящая, либо не выводится вовсе:
 * по спецификации поле необязательное, и его отсутствие лучше вранья.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ArticleRepository;
use App\Models\TaxonomyRepository;
use PDOException;

final class SitemapController extends Controller
{
    /**
     * Какой страницей какой файл текстов управляет.
     *
     * Дата изменения страницы берётся из времени этого файла: тексты
     * страниц лежат в config/content, и при выкладке обновления файл
     * заменяется — значит его время и есть время, когда страница
     * поменялась.
     *
     * Страница без файла в этом списке уйдёт в карту без даты.
     */
    private const CONTENT = [
        '/'                                => 'home',
        '/uslugi'                          => 'services',
        '/uslugi/vnedrenie-bitrix24'       => 'service-bitrix24',
        '/uslugi/vnedrenie-amocrm'         => 'service-amocrm',
        '/uslugi/nastroyka-i-dorabotka-crm' => 'service-dorabotka',
        '/uslugi/integracii'               => 'service-integracii',
        '/uslugi/soprovozhdenie-crm'       => 'service-soprovozhdenie',
        '/resheniya'                       => 'solutions',
        '/resheniya/prodazhi'              => 'solution-prodazhi',
        '/resheniya/kommunikacii'          => 'solution-kommunikacii',
        '/resheniya/analitika'             => 'solution-analitika',
        '/resheniya/upravlenie-sotrudnikami' => 'solution-sotrudniki',
        '/keysy'                           => 'cases',
        '/keysy/gradus-klimata'            => 'case-gradus-klimata',
        '/keysy/neoray'                    => 'case-neoray',
        '/keysy/mid'                       => 'case-mid',
        '/keysy/arsenalsnab'               => 'case-arsenalsnab',
        '/keysy/obrazovatelnyy-centr'      => 'case-obrazovatelnyy-centr',
        '/stati'                           => 'articles',
        '/o-kompanii'                      => 'about',
        '/kontakty'                        => 'contacts',
        '/privacy'                         => 'legal',
        '/soglasie'                        => 'legal',
        TariffController::BITRIX           => 'tarify-bitrix24',
    ];

    /** Индекс карт: /sitemap.xml */
    public function index(): void
    {
        $body = '';

        // Индекс карт должен открываться и при недоступной базе: без него
        // поисковик не найдёт и карту постоянных страниц, которой база
        // не нужна. Поэтому у статей в этом случае просто не будет даты.
        try {
            $articles = $this->articleUrls();
        } catch (PDOException) {
            $articles = [];
        }

        foreach (['sitemap-pages.xml' => $this->pageUrls(),
                  'sitemap-articles.xml' => $articles] as $file => $urls) {
            // У файла карты дата — самая свежая из тех, что внутри него.
            $dates = array_filter(array_column($urls, 'lastmod'));

            $body .= "  <sitemap><loc>{$this->config['base_url']}/{$file}</loc>"
                . ($dates !== [] ? '<lastmod>' . max($dates) . '</lastmod>' : '')
                . "</sitemap>\n";
        }

        $this->xml('sitemapindex', $body);
    }

    /** Постоянные страницы: /sitemap-pages.xml */
    public function pages(): void
    {
        $this->urlset($this->pageUrls());
    }

    /** Статьи и страницы категорий: /sitemap-articles.xml */
    public function articles(): void
    {
        $this->urlset($this->articleUrls());
    }

    /**
     * Постоянные страницы сайта.
     *
     * @return array<int, array{loc:string, lastmod:string, priority:string}>
     */
    private function pageUrls(): array
    {
        // По мере разработки внутренних страниц список пополняется.
        $paths = [
            '/'           => '1.0',
            '/uslugi'     => '0.9',
            '/o-kompanii' => '0.7',
            '/kontakty'   => '0.7',
            '/privacy'    => '0.3',
            '/soglasie'   => '0.3',
        ];
        // Адреса разделов берутся из самих контроллеров — список в одном месте.
        $paths[TariffController::BITRIX] = '0.8';
        $paths['/resheniya'] = '0.9';
        $paths['/keysy']     = '0.9';
        $paths['/stati']     = '0.9';

        foreach ([
            ...ServiceController::paths(),
            ...SolutionController::paths(),
            ...CaseController::paths(),
        ] as $path) {
            $paths[$path] = '0.8';
        }

        $urls = [];

        foreach ($paths as $path => $priority) {
            $urls[] = [
                'loc'      => $this->config['base_url'] . $path,
                'lastmod'  => $this->pageDate($path),
                'priority' => $priority,
            ];
        }

        return $urls;
    }

    /**
     * Статьи и категории.
     *
     * Даты берутся из базы — поисковик видит, что статья менялась,
     * и приходит перечитать её. У категории дата самой свежей статьи
     * в ней: сама по себе категория не меняется, меняется её состав.
     *
     * Если база недоступна, ошибка не глушится: сам файл карты статей
     * ответит «временно недоступно», и поисковик придёт за ним позже.
     * Пустая карта была бы хуже — она выглядит как «статей больше нет».
     *
     * @return array<int, array{loc:string, lastmod:string, priority:string}>
     */
    private function articleUrls(): array
    {
        $urls = [];

        foreach ((new TaxonomyRepository($this->db()))->categories() as $category) {
            if ((int) $category['articles'] === 0) {
                continue;
            }

            $urls[] = [
                'loc'      => $this->config['base_url'] . '/stati/kategoriya/' . $category['slug'],
                'lastmod'  => substr((string) ($category['updated_at'] ?? ''), 0, 10),
                'priority' => '0.7',
            ];
        }

        foreach ((new ArticleRepository($this->db()))->published() as $article) {
            $urls[] = [
                'loc'      => $this->config['base_url'] . '/stati/' . $article['slug'],
                'lastmod'  => substr((string) $article['updated_at'], 0, 10),
                'priority' => '0.6',
            ];
        }

        return $urls;
    }

    /** Дата изменения постоянной страницы — по времени файла с её текстами. */
    private function pageDate(string $path): string
    {
        $name = self::CONTENT[$path] ?? '';

        if ($name === '') {
            return '';
        }

        $time = @filemtime(dirname(__DIR__, 2) . '/config/content/' . $name . '.php');

        return $time === false ? '' : date('Y-m-d', $time);
    }

    /** @param array<int, array{loc:string, lastmod:string, priority:string}> $urls */
    private function urlset(array $urls): void
    {
        $body = '';

        foreach ($urls as $url) {
            $body .= "  <url><loc>{$url['loc']}</loc>"
                . ($url['lastmod'] !== '' ? "<lastmod>{$url['lastmod']}</lastmod>" : '')
                . "<priority>{$url['priority']}</priority></url>\n";
        }

        $this->xml('urlset', $body);
    }

    private function xml(string $root, string $body): void
    {
        header('Content-Type: application/xml; charset=UTF-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . "<{$root} xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            . $body
            . "</{$root}>";
    }
}
