<?php
/**
 * Вопросы и ответы под статьёй.
 *
 * Заводятся в админке при правке статьи, у каждой статьи свои. Если их нет,
 * секция не рисуется — см. вызов в pages/article.php.
 *
 * Вопрос остаётся заголовком h3 и внутри раскрывающегося блока: так он
 * попадает в структуру страницы, и поисковик видит, что здесь отвечают
 * именно на этот вопрос. Спрятанный ответ на это не влияет — содержимое
 * раскрывающихся блоков поисковики читают целиком.
 *
 * Сделано на <details>, а не на скрипте: без JavaScript вопросы всё равно
 * открываются, просто сразу, без плавности. Плавность добавляет
 * articles.js — он же следит, чтобы высота считалась по месту.
 *
 * Значок стрелки нарисован в CSS двумя границами: внутри <summary>
 * по правилам разметки может стоять либо обычный текст, либо один
 * заголовок — но не заголовок вместе с картинкой.
 *
 * Разметка ответа приходит из админки и чистится тем же способом, что
 * и текст статьи: остаются только разрешённые теги.
 *
 * @var array $faq   Вопросы: question и answer
 * @var array $texts Тексты раздела «Статьи»
 */

use App\Core\Text;
use App\Core\View;
?>
<section class="section section--tight" id="voprosy">
    <div class="container container--narrow">
        <h2 class="faq__title"><?= View::e($texts['article']['faq']) ?></h2>

        <div class="faq">
            <?php foreach ($faq as $item): ?>
                <details class="faq__item">
                    <summary class="faq__head">
                        <h3 class="faq__question"><?= View::e((string) $item['question']) ?></h3>
                    </summary>

                    <?php // Обёртка нужна для плавности: анимируется её высота,
                          // а у самого ответа остаются обычные отступы. ?>
                    <?php
                    // Ответ можно написать без тегов вовсе — тогда это просто
                    // строка, и абзац вокруг неё нужно поставить самим,
                    // иначе текст прилипнет к вопросу.
                    $answer = Text::safeHtml((string) $item['answer']);

                    if (!preg_match('/<(p|ul|ol|blockquote)\b/i', $answer)) {
                        $answer = '<p>' . nl2br($answer) . '</p>';
                    }
                    ?>
                    <div class="faq__wrap">
                        <div class="faq__answer prose"><?= Text::newTab($answer) ?></div>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
