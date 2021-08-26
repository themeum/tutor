jQuery(document).ready(function ($) {
  "use strict";

  // $(window).on("click", function (e) {
  //   $(".tutor-notification, .search_result").removeClass("show");
  // });
  $(document).keyup(function (e) {
    if (e.key === "Escape") {
      // escape key maps to keycode `27`
      $(".tutor-notification, .search_result").removeClass("show");
    }
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

  $("#search_settings").on("keyup", function (e) {
    e.preventDefault();
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
          output += `<div class="no_item">No item found</div>`;
        }
        $(".search_result").html(output).addClass("show");
        output = "";
        // console.log("working");
      },
      complete: function () {

        navigationTrigger();

      },
      
    });



    


  });

  // $(document)
  //   .off("click", ".search_result a")
  //   .on("click", ".search_result a", function () {
  //     var tab_page_id = $(this).attr("data-tab");
  //     $(".option-nav-item").removeClass("current");
  //     $(this).closest("li").addClass("current");
  //     // $(".tutor-option-nav-page").hide();
  //     $(tab_page_id).addClass("current-page").show();
  //     console.log(tab_page_id);

  //     window.history.pushState("obj", "", $(this).attr("href"));
  //   });

  // $('.tutor-option-nav-tabs li a').click(function(e){
  // e.preventDefault();
  //   var tab_page_id = $(this).attr("data-tab");
  //   $(".option-nav-item").removeClass("current");
  //   $(this).closest("li").addClass("current");
  //   $(".tutor-option-nav-page").hide();
  //   $(tab_page_id).addClass("current-page").show();
  //   window.history.pushState("obj", "", $(this).attr("href"));
  // });
});


/**
 * Search suggestion navigation
 */
function navigationTrigger(){
   const suggestionLinks = document.querySelectorAll(".search-field .search_result a");
   const navTabItems = document.querySelectorAll('li.tutor-option-nav-item a');
   const navPages = document.querySelectorAll('.tutor-option-nav-page');

   suggestionLinks.forEach((link) => {
     link.addEventListener('click', (e) => {
        const dataTab = e.target.closest('[data-tab]').dataset.tab;
        console.log(dataTab);
        if (dataTab) {
          // remove active from other buttons
          navTabItems.forEach((item) => {
            item.classList.remove('active');
            if (e.target.dataset.tab) {
              e.target.classList.add('active');
            } else {
              e.target.parentElement.classList.add('active');
            }
          });
          // hide other tab contents
          navPages.forEach((content) => {
            content.classList.remove('active');
          });
          // add active to the current content
          const currentContent = document.querySelector(`#${dataTab}`);
          currentContent.classList.add('active');

          // History push
          const url = new URL(window.location);
          url.searchParams.set('tab_page',dataTab);
          window.history.pushState({}, '', url);
        }

        // Reset + Hide Suggestion box
        document.querySelector('.search_result').classList.remove('show');
        document.querySelector('.search-field input[type="search"]').value = '';

     })
   })
}