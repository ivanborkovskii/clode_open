<?php
/**
 * Юридические страницы: политика и согласие.
 * Обе собираются одним шаблоном — отличается только набор текстов.
 */

declare(strict_types=1);

namespace App\Controllers;

final class LegalController extends Controller
{
    public function privacy(): void
    {
        $this->show('privacy', '/privacy');
    }

    public function consent(): void
    {
        $this->show('consent', '/soglasie');
    }

    private function show(string $key, string $path): void
    {
        $doc = $this->content('legal')[$key];

        $this->html($this->view->render('legal', [
            'seo' => [
                'title'       => $doc['title'],
                'description' => $doc['description'],
                'canonical'   => $this->url($path),
            ],
            'doc' => $doc,
        ]));
    }
}
