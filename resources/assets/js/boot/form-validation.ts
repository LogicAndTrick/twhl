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
});
