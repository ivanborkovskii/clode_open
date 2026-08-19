/**
 * Раздел «Статьи»: подсказки поиска и оценка статьи.
 *
 * Оба поведения — надстройка. Без скрипта поиск работает как обычная форма,
 * а звёзды — как обычные кнопки отправки: страница перезагружается,
 * результат тот же.
 */

(function () {
  'use strict';

  /* ------------------------------------------------------------------
     Подсказки при наборе
     ------------------------------------------------------------------ */

  var search = document.querySelector('[data-search]');

  if (search) {
    initSearch(search);
  }

  function initSearch(form) {
    var input = form.querySelector('[data-search-input]');
    var drop = form.querySelector('[data-search-drop]');

    if (!input || !drop) {
      return;
    }

    var timer = null;
    var lastQuery = '';
    var items = [];
    var current = -1;

    var close = function () {
      drop.hidden = true;
      drop.textContent = '';
      input.setAttribute('aria-expanded', 'false');
      items = [];
      current = -1;
    };

    var setCurrent = function (index) {
      if (items.length === 0) {
        return;
      }

      // Переход за край списка возвращает к другому его концу —
      // так стрелками можно ходить по кругу, не глядя на границы.
      current = (index + items.length) % items.length;

      items.forEach(function (item, i) {
        item.classList.toggle('is-current', i === current);
      });

      items[current].scrollIntoView({ block: 'nearest' });
    };

    var render = function (data) {
      drop.textContent = '';
      items = [];
      current = -1;

      if (data.items.length === 0) {
        var empty = document.createElement('p');
        empty.className = 'asearch__empty';
        empty.textContent = form.dataset.empty || 'Ничего не нашлось.';
        drop.appendChild(empty);
        drop.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        return;
      }

      var words = data.q.split(/\s+/).filter(function (word) {
        return word.length >= 2;
      });

      data.items.forEach(function (row) {
        var link = document.createElement('a');
        var title = document.createElement('span');
        var text = document.createElement('span');
        var category = document.createElement('span');

        link.className = 'asearch__item';
        link.href = row.href;
        link.setAttribute('role', 'option');

        title.className = 'asearch__item-title';
        title.appendChild(highlight(row.title, words));

        text.className = 'asearch__item-text';
        text.appendChild(highlight(row.excerpt, words));

        category.className = 'asearch__item-cat';
        category.textContent = row.category;

        link.appendChild(title);

        if (row.excerpt) {
          link.appendChild(text);
        }

        link.appendChild(category);
        drop.appendChild(link);
        items.push(link);
      });

      drop.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    };

    var request = function () {
      var query = input.value.trim();

      if (query.length < 2) {
        close();
        return;
      }

      if (query === lastQuery) {
        return;
      }

      lastQuery = query;

      fetch('/api/poisk-statey?q=' + encodeURIComponent(query), {
        headers: { 'X-Requested-With': 'fetch' }
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          // Пока шёл запрос, в поле могли дописать ещё букв —
          // тогда этот ответ уже не про то, что набрано сейчас.
          if (input.value.trim() === data.q) {
            render(data);
          }
        })
        .catch(close);
    };

    input.addEventListener('input', function () {
      window.clearTimeout(timer);
      // Запрос не на каждую букву: при быстром наборе это десяток
      // обращений к серверу вместо одного.
      timer = window.setTimeout(request, 200);
    });

    input.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        close();
        return;
      }

      if (drop.hidden || items.length === 0) {
        return;
      }

      if (event.key === 'ArrowDown') {
        event.preventDefault();
        setCurrent(current + 1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        setCurrent(current - 1);
      } else if (event.key === 'Enter' && current >= 0) {
        event.preventDefault();
        items[current].click();
      }
    });

    // Клик мимо подсказок закрывает список, но не мешает перейти
    // по самой подсказке.
    document.addEventListener('click', function (event) {
      if (!form.contains(event.target)) {
        close();
      }
    });

    input.addEventListener('focus', function () {
      if (input.value.trim().length >= 2) {
        lastQuery = '';
        request();
      }
    });
  }

  /**
   * Текст с выделенными совпадениями.
   * Собирается узлами, а не строкой разметки: в заголовке статьи
   * могут быть любые символы, и превращать их в HTML незачем.
   */
  function highlight(text, words) {
    var fragment = document.createDocumentFragment();

    if (!text) {
      return fragment;
    }

    if (words.length === 0) {
      fragment.appendChild(document.createTextNode(text));
      return fragment;
    }

    var pattern = words
      .map(function (word) {
        return word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      })
      .join('|');

    var parts = text.split(new RegExp('(' + pattern + ')', 'gi'));

    parts.forEach(function (part, index) {
      if (!part) {
        return;
      }

      // Части с нечётным номером — это то, что попало в скобки шаблона,
      // то есть сами совпадения.
      if (index % 2 === 1) {
        var mark = document.createElement('b');
        mark.textContent = part;
        fragment.appendChild(mark);
      } else {
        fragment.appendChild(document.createTextNode(part));
      }
    });

    return fragment;
  }

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
