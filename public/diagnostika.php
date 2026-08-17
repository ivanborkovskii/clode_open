<?php
/**
 * Диагностика окружения. Временный файл.
 *
 * Кладётся в public_html, открывается в браузере, показывает, что именно
 * не работает на хостинге. Ни от чего в проекте не зависит — работает,
 * даже если сам сайт не запускается.
 *
 * УДАЛИТЬ ПОСЛЕ ПРОВЕРКИ: файл показывает пути на сервере.
 */

declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

$root = dirname(__DIR__);   // уровень выше public_html
$rows = [];

/** @param string|bool $value */
function row(array &$rows, string $name, bool $ok, $value, string $hint = ''): void
{
    $rows[] = ['name' => $name, 'ok' => $ok, 'value' => (string) $value, 'hint' => $hint];
}

// --- 1. PHP -----------------------------------------------------------------
$phpOk = PHP_VERSION_ID >= 80100;
row($rows, 'Версия PHP', $phpOk, PHP_VERSION,
    $phpOk ? '' : 'Нужна 8.1 или новее. Меняется в панели хостинга.');

// --- 2. Каталоги ------------------------------------------------------------
foreach (['app', 'config', 'storage'] as $dir) {
    $path = $root . '/' . $dir;
    row($rows, "Папка {$dir}", is_dir($path), $path,
        is_dir($path) ? '' : 'Не найдена. Должна лежать рядом с public_html, а не внутри.');
}

// --- 3. Запись в лог заявок -------------------------------------------------
$logDir = $root . '/storage/logs';

if (!is_dir($logDir)) {
    row($rows, 'Папка storage/logs', false, $logDir, 'Папки нет — создайте её.');
} else {
    $probe   = $logDir . '/probe.tmp';
    $written = @file_put_contents($probe, 'test');
    $canWrite = $written !== false;
    @unlink($probe);

    row($rows, 'Запись в storage/logs', $canWrite,
        $canWrite ? 'работает' : 'ЗАПРЕЩЕНА',
        $canWrite ? '' : 'Поставьте папке права 775, затем 777.');

    $leads = $logDir . '/leads.log';
    row($rows, 'Файл leads.log', true,
        is_file($leads) ? 'есть, ' . filesize($leads) . ' байт' : 'ещё не создан',
        is_file($leads) ? '' : 'Появится после первой успешной заявки.');
}

// --- 4. Сессии --------------------------------------------------------------
// Без рабочих сессий не проходит проверка защиты формы, и заявки отклоняются.
// Сайт хранит сессии внутри проекта — проверяем именно этот каталог.
$sessDir = $root . '/storage/sessions';

if (!is_dir($sessDir)) {
    @mkdir($sessDir, 0775, true);
}

$sessDirOk = is_dir($sessDir) && is_writable($sessDir);

row($rows, 'Папка storage/sessions', $sessDirOk, $sessDir,
    $sessDirOk ? 'создаётся и доступна для записи' : 'Создайте папку sessions в storage и дайте права 775.');

if ($sessDirOk) {
    session_save_path($sessDir);
}

$sessionOk = false;

if (session_status() === PHP_SESSION_NONE) {
    $sessionOk = @session_start();
}

$sessionNote = 'каталог сессий: ' . (session_save_path() ?: '(по умолчанию)');

if ($sessionOk) {
    $_SESSION['probe'] = 'ok';
    session_write_close();
    // Перечитываем с диска: запись в массив ещё не значит, что файл сохранился.
    session_start();
    $sessionOk = ($_SESSION['probe'] ?? '') === 'ok';
}

row($rows, 'Сессии', $sessionOk, $sessionOk ? 'работают' : 'НЕ РАБОТАЮТ',
    $sessionOk ? $sessionNote : 'Без сессий форма отклоняет все заявки: не проходит защита от подделки запроса.');

// --- 5. Почта ---------------------------------------------------------------
$mailExists = function_exists('mail');
row($rows, 'Функция mail()', $mailExists, $mailExists ? 'доступна' : 'ОТКЛЮЧЕНА',
    $mailExists ? '' : 'Хостинг запретил отправку почты из PHP.');

// --- 6. Маршрутизация -------------------------------------------------------
$htaccess = __DIR__ . '/.htaccess';
row($rows, 'Файл .htaccess', is_file($htaccess), is_file($htaccess) ? 'на месте' : 'НЕ НАЙДЕН',
    is_file($htaccess) ? '' : 'Без него работает только главная страница.');

$rewrite = in_array('mod_rewrite', function_exists('apache_get_modules') ? apache_get_modules() : [], true);
row($rows, 'mod_rewrite', $rewrite || !function_exists('apache_get_modules'),
    $rewrite ? 'включён' : (function_exists('apache_get_modules') ? 'ВЫКЛЮЧЕН' : 'проверить нельзя'),
    $rewrite || !function_exists('apache_get_modules') ? '' : 'Без него адреса вроде /privacy не откроются.');

$index = __DIR__ . '/index.php';
row($rows, 'Файл index.php', is_file($index), is_file($index) ? 'на месте' : 'НЕ НАЙДЕН',
    is_file($index) ? '' : 'Содержимое папки public должно лежать прямо в public_html.');

// --- 7. Что пришло с формы --------------------------------------------------
$posted = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : null;

$failed = array_filter($rows, static fn (array $r): bool => !$r['ok']);
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Диагностика сайта</title>
<style>
  body { margin: 0; padding: 24px; background: #f4f4f6; color: #16161a;
         font: 15px/1.5 system-ui, sans-serif; }
  .wrap { max-width: 780px; margin: 0 auto; }
  h1 { font-size: 22px; margin: 0 0 4px; }
  .lead { color: #63636b; margin: 0 0 24px; }
  .verdict { padding: 14px 16px; border-radius: 4px; margin-bottom: 24px; font-weight: 600; }
  .verdict.good { background: #e6f4ec; color: #14532d; }
  .verdict.bad  { background: #fdecec; color: #7f1d1d; }
  table { width: 100%; border-collapse: collapse; background: #fff;
          border: 1px solid #e0e0e6; border-radius: 4px; overflow: hidden; }
  th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #eeeef2;
           vertical-align: top; }
  th { background: #fafafc; font-size: 13px; color: #63636b; }
  tr:last-child td { border-bottom: 0; }
  .st { font-weight: 700; white-space: nowrap; }
  .ok { color: #14803c; }
  .no { color: #c02626; }
  .val { font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 13px;
         word-break: break-all; }
  .hint { color: #c02626; font-size: 13px; margin-top: 4px; }
  /* У пройденных проверок пояснение — просто уточнение, а не ошибка. */
  .hint--ok { color: #63636b; }
  form { margin-top: 24px; padding: 16px; background: #fff;
         border: 1px solid #e0e0e6; border-radius: 4px; }
  button { padding: 9px 16px; border: 0; border-radius: 3px; background: #1e96f0;
           color: #fff; font: inherit; font-weight: 600; cursor: pointer; }
  pre { background: #16161a; color: #e8e8ea; padding: 14px; border-radius: 4px;
        overflow-x: auto; font-size: 13px; }
  .warn { margin-top: 24px; padding: 14px 16px; background: #fff7e6;
          border: 1px solid #f0d9a8; border-radius: 4px; }
</style>
</head>
<body>
<div class="wrap">
  <h1>Диагностика сайта</h1>
  <p class="lead">Проверка окружения на хостинге. Покажите этот результат разработчику.</p>

  <?php if ($failed === []): ?>
    <div class="verdict good">Всё в порядке — окружение готово к работе формы.</div>
  <?php else: ?>
    <div class="verdict bad">Найдено проблем: <?= count($failed) ?>. Смотрите красные строки.</div>
  <?php endif; ?>

  <table>
    <tr><th>Что проверяем</th><th>Состояние</th><th>Значение</th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td class="st <?= $r['ok'] ? 'ok' : 'no' ?>"><?= $r['ok'] ? 'OK' : 'ОШИБКА' ?></td>
        <td>
          <span class="val"><?= htmlspecialchars($r['value']) ?></span>
          <?php if ($r['hint'] !== ''): ?>
            <div class="hint <?= $r['ok'] ? 'hint--ok' : '' ?>"><?= htmlspecialchars($r['hint']) ?></div>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <form method="post">
    <p><strong>Проверка приёма данных.</strong> Нажмите кнопку — сервер покажет, дошли ли поля формы.</p>
    <input type="hidden" name="proverka" value="da">
    <button type="submit">Отправить проверочные данные</button>

    <?php if ($posted !== null): ?>
      <p style="margin-top:16px"><strong>Сервер получил:</strong></p>
      <pre><?= htmlspecialchars(print_r($posted, true)) ?></pre>
      <p><?= $posted === [] ? 'Пусто — хостинг не передаёт данные форм в PHP.' : 'Данные форм доходят нормально.' ?></p>
    <?php endif; ?>
  </form>

  <div id="route-box" style="margin-top:24px;padding:16px;background:#fff;
       border:1px solid #e0e0e6;border-radius:4px">
    <p><strong>Проверка адреса заявки.</strong> <span id="route-result">проверяем…</span></p>
  </div>

  <div class="warn">
    <strong>Удалите этот файл после проверки.</strong>
    Он показывает пути на сервере, посторонним это видеть незачем.
  </div>
</div>

<script>
// Обращаемся к /zayavka так же, как это делает форма на сайте.
// Без токена сервер обязан ответить JSON с ошибкой — это доказывает,
// что адрес доходит до PHP. Любой другой ответ означает, что заявка
// до обработчика не добирается.
(function () {
  var out = document.getElementById('route-result');

  fetch('/zayavka', {
    method: 'POST',
    body: new FormData(),
    headers: { 'X-Requested-With': 'fetch' }
  })
    .then(function (response) {
      return response.text().then(function (text) {
        return { code: response.status, text: text };
      });
    })
    .then(function (r) {
      var json = null;

      try { json = JSON.parse(r.text); } catch (e) { /* ответ не JSON */ }

      if (json && json.status === 'error') {
        out.innerHTML = '<span style="color:#14803c;font-weight:700">OK</span> — '
          + 'адрес /zayavka работает, PHP отвечает. Код ' + r.code + '.';
        return;
      }

      out.innerHTML = '<span style="color:#c02626;font-weight:700">ОШИБКА</span> — '
        + 'вместо ответа обработчика пришёл код ' + r.code
        + '. Заявки до PHP не доходят.<pre>'
        + r.text.slice(0, 400).replace(/[<&]/g, function (c) {
            return c === '<' ? '&lt;' : '&amp;';
          })
        + '</pre>';
    })
    .catch(function (e) {
      out.innerHTML = '<span style="color:#c02626;font-weight:700">ОШИБКА</span> — '
        + 'запрос не удался: ' + e.message;
    });
})();
</script>
</body>
</html>
