/**
 * jQuery-like event delegation
 */
export function filteredEventListener(
    element: Element | Document,
    event: keyof HTMLElementEventMap,
    selector: string,
    callback: (event: Event, element: Element) => void,
) {
    const handler = (e: Event) => {
        const target = e.target as Element | null;
        const el = target?.closest(selector);
        if (el) callback.call(el, e, el);
    };

    element.addEventListener(event, handler);
    return handler;
}

const escapeHtmlEntityMap: Record<string, string> = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
    '/': '&#x2F;',
};

export const escapeHtml = (string: string) => {
    return String(string).replace(/[&<>"'/]/g, (s) => escapeHtmlEntityMap[s]);
};

// If n = 1, return 'unit', else return 'units'
export const pl = function (num: number, unit: string) {
    num = Math.floor(num);
    return num + ' ' + unit + (num == 1 ? '' : 's');
};

// Print a readable time (e.g. '20 seconds ago', '2 days ago')
export function readableTime(ts: number) {
    var z = Date.now() - ts,
        s = z / 1000,
        i = s / 60,
        h = i / 60,
        d = h / 24,
        w = d / 7,
        m = d / 30,
        y = d / 365;
    if (s < 10) return 'just now';
    return (
        (y >= 1
            ? pl(y, 'year')
            : m >= 1
              ? pl(m, 'month')
              : w >= 1
                ? pl(w, 'week')
                : d >= 1
                  ? pl(d, 'day')
                  : h >= 1
                    ? pl(h, 'hour')
                    : i >= 1
                      ? pl(i, 'minute')
                      : pl(s, 'second')) + ' ago'
    );
}

// https://developer.mozilla.org/en-US/docs/Web/API/Window/btoa#unicode_strings
export function b64EncodeUnicode(str: string) {
    const bytes = new TextEncoder().encode(str);
    const binString = Array.from(bytes, (x) => String.fromCharCode(x)).join('');
    return btoa(binString);
}

export function b64DecodeUnicode(base64: string) {
    const binString = atob(base64);
    const bytes = Uint8Array.from(binString, (x) => x.charCodeAt(0));
    return new TextDecoder().decode(bytes);
}
