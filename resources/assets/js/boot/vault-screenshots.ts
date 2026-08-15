import Uppy from '@uppy/core';
import DragDrop from '@uppy/drag-drop';
import DropTarget from '@uppy/drop-target';
import StatusBar from '@uppy/status-bar';
import XHR from '@uppy/xhr-upload';
import Sortable from 'sortablejs';

import { nanoTemplate, nanoTemplateHtml } from '../lib/nano-templating';
import { filteredEventListener } from '../lib/utils';

(window as any).initialiseVaultScreenshots = function () {
    const screenshot_template = document.getElementById('vault-screenshot-template')!.textContent.trim();

    const screenshotUploadForm = document.getElementById('screenshot-upload')! as HTMLFormElement;
    const screenshotListEl = document.querySelector('.screenshot-list')! as HTMLElement;

    const vaultItemId = parseInt((screenshotUploadForm.querySelector('[name="id"]')! as HTMLInputElement).value, 10);
    const csrfToken = (screenshotUploadForm.querySelector('[name="_token"]')! as HTMLInputElement).value;

    screenshotUploadForm.classList.add('d-none');

    const uppy = new Uppy({
        restrictions: {
            maxFileSize: 1024 * 1024 * 2,
            allowedFileTypes: ['.jpeg', '.jpg', '.png'],
        },
        meta: {
            id: vaultItemId,
            _token: csrfToken,
        },
    })
        .use(DragDrop, {
            target: '#screenshot-uppy',
            allowMultipleFiles: true,
            inputName: 'file',
        })
        .use(XHR, {
            endpoint: screenshotUploadForm.action,
            formData: true,
            fieldName: 'file',
            allowedMetaFields: ['_token', 'id'],
        })
        .use(StatusBar, { target: '#screenshot-uppy' })
        .use(DropTarget, { target: 'body' });

    uppy.on('file-added', () => {
        uppy.upload();
    });
    uppy.on('complete', (upload) => {
        if (upload.failed?.length) {
            console.log(upload.failed);
            /*
                if (typeof message == 'object' && message.file) {
                    $(file.previewElement).find('[data-dz-errormessage]').text(message.file);
                }
            */
        }
        update_screenshot_list();
    });

    async function save_screenshot_order() {
        screenshotListEl.classList.add('loading');
        const ids = Array.from(screenshotListEl.querySelectorAll('li')).map((x) => x.dataset.id);

        const url = nanoTemplate(window.urls.vault.save_screenshot_order, { id: vaultItemId });
        await fetch(url, {
            method: 'post',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids, _token: csrfToken }),
        });
        screenshotListEl.classList.remove('loading');
    }

    let currentSortable: Sortable | null = null;
    async function update_screenshot_list() {
        screenshotListEl.classList.add('loading');

        const url = window.urls.vault.get_screenshots + '?count=100&item_id=' + vaultItemId;
        const resp = await fetch(url);
        const data = await resp.json();

        currentSortable?.destroy();
        screenshotListEl.replaceChildren();
        for (const ss of data) {
            const ssEl = nanoTemplateHtml(screenshot_template, ss)!;
            screenshotListEl.append(ssEl);
        }
        Sortable.create(screenshotListEl, {
            handle: '.drag-handle',
            animation: 150,
            onUpdate() {
                save_screenshot_order();
            },
        });

        screenshotListEl.classList.remove('loading');
    }

    filteredEventListener(screenshotListEl, 'click', '.delete-button', async (event, el) => {
        screenshotListEl.classList.add('loading');
        const id = el.closest('li')!.dataset.id;
        await fetch(window.urls.vault.delete_screenshot, {
            method: 'post',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, _token: csrfToken }),
        });
        update_screenshot_list();
    });

    update_screenshot_list();
};
