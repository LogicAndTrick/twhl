import hljs from 'highlight.js/lib/core';
import angelscript from 'highlight.js/lib/languages/angelscript';
import cpp from 'highlight.js/lib/languages/cpp';
import csharp from 'highlight.js/lib/languages/csharp';
import css from 'highlight.js/lib/languages/css';
import dos from 'highlight.js/lib/languages/dos';
import ini from 'highlight.js/lib/languages/ini';
import javascript from 'highlight.js/lib/languages/javascript';
import json from 'highlight.js/lib/languages/json';
import php from 'highlight.js/lib/languages/php';
import xml from 'highlight.js/lib/languages/xml';

hljs.registerLanguage('php', php);
hljs.registerLanguage('dos', dos);
hljs.registerLanguage('css', css);
hljs.registerLanguage('cpp', cpp);
hljs.registerLanguage('csharp', csharp);
hljs.registerLanguage('ini', ini);
hljs.registerLanguage('json', json);
hljs.registerLanguage('xml', xml);
hljs.registerLanguage('angelscript', angelscript);
hljs.registerLanguage('javascript', javascript);

export default hljs;
