(function (root) {
  var LANGS = ['id', 'en', 'ja'];
  var USD_RATE = 16500;
  var STORAGE_KEY = 'fts-lang';
  var MAIL_TO = 'info@fts-tech.co.id';
  var REQUIRED = ['name', 'email', 'message'];

  function resolveLang(stored) {
    return LANGS.indexOf(stored) !== -1 ? stored : 'id';
  }

  function formatPrice(idr, currency) {
    if (currency === 'USD') {
      return '$' + (idr / USD_RATE).toFixed(2);
    }
    return 'Rp ' + String(idr).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function buildMailto(fields) {
    var subject = (fields.subject || '').trim() || 'Inquiry from fts-tech.co.id';
    var body =
      'Name: ' + fields.name + '\n' +
      'Email: ' + fields.email + '\n' +
      'Company: ' + (fields.company || '-') + '\n\n' +
      fields.message;
    return 'mailto:' + MAIL_TO +
      '?subject=' + encodeURIComponent(subject) +
      '&body=' + encodeURIComponent(body);
  }

  function validateForm(fields) {
    return REQUIRED.filter(function (name) {
      return !String(fields[name] || '').trim();
    });
  }

  var api = {
    resolveLang: resolveLang,
    formatPrice: formatPrice,
    buildMailto: buildMailto,
    validateForm: validateForm
  };

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = api;
  }

  if (typeof document === 'undefined') return;

  // ---- Browser wiring ----
  var currency = 'IDR';

  function readStoredLang() {
    try { return localStorage.getItem(STORAGE_KEY); } catch (e) { return null; }
  }

  function storeLang(lang) {
    try { localStorage.setItem(STORAGE_KEY, lang); } catch (e) { /* storage unavailable */ }
  }

  function applyLang(lang) {
    var dict = root.I18N[lang];
    document.documentElement.lang = lang;
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      var key = el.getAttribute('data-i18n');
      if (dict[key] !== undefined) el.textContent = dict[key];
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
      var key = el.getAttribute('data-i18n-placeholder');
      if (dict[key] !== undefined) el.setAttribute('placeholder', dict[key]);
    });
    var desc = document.querySelector('meta[name="description"]');
    if (desc) desc.setAttribute('content', dict['meta.description']);
    var ogTitle = document.querySelector('meta[property="og:title"]');
    if (ogTitle) ogTitle.setAttribute('content', dict['meta.title']);
    var ogDesc = document.querySelector('meta[property="og:description"]');
    if (ogDesc) ogDesc.setAttribute('content', dict['meta.description']);
    document.querySelectorAll('.lang-switch [data-lang]').forEach(function (btn) {
      btn.setAttribute('aria-pressed', String(btn.getAttribute('data-lang') === lang));
    });
    storeLang(lang);
  }

  function renderPrices() {
    document.querySelectorAll('.price[data-idr]').forEach(function (el) {
      el.textContent = formatPrice(Number(el.getAttribute('data-idr')), currency);
    });
    document.querySelectorAll('[data-currency]').forEach(function (btn) {
      btn.setAttribute('aria-pressed', String(btn.getAttribute('data-currency') === currency));
    });
  }

  function onSubmit(event) {
    event.preventDefault();
    var form = event.currentTarget;
    var fields = {};
    ['name', 'email', 'company', 'subject', 'message'].forEach(function (n) {
      fields[n] = form.elements[n].value;
    });
    var missing = validateForm(fields);
    var errorEl = document.getElementById('form-error');
    form.querySelectorAll('.invalid').forEach(function (el) { el.classList.remove('invalid'); });
    if (missing.length) {
      errorEl.hidden = false;
      missing.forEach(function (n) { form.elements[n].classList.add('invalid'); });
      form.elements[missing[0]].focus();
      return;
    }
    errorEl.hidden = true;
    window.location.href = buildMailto(fields);
  }

  function init() {
    applyLang(resolveLang(readStoredLang()));
    renderPrices();
    document.querySelectorAll('.lang-switch [data-lang]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        applyLang(resolveLang(btn.getAttribute('data-lang')));
      });
    });
    document.querySelectorAll('[data-currency]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        currency = btn.getAttribute('data-currency') === 'USD' ? 'USD' : 'IDR';
        renderPrices();
      });
    });
    var form = document.getElementById('contact-form');
    if (form) form.addEventListener('submit', onSubmit);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(typeof window !== 'undefined' ? window : globalThis);
