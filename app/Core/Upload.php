<?php
/**
 * Загрузка картинок для статей.
 *
 * Всё, что загружается, пересобирается заново средствами GD и сохраняется
 * в WebP. Это даёт две вещи сразу: картинки весят в несколько раз меньше,
 * а файл, который только притворяется картинкой, не сохранится — GD просто
 * не сможет его прочитать.
 */

declare(strict_types=1);

namespace App\Core;

final class Upload
{
    /** Каталог внутри public. Внутри — подкаталоги по месяцам. */
    public const DIR = '/assets/img/stati';

    /** Больше этой ширины на сайте картинка не показывается. */
    private const MAX_WIDTH = 1600;

    private const MAX_BYTES = 12 * 1024 * 1024;

    private const TYPES = [
        IMAGETYPE_JPEG => 'imagecreatefromjpeg',
        IMAGETYPE_PNG  => 'imagecreatefrompng',
        IMAGETYPE_WEBP => 'imagecreatefromwebp',
        IMAGETYPE_GIF  => 'imagecreatefromgif',
    ];

    /**
     * Сохраняет загруженный файл.
     *
     * @param  array<string, mixed> $file Элемент из $_FILES
     * @return array{path?:string, width?:int, height?:int, error?:string}
     */
    public static function image(array $file, string $publicRoot): array
    {
        $code = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($code === UPLOAD_ERR_NO_FILE) {
            return ['error' => 'Файл не выбран'];
        }

        if ($code !== UPLOAD_ERR_OK) {
            // Чаще всего это ограничение самого PHP на размер загрузки —
            // сообщение должно подсказывать, куда смотреть.
            return ['error' => 'Файл не загрузился. Возможно, он больше, чем разрешено '
                . 'настройками хостинга (upload_max_filesize)'];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');

        if (!is_uploaded_file($tmp)) {
            return ['error' => 'Файл не загрузился'];
        }

        if (filesize($tmp) > self::MAX_BYTES) {
            return ['error' => 'Картинка больше 12 МБ'];
        }

        $info = @getimagesize($tmp);
        $type = $info[2] ?? 0;

        if ($info === false || !isset(self::TYPES[$type])) {
            return ['error' => 'Это не картинка. Подойдут JPG, PNG, WebP или GIF'];
        }

        $source = @(self::TYPES[$type])($tmp);

        if ($source === false) {
            return ['error' => 'Картинку не удалось прочитать'];
        }

        $width  = imagesx($source);
        $height = imagesy($source);

        if ($width > self::MAX_WIDTH) {
            $height = (int) round($height * self::MAX_WIDTH / $width);
            $width  = self::MAX_WIDTH;

            $resized = imagescale($source, $width, $height);

            if ($resized !== false) {
                imagedestroy($source);
                $source = $resized;
            }
        }

        $dir = $publicRoot . self::DIR . '/' . date('Y-m');

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            imagedestroy($source);

            return ['error' => 'Нет прав на запись в каталог с картинками'];
        }

        $name = date('Ymd') . '-' . bin2hex(random_bytes(5)) . '.webp';
        $saved = @imagewebp($source, $dir . '/' . $name, 82);

        imagedestroy($source);

        if (!$saved) {
            return ['error' => 'Картинку не удалось сохранить'];
        }

        return [
            'path'   => self::DIR . '/' . date('Y-m') . '/' . $name,
            'width'  => $width,
            'height' => $height,
        ];
    }
}
