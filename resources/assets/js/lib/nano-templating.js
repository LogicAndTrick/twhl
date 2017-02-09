/**
 * Forget micro-templates! Nano-templates are where it's at!
 * This is possibly the most basic templating function in the world.
 * Anything in {brackets} is replaced with the value of obj.brackets. You can't escape anything.
 * Example usage: template('Hello, {name}', { name: 'world' })
 *
 * @param template_string Template string
 * @param obj Data source
 * @returns string Template results
 */
function template(template_string, obj) {
    return template_string.replace(/\{(.*?)\}/ig, function(match, name, offset, string) {
        return obj[name] || '';
    });
}
