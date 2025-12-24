
/**
 * Initialize min version of tinyMCE
 *
 * @param {*} selector   css select to init editor 
 * @param {*} plugins    supported plugins (For ex: codesample code ) separated by space
 * @param {*} toolbar    default supported tools:
 * bold italic underline alignleft aligncenter alignright bullist numlist link unlink
 * 
 * To add more tools pass as third arguments
 * @param {*} setContent default content
 */
function initTinyMCE(selector, plugins = '', tools = '') {
    let defaultTools = `bold italic underline  link unlink ${tools}`;
    tinymce.init({
        selector: selector,
        height: 250,
        plugins: plugins,
        toolbar: defaultTools,
        menu: {},
        menubar: {},
        relative_urls : false,
    });
}
export default initTinyMCE;