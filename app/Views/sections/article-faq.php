<?php
/**
 * Вопросы и ответы под статьёй.
 *
 * Заводятся в админке при правке статьи, у каждой статьи свои. Если их нет,
 * секция не рисуется — см. вызов в pages/article.php.
 *
 * Вопрос выводится заголовком h3, а не строкой в раскрывающемся блоке.
 * Так он попадает в структуру страницы: поисковик видит, что на странице
 * отвечают именно на этот вопрос, и может показать её по нему. Ответ
 * идёт сразу под вопросом и ничем не закрыт.
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
                <div class="faq__item">
                    <h3 class="faq__question"><?= View::e((string) $item['question']) ?></h3>

                    <div class="faq__answer prose">
                        <?php
                        // Ответ можно написать без тегов вовсе — тогда это
                        // просто строка, и абзац вокруг неё нужно поставить
                        // самим, иначе текст прилипнет к вопросу.
                        $answer = Text::safeHtml((string) $item['answer']);

                        if (!preg_match('/<(p|ul|ol|blockquote)\b/i', $answer)) {
                            $answer = '<p>' . nl2br($answer) . '</p>';
                        }
                        ?>
                        <?= Text::newTab($answer) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
