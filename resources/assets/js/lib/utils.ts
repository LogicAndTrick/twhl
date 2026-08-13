/**
 * jQuery-like event delegation
 */
export function filteredEventListener(element: Element, event: keyof HTMLElementEventMap, selector: string, callback: EventListener) {
    const handler = (e: Event) => {
        if ((e.target as Element)?.closest(selector)) {
            callback.call(e.target, e);
        }
    };
    element.addEventListener(event, handler);
    return handler;
};

const escapeHtmlEntityMap: Record<string, string> = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': '&quot;',
    "'": '&#39;',
    "/": '&#x2F;'
};

export const escapeHtml = (string: string) => {
    return String(string).replace(/[&<>"'\/]/g, s => escapeHtmlEntityMap[s]);
}
