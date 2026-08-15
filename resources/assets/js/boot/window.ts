import { nanoTemplate, nanoTemplateHtml } from '../lib/nano-templating';
import { filteredEventListener } from '../lib/utils';

Object.assign(window, {
    filteredEventListener,
    nanoTemplate,
    nanoTemplateHtml,
});
