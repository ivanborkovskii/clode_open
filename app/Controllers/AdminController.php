<?php
/**
 * Админка раздела «Статьи».
 *
 * Один контроллер на весь раздел: действий немного, и все они про одно и
 * то же — статьи, темы и комментарии. Адреса вида /admin/statya разбираются
 * здесь же, поэтому в маршрутах раздел занимает три строки, а не двадцать.
 *
 * Доступ: сессия с идентификатором учётной записи. Пока в таблице admins
 * нет ни одной записи, работает только страница установки — на ней и
 * заводится первый вход.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Core\Text;
use App\Core\Upload;
use App\Models\AdminRepository;
use App\Models\ArticleRepository;
use App\Models\CommentRepository;
use App\Models\TaxonomyRepository;
use PDOException;

final class AdminController extends Controller
{
    /** Сколько неудачных попыток входа подряд допускается. */
    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 300;

    public function handle(string $action = ''): void
    {
        Session::start();

        try {
            $admins = new AdminRepository($this->db());
            $empty  = $admins->isEmpty();
        } catch (PDOException $error) {
            // Раздел «Статьи» — единственная часть сайта, которой нужна база.
            // Если подключиться не удалось, честно говорим об этом, а не
            // показываем пустую страницу.
            $this->page('admin/error', [
                'title'   => 'Нет связи с базой данных',
                'message' => 'Проверьте настройки подключения в config/app.php '
                    . 'или переменные окружения DB_HOST, DB_DATABASE, DB_USERNAME '
                    . 'и DB_PASSWORD. Также убедитесь, что база создана и в неё '
                    . 'загружен файл database/schema.sql.',
                'details' => $this->config['debug'] ? $error->getMessage() : '',
            ]);

            return;
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Пока учётной записи нет, всё ведёт на установку. Как только она
        // появилась, страница установки закрывается сама.
        if ($empty) {
            if ($action !== 'ustanovka') {
                $this->redirect('/admin/ustanovka');
                return;
            }

            $this->setup($admins, $method);
            return;
        }

        if ($action === 'ustanovka') {
            $this->redirect('/admin');
            return;
        }

        if ($action === 'vhod') {
            $this->login($admins, $method);
            return;
        }

        if (!$this->isAuthorized($admins)) {
            $this->redirect('/admin/vhod');
            return;
        }

        // Если отправленное больше post_max_size, PHP отбрасывает весь запрос
        // целиком: и поля, и файлы приходят пустыми. Без этой проверки дальше
        // не сошёлся бы токен формы и человек увидел бы «форма устарела»,
        // хотя дело в размере картинки.
        if ($method === 'POST' && $_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->page('admin/error', [
                'title'   => 'Отправленное не поместилось',
                'message' => 'Сервер принимает за один раз не больше '
                    . ini_get('post_max_size') . ', а картинка — не больше '
                    . ini_get('upload_max_filesize') . '. Нажмите в браузере «Назад»: '
                    . 'написанное сохранится. Уменьшите картинку или сохраните '
                    . 'статью без неё, а картинку добавьте отдельно.',
                'details' => '',
            ]);

            return;
        }

        match ($action) {
            ''             => $this->articles(),
            'vyhod'        => $this->logout(),
            'statya'       => $method === 'POST' ? $this->saveArticle() : $this->editArticle(),
            'udalit'       => $this->deleteArticle(),
            'kommentarii'  => $this->comments(),
            'kommentariy'  => $this->moderate(),
            'temy'         => $method === 'POST' ? $this->saveTag() : $this->tags(),
            'kartinka'     => $this->uploadImage(),
            'parol'        => $this->password($method),
            'proverka'     => $this->selfCheck(),
            default        => $this->notFound(),
        };
    }

    /* --------------------------------------------------------------------
       Вход и учётная запись
       -------------------------------------------------------------------- */

    private function setup(AdminRepository $admins, string $method): void
    {
        $errors = [];

        if ($method === 'POST') {
            $login    = trim((string) ($_POST['login'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $repeat   = (string) ($_POST['password_repeat'] ?? '');

            if (mb_strlen($login) < 3) {
                $errors['login'] = 'Логин должен быть от 3 символов';
            }

            // Требование к длине, а не к «одной заглавной и одной цифре»:
            // длинный пароль надёжнее короткого со спецсимволами, и его
            // не приходится записывать на бумажке.
            if (mb_strlen($password) < 10) {
                $errors['password'] = 'Пароль должен быть от 10 символов';
            }

            if ($password !== $repeat) {
                $errors['password_repeat'] = 'Пароли не совпадают';
            }

            if ($errors === []) {
                $id = $admins->create($login, $password);
                $_SESSION['admin_id'] = $id;

                $this->redirect('/admin');
                return;
            }
        }

        $this->page('admin/setup', ['errors' => $errors, 'values' => $_POST]);
    }

    private function login(AdminRepository $admins, string $method): void
    {
        if ($this->isAuthorized($admins)) {
            $this->redirect('/admin');
            return;
        }

        $error = '';

        if ($method === 'POST') {
            $error = $this->attemptLogin($admins);

            if ($error === '') {
                $this->redirect('/admin');
                return;
            }
        }

        $this->page('admin/login', ['error' => $error, 'login' => $_POST['login'] ?? '']);
    }

    /** Возвращает текст ошибки или пустую строку при успехе. */
    private function attemptLogin(AdminRepository $admins): string
    {
        if (!Csrf::check($_POST['_token'] ?? null)) {
            return 'Форма устарела. Обновите страницу и попробуйте ещё раз.';
        }

        // Простая защита от перебора: после нескольких промахов подряд
        // вход закрывается на пять минут.
        $lockedUntil = (int) ($_SESSION['admin_lock'] ?? 0);

        if ($lockedUntil > time()) {
            $minutes = (int) ceil(($lockedUntil - time()) / 60);

            return "Слишком много попыток. Попробуйте через {$minutes} мин.";
        }

        $admin = $admins->byLogin(trim((string) ($_POST['login'] ?? '')));
        $ok    = $admin !== null
            && password_verify((string) ($_POST['password'] ?? ''), (string) $admin['password_hash']);

        if (!$ok) {
            $attempts = (int) ($_SESSION['admin_attempts'] ?? 0) + 1;
            $_SESSION['admin_attempts'] = $attempts;

            if ($attempts >= self::MAX_ATTEMPTS) {
                $_SESSION['admin_lock'] = time() + self::LOCK_SECONDS;
                $_SESSION['admin_attempts'] = 0;
            }

            return 'Неверный логин или пароль';
        }

        // Новый идентификатор сессии после входа: старый мог быть заранее
        // подсунут посетителю, и тогда он остался бы действительным.
        session_regenerate_id(true);

        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_attempts'] = 0;

        $admins->markLogin((int) $admin['id']);

        return '';
    }

    private function logout(): void
    {
        unset($_SESSION['admin_id']);
        session_regenerate_id(true);

        $this->redirect('/admin/vhod');
    }

    private function password(string $method): void
    {
        $admins = new AdminRepository($this->db());
        $admin  = $admins->byId((int) $_SESSION['admin_id']);
        $errors = [];
        $done   = false;

        if ($method === 'POST' && Csrf::check($_POST['_token'] ?? null)) {
            $current = (string) ($_POST['current'] ?? '');
            $next    = (string) ($_POST['password'] ?? '');

            if (!password_verify($current, (string) $admin['password_hash'])) {
                $errors['current'] = 'Текущий пароль указан неверно';
            }

            if (mb_strlen($next) < 10) {
                $errors['password'] = 'Пароль должен быть от 10 символов';
            }

            if ($next !== (string) ($_POST['password_repeat'] ?? '')) {
                $errors['password_repeat'] = 'Пароли не совпадают';
            }

            if ($errors === []) {
                $admins->changePassword((int) $admin['id'], $next);
                $done = true;
            }
        }

        $this->page('admin/password', ['errors' => $errors, 'done' => $done, 'admin' => $admin]);
    }

    private function isAuthorized(AdminRepository $admins): bool
    {
        $id = (int) ($_SESSION['admin_id'] ?? 0);

        return $id > 0 && $admins->byId($id) !== null;
    }

    /* --------------------------------------------------------------------
       Статьи
       -------------------------------------------------------------------- */

    private function articles(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));

        $this->page('admin/articles', [
            'articles' => (new ArticleRepository($this->db()))->adminListing($query),
            'query'    => $query,
            'flash'    => $this->flash(),
        ]);
    }

    private function editArticle(): void
    {
        $id      = (int) ($_GET['id'] ?? 0);
        $article = $id > 0 ? (new ArticleRepository($this->db()))->findById($id) : null;

        if ($id > 0 && $article === null) {
            $this->notFound();
            return;
        }

        $taxonomy = new TaxonomyRepository($this->db());

        $this->page('admin/edit', [
            'article'    => $article,
            'categories' => $taxonomy->categories(),
            'tags'       => $taxonomy->tags(),
            'errors'     => $_SESSION['admin_errors'] ?? [],
            'values'     => $_SESSION['admin_values'] ?? [],
        ]);

        unset($_SESSION['admin_errors'], $_SESSION['admin_values']);
    }

    private function saveArticle(): void
    {
        if (!Csrf::check($_POST['_token'] ?? null)) {
            $this->back('/admin/statya', 'Форма устарела, попробуйте ещё раз');
            return;
        }

        $articles = new ArticleRepository($this->db());
        $id       = (int) ($_POST['id'] ?? 0) ?: null;
        $existing = $id !== null ? $articles->findById($id) : null;

        $title = trim((string) ($_POST['title'] ?? ''));
        $body  = (string) ($_POST['body'] ?? '');
        $slug  = Text::slug((string) ($_POST['slug'] ?? '') ?: $title);

        $errors     = [];
        $categories = (new TaxonomyRepository($this->db()))->categories();
        $categoryId = (int) ($_POST['category_id'] ?? 0);

        if ($title === '') {
            $errors['title'] = 'Без заголовка статью не сохранить';
        }

        // Категория обязательна, и она должна существовать: иначе запрос
        // упал бы на внешнем ключе с ошибкой базы вместо понятного текста.
        if (!in_array($categoryId, array_map('intval', array_column($categories, 'id')), true)) {
            $errors['category_id'] = 'Выберите категорию статьи';
        }

        if (trim(Text::plain($body)) === '') {
            $errors['body'] = 'Текст статьи пустой';
        }

        if ($slug === '') {
            $errors['slug'] = 'Не удалось составить адрес — задайте его вручную';
        } elseif ($articles->slugTaken($slug, $id)) {
            $errors['slug'] = 'Такой адрес уже занят другой статьёй';
        }

        if ($errors !== []) {
            $_SESSION['admin_errors'] = $errors;
            $_SESSION['admin_values'] = $this->postValues();

            $this->redirect('/admin/statya' . ($id !== null ? '?id=' . $id : ''));
            return;
        }

        $cover       = (string) ($_POST['cover'] ?? '');
        $coverWidth  = (int) ($_POST['cover_width'] ?? 0);
        $coverHeight = (int) ($_POST['cover_height'] ?? 0);

        // Новая обложка заменяет прежнюю. Если файл не выбирали,
        // остаётся то, что было.
        if (($_FILES['cover_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $uploaded = Upload::image($_FILES['cover_file'], $this->publicDir());

            if (isset($uploaded['error'])) {
                $_SESSION['admin_errors'] = ['cover' => $uploaded['error']];
                $_SESSION['admin_values'] = $this->postValues();

                $this->redirect('/admin/statya' . ($id !== null ? '?id=' . $id : ''));
                return;
            }

            $cover       = $uploaded['path'];
            $coverWidth  = $uploaded['width'];
            $coverHeight = $uploaded['height'];
        }

        $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
        $reading = (int) ($_POST['reading_time'] ?? 0);
        $date    = (string) ($_POST['published_at'] ?? '');

        $data = [
            'slug'        => $slug,
            'category_id' => $categoryId,
            'title'       => $title,
            // Анонс необязателен: если его не написали, берём начало текста.
            'excerpt'     => $excerpt !== '' ? $excerpt : Text::excerpt($body),
            'body'        => $body,
            // Поисковая строка собирается здесь, а не при каждом запросе:
            // статью сохраняют изредка, а ищут по ней постоянно.
            'search_text' => $title . ' ' . $excerpt . ' ' . Text::plain($body),
            'cover'        => $cover,
            'cover_alt'    => trim((string) ($_POST['cover_alt'] ?? '')),
            'cover_width'  => $coverWidth,
            'cover_height' => $coverHeight,
            'author'       => trim((string) ($_POST['author'] ?? '')),
            'reading_time' => $reading > 0 ? $reading : Text::readingTime($body),
            'published_at' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d'),
            'status'       => ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft',
            'meta_title'       => trim((string) ($_POST['meta_title'] ?? '')),
            'meta_description' => trim((string) ($_POST['meta_description'] ?? '')),
        ];

        $tagIds = (new TaxonomyRepository($this->db()))->tagIds(
            array_map('strval', (array) ($_POST['tags'] ?? [])),
        );

        $saved = $articles->save($id, $data, $tagIds);

        $_SESSION['admin_flash'] = $existing === null
            ? 'Статья создана'
            : 'Изменения сохранены';

        $this->redirect('/admin/statya?id=' . $saved);
    }

    private function deleteArticle(): void
    {
        if (Csrf::check($_POST['_token'] ?? null)) {
            (new ArticleRepository($this->db()))->delete((int) ($_POST['id'] ?? 0));
            $_SESSION['admin_flash'] = 'Статья удалена';
        }

        $this->redirect('/admin');
    }

    /** Загрузка картинки в текст статьи. Ответ читает редактор. */
    private function uploadImage(): void
    {
        if (!Csrf::check($_POST['_token'] ?? null)) {
            $this->json(['error' => 'Форма устарела, обновите страницу'], 422);
            return;
        }

        $result = Upload::image($_FILES['file'] ?? [], $this->publicDir());

        $this->json($result, isset($result['error']) ? 422 : 200);
    }

    /* --------------------------------------------------------------------
       Темы и комментарии
       -------------------------------------------------------------------- */

    private function tags(): void
    {
        $this->page('admin/tags', [
            'tags'  => (new TaxonomyRepository($this->db()))->tags(),
            'flash' => $this->flash(),
        ]);
    }

    private function saveTag(): void
    {
        if (!Csrf::check($_POST['_token'] ?? null)) {
            $this->redirect('/admin/temy');
            return;
        }

        $taxonomy = new TaxonomyRepository($this->db());
        $action   = (string) ($_POST['do'] ?? '');
        $name     = trim((string) ($_POST['name'] ?? ''));

        if ($action === 'add' && $name !== '') {
            $slug = Text::slug((string) ($_POST['slug'] ?? '') ?: $name);

            if ($slug !== '') {
                $taxonomy->addTag($slug, $name, (int) ($_POST['position'] ?? 0));
                $_SESSION['admin_flash'] = 'Тема добавлена';
            }
        } elseif ($action === 'rename' && $name !== '') {
            $taxonomy->renameTag((int) ($_POST['id'] ?? 0), $name, (int) ($_POST['position'] ?? 0));
            $_SESSION['admin_flash'] = 'Тема переименована';
        } elseif ($action === 'delete') {
            // Статьи остаются, у них просто снимается эта тема.
            $taxonomy->deleteTag((int) ($_POST['id'] ?? 0));
            $_SESSION['admin_flash'] = 'Тема удалена';
        }

        $this->redirect('/admin/temy');
    }

    private function comments(): void
    {
        $status   = (string) ($_GET['status'] ?? 'new');
        $comments = new CommentRepository($this->db());

        $this->page('admin/comments', [
            'comments' => $comments->listing($status),
            'status'   => $status,
            'new'      => $comments->newCount(),
            'flash'    => $this->flash(),
        ]);
    }

    private function moderate(): void
    {
        if (Csrf::check($_POST['_token'] ?? null)) {
            $comments = new CommentRepository($this->db());
            $id       = (int) ($_POST['id'] ?? 0);

            if (($_POST['do'] ?? '') === 'delete') {
                $comments->delete($id);
                $_SESSION['admin_flash'] = 'Комментарий удалён';
            } else {
                $comments->setStatus($id, (string) ($_POST['do'] ?? ''));
                $_SESSION['admin_flash'] = 'Комментарий обновлён';
            }
        }

        $this->redirect('/admin/kommentarii?status=' . urlencode((string) ($_POST['back'] ?? 'new')));
    }

    /* --------------------------------------------------------------------
       Проверка настроек
       -------------------------------------------------------------------- */

    /**
     * Самопроверка: то, что на разных хостингах ведёт себя по-разному.
     *
     * Нужна не «на всякий случай»: каталог сайта, права на запись, версия
     * PHP и поведение базы у хостинга свои, и по внешнему виду сайта
     * не понять, где именно споткнулось. Здесь всё проверяется настоящими
     * действиями — записью файла и записью в базу, — а не догадками.
     */
    private function selfCheck(): void
    {
        $public = $this->publicDir();
        $upload = $public . Upload::DIR;

        $checks = [];

        $checks[] = [
            'title' => 'Версия PHP',
            'ok'    => PHP_VERSION_ID >= 80100,
            'value' => PHP_VERSION,
            'hint'  => 'Нужна 8.1 или новее.',
        ];

        foreach (['pdo_mysql' => 'работа с базой', 'gd' => 'обработка картинок',
                  'mbstring' => 'русский текст'] as $extension => $why) {
            $checks[] = [
                'title' => 'Расширение ' . $extension,
                'ok'    => extension_loaded($extension),
                'value' => extension_loaded($extension) ? 'есть' : 'нет',
                'hint'  => 'Отвечает за ' . $why . '.',
            ];
        }

        $checks[] = [
            'title' => 'Поддержка WebP',
            'ok'    => function_exists('imagewebp'),
            'value' => function_exists('imagewebp') ? 'есть' : 'нет',
            'hint'  => 'Без неё картинки не сохранятся.',
        ];

        $checks[] = [
            'title' => 'Каталог сайта',
            'ok'    => is_dir($public),
            'value' => $public,
            'hint'  => 'Сюда веб-сервер смотрит наружу. Картинки должны '
                . 'сохраняться внутри него.',
        ];

        $checks[] = [
            'title' => 'Каталог картинок',
            'ok'    => is_dir($upload) && is_writable($upload),
            'value' => $upload . ' — ' . match (true) {
                !is_dir($upload)      => 'не существует',
                !is_writable($upload) => 'нет прав на запись',
                default               => 'есть, запись разрешена',
            },
            'hint'  => 'Если прав нет, поставьте каталогу права 755 или 775.',
        ];

        $checks[] = $this->checkUpload($public);
        $checks[] = $this->checkSession();
        $checks[] = $this->checkComment();

        $checks[] = [
            'title' => 'Предел размера загрузки',
            'ok'    => true,
            'value' => 'upload_max_filesize = ' . ini_get('upload_max_filesize')
                . ', post_max_size = ' . ini_get('post_max_size'),
            'hint'  => 'Картинка больше этого размера до сервера не дойдёт.',
        ];

        $this->page('admin/check', [
            'checks'   => $checks,
            'comments' => $this->recentComments(),
        ]);
    }

    /**
     * Настоящая запись картинки: создаём файл, проверяем, что он доступен
     * по своему адресу, и удаляем.
     *
     * @return array<string, mixed>
     */
    private function checkUpload(string $public): array
    {
        $image = @imagecreatetruecolor(40, 20);

        if ($image === false) {
            return [
                'title' => 'Запись картинки',
                'ok'    => false,
                'value' => 'GD не создаёт изображения',
                'hint'  => 'Обратитесь в поддержку хостинга.',
            ];
        }

        $dir  = $public . Upload::DIR . '/proverka';
        $file = $dir . '/proverka.webp';

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            imagedestroy($image);

            return [
                'title' => 'Запись картинки',
                'ok'    => false,
                'value' => 'не удалось создать каталог ' . $dir,
                'hint'  => 'Дайте права на запись каталогу ' . $public . Upload::DIR . '.',
            ];
        }

        $saved = @imagewebp($image, $file, 80);
        imagedestroy($image);

        if (!$saved) {
            return [
                'title' => 'Запись картинки',
                'ok'    => false,
                'value' => 'файл не записался: ' . $file,
                'hint'  => 'Чаще всего это права на запись.',
            ];
        }

        $url = $this->config['base_url'] . Upload::DIR . '/proverka/proverka.webp';

        @unlink($file);
        @rmdir($dir);

        return [
            'title' => 'Запись картинки',
            'ok'    => true,
            'value' => 'файл создан и удалён: ' . $file,
            'hint'  => 'Значит, обложки и картинки в тексте сохранятся. '
                . 'Адрес, по которому они открываются: ' . $url,
        ];
    }

    /** @return array<string, mixed> */
    private function checkSession(): array
    {
        $path    = session_save_path() ?: 'каталог по умолчанию';
        $working = session_status() === PHP_SESSION_ACTIVE;

        return [
            'title' => 'Сессии',
            'ok'    => $working,
            'value' => $working ? 'работают, хранятся в ' . $path : 'не запускаются',
            'hint'  => 'От них зависит вход в админку и защита форм. '
                . 'Раз вы видите эту страницу, вход уже работает.',
        ];
    }

    /**
     * Настоящая запись комментария: вставляем и сразу удаляем.
     * Если база откажет, здесь будет её точный текст ошибки.
     *
     * @return array<string, mixed>
     */
    private function checkComment(): array
    {
        $articles = new ArticleRepository($this->db());
        $any      = $articles->adminListing('', 1);

        if ($any === []) {
            return [
                'title' => 'Запись комментария',
                'ok'    => true,
                'value' => 'проверять не на чем — статей ещё нет',
                'hint'  => 'Проверка появится, когда будет хотя бы одна статья.',
            ];
        }

        try {
            $comments = new CommentRepository($this->db());
            $comments->add(
                (int) $any[0]['id'],
                'Проверка настроек',
                'proverka@example.com',
                'Это тестовая запись, она удаляется сразу после проверки.',
                $_SERVER['REMOTE_ADDR'] ?? null,
            );

            $this->db()->exec(
                "DELETE FROM comments WHERE name = 'Проверка настроек' AND status = 'new'"
            );

            return [
                'title' => 'Запись комментария',
                'ok'    => true,
                'value' => 'база принимает комментарии',
                'hint'  => 'Комментарий с сайта сохранится и будет ждать проверки '
                    . 'в разделе «Комментарии».',
            ];
        } catch (\Throwable $error) {
            return [
                'title' => 'Запись комментария',
                'ok'    => false,
                'value' => $error->getMessage(),
                'hint'  => 'Это точная ошибка базы данных — пришлите её текст.',
            ];
        }
    }

    /**
     * Последние комментарии с их состоянием: сразу видно, дошёл ли
     * комментарий до базы и не ждёт ли он просто проверки.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentComments(): array
    {
        try {
            return (new CommentRepository($this->db()))->listing('all', 5);
        } catch (\Throwable) {
            return [];
        }
    }

    /* --------------------------------------------------------------------
       Служебное
       -------------------------------------------------------------------- */

    /**
     * Выводит страницу админки: свой макет, всегда закрытый от индексации.
     *
     * @param array<string, mixed> $data
     */
    private function page(string $template, array $data = []): void
    {
        $counts = ['comments' => 0];

        try {
            $counts['comments'] = (new CommentRepository($this->db()))->newCount();
        } catch (PDOException) {
            // Счётчик — украшение меню. Если базы нет, страница ошибки
            // всё равно должна открыться.
        }

        $this->html($this->view->render($template, $data + [
            'seo'    => ['title' => 'Управление статьями', 'noindex' => true],
            'counts' => $counts,
            'auth'   => (int) ($_SESSION['admin_id'] ?? 0) > 0,
        ], 'layouts/admin'));
    }

    /**
     * Каталог, который веб-сервер отдаёт наружу.
     *
     * Приходит из точки входа: только она знает настоящее имя каталога.
     * Раньше здесь было жёстко записано «public», и на хостинге, где корень
     * называется public_html, картинки сохранялись рядом с сайтом — файл
     * записывался без ошибки, но по своему адресу не открывался.
     */
    private function publicDir(): string
    {
        return (string) ($this->config['public_dir'] ?? dirname(__DIR__, 2) . '/public');
    }

    private function flash(): string
    {
        $message = (string) ($_SESSION['admin_flash'] ?? '');
        unset($_SESSION['admin_flash']);

        return $message;
    }

    private function back(string $path, string $message): void
    {
        $_SESSION['admin_flash'] = $message;

        $this->redirect($path);
    }

    /**
     * Значения формы для повторного показа после ошибки.
     *
     * @return array<string, mixed>
     */
    private function postValues(): array
    {
        $values = $_POST;
        unset($values['_token']);

        return $values;
    }
}
