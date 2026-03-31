

// this function will load after document content load
window.readyState_complete = (func) => {
  const _caller = (f) => f();
  document.addEventListener('readystatechange', (e) => e.target.readyState === 'complete' ? (typeof func == 'function' ? setTimeout(() => _caller(func)) : '') : '');
}

window.addBodyClass = (currentUrl) => {
  // History push
  const url = new URL(currentUrl);
  const tabPage = url.searchParams.get('tab_page');
  const tabPageEdit = url.searchParams.get('edit') && '_edit';
  document.body.classList.add(tabPage);
  document.body.classList.add(tabPage + tabPageEdit);
};

window.selectorById = (selector) => {
  return document.getElementById(selector);
};

window.selectorByClass = (selector) => {
  return document.getElementsByClassName(selector);
};

/**
 * Function to download json file
 * @param {json} response
 * @param {string} fileName
 */
window.json_download = (response, fileName) => {
  const fileToSave = new Blob([response], {
    type: "application/json",
  });
  const el = document.createElement("a");
  el.href = URL.createObjectURL(fileToSave);
  el.download = fileName;
  el.click();
};

