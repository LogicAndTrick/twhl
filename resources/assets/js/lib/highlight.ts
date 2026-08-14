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
import plaintext from 'highlight.js/lib/languages/plaintext';
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
hljs.registerLanguage('plaintext', plaintext);

hljs.addPlugin({
    'before:highlightElement'({ el }) {
        const children = Array.from(el.children);
        (el as any).__custom_highlight_children = children;
        children.forEach((x) => x.parentElement?.removeChild(x));
    },
    'after:highlightElement'({ el }) {
        const children = (el as any).__custom_highlight_children as Element[];
        if (children) el.prepend(...children);
    },
});

export default hljs;
