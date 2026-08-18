/**
 * Админка: подтверждение удаления, адрес из заголовка и кнопки разметки
 * над текстом статьи.
 *
 * Без скрипта админка тоже работает: адрес составится на сервере, теги
 * можно написать руками, а подтверждение удаления просто не появится.
 */

(function () {
  'use strict';

  /* ------------------------------------------------------------------
     Подтверждение необратимых действий
     ------------------------------------------------------------------ */

  document.querySelectorAll('[data-confirm]').forEach(function (node) {
    var handler = function (event) {
      if (!window.confirm(node.dataset.confirm)) {
        event.preventDefault();
      }
    };

    // Подтверждение висит либо на форме, либо на конкретной кнопке:
    // в одной форме бывает и «сохранить», и «удалить».
    node.addEventListener(node.tagName === 'FORM' ? 'submit' : 'click', handler);
  });

  /* ------------------------------------------------------------------
     Адрес статьи из заголовка
     ------------------------------------------------------------------ */

  var title = document.querySelector('[data-title]');
  var slug = document.querySelector('[data-slug]');

  if (title && slug) {
    // Подставляем, только пока адрес не трогали руками и статья новая:
    // у опубликованной статьи менять адрес нельзя — старый перестанет
    // открываться.
    var untouched = slug.value === '';

    slug.addEventListener('input', function () {
      untouched = false;
    });

    title.addEventListener('input', function () {
      if (untouched) {
        slug.value = translit(title.value);
      }
    });
  }

  var MAP = {
    а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'e', ж: 'zh', з: 'z',
    и: 'i', й: 'y', к: 'k', л: 'l', м: 'm', н: 'n', о: 'o', п: 'p', р: 'r',
    с: 's', т: 't', у: 'u', ф: 'f', х: 'h', ц: 'c', ч: 'ch', ш: 'sh',
    щ: 'shch', ъ: '', ы: 'y', ь: '', э: 'e', ю: 'yu', я: 'ya'
  };

  function translit(value) {
    return value
      .toLowerCase()
      .split('')
      .map(function (letter) {
        return MAP[letter] !== undefined ? MAP[letter] : letter;
      })
      .join('')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  /* ------------------------------------------------------------------
     Кнопки разметки
     ------------------------------------------------------------------ */

  var body = document.querySelector('[data-body]');
  var toolbar = document.querySelector('[data-toolbar]');

  if (!body || !toolbar) {
    return;
  }

  /** Заменяет выделенный фрагмент и оставляет курсор внутри вставленного. */
  function replaceSelection(before, after) {
    var start = body.selectionStart;
    var end = body.selectionEnd;
    var selected = body.value.slice(start, end);

    body.setRangeText(before + selected + after, start, end, 'end');
    body.focus();
  }

  toolbar.addEventListener('click', function (event) {
    var button = event.target.closest('button');

    if (!button) {
      return;
    }

    if (button.dataset.wrap) {
      var tag = button.dataset.wrap;
      replaceSelection('<' + tag + '>', '</' + tag + '>');
      return;
    }

    if (button.dataset.list) {
      var list = button.dataset.list;
      var start = body.selectionStart;
      var end = body.selectionEnd;
      var lines = body.value.slice(start, end).split('\n').filter(function (line) {
        return line.trim() !== '';
      });

      if (lines.length === 0) {
        lines = ['', ''];
      }

      var items = lines
        .map(function (line) {
          return '  <li>' + line.trim() + '</li>';
        })
        .join('\n');

      body.setRangeText('<' + list + '>\n' + items + '\n</' + list + '>', start, end, 'end');
      body.focus();
      return;
    }

    if (button.hasAttribute('data-link')) {
      var href = window.prompt('Адрес ссылки', 'https://');

      if (href) {
        replaceSelection('<a href="' + href.replace(/"/g, '&quot;') + '">', '</a>');
      }

      return;
    }

    if (button.hasAttribute('data-image')) {
      pickImage();
    }
  });

  /** Выбор файла и загрузка картинки прямо в текст статьи. */
  function pickImage() {
    var picker = document.createElement('input');
    picker.type = 'file';
    picker.accept = 'image/*';

    picker.addEventListener('change', function () {
      if (!picker.files || !picker.files[0]) {
        return;
      }

      var form = body.form;
      var data = new FormData();
      data.append('file', picker.files[0]);
      data.append('_token', form.elements._token.value);

      var placeholder = '[загружается картинка…]';
      var at = body.selectionStart;

      body.setRangeText(placeholder, at, body.selectionEnd, 'end');

      fetch('/admin/kartinka', { method: 'POST', body: data })
        .then(function (response) {
          return response.json();
        })
        .then(function (result) {
          var replacement;

          if (result.error) {
            window.alert(result.error);
            replacement = '';
          } else {
            var alt = window.prompt('Что на картинке? Это описание читают поисковики.', '') || '';

            replacement = '<figure>\n  <img src="' + result.path + '" alt="' +
              alt.replace(/"/g, '&quot;') + '" width="' + result.width +
              '" height="' + result.height + '" loading="lazy">\n</figure>';
          }

          body.value = body.value.replace(placeholder, replacement);
        })
        .catch(function () {
          window.alert('Картинку загрузить не удалось');
          body.value = body.value.replace(placeholder, '');
        });
    });

    picker.click();
  }
})();
