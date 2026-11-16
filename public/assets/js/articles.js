/**
 * Раздел «Статьи»: ответы на комментарии и оценка статьи.
 *
 * Подсказки поиска здесь больше не живут: такая же строка поиска стоит
 * в шапке на всех страницах сайта, и общий механизм лежит в main.js.
 * Эта строка отличается от остальных только адресом, по которому
 * спрашивает подсказки, — он записан в разметке.
 *
 * Всё это надстройка. Без скрипта звёзды работают как обычные кнопки
 * отправки, а форма ответа остаётся на своём месте под списком.
 */

(function () {
  'use strict';

  /** Число со словом в нужной форме: 1 оценка, 2 оценки, 5 оценок. */
  function plural(count, one, few, many) {
    var mod100 = count % 100;
    var mod10 = count % 10;

    if (mod100 >= 11 && mod100 <= 14) {
      return many;
    }

    if (mod10 === 1) {
      return one;
    }

    return mod10 >= 2 && mod10 <= 4 ? few : many;
  }

  /* ------------------------------------------------------------------
     Ответ на комментарий

     Форма на странице одна: по нажатию «Ответить» она переезжает под
     нужный комментарий. Вторая форма в разметке означала бы второй
     набор полей и второй результат отправки — а отвечают по одному.
     ------------------------------------------------------------------ */

  var commentForm = document.querySelector('[data-comment-form]');

  if (commentForm) {
    initReplies(commentForm);
  }

  function initReplies(card) {
    var field = card.querySelector('[data-reply-field]');
    var note = card.querySelector('[data-reply-note]');
    var label = card.querySelector('[data-reply-label]');
    var title = card.querySelector('[data-form-title]');
    var cancel = card.querySelector('[data-reply-cancel]');

    // Форму нужно возвращать на место, поэтому запоминаем, где она стояла.
    var home = document.createElement('div');
    card.parentNode.insertBefore(home, card);

    if (!field || !note) {
      return;
    }

    var titleText = title ? title.textContent.trim() : '';

    var reset = function () {
      field.value = '';
      note.hidden = true;
      home.parentNode.insertBefore(card, home);

      if (title) {
        title.textContent = titleText;
      }
    };

    document.querySelectorAll('[data-reply]').forEach(function (link) {
      link.addEventListener('click', function (event) {
        event.preventDefault();

        var comment = link.closest('.comment');

        field.value = link.dataset.reply;
        note.hidden = false;

        if (label) {
          label.textContent = (card.dataset.answering || '') + ' ' + link.dataset.replyName;
        }

        if (title && card.dataset.replyTitle) {
          title.textContent = card.dataset.replyTitle;
        }

        // Форма встаёт сразу под тем комментарием, на который отвечают:
        // так видно, к чему относится ответ.
        if (comment) {
          comment.appendChild(card);
        }

        var input = card.querySelector('#c-body');

        if (input) {
          input.focus({ preventScroll: true });
        }

        card.scrollIntoView({ block: 'center' });
      });
    });

    if (cancel) {
      cancel.addEventListener('click', function (event) {
        event.preventDefault();
        reset();
        card.scrollIntoView({ block: 'center' });
      });
    }
  }

  /* ------------------------------------------------------------------
     Оценка статьи
     ------------------------------------------------------------------ */

  var rating = document.querySelector('[data-rating]');

  if (rating) {
    initRating(rating);
  }

  function initRating(form) {
    var stars = Array.prototype.slice.call(form.querySelectorAll('[data-star]'));
    var summary = form.querySelector('[data-rating-summary]');

    var paint = function (value) {
      stars.forEach(function (star) {
        star.classList.toggle('is-on', Number(star.dataset.star) <= value);
        star.setAttribute('aria-pressed', String(Number(star.dataset.star) === value));
      });
    };

    stars.forEach(function (star) {
      star.addEventListener('click', function (event) {
        event.preventDefault();

        var value = Number(star.dataset.star);
        var data = new FormData(form);
        data.set('value', String(value));

        paint(value);

        fetch(form.action, {
          method: 'POST',
          body: data,
          headers: { 'X-Requested-With': 'fetch' }
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (result) {
            if (result.status !== 'ok' || !summary) {
              return;
            }

            summary.textContent = '';

            var average = document.createElement('b');
            var votes = document.createElement('span');

            average.textContent = String(result.summary.average).replace('.', ',');
            votes.textContent = result.summary.votes + ' ' + plural(
              result.summary.votes, 'оценка', 'оценки', 'оценок'
            );

            summary.appendChild(average);
            summary.appendChild(votes);

            var lead = form.parentNode.querySelector('.rating__lead');

            if (lead) {
              lead.textContent = 'Спасибо, ваша оценка учтена.';
            }
          })
          .catch(function () {
            // Сеть недоступна — отправляем обычной формой, чтобы оценка
            // всё-таки дошла.
            form.submit();
          });
      });
    });
  }

})();
