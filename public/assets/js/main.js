/**
 * Скрипты сайта.
 *
 * Три поведения: мобильное меню, переключение услуг и отправка формы.
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
     Форма заявки
     ------------------------------------------------------------------ */

  var form = document.querySelector('[data-form]');

  if (!form) {
    return;
  }

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
        showErrors(result.data.errors || {});
      })
      .catch(function () {
        // Сеть недоступна — отправляем формой, чтобы заявка не потерялась.
        setLoading(false);
        form.submit();
      });
  });

  function replaceWithSuccess() {
    var success = document.createElement('div');
    success.className = 'form__success';
    success.setAttribute('role', 'status');
    success.innerHTML =
      '<h3>Заявка отправлена</h3>' +
      '<p>Свяжемся с вами в рабочее время. Если вопрос срочный — можно позвонить напрямую.</p>';

    form.replaceWith(success);
  }
})();
