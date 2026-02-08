$(() => {
    for (const element of document.querySelectorAll("[data-patterndescription]")) {
        element.addEventListener("invalid", ({ target }) => {
            const patternDescription = target.dataset?.patterndescription;
            const { customError, patternMismatch } = target.validity ?? {};
            if (!customError && patternMismatch && patternDescription) {
                target.setCustomValidity(patternDescription);
                target.reportValidity();
            }
        });

        element.addEventListener("input", ({ target }) => {
            const patternDescription = target.dataset?.patterndescription;
            const { customError, patternMismatch } = target.validity ?? {};

            if (customError && !patternMismatch && patternDescription) {
                target.setCustomValidity('');
                target.reportValidity();
            }
        });
    }
});
