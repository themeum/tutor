/**
 * sprintf helper like as php
 *
 * @param {string} str string
 * @param  {...any} args
 * 
 * @returns string
 */
function sprintf(str, ...args) {
    return str.replace(/%s/g, function () {
        return args.shift();
    });
}

export default sprintf;