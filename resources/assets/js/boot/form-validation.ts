document.addEventListener('DOMContentLoaded', () => {
    for (const element of document.querySelectorAll('[data-patterndescription]')) {
        element.addEventListener('invalid', ({ target }) => {
            if (!(target instanceof HTMLInputElement)) return;
            const patternDescription = target.dataset?.patterndescription;
            const { customError, patternMismatch } = target.validity ?? {};
            if (!customError && patternMismatch && patternDescription) {
                target.setCustomValidity(patternDescription);
                target.reportValidity();
            }
        });

        element.addEventListener('input', ({ target }) => {
            if (!(target instanceof HTMLInputElement)) return;
            const patternDescription = target.dataset?.patterndescription;
            const { customError, patternMismatch } = target.validity ?? {};

            if (customError && !patternMismatch && patternDescription) {
                target.setCustomValidity('');
                target.reportValidity();
            }
        });
    }

    // do one for data-maxsize as well
    for (const element of document.querySelectorAll('[data-maxsize]')) {
        element.addEventListener('change', ({ target }) => {
            if (!(target instanceof HTMLInputElement)) return;

            const maxFileSizeMb = parseInt(target.dataset?.maxsize ?? '0', 10);
            if (!maxFileSizeMb) return;

            const maxFileSizeBytes = maxFileSizeMb * 1024 * 1024;
            if (target.files && target.files.length > 0 && Array.from(target.files).some((file) => file.size > maxFileSizeBytes)) {
                target.setCustomValidity(`File size must not exceed ${maxFileSizeMb} MB`);
                target.reportValidity();
            } else {
                target.setCustomValidity('');
                target.reportValidity();
            }
        });
    }
});
