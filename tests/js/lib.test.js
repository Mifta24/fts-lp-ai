import test from 'node:test';
import assert from 'node:assert/strict';
import { formatPrice, buildMailto, validateForm } from '../../resources/js/lib.js';

test('formatPrice formats rupiah with Indonesian thousands separators', () => {
    assert.equal(formatPrice(700000, 'IDR'), 'Rp 700.000');
    assert.equal(formatPrice(1200000, 'IDR'), 'Rp 1.200.000');
    assert.equal(formatPrice(1600000, 'IDR'), 'Rp 1.600.000');
});

test('formatPrice converts to USD with two decimals at the given rate', () => {
    assert.equal(formatPrice(700000, 'USD', 16500), '$42.42');
    assert.equal(formatPrice(1200000, 'USD', 16500), '$72.73');
    assert.equal(formatPrice(1600000, 'USD', 16500), '$96.97');
});

test('formatPrice defaults to a rate of 16500', () => {
    assert.equal(formatPrice(700000, 'USD'), '$42.42');
});

test('buildMailto keeps special characters and line breaks intact', () => {
    const url = buildMailto(
        { name: 'A', email: 'a@b.c', company: '', subject: 'Hi', message: 'a&b #1\nline2' },
        'info@fts-tech.co.id',
    );
    assert.ok(url.startsWith('mailto:info@fts-tech.co.id?'), url);
    assert.ok(url.includes('a%26b%20%231%0Aline2'), url);
});

test('buildMailto uses a default subject when none is given', () => {
    const url = buildMailto({ name: 'A', email: 'a@b.c', company: '', subject: '  ', message: 'x' }, 'info@fts-tech.co.id');
    assert.match(url, /subject=[^&]+/);
});

test('validateForm returns the names of empty required fields', () => {
    assert.deepEqual(validateForm({ name: '', email: 'a@b.c', message: '' }), ['name', 'message']);
    assert.deepEqual(validateForm({ name: '   ', email: 'a@b.c', message: 'x' }), ['name']);
    assert.deepEqual(validateForm({ name: 'A', email: 'a@b.c', message: 'x' }), []);
});
