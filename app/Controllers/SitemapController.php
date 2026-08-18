<?php
/**
 * Карта сайта.
 *
 * Сразу сделана как индекс из нескольких файлов: при тысячах SEO-статей
 * один файл не подойдёт (лимит поисковых систем — 50 000 URL на файл).
 * Сейчас в индексе один файл со статическими страницами; файлы со статьями
 * добавятся, когда появится раздел.
 */

declare(strict_types=1);

namespace App\Controllers;

final class SitemapController extends Controller
{
    /** Индекс карт: /sitemap.xml */
    public function index(): void
    {
        $files = ['sitemap-pages.xml'];
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
        $paths['/resheniya'] = '0.9';
        $paths['/keysy']     = '0.9';

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

    private function xml(string $root, string $body): void
    {
        header('Content-Type: application/xml; charset=UTF-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . "<{$root} xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            . $body
            . "</{$root}>";
    }
}
