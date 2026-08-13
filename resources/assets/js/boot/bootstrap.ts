import { Tooltip, Modal } from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((x) => {
        new Tooltip(x);
    });
});

(window as any).bootstrapModal = Modal;
