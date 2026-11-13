const test = require('node:test');
const assert = require('node:assert');
const { resolveLang, formatPrice, buildMailto, validateForm } = require('../app.js');

test('resolveLang falls back to id for unknown or missing values', () => {
  assert.strictEqual(resolveLang('xx'), 'id');
  assert.strictEqual(resolveLang(null), 'id');
  assert.strictEqual(resolveLang(undefined), 'id');
  assert.strictEqual(resolveLang('ja'), 'ja');
  assert.strictEqual(resolveLang('en'), 'en');
});

test('formatPrice formats IDR with Indonesian thousands separators', () => {
  assert.strictEqual(formatPrice(700000, 'IDR'), 'Rp 700.000');
  assert.strictEqual(formatPrice(1200000, 'IDR'), 'Rp 1.200.000');
});

test('formatPrice converts to USD at 16500 with two decimals', () => {
  assert.strictEqual(formatPrice(700000, 'USD'), '$42.42');
  assert.strictEqual(formatPrice(1200000, 'USD'), '$72.73');
  assert.strictEqual(formatPrice(1600000, 'USD'), '$96.97');
});

test('buildMailto encodes special characters and line breaks', () => {
  const url = buildMailto({ name: 'A', email: 'a@b.c', company: '', subject: 'Hi', message: 'a&b #1\nline2' });
  assert.ok(url.startsWith('mailto:info@fts-tech.co.id'));
  assert.ok(url.includes('a%26b%20%231%0Aline2'), url);
});

test('buildMailto uses a default subject when none is given', () => {
  const url = buildMailto({ name: 'A', email: 'a@b.c', company: '', subject: '', message: 'x' });
  assert.ok(/subject=[^&]+/.test(url), url);
});

test('validateForm returns the names of empty required fields', () => {
  assert.deepStrictEqual(validateForm({ name: '', email: 'a@b.c', message: '' }), ['name', 'message']);
  assert.deepStrictEqual(validateForm({ name: ' ', email: 'a@b.c', message: 'x' }), ['name']);
  assert.deepStrictEqual(validateForm({ name: 'A', email: 'a@b.c', message: 'x' }), []);
});
