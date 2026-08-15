/**
 * Forget micro-templates! Nano-templates are where it's at!
 * This is possibly the most basic templating function in the world.
 * Anything in {brackets} is replaced with the value of obj.brackets. You can't escape anything.
 * Example usage: nanoTemplate('Hello, {name}', { name: 'world' })
 *
 * @param template_string Template string
 * @param objs Data sources
 * @returns Template results
 */
export function nanoTemplate(template_string: string, ...objs: any[]) {
    return template_string.replace(/\{(.*?)\}/gi, (_, name) => {
        for (const obj of objs) {
            if (obj && obj[name]) return obj[name];
        }
        return '';
    });
}

export function nanoTemplateHtml(template_string: string, ...objs: any[]) {
    const el = document.createElement('template');
    el.innerHTML = nanoTemplate(template_string, ...objs);
    const count = el.content.childNodes.length;
    return count === 1 ? el.content.firstChild : null;
}
