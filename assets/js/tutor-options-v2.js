jQuery(document).ready(function ($) {
  "use strict";

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
      beforeSend: function () {},
      success: function (data) {
        // console.log(data.data);
        $(".tutor-notification").addClass("show");
      },
      complete: function () {},
    });
  });

  // Svg icon
  const svgWarningIcon = `<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.0388 14.2395C18.2457 14.5683 18.3477 14.9488 18.3321 15.3333C18.3235 15.6951 18.2227 16.0493 18.0388 16.3647C17.851 16.6762 17.5885 16.9395 17.2733 17.1326C16.9301 17.3257 16.5383 17.4237 16.1412 17.4159H5.87591C5.47974 17.4234 5.08907 17.3253 4.74673 17.1326C4.42502 16.9409 4.15549 16.6776 3.96071 16.3647C3.77376 16.0506 3.67282 15.6956 3.66741 15.3333C3.6596 14.9496 3.76106 14.5713 3.96071 14.2395L9.11094 5.64829C9.29701 5.31063 9.58016 5.03215 9.9263 4.84641C10.2558 4.67355 10.6248 4.58301 10.9998 4.58301C11.3747 4.58301 11.7437 4.67355 12.0732 4.84641C12.4259 5.02952 12.7154 5.30825 12.9062 5.64829L18.0388 14.2395ZM11.7447 10.4086C11.7447 10.2131 11.7653 10.0176 11.7799 9.81924C11.7946 9.62089 11.8063 9.41971 11.818 9.21853C11.8178 9.1484 11.8129 9.07836 11.8034 9.00885C11.7916 8.94265 11.7719 8.87799 11.7447 8.81617C11.6644 8.64655 11.5255 8.50928 11.3517 8.42798C11.1805 8.3467 10.9848 8.32759 10.8003 8.37414C10.6088 8.42217 10.4413 8.53471 10.3281 8.69149C10.213 8.84985 10.1525 9.03921 10.1551 9.2327C10.1551 9.3602 10.1756 9.48771 10.1844 9.61239C10.1932 9.73706 10.202 9.86457 10.2137 9.99208C10.2401 10.4709 10.2695 10.947 10.2988 11.4088C10.3281 11.8707 10.3545 12.3552 10.3838 12.8256C10.3857 12.9019 10.4032 12.9771 10.4352 13.0468C10.4672 13.1166 10.5131 13.1796 10.5703 13.2322C10.6275 13.2849 10.6948 13.3261 10.7685 13.3536C10.8422 13.381 10.9208 13.3942 10.9998 13.3923C11.0794 13.3946 11.1587 13.3813 11.2328 13.353C11.307 13.3248 11.3744 13.2822 11.4309 13.228C11.5454 13.1171 11.6115 12.968 11.6157 12.8114V12.5281C11.6157 12.4317 11.6157 12.3382 11.6157 12.2447C11.6362 11.9415 11.6538 11.6327 11.6743 11.3238C11.6949 11.015 11.7271 10.7118 11.7447 10.4086ZM10.9998 15.5118C11.1049 15.5119 11.2091 15.4919 11.3062 15.453C11.4034 15.4141 11.4916 15.3571 11.5658 15.2851C11.6441 15.2191 11.7061 15.137 11.7472 15.0448C11.7883 14.9526 11.8075 14.8527 11.8034 14.7524C11.8053 14.6497 11.7863 14.5476 11.7474 14.452C11.7085 14.3564 11.6505 14.2692 11.5767 14.1953C11.5029 14.1213 11.4147 14.0621 11.3172 14.0211C11.2197 13.9801 11.1149 13.958 11.0086 13.9562C10.9023 13.9543 10.7966 13.9727 10.6977 14.0103C10.5987 14.0479 10.5084 14.1039 10.4319 14.1752C10.3553 14.2465 10.2941 14.3317 10.2516 14.4259C10.2092 14.52 10.1863 14.6214 10.1844 14.7241C10.1844 14.933 10.2703 15.1333 10.4232 15.2811C10.5761 15.4288 10.7835 15.5118 10.9998 15.5118Z" fill="#9CA0AC"/></svg>`;

  const svgMagnifyingGlass = `<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.3056 5.375C7.58249 5.375 5.375 7.58249 5.375 10.3056C5.375 13.0286 7.58249 15.2361 10.3056 15.2361C13.0286 15.2361 15.2361 13.0286 15.2361 10.3056C15.2361 7.58249 13.0286 5.375 10.3056 5.375ZM4.125 10.3056C4.125 6.89214 6.89214 4.125 10.3056 4.125C13.719 4.125 16.4861 6.89214 16.4861 10.3056C16.4861 13.719 13.719 16.4861 10.3056 16.4861C6.89214 16.4861 4.125 13.719 4.125 10.3056Z" fill="#9CA0AC"/><path fill-rule="evenodd" clip-rule="evenodd" d="M13.7874 13.7872C14.0314 13.5431 14.4272 13.5431 14.6712 13.7872L17.6921 16.8081C17.9362 17.0521 17.9362 17.4479 17.6921 17.6919C17.448 17.936 17.0523 17.936 16.8082 17.6919L13.7874 14.6711C13.5433 14.427 13.5433 14.0313 13.7874 13.7872Z" fill="#9CA0AC"/></svg>`;

  const svgAngleRight = `<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.842 12.633C7.80402 12.6702 7.7592 12.6998 7.71 12.72C7.65839 12.7401 7.60341 12.7503 7.548 12.75C7.49655 12.7496 7.44563 12.7395 7.398 12.72C7.34843 12.7005 7.30347 12.6709 7.266 12.633L6.88201 12.252C6.84384 12.2138 6.81284 12.1691 6.79051 12.12C6.76739 12.0694 6.75367 12.015 6.75001 11.9595C6.74971 11.9045 6.75832 11.8498 6.77551 11.7975C6.79308 11.7477 6.82181 11.7025 6.85951 11.6655L9.53249 9.00001L6.86701 6.33453C6.82576 6.29904 6.79427 6.2536 6.77551 6.20253C6.75832 6.15026 6.74971 6.09555 6.75001 6.04053C6.75367 5.98502 6.76739 5.93064 6.79051 5.88003C6.81284 5.8309 6.84384 5.78619 6.88201 5.74803L7.263 5.36704C7.30047 5.32916 7.34543 5.29953 7.395 5.28004C7.44263 5.26056 7.49355 5.25038 7.545 5.25004C7.60142 5.24931 7.65745 5.2595 7.71 5.28004C7.7592 5.30025 7.80402 5.3298 7.842 5.36704L11.181 8.70752C11.2233 8.74442 11.2579 8.78926 11.283 8.83951C11.3077 8.88941 11.3206 8.94433 11.3206 9.00001C11.3206 9.05569 11.3077 9.11062 11.283 9.16051C11.2579 9.21076 11.2233 9.25561 11.181 9.29251L7.842 12.633Z" fill="#B4B7C0"/></svg>`;

  function titleCase(str) {
    var splitStr = str.toLowerCase().split(" ");
    for (var i = 0; i < splitStr.length; i++) {
      // You do not need to check if i is larger than splitStr length, as your for does that for you
      // Assign it back to the array
      splitStr[i] =
        splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
    }
    // Directly return the joined string
    return splitStr.join(" ");
  }

  function view_item(text, section_slug, section, block, field_key) {
    var navTrack = block ? `${svgAngleRight} ${block}` : "";

    var output = `
      <a data-tab="${section_slug}" data-key="field_${field_key}">
        <div class="search_result_title">
          ${svgMagnifyingGlass}
          <span class="text-regular-caption">${text}</span>
        </div>
        <div class="search_navigation">
          <div class="nav-track text-regular-small">
            <span>${section}</span>
            <span>${navTrack}</span>
          </div>
        </div>
      </a>`;

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
          keyword: searchKey,
        },
        // beforeSend: function () {},
        success: function (data) {
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
            item_text = item.label;
            section_slug = item.section_slug;
            section_label = item.section_label;
            block_label = item.block_label;
            field_key = item.key;
            searchKeyRegex = new RegExp(searchKey, "ig");
            // console.log(item_text.match(searchKeyRegex));
            matchedText = item_text.match(searchKeyRegex)?.[0];

            if (matchedText) {
              wrapped_item = item_text.replace(
                searchKeyRegex,
                `<span style='color: #212327; font-weight:500'>${matchedText}</span>`
              );
              output += view_item(
                wrapped_item,
                section_slug,
                section_label,
                block_label,
                field_key
              );
              notfound = false;
            }
          });
          if (notfound) {
            output += `<div class="no_item"> ${svgWarningIcon} No Results Found</div>`;
          }
          $(".search_result").html(output).addClass("show");
          output = "";
          // console.log("working");
        },
        complete: function () {
          // Active navigation element
          navigationTrigger();
        },
      });
    } else {
      document.querySelector(".search-popup-opener").classList.remove("show");
    }
  });
});

/**
 * Search suggestion, navigation trigger
 */
function navigationTrigger() {
  const suggestionLinks = document.querySelectorAll(
    ".search-field .search-popup-opener a"
  );
  const navTabItems = document.querySelectorAll("li.tutor-option-nav-item a");
  const navPages = document.querySelectorAll(".tutor-option-nav-page");

  suggestionLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      const dataTab = e.target.closest("[data-tab]").dataset.tab;
      const dataKey = e.target.closest("[data-key]").dataset.key;
      if (dataTab) {
        // remove active from other buttons
        navTabItems.forEach((item) => {
          item.classList.remove("active");
        });
        // add active to the current nav item
        document
          .querySelector(`.tutor-option-tabs [data-tab=${dataTab}]`)
          .classList.add("active");

        // hide other tab contents
        navPages.forEach((content) => {
          content.classList.remove("active");
        });
        // add active to the current content
        document
          .querySelector(`.tutor-option-tab-pages #${dataTab}`)
          .classList.add("active");

        // History push
        const url = new URL(window.location);
        url.searchParams.set("tab_page", dataTab);
        window.history.pushState({}, "", url);
      }

      // Reset + Hide Suggestion box
      document.querySelector(".search-popup-opener").classList.remove("show");
      document.querySelector('.search-field input[type="search"]').value = "";

      // Highlight selected element
      highlightSearchedItem(dataKey);
    });
  });
}


/**
 * Highlight items form search suggestion
 */
function highlightSearchedItem(dataKey){
  console.log(dataKey);
  const targetEl = document.querySelector(`#${dataKey} .tutor-option-field-label label`);
  const scrollTargetEl = document.querySelector(`#${dataKey}`).parentNode.parentNode;
  console.dir(scrollTargetEl);

  targetEl.classList.add('isHighlighted');
  setTimeout(() => {
    targetEl.classList.remove('isHighlighted');
  }, 6000);

  scrollTargetEl.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});

}