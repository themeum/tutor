const element = (selector) => {
  return document.querySelector(selector);
};
const elements = (selector) => {
  return document.querySelectorAll(selector);
};
const notice_message = (message = "") => {
  let noticeElement = element(".tutor-notification");
  noticeElement.classList.add("show");
  if (message) {
    noticeElement.querySelector(
      ".tutor-notification-content p"
    ).innerText = message;
  }
  setTimeout(() => {
    noticeElement.classList.remove("show");
  }, 4000);
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

export { element, elements, notice_message, json_download };
