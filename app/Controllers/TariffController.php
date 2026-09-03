<?php
/**
 * Тарифы. Пока один раздел — лицензии Битрикс24.
 *
 * Публиковать тарифы требует сам Битрикс24 от партнёров, поэтому страница
 * заведена отдельным адресом, а не абзацем внутри услуги: её должно быть
 * видно в меню и находить поиском.
 *
 * Адрес /tarify/bitriks24, а не /tarify: когда понадобится такая же
 * страница по amoCRM, она встанет рядом и адрес этой не изменится.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Schema;

final class TariffController extends Controller
{
    /** Адрес страницы. Нужен и здесь, и в карте сайта. */
    public const BITRIX = '/tarify/bitriks24';

    public function bitrix(): void
    {
        $page = $this->content('tarify-bitrix24');

        $this->html($this->view->render('tarify', [
            'styles' => ['css/pages.css'],
            'seo' => [
                'title'       => 'Тарифы Битрикс24: цены на лицензии в облаке',
                'description' => 'Стоимость лицензий Битрикс24: Бесплатный, Базовый '
                    . '2 490 ₽, Стандартный 6 990 ₽ и Профессиональный 13 990 ₽ в месяц. '
                    . 'Что входит в каждый тариф и сколько пользователей. '
                    . 'Лицензия и работы по внедрению оплачиваются отдельно.',
                'canonical'   => $this->url(self::BITRIX),
                'breadcrumbs' => [
                    ['label' => 'Главная',          'href' => '/'],
                    ['label' => 'Тарифы Битрикс24', 'href' => self::BITRIX],
                ],
                'jsonld' => [Schema::products(
                    $this->url(self::BITRIX),
                    'Битрикс24',
                    $page['plans']['items'],
                )],
            ],
            'page' => $page,
            'form' => $this->formFlash(),
        ]));
    }
}
