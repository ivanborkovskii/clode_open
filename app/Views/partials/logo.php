<?php
/**
 * Логотип: знак-плитка и название компании.
 *
 * Отдельным файлом, потому что стоит в двух местах — в шапке и в подвале,
 * и оба раза одинаково.
 *
 * Знак повторяет иконку вкладки (favicon.svg) — те же штрихи, та же точка,
 * та же сетка 32×32. Отличается только цвет подложки: у иконки он
 * --ink-900, а шапка и подвал сами такого же цвета, и квадрат на них
 * бесследно растворился бы. Поэтому здесь подложка на тон светлее.
 *
 * Название набрано в два цвета: хвост — акцентным синим. Из какого
 * места оно делится, решает config/app.php, а не этот файл.
 *
 * @var array $config
 */

use App\Core\View;

$company = $config['company'];
$brand   = (string) $company['brand'];
$accent  = (string) ($company['brand_accent'] ?? '');

// Если хвост вдруг не совпал с концом названия — красим всё одним цветом.
// Логотип без второго цвета выглядит скромнее, чем с обрезанным словом.
$head = $accent !== '' && str_ends_with($brand, $accent)
    ? substr($brand, 0, -strlen($accent))
    : $brand;
$tail = $head === $brand ? '' : $accent;
?>
<a class="logo" href="/">
    <svg class="logo__mark" viewBox="0 0 32 32" width="36" height="36" aria-hidden="true">
        <rect width="32" height="32" fill="#142c5e"/>
        <path d="M6 22L12 10M14 22L20 10" stroke="#1e96f0" stroke-width="3" stroke-linecap="square"/>
        <circle cx="25" cy="12" r="3" fill="#1e96f0"/>
    </svg>
    <span class="logo__name"><?= View::e($head) ?><?php if ($tail !== ''): ?><span class="logo__accent"><?= View::e($tail) ?></span><?php endif; ?></span>
</a>
