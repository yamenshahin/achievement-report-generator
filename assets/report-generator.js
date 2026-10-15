(function () {
  'use strict';

  const root = document.querySelector('.arg-app');
  if (!root) return;

  const defaults = {};
  root.querySelectorAll('[data-report-field]').forEach((input) => {
    defaults[input.dataset.reportField] = input.value;
  });

  function syncField(input) {
    const key = input.dataset.reportField;
    const value = input.value.trim();
    root.querySelectorAll('[data-report-output="' + key + '"]').forEach((output) => {
      output.textContent = value || '—';
      if (output.hasAttribute('data-empty-hide')) {
        output.classList.toggle('is-empty', !value);
      }
    });
  }

  function syncAll() {
    root.querySelectorAll('[data-report-field]').forEach(syncField);
  }

  root.addEventListener('input', (event) => {
    if (event.target.matches('[data-report-field]')) syncField(event.target);
  });

  const printButton = root.querySelector('[data-arg-print]');
  if (printButton) {
    printButton.addEventListener('click', () => {
      syncAll();
      window.print();
    });
  }

  const resetButton = root.querySelector('[data-arg-reset]');
  if (resetButton) {
    resetButton.addEventListener('click', () => {
      root.querySelectorAll('[data-report-field]').forEach((input) => {
        input.value = defaults[input.dataset.reportField] ?? '';
      });
      syncAll();
    });
  }

  syncAll();
})();