<?php
/**
 * Поиск по всему сайту.
 *
 * Два входа в одно и то же: страница /poisk с полной выдачей и адрес
 * /api/poisk для подсказок при наборе. Выборка у них общая, отличается
 * только число строк и то, во что они превращаются — в разметку
 * страницы или в JSON для скрипта.
 *
 * Страница закрыта от индексации. Выдача поиска — это не содержание
 * сайта, а его перетасовка: для поисковых систем это страницы низкого
 * качества, и в выдачу они попадать не должны.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Text;
use App\Models\SearchRepository;

final class SearchController extends Controller
{
    /**
     * Сколько страниц показываем на /poisk.
     *
     * Страниц за этой чертой человек не смотрит, а листалка ради них
     * означала бы вторую, третью и десятую страницу выдачи — пустую
     * работу и для кода, и для посетителя. Если нашлось больше, страница
     * честно говорит об этом и предлагает уточнить запрос.
     */
    private const LIMIT = 25;

    /** Сколько подсказок показываем при наборе. */
    private const HINTS = 8;

    public function index(): void
    {
        $texts = $this->content('search');
        $query = $this->query();
        $items = $this->rows($query, self::LIMIT, 110);
        $leads = $texts['leads'];

        // Строка под заголовком отвечает на то, что человек сделал:
        // ещё ничего не набрал, набрал и нашлось, набрал и не нашлось.
        if ($query === '') {
            $lead = $leads['start'];
        } elseif ($items === []) {
            $lead = sprintf($leads['none'], $query);
        } else {
            // Найтись может больше, чем помещается на странице. Число
            // называем настоящее, а не длину списка: «нашли 40» при сорока
            // показанных из шестидесяти — неправда.
            $total = (new SearchRepository($this->db()))->count($query);

            $lead = sprintf(
                $leads['found'],
                $total,
                Text::plural($total, ...$leads['pages']),
                $query,
            );

            if ($total > count($items)) {
                $lead .= ' ' . sprintf($leads['more'], count($items));
            }
        }

        $this->html($this->view->render('search', [
            'styles' => ['css/pages.css'],
            'seo' => [
                'title'       => $texts['hero']['title'],
                'description' => '',
                'noindex'     => true,
                'breadcrumbs' => [
                    ['label' => 'Главная', 'href' => '/'],
                    ['label' => $texts['hero']['crumb'], 'href' => '/poisk'],
                ],
            ],
            'page'  => $texts,
            'query' => $query,
            'items' => $items,
            'lead'  => $lead,
            // Слова запроса нужны шаблону: он выделяет совпадения
            // в найденных кусках текста.
            'words' => SearchRepository::stems($query),
            // Адрес сайта без «https://» — под каждой найденной страницей
            // видно, куда она ведёт, как в обычной поисковой выдаче.
            'host'  => preg_replace('~^https?://~', '', $this->config['base_url']),
        ]));
    }

    /**
     * Подсказки при наборе.
     *
     * Отдаёт обычный текст без разметки: подсветку совпадений делает
     * скрипт в браузере. Готовый HTML отсюда пришлось бы вставлять
     * в страницу как есть — это лишний риск на ровном месте.
     */
    public function suggest(): void
    {
        $query = $this->query();
        $items = [];

        foreach ($this->rows($query, self::HINTS, 70) as $row) {
            $items[] = [
                'title'    => $row['title'],
                'href'     => $row['url'],
                'excerpt'  => $row['text'],
                'category' => $row['section'],
            ];
        }

        // Подсказки меняются только при обновлении указателя, но держать
        // их в памяти браузера нельзя: после обновления он показывал бы
        // старые.
        header('Cache-Control: no-store');

        // Слова отдаём вместе с подсказками: скрипт выделяет совпадения
        // по ним, а не по набранному. Иначе при «воронка» подсветка
        // не находила бы «воронки» — поиск их уже считает одним словом.
        $this->json([
            'q'     => $query,
            'words' => SearchRepository::stems($query),
            'items' => $items,
        ]);
    }

    /**
     * Найденные страницы в том виде, в каком их показывают.
     *
     * @return array<int, array{url:string, section:string, title:string, text:string}>
     */
    private function rows(string $query, int $limit, int $radius): array
    {
        $words = SearchRepository::stems($query);

        if ($words === []) {
            return [];
        }

        $items = [];

        foreach ((new SearchRepository($this->db()))->find($query, $limit) as $row) {
            // Если слово есть в заголовке или в первой строке — показываем
            // первую строку. Если только в тексте — кусок текста вокруг
            // него, иначе непонятно, почему страница попала в выдачу.
            $shown = $row['title'] . ' ' . $row['excerpt'];
            $found = false;

            foreach ($words as $word) {
                if (mb_stripos($shown, $word) !== false) {
                    $found = true;
                    break;
                }
            }

            $items[] = [
                'url'     => (string) $row['url'],
                'section' => (string) $row['section'],
                'title'   => (string) $row['title'],
                'text'    => $found && $row['excerpt'] !== ''
                    ? Text::excerpt((string) $row['excerpt'], $radius * 2)
                    : Text::snippet((string) $row['body'], $words, $radius),
            ];
        }

        return $items;
    }

    /**
     * Поисковый запрос из адреса.
     *
     * Длину ограничиваем: в строку поиска можно вставить целую страницу
     * текста, а искать по ней нечего — слов всё равно берётся шесть.
     */
    private function query(): string
    {
        return mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 120);
    }
}
