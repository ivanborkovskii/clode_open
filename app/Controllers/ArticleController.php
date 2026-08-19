<?php
/**
 * Раздел «Статьи»: список с поиском и фильтрами, страница статьи,
 * комментарии и оценки.
 *
 * Всё содержимое берётся из базы. Фильтры устроены так:
 *  - категория всегда одна и живёт в адресе: /stati/kategoriya/bitrix24;
 *  - теги сужают выбранное и передаются в параметре ?tegi=sdelki,zadachi,
 *    статья попадает в список, только если у неё есть все отмеченные теги;
 *  - поиск — параметр ?q=, ищет по заголовку, анонсу и тексту.
 *
 * Без JavaScript раздел работает полностью: фильтры — обычные ссылки,
 * поиск — обычная форма, комментарий и оценка — обычные POST-формы.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Core\Text;
use App\Core\Validator;
use App\Models\ArticleRepository;
use App\Models\CommentRepository;
use App\Models\RatingRepository;
use App\Models\TaxonomyRepository;

final class ArticleController extends Controller
{
    private const PER_PAGE = 9;

    public function index(): void
    {
        $this->listing(null);
    }

    public function category(string $slug): void
    {
        $category = (new TaxonomyRepository($this->db()))->category($slug);

        if ($category === null) {
            $this->notFound();
            return;
        }

        $this->listing($category);
    }

    /**
     * Общий вывод списка: с категорией в адресе и без неё.
     *
     * @param array<string, mixed>|null $category
     */
    private function listing(?array $category): void
    {
        $taxonomy = new TaxonomyRepository($this->db());
        $articles = new ArticleRepository($this->db());

        // Из адреса берём только теги, которые действительно существуют:
        // в параметре может оказаться что угодно.
        $requested = $this->tagSlugs();
        $selected  = $taxonomy->tagsBySlugs($requested);
        $active    = array_column($selected, 'slug');

        $query = trim((string) ($_GET['q'] ?? ''));
        $page  = max(1, (int) ($_GET['stranica'] ?? 1));
        $base  = $category === null ? '/stati' : '/stati/kategoriya/' . $category['slug'];

        $result = $articles->paginate([
            'category' => $category['slug'] ?? '',
            'tags'     => $active,
            'q'        => $query,
        ], $page, self::PER_PAGE);

        $content = $this->content('articles');

        $this->html($this->view->render('articles', [
            'styles'  => ['css/pages.css', 'css/articles.css'],
            'scripts' => ['js/articles.js'],
            'seo'     => $this->listingSeo($category, $selected, $query, $result['page']),
            'page'    => [
                'hero'       => $this->listingHero($content, $category, $selected, $query),
                'search'     => ['action' => $base, 'value' => $query],
                'categories' => $this->categoryChips($taxonomy, $category, $active, $query),
                'tags'       => $this->tagChips($taxonomy, $base, $active, $query),
                'reset'      => ($active !== [] || $query !== '' || $category !== null) ? '/stati' : '',
                'texts'      => $content,
                'form'       => $content['form'],
            ],
            'list'       => $result,
            'pagination' => $this->pagination($base, $active, $query, $result),
            'form'       => $this->formFlash(),
        ]));
    }

    public function show(string $slug): void
    {
        Session::start();

        // Вошедшему в админку черновик показывается как обычная страница —
        // это и есть предпросмотр перед публикацией. Для всех остальных
        // неопубликованной статьи не существует.
        $isEditor = (int) ($_SESSION['admin_id'] ?? 0) > 0;

        $articles = new ArticleRepository($this->db());
        $article  = $articles->find($slug, !$isEditor);

        if ($article === null) {
            $this->notFound();
            return;
        }

        $ratings  = new RatingRepository($this->db());
        $comments = new CommentRepository($this->db());
        $id       = (int) $article['id'];
        $content  = $this->content('articles');

        $this->html($this->view->render('article', [
            'styles'  => ['css/pages.css', 'css/articles.css'],
            'scripts' => ['js/articles.js'],
            'seo'     => $this->articleSeo($article) + [
                // Черновик не должен попасть в поиск, даже если ссылку
                // на него кому-то отправили.
                'noindex' => $article['status'] !== 'published',
            ],
            'article' => $article,
            'rating'  => [
                'summary' => $ratings->summary($id),
                'mine'    => $ratings->of($id, $this->voter()),
            ],
            'comments' => $comments->tree($id),
            // Кому именно пишут ответ — приходит из адреса ?otvet=НОМЕР.
            // Так «Ответить» работает и без JavaScript.
            'replyTo'  => $this->replyTarget($comments, $id),
            'related'  => $articles->related($article),
            'texts'    => $content,
            // Форма под статьёй говорит о теме этой статьи, а не вообще.
            'leadForm' => $this->articleForm($article, $content['form']),
            'state'    => $this->commentFlash(),
            'form'     => $this->formFlash(),
        ]));
    }

    /**
     * Подсказки поиска при наборе.
     *
     * Отдаёт обычный текст: подсветку совпадений делает скрипт в браузере.
     */
    public function suggest(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $items = [];

        $words = ArticleRepository::words($query);

        if ($words !== []) {
            foreach ((new ArticleRepository($this->db()))->suggest($query) as $row) {
                // Если слово есть в заголовке или анонсе — показываем анонс.
                // Если только в тексте — показываем кусок текста вокруг него,
                // иначе непонятно, почему статья попала в подсказки.
                $shown = $row['title'] . ' ' . $row['excerpt'];
                $found = false;

                foreach ($words as $word) {
                    if (mb_stripos($shown, $word) !== false) {
                        $found = true;
                        break;
                    }
                }

                $items[] = [
                    'title'    => $row['title'],
                    'href'     => '/stati/' . $row['slug'],
                    'excerpt'  => $found
                        ? Text::excerpt((string) $row['excerpt'], 140)
                        : Text::snippet((string) $row['search_text'], $words),
                    'category' => $row['category_name'],
                ];
            }
        }

        // Подсказки меняются только при смене статей, но кэшировать их
        // надолго нельзя: только что опубликованная статья должна
        // находиться сразу.
        header('Cache-Control: no-store');

        $this->json(['q' => $query, 'items' => $items]);
    }

    /**
     * Тексты формы заявки под статьёй.
     *
     * Заголовки называют тему статьи: «Разберём вашу задачу по интеграции
     * Битрикс24 и Телеграм». Тема берётся из поля статьи, а если оно
     * не заполнено — составляется из заголовка. Не получилось и это —
     * остаются обычные тексты, те же, что на остальных страницах сайта:
     * лучше общий заголовок, чем нескладный.
     *
     * @param  array<string, mixed> $article
     * @param  array<string, mixed> $form
     * @return array<string, mixed>
     */
    private function articleForm(array $article, array $form): array
    {
        $topic = trim((string) ($article['form_topic'] ?? ''));

        if ($topic === '') {
            $topic = Text::topic((string) $article['title'], $form['topic_words'] ?? []);
        }

        if ($topic === '') {
            return $form;
        }

        return [
            'title'      => sprintf($form['title_topic'], $topic),
            'card_title' => sprintf($form['card_title_topic'], $topic),
            'lead'       => sprintf($form['lead_topic'], $topic),
        ] + $form;
    }

    /**
     * Комментарий, на который посетитель нажал «Ответить».
     *
     * Номер приходит из адреса, поэтому проверяем всё: что комментарий
     * существует, что он опубликован и что он от этой же статьи.
     *
     * @return array{id:int, name:string}|null
     */
    private function replyTarget(CommentRepository $comments, int $articleId): ?array
    {
        $id = (int) ($_GET['otvet'] ?? 0);

        if ($id <= 0) {
            return null;
        }

        $comment = $comments->find($id);

        if ($comment === null
            || (int) $comment['article_id'] !== $articleId
            || $comment['status'] !== 'approved'
        ) {
            return null;
        }

        return ['id' => $id, 'name' => (string) $comment['name']];
    }

    /** Приём комментария к статье. */
    public function comment(string $slug): void
    {
        $isAjax  = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
        $article = (new ArticleRepository($this->db()))->find($slug);

        if ($article === null) {
            $this->notFound();
            return;
        }

        if (!Csrf::check($_POST['_token'] ?? null)) {
            $this->commentResult($isAjax, $slug, 'error', [
                '_form' => 'Сессия устарела. Обновите страницу и напишите ещё раз.',
            ]);
            return;
        }

        $validator = new Validator($_POST);

        // Скрытое поле, которое заполняют боты. Как и в форме заявки,
        // по нему ничего не выбрасываем: комментарий сохранится,
        // а решение принимает человек при проверке.
        $suspicious = $validator->value('pole_2') !== '';

        $validator
            ->required('name', 'Представьтесь, пожалуйста')
            ->length('name', 2, 100, 'Имя должно быть от 2 до 100 символов')
            ->email('email', 'Проверьте адрес электронной почты')
            ->required('body', 'Напишите текст комментария')
            ->length('body', 3, 3000, 'Комментарий должен быть от 3 до 3000 символов')
            ->required('privacy', 'Без согласия на обработку данных комментарий не отправить');

        if (!$validator->passes()) {
            $this->commentResult($isAjax, $slug, 'error', $validator->errors(), $_POST);
            return;
        }

        $comments = new CommentRepository($this->db());

        // Ответ привязывается ровно к тому комментарию, на который нажали:
        // так на странице видно, кто кому отвечает, и ответ на ответ
        // начинает новую ветку.
        $parentId = (int) ($_POST['parent_id'] ?? 0);
        $parent   = $parentId > 0 ? $comments->find($parentId) : null;

        $parentId = $parent !== null
            && (int) $parent['article_id'] === (int) $article['id']
            && $parent['status'] === 'approved'
                ? $parentId
                : null;

        $comments->add(
            (int) $article['id'],
            $validator->value('name'),
            $validator->value('email'),
            // Разметку в комментариях не разрешаем вовсе: текст выводится
            // экранированным, и вставить сюда ссылку или скрипт нельзя.
            ($suspicious ? '[возможно спам] ' : '') . $validator->value('body'),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $parentId,
        );

        $this->commentResult($isAjax, $slug, 'success', []);
    }

    /** Оценка статьи: одна на посетителя, повторная заменяет прежнюю. */
    public function rate(string $slug): void
    {
        $isAjax  = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
        $article = (new ArticleRepository($this->db()))->find($slug);
        $value   = (int) ($_POST['value'] ?? 0);

        if ($article === null || $value < 1 || $value > 5 || !Csrf::check($_POST['_token'] ?? null)) {
            if ($isAjax) {
                $this->json(['status' => 'error'], 422);
                return;
            }

            $this->redirect('/stati/' . $slug . '#ocenka', 303);
            return;
        }

        $ratings = new RatingRepository($this->db());
        $ratings->vote((int) $article['id'], $value, $this->voter());

        if ($isAjax) {
            $this->json([
                'status'  => 'ok',
                'mine'    => $value,
                'summary' => $ratings->summary((int) $article['id']),
            ]);
            return;
        }

        $this->redirect('/stati/' . $slug . '?ocenka=ok#ocenka', 303);
    }

    /**
     * Отпечаток посетителя для оценок.
     *
     * Собирается из метки в куке, адреса и браузера. Личных данных
     * не содержит: наружу уходит только необратимый хэш.
     */
    private function voter(): string
    {
        Session::start();

        if (empty($_COOKIE['ocenka'])) {
            $mark = bin2hex(random_bytes(16));
            setcookie('ocenka', $mark, [
                'expires'  => time() + 86400 * 365,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            $_COOKIE['ocenka'] = $mark;
        }

        return hash('sha256', implode('|', [
            $_COOKIE['ocenka'],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]));
    }

    /**
     * Теги из адреса: ?tegi=sdelki,zadachi
     *
     * @return array<int, string>
     */
    private function tagSlugs(): array
    {
        $raw = (string) ($_GET['tegi'] ?? '');

        if ($raw === '') {
            return [];
        }

        $slugs = array_filter(
            array_map('trim', explode(',', $raw)),
            static fn (string $slug): bool => $slug !== '' && preg_match('/^[a-z0-9\-]+$/', $slug) === 1,
        );

        return array_slice(array_unique($slugs), 0, 10);
    }

    /**
     * Адрес страницы списка с нужным набором фильтров.
     *
     * @param array<int, string> $tags
     */
    private function link(string $base, array $tags, string $query, int $page = 1): string
    {
        $params = [];

        if ($tags !== []) {
            $params['tegi'] = implode(',', $tags);
        }

        if ($query !== '') {
            $params['q'] = $query;
        }

        if ($page > 1) {
            $params['stranica'] = $page;
        }

        return $params === [] ? $base : $base . '?' . http_build_query($params);
    }

    /**
     * Кнопки категорий. Выбранные теги и поисковый запрос при смене
     * категории сохраняются — фильтры складываются, а не сбрасывают друг друга.
     *
     * @param  array<string, mixed>|null $current
     * @param  array<int, string>        $tags
     * @return array<int, array<string, mixed>>
     */
    private function categoryChips(
        TaxonomyRepository $taxonomy,
        ?array $current,
        array $tags,
        string $query,
    ): array {
        $chips = [[
            'name'   => 'Все',
            'href'   => $this->link('/stati', $tags, $query),
            'active' => $current === null,
            'count'  => null,
        ]];

        foreach ($taxonomy->categories() as $category) {
            $chips[] = [
                'name'   => $category['name'],
                'href'   => $this->link('/stati/kategoriya/' . $category['slug'], $tags, $query),
                'active' => ($current['slug'] ?? '') === $category['slug'],
                'count'  => (int) $category['articles'],
            ];
        }

        return $chips;
    }

    /**
     * Кнопки тегов. Нажатие добавляет тег к выбранным или снимает его.
     *
     * @param  array<int, string> $active
     * @return array<int, array<string, mixed>>
     */
    private function tagChips(
        TaxonomyRepository $taxonomy,
        string $base,
        array $active,
        string $query,
    ): array {
        $chips = [];

        foreach ($taxonomy->tags(true) as $tag) {
            $isActive = in_array($tag['slug'], $active, true);

            $next = $isActive
                ? array_values(array_diff($active, [$tag['slug']]))
                : [...$active, $tag['slug']];

            $chips[] = [
                'slug'   => $tag['slug'],
                'name'   => $tag['name'],
                'href'   => $this->link($base, $next, $query),
                'active' => $isActive,
                'count'  => (int) $tag['articles'],
            ];
        }

        return $chips;
    }

    /**
     * @param  array<int, string> $tags
     * @param  array{page:int, pages:int} $result
     * @return array<string, mixed>
     */
    private function pagination(string $base, array $tags, string $query, array $result): array
    {
        $pages = [];

        for ($number = 1; $number <= $result['pages']; $number++) {
            $pages[] = [
                'number' => $number,
                'href'   => $this->link($base, $tags, $query, $number),
                'active' => $number === $result['page'],
            ];
        }

        return [
            'page'  => $result['page'],
            'pages' => $result['pages'],
            'items' => $pages,
            'prev'  => $result['page'] > 1
                ? $this->link($base, $tags, $query, $result['page'] - 1) : '',
            'next'  => $result['page'] < $result['pages']
                ? $this->link($base, $tags, $query, $result['page'] + 1) : '',
        ];
    }

    /**
     * Заголовок и подзаголовок списка. Меняются вместе с фильтрами:
     * страница категории должна отличаться от общего списка не только
     * набором карточек.
     *
     * @param  array<string, mixed>      $content
     * @param  array<string, mixed>|null $category
     * @param  array<int, array<string, mixed>> $tags
     * @return array<string, string>
     */
    private function listingHero(array $content, ?array $category, array $tags, string $query): array
    {
        if ($query !== '') {
            return [
                'title' => 'Поиск по статьям',
                'lead'  => 'Результаты по запросу «' . $query . '».',
                'text'  => '',
            ];
        }

        if ($category !== null) {
            return [
                'title' => $category['name'],
                'lead'  => $category['description'],
                'text'  => $tags === [] ? '' : 'Отобраны статьи с темами: '
                    . implode(', ', array_column($tags, 'name')) . '.',
            ];
        }

        if ($tags !== []) {
            return [
                'title' => $content['hero']['title'],
                'lead'  => 'Статьи с темами: ' . implode(', ', array_column($tags, 'name')) . '.',
                'text'  => '',
            ];
        }

        return $content['hero'];
    }

    /**
     * Метатеги списка.
     *
     * Комбинации фильтров закрываем от индексации: страница «категория плюс
     * три тега» — это тот же набор статей в другом порядке, и в поиске
     * такие адреса плодят дубли. Открытыми остаются общий список
     * и страницы категорий.
     *
     * @param  array<string, mixed>|null $category
     * @param  array<int, array<string, mixed>> $tags
     * @return array<string, mixed>
     */
    private function listingSeo(?array $category, array $tags, string $query, int $page): array
    {
        $content   = $this->content('articles');
        $isFilered = $tags !== [] || $query !== '' || $page > 1;

        $crumbs = [['label' => 'Главная', 'href' => '/'], ['label' => 'Статьи', 'href' => '/stati']];

        if ($category !== null) {
            $crumbs[] = [
                'label' => $category['name'],
                'href'  => '/stati/kategoriya/' . $category['slug'],
            ];
        }

        $base = $category === null ? '/stati' : '/stati/kategoriya/' . $category['slug'];

        return [
            'title' => $category === null
                ? $content['seo']['title']
                : $category['title'],
            'description' => $category === null
                ? $content['seo']['description']
                : $category['description'],
            'canonical'   => $this->url($base),
            'noindex'     => $isFilered,
            'breadcrumbs' => $crumbs,
        ];
    }

    /**
     * @param  array<string, mixed> $article
     * @return array<string, mixed>
     */
    private function articleSeo(array $article): array
    {
        $description = $article['meta_description'] !== ''
            ? $article['meta_description']
            : Text::excerpt((string) ($article['excerpt'] ?: $article['body']), 300);

        return [
            'title' => $article['meta_title'] !== '' ? $article['meta_title'] : $article['title'],
            'description' => $description,
            'canonical'   => $this->url('/stati/' . $article['slug']),
            'og_type'     => 'article',
            'og_image'    => $article['cover'] !== ''
                ? $this->config['base_url'] . $article['cover']
                : '',
            'breadcrumbs' => [
                ['label' => 'Главная', 'href' => '/'],
                ['label' => 'Статьи',  'href' => '/stati'],
                [
                    'label' => $article['category_name'],
                    'href'  => '/stati/kategoriya/' . $article['category_slug'],
                ],
                ['label' => $article['title'], 'href' => '/stati/' . $article['slug']],
            ],
            // Микроразметка статьи: заголовок, даты и автор — то, что
            // поисковики показывают в выдаче.
            'jsonld' => [[
                '@context' => 'https://schema.org',
                '@type'    => 'Article',
                'headline' => $article['title'],
                'description'   => $description,
                'datePublished' => $article['published_at'],
                'dateModified'  => substr((string) $article['updated_at'], 0, 10),
                'author' => [
                    '@type' => 'Person',
                    'name'  => $article['author'] !== ''
                        ? $article['author']
                        : $this->config['company']['name'],
                ],
                'mainEntityOfPage' => $this->url('/stati/' . $article['slug']),
            ] + ($article['cover'] !== '' ? ['image' => $this->config['base_url'] . $article['cover']] : [])],
        ];
    }

    /**
     * Результат отправки комментария для формы без JavaScript.
     *
     * @return array{status:string, errors:array<string,string>, values:array<string,string>}
     */
    private function commentFlash(): array
    {
        Session::start();

        $flash = $_SESSION['comment_flash'] ?? [];
        unset($_SESSION['comment_flash']);

        $marker = $_GET['kommentariy'] ?? '';
        $status = $flash['status'] ?? match ($marker) {
            'ok'    => 'success',
            'error' => 'error',
            default => '',
        };

        return [
            'status' => $status,
            'errors' => $flash['errors'] ?? [],
            'values' => $flash['values'] ?? [],
        ];
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed>  $values
     */
    private function commentResult(
        bool $isAjax,
        string $slug,
        string $status,
        array $errors,
        array $values = [],
    ): void {
        if ($isAjax) {
            $this->json(['status' => $status, 'errors' => $errors], $status === 'success' ? 200 : 422);
            return;
        }

        Session::start();

        $_SESSION['comment_flash'] = [
            'status' => $status,
            'errors' => $errors,
            'values' => $status === 'success'
                ? []
                : array_map('strval', array_diff_key($values, ['_token' => ''])),
        ];

        $this->redirect(
            '/stati/' . $slug . '?kommentariy=' . ($status === 'success' ? 'ok' : 'error') . '#kommentarii',
            303,
        );
    }
}
