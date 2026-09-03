<?php
/**
 * Карта сайта.
 *
 * Сразу сделана как индекс из нескольких файлов: при тысячах SEO-статей
 * один файл не подойдёт (лимит поисковых систем — 50 000 URL на файл).
 * Сейчас в индексе два файла: постоянные страницы и статьи.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ArticleRepository;
use App\Models\TaxonomyRepository;

final class SitemapController extends Controller
{
    /** Индекс карт: /sitemap.xml */
    public function index(): void
    {
        $files = ['sitemap-pages.xml', 'sitemap-articles.xml'];
        $body  = '';

        foreach ($files as $file) {
            $body .= "  <sitemap><loc>{$this->config['base_url']}/{$file}</loc>"
                . '<lastmod>' . date('Y-m-d') . "</lastmod></sitemap>\n";
        }

        $this->xml('sitemapindex', $body);
    }

    /** Статические страницы: /sitemap-pages.xml */
    public function pages(): void
    {
        // По мере разработки внутренних страниц список пополняется.
        $paths = [
            '/'         => '1.0',
            '/uslugi'   => '0.9',
            '/o-kompanii' => '0.7',
            '/kontakty'   => '0.7',
            '/privacy'  => '0.3',
            '/soglasie' => '0.3',
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

        $body  = '';

        foreach ($paths as $path => $priority) {
            $body .= "  <url><loc>{$this->config['base_url']}{$path}</loc>"
                . '<lastmod>' . date('Y-m-d') . "</lastmod>"
                . "<priority>{$priority}</priority></url>\n";
        }

        $this->xml('urlset', $body);
    }

    /**
     * Статьи и страницы категорий: /sitemap-articles.xml
     *
     * Даты берутся из базы — поисковик видит, что статья менялась,
     * и приходит перечитать её.
     */
    public function articles(): void
    {
        $body = '';

        foreach ((new TaxonomyRepository($this->db()))->categories() as $category) {
            if ((int) $category['articles'] === 0) {
                continue;
            }

            $body .= "  <url><loc>{$this->config['base_url']}/stati/kategoriya/{$category['slug']}</loc>"
                . '<lastmod>' . date('Y-m-d') . '</lastmod>'
                . "<priority>0.7</priority></url>\n";
        }

        foreach ((new ArticleRepository($this->db()))->published() as $article) {
            $modified = substr((string) $article['updated_at'], 0, 10);

            $body .= "  <url><loc>{$this->config['base_url']}/stati/{$article['slug']}</loc>"
                . "<lastmod>{$modified}</lastmod>"
                . "<priority>0.6</priority></url>\n";
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
