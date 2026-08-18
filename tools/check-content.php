<?php
/**
 * Проверка текстов страниц.
 *
 * Ловит ошибки, которые не видит браузерный тест: один и тот же снимок
 * экрана в шапке страницы и ниже в кейсах, ссылки на несуществующие
 * файлы картинок, пустые обязательные поля.
 *
 * Запуск: php tools/check-content.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$checked = 0;

foreach (glob($root . '/config/content/*.php') as $file) {
    $name = basename($file);
    $page = require $file;

    if (!isset($page['hero'])) {
        continue;
    }

    $checked++;

    // Снимки в шапке страницы.
    $heroOrigins = [];

    foreach ($page['hero']['media']['shots'] ?? [] as $shot) {
        $img = $root . '/public/assets/img/hero/' . $shot['src'] . '.webp';

        if (!is_file($img)) {
            $errors[] = "{$name}: нет файла {$shot['src']}.webp";
        }

        if (!is_file($root . '/public/assets/img/hero/' . $shot['src'] . '-sm.webp')) {
            $errors[] = "{$name}: нет уменьшенной копии {$shot['src']}-sm.webp";
        }

        if (empty($shot['alt'])) {
            $errors[] = "{$name}: у снимка {$shot['src']} нет описания alt";
        }

        // Размеры в разметке должны совпадать с файлом, иначе вёрстка
        // дёргается при загрузке.
        if (is_file($img)) {
            [$w, $h] = getimagesize($img);

            if ($w !== $shot['w'] || $h !== $shot['h']) {
                $errors[] = "{$name}: у {$shot['src']} размеры в тексте {$shot['w']}×{$shot['h']}, "
                    . "а в файле {$w}×{$h}";
            }
        }

        if (empty($shot['origin'])) {
            $errors[] = "{$name}: у снимка {$shot['src']} не указано, из какого кадра он сделан";
        } else {
            $heroOrigins[] = $shot['origin'];
        }
    }

    // Галерея кейса должна показывать все существующие снимки проекта
    // и в порядке их номеров. Если добавить файл и забыть про текст,
    // снимок просто не появится на сайте — это ловится здесь.
    if (!empty($page['gallery']['shots'])) {
        $slug = str_replace(['case-', '.php'], '', $name);

        $files = glob($root . "/public/assets/img/cases/{$slug}-[0-9].webp");
        $onDisk = array_map(
            static fn (string $f): int => (int) preg_replace('~.*-(\d+)\.webp$~', '$1', $f),
            $files,
        );
        sort($onDisk);

        $listed = array_map(static fn (array $s): int => (int) $s['n'], $page['gallery']['shots']);

        if ($listed !== $onDisk) {
            $errors[] = "{$name}: в галерее кадры [" . implode(', ', $listed)
                . '], а в папке лежат [' . implode(', ', $onDisk) . ']';
        }

        foreach ($page['gallery']['shots'] as $shot) {
            if (empty($shot['alt'])) {
                $errors[] = "{$name}: у кадра {$shot['n']} в галерее нет описания alt";
            }

            foreach (['', '-sm'] as $suffix) {
                if (!is_file($root . "/public/assets/img/cases/{$slug}-{$shot['n']}{$suffix}.webp")) {
                    $errors[] = "{$name}: нет файла {$slug}-{$shot['n']}{$suffix}.webp";
                }
            }
        }
    }

    // Кейсы: тот же кадр не должен стоять и в шапке, и ниже.
    foreach ($page['cases']['items'] ?? [] as $case) {
        $shot = $case['slug'] . '-' . ($case['shot'] ?? 1);

        if (in_array($shot, $heroOrigins, true)) {
            $errors[] = "{$name}: кадр {$shot} стоит и в шапке страницы, и в кейсах";
        }

        foreach (['', '-sm'] as $suffix) {
            if (!is_file($root . "/public/assets/img/cases/{$shot}{$suffix}.webp")) {
                $errors[] = "{$name}: нет файла кейса {$shot}{$suffix}.webp";
            }
        }

        if (empty($case['alt'])) {
            $errors[] = "{$name}: у кейса {$case['slug']} нет описания alt";
        }
    }
}

echo "Проверено файлов с текстами: {$checked}\n";

if ($errors === []) {
    echo "Ошибок нет\n";
    exit(0);
}

echo "\nОшибки:\n  " . implode("\n  ", $errors) . "\n";
exit(1);
