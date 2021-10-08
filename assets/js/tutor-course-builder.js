/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**********************************************!*\
  !*** ./assets/react/course-builder/index.js ***!
  \**********************************************/
window.jQuery(document).ready(function ($) {
  $('.tutor-certificate-template-tab [data-tutor-tab-target]').click(function () {
    $(this).addClass('is-active').siblings().removeClass('is-active');
    $('#' + $(this).data('tutor-tab-target')).show().siblings().hide();
  });
});
/******/ })()
;
//# sourceMappingURL=tutor-course-builder.js.map