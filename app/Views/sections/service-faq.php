<?php
/**
 * Вопросы и ответы на странице услуги.
 *
 * От такого же блока под статьёй отличается одним: вопросы приходят
 * из файла с текстами услуги, а не из админки. Разметка и классы общие,
 * поэтому и стили общие — они в app.css.
 *
 * Ответ здесь обычный текст, без разметки: тексты услуг пишутся в файле,
 * а не вводятся через админку, и чистить нечего.
 *
 * Вопрос остаётся заголовком h3 внутри раскрывающегося блока: так он
 * попадает в структуру страницы, и поисковик видит, что отвечают именно
 * на него. Спрятанный ответ этому не мешает — содержимое <details>
 * поисковики читают целиком.
 *
 * @var array $faq title, items (question и answer)
 */

use App\Core\View;
?>
<section class="section section--tight" id="voprosy">
    <div class="container container--narrow">
        <h2 class="faq__title"><?= View::e($faq['title']) ?></h2>

        <div class="faq">
            <?php foreach ($faq['items'] as $item): ?>
                <details class="faq__item">
                    <summary class="faq__head">
                        <h3 class="faq__question"><?= View::e((string) $item['question']) ?></h3>
                    </summary>

                    <div class="faq__wrap">
                        <div class="faq__answer">
                            <p><?= View::e((string) $item['answer']) ?></p>
                        </div>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
