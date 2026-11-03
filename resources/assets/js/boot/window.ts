import { nanoTemplate, nanoTemplateHtml, nanoTemplateReplace } from '../lib/nano-templating';
import { filteredEventListener } from '../lib/utils';

Object.assign(window, {
    filteredEventListener,
    nanoTemplate,
    nanoTemplateHtml,
    nanoTemplateReplace,
});
