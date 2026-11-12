export const DEFAULT_USD_RATE = 16500;
export const REQUIRED_FIELDS = ['name', 'email', 'message'];

/** Formats a price given in rupiah, either as "Rp 700.000" or as "$42.42". */
export function formatPrice(idr, currency, rate = DEFAULT_USD_RATE) {
    if (currency === 'USD') {
        return `$${(idr / rate).toFixed(2)}`;
    }

    return `Rp ${String(idr).replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
}

/** Builds the mailto: link for the contact form. Every value is URL-encoded. */
export function buildMailto(fields, to) {
    const subject = (fields.subject || '').trim() || 'Inquiry from the FTS website';
    const body = [
        `Name: ${fields.name}`,
        `Email: ${fields.email}`,
        `Company: ${(fields.company || '').trim() || '-'}`,
        '',
        fields.message,
    ].join('\n');

    return `mailto:${to}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
}

/** Returns the names of required fields that are empty or only whitespace. */
export function validateForm(fields) {
    return REQUIRED_FIELDS.filter((name) => !String(fields[name] ?? '').trim());
}
