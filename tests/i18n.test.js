const test = require('node:test');
const assert = require('node:assert');
const fs = require('node:fs');
const path = require('node:path');
const I18N = require('../i18n.js');

const htmlPath = path.join(__dirname, '..', 'index.html');
const langs = ['id', 'en', 'ja'];

test('all languages have identical key sets', () => {
  const base = Object.keys(I18N.id).sort();
  for (const l of ['en', 'ja']) {
    assert.deepStrictEqual(Object.keys(I18N[l]).sort(), base, `keys differ in ${l}`);
  }
});

test('no value is an empty string', () => {
  for (const l of langs) {
    for (const [k, v] of Object.entries(I18N[l])) {
      assert.ok(typeof v === 'string' && v.trim() !== '', `${l}.${k} is empty`);
    }
  }
});

test('every data-i18n key used in index.html exists', { skip: !fs.existsSync(htmlPath) }, () => {
  const html = fs.readFileSync(htmlPath, 'utf8');
  const keys = [...html.matchAll(/data-i18n(?:-placeholder)?="([^"]+)"/g)].map((m) => m[1]);
  assert.ok(keys.length > 0, 'no data-i18n keys found');
  const missing = keys.filter((k) => !(k in I18N.id));
  assert.deepStrictEqual(missing, [], `missing keys: ${missing.join(', ')}`);
});
