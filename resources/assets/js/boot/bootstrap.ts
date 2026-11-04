import { Tooltip, Modal, Toast } from 'bootstrap';

import { elem } from '../lib/utils';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((x) => {
        new Tooltip(x);
    });
});

(window as any).bootstrapModal = Modal;
(window as any).bootstrapToast = Toast;

let toastContainer: HTMLElement | null = null;

(window as any).toast = (message: string, theme = 'success', delay = 5000) => {
    const body = elem('div', 'toast-body', message);
    const close = elem('button', 'btn-close btn-close-white me-2 m-auto', undefined, (x) => ([x.type, x.dataset.bsDismiss] = ['button', 'toast']));
    const wrapper = elem('div', 'd-flex', [body, close]);
    const el = elem('div', `toast align-items-center border-0 bg-${theme} text-white`, wrapper);
    if (!toastContainer) {
        toastContainer = elem('div', 'toast-container');
        const outer = elem('div', 'position-fixed top-0 d-flex justify-content-center align-items-start w-100 h-100 p-5', toastContainer);
        outer.style.pointerEvents = 'none';
        document.body.appendChild(outer);
    }
    toastContainer.appendChild(el);
    const toast = new Toast(el, { autohide: true, delay });
    toast.show();
    el.addEventListener('hidden.bs.toast', () => {
        el.remove();
        toast.dispose();
    });
};
