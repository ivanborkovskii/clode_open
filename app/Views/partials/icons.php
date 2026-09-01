<?php
/**
 * Спрайт иконок.
 *
 * Подключается один раз в начале <body>, дальше иконки вставляются
 * через <use href="#i-name">. Один набор правил: линия 1.5, квадратные
 * концы, сетка 24×24, без заливок — иконки наследуют цвет текста.
 */
?>
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <g id="i-scatter" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M3 4h6v5H3zM15 4h6v5h-6zM3 15h6v5H3zM15 15h6v5h-6z"/>
            <path d="M11 6.5h2M11 17.5h2M6 11v2M18 11v2" stroke-dasharray="2 2"/>
        </g>

        <g id="i-funnel" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M3 4h18l-7 8v8l-4-2v-6z"/>
        </g>

        <g id="i-clock" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M12 7v5.5l3.5 2"/>
        </g>

        <g id="i-user-leave" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M10 11a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM3.5 20v-1.5A4.5 4.5 0 018 14h4"/>
            <path d="M16 17h5M18.5 14.5L21 17l-2.5 2.5"/>
        </g>

        <g id="i-chart" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M3.5 20h17M6.5 20v-6M11 20V8M15.5 20v-9M20 20V5"/>
        </g>

        <g id="i-chat" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M20.5 12.5c0 3.9-3.8 7-8.5 7-1.2 0-2.4-.2-3.4-.6L3.5 20.5l1.7-4A6.7 6.7 0 013.5 12.5c0-3.9 3.8-7 8.5-7s8.5 3.1 8.5 7z"/>
        </g>

        <g id="i-users" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 11a3 3 0 100-6 3 3 0 000 6zM3 19v-1a4 4 0 014-4h4a4 4 0 014 4v1"/>
            <path d="M16 5.3a3 3 0 010 5.4M17.5 14h.5a4 4 0 014 4v1"/>
        </g>

        <g id="i-cart" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M3 4h2.5l2.2 10.5h10L20 7H6.2"/>
            <circle cx="9.5" cy="19" r="1.4"/>
            <circle cx="17" cy="19" r="1.4"/>
        </g>

        <g id="i-phone" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M8.2 4.5l2 3.4-2 2c1 2.1 2.8 3.9 4.9 4.9l2-2 3.4 2-.6 3.1c-.2.9-1 1.5-1.9 1.4C10 18.6 5.4 14 4.6 7.9c-.1-.9.5-1.7 1.4-1.9z"/>
        </g>

        <g id="i-globe" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="8.5"/>
            <path d="M3.5 12h17M12 3.5c2.2 2.3 3.4 5.4 3.4 8.5S14.2 18.2 12 20.5c-2.2-2.3-3.4-5.4-3.4-8.5S9.8 5.8 12 3.5z"/>
        </g>

        <g id="i-box" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 3.5l8 4.2v8.6l-8 4.2-8-4.2V7.7z"/>
            <path d="M4 7.7l8 4.3 8-4.3M12 12v8.5"/>
        </g>

        <g id="i-code" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M8.5 8L4 12l4.5 4M15.5 8l4.5 4-4.5 4M13.5 5l-3 14"/>
        </g>

        <g id="i-shield" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 3.5l7 2.5v6c0 4-3 7.3-7 8.5-4-1.2-7-4.5-7-8.5V6z"/>
            <path d="M9 12l2 2 4-4"/>
        </g>

        <g id="i-arrow" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M14 5l7 7-7 7M21 12H3"/>
        </g>

        <?php // Та же стрелка, повёрнутая вверх — для кнопки «Наверх». ?>
        <g id="i-arrow-up" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M5 10l7-7 7 7M12 3v18"/>
        </g>

        <?php
        // Звезда для оценки статьи. Заливка задаётся в CSS: пустая звезда —
        // это та же фигура с fill: none и обводкой.
        ?>
        <g id="i-star" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
            <path d="M12 3.6l2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.5 9.8l5.9-.9z"/>
        </g>

        <g id="i-mail" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
            <path d="M3.5 6h17v12h-17z"/>
            <path d="m3.5 6.5 8.5 6.5 8.5-6.5"/>
        </g>

        <?php
        /* Знаки мессенджеров. Нарисованы в том же стиле, что и остальные
           иконки набора: сетка 24×24, линия, без заливки, цвет от текста.

           Телеграм — его самолётик. У MAX знак — круглое речевое облачко
           с коротким хвостиком слева внизу; здесь оно и нарисовано, линией
           чуть толще: у MAX контур знака жирный. Рядом со значком всегда
           стоит название, так что перепутать их не с чем. */
        ?>
        <g id="i-telegram" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
            <path d="M21.5 4.6 3.4 11.6c-.6.2-.6 1.1 0 1.3l4.4 1.5 1.7 5c.2.6.9.7 1.3.3l2.4-2.3 4.3 3.2c.5.4 1.2.1 1.3-.5l3.7-14.6c.2-.6-.4-1.1-1-.9z"/>
            <path d="m7.8 14.4 11.9-8.2-8.6 9.1"/>
        </g>

        <g id="i-max" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round">
            <path d="M12.4 3.8c-4.8 0-8.7 3.5-8.7 7.9 0 2.3 1.1 4.4 2.8 5.8-.1 1.9-.9 3.4-2.2 4.4 2.4-.1 4.4-.9 5.9-2.2.7.1 1.4.2 2.2.2 4.8 0 8.7-3.5 8.7-7.9s-3.9-8.2-8.7-8.2z"/>
        </g>
    </defs>
</svg>
