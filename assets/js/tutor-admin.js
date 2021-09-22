/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/react/admin-dashboard/segments/image-preview.js":
/*!****************************************************************!*\
  !*** ./assets/react/admin-dashboard/segments/image-preview.js ***!
  \****************************************************************/
/***/ (() => {

(function () {
  'use strict';

  toolTipOnWindowResize();
})();
/**
 * Navigation tab
 */


var navTabLists = document.querySelectorAll('ul.tutor-option-nav');
var navTabItems = document.querySelectorAll('li.tutor-option-nav-item a');
var navPages = document.querySelectorAll('.tutor-option-nav-page');
navTabLists.forEach(function (list) {
  list.addEventListener('click', function (e) {
    var dataTab = e.target.parentElement.dataset.tab || e.target.dataset.tab;

    if (dataTab) {
      // remove active from other buttons
      navTabItems.forEach(function (item) {
        item.classList.remove('active');

        if (e.target.dataset.tab) {
          e.target.classList.add('active');
        } else {
          e.target.parentElement.classList.add('active');
        }
      }); // hide other tab contents

      navPages.forEach(function (content) {
        content.classList.remove('active');
      }); // add active to the current content

      var currentContent = document.querySelector("#".concat(dataTab));
      currentContent.classList.add('active'); // History push

      var url = new URL(window.location);
      var params = new URLSearchParams({
        page: "tutor_settings",
        tab_page: dataTab
      });
      var pushUrl = "".concat(url.origin + url.pathname, "?").concat(params.toString());
      window.history.pushState({}, '', pushUrl);
    }
  });
});
/**
 * Toggle disable input fields
 * Selecetor -> .tutor-option-single-item.monitization-fees
 */

var moniFees = document.querySelector('.monitization-fees');
var feesToggle = document.querySelector('.monitization-fees input[name=deduct-fees]');

if (moniFees && feesToggle) {
  window.addEventListener('load', function () {
    return toggleDisableClass(feesToggle, moniFees);
  });
  feesToggle.addEventListener('change', function () {
    return toggleDisableClass(feesToggle, moniFees);
  });
}

var toggleDisableClass = function toggleDisableClass(input, parent) {
  if (input.checked) {
    parent.classList.remove('is-disable');
    toggleDisableAttribute(moniFees, false);
  } else {
    parent.classList.add('is-disable');
    toggleDisableAttribute(moniFees, true);
  }
};

var toggleDisableAttribute = function toggleDisableAttribute(elem, state) {
  var inputArr = elem.querySelectorAll('.tutor-option-field-row:nth-child(2) textarea, .tutor-option-field-row:nth-child(3) select, .tutor-option-field-row:nth-child(3) input');
  inputArr.forEach(function (item) {
    return item.disabled = state;
  });
};
/**
 * Image Preview : Logo and Signature Upload
 * Selector -> .tutor-option-field-input.image-previewer
 */


var imgPreviewers = document.querySelectorAll('.image-previewer');
var imgPreviews = document.querySelectorAll('.image-previewer img');
var imgPrevInputs = document.querySelectorAll('.image-previewer input[type=file]');
var imgPrevDelBtns = document.querySelectorAll('.image-previewer .delete-btn');

if (imgPrevInputs && imgPrevDelBtns) {
  // Checking Img Src when document loads
  document.addEventListener('DOMContentLoaded', function () {
    imgPreviewers.forEach(function (previewer) {
      imgPreviews.forEach(function (img) {
        if (img.getAttribute('src')) {
          img.closest('.image-previewer').classList.add('is-selected');
        } else {
          previewer.classList.remove('is-selected');
        }
      });
    });
  }); // Updating Image Preview

  imgPrevInputs.forEach(function (input) {
    input.addEventListener('change', function (e) {
      var file = this.files[0];
      var parentEl = input.closest('.image-previewer');
      var targetImg = parentEl.querySelector('img');
      var prevLoader = parentEl.querySelector('.preview-loading');

      if (file) {
        prevLoader.classList.add('is-loading');
        getImageAsDataURL(file, targetImg);
        parentEl.classList.add('is-selected');
        setTimeout(function () {
          prevLoader.classList.remove('is-loading');
        }, 200);
      }
    });
  }); // Deleting Image Preview

  imgPrevDelBtns.forEach(function (delBtn) {
    delBtn.addEventListener('click', function (e) {
      var parentEl = this.closest('.image-previewer');
      var targetImg = parentEl.querySelector('img');
      targetImg.setAttribute('src', '');
      parentEl.classList.remove('is-selected');
    });
  });
} // Get Image file as Data URL


var getImageAsDataURL = function getImageAsDataURL(file, imgSrc) {
  var reader = new FileReader();

  reader.onload = function () {
    imgSrc.setAttribute('src', this.result);
  };

  reader.readAsDataURL(file);
};
/**
 * Sharing Percentage : Monitization > Option
 */


var insInput = document.querySelector('input[type=number]#revenue-instructor');
var adminInput = document.querySelector('input[type=number]#revenue-admin');
var revenueInputs = document.querySelectorAll('.revenue-percentage input[type=number]');

if (insInput && adminInput && revenueInputs) {
  insInput.addEventListener('input', function (e) {
    e.target.value <= 100 && (adminInput.value = 100 - e.target.value);
    revenueInputValidation(e.target.value);
  });
  adminInput.addEventListener('input', function (e) {
    e.target.value <= 100 && (insInput.value = 100 - e.target.value);
    revenueInputValidation(e.target.value);
  });
}

var revenueInputValidation = function revenueInputValidation(value) {
  value > 100 ? revenueInputs.forEach(function (input) {
    return input.classList.add('warning');
  }) : revenueInputs.forEach(function (input) {
    return input.classList.remove('warning');
  });
};
/**
 * Copy to clipboard : Email > Server Cron
 */


var codeTexarea = document.querySelector('.input-field-code textarea');
var copyBtn = document.querySelector('.code-copy-btn');

if (copyBtn && codeTexarea) {
  copyBtn.addEventListener('click', function (e) {
    var _this = this;

    e.preventDefault();
    this.focus();
    codeTexarea.select();
    document.execCommand('copy');
    var btnEl = this.innerHTML;
    setTimeout(function () {
      _this.innerHTML = btnEl;
    }, 3000);
    this.innerHTML = "\n\t\t\t<span class=\"tutor-btn-icon las la-clipboard-list\"></span>\n\t\t\t<span>Copied to Clipboard!</span>\n\t\t";
  });
}
/**
 * Popup Menu Toggle -> Import/Export > .settings-history
 */


var popupToggleBtns = document.querySelectorAll('.popup-opener .popup-btn');
var popupMenus = document.querySelectorAll('.popup-opener .popup-menu');

if (popupToggleBtns && popupMenus) {
  popupToggleBtns.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      var popupClosest = e.target.closest('.popup-opener').querySelector('.popup-menu');
      popupClosest.classList.toggle('visible');
      popupMenus.forEach(function (popupMenu) {
        if (popupMenu !== popupClosest) {
          popupMenu.classList.remove('visible');
        }
      });
    });
  });
  window.addEventListener('click', function (e) {
    if (!e.target.matches('.popup-opener .popup-btn')) {
      popupMenus.forEach(function (popupMenu) {
        if (popupMenu.classList.contains('visible')) {
          popupMenu.classList.remove('visible');
        }
      });
    }
  });
}
/**
 * Drag and Drop files -> Import/Export > .import-setting
 */


var dropZoneInputs = document.querySelectorAll('.drag-drop-zone input[type=file]');
dropZoneInputs.forEach(function (inputEl) {
  var dropZone = inputEl.closest('.drag-drop-zone');
  ['dragover', 'dragleave', 'dragend'].forEach(function (dragEvent) {
    if (dragEvent === 'dragover') {
      dropZone.addEventListener(dragEvent, function (e) {
        e.preventDefault();
        dropZone.classList.add('dragover');
      });
    } else {
      dropZone.addEventListener(dragEvent, function (e) {
        dropZone.classList.remove('dragover');
      });
    }
  });
  dropZone.addEventListener('drop', function (e) {
    e.preventDefault();
    var files = e.dataTransfer.files;
    getFilesAndUpdateDOM(files, inputEl, dropZone);
    dropZone.classList.remove('dragover');
  });
  inputEl.addEventListener('change', function (e) {
    var files = e.target.files;
    getFilesAndUpdateDOM(files, inputEl, dropZone);
  });
});

var getFilesAndUpdateDOM = function getFilesAndUpdateDOM(files, inputEl, dropZone) {
  if (files.length) {
    inputEl.files = files;
    dropZone.classList.add('file-attached');
    dropZone.querySelector('.file-info').innerHTML = "File attached - ".concat(files[0].name);
  } else {
    dropZone.classList.remove('file-attached');
    dropZone.querySelector('.file-info').innerHTML = '';
  }
};
/**
 * Tooltip direction change on smaller devices -> .tooltip-right
 */


function toolTipOnWindowResize() {
  var mediaQuery = window.matchMedia('(max-width: 992px)');

  if (mediaQuery.matches) {
    var toolTips = document.querySelectorAll('.tooltip-right');
    toolTips.forEach(function (toolTip) {
      toolTip.classList.replace('tooltip-right', 'tooltip-left');
    });
  } else {
    var _toolTips = document.querySelectorAll('.tooltip-left');

    _toolTips.forEach(function (toolTip) {
      toolTip.classList.replace('tooltip-left', 'tooltip-right');
    });
  }
}

window.addEventListener('resize', toolTipOnWindowResize);
/**
 * Search Suggestion box
 */

/* const searchInput = document.querySelector('.search-field input[type=search]');
const searchPopupOpener = document.querySelector('.search-popup-opener');

searchInput.addEventListener('input', (e) => {
	if (e.target.value) {
		searchPopupOpener.classList.add('visible');
	} else {
		searchPopupOpener.classList.remove('visible');
	}
}); */

/***/ }),

/***/ "./assets/react/admin-dashboard/segments/options.js":
/*!**********************************************************!*\
  !*** ./assets/react/admin-dashboard/segments/options.js ***!
  \**********************************************************/
/***/ (() => {

"use strict";
 // SVG Icons Totor V2

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

var tutorIconsV2 = {
  warning: '<svg class="tutor-icon-v2 warning" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.0388 14.2395C18.2457 14.5683 18.3477 14.9488 18.3321 15.3333C18.3235 15.6951 18.2227 16.0493 18.0388 16.3647C17.851 16.6762 17.5885 16.9395 17.2733 17.1326C16.9301 17.3257 16.5383 17.4237 16.1412 17.4159H5.87591C5.47974 17.4234 5.08907 17.3253 4.74673 17.1326C4.42502 16.9409 4.15549 16.6776 3.96071 16.3647C3.77376 16.0506 3.67282 15.6956 3.66741 15.3333C3.6596 14.9496 3.76106 14.5713 3.96071 14.2395L9.11094 5.64829C9.29701 5.31063 9.58016 5.03215 9.9263 4.84641C10.2558 4.67355 10.6248 4.58301 10.9998 4.58301C11.3747 4.58301 11.7437 4.67355 12.0732 4.84641C12.4259 5.02952 12.7154 5.30825 12.9062 5.64829L18.0388 14.2395ZM11.7447 10.4086C11.7447 10.2131 11.7653 10.0176 11.7799 9.81924C11.7946 9.62089 11.8063 9.41971 11.818 9.21853C11.8178 9.1484 11.8129 9.07836 11.8034 9.00885C11.7916 8.94265 11.7719 8.87799 11.7447 8.81617C11.6644 8.64655 11.5255 8.50928 11.3517 8.42798C11.1805 8.3467 10.9848 8.32759 10.8003 8.37414C10.6088 8.42217 10.4413 8.53471 10.3281 8.69149C10.213 8.84985 10.1525 9.03921 10.1551 9.2327C10.1551 9.3602 10.1756 9.48771 10.1844 9.61239C10.1932 9.73706 10.202 9.86457 10.2137 9.99208C10.2401 10.4709 10.2695 10.947 10.2988 11.4088C10.3281 11.8707 10.3545 12.3552 10.3838 12.8256C10.3857 12.9019 10.4032 12.9771 10.4352 13.0468C10.4672 13.1166 10.5131 13.1796 10.5703 13.2322C10.6275 13.2849 10.6948 13.3261 10.7685 13.3536C10.8422 13.381 10.9208 13.3942 10.9998 13.3923C11.0794 13.3946 11.1587 13.3813 11.2328 13.353C11.307 13.3248 11.3744 13.2822 11.4309 13.228C11.5454 13.1171 11.6115 12.968 11.6157 12.8114V12.5281C11.6157 12.4317 11.6157 12.3382 11.6157 12.2447C11.6362 11.9415 11.6538 11.6327 11.6743 11.3238C11.6949 11.015 11.7271 10.7118 11.7447 10.4086ZM10.9998 15.5118C11.1049 15.5119 11.2091 15.4919 11.3062 15.453C11.4034 15.4141 11.4916 15.3571 11.5658 15.2851C11.6441 15.2191 11.7061 15.137 11.7472 15.0448C11.7883 14.9526 11.8075 14.8527 11.8034 14.7524C11.8053 14.6497 11.7863 14.5476 11.7474 14.452C11.7085 14.3564 11.6505 14.2692 11.5767 14.1953C11.5029 14.1213 11.4147 14.0621 11.3172 14.0211C11.2197 13.9801 11.1149 13.958 11.0086 13.9562C10.9023 13.9543 10.7966 13.9727 10.6977 14.0103C10.5987 14.0479 10.5084 14.1039 10.4319 14.1752C10.3553 14.2465 10.2941 14.3317 10.2516 14.4259C10.2092 14.52 10.1863 14.6214 10.1844 14.7241C10.1844 14.933 10.2703 15.1333 10.4232 15.2811C10.5761 15.4288 10.7835 15.5118 10.9998 15.5118Z" fill="#9CA0AC"/></svg>',
  magnifyingGlass: '<svg class="tutor-icon-v2 magnifying-glass" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.3056 5.375C7.58249 5.375 5.375 7.58249 5.375 10.3056C5.375 13.0286 7.58249 15.2361 10.3056 15.2361C13.0286 15.2361 15.2361 13.0286 15.2361 10.3056C15.2361 7.58249 13.0286 5.375 10.3056 5.375ZM4.125 10.3056C4.125 6.89214 6.89214 4.125 10.3056 4.125C13.719 4.125 16.4861 6.89214 16.4861 10.3056C16.4861 13.719 13.719 16.4861 10.3056 16.4861C6.89214 16.4861 4.125 13.719 4.125 10.3056Z" fill="#9CA0AC"/><path fill-rule="evenodd" clip-rule="evenodd" d="M13.7874 13.7872C14.0314 13.5431 14.4272 13.5431 14.6712 13.7872L17.6921 16.8081C17.9362 17.0521 17.9362 17.4479 17.6921 17.6919C17.448 17.936 17.0523 17.936 16.8082 17.6919L13.7874 14.6711C13.5433 14.427 13.5433 14.0313 13.7874 13.7872Z" fill="#9CA0AC"/></svg>',
  angleRight: '<svg class="tutor-icon-v2 angle-right" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.842 12.633C7.80402 12.6702 7.7592 12.6998 7.71 12.72C7.65839 12.7401 7.60341 12.7503 7.548 12.75C7.49655 12.7496 7.44563 12.7395 7.398 12.72C7.34843 12.7005 7.30347 12.6709 7.266 12.633L6.88201 12.252C6.84384 12.2138 6.81284 12.1691 6.79051 12.12C6.76739 12.0694 6.75367 12.015 6.75001 11.9595C6.74971 11.9045 6.75832 11.8498 6.77551 11.7975C6.79308 11.7477 6.82181 11.7025 6.85951 11.6655L9.53249 9.00001L6.86701 6.33453C6.82576 6.29904 6.79427 6.2536 6.77551 6.20253C6.75832 6.15026 6.74971 6.09555 6.75001 6.04053C6.75367 5.98502 6.76739 5.93064 6.79051 5.88003C6.81284 5.8309 6.84384 5.78619 6.88201 5.74803L7.263 5.36704C7.30047 5.32916 7.34543 5.29953 7.395 5.28004C7.44263 5.26056 7.49355 5.25038 7.545 5.25004C7.60142 5.24931 7.65745 5.2595 7.71 5.28004C7.7592 5.30025 7.80402 5.3298 7.842 5.36704L11.181 8.70752C11.2233 8.74442 11.2579 8.78926 11.283 8.83951C11.3077 8.88941 11.3206 8.94433 11.3206 9.00001C11.3206 9.05569 11.3077 9.11062 11.283 9.16051C11.2579 9.21076 11.2233 9.25561 11.181 9.29251L7.842 12.633Z" fill="#B4B7C0"/></svg>'
}; // Tutor v2 icons

var angleRight = tutorIconsV2.angleRight,
    magnifyingGlass = tutorIconsV2.magnifyingGlass,
    warning = tutorIconsV2.warning;
document.addEventListener('DOMContentLoaded', function () {
  var $ = window.jQuery;
  var image_uploader = document.querySelectorAll(".image_upload_button"); // let image_input = document.getElementById("image_url_field");

  var _loop = function _loop(i) {
    var image_upload_wrap = image_uploader[i].closest(".image-previewer");
    var input_file = image_upload_wrap.querySelector(".input_file");
    var upload_preview = image_upload_wrap.querySelector(".upload_preview");
    var image_delete = image_upload_wrap.querySelector(".delete-btn");

    image_uploader[i].onclick = function (e) {
      e.preventDefault();
      var image_frame = wp.media({
        title: "Upload Image",
        library: {
          type: "image"
        },
        multiple: false,
        frame: "post",
        state: "insert"
      });
      image_frame.open();
      /* image_frame.on("select", function (e) {
      	console.log("image size");
      	console.log(image.state().get("selection").first().toJSON());
      		var image_url = image_frame.state().get("selection").first().toJSON().url;
      		upload_previewer.src = image_input.value = image_url;
      }); */

      image_frame.on("insert", function (selection) {
        var state = image_frame.state();
        selection = selection || state.get("selection");
        if (!selection) return; // We set multiple to false so only get one image from the uploader

        var attachment = selection.first();
        var display = state.display(attachment).toJSON(); // <-- additional properties

        attachment = attachment.toJSON(); // Do something with attachment.id and/or attachment.url here

        var image_url = attachment.sizes[display.size].url;
        upload_preview.src = input_file.value = image_url;
      });
    };

    image_delete.onclick = function () {
      input_file.value = "";
    };
  };

  for (var i = 0; i < image_uploader.length; ++i) {
    _loop(i);
  }

  $(window).on("click", function (e) {
    $(".tutor-notification, .search_result").removeClass("show");
  });
  $(".tutor-notification-close").click(function (e) {
    $(".tutor-notification").removeClass("show");
  });
  $("#save_tutor_option").click(function (e) {
    e.preventDefault();
    $("#tutor-option-form").submit();
  });
  $("#tutor-option-form").submit(function (e) {
    e.preventDefault();
    var $form = $(this);
    var data = $form.serializeObject();
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: "POST",
      data: data,
      beforeSend: function beforeSend() {},
      success: function success(data) {
        $(".tutor-notification").addClass("show");
        setTimeout(function () {
          $(".tutor-notification").removeClass("show");
        }, 4000);
      },
      complete: function complete() {}
    });
  });

  function titleCase(str) {
    var splitStr = str.toLowerCase().split(" ");

    for (var i = 0; i < splitStr.length; i++) {
      // You do not need to check if i is larger than splitStr length, as your for does that for you
      // Assign it back to the array
      splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
    } // Directly return the joined string


    return splitStr.join(" ");
  }

  function view_item(text, section_slug, section, block, field_key) {
    var navTrack = block ? "".concat(angleRight, " ").concat(block) : "";
    var output = "\n\t\t<a data-tab=\"".concat(section_slug, "\" data-key=\"field_").concat(field_key, "\">\n\t\t\t<div class=\"search_result_title\">\n\t\t\t").concat(magnifyingGlass, "\n\t\t\t<span class=\"text-regular-caption\">").concat(text, "</span>\n\t\t\t</div>\n\t\t\t<div class=\"search_navigation\">\n\t\t\t<div class=\"nav-track text-regular-small\">\n\t\t\t\t<span>").concat(section, "</span>\n\t\t\t\t<span>").concat(navTrack, "</span>\n\t\t\t</div>\n\t\t\t</div>\n\t\t</a>");
    return output;
  }

  $("#search_settings").on("input", function (e) {
    e.preventDefault();

    if (e.target.value) {
      var searchKey = this.value;
      $.ajax({
        url: window._tutorobject.ajaxurl,
        type: "POST",
        data: {
          action: "tutor_option_search",
          keyword: searchKey
        },
        // beforeSend: function () {},
        success: function success(data) {
          var output = "",
              wrapped_item = "",
              notfound = true,
              item_text = "",
              section_slug = "",
              section_label = "",
              block_label = "",
              matchedText = "",
              searchKeyRegex = "",
              field_key = "",
              result = data.data.fields;
          Object.values(result).forEach(function (item, index, arr) {
            var _item_text$match;

            item_text = item.label;
            section_slug = item.section_slug;
            section_label = item.section_label;
            block_label = item.block_label;
            field_key = item.key;
            searchKeyRegex = new RegExp(searchKey, "ig"); // console.log(item_text.match(searchKeyRegex));

            matchedText = (_item_text$match = item_text.match(searchKeyRegex)) === null || _item_text$match === void 0 ? void 0 : _item_text$match[0];

            if (matchedText) {
              wrapped_item = item_text.replace(searchKeyRegex, "<span style='color: #212327; font-weight:500'>".concat(matchedText, "</span>"));
              output += view_item(wrapped_item, section_slug, section_label, block_label, field_key);
              notfound = false;
            }
          });

          if (notfound) {
            output += "<div class=\"no_item\"> ".concat(warning, " No Results Found</div>");
          }

          $(".search_result").html(output).addClass("show");
          output = ""; // console.log("working");
        },
        complete: function complete() {
          // Active navigation element
          navigationTrigger();
        }
      });
    } else {
      document.querySelector(".search-popup-opener").classList.remove("show");
    }
  });
  /**
   * Search suggestion, navigation trigger
   */

  function navigationTrigger() {
    var suggestionLinks = document.querySelectorAll(".search-field .search-popup-opener a");
    var navTabItems = document.querySelectorAll("li.tutor-option-nav-item a");
    var navPages = document.querySelectorAll(".tutor-option-nav-page");
    suggestionLinks.forEach(function (link) {
      link.addEventListener("click", function (e) {
        var dataTab = e.target.closest("[data-tab]").dataset.tab;
        var dataKey = e.target.closest("[data-key]").dataset.key;

        if (dataTab) {
          // remove active from other buttons
          navTabItems.forEach(function (item) {
            item.classList.remove("active");
          }); // add active to the current nav item

          document.querySelector(".tutor-option-tabs [data-tab=".concat(dataTab, "]")).classList.add("active"); // hide other tab contents

          navPages.forEach(function (content) {
            content.classList.remove("active");
          }); // add active to the current content

          document.querySelector(".tutor-option-tab-pages #".concat(dataTab)).classList.add("active"); // History push

          var url = new URL(window.location);
          url.searchParams.set("tab_page", dataTab);
          window.history.pushState({}, "", url);
        } // Reset + Hide Suggestion box


        document.querySelector(".search-popup-opener").classList.remove("show");
        document.querySelector('.search-field input[type="search"]').value = ""; // Highlight selected element

        highlightSearchedItem(dataKey);
      });
    });
  }
  /**
   * Highlight items form search suggestion
   */


  function highlightSearchedItem(dataKey) {
    var target = document.querySelector("#".concat(dataKey));
    var targetEl = target && target.querySelector(".tutor-option-field-label label");
    var scrollTargetEl = target && target.parentNode.querySelector(".tutor-option-field-row");
    console.log("target -> ".concat(target, " scrollTarget -> ").concat(scrollTargetEl));

    if (scrollTargetEl) {
      targetEl.classList.add("isHighlighted");
      setTimeout(function () {
        targetEl.classList.remove("isHighlighted");
      }, 6000);
      scrollTargetEl.scrollIntoView({
        behavior: "smooth",
        block: "center",
        inline: "nearest"
      });
    } else {
      console.warn("scrollTargetEl Not found!");
    }
  }
  /**
   * Email Manage template - live Preview
   */


  var emailManagePageInputs = document.querySelectorAll('.email-manage-page input[type="file"], .email-manage-page input[type="text"], .email-manage-page textarea');
  var dataSourceEls = document.querySelectorAll(".email-manage-page [data-source]");
  emailManagePageInputs.forEach(function (input) {
    input.addEventListener("input", function (e) {
      var _e$target = e.target,
          name = _e$target.name,
          value = _e$target.value;

      if (e.target.files) {
        var file = e.target.files[0];
        console.dir(e.target.files[0]);
        var reader = new FileReader();

        reader.onload = function () {
          document.querySelector('img[data-source="email-title-logo"]').setAttribute("src", this.result);
        };

        reader.readAsDataURL(file);
      }

      var dataSourceEl = document.querySelector(".email-manage-page [data-source=".concat(name, "]"));

      if (dataSourceEl) {
        if (dataSourceEl.href) {
          dataSourceEl.href = value;
        } else {
          dataSourceEl.innerHTML = value;
        }
      }
    });
  });

  function postData() {
    return _postData.apply(this, arguments);
  }
  /* document.querySelector("#export_settings").onclick = (e) => {
  	e.preventDefault();
  	postData(_tutorobject.ajaxurl, {
  	data: {
  		action: "tutor_export_settings",
  		nonce_key: _tutorobject.nonce_key,
  	},
  	}).then((data) => {
  	console.log(data); // JSON data parsed by `data.json()` call
  	});
  };
  */


  function _postData() {
    _postData = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
      var url,
          data,
          response,
          _args = arguments;
      return regeneratorRuntime.wrap(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              url = _args.length > 0 && _args[0] !== undefined ? _args[0] : "";
              data = _args.length > 1 && _args[1] !== undefined ? _args[1] : {};
              _context.next = 4;
              return fetch(url, {
                method: "POST",
                // *GET, POST, PUT, DELETE, etc.
                mode: "cors",
                // no-cors, *cors, same-origin
                cache: "no-cache",
                // *default, no-cache, reload, force-cache, only-if-cached
                credentials: "same-origin",
                // include, *same-origin, omit
                headers: {
                  "Content-Type": "application/json" // 'Content-Type': 'application/x-www-form-urlencoded',

                },
                redirect: "follow",
                // manual, *follow, error
                referrerPolicy: "no-referrer",
                // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
                body: JSON.stringify(data) // body data type must match "Content-Type" header

              });

            case 4:
              response = _context.sent;
              console.log(response.json());
              return _context.abrupt("return", response.json());

            case 7:
            case "end":
              return _context.stop();
          }
        }
      }, _callee);
    }));
    return _postData.apply(this, arguments);
  }

  var exporter = document.querySelector("#export_settings");
  !exporter ? 0 : exporter.addEventListener('click', function (e) {
    e.preventDefault();
    fetch(_tutorobject.ajaxurl, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "Cache-Control": "no-cache"
      },
      body: new URLSearchParams({
        action: "tutor_export_settings"
      })
    }).then(function (response) {
      return response.json();
    }).then(function (response) {
      var file = new Blob([JSON.stringify(response)], {
        type: "application/json"
      });
      var url = URL.createObjectURL(file);
      var element = document.createElement("a");
      element.setAttribute("href", url);
      element.setAttribute("download", "tutor_options");
      element.click();
      document.body.removeChild(element);
    })["catch"](function (err) {
      return console.log(err);
    });
  });
});

/***/ }),

/***/ "./assets/react/lib/common.js":
/*!************************************!*\
  !*** ./assets/react/lib/common.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _tutor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./tutor */ "./assets/react/lib/tutor.js");
/* harmony import */ var _media_chooser__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./media-chooser */ "./assets/react/lib/media-chooser.js");
/* harmony import */ var _media_chooser__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_media_chooser__WEBPACK_IMPORTED_MODULE_1__);



/***/ }),

/***/ "./assets/react/lib/media-chooser.js":
/*!*******************************************!*\
  !*** ./assets/react/lib/media-chooser.js ***!
  \*******************************************/
/***/ (() => {

window.jQuery(document).ready(function ($) {
  var __ = window.wp.i18n.__;
  /**
   * Lesson upload thumbnail
   */

  $(document).on('click', '.tutor-thumbnail-uploader .tutor-thumbnail-upload-button', function (event) {
    event.preventDefault();
    var wrapper = $(this).closest('.tutor-thumbnail-uploader');
    var frame;

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: wrapper.data('media-heading'),
      button: {
        text: wrapper.data('button-text')
      },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      wrapper.find('img').attr('src', attachment.url);
      wrapper.find('input[type="hidden"].tutor-tumbnail-id-input').val(attachment.id);
      wrapper.find('.delete-btn').show();
    });
    frame.open();
  });
  /**
   * Lesson Feature Image Delete
   * @since v.1.5.6
   */

  $(document).on('click', '.tutor-thumbnail-uploader .delete-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var wrapper = $that.closest('.tutor-thumbnail-uploader');
    wrapper.find('input[type="hidden"].tutor-tumbnail-id-input').val('');
    wrapper.find('img').attr('src', '');
    $that.hide();
  });
});

/***/ }),

/***/ "./assets/react/lib/tutor.js":
/*!***********************************!*\
  !*** ./assets/react/lib/tutor.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _v2_library_src_js_main__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../v2-library/_src/js/main */ "./v2-library/_src/js/main.js");
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }



window.tutor_get_nonce_data = function (send_key_value) {
  var nonce_data = window._tutorobject || {};
  var nonce_key = nonce_data.nonce_key || '';
  var nonce_value = nonce_data[nonce_key] || '';

  if (send_key_value) {
    return {
      key: nonce_key,
      value: nonce_value
    };
  }

  return _defineProperty({}, nonce_key, nonce_value);
};

window.tutor_popup = function ($, icon, padding) {
  var $this = this;
  var element;

  this.popup_wrapper = function (wrapper_tag) {
    var img_tag = icon === '' ? '' : '<img class="tutor-pop-icon" src="' + window._tutorobject.tutor_url + 'assets/images/' + icon + '.svg"/>';
    return '<' + wrapper_tag + ' class="tutor-component-popup-container">\
            <div class="tutor-component-popup-' + padding + '">\
                <div class="tutor-component-content-container">' + img_tag + '</div>\
                <div class="tutor-component-button-container"></div>\
            </div>\
        </' + wrapper_tag + '>';
  };

  this.popup = function (data) {
    var title = data.title ? '<h3>' + data.title + '</h3>' : '';
    var description = data.description ? '<p>' + data.description + '</p>' : '';
    var buttons = Object.keys(data.buttons || {}).map(function (key) {
      var button = data.buttons[key];
      var button_id = button.id ? 'tutor-popup-' + button.id : '';
      return $('<button id="' + button_id + '" class="tutor-button tutor-button-' + button["class"] + '">' + button.title + '</button>').click(button.callback);
    });
    element = $($this.popup_wrapper(data.wrapper_tag || 'div'));
    var content_wrapper = element.find('.tutor-component-content-container');
    content_wrapper.append(title);
    data.after_title ? content_wrapper.append(data.after_title) : 0;
    content_wrapper.append(description);
    data.after_description ? content_wrapper.append(data.after_description) : 0; // Assign close event on click black overlay

    element.click(function () {
      $(this).remove();
    }).children().click(function (e) {
      e.stopPropagation();
    }); // Append action button

    for (var i = 0; i < buttons.length; i++) {
      element.find('.tutor-component-button-container').append(buttons[i]);
    }

    $('body').append(element);
    return element;
  };

  return {
    popup: this.popup
  };
};

window.tutorDotLoader = function (loaderType) {
  return "    \n    <div class=\"tutor-dot-loader ".concat(loaderType ? loaderType : '', "\">\n        <span class=\"dot dot-1\"></span>\n        <span class=\"dot dot-2\"></span>\n        <span class=\"dot dot-3\"></span>\n        <span class=\"dot dot-4\"></span>\n    </div>");
};

window.tutor_date_picker = function () {
  if (jQuery.datepicker) {
    var format = _tutorobject.wp_date_format;

    if (!format) {
      format = "yy-mm-dd";
    }

    $(".tutor_date_picker").datepicker({
      "dateFormat": format
    });
  }
};

jQuery(document).ready(function ($) {
  'use strict';

  var _wp$i18n = wp.i18n,
      __ = _wp$i18n.__,
      _x = _wp$i18n._x,
      _n = _wp$i18n._n,
      _nx = _wp$i18n._nx;
  /**
   * Global date_picker selector 
   * 
   * @since 1.9.7
   */

  function load_date_picker() {
    if (jQuery.datepicker) {
      var format = _tutorobject.wp_date_format;

      if (!format) {
        format = "yy-mm-dd";
      }

      $(".tutor_date_picker").datepicker({
        "dateFormat": format
      });
    }
    /** Disable typing on datePicker field */


    $(document).on('keydown', '.hasDatepicker, .tutor_date_picker', function (e) {
      if (e.keyCode !== 8) {
        e.preventDefault();
      }
    });
  }

  ;
  load_date_picker();
  /**
   * Video source tabs
   */

  if (jQuery().select2) {
    $('.videosource_select2').select2({
      width: "100%",
      templateSelection: iformat,
      templateResult: iformat,
      allowHtml: true
    });
  } //videosource_select2


  function iformat(icon) {
    var originalOption = icon.element;
    return $('<span><i class="tutor-icon-' + $(originalOption).data('icon') + '"></i> ' + icon.text + '</span>');
  }
  /**
   * Course Builder
   *
   * @since v.1.3.4
   */


  $(document).on('click', '.tutor-course-thumbnail-upload-btn', function (event) {
    event.preventDefault();
    var $that = $(this);
    var frame;

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: __('Select or Upload Media Of Your Chosen Persuasion', 'tutor'),
      button: {
        text: __('Use this media', 'tutor')
      },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('src', attachment.url);
      $that.closest('.tutor-thumbnail-wrap').find('input').val(attachment.id);
      $('.tutor-course-thumbnail-delete-btn').show();
    });
    frame.open();
  }); //Delete Thumbnail

  $(document).on('click', '.tutor-course-thumbnail-delete-btn', function (event) {
    event.preventDefault();
    var $that = $(this);
    var placeholder_src = $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('data-placeholder-src');
    $that.closest('.tutor-thumbnail-wrap').find('.thumbnail-img').attr('src', placeholder_src);
    $that.closest('.tutor-thumbnail-wrap').find('input').val('');
    $('.tutor-course-thumbnail-delete-btn').hide();
  });
  $(document).on('change keyup', '.course-edit-topic-title-input', function (e) {
    e.preventDefault();
    $(this).closest('.tutor-topics-top').find('.topic-inner-title').html($(this).val());
  });
  $(document).on('click', '.tutor-topics-edit-button', function (e) {
    e.preventDefault();
    var $button = $(this);
    var topics_id = $button.closest('.tutor-topics-wrap').find('[name="topic_id"]').val();
    ;
    var topic_title = $button.closest('.tutor-topics-wrap').find('[name="topic_title"]').val();
    var topic_summery = $button.closest('.tutor-topics-wrap').find('[name="topic_summery"]').val();
    var data = {
      topic_title: topic_title,
      topic_summery: topic_summery,
      topic_id: topics_id,
      action: 'tutor_update_topic'
    };
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $button.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $button.closest('.tutor-topics-wrap').find('span.topic-inner-title').text(topic_title);
          $button.closest('.tutor-modal').removeClass('tutor-is-active');
        }
      },
      complete: function complete() {
        $button.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Delete Lesson from course builder
   */

  $(document).on('click', '.tutor-delete-lesson-btn', function (e) {
    e.preventDefault();

    if (!confirm(__('Are you sure?', 'tutor'))) {
      return;
    }

    var $that = $(this);
    var lesson_id = $that.attr('data-lesson-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        lesson_id: lesson_id,
        action: 'tutor_delete_lesson_by_id'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $that.closest('.course-content-item').remove();
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Quiz Modal
   */

  $(document).on('click', '.modal-close-btn', function (e) {
    e.preventDefault();
    $('.tutor-modal-wrap').removeClass('show');
  });
  $(document).on('keyup', function (e) {
    if (e.keyCode === 27) {
      $('.tutor-modal-wrap').removeClass('show');
    }
  });
  /**
   * Quiz Builder Modal Tabs
   */

  $(document).on('click', '.tutor-quiz-modal-tab-item', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $quizTitle = $('[name="quiz_title"]');
    var quiz_title = $quizTitle.val();

    if (!quiz_title) {
      $quizTitle.closest('.tutor-quiz-builder-form-row').find('.quiz_form_msg').html('<p class="quiz-form-warning">Please save the quiz' + ' first</p>');
      return;
    } else {
      $quizTitle.closest('.tutor-quiz-builder-form-row').find('.quiz_form_msg').html('');
    }

    var tabSelector = $that.attr('href');
    $('.quiz-builder-tab-container').hide();
    $(tabSelector).show();
    $('a.tutor-quiz-modal-tab-item').removeClass('active');
    $that.addClass('active');
  });
  $(document).on('click', '.quiz-modal-tab-navigation-btn.quiz-modal-btn-cancel', function (e) {
    e.preventDefault();
    $('.tutor-modal-wrap').removeClass('show');
  });
  /**
   * Get question answers option edit form
   *
   * @since v.1.0.0
   */

  $(document).on('click', '.tutor-quiz-answer-edit a', function (e) {
    e.preventDefault();
    var $that = $(this);
    var answer_id = $that.closest('.tutor-quiz-answer-wrap').attr('data-answer-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        answer_id: answer_id,
        action: 'tutor_quiz_edit_question_answer'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('#tutor_quiz_question_answer_form').html(data.data.output);
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Saving question answers options
   * Student should select the right answer at quiz attempts
   *
   * @since v.1.0.0
   */

  $(document).on('click', '#quiz-answer-save-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
    $formInput.action = 'tutor_save_quiz_answer_options';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: $formInput,
      beforeSend: function beforeSend() {
        $('#quiz_validation_msg_wrap').html("");
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('#tutor_quiz_question_answers').trigger('refresh');
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Updating Answer
   *
   * @since v.1.0.0
   */

  $(document).on('click', '#quiz-answer-edit-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $formInput = $('#tutor-quiz-question-wrapper :input').serializeObject();
    $formInput.action = 'tutor_update_quiz_answer_options';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: $formInput,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $('#tutor_quiz_question_answers').trigger('refresh');
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  $(document).on('change', '.tutor-quiz-answers-mark-correct-wrap input', function (e) {
    e.preventDefault();
    var $that = $(this);
    var answer_id = $that.val();
    var inputValue = 1;

    if (!$that.prop('checked')) {
      inputValue = 0;
    }

    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        answer_id: answer_id,
        inputValue: inputValue,
        action: 'tutor_mark_answer_as_correct'
      }
    });
  });
  /**
   * Delete answer for a question in quiz builder
   *
   * @since v.1.0.0
   */

  $(document).on('click', '.tutor-quiz-answer-trash-wrap a.answer-trash-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var answer_id = $that.attr('data-answer-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        answer_id: answer_id,
        action: 'tutor_quiz_builder_delete_answer'
      },
      beforeSend: function beforeSend() {
        $that.closest('.tutor-quiz-answer-wrap').remove();
      }
    });
  });
  /**
   * Delete Quiz
   * @since v.1.0.0
   */

  $(document).on('click', '.tutor-delete-quiz-btn', function (e) {
    e.preventDefault();

    if (!confirm(__('Are you sure?', 'tutor'))) {
      return;
    }

    var $that = $(this);
    var quiz_id = $that.attr('data-quiz-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        quiz_id: quiz_id,
        action: 'tutor_delete_quiz_by_id'
      },
      beforeSend: function beforeSend() {
        $that.closest('.course-content-item').remove();
      }
    });
  });
  $(document).on('click', '.tutor-media-upload-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var frame;

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: __('Select or Upload Media Of Your Chosen Persuasion', 'tutor'),
      button: {
        text: __('Use this media', 'tutor')
      },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $that.html('<img src="' + attachment.url + '" alt="" />');
      $that.closest('.tutor-media-upload-wrap').find('input').val(attachment.id);
    });
    frame.open();
  });
  $(document).on('click', '.tutor-media-upload-trash', function (e) {
    e.preventDefault();
    var $that = $(this);
    $that.closest('.tutor-media-upload-wrap').find('.tutor-media-upload-btn').html('<i class="tutor-icon-image1"></i>');
    $that.closest('.tutor-media-upload-wrap').find('input').val('');
  });
  /**
   * Delay Function
   */

  var tutor_delay = function () {
    var timer = 0;
    return function (callback, ms) {
      clearTimeout(timer);
      timer = setTimeout(callback, ms);
    };
  }();
  /**
   * Add instructor modal
   */


  $(document).on('click', '.tutor-add-instructor-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var course_id = $('#post_ID').val();
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        course_id: course_id,
        action: 'tutor_load_instructors_modal'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $('.tutor-instructors-modal-wrap .modal-container').html(data.data.output);
          $('.tutor-instructors-modal-wrap').addClass('show');
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  $(document).on('change keyup', '.tutor-instructors-modal-wrap .tutor-modal-search-input', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $modal = $('.tutor-modal-wrap');
    tutor_delay(function () {
      var search_terms = $that.val();
      var course_id = $('#post_ID').val();
      $.ajax({
        url: window._tutorobject.ajaxurl,
        type: 'POST',
        data: {
          course_id: course_id,
          search_terms: search_terms,
          action: 'tutor_load_instructors_modal'
        },
        beforeSend: function beforeSend() {
          $modal.addClass('loading');
        },
        success: function success(data) {
          if (data.success) {
            $('.tutor-instructors-modal-wrap .modal-container').html(data.data.output);
            $('.tutor-instructors-modal-wrap').addClass('show');
          }
        },
        complete: function complete() {
          $modal.removeClass('loading');
        }
      });
    }, 1000);
  });
  $(document).on('click', '.add_instructor_to_course_btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var $modal = $('.tutor-modal-wrap');
    var course_id = $('#post_ID').val();
    var data = $modal.find('input').serializeObject();
    data.course_id = course_id;
    data.action = 'tutor_add_instructors_to_course';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $('.tutor-course-available-instructors').html(data.data.output);
          $('.tutor-modal-wrap').removeClass('show');
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  $(document).on('click', '.tutor-instructor-delete-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var course_id = $('#post_ID').val();
    var instructor_id = $that.closest('.added-instructor-item').attr('data-instructor-id');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        course_id: course_id,
        instructor_id: instructor_id,
        action: 'detach_instructor_from_course'
      },
      success: function success(data) {
        if (data.success) {
          $that.closest('.added-instructor-item').remove();
        }
      }
    });
  });
  $(document).on('click', '.settings-tabs-navs li', function (e) {
    e.preventDefault();
    var $that = $(this);
    var data_target = $that.find('a').attr('data-target');
    var url = $that.find('a').attr('href');
    $that.addClass('active').siblings('li.active').removeClass('active');
    $('.settings-tab-wrap').removeClass('active').hide();
    $(data_target).addClass('active').show();
    window.history.pushState({}, '', url);
  });
  /**
   * Re init required
   * Modal Loaded...
   */

  $(document).on('lesson_modal_loaded quiz_modal_loaded assignment_modal_loaded', function (e, obj) {
    if (jQuery().select2) {
      $('.select2_multiselect').select2({
        dropdownCssClass: 'increasezindex'
      });
    }

    load_date_picker();
  });
  /**
   * Tutor number validation
   *
   * @since v.1.6.3
   */

  $(document).on('keyup change', '.tutor-number-validation', function (e) {
    var input = $(this);
    var val = parseInt(input.val());
    var min = parseInt(input.attr('data-min'));
    var max = parseInt(input.attr('data-max'));

    if (val < min) {
      input.val(min);
    } else if (val > max) {
      input.val(max);
    }
  });
  /*
  * @since v.1.6.4
  * Quiz Attempts Instructor Feedback 
  */

  $(document).on('click', '.tutor-instructor-feedback', function (e) {
    e.preventDefault();
    var $that = $(this);
    $.ajax({
      url: window.ajaxurl || _tutorobject.ajaxurl,
      type: 'POST',
      data: {
        attempts_id: $that.data('attemptid'),
        feedback: $('.tutor-instructor-feedback-content').val(),
        action: 'tutor_instructor_feedback'
      },
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        if (data.success) {
          $that.closest('.course-content-item').remove();
          tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success');
        }
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Since 1.7.9
   * Announcements scripts
   */

  var add_new_button = $(".tutor-announcement-add-new");
  var update_button = $(".tutor-announcement-edit");
  var delete_button = $(".tutor-announcement-delete");
  var details_button = $(".tutor-announcement-details");
  var close_button = $(".tutor-announcement-close-btn");
  var create_modal = $(".tutor-accouncement-create-modal");
  var update_modal = $(".tutor-accouncement-update-modal");
  var details_modal = $(".tutor-accouncement-details-modal"); //open create modal

  $(add_new_button).click(function () {
    create_modal.addClass("show");
    $("#tutor-annoucement-backend-create-modal").addClass('show');
  });
  $(details_button).click(function () {
    var announcement_date = $(this).attr('announcement-date');
    var announcement_id = $(this).attr('announcement-id');
    var course_id = $(this).attr('course-id');
    var course_name = $(this).attr('course-name');
    var announcement_title = $(this).attr('announcement-title');
    var announcement_summary = $(this).attr('announcement-summary');
    $(".tutor-announcement-detail-content").html("<h3>".concat(announcement_title, "</h3><p>").concat(announcement_summary, "</p>"));
    $(".tutor-announcement-detail-course-info p").html("".concat(course_name));
    $(".tutor-announcement-detail-date-info p").html("".concat(announcement_date)); //set attr on edit button

    $("#tutor-announcement-edit-from-detail").attr('announcement-id', announcement_id);
    $("#tutor-announcement-edit-from-detail").attr('course-id', course_id);
    $("#tutor-announcement-edit-from-detail").attr('announcement-title', announcement_title);
    $("#tutor-announcement-edit-from-detail").attr('announcement-summary', announcement_summary);
    $("#tutor-announcement-delete-from-detail").attr('announcement-id', announcement_id);
    details_modal.addClass("show");
  }); //open update modal

  $(update_button).click(function () {
    if (details_modal) {
      details_modal.removeClass('show');
    }

    var announcement_id = $(this).attr('announcement-id');
    var course_id = $(this).attr('course-id');
    var announcement_title = $(this).attr('announcement-title');
    var announcement_summary = $(this).attr('announcement-summary');
    $("#tutor-announcement-course-id").val(course_id);
    $("#announcement_id").val(announcement_id);
    $("#tutor-announcement-title").val(announcement_title);
    $("#tutor-announcement-summary").val(announcement_summary);
    update_modal.addClass("show");
  }); //close create and update modal

  $(close_button).click(function () {
    create_modal.removeClass("show");
    update_modal.removeClass("show");
    details_modal.removeClass("show");
    $("#tutor-annoucement-backend-create-modal").removeClass('show');
  }); //create announcement

  $(".tutor-announcements-form").on('submit', function (e) {
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    var formData = $(".tutor-announcements-form").serialize() + '&action=tutor_announcement_create' + '&action_type=create';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: formData,
      beforeSend: function beforeSend() {
        $btn.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $(".tutor-alert").remove();

        if (data.status == "success") {
          location.reload();
        }

        if (data.status == "validation_error") {
          $(".tutor-announcements-create-alert").append("<div class=\"tutor-alert alert-warning\"></div>");

          for (var _i = 0, _Object$entries = Object.entries(data.message); _i < _Object$entries.length; _i++) {
            var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
                key = _Object$entries$_i[0],
                value = _Object$entries$_i[1];

            $(".tutor-announcements-create-alert .tutor-alert").append("<li>".concat(value, "</li>"));
          }
        }

        if (data.status == "fail") {
          $(".tutor-announcements-create-alert").html("<li>".concat(data.message, "</li>"));
        }
      },
      error: function error(data) {
        console.log(data);
      }
    });
  }); //update announcement

  $(".tutor-announcements-update-form").on('submit', function (e) {
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    var formData = $(".tutor-announcements-update-form").serialize() + '&action=tutor_announcement_create' + '&action_type=update';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: formData,
      beforeSend: function beforeSend() {
        $btn.addClass('tutor-updating-message');
      },
      success: function success(data) {
        $(".tutor-alert").remove();

        if (data.status == "success") {
          location.reload();
        }

        if (data.status == "validation_error") {
          $(".tutor-announcements-update-alert").append("<div class=\"tutor-alert alert-warning\"></div>");

          for (var _i2 = 0, _Object$entries2 = Object.entries(data.message); _i2 < _Object$entries2.length; _i2++) {
            var _Object$entries2$_i = _slicedToArray(_Object$entries2[_i2], 2),
                key = _Object$entries2$_i[0],
                value = _Object$entries2$_i[1];

            $(".tutor-announcements-update-alert > .tutor-alert").append("<li>".concat(value, "</li>"));
          }
        }

        if (data.status == "fail") {
          $(".tutor-announcements-create-alert").html("<li>".concat(data.message, "</li>"));
        }
      },
      error: function error() {}
    });
  });
  $(delete_button).click(function () {
    var announcement_id = $(this).attr('announcement-id');
    var whichtr = $("#tutor-announcement-tr-" + announcement_id);

    if (confirm("Do you want to delete?")) {
      $.ajax({
        url: window._tutorobject.ajaxurl,
        type: 'POST',
        data: {
          action: 'tutor_announcement_delete',
          announcement_id: announcement_id
        },
        beforeSend: function beforeSend() {},
        success: function success(data) {
          whichtr.remove();

          if (details_modal.length) {
            details_modal.removeClass('show');
          }

          if (data.status == "fail") {
            console.log(data.message);
          }
        },
        error: function error() {}
      });
    }
  }); //sorting 
  // if (jQuery.datepicker){
  //     $( "#tutor-announcement-datepicker" ).datepicker({"dateFormat" : 'yy-mm-dd'});
  // }

  function urlPrams(type, val) {
    var url = new URL(window.location.href);
    var search_params = url.searchParams;
    search_params.set(type, val);
    url.search = search_params.toString();
    search_params.set('paged', 1);
    url.search = search_params.toString();
    return url.toString();
  }

  $('.tutor-announcement-course-sorting').on('change', function (e) {
    window.location = urlPrams('course-id', $(this).val());
  });
  $('.tutor-announcement-order-sorting').on('change', function (e) {
    window.location = urlPrams('order', $(this).val());
  });
  $('.tutor-announcement-date-sorting').on('change', function (e) {
    window.location = urlPrams('date', $(this).val());
  });
  $('.tutor-announcement-search-sorting').on('click', function (e) {
    window.location = urlPrams('search', $(".tutor-announcement-search-field").val());
  }); //dropdown toggle

  $(document).click(function () {
    $(".tutor-dropdown").removeClass('show');
  });
  $(".tutor-dropdown").click(function (e) {
    e.stopPropagation();

    if ($('.tutor-dropdown').hasClass('show')) {
      $('.tutor-dropdown').removeClass('show');
    }

    $(this).addClass('show');
  }); //announcement end

  /**
   * @since v.1.8.6
   * SUbmit form through ajax
   */

  $('.tutor-form-submit-through-ajax').submit(function (e) {
    e.preventDefault();
    var $that = $(this);
    var url = $(this).attr('action') || window.location.href;
    var type = $(this).attr('method') || 'GET';
    var data = $(this).serializeObject();
    $that.find('button').addClass('tutor-updating-message');
    $.ajax({
      url: url,
      type: type,
      data: data,
      success: function success() {
        tutor_toast(__('Success', 'tutor'), $that.data('toast_success_message'), 'success');
      },
      complete: function complete() {
        $that.find('button').removeClass('tutor-updating-message');
      }
    });
  });
  /*
  * @since v.1.7.9
  * Send wp nonce to every ajax request
  */

  $.ajaxSetup({
    data: tutor_get_nonce_data()
  });
});

jQuery.fn.serializeObject = function () {
  var values = {};
  var array = this.serializeArray();
  jQuery.each(array, function () {
    if (values[this.name]) {
      if (!values[this.name].push) {
        values[this.name] = [values[this.name]];
      }

      values[this.name].push(this.value || '');
    } else {
      values[this.name] = this.value || '';
    }
  });
  return values;
};

window.tutor_toast = function (title, description, type) {
  var tutor_ob = window._tutorobject || {};
  var asset = (tutor_ob.tutor_url || '') + 'assets/images/';

  if (!jQuery('.tutor-toast-parent').length) {
    jQuery('body').append('<div class="tutor-toast-parent"></div>');
  }

  var icons = {
    success: asset + 'icon-check.svg',
    error: asset + 'icon-cross.svg'
  };
  var content = jQuery('\
        <div>\
            <div>\
                <img src="' + icons[type] + '"/>\
            </div>\
            <div>\
                <div>\
                    <b>' + title + '</b>\
                    <span>' + description + '</span>\
                </div>\
            </div>\
            <div>\
                <i class="tutor-toast-close tutor-icon-line-cross"></i>\
            </div>\
        </div>');
  content.find('.tutor-toast-close').click(function () {
    content.remove();
  });
  jQuery('.tutor-toast-parent').append(content);
  setTimeout(function () {
    if (content) {
      content.fadeOut('fast', function () {
        jQuery(this).remove();
      });
    }
  }, 5000);
};

/***/ }),

/***/ "./v2-library/_src/js/main.js":
/*!************************************!*\
  !*** ./v2-library/_src/js/main.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _tutorModal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./tutorModal */ "./v2-library/_src/js/tutorModal.js");
/* harmony import */ var _tutorModal__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_tutorModal__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _thumbnailUploadPreview__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./thumbnailUploadPreview */ "./v2-library/_src/js/thumbnailUploadPreview.js");
/* harmony import */ var _thumbnailUploadPreview__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_thumbnailUploadPreview__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _popupMenu__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./popupMenu */ "./v2-library/_src/js/popupMenu.js");
/* harmony import */ var _popupMenu__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_popupMenu__WEBPACK_IMPORTED_MODULE_2__);




/***/ }),

/***/ "./v2-library/_src/js/popupMenu.js":
/*!*****************************************!*\
  !*** ./v2-library/_src/js/popupMenu.js ***!
  \*****************************************/
/***/ (() => {

(function popupMenuToggle() {
  /**
   * Popup Menu Toggle .tutor-popup-opener
   */

  /*
  const popupToggleBtns = document.querySelectorAll('.tutor-popup-opener .popup-btn');
  const popupMenus = document.querySelectorAll('.tutor-popup-opener .popup-menu');
  	 if (popupToggleBtns && popupMenus) {
  	popupToggleBtns.forEach((btn) => {
  		btn.addEventListener('click', (e) => {
  			const popupClosest = e.target.closest('.tutor-popup-opener').querySelector('.popup-menu');
  			popupClosest.classList.toggle('visible');
  				popupMenus.forEach((popupMenu) => {
  				if (popupMenu !== popupClosest) {
  					popupMenu.classList.remove('visible');
  				}
  			});
  		});
  	});
  		document.addEventListener('click', (e) => {
  		if (!e.target.matches('.tutor-popup-opener .popup-btn')) {
  			popupMenus.forEach((popupMenu) => {
  				if (popupMenu.classList.contains('visible')) {
  					popupMenu.classList.remove('visible');
  				}
  			});
  		}
  	});
  } */
  document.addEventListener('click', function (e) {
    var attr = 'data-tutor-popup-target';

    if (e.target.hasAttribute(attr)) {
      e.preventDefault();
      var id = e.target.hasAttribute(attr) ? e.target.getAttribute(attr) : e.target.closest("[".concat(attr, "]")).getAttribute(attr);
      var popupMenu = document.getElementById(id);

      if (popupMenu.classList.contains('visible')) {
        popupMenu.classList.remove('visible');
      } else {
        document.querySelectorAll('.tutor-popup-opener .popup-menu').forEach(function (popupMenu) {
          popupMenu.classList.remove('visible');
        });
        popupMenu.classList.add('visible');
      }
    } else {
      document.querySelectorAll('.tutor-popup-opener .popup-menu').forEach(function (popupMenu) {
        popupMenu.classList.remove('visible');
      });
    }
  });
})();

/***/ }),

/***/ "./v2-library/_src/js/thumbnailUploadPreview.js":
/*!******************************************************!*\
  !*** ./v2-library/_src/js/thumbnailUploadPreview.js ***!
  \******************************************************/
/***/ (() => {

(function thumbnailUploadPreview() {
  /**
   * Image Preview : Logo and Signature Upload
   * Selector -> .tutor-option-field-input.image-previewer
   */
  var imgPreviewers = document.querySelectorAll('.tutor-thumbnail-uploader');
  var imgPreviews = document.querySelectorAll('.tutor-thumbnail-uploader img');
  var imgPrevInputs = document.querySelectorAll('.tutor-thumbnail-uploader input[type=file]');
  var imgPrevDelBtns = document.querySelectorAll('.tutor-thumbnail-uploader .delete-btn');

  if (imgPrevInputs && imgPrevDelBtns) {
    // Checking Img Src when document loads
    document.addEventListener('DOMContentLoaded', function () {
      imgPreviewers.forEach(function (previewer) {
        imgPreviews.forEach(function (img) {
          if (img.getAttribute('src')) {
            img.closest('.image-previewer').classList.add('is-selected');
          } else {
            previewer.classList.remove('is-selected');
          }

          console.log(img);
        });
      });
    }); // Updating Image Preview

    imgPrevInputs.forEach(function (input) {
      input.addEventListener('change', function (e) {
        var file = this.files[0];
        var parentEl = input.closest('.image-previewer');
        var targetImg = parentEl.querySelector('img');
        var prevLoader = parentEl.querySelector('.preview-loading');

        if (file) {
          prevLoader.classList.add('is-loading');
          getImageAsDataURL(file, targetImg);
          parentEl.classList.add('is-selected');
          setTimeout(function () {
            prevLoader.classList.remove('is-loading');
          }, 200);
        }
      });
    }); // Deleting Image Preview

    imgPrevDelBtns.forEach(function (delBtn) {
      delBtn.addEventListener('click', function (e) {
        var parentEl = this.closest('.image-previewer');
        var targetImg = parentEl.querySelector('img');
        targetImg.setAttribute('src', '');
        parentEl.classList.remove('is-selected');
      });
    });
  } // Get Image file as Data URL


  var getImageAsDataURL = function getImageAsDataURL(file, imgSrc) {
    var reader = new FileReader();

    reader.onload = function () {
      imgSrc.setAttribute('src', this.result);
    };

    reader.readAsDataURL(file);
  };
})();

/***/ }),

/***/ "./v2-library/_src/js/tutorModal.js":
/*!******************************************!*\
  !*** ./v2-library/_src/js/tutorModal.js ***!
  \******************************************/
/***/ (() => {

(function () {
  'use strict'; // modal

  tutorModal();
})();

function tutorModal() {
  document.addEventListener('click', function (e) {
    var attr = 'data-tutor-modal-target';
    var closeAttr = 'data-tutor-modal-close';
    var overlay = 'tutor-modal-overlay';

    if (e.target.hasAttribute(attr) || e.target.closest("[".concat(attr, "]"))) {
      e.preventDefault();
      var id = e.target.hasAttribute(attr) ? e.target.getAttribute(attr) : e.target.closest("[".concat(attr, "]")).getAttribute(attr);
      var modal = document.getElementById(id);

      if (modal) {
        modal.classList.add('tutor-is-active');
      }
    }

    if (e.target.hasAttribute(closeAttr) || e.target.classList.contains(overlay) || e.target.closest("[".concat(closeAttr, "]"))) {
      e.preventDefault();

      var _modal = document.querySelectorAll('.tutor-modal.tutor-is-active');

      _modal.forEach(function (m) {
        m.classList.remove('tutor-is-active');
      });
    }
  }); // open
  // const modalButton = document.querySelectorAll("[data-tutor-modal-target]");
  // modalButton.forEach(b => {
  //     const id = b.getAttribute("data-tutor-modal-target");
  //     const modal = document.getElementById(id);
  //     if (modal) {
  //         b.addEventListener("click", e => {
  //             e.preventDefault();
  //             modal.classList.add("tutor-is-active");
  //         })
  //     }
  // })
  // close
  // const close = document.querySelectorAll("[data-tutor-modal-close], .tutor-modal-overlay");
  // close.forEach(c => {
  //     c.addEventListener("click", e => {
  //         e.preventDefault();
  //         const modal = document.querySelectorAll(".tutor-modal.tutor-is-active");
  //         modal.forEach(m => {
  //             m.classList.remove("tutor-is-active");
  //         })
  //     })
  // })
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!*****************************************************!*\
  !*** ./assets/react/admin-dashboard/tutor-admin.js ***!
  \*****************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _lib_common__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../lib/common */ "./assets/react/lib/common.js");
/* harmony import */ var _segments_image_preview__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./segments/image-preview */ "./assets/react/admin-dashboard/segments/image-preview.js");
/* harmony import */ var _segments_image_preview__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_segments_image_preview__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _segments_options__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./segments/options */ "./assets/react/admin-dashboard/segments/options.js");
/* harmony import */ var _segments_options__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_segments_options__WEBPACK_IMPORTED_MODULE_2__);
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }




jQuery(document).ready(function ($) {
  'use strict';

  var _wp$i18n = wp.i18n,
      __ = _wp$i18n.__,
      _x = _wp$i18n._x,
      _n = _wp$i18n._n,
      _nx = _wp$i18n._nx;

  var search_student_placeholder = __('Search students', 'tutor');
  /**
   * Color Picker
   * @since v.1.2.21
   */


  if (jQuery().wpColorPicker) {
    $('.tutor_colorpicker').wpColorPicker();
  }

  if (jQuery().select2) {
    $('.tutor_select2').select2();
  }
  /**
   * Option Settings Nav Tab
   */


  $('.tutor-option-nav-tabs li a').click(function (e) {
    e.preventDefault();
    var tab_page_id = $(this).attr('data-tab');
    $('.option-nav-item').removeClass('current');
    $(this).closest('li').addClass('current');
    $('.tutor-option-nav-page').hide();
    $(tab_page_id).addClass('current-page').show();
    window.history.pushState('obj', '', $(this).attr('href'));
  });
  $('#save_tutor_option').click(function (e) {
    e.preventDefault();
    $(this).closest('form').submit();
  });
  $('#tutor-option-form').submit(function (e) {
    e.preventDefault();
    var $form = $(this);
    var data = $form.serializeObject();
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: data,
      beforeSend: function beforeSend() {
        $form.find('.button').addClass('tutor-updating-message');
      },
      success: function success(data) {
        data.success ? tutor_toast(__('Saved', 'tutor'), $form.data('toast_success_message'), 'success') : tutor_toast(__('Request Error', 'tutor'), __('Could not save', 'tutor'), 'error');
      },
      complete: function complete() {
        $form.find('.button').removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Withdraw nav tabs
   * @since v.1.1.2
   */

  $(document).on('click', '.withdraw-method-nav li a', function (e) {
    e.preventDefault();
    var tab_page_id = $(this).attr('data-target-id');
    $('.withdraw-method-form-wrap').hide();
    $('#' + tab_page_id).show();
  });
  /**
   * End Withdraw nav tabs
   */

  $(document).on('click', '.video_source_wrap_html5 .video_upload_btn', function (event) {
    event.preventDefault();
    var $that = $(this);
    var frame; // If the media frame already exists, reopen it.

    if (frame) {
      frame.open();
      return;
    } // Create a new media frame


    frame = wp.media({
      title: __('Select or Upload Media Of Your Choice', 'tutor'),
      button: {
        text: __('Upload media', 'tutor')
      },
      library: {
        type: 'video'
      },
      multiple: false // Set to true to allow multiple files to be selected

    }); // When an image is selected in the media frame...

    frame.on('select', function () {
      // Get media attachment details from the frame state
      var attachment = frame.state().get('selection').first().toJSON();
      $that.closest('.video_source_wrap_html5').find('span.video_media_id').data('video_url', attachment.url).text(attachment.id).trigger('paste').closest('p').show();
      $that.closest('.video_source_wrap_html5').find('input.input_source_video_id').val(attachment.id);
    }); // Finally, open the modal on click

    frame.open();
  });
  /**
   * Open Sidebar Menu
   */

  if (_tutorobject.open_tutor_admin_menu) {
    var $adminMenu = $('#adminmenu');
    $adminMenu.find('[href="admin.php?page=tutor"]').closest('li.wp-has-submenu').addClass('wp-has-current-submenu');
    $adminMenu.find('[href="admin.php?page=tutor"]').closest('li.wp-has-submenu').find('a.wp-has-submenu').removeClass('wp-has-current-submenu').addClass('wp-has-current-submenu');
  }

  $(document).on('click', '.tutor-option-media-upload-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    var frame;

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: __('Select or Upload Media Of Your Choice', 'tutor'),
      button: {
        text: __('Upload media', 'tutor')
      },
      multiple: false
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $that.closest('.option-media-wrap').find('.option-media-preview').html('<img src="' + attachment.url + '" alt="" />');
      $that.closest('.option-media-wrap').find('input').val(attachment.id);
      $that.closest('.option-media-wrap').find('.tutor-media-option-trash-btn').show();
    });
    frame.open();
  });
  /**
   * Remove option media
   * @since v.1.4.3
   */

  $(document).on('click', '.tutor-media-option-trash-btn', function (e) {
    e.preventDefault();
    var $that = $(this);
    $that.closest('.option-media-wrap').find('img').remove();
    $that.closest('.option-media-wrap').find('input').val('');
    $that.closest('.option-media-wrap').find('.tutor-media-option-trash-btn').hide();
  });
  $(document).on('change', '.tutor_addons_list_item', function (e) {
    var $that = $(this);
    var isEnable = $that.prop('checked') ? 1 : 0;
    var addonFieldName = $that.attr('name');
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: {
        isEnable: isEnable,
        addonFieldName: addonFieldName,
        action: 'addon_enable_disable'
      },
      success: function success(data) {
        if (data.success) {//Success
        }
      }
    });
  });
  /**
   * Add instructor
   * @since v.1.0.3
   */

  $(document).on('submit', '#new-instructor-form', function (e) {
    e.preventDefault();
    var $that = $(this);
    var formData = $that.serializeObject();
    formData.action = 'tutor_add_instructor';
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: formData,
      success: function success(data) {
        if (data.success) {
          $that.trigger("reset");
          $('#form-response').html('<p class="tutor-status-approved-context">' + data.data.msg + '</p>');
        } else {
          var errorMsg = '';
          var errors = data.data.errors;

          if (errors && Object.keys(errors).length) {
            $.each(data.data.errors, function (index, value) {
              if (value && _typeof(value) === 'object' && value.constructor === Object) {
                $.each(value, function (key, value1) {
                  errorMsg += '<p class="tutor-required-fields">' + value1[0] + '</p>';
                });
              } else {
                errorMsg += '<p class="tutor-required-fields">' + value + '</p>';
              }
            });
            $('#form-response').html(errorMsg);
          }
        }
      }
    });
  });
  /**
   * Instructor block unblock action
   * @since v.1.5.3
   */

  $(document).on('click', 'a.instructor-action', function (e) {
    e.preventDefault();
    var $that = $(this);
    var action = $that.attr('data-action');
    var instructor_id = $that.attr('data-instructor-id');
    var prompt_message = $that.attr('data-prompt-message');

    if (prompt_message && !confirm(prompt_message)) {
      // Avoid Accidental CLick
      return;
    }

    var nonce_key = _tutorobject.nonce_key;
    var json_data = {
      instructor_id: instructor_id,
      action_name: action,
      action: 'instructor_approval_action'
    };
    json_data[nonce_key] = _tutorobject[nonce_key];
    $.ajax({
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      data: json_data,
      beforeSend: function beforeSend() {
        $that.addClass('tutor-updating-message');
      },
      success: function success(data) {
        location.reload(true);
      },
      complete: function complete() {
        $that.removeClass('tutor-updating-message');
      }
    });
  });
  /**
   * Add Assignment
   */

  $(document).on('click', '.add-assignment-attachments', function (event) {
    event.preventDefault();
    var $that = $(this);
    var frame; // If the media frame already exists, reopen it.

    if (frame) {
      frame.open();
      return;
    } // Create a new media frame


    frame = wp.media({
      title: __('Select or Upload Media Of Your Choice', 'tutor'),
      button: {
        text: __('Upload media', 'tutor')
      },
      multiple: false // Set to true to allow multiple files to be selected

    }); // When an image is selected in the media frame...

    frame.on('select', function () {
      // Get media attachment details from the frame state
      var attachment = frame.state().get('selection').first().toJSON();
      var field_markup = '<div class="tutor-individual-attachment-file"><p class="attachment-file-name">' + attachment.filename + '</p><input type="hidden" name="tutor_assignment_attachments[]" value="' + attachment.id + '"><a href="javascript:;" class="remove-assignment-attachment-a text-muted"> &times; Remove</a></div>';
      $('#assignment-attached-file').append(field_markup);
      $that.closest('.video_source_wrap_html5').find('input').val(attachment.id);
    }); // Finally, open the modal on click

    frame.open();
  });
  $(document).on('click', '.remove-assignment-attachment-a', function (event) {
    event.preventDefault();
    $(this).closest('.tutor-individual-attachment-file').remove();
  });
  /**
   * Used for backend profile photo upload.
   */
  //tutor_video_poster_upload_btn

  $(document).on('click', '.tutor_video_poster_upload_btn', function (event) {
    event.preventDefault();
    var $that = $(this);
    var frame; // If the media frame already exists, reopen it.

    if (frame) {
      frame.open();
      return;
    } // Create a new media frame


    frame = wp.media({
      title: __('Select or Upload Media Of Your Choice', 'tutor'),
      button: {
        text: __('Upload media', 'tutor')
      },
      multiple: false // Set to true to allow multiple files to be selected

    }); // When an image is selected in the media frame...

    frame.on('select', function () {
      // Get media attachment details from the frame state
      var attachment = frame.state().get('selection').first().toJSON();
      $that.closest('.tutor-video-poster-wrap').find('.video-poster-img').html('<img src="' + attachment.sizes.thumbnail.url + '" alt="" />');
      $that.closest('.tutor-video-poster-wrap').find('input').val(attachment.id);
    }); // Finally, open the modal on click

    frame.open();
  });
  /**
   * Tutor Memberships toggle in Paid Membership Pro panel
   * @since v.1.3.6
   */

  $(document).on('change', '#tutor_pmpro_membership_model_select', function (e) {
    e.preventDefault();
    var $that = $(this);

    if ($that.val() === 'category_wise_membership') {
      $('.membership_course_categories').show();
    } else {
      $('.membership_course_categories').hide();
    }
  });
  $(document).on('change', '#tutor_pmpro_membership_model_select', function (e) {
    e.preventDefault();
    var $that = $(this);

    if ($that.val() === 'category_wise_membership') {
      $('.membership_course_categories').show();
    } else {
      $('.membership_course_categories').hide();
    }
  }); // Require category selection

  $(document).on('submit', '.pmpro_admin form', function (e) {
    var form = $(this);

    if (!form.find('input[name="tutor_action"]').length) {
      // Level editor or tutor action not necessary
      return;
    }

    if (form.find('[name="tutor_pmpro_membership_model"]').val() == 'category_wise_membership' && !form.find('.membership_course_categories input:checked').length) {
      if (!confirm(__('Do you want to save without any category?', 'tutor'))) {
        e.preventDefault();
      }
    }
  });
  /**
   * Find user/student from select2
   * @since v.1.4.0
   */

  $('#select2_search_user_ajax').select2({
    allowClear: true,
    minimumInputLength: 1,
    placeholder: search_student_placeholder,
    language: {
      inputTooShort: function inputTooShort() {
        return __('Please add 1 or more character', 'tutor');
      }
    },
    escapeMarkup: function escapeMarkup(m) {
      return m;
    },
    ajax: {
      url: window._tutorobject.ajaxurl,
      type: 'POST',
      dataType: 'json',
      delay: 1000,
      data: function data(params) {
        return {
          term: params.term,
          action: 'tutor_json_search_students'
        };
      },
      processResults: function processResults(data) {
        var terms = [];

        if (data) {
          $.each(data, function (id, text) {
            terms.push({
              id: id,
              text: text
            });
          });
        }

        return {
          results: terms
        };
      },
      cache: true
    }
  });
  /**
   * Confirm Alert for deleting enrollments data
   *
   * @since v.1.4.0
   */

  $(document).on('click', 'table.enrolments .delete a', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var popup;
    var data = {
      title: __('Delete this enrolment', 'tutor'),
      description: __('All of the course data like quiz attempts, assignment, lesson <br/>progress will be deleted if you delete this student\'s enrollment.', 'tutor'),
      buttons: {
        reset: {
          title: __('Cancel', 'tutor'),
          "class": 'secondary',
          callback: function callback() {
            popup.remove();
          }
        },
        keep: {
          title: __('Yes, Delete This', 'tutor'),
          "class": 'primary',
          callback: function callback() {
            window.location.replace(url);
          }
        }
      }
    };
    popup = new window.tutor_popup($, 'icon-trash', 40).popup(data);
  });
  /**
   * Show hide is course public checkbox (backend dashboard editor)
   * 
   * @since  v.1.7.2
  */

  var price_type = $('#tutor-attach-product [name="tutor_course_price_type"]');

  if (price_type.length == 0) {
    $('#_tutor_is_course_public_meta_checkbox').show();
  } else {
    price_type.change(function () {
      if ($(this).prop('checked')) {
        var method = $(this).val() == 'paid' ? 'hide' : 'show';
        $('#_tutor_is_course_public_meta_checkbox')[method]();
      }
    }).trigger('change');
  }
  /**
   * Focus selected instructor layout in setting page
   * 
   * @since  v.1.7.5
  */


  $(document).on('click', '.instructor-layout-template', function () {
    $('.instructor-layout-template').removeClass('selected-template');
    $(this).addClass('selected-template');
  });
  /**
   * Programmatically open preview link. For some reason it's not working normally.
   * 
   * @since  v.1.7.9
  */

  $('#preview-action a.preview').click(function (e) {
    var href = $(this).attr('href');

    if (href) {
      e.preventDefault();
      window.open(href, '_blank');
    }
  });
});
})();

/******/ })()
;
//# sourceMappingURL=tutor-admin.js.map