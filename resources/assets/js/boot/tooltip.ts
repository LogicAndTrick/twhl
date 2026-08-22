import { Instance, createPopper } from '@popperjs/core';

// A singleton tooltip instance, because the Bootstrap one doesn't work very well, and has a bad API
// You need to manually bind events, no automatic mouseover/mouseout behaviour or any of that.

// setup elements - these are global references that we keep in memory
const tooltipEl = document.createElement('div');
const arrowEl = document.createElement('div');
const innerEl = document.createElement('div');

tooltipEl.classList.add('tooltip', 'bs-tooltip-auto', 'fade', 'global-tooltip');
tooltipEl.setAttribute('role', 'tooltip');
arrowEl.classList.add('tooltip-arrow');
innerEl.classList.add('tooltip-inner');

tooltipEl.append(arrowEl, innerEl);

// popper instance
let popperInstance: Instance | null = null;

/** init or update the popper instance. */
function initOrUpdatePopper(element: HTMLElement) {
    // we combine both init and update, since update will only work if the
    // instance has been created, which means we have to check it regardless.
    if (popperInstance) {
        // already initialised
        popperInstance.state.elements.reference = element;
        popperInstance.update();
        return;
    }

    // need to setup the instance
    document.body.appendChild(tooltipEl);

    // same setup as bootstrap defaults
    // https://github.com/twbs/bootstrap/blob/main/js/src/tooltip.js
    popperInstance = createPopper(element, tooltipEl, {
        placement: 'top',
        modifiers: [
            {
                name: 'flip',
                options: {
                    // different from bootstrap defaults, but top->bottom is nicer than top->right
                    fallbackPlacements: ['top', 'bottom', 'right', 'left'],
                },
            },
            {
                name: 'offset',
                options: { offset: [0, 6] },
            },
            {
                name: 'preventOverflow',
                options: {
                    boundary: 'clippingParents',
                },
            },
            {
                name: 'arrow',
                options: { element: '.tooltip-arrow' },
            },
            {
                name: 'preSetPlacement',
                enabled: true,
                phase: 'beforeMain',
                fn: (data) => {
                    tooltipEl.setAttribute('data-popper-placement', data.state.placement);
                },
            },
        ],
    });
}

/** immediately destroy the popper instance and remove it from the document */
function destroyPopper() {
    popperInstance?.destroy();
    popperInstance = null;
    tooltipEl.remove();
}

// tooltip api
type TooltipContentDataType = Element | string | null | undefined;

/** resolve the given parameter and set the value of the target element based on its type. */
function setTooltipContent(targetElement: HTMLElement, content: (() => TooltipContentDataType) | TooltipContentDataType) {
    let data: TooltipContentDataType;
    if (typeof content == 'function') data = content.call(window);
    else data = content;

    // only support html if the caller gives us an element
    // this means we don't have to worry about XSS holes in this api
    if (data instanceof HTMLElement) {
        targetElement.replaceChildren(data);
    } else if (typeof data == 'string') {
        targetElement.textContent = data;
    } else {
        targetElement.textContent = '';
    }
}

// we destroy the popper shortly after its closed, so it cleans up after itself
let destroyTimeout: number | undefined = undefined;

/**
 * Open the tooltip with the given content, anchored to the given element.
 * @param element The source element of the tooltip
 * @param content The contents of the tooltip
 */
export function openTooltip(element: HTMLElement, content: (() => TooltipContentDataType) | TooltipContentDataType) {
    // cancel destruction
    clearTimeout(destroyTimeout);
    destroyTimeout = undefined;

    // set content and create/update the popper
    setTooltipContent(innerEl, content);
    initOrUpdatePopper(element);

    // show the tooltip
    tooltipEl.classList.add('show');
}

/**
 * Close the tooltip, if it is open.
 */
export function closeTooltip() {
    // hide the tooltip
    tooltipEl.classList.remove('show');

    // queue destruction
    if (destroyTimeout !== undefined) return;
    destroyTimeout = window.setTimeout(() => {
        destroyTimeout = undefined;
        if (!popperInstance) return; // something else destroyed it already
        destroyPopper();
    }, 200);
}
