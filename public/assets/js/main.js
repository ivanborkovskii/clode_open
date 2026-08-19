/**
 * Скрипты сайта.
 *
 * Мобильное меню, переключение услуг, просмотр картинок и отправка форм.
 * Всё содержимое доступно и без JavaScript — скрипт только улучшает работу.
 */

(function () {
  'use strict';

  /* ------------------------------------------------------------------
     Мобильное меню
     ------------------------------------------------------------------ */

  var burger = document.querySelector('[data-menu-toggle]');
  var menu = document.getElementById('mobile-menu');

  if (burger && menu) {
    var setMenu = function (open) {
      burger.setAttribute('aria-expanded', String(open));
      burger.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
      menu.dataset.open = String(open);
      document.body.dataset.menuOpen = String(open);
    };

    burger.addEventListener('click', function () {
      setMenu(burger.getAttribute('aria-expanded') !== 'true');
    });

    // Переход по ссылке внутри меню закрывает его.
    menu.addEventListener('click', function (event) {
      if (event.target.closest('a')) {
        setMenu(false);
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && menu.dataset.open === 'true') {
        setMenu(false);
        burger.focus();
      }
    });

    // При возврате на десктоп меню не должно остаться открытым.
    window.matchMedia('(min-width: 1024px)').addEventListener('change', function (event) {
      if (event.matches) {
        setMenu(false);
      }
    });
  }

  /* ------------------------------------------------------------------
     Кнопка «Наверх»
     ------------------------------------------------------------------ */

  var toTop = document.querySelector('[data-to-top]');

  if (toTop) {
    var shown = false;

    var updateToTop = function () {
      // Порог — один экран: кнопка появляется, когда первый экран
      // прокручен и вернуться к меню одним движением уже нельзя.
      var visible = window.pageYOffset > window.innerHeight;

      // Класс трогаем только при смене состояния: обработчик прокрутки
      // вызывается десятки раз в секунду.
      if (visible !== shown) {
        shown = visible;
        toTop.classList.toggle('is-on', visible);
      }
    };

    updateToTop();
    window.addEventListener('scroll', updateToTop, { passive: true });
    window.addEventListener('resize', updateToTop);
  }

  /* ------------------------------------------------------------------
     Услуги: вкладки на десктопе, аккордеон на мобильном.
     Разметка одна, различается только раскладка в CSS.
     ------------------------------------------------------------------ */

  document.querySelectorAll('[data-tabs]').forEach(function (group) {
    var tabs = Array.prototype.slice.call(group.querySelectorAll('[data-tab]'));
    var panels = Array.prototype.slice.call(group.querySelectorAll('[data-panel]'));

    var activate = function (index) {
      tabs.forEach(function (tab, i) {
        tab.setAttribute('aria-selected', String(i === index));
        panels[i].hidden = i !== index;
      });
    };

    tabs.forEach(function (tab, index) {
      tab.addEventListener('click', function () {
        activate(index);
      });

      // Стрелки переключают пункты — привычное поведение для вкладок.
      tab.addEventListener('keydown', function (event) {
        var step = event.key === 'ArrowDown' || event.key === 'ArrowRight' ? 1
          : event.key === 'ArrowUp' || event.key === 'ArrowLeft' ? -1 : 0;

        if (step === 0) {
          return;
        }

        event.preventDefault();

        var next = (index + step + tabs.length) % tabs.length;
        activate(next);
        tabs[next].focus();
      });
    });
  });

  /* ------------------------------------------------------------------
     Просмотр картинки во весь экран

     Ссылка на картинку с атрибутом data-zoom открывается не переходом
     на другой адрес, а поверх страницы. Клик по картинке увеличивает её
     до настоящего размера, повторный клик возвращает обратно.

     Так же открываются картинки внутри области data-zoom-area — это текст
     статьи. Там картинки вставлены автором обычными тегами, без ссылок,
     и оборачивать каждую в ссылку значило бы менять текст статьи.

     Если браузер не умеет модальные окна, обработчик не ставится
     и ссылка работает как обычная — картинка откроется этой же вкладкой.
     ------------------------------------------------------------------ */

  var zoomLinks = document.querySelectorAll('[data-zoom]');
  var zoomArea = document.querySelector('[data-zoom-area]');

  if ((zoomLinks.length || zoomArea) && typeof HTMLDialogElement !== 'undefined'
      && HTMLDialogElement.prototype.showModal) {
    var viewer = document.createElement('dialog');
    viewer.className = 'viewer';
    viewer.innerHTML =
      '<div class="viewer__bar">' +
        '<span>' +
          '<span class="viewer__title" data-viewer-title></span> ' +
          '<span class="viewer__hint">нажмите на картинку, чтобы увеличить</span>' +
        '</span>' +
        '<button class="viewer__close" type="button" aria-label="Закрыть">&times;</button>' +
      '</div>' +
      '<div class="viewer__scroll" data-viewer-scroll>' +
        '<img class="viewer__img" alt="" data-viewer-img>' +
      '</div>';

    document.body.appendChild(viewer);

    var viewerScroll = viewer.querySelector('[data-viewer-scroll]');
    var viewerImg = viewer.querySelector('[data-viewer-img]');
    var viewerTitle = viewer.querySelector('[data-viewer-title]');

    var setZoom = function (on, point) {
      viewer.dataset.zoomed = String(on);

      if (!on) {
        return;
      }

      // Прокручиваем к той точке, по которой человек кликнул, —
      // иначе после увеличения он оказывается в левом верхнем углу.
      var x = point ? point.x : 0.5;
      var y = point ? point.y : 0.5;

      viewerScroll.scrollLeft = viewerImg.offsetWidth * x - viewerScroll.clientWidth / 2;
      viewerScroll.scrollTop = viewerImg.offsetHeight * y - viewerScroll.clientHeight / 2;
    };

    var closeViewer = function () {
      viewer.close();
    };

    var openViewer = function (src, title) {
      viewerImg.src = src;
      viewerImg.alt = title;
      viewerTitle.textContent = title;

      setZoom(false);
      viewer.showModal();
      document.body.dataset.viewerOpen = 'true';
    };

    zoomLinks.forEach(function (link) {
      link.addEventListener('click', function (event) {
        event.preventDefault();
        openViewer(link.href, link.dataset.zoom || '');
      });
    });

    if (zoomArea) {
      // Картинки текста статьи. Обработчик один на всю область: сколько
      // их в статье и какие — заранее неизвестно.
      zoomArea.querySelectorAll('img').forEach(function (image) {
        // С клавиатуры картинка тоже должна открываться, а обычный <img>
        // фокус не принимает.
        image.tabIndex = 0;
        image.title = 'Нажмите, чтобы увеличить';
      });

      zoomArea.addEventListener('click', function (event) {
        var image = event.target.closest('img');

        // Картинка внутри ссылки ведёт по этой ссылке — так задумал автор.
        if (!image || image.closest('a')) {
          return;
        }

        openViewer(image.currentSrc || image.src, image.alt || '');
      });

      zoomArea.addEventListener('keydown', function (event) {
        var image = event.target.closest('img');

        if (!image || image.closest('a') || (event.key !== 'Enter' && event.key !== ' ')) {
          return;
        }

        event.preventDefault();
        openViewer(image.currentSrc || image.src, image.alt || '');
      });
    }

    viewerImg.addEventListener('click', function (event) {
      if (viewer.dataset.zoomed === 'true') {
        setZoom(false);
        return;
      }

      var box = viewerImg.getBoundingClientRect();

      setZoom(true, {
        x: (event.clientX - box.left) / box.width,
        y: (event.clientY - box.top) / box.height
      });
    });

    viewer.querySelector('.viewer__close').addEventListener('click', closeViewer);

    // Клик мимо картинки закрывает просмотр.
    viewerScroll.addEventListener('click', function (event) {
      if (event.target === viewerScroll) {
        closeViewer();
      }
    });

    // Событие close срабатывает и на кнопке, и на клавише Escape,
    // поэтому снимаем блокировку прокрутки страницы в одном месте.
    viewer.addEventListener('close', function () {
      document.body.dataset.viewerOpen = 'false';
      viewerImg.removeAttribute('src');
    });
  }

  /* ------------------------------------------------------------------
     Форма заявки
     ------------------------------------------------------------------ */

  // На странице статьи форм две — комментарий и заявка. Поэтому каждая
  // настраивается отдельно: раньше скрипт брал только первую на странице,
  // и вторая отправлялась перезагрузкой.
  document.querySelectorAll('[data-form]').forEach(initForm);

  function initForm(form) {
    // В разметке у полей стоят required, и без скрипта форму проверяет сам
    // браузер — так согласие на обработку данных нельзя обойти, даже если
    // скрипт не загрузился. Когда скрипт работает, встроенную проверку
    // выключаем: ошибки показываются в оформлении сайта, ответом сервера.
    form.noValidate = true;

    var button = form.querySelector('[data-submit]');
    var label = form.querySelector('[data-submit-label]');
    var labelText = label ? label.textContent : '';

    var status = form.querySelector('[data-form-status]');

    var showErrors = function (errors) {
      // Общая ошибка формы: устаревшая сессия или сбой сохранения на сервере.
      // Без этого при отправке через fetch человек не увидел бы ничего.
      if (status) {
        status.textContent = errors._form || '';
        status.hidden = !errors._form;
      }

      form.querySelectorAll('[data-error-for]').forEach(function (node) {
        var field = node.dataset.errorFor;
        var input = form.elements[field];

        node.textContent = errors[field] || '';

        if (input) {
          if (errors[field]) {
            input.setAttribute('aria-invalid', 'true');
          } else {
            input.removeAttribute('aria-invalid');
          }
        }
      });

      // Фокус на первое поле с ошибкой — не приходится искать её глазами.
      var first = form.querySelector('[aria-invalid="true"]');

      if (first) {
        first.focus({ preventScroll: false });
      } else if (status && !status.hidden) {
        // Общая ошибка ни к какому полю не относится — подводим к ней сами,
        // иначе она может оказаться выше видимой части страницы.
        status.scrollIntoView({ block: 'center' });
      }
    };

    var setLoading = function (loading) {
      if (!button) {
        return;
      }

      button.setAttribute('aria-busy', String(loading));

      if (label) {
        label.textContent = loading ? 'Отправляем…' : labelText;
      }
    };

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      showErrors({});
      setLoading(true);

      // Чистим ловушку для ботов перед отправкой: если автозаполнение
      // браузера всё-таки добралось до неё, заявка живого человека
      // не должна из-за этого попасть под подозрение.
      var trap = form.querySelector('[data-trap]');

      if (trap) {
        trap.value = '';
      }

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'fetch' }
      })
        .then(function (response) {
          return response.json().then(function (data) {
            return { ok: response.ok, data: data };
          });
        })
        .then(function (result) {
          if (result.ok && result.data.status === 'success') {
            replaceWithSuccess();
            return;
          }

          setLoading(false);

          var errors = result.data.errors || {};

          // Сервер может ответить ошибкой без подробностей — например,
          // если отказала база. Без этой строки форма молча ничего
          // не делала бы, и человек решил бы, что кнопка сломана.
          if (Object.keys(errors).length === 0) {
            errors._form = 'Отправить не удалось из-за ошибки на сервере. '
              + 'Попробуйте ещё раз или напишите нам.';
          }

          showErrors(errors);
        })
        .catch(function () {
          // Сеть недоступна — отправляем формой, чтобы заявка не потерялась.
          setLoading(false);
          form.submit();
        });
    });

    // Текст ответа приходит из разметки формы: у заявки и у комментария
    // он разный, а держать его в скрипте значит хранить одни и те же
    // слова в двух местах.
    function replaceWithSuccess() {
      var success = document.createElement('div');
      var title = document.createElement('h3');
      var text = document.createElement('p');

      success.className = 'form__success';
      success.setAttribute('role', 'status');

      title.textContent = form.dataset.successTitle || 'Отправлено';
      text.textContent = form.dataset.successText || '';

      success.appendChild(title);
      success.appendChild(text);

      form.replaceWith(success);
    }
  }
})();
