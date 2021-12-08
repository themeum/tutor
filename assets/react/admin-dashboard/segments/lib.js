

// this function will load after document content load
window.readyState_complete = (func) => {
  const _funcCaller = (f) => f();
  document.addEventListener('readystatechange', (event) => {
    if (event.target.readyState === 'complete') {
      typeof func == 'function' ? _funcCaller(func) : '';
    }
  });
}



const element = (selector) => {
  return document.querySelector(selector);
};
const elements = (selector) => {
  return document.querySelectorAll(selector);
};

/**
 * Function to download json file
 * @param {json} response
 * @param {string} fileName
 */
const json_download = (response, fileName) => {
  const fileToSave = new Blob([response], {
    type: "application/json",
  });
  const el = document.createElement("a");
  el.href = URL.createObjectURL(fileToSave);
  el.download = fileName;
  el.click();
};

export { element, elements, json_download };
