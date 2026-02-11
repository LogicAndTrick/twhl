/**
 * jQuery-like event delegation
 * @param event {keyof HTMLElementEventMap}
 * @param selector {string}
 * @param callback {EventListenerOrEventListenerObject}
 * @return {handler}
 */
Element.prototype.filteredEventListener = function (event, selector, callback) {
    const handler = function (e) {
        if (e.target.closest(selector)) {
            callback.call(e.target, e);
        }
    };
    this.addEventListener(event, handler);
    return handler;
};

const escapeHtmlEntityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': '&quot;',
    "'": '&#39;',
    "/": '&#x2F;'
};

function escapeHtml(string) {
    return String(string).replace(/[&<>"'\/]/g, function (s) {
        return escapeHtmlEntityMap[s];
    });
}
