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
  const svgWarningIcon = `<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M18.0388 14.2395C18.2457 14.5683 18.3477 14.9488 18.3321 15.3333C18.3235 15.6951 18.2227 16.0493 18.0388 16.3647C17.851 16.6762 17.5885 16.9395 17.2733 17.1326C16.9301 17.3257 16.5383 17.4237 16.1412 17.4159H5.87591C5.47974 17.4234 5.08907 17.3253 4.74673 17.1326C4.42502 16.9409 4.15549 16.6776 3.96071 16.3647C3.77376 16.0506 3.67282 15.6956 3.66741 15.3333C3.6596 14.9496 3.76106 14.5713 3.96071 14.2395L9.11094 5.64829C9.29701 5.31063 9.58016 5.03215 9.9263 4.84641C10.2558 4.67355 10.6248 4.58301 10.9998 4.58301C11.3747 4.58301 11.7437 4.67355 12.0732 4.84641C12.4259 5.02952 12.7154 5.30825 12.9062 5.64829L18.0388 14.2395ZM11.7447 10.4086C11.7447 10.2131 11.7653 10.0176 11.7799 9.81924C11.7946 9.62089 11.8063 9.41971 11.818 9.21853C11.8178 9.1484 11.8129 9.07836 11.8034 9.00885C11.7916 8.94265 11.7719 8.87799 11.7447 8.81617C11.6644 8.64655 11.5255 8.50928 11.3517 8.42798C11.1805 8.3467 10.9848 8.32759 10.8003 8.37414C10.6088 8.42217 10.4413 8.53471 10.3281 8.69149C10.213 8.84985 10.1525 9.03921 10.1551 9.2327C10.1551 9.3602 10.1756 9.48771 10.1844 9.61239C10.1932 9.73706 10.202 9.86457 10.2137 9.99208C10.2401 10.4709 10.2695 10.947 10.2988 11.4088C10.3281 11.8707 10.3545 12.3552 10.3838 12.8256C10.3857 12.9019 10.4032 12.9771 10.4352 13.0468C10.4672 13.1166 10.5131 13.1796 10.5703 13.2322C10.6275 13.2849 10.6948 13.3261 10.7685 13.3536C10.8422 13.381 10.9208 13.3942 10.9998 13.3923C11.0794 13.3946 11.1587 13.3813 11.2328 13.353C11.307 13.3248 11.3744 13.2822 11.4309 13.228C11.5454 13.1171 11.6115 12.968 11.6157 12.8114V12.5281C11.6157 12.4317 11.6157 12.3382 11.6157 12.2447C11.6362 11.9415 11.6538 11.6327 11.6743 11.3238C11.6949 11.015 11.7271 10.7118 11.7447 10.4086ZM10.9998 15.5118C11.1049 15.5119 11.2091 15.4919 11.3062 15.453C11.4034 15.4141 11.4916 15.3571 11.5658 15.2851C11.6441 15.2191 11.7061 15.137 11.7472 15.0448C11.7883 14.9526 11.8075 14.8527 11.8034 14.7524C11.8053 14.6497 11.7863 14.5476 11.7474 14.452C11.7085 14.3564 11.6505 14.2692 11.5767 14.1953C11.5029 14.1213 11.4147 14.0621 11.3172 14.0211C11.2197 13.9801 11.1149 13.958 11.0086 13.9562C10.9023 13.9543 10.7966 13.9727 10.6977 14.0103C10.5987 14.0479 10.5084 14.1039 10.4319 14.1752C10.3553 14.2465 10.2941 14.3317 10.2516 14.4259C10.2092 14.52 10.1863 14.6214 10.1844 14.7241C10.1844 14.933 10.2703 15.1333 10.4232 15.2811C10.5761 15.4288 10.7835 15.5118 10.9998 15.5118Z" fill="#9CA0AC"/>
  </svg>
  `;

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

  function view_item(text, section_slug, section, block) {
    var output = "";
    output += `<a data-tab="` + section_slug + `">`;
    output += `<div class="search_result_title">`;
    output += `<i class="las la-search"></i>`;
    output += `<span>` + text + `</span>`;
    output += `</div>`;
    output += `<div class="search_navigation">`;
    output += `<span>` + section + `</span>`;
    output += block ? `<i class="las la-angle-right"></i>` : ``;
    output += `<span>` + block + `</span>`;
    output += `</div>`;
    output += `</a>`;
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
            result = data.data.fields;
          // console.log(result);

          Object.values(result).forEach(function (item, index, arr) {
            item_text = item.label;
            section_slug = item.section_slug;
            section_label = item.section_label;
            block_label = item.block_label;
            searchKeyRegex = new RegExp(searchKey, "ig");
            // console.log(item_text.match(searchKeyRegex));
            matchedText = item_text.match(searchKeyRegex)?.[0];

            if (matchedText) {
              wrapped_item = item_text.replace(
                searchKeyRegex,
                "<span style='color: #222;font-weight:600'>" +
                  matchedText +
                  "</span>"
              );
              output += view_item(
                wrapped_item,
                section_slug,
                section_label,
                block_label
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
          navigationTrigger();
        },
      });
    } else {
      document.querySelector(".search-popup-opener").classList.remove("show");
    }
  });
});

/**
 * Search suggestion navigation
 */
function navigationTrigger() {
  const suggestionLinks = document.querySelectorAll(
    ".search-field .search_result a"
  );
  const navTabItems = document.querySelectorAll("li.tutor-option-nav-item a");
  const navPages = document.querySelectorAll(".tutor-option-nav-page");

  suggestionLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      const dataTab = e.target.closest("[data-tab]").dataset.tab;
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
      document.querySelector(".search_result").classList.remove("show");
      document.querySelector('.search-field input[type="search"]').value = "";
    });
  });
}
