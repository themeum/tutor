<<<<<<< HEAD
(()=>{var o={119:()=>{!function(){"use strict";g()}();var t=document.querySelectorAll("ul.tutor-option-nav"),a=document.querySelectorAll("li.tutor-option-nav-item a"),n=document.querySelectorAll(".tutor-option-nav-page");t.forEach(function(t){t.addEventListener("click",function(e){var t,o=e.target.parentElement.dataset.tab||e.target.dataset.tab;o&&(a.forEach(function(t){t.classList.remove("active"),(e.target.dataset.tab?e.target:e.target.parentElement).classList.add("active")}),n.forEach(function(t){t.classList.remove("active")}),document.querySelector("#".concat(o)).classList.add("active"),t=new URL(window.location),o=new URLSearchParams({page:"tutor_settings",tab_page:o}),o="".concat(t.origin+t.pathname,"?").concat(o.toString()),window.history.pushState({},"",o))})});var o=document.querySelector(".monitization-fees"),e=document.querySelector(".monitization-fees input[name=deduct-fees]");o&&e&&(window.addEventListener("load",function(){return i(e,o)}),e.addEventListener("change",function(){return i(e,o)}));var i=function(t,e){t.checked?(e.classList.remove("is-disable"),r(o,!1)):(e.classList.add("is-disable"),r(o,!0))},r=function(t,e){t.querySelectorAll(".tutor-option-field-row:nth-child(2) textarea, .tutor-option-field-row:nth-child(3) select, .tutor-option-field-row:nth-child(3) input").forEach(function(t){return t.disabled=e})},s=document.querySelectorAll(".image-previewer"),u=document.querySelectorAll(".image-previewer img"),c=document.querySelectorAll(".image-previewer input[type=file]"),t=document.querySelectorAll(".image-previewer .delete-btn");c&&t&&(document.addEventListener("DOMContentLoaded",function(){s.forEach(function(e){u.forEach(function(t){t.getAttribute("src")?t.closest(".image-previewer").classList.add("is-selected"):e.classList.remove("is-selected")})})}),c.forEach(function(i){i.addEventListener("change",function(t){var e=this.files[0],o=i.closest(".image-previewer"),a=o.querySelector("img"),n=o.querySelector(".preview-loading");e&&(n.classList.add("is-loading"),l(e,a),o.classList.add("is-selected"),setTimeout(function(){n.classList.remove("is-loading")},200))})}),t.forEach(function(t){t.addEventListener("click",function(t){var e=this.closest(".image-previewer");e.querySelector("img").setAttribute("src",""),e.classList.remove("is-selected")})}));var l=function(t,e){var o=new FileReader;o.onload=function(){e.setAttribute("src",this.result)},o.readAsDataURL(t)},d=document.querySelector("input[type=number]#revenue-instructor"),m=document.querySelector("input[type=number]#revenue-admin"),p=document.querySelectorAll(".revenue-percentage input[type=number]");d&&m&&p&&(d.addEventListener("input",function(t){t.target.value<=100&&(m.value=100-t.target.value),f(t.target.value)}),m.addEventListener("input",function(t){t.target.value<=100&&(d.value=100-t.target.value),f(t.target.value)}));var f=function(t){100<t?p.forEach(function(t){return t.classList.add("warning")}):p.forEach(function(t){return t.classList.remove("warning")})},_=document.querySelector(".input-field-code textarea"),t=document.querySelector(".code-copy-btn");t&&_&&t.addEventListener("click",function(t){var e=this;t.preventDefault(),this.focus(),_.select(),document.execCommand("copy");var o=this.innerHTML;setTimeout(function(){e.innerHTML=o},3e3),this.innerHTML='\n\t\t\t<span class="tutor-btn-icon las la-clipboard-list"></span>\n\t\t\t<span>Copied to Clipboard!</span>\n\t\t'});var t=document.querySelectorAll(".popup-opener .popup-btn"),v=document.querySelectorAll(".popup-opener .popup-menu");t&&v&&(t.forEach(function(t){t.addEventListener("click",function(t){var e=t.target.closest(".popup-opener").querySelector(".popup-menu");e.classList.toggle("visible"),v.forEach(function(t){t!==e&&t.classList.remove("visible")})})}),window.addEventListener("click",function(t){t.target.matches(".popup-opener .popup-btn")||v.forEach(function(t){t.classList.contains("visible")&&t.classList.remove("visible")})})),document.querySelectorAll(".drag-drop-zone input[type=file]").forEach(function(e){var o=e.closest(".drag-drop-zone");["dragover","dragleave","dragend"].forEach(function(t){"dragover"===t?o.addEventListener(t,function(t){t.preventDefault(),o.classList.add("dragover")}):o.addEventListener(t,function(t){o.classList.remove("dragover")})}),o.addEventListener("drop",function(t){t.preventDefault();t=t.dataTransfer.files;h(t,e,o),o.classList.remove("dragover")}),e.addEventListener("change",function(t){t=t.target.files;h(t,e,o)})});var h=function(t,e,o){t.length?(e.files=t,o.classList.add("file-attached"),o.querySelector(".file-info").innerHTML="File attached - ".concat(t[0].name)):(o.classList.remove("file-attached"),o.querySelector(".file-info").innerHTML="")};function g(){window.matchMedia("(max-width: 992px)").matches?document.querySelectorAll(".tooltip-right").forEach(function(t){t.classList.replace("tooltip-right","tooltip-left")}):document.querySelectorAll(".tooltip-left").forEach(function(t){t.classList.replace("tooltip-left","tooltip-right")})}window.addEventListener("resize",g)},972:()=>{var g='<svg class="tutor-icon-v2 angle-right" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.842 12.633C7.80402 12.6702 7.7592 12.6998 7.71 12.72C7.65839 12.7401 7.60341 12.7503 7.548 12.75C7.49655 12.7496 7.44563 12.7395 7.398 12.72C7.34843 12.7005 7.30347 12.6709 7.266 12.633L6.88201 12.252C6.84384 12.2138 6.81284 12.1691 6.79051 12.12C6.76739 12.0694 6.75367 12.015 6.75001 11.9595C6.74971 11.9045 6.75832 11.8498 6.77551 11.7975C6.79308 11.7477 6.82181 11.7025 6.85951 11.6655L9.53249 9.00001L6.86701 6.33453C6.82576 6.29904 6.79427 6.2536 6.77551 6.20253C6.75832 6.15026 6.74971 6.09555 6.75001 6.04053C6.75367 5.98502 6.76739 5.93064 6.79051 5.88003C6.81284 5.8309 6.84384 5.78619 6.88201 5.74803L7.263 5.36704C7.30047 5.32916 7.34543 5.29953 7.395 5.28004C7.44263 5.26056 7.49355 5.25038 7.545 5.25004C7.60142 5.24931 7.65745 5.2595 7.71 5.28004C7.7592 5.30025 7.80402 5.3298 7.842 5.36704L11.181 8.70752C11.2233 8.74442 11.2579 8.78926 11.283 8.83951C11.3077 8.88941 11.3206 8.94433 11.3206 9.00001C11.3206 9.05569 11.3077 9.11062 11.283 9.16051C11.2579 9.21076 11.2233 9.25561 11.181 9.29251L7.842 12.633Z" fill="#B4B7C0"/></svg>',w='<svg class="tutor-icon-v2 magnifying-glass" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.3056 5.375C7.58249 5.375 5.375 7.58249 5.375 10.3056C5.375 13.0286 7.58249 15.2361 10.3056 15.2361C13.0286 15.2361 15.2361 13.0286 15.2361 10.3056C15.2361 7.58249 13.0286 5.375 10.3056 5.375ZM4.125 10.3056C4.125 6.89214 6.89214 4.125 10.3056 4.125C13.719 4.125 16.4861 6.89214 16.4861 10.3056C16.4861 13.719 13.719 16.4861 10.3056 16.4861C6.89214 16.4861 4.125 13.719 4.125 10.3056Z" fill="#9CA0AC"/><path fill-rule="evenodd" clip-rule="evenodd" d="M13.7874 13.7872C14.0314 13.5431 14.4272 13.5431 14.6712 13.7872L17.6921 16.8081C17.9362 17.0521 17.9362 17.4479 17.6921 17.6919C17.448 17.936 17.0523 17.936 16.8082 17.6919L13.7874 14.6711C13.5433 14.427 13.5433 14.0313 13.7874 13.7872Z" fill="#9CA0AC"/></svg>',a='<svg class="tutor-icon-v2 warning" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.0388 14.2395C18.2457 14.5683 18.3477 14.9488 18.3321 15.3333C18.3235 15.6951 18.2227 16.0493 18.0388 16.3647C17.851 16.6762 17.5885 16.9395 17.2733 17.1326C16.9301 17.3257 16.5383 17.4237 16.1412 17.4159H5.87591C5.47974 17.4234 5.08907 17.3253 4.74673 17.1326C4.42502 16.9409 4.15549 16.6776 3.96071 16.3647C3.77376 16.0506 3.67282 15.6956 3.66741 15.3333C3.6596 14.9496 3.76106 14.5713 3.96071 14.2395L9.11094 5.64829C9.29701 5.31063 9.58016 5.03215 9.9263 4.84641C10.2558 4.67355 10.6248 4.58301 10.9998 4.58301C11.3747 4.58301 11.7437 4.67355 12.0732 4.84641C12.4259 5.02952 12.7154 5.30825 12.9062 5.64829L18.0388 14.2395ZM11.7447 10.4086C11.7447 10.2131 11.7653 10.0176 11.7799 9.81924C11.7946 9.62089 11.8063 9.41971 11.818 9.21853C11.8178 9.1484 11.8129 9.07836 11.8034 9.00885C11.7916 8.94265 11.7719 8.87799 11.7447 8.81617C11.6644 8.64655 11.5255 8.50928 11.3517 8.42798C11.1805 8.3467 10.9848 8.32759 10.8003 8.37414C10.6088 8.42217 10.4413 8.53471 10.3281 8.69149C10.213 8.84985 10.1525 9.03921 10.1551 9.2327C10.1551 9.3602 10.1756 9.48771 10.1844 9.61239C10.1932 9.73706 10.202 9.86457 10.2137 9.99208C10.2401 10.4709 10.2695 10.947 10.2988 11.4088C10.3281 11.8707 10.3545 12.3552 10.3838 12.8256C10.3857 12.9019 10.4032 12.9771 10.4352 13.0468C10.4672 13.1166 10.5131 13.1796 10.5703 13.2322C10.6275 13.2849 10.6948 13.3261 10.7685 13.3536C10.8422 13.381 10.9208 13.3942 10.9998 13.3923C11.0794 13.3946 11.1587 13.3813 11.2328 13.353C11.307 13.3248 11.3744 13.2822 11.4309 13.228C11.5454 13.1171 11.6115 12.968 11.6157 12.8114V12.5281C11.6157 12.4317 11.6157 12.3382 11.6157 12.2447C11.6362 11.9415 11.6538 11.6327 11.6743 11.3238C11.6949 11.015 11.7271 10.7118 11.7447 10.4086ZM10.9998 15.5118C11.1049 15.5119 11.2091 15.4919 11.3062 15.453C11.4034 15.4141 11.4916 15.3571 11.5658 15.2851C11.6441 15.2191 11.7061 15.137 11.7472 15.0448C11.7883 14.9526 11.8075 14.8527 11.8034 14.7524C11.8053 14.6497 11.7863 14.5476 11.7474 14.452C11.7085 14.3564 11.6505 14.2692 11.5767 14.1953C11.5029 14.1213 11.4147 14.0621 11.3172 14.0211C11.2197 13.9801 11.1149 13.958 11.0086 13.9562C10.9023 13.9543 10.7966 13.9727 10.6977 14.0103C10.5987 14.0479 10.5084 14.1039 10.4319 14.1752C10.3553 14.2465 10.2941 14.3317 10.2516 14.4259C10.2092 14.52 10.1863 14.6214 10.1844 14.7241C10.1844 14.933 10.2703 15.1333 10.4232 15.2811C10.5761 15.4288 10.7835 15.5118 10.9998 15.5118Z" fill="#9CA0AC"/></svg>';jQuery(document).ready(function(e){"use strict";for(var o=document.querySelectorAll(".image_upload_button"),t=0;t<o.length;++t)!function(t){var e=o[t].closest(".image-previewer"),a=e.querySelector(".input_file"),n=e.querySelector(".upload_preview"),e=e.querySelector(".delete-btn");o[t].onclick=function(t){t.preventDefault();var o=wp.media({title:"Upload Image",library:{type:"image"},multiple:!1,frame:"post",state:"insert"});o.open(),o.on("insert",function(t){var e=o.state();(t=t||e.get("selection"))&&(t=t.first(),e=e.display(t).toJSON(),e=(t=t.toJSON()).sizes[e.size].url,n.src=a.value=e)})},e.onclick=function(){a.value=""}}(t);e(window).on("click",function(t){e(".tutor-notification, .search_result").removeClass("show")}),e(".tutor-notification-close").click(function(t){e(".tutor-notification").removeClass("show")}),e("#save_tutor_option").click(function(t){t.preventDefault(),e("#tutor-option-form").submit()}),e("#tutor-option-form").submit(function(t){t.preventDefault();t=e(this).serializeObject();e.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){},success:function(t){e(".tutor-notification").addClass("show"),setTimeout(function(){e(".tutor-notification").removeClass("show")},4e3)},complete:function(){}})}),e("#search_settings").on("input",function(t){var h;t.preventDefault(),t.target.value?(h=this.value,e.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{action:"tutor_option_search",keyword:h},success:function(t){var s,u,c,l,d,m,p,f="",_=!0,v="",t=t.data.fields;Object.values(t).forEach(function(t,e,o){var a,n,i,r;v=t.label,u=t.section_slug,c=t.section_label,l=t.block_label,p=t.key,m=new RegExp(h,"ig"),(d=null===(r=v.match(m))||void 0===r?void 0:r[0])&&(s=v.replace(m,"<span style='color: #212327; font-weight:500'>".concat(d,"</span>")),f+=(a=s,n=u,i=c,t=p,r=(r=l)?"".concat(g," ").concat(r):"",'\n      <a data-tab="'.concat(n,'" data-key="field_').concat(t,'">\n        <div class="search_result_title">\n          ').concat(w,'\n          <span class="text-regular-caption">').concat(a,'</span>\n        </div>\n        <div class="search_navigation">\n          <div class="nav-track text-regular-small">\n            <span>').concat(i,"</span>\n            <span>").concat(r,"</span>\n          </div>\n        </div>\n      </a>")),_=!1)}),_&&(f+='<div class="no_item"> '.concat(a," No Results Found</div>")),e(".search_result").html(f).addClass("show"),f=""},complete:function(){var t,n,i;t=document.querySelectorAll(".search-field .search-popup-opener a"),n=document.querySelectorAll("li.tutor-option-nav-item a"),i=document.querySelectorAll(".tutor-option-nav-page"),t.forEach(function(t){t.addEventListener("click",function(t){var e,o,a=t.target.closest("[data-tab]").dataset.tab,t=t.target.closest("[data-key]").dataset.key;a&&(n.forEach(function(t){t.classList.remove("active")}),document.querySelector(".tutor-option-tabs [data-tab=".concat(a,"]")).classList.add("active"),i.forEach(function(t){t.classList.remove("active")}),document.querySelector(".tutor-option-tab-pages #".concat(a)).classList.add("active"),(e=new URL(window.location)).searchParams.set("tab_page",a),window.history.pushState({},"",e)),document.querySelector(".search-popup-opener").classList.remove("show"),document.querySelector('.search-field input[type="search"]').value="",e=t,t=document.querySelector("#".concat(e)),o=t&&t.querySelector(".tutor-option-field-label label"),e=t&&t.parentNode.querySelector(".tutor-option-field-row"),console.log("target -> ".concat(t," scrollTarget -> ").concat(e)),e?(o.classList.add("isHighlighted"),setTimeout(function(){o.classList.remove("isHighlighted")},6e3),e.scrollIntoView({behavior:"smooth",block:"center",inline:"nearest"})):console.warn("scrollTargetEl Not found!")})})}})):document.querySelector(".search-popup-opener").classList.remove("show")})});var t=document.querySelectorAll('.email-manage-page input[type="file"], .email-manage-page input[type="text"], .email-manage-page textarea');document.querySelectorAll(".email-manage-page [data-source]");t.forEach(function(t){t.addEventListener("input",function(t){var e=t.target,o=e.name,a=e.value;t.target.files&&(e=t.target.files[0],console.dir(t.target.files[0]),(t=new FileReader).onload=function(){document.querySelector('img[data-source="email-title-logo"]').setAttribute("src",this.result)},t.readAsDataURL(e));o=document.querySelector(".email-manage-page [data-source=".concat(o,"]"));o&&(o.href?o.href=a:o.innerHTML=a)})})},12:()=>{!function(){"use strict";document.addEventListener("click",function(t){var e="data-tutor-modal-target",o="data-tutor-modal-close";(t.target.hasAttribute(e)||t.target.closest("[".concat(e,"]")))&&(t.preventDefault(),e=(t.target.hasAttribute(e)?t.target:t.target.closest("[".concat(e,"]"))).getAttribute(e),(e=document.getElementById(e))&&e.classList.add("tutor-is-active")),(t.target.hasAttribute(o)||t.target.classList.contains("tutor-modal-overlay")||t.target.closest("[".concat(o,"]")))&&(t.preventDefault(),document.querySelectorAll(".tutor-modal.tutor-is-active").forEach(function(t){t.classList.remove("tutor-is-active")}))})}()}},a={};function i(t){var e=a[t];if(void 0!==e)return e.exports;e=a[t]={exports:{}};return o[t](e,e.exports,i),e.exports}(()=>{"use strict";i(12);function w(t){return(w="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function b(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var o=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=o){var a,n,i=[],r=!0,s=!1;try{for(o=o.call(t);!(r=(a=o.next()).done)&&(i.push(a.value),!e||i.length!==e);r=!0);}catch(t){s=!0,n=t}finally{try{r||null==o.return||o.return()}finally{if(s)throw n}}return i}}(t,e)||function(t,e){if(t){if("string"==typeof t)return a(t,e);var o=Object.prototype.toString.call(t).slice(8,-1);return"Map"===(o="Object"===o&&t.constructor?t.constructor.name:o)||"Set"===o?Array.from(t):"Arguments"===o||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(o)?a(t,e):void 0}}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function a(t,e){(null==e||e>t.length)&&(e=t.length);for(var o=0,a=new Array(e);o<e;o++)a[o]=t[o];return a}function y(t){var e=window._tutorobject||{},o=e.nonce_key||"",e=e[o]||"";return t?{key:o,value:e}:(t=e,(e=o)in(o={})?Object.defineProperty(o,e,{value:t,enumerable:!0,configurable:!0,writable:!0}):o[e]=t,o)}function q(){function t(t){t.classList.contains("disabled")||t.classList.add("disabled")}function e(t){t.classList.contains("disabled")&&t.classList.remove("disabled")}var o=document.querySelector(".tutor_select_value_holder").value,a=document.getElementById("tutor_quiz_question_answers"),n=document.getElementById("tutor_quiz_question_answer_form"),i=document.querySelector(".add_question_answers_option");("open_ended"===o||"short_answer"===o||("true_false"===o||"fill_in_the_blank"===o)&&(n.hasChildNodes()||a.hasChildNodes())?t:e)(i)}window.tutor_popup=function(r,o,a){var s,u=this;return this.popup_wrapper=function(t){var e=""===o?"":'<img class="tutor-pop-icon" src="'+window._tutorobject.tutor_url+"assets/images/"+o+'.svg"/>';return"<"+t+' class="tutor-component-popup-container">            <div class="tutor-component-popup-'+a+'">                <div class="tutor-component-content-container">'+e+'</div>                <div class="tutor-component-button-container"></div>            </div>        </'+t+">"},this.popup=function(o){var t=o.title?"<h3>"+o.title+"</h3>":"",e=o.description?"<p>"+o.description+"</p>":"",a=Object.keys(o.buttons||{}).map(function(t){var e=o.buttons[t],t=e.id?"tutor-popup-"+e.id:"";return r('<button id="'+t+'" class="tutor-button tutor-button-'+e.class+'">'+e.title+"</button>").click(e.callback)}),n=(s=r(u.popup_wrapper(o.wrapper_tag||"div"))).find(".tutor-component-content-container");n.append(t),o.after_title&&n.append(o.after_title),n.append(e),o.after_description&&n.append(o.after_description),s.click(function(){r(this).remove()}).children().click(function(t){t.stopPropagation()});for(var i=0;i<a.length;i++)s.find(".tutor-component-button-container").append(a[i]);return r("body").append(s),s},{popup:this.popup}},window.tutorDotLoader=function(t){return'    \n    <div class="tutor-dot-loader '.concat(t||"",'">\n        <span class="dot dot-1"></span>\n        <span class="dot dot-2"></span>\n        <span class="dot dot-3"></span>\n        <span class="dot dot-4"></span>\n    </div>')},window.tutor_date_picker=function(){var t;jQuery.datepicker&&(t=_tutorobject.wp_date_format||"yy-mm-dd",$(".tutor_date_picker").datepicker({dateFormat:t}))},jQuery(document).ready(function(l){var t=wp.i18n,n=t.__;t._x,t._n,t._nx;function o(){var t;jQuery.datepicker&&(t=_tutorobject.wp_date_format||"yy-mm-dd",l(".tutor_date_picker").datepicker({dateFormat:t}))}function s(){l(".tutor-field-slider").each(function(){var t=l(this),o=t.closest(".tutor-field-type-slider").find('input[type="hidden"]'),a=t.closest(".tutor-field-type-slider").find(".tutor-field-type-slider-value"),e=parseFloat(t.closest(".tutor-field-type-slider").attr("data-min")),n=parseFloat(t.closest(".tutor-field-type-slider").attr("data-max"));t.slider({range:"max",min:e,max:n,value:o.val(),slide:function(t,e){a.text(e.value),o.val(e.value)}})})}function e(t){var e=t.element;return l('<span><i class="tutor-icon-'+l(e).data("icon")+'"></i> '+t.text+"</span>")}function i(){var i={};l(".tutor-topics-wrap").each(function(t,e){var o=l(this),a=parseInt(o.attr("id").match(/\d+/)[0],10),n={};o.find(".course-content-item").each(function(t,e){var o=l(this),o=parseInt(o.attr("id").match(/\d+/)[0],10);n[t]=o}),i[t]={topic_id:a,lesson_ids:n}}),l("#tutor_topics_lessons_sorting").val(JSON.stringify(i))}function r(){return{init:function(){l(document).on("click",".tutor-select .tutor-select-option",function(t){t.preventDefault();var e=l(this);"true"!==e.attr("data-is-pro")?(t=e.html().trim(),e.closest(".tutor-select").find(".select-header .lead-option").html(t),e.closest(".tutor-select").find(".select-header input.tutor_select_value_holder").val(e.attr("data-value")).trigger("change"),e.closest(".tutor-select-options").hide(),q()):alert("Tutor Pro version required")}),l(document).on("click",".tutor-select .select-header",function(t){t.preventDefault(),l(this).closest(".tutor-select").find(".tutor-select-options").slideToggle()}),this.setValue(),this.hideOnOutSideClick()},setValue:function(){l(".tutor-select").each(function(){var t=l(this).find(".tutor-select-option");t.length&&t.each(function(){var t,e=l(this);"selected"===e.attr("data-selected")&&(t=e.html().trim(),e.closest(".tutor-select").find(".select-header .lead-option").html(t),e.closest(".tutor-select").find(".select-header input.tutor_select_value_holder").val(e.attr("data-value")))})})},hideOnOutSideClick:function(){l(document).mouseup(function(t){var e=l(".tutor-select-options");l(t.target).closest(".select-header").length||e.is(t.target)||0!==e.has(t.target).length||e.hide()})},reInit:function(){this.setValue()}}}o(),s(),jQuery().select2&&l(".videosource_select2").select2({width:"100%",templateSelection:e,templateResult:e,allowHtml:!0}),l(document).on("change",".tutor_lesson_video_source",function(t){var e=l(this),o=l(this).val();o?l(".video-metabox-source-input-wrap").show():l(".video-metabox-source-input-wrap").hide(),e.closest(".tutor-option-field").find(".video-metabox-source-item").hide(),e.closest(".tutor-option-field").find(".video_source_wrap_"+o).show()}),l(document).on("click",".tutor-course-thumbnail-upload-btn",function(t){t.preventDefault();var e,o=l(this);e||(e=wp.media({title:n("Select or Upload Media Of Your Chosen Persuasion","tutor"),button:{text:n("Use this media","tutor")},multiple:!1})).on("select",function(){var t=e.state().get("selection").first().toJSON();o.closest(".tutor-thumbnail-wrap").find(".thumbnail-img").attr("src",t.url),o.closest(".tutor-thumbnail-wrap").find("input").val(t.id),l(".tutor-course-thumbnail-delete-btn").show()}),e.open()}),l(document).on("click",".tutor-course-thumbnail-delete-btn",function(t){t.preventDefault();var e=l(this),t=e.closest(".tutor-thumbnail-wrap").find(".thumbnail-img").attr("data-placeholder-src");e.closest(".tutor-thumbnail-wrap").find(".thumbnail-img").attr("src",t),e.closest(".tutor-thumbnail-wrap").find("input").val(""),l(".tutor-course-thumbnail-delete-btn").hide()}),l(".tutor-zoom-meeting-modal-wrap").on("submit",".tutor-meeting-modal-form",function(t){t.preventDefault();var e=l(this),o=e.serializeObject(),t=Intl.DateTimeFormat().resolvedOptions().timeZone;o.timezone=t;var a=e.find('button[type="submit"]');l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:o,beforeSend:function(){a.addClass("tutor-updating-message")},success:function(t){t.success?tutor_toast(n("Success","tutor"),a.data("toast_success_message"),"success"):tutor_toast(n("Update Error","tutor"),n("Meeting Update Failed","tutor"),"error"),t.course_contents?(l(t.selector).html(t.course_contents),"#tutor-course-content-wrap"==t.selector&&jQuery().sortable&&(l(".course-contents").sortable({handle:".course-move-handle",start:function(t,e){e.placeholder.css("visibility","visible")},stop:function(t,e){i()}}),l(".tutor-lessons:not(.drop-lessons)").sortable({connectWith:".tutor-lessons",items:"div.course-content-item",start:function(t,e){e.placeholder.css("visibility","visible")},stop:function(t,e){i()}})),l(".tutor-zoom-meeting-modal-wrap").removeClass("show")):location.reload()},complete:function(){a.removeClass("tutor-updating-message")}})}),l(document).on("change keyup",".course-edit-topic-title-input",function(t){t.preventDefault(),l(this).closest(".tutor-topics-top").find(".topic-inner-title").html(l(this).val())}),l(document).on("click",".tutor-topics-edit-button",function(t){t.preventDefault();var e=l(this),o=e.closest(".tutor-topics-wrap").find('[name="topic_id"]').val(),a=e.closest(".tutor-topics-wrap").find('[name="topic_title"]').val(),t=e.closest(".tutor-topics-wrap").find('[name="topic_summery"]').val();l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{topic_title:a,topic_summery:t,topic_id:o,action:"tutor_update_topic"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&(e.closest(".tutor-topics-wrap").find("span.topic-inner-title").text(a),e.closest(".tutor-modal").removeClass("tutor-is-active"))},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".lesson_thumbnail_upload_btn",function(t){t.preventDefault();var e,o=l(this);e||(e=wp.media({title:n("Select or Upload Media Of Your Chosen Persuasion","tutor"),button:{text:n("Use this media","tutor")},multiple:!1})).on("select",function(){var t=e.state().get("selection").first().toJSON();o.closest(".tutor-thumbnail-wrap").find(".thumbnail-img").html('<img src="'+t.url+'" alt="" /><a href="javascript:;" class="tutor-lesson-thumbnail-delete-btn"><i class="tutor-icon-line-cross"></i></a>'),o.closest(".tutor-thumbnail-wrap").find("input").val(t.id),l(".tutor-lesson-thumbnail-delete-btn").show()}),e.open()}),l(document).on("click",".tutor-lesson-thumbnail-delete-btn",function(t){t.preventDefault();t=l(this);t.closest(".tutor-thumbnail-wrap").find("._lesson_thumbnail_id").val(""),t.closest(".tutor-thumbnail-wrap").find(".thumbnail-img").html(""),t.hide()}),l(document).on("click",".tutor-delete-lesson-btn",function(t){var e;t.preventDefault(),confirm(n("Are you sure?","tutor"))&&(t=(e=l(this)).attr("data-lesson-id"),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{lesson_id:t,action:"tutor_delete_lesson_by_id"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&e.closest(".course-content-item").remove()},complete:function(){e.removeClass("tutor-updating-message")}}))}),l(document).on("click",".quiz-modal-btn-first-step",function(t){t.preventDefault();var e,o,a,n=l(this),i=l('[name="quiz_title"]'),r=i.val(),t=l('[name="quiz_description"]').val();r?(i.closest(".tutor-quiz-builder-group").find(".quiz_form_msg").html(""),e=l("#post_ID").val(),o=n.closest(".tutor-modal-wrap").attr("quiz-for-post-id"),l("#tutor_quiz_builder_quiz_id").length?(a=l("#tutor_quiz_builder_quiz_id").val(),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{quiz_title:r,quiz_description:t,quiz_id:a,topic_id:o,action:"tutor_quiz_builder_quiz_update"},beforeSend:function(){n.addClass("tutor-updating-message")},success:function(t){l("#tutor-quiz-"+a).html(t.data.output_quiz_row),l('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger("click"),s()},complete:function(){n.removeClass("tutor-updating-message")}})):l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{quiz_title:r,quiz_description:t,course_id:e,topic_id:o,action:"tutor_create_quiz_and_load_modal"},beforeSend:function(){n.addClass("tutor-updating-message")},success:function(t){l(".tutor-quiz-builder-modal-wrap .modal-container").html(t.data.output),l("#tutor-topics-"+o+" .tutor-lessons").append(t.data.output_quiz_row),l('#tutor-quiz-modal-tab-items-wrap a[href="#quiz-builder-tab-questions"]').trigger("click"),s(),l(document).trigger("quiz_modal_loaded",{topic_id:o,course_id:e})},complete:function(){n.removeClass("tutor-updating-message")}})):i.closest(".tutor-quiz-builder-group").find(".quiz_form_msg").html("Please enter quiz title")}),l(document).on("click",".open-tutor-quiz-modal",function(t){t.preventDefault();var e=l(this),o=e.attr("data-quiz-id"),a=e.attr("data-topic-id");null==a&&(a=e.closest(".tutor-modal-wrap").attr("quiz-for-post-id"));var n=l("#post_ID").val();l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{quiz_id:o,topic_id:a,course_id:n,action:"tutor_load_edit_quiz_modal"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l(".tutor-quiz-builder-modal-wrap .modal-container").html(t.data.output),l(".tutor-quiz-builder-modal-wrap").attr("data-quiz-id",o).attr("quiz-for-post-id",a).addClass("show"),e.attr("data-back-to-tab")&&(t=e.attr("data-back-to-tab"),l('#tutor-quiz-modal-tab-items-wrap a[href="'+t+'"]').trigger("click")),l(document).trigger("quiz_modal_loaded",{quiz_id:o,topic_id:a,course_id:n}),s(),jQuery().sortable&&l(".quiz-builder-questions-wrap").sortable({handle:".question-sorting",start:function(t,e){e.placeholder.css("visibility","visible")},stop:function(t,e){var a;a={},l(".quiz-builder-question-wrap").each(function(t,e){var o=l(this),o=parseInt(o.attr("data-question-id"),10);a[t]=o}),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{sorted_question_ids:a,action:"tutor_quiz_question_sorting"}})}})},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".quiz-modal-settings-save-btn",function(t){t.preventDefault();var e=l(this),o=l(".tutor-quiz-builder-modal-wrap").attr("data-quiz-id"),a=l("#current_topic_id_for_quiz").val(),t=l("#quiz-builder-tab-settings :input, #quiz-builder-tab-advanced-options :input").serializeObject();t.topic_id=a,t.quiz_id=o,t.action="tutor_quiz_modal_update_settings",l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&l("#tutor-course-content-wrap").html(t.data.course_contents),t.success?tutor_toast(n("Success","tutor"),e.data("toast_success_message"),"success"):tutor_toast(n("Update Error","tutor"),n("Quiz Update Failed","tutor"),"error")},complete:function(){e.removeClass("tutor-updating-message"),"modal_close"===e.attr("data-action")&&l(".tutor-modal-wrap").removeClass("show")}})}),l(document).on("click",".quiz-modal-question-save-btn",function(t){t.preventDefault();var e=l(this),o=l(".quiz_question_form :input").serializeObject();o.action="tutor_quiz_modal_update_question";t=e.closest(".tutor-modal-wrap").attr("quiz-for-post-id");o.topic_id=t,l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:o,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success?e.closest(".tutor-quiz-builder-modal-contents").find(".open-tutor-quiz-modal").trigger("click"):void 0!==t.data&&l("#quiz_validation_msg_wrap").html(t.data.validation_msg)},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".modal-close-btn",function(t){t.preventDefault(),l(".tutor-modal-wrap").removeClass("show")}),l(document).on("keyup",function(t){27===t.keyCode&&l(".tutor-modal-wrap").removeClass("show")}),l(document).on("click",".tutor-add-quiz-btn",function(t){t.preventDefault();var e=l(this),o=l(this).closest(".tutor_add_quiz_wrap").attr("data-add-quiz-under"),t=l(this).data("topic-id");l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{quiz_for_post_id:o,current_topic_id:t,action:"tutor_load_quiz_builder_modal"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l(".tutor-quiz-builder-modal-wrap .modal-container").html(t.data.output),l(".tutor-quiz-builder-modal-wrap").attr("quiz-for-post-id",o).addClass("show")},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".tutor-quiz-modal-tab-item",function(t){t.preventDefault();var e=l(this),o=l('[name="quiz_title"]');o.val()?(o.closest(".tutor-quiz-builder-form-row").find(".quiz_form_msg").html(""),t=e.attr("href"),l(".quiz-builder-tab-container").hide(),l(t).show(),l("a.tutor-quiz-modal-tab-item").removeClass("active"),e.addClass("active")):o.closest(".tutor-quiz-builder-form-row").find(".quiz_form_msg").html('<p class="quiz-form-warning">Please save the quiz first</p>')}),l(document).on("click",".quiz-modal-btn-next, .quiz-modal-btn-back",function(t){t.preventDefault();t=l(this).attr("href");l('#tutor-quiz-modal-tab-items-wrap a[href="'+t+'"]').trigger("click")}),l(document).on("click",".quiz-modal-tab-navigation-btn.quiz-modal-btn-cancel",function(t){t.preventDefault(),l(".tutor-modal-wrap").removeClass("show")}),l(document).on("click",".tutor-quiz-open-question-form",function(t){t.preventDefault();var e=l(this),o=l("#tutor_quiz_builder_quiz_id").val(),a=l("#post_ID").val(),t=e.attr("data-question-id"),a={quiz_id:o,course_id:a,action:"tutor_quiz_builder_get_question_form"};t&&(a.question_id=t),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:a,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l(".tutor-quiz-builder-modal-contents").html(t.data.output),r().reInit(),jQuery().sortable&&l("#tutor_quiz_question_answers").sortable({handle:".tutor-quiz-answer-sort-icon",start:function(t,e){e.placeholder.css("visibility","visible")},stop:function(t,e){var a;a={},l(".tutor-quiz-answer-wrap").each(function(t,e){var o=l(this),o=parseInt(o.attr("data-answer-id"),10);a[t]=o}),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{sorted_answer_ids:a,action:"tutor_quiz_answer_sorting"}})}}),q()},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".tutor-quiz-question-trash",function(t){t.preventDefault();var e=l(this),t=e.attr("data-question-id");l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{question_id:t,action:"tutor_quiz_builder_question_delete"},beforeSend:function(){e.closest(".quiz-builder-question-wrap").remove()}})}),l(document).on("click",".add_question_answers_option:not(.disabled)",function(t){t.preventDefault();var e=l(this),o=e.attr("data-question-id"),t=l(".quiz_question_form :input").serializeObject();t.question_id=o,t.action="tutor_quiz_add_question_answers",l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l("#tutor_quiz_question_answer_form").html(t.data.output),q()},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".tutor-quiz-answer-edit a",function(t){t.preventDefault();var e=l(this),t=e.closest(".tutor-quiz-answer-wrap").attr("data-answer-id");l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{answer_id:t,action:"tutor_quiz_edit_question_answer"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l("#tutor_quiz_question_answer_form").html(t.data.output)},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click","#quiz-answer-save-btn",function(t){t.preventDefault();var e=l(this),t=l(".quiz_question_form :input").serializeObject();t.action="tutor_save_quiz_answer_options",l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){l("#quiz_validation_msg_wrap").html(""),e.addClass("tutor-updating-message")},success:function(t){l("#tutor_quiz_question_answers").trigger("refresh")},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click","#quiz-answer-edit-btn",function(t){t.preventDefault();var e=l(this),t=l(".quiz_question_form :input").serializeObject();t.action="tutor_update_quiz_answer_options",l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){l("#tutor_quiz_question_answers").trigger("refresh")},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("change",".tutor-quiz-answers-mark-correct-wrap input",function(t){t.preventDefault();var e=l(this),o=e.val(),t=1;e.prop("checked")||(t=0),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{answer_id:o,inputValue:t,action:"tutor_mark_answer_as_correct"}})}),l(document).on("refresh","#tutor_quiz_question_answers",function(t){t.preventDefault();var e=l(this),o=e.attr("data-question-id"),t=l(".tutor_select_value_holder").val();l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{question_id:o,question_type:t,action:"tutor_quiz_builder_get_answers_by_question"},beforeSend:function(){e.addClass("tutor-updating-message"),l("#tutor_quiz_question_answer_form").html("")},success:function(t){t.success&&e.html(t.data.output)},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".tutor-quiz-answer-trash-wrap a.answer-trash-btn",function(t){t.preventDefault();var e=l(this),t=e.attr("data-answer-id");l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{answer_id:t,action:"tutor_quiz_builder_delete_answer"},beforeSend:function(){e.closest(".tutor-quiz-answer-wrap").remove()}})}),l(document).on("click",".tutor-delete-quiz-btn",function(t){var e;t.preventDefault(),confirm(n("Are you sure?","tutor"))&&(t=(e=l(this)).attr("data-quiz-id"),l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{quiz_id:t,action:"tutor_delete_quiz_by_id"},beforeSend:function(){e.closest(".course-content-item").remove()}}))}),r().init(),l(document).on("change","input.tutor_select_value_holder",function(t){l(this);l(".add_question_answers_option").trigger("click"),l("#tutor_quiz_question_answers").trigger("refresh")}),l(document).on("click",".tutor-media-upload-btn",function(t){t.preventDefault();var e,o=l(this);e||(e=wp.media({title:n("Select or Upload Media Of Your Chosen Persuasion","tutor"),button:{text:n("Use this media","tutor")},multiple:!1})).on("select",function(){var t=e.state().get("selection").first().toJSON();o.html('<img src="'+t.url+'" alt="" />'),o.closest(".tutor-media-upload-wrap").find("input").val(t.id)}),e.open()}),l(document).on("click",".tutor-media-upload-trash",function(t){t.preventDefault();t=l(this);t.closest(".tutor-media-upload-wrap").find(".tutor-media-upload-btn").html('<i class="tutor-icon-image1"></i>'),t.closest(".tutor-media-upload-wrap").find("input").val("")});var a,u=(a=0,function(t,e){clearTimeout(a),a=setTimeout(t,e)});l(document).on("click",".tutor-add-instructor-btn",function(t){t.preventDefault();var e=l(this),t=l("#post_ID").val();l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{course_id:t,action:"tutor_load_instructors_modal"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&(l(".tutor-instructors-modal-wrap .modal-container").html(t.data.output),l(".tutor-instructors-modal-wrap").addClass("show"))},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("change keyup",".tutor-instructors-modal-wrap .tutor-modal-search-input",function(t){t.preventDefault();var o=l(this),a=l(".tutor-modal-wrap");u(function(){var t=o.val(),e=l("#post_ID").val();l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{course_id:e,search_terms:t,action:"tutor_load_instructors_modal"},beforeSend:function(){a.addClass("loading")},success:function(t){t.success&&(l(".tutor-instructors-modal-wrap .modal-container").html(t.data.output),l(".tutor-instructors-modal-wrap").addClass("show"))},complete:function(){a.removeClass("loading")}})},1e3)}),l(document).on("click",".add_instructor_to_course_btn",function(t){t.preventDefault();var e=l(this),o=l(".tutor-modal-wrap"),t=l("#post_ID").val(),o=o.find("input").serializeObject();o.course_id=t,o.action="tutor_add_instructors_to_course",l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:o,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&(l(".tutor-course-available-instructors").html(t.data.output),l(".tutor-modal-wrap").removeClass("show"))},complete:function(){e.removeClass("tutor-updating-message")}})}),l(document).on("click",".tutor-instructor-delete-btn",function(t){t.preventDefault();var e=l(this),o=l("#post_ID").val(),t=e.closest(".added-instructor-item").attr("data-instructor-id");l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{course_id:o,instructor_id:t,action:"detach_instructor_from_course"},success:function(t){t.success&&e.closest(".added-instructor-item").remove()}})}),l(document).on("click",".settings-tabs-navs li",function(t){t.preventDefault();var e=l(this),o=e.find("a").attr("data-target"),t=e.find("a").attr("href");e.addClass("active").siblings("li.active").removeClass("active"),l(".settings-tab-wrap").removeClass("active").hide(),l(o).addClass("active").show(),window.history.pushState({},"",t)}),l(document).on("lesson_modal_loaded quiz_modal_loaded assignment_modal_loaded",function(t,e){jQuery().select2&&l(".select2_multiselect").select2({dropdownCssClass:"increasezindex"}),o()}),l(document).on("keyup change",".tutor-number-validation",function(t){var e=l(this),o=parseInt(e.val()),a=parseInt(e.attr("data-min")),n=parseInt(e.attr("data-max"));o<a?e.val(a):n<o&&e.val(n)}),l(document).on("click",".tutor-instructor-feedback",function(t){t.preventDefault();var e=l(this);l.ajax({url:window.ajaxurl||_tutorobject.ajaxurl,type:"POST",data:{attempts_id:e.data("attemptid"),feedback:l(".tutor-instructor-feedback-content").val(),action:"tutor_instructor_feedback"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){t.success&&(e.closest(".course-content-item").remove(),tutor_toast(n("Success","tutor"),e.data("toast_success_message"),"success"))},complete:function(){e.removeClass("tutor-updating-message")}})});var c=l(".tutor-announcement-add-new"),d=l(".tutor-announcement-edit"),m=l(".tutor-announcement-delete"),p=l(".tutor-announcement-details"),t=l(".tutor-announcement-close-btn"),f=l(".tutor-accouncement-create-modal"),_=l(".tutor-accouncement-update-modal"),v=l(".tutor-accouncement-details-modal");function h(t,e){var o=new URL(window.location.href),a=o.searchParams;return a.set(t,e),o.search=a.toString(),a.set("paged",1),o.search=a.toString(),o.toString()}l(c).click(function(){f.addClass("show"),l("#tutor-annoucement-backend-create-modal").addClass("show")}),l(p).click(function(){var t=l(this).attr("announcement-date"),e=l(this).attr("announcement-id"),o=l(this).attr("course-id"),a=l(this).attr("course-name"),n=l(this).attr("announcement-title"),i=l(this).attr("announcement-summary");l(".tutor-announcement-detail-content").html("<h3>".concat(n,"</h3><p>").concat(i,"</p>")),l(".tutor-announcement-detail-course-info p").html("".concat(a)),l(".tutor-announcement-detail-date-info p").html("".concat(t)),l("#tutor-announcement-edit-from-detail").attr("announcement-id",e),l("#tutor-announcement-edit-from-detail").attr("course-id",o),l("#tutor-announcement-edit-from-detail").attr("announcement-title",n),l("#tutor-announcement-edit-from-detail").attr("announcement-summary",i),l("#tutor-announcement-delete-from-detail").attr("announcement-id",e),v.addClass("show")}),l(d).click(function(){v&&v.removeClass("show");var t=l(this).attr("announcement-id"),e=l(this).attr("course-id"),o=l(this).attr("announcement-title"),a=l(this).attr("announcement-summary");l("#tutor-announcement-course-id").val(e),l("#announcement_id").val(t),l("#tutor-announcement-title").val(o),l("#tutor-announcement-summary").val(a),_.addClass("show")}),l(t).click(function(){f.removeClass("show"),_.removeClass("show"),v.removeClass("show"),l("#tutor-annoucement-backend-create-modal").removeClass("show")}),l(".tutor-announcements-form").on("submit",function(t){t.preventDefault();var e=l(this).find('button[type="submit"]'),t=l(".tutor-announcements-form").serialize()+"&action=tutor_announcement_create&action_type=create";l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){if(l(".tutor-alert").remove(),"success"==t.status&&location.reload(),"validation_error"==t.status){l(".tutor-announcements-create-alert").append('<div class="tutor-alert alert-warning"></div>');for(var e=0,o=Object.entries(t.message);e<o.length;e++){var a=b(o[e],2),a=(a[0],a[1]);l(".tutor-announcements-create-alert .tutor-alert").append("<li>".concat(a,"</li>"))}}"fail"==t.status&&l(".tutor-announcements-create-alert").html("<li>".concat(t.message,"</li>"))},error:function(t){console.log(t)}})}),l(".tutor-announcements-update-form").on("submit",function(t){t.preventDefault();var e=l(this).find('button[type="submit"]'),t=l(".tutor-announcements-update-form").serialize()+"&action=tutor_announcement_create&action_type=update";l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){if(l(".tutor-alert").remove(),"success"==t.status&&location.reload(),"validation_error"==t.status){l(".tutor-announcements-update-alert").append('<div class="tutor-alert alert-warning"></div>');for(var e=0,o=Object.entries(t.message);e<o.length;e++){var a=b(o[e],2),a=(a[0],a[1]);l(".tutor-announcements-update-alert > .tutor-alert").append("<li>".concat(a,"</li>"))}}"fail"==t.status&&l(".tutor-announcements-create-alert").html("<li>".concat(t.message,"</li>"))},error:function(){}})}),l(m).click(function(){var t=l(this).attr("announcement-id"),e=l("#tutor-announcement-tr-"+t);confirm("Do you want to delete?")&&l.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{action:"tutor_announcement_delete",announcement_id:t},beforeSend:function(){},success:function(t){e.remove(),v.length&&v.removeClass("show"),"fail"==t.status&&console.log(t.message)},error:function(){}})}),l(".tutor-announcement-course-sorting").on("change",function(t){window.location=h("course-id",l(this).val())}),l(".tutor-announcement-order-sorting").on("change",function(t){window.location=h("order",l(this).val())}),l(".tutor-announcement-date-sorting").on("change",function(t){window.location=h("date",l(this).val())}),l(".tutor-announcement-search-sorting").on("click",function(t){window.location=h("search",l(".tutor-announcement-search-field").val())}),l(document).click(function(){l(".tutor-dropdown").removeClass("show")}),l(".tutor-dropdown").click(function(t){t.stopPropagation(),l(".tutor-dropdown").hasClass("show")&&l(".tutor-dropdown").removeClass("show"),l(this).addClass("show")});var g,m=".video_source_wrap_external_url input, .video_source_wrap_vimeo input, .video_source_wrap_youtube input, .video_source_wrap_html5, .video_source_upload_wrap_html5";l("body").on("paste",m,function(t){t.stopImmediatePropagation();var r=l(this).closest(".lesson-modal-form-wrap").find(".tutor-option-field-video-duration"),e=r.find("label"),o=l(this).hasClass("video_source_wrap_html5")||l(this).hasClass("video_source_upload_wrap_html5"),a=l(this).data("autofill_url");l(this).data("autofill_url",null);function n(t){t?0==e.find("img").length&&e.append(' <img src="'+window._tutorobject.loading_icon_url+'" style="display:inline-block"/>'):e.find("img").remove()}function i(t){for(var e=Math.floor(t/3600),o=Math.floor((t-3600*e)/60),t=Math.round(t-3600*e-60*o),a=[e=e<10?"0"+e:e,o=o<10?"0"+o:o,t=t<10?"0"+t:t],n=r.find("input"),i=0;i<3;i++)n.eq(i).val(a[i])}var s,u,c,t=o?l(this).find("span").data("video_url"):a||t.originalEvent.clipboardData.getData("text");o||l(this).parent().hasClass("video_source_wrap_external_url")?((s=document.createElement("video")).addEventListener("loadedmetadata",function(){i(s.duration),n(!1)}),n(!0),s.src=t):l(this).parent().hasClass("video_source_wrap_vimeo")?(u=(c=t.match(/^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/))?c[5]:null)&&(n(!0),l.getJSON("http://vimeo.com/api/v2/video/"+u+"/json",function(t){Array.isArray(t)&&t[0]&&void 0!==t[0].duration&&i(t[0].duration),n(!1)})):l(this).parent().hasClass("video_source_wrap_youtube")&&(u=!(!(c=t.match(/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/))||11!=c[7].length)&&c[7],c=l(this).data("youtube_api_key"),u&&c&&(c="https://www.googleapis.com/youtube/v3/videos?id="+u+"&key="+c+"&part=contentDetails",n(!0),l.getJSON(c,function(t){"object"==w(t)&&t.items&&t.items[0]&&t.items[0].contentDetails&&t.items[0].contentDetails.duration&&i(function(t){t=(t=t.match(/PT(\d+H)?(\d+M)?(\d+S)?/)).slice(1).map(function(t){if(null!=t)return t.replace(/\D/,"")});return 3600*(parseInt(t[0])||0)+60*(parseInt(t[1])||0)+(parseInt(t[2])||0)}(t.items[0].contentDetails.duration)),n(!1)})))}).on("input",m,function(){g&&clearTimeout(g);var e=l(this);g=setTimeout(function(){var t=(t=e.val())?t.trim():"";console.log("Trigger",t),t&&e.data("autofill_url",t).trigger("paste")},700)}),l(".tutor-form-submit-through-ajax").submit(function(t){t.preventDefault();var e=l(this),o=l(this).attr("action")||window.location.href,a=l(this).attr("method")||"GET",t=l(this).serializeObject();e.find("button").addClass("tutor-updating-message"),l.ajax({url:o,type:a,data:t,success:function(){tutor_toast(n("Success","tutor"),e.data("toast_success_message"),"success")},complete:function(){e.find("button").removeClass("tutor-updating-message")}})}),l.ajaxSetup({data:y()})}),jQuery.fn.serializeObject=function(){var t={},e=this.serializeArray();return jQuery.each(e,function(){t[this.name]?(t[this.name].push||(t[this.name]=[t[this.name]]),t[this.name].push(this.value||"")):t[this.name]=this.value||""}),t},window.tutor_toast=function(t,e,o){var a=((window._tutorobject||{}).tutor_url||"")+"assets/images/";jQuery(".tutor-toast-parent").length||jQuery("body").append('<div class="tutor-toast-parent"></div>');var n=jQuery('        <div>            <div>                <img src="'+{success:a+"icon-check.svg",error:a+"icon-cross.svg"}[o]+'"/>            </div>            <div>                <div>                    <b>'+t+"</b>                    <span>"+e+'</span>                </div>            </div>            <div>                <i class="tutor-toast-close tutor-icon-line-cross"></i>            </div>        </div>');n.find(".tutor-toast-close").click(function(){n.remove()}),jQuery(".tutor-toast-parent").append(n),setTimeout(function(){n&&n.fadeOut("fast",function(){jQuery(this).remove()})},5e3)};i(119),i(972);function n(t){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}jQuery(document).ready(function(i){var t=wp.i18n,r=t.__,e=(t._x,t._n,t._nx,r("Search students","tutor"));jQuery().wpColorPicker&&i(".tutor_colorpicker").wpColorPicker(),jQuery().select2&&i(".tutor_select2").select2(),i(".tutor-option-nav-tabs li a").click(function(t){t.preventDefault();t=i(this).attr("data-tab");i(".option-nav-item").removeClass("current"),i(this).closest("li").addClass("current"),i(".tutor-option-nav-page").hide(),i(t).addClass("current-page").show(),window.history.pushState("obj","",i(this).attr("href"))}),i("#save_tutor_option").click(function(t){t.preventDefault(),i(this).closest("form").submit()}),i("#tutor-option-form").submit(function(t){t.preventDefault();var e=i(this),t=e.serializeObject();i.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,beforeSend:function(){e.find(".button").addClass("tutor-updating-message")},success:function(t){t.success?tutor_toast(r("Saved","tutor"),e.data("toast_success_message"),"success"):tutor_toast(r("Request Error","tutor"),r("Could not save","tutor"),"error")},complete:function(){e.find(".button").removeClass("tutor-updating-message")}})}),i(document).on("click",".withdraw-method-nav li a",function(t){t.preventDefault();t=i(this).attr("data-target-id");i(".withdraw-method-form-wrap").hide(),i("#"+t).show()}),i(document).on("change",".tutor_lesson_video_source",function(t){var e=i(this).val();i('[class^="video_source_wrap"]').hide(),i(".video_source_wrap_"+e).show(),"html5"===e?i(".tutor-video-poster-field").show():i(".tutor-video-poster-field").hide()}),i(document).on("click",".video_source_wrap_html5 .video_upload_btn",function(t){t.preventDefault();var e,o=i(this);e||(e=wp.media({title:r("Select or Upload Media Of Your Choice","tutor"),button:{text:r("Upload media","tutor")},library:{type:"video"},multiple:!1})).on("select",function(){var t=e.state().get("selection").first().toJSON();o.closest(".video_source_wrap_html5").find("span.video_media_id").data("video_url",t.url).text(t.id).trigger("paste").closest("p").show(),o.closest(".video_source_wrap_html5").find("input.input_source_video_id").val(t.id)}),e.open()}),i(document).on("click","a.tutor-delete-attachment",function(t){t.preventDefault(),i(this).closest(".tutor-added-attachment").remove()}),i(document).on("click",".tutorUploadAttachmentBtn",function(t){t.preventDefault();var a,n=i(this);a||(a=wp.media({title:r("Select or Upload Media Of Your Choice","tutor"),button:{text:r("Upload media","tutor")},multiple:!0})).on("select",function(){var t=a.state().get("selection").toJSON();if(t.length)for(var e=0;e<t.length;e++){var o=t[e],o='<div class="tutor-added-attachment"><i class="tutor-icon-archive"></i> <a href="javascript:;" class="tutor-delete-attachment tutor-icon-line-cross"></a> <span> <a href="'+o.url+'">'+o.filename+'</a> </span><input type="hidden" name="tutor_attachments[]" value="'+o.id+'"></div>';n.closest(".tutor-lesson-attachments-metabox").find(".tutor-added-attachments-wrap").append(o)}}),a.open()}),_tutorobject.open_tutor_admin_menu&&((t=i("#adminmenu")).find('[href="admin.php?page=tutor"]').closest("li.wp-has-submenu").addClass("wp-has-current-submenu"),t.find('[href="admin.php?page=tutor"]').closest("li.wp-has-submenu").find("a.wp-has-submenu").removeClass("wp-has-current-submenu").addClass("wp-has-current-submenu")),i(document).on("click",".tutor-option-media-upload-btn",function(t){t.preventDefault();var e,o=i(this);e||(e=wp.media({title:r("Select or Upload Media Of Your Choice","tutor"),button:{text:r("Upload media","tutor")},multiple:!1})).on("select",function(){var t=e.state().get("selection").first().toJSON();o.closest(".option-media-wrap").find(".option-media-preview").html('<img src="'+t.url+'" alt="" />'),o.closest(".option-media-wrap").find("input").val(t.id),o.closest(".option-media-wrap").find(".tutor-media-option-trash-btn").show()}),e.open()}),i(document).on("click",".tutor-media-option-trash-btn",function(t){t.preventDefault();t=i(this);t.closest(".option-media-wrap").find("img").remove(),t.closest(".option-media-wrap").find("input").val(""),t.closest(".option-media-wrap").find(".tutor-media-option-trash-btn").hide()}),i(document).on("change",".tutor_addons_list_item",function(t){var e=i(this),o=e.prop("checked")?1:0,e=e.attr("name");i.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{isEnable:o,addonFieldName:e,action:"addon_enable_disable"},success:function(t){t.success}})}),i(document).on("submit","#new-instructor-form",function(t){t.preventDefault();var o=i(this),t=o.serializeObject();t.action="tutor_add_instructor",i.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:t,success:function(t){var a,e;t.success?(o.trigger("reset"),i("#form-response").html('<p class="tutor-status-approved-context">'+t.data.msg+"</p>")):(a="",(e=t.data.errors)&&Object.keys(e).length&&(i.each(t.data.errors,function(t,e){var o;(o=e)&&"object"===n(o)&&o.constructor===Object?i.each(e,function(t,e){a+='<p class="tutor-required-fields">'+e[0]+"</p>"}):a+='<p class="tutor-required-fields">'+e+"</p>"}),i("#form-response").html(a)))}})}),i(document).on("click","a.instructor-action",function(t){t.preventDefault();var e=i(this),o=e.attr("data-action"),a=e.attr("data-instructor-id"),t=e.attr("data-prompt-message");t&&!confirm(t)||((a={instructor_id:a,action_name:o,action:"instructor_approval_action"})[o=_tutorobject.nonce_key]=_tutorobject[o],i.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:a,beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){location.reload(!0)},complete:function(){e.removeClass("tutor-updating-message")}}))}),i(document).on("click",".tutor-create-assignments-btn",function(t){t.preventDefault();var e=i(this),o=i(this).attr("data-topic-id"),a=i("#post_ID").val();i.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{topic_id:o,course_id:a,action:"tutor_load_assignments_builder_modal"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){i(".tutor-lesson-modal-wrap .modal-container").html(t.data.output),i(".tutor-lesson-modal-wrap").attr("data-topic-id",o).addClass("show"),i(document).trigger("assignment_modal_loaded",{topic_id:o,course_id:a}),tinymce.init(tinyMCEPreInit.mceInit.tutor_editor_config),tinymce.execCommand("mceRemoveEditor",!1,"tutor_assignments_modal_editor"),tinyMCE.execCommand("mceAddEditor",!1,"tutor_assignments_modal_editor")},complete:function(){quicktags({id:"tutor_assignments_modal_editor"}),e.removeClass("tutor-updating-message")}})}),i(document).on("click",".open-tutor-assignment-modal",function(t){t.preventDefault();var e=i(this),o=e.attr("data-assignment-id"),a=e.attr("data-topic-id"),n=i("#post_ID").val();i.ajax({url:window._tutorobject.ajaxurl,type:"POST",data:{assignment_id:o,topic_id:a,course_id:n,action:"tutor_load_assignments_builder_modal"},beforeSend:function(){e.addClass("tutor-updating-message")},success:function(t){i(".tutor-lesson-modal-wrap .modal-container").html(t.data.output),i(".tutor-lesson-modal-wrap").attr({"data-assignment-id":o,"data-topic-id":a}).addClass("show"),i(document).trigger("assignment_modal_loaded",{assignment_id:o,topic_id:a,course_id:n}),tinymce.init(tinyMCEPreInit.mceInit.tutor_editor_config),tinymce.execCommand("mceRemoveEditor",!1,"tutor_assignments_modal_editor"),tinyMCE.execCommand("mceAddEditor",!1,"tutor_assignments_modal_editor")},complete:function(){quicktags({id:"tutor_assignments_modal_editor"}),e.removeClass("tutor-updating-message")}})}),i(document).on("click",".add-assignment-attachments",function(t){t.preventDefault();var o,a=i(this);o||(o=wp.media({title:r("Select or Upload Media Of Your Choice","tutor"),button:{text:r("Upload media","tutor")},multiple:!1})).on("select",function(){var t=o.state().get("selection").first().toJSON(),e='<div class="tutor-individual-attachment-file"><p class="attachment-file-name">'+t.filename+'</p><input type="hidden" name="tutor_assignment_attachments[]" value="'+t.id+'"><a href="javascript:;" class="remove-assignment-attachment-a text-muted"> &times; Remove</a></div>';i("#assignment-attached-file").append(e),a.closest(".video_source_wrap_html5").find("input").val(t.id)}),o.open()}),i(document).on("click",".remove-assignment-attachment-a",function(t){t.preventDefault(),i(this).closest(".tutor-individual-attachment-file").remove()}),i(document).on("click",".tutor_video_poster_upload_btn",function(t){t.preventDefault();var e,o=i(this);e||(e=wp.media({title:r("Select or Upload Media Of Your Choice","tutor"),button:{text:r("Upload media","tutor")},multiple:!1})).on("select",function(){var t=e.state().get("selection").first().toJSON();o.closest(".tutor-video-poster-wrap").find(".video-poster-img").html('<img src="'+t.sizes.thumbnail.url+'" alt="" />'),o.closest(".tutor-video-poster-wrap").find("input").val(t.id)}),e.open()}),i(document).on("change","#tutor_pmpro_membership_model_select",function(t){t.preventDefault(),"category_wise_membership"===i(this).val()?i(".membership_course_categories").show():i(".membership_course_categories").hide()}),i(document).on("change","#tutor_pmpro_membership_model_select",function(t){t.preventDefault(),"category_wise_membership"===i(this).val()?i(".membership_course_categories").show():i(".membership_course_categories").hide()}),i(document).on("submit",".pmpro_admin form",function(t){var e=i(this);e.find('input[name="tutor_action"]').length&&("category_wise_membership"!=e.find('[name="tutor_pmpro_membership_model"]').val()||e.find(".membership_course_categories input:checked").length||confirm(r("Do you want to save without any category?","tutor"))||t.preventDefault())}),i("#select2_search_user_ajax").select2({allowClear:!0,minimumInputLength:1,placeholder:e,language:{inputTooShort:function(){return r("Please add 1 or more character","tutor")}},escapeMarkup:function(t){return t},ajax:{url:window._tutorobject.ajaxurl,type:"POST",dataType:"json",delay:1e3,data:function(t){return{term:t.term,action:"tutor_json_search_students"}},processResults:function(t){var o=[];return t&&i.each(t,function(t,e){o.push({id:t,text:e})}),{results:o}},cache:!0}}),i(document).on("click","table.enrolments .delete a",function(t){t.preventDefault();var e=i(this).attr("href"),t={title:r("Delete this enrolment","tutor"),description:r("All of the course data like quiz attempts, assignment, lesson <br/>progress will be deleted if you delete this student's enrollment.","tutor"),buttons:{reset:{title:r("Cancel","tutor"),class:"secondary",callback:function(){o.remove()}},keep:{title:r("Yes, Delete This","tutor"),class:"primary",callback:function(){window.location.replace(e)}}}},o=new window.tutor_popup(i,"icon-trash",40).popup(t)});e=i('#tutor-attach-product [name="tutor_course_price_type"]');0==e.length?i("#_tutor_is_course_public_meta_checkbox").show():e.change(function(){var t;i(this).prop("checked")&&(t="paid"==i(this).val()?"hide":"show",i("#_tutor_is_course_public_meta_checkbox")[t]())}).trigger("change"),i(document).on("click",".instructor-layout-template",function(){i(".instructor-layout-template").removeClass("selected-template"),i(this).addClass("selected-template")}),i("#preview-action a.preview").click(function(t){var e=i(this).attr("href");e&&(t.preventDefault(),window.open(e,"_blank"))})})})()})();
=======
jQuery(document).ready(function($){
    'use strict';

    const { __, _x, _n, _nx } = wp.i18n;
    const search_student_placeholder = __( 'Search students', 'tutor' );
    /**
     * Color Picker
     * @since v.1.2.21
     */
    if (jQuery().wpColorPicker) {
        $('.tutor_colorpicker').wpColorPicker();
    }

    if (jQuery().select2){
        $('.tutor_select2').select2();
    }

    /**
     * Option Settings Nav Tab
     */
    $('.tutor-option-nav-tabs li a').click(function(e){
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
    $('#tutor-option-form').submit(function(e){
        e.preventDefault();

        var $form = $(this);
        var data = $form.serializeObject();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : data,
            beforeSend: function () {
                $form.find('.button').addClass('tutor-updating-message');
            },
            success: function (data) {
                data.success ? 
                    tutor_toast(__('Saved', 'tutor'), $form.data('toast_success_message'), 'success') : 
                    tutor_toast(__('Request Error', 'tutor'), __('Could not save', 'tutor'), 'error');
            },
            complete: function () {
                $form.find('.button').removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Withdraw nav tabs
     * @since v.1.1.2
     */
    $(document).on('click', '.withdraw-method-nav li a', function(e){
        e.preventDefault();
        var tab_page_id = $(this).attr('data-target-id');
        $('.withdraw-method-form-wrap').hide();
        $('#'+tab_page_id).show();
    });

    /**
     * End Withdraw nav tabs
     */

    /**
     * Don't move it to anywhere?
     */
    function enable_sorting_topic_lesson(){
        if (jQuery().sortable) {
            $(".course-contents").sortable({
                handle: ".course-move-handle",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_sorting_topics_and_lesson();
                },
            });
            $(".tutor-lessons:not(.drop-lessons)").sortable({
                connectWith: ".tutor-lessons",
                items: "div.course-content-item",
                start: function (e, ui) {
                    ui.placeholder.css('visibility', 'visible');
                },
                stop: function (e, ui) {
                    tutor_sorting_topics_and_lesson();
                },
            });
        }
    }
    enable_sorting_topic_lesson();
    function tutor_sorting_topics_and_lesson(){
        var topics = {};
        $('.tutor-topics-wrap').each(function(index, item){
            var $topic = $(this);
            var topics_id = parseInt($topic.attr('id').match(/\d+/)[0], 10);
            var lessons = {};

            $topic.find('.course-content-item').each(function(lessonIndex, lessonItem){
                var $lesson = $(this);
                var lesson_id = parseInt($lesson.attr('id').match(/\d+/)[0], 10);

                lessons[lessonIndex] = lesson_id;
            });
            topics[index] = { 'topic_id' : topics_id, 'lesson_ids' : lessons };
        });
        $('#tutor_topics_lessons_sorting').val(JSON.stringify(topics));
    }

    /**
     * Lesson Update or Create Modal
     */
    $(document).on( 'click', '.update_lesson_modal_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var content;
        var inputid = 'tutor_lesson_modal_editor';
        var editor = tinyMCE.get(inputid);
        if (editor) {
            content = editor.getContent();
        } else {
            content = $('#'+inputid).val();
        }

        var form_data = $(this).closest('form').serializeObject();
        form_data.lesson_content = content;

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : form_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('#tutor-course-content-wrap').html(data.data.course_contents);
                    enable_sorting_topic_lesson();

                    //Close the modal
                    $('.tutor-lesson-modal-wrap').removeClass('show');

                    tutor_toast(__('Lesson Updated', 'tutor'), $that.data('toast_success_message'), 'success');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Lesson Video
     */
    $(document).on('change', '.tutor_lesson_video_source', function(e){
        var selector = $(this).val();
        $('[class^="video_source_wrap"]').hide();
        $('.video_source_wrap_'+selector).show();

        if (selector === 'html5'){
            $('.tutor-video-poster-field').show();
        } else{
            $('.tutor-video-poster-field').hide();
        }
    });

    $(document).on( 'click', '.video_source_wrap_html5 .video_upload_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: __( 'Select or Upload Media Of Your Choice', 'tutor' ),
            button: {
                text: __( 'Upload media', 'tutor' )
            },
            library: { type: 'video' },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.video_source_wrap_html5').find('span.video_media_id').data('video_url', attachment.url).text(attachment.id).trigger('paste').closest('p').show();
            $that.closest('.video_source_wrap_html5').find('input.input_source_video_id').val(attachment.id);
        });
        // Finally, open the modal on click
        frame.open();
    });

    $(document).on('click', 'a.tutor-delete-attachment', function(e){
        e.preventDefault();
        $(this).closest('.tutor-added-attachment').remove();
    });

    $(document).on('click', '.tutorUploadAttachmentBtn', function(e){
        e.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }
        // Create a new media frame
        frame = wp.media({
            title: __( 'Select or Upload Media Of Your Choice', 'tutor' ),
            button: {
                text: __( 'Upload media', 'tutor' )
            },
            multiple: true  // Set to true to allow multiple files to be selected
        });
        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachments = frame.state().get('selection').toJSON();
            if (attachments.length){
                for (var i=0; i < attachments.length; i++){
                    var attachment = attachments[i];

                    var inputHtml = '<div class="tutor-added-attachment"><i class="tutor-icon-archive"></i> <a href="javascript:;" class="tutor-delete-attachment tutor-icon-line-cross"></a> <span> <a href="'+attachment.url+'">'+attachment.filename+'</a> </span><input type="hidden" name="tutor_attachments[]" value="'+attachment.id+'"></div>';
                    $that.closest('.tutor-lesson-attachments-metabox').find('.tutor-added-attachments-wrap').append(inputHtml);
                }
            }
        });
        // Finally, open the modal on click
        frame.open();
    });

    /**
     * Open Sidebar Menu
     */
    if (_tutorobject.open_tutor_admin_menu){
        var $adminMenu = $('#adminmenu');
        $adminMenu.find('[href="admin.php?page=tutor"]').closest('li.wp-has-submenu').addClass('wp-has-current-submenu');
        $adminMenu.find('[href="admin.php?page=tutor"]').closest('li.wp-has-submenu').find('a.wp-has-submenu').removeClass('wp-has-current-submenu').addClass('wp-has-current-submenu');
    }

    $(document).on('click', '.tutor-option-media-upload-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var frame;
        if ( frame ) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: __( 'Select or Upload Media Of Your Choice', 'tutor' ),
            button: {
                text: __( 'Upload media', 'tutor' )
            },
            multiple: false
        });
        frame.on( 'select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.option-media-wrap').find('.option-media-preview').html('<img src="'+attachment.url+'" alt="" />');
            $that.closest('.option-media-wrap').find('input').val(attachment.id);
            $that.closest('.option-media-wrap').find('.tutor-media-option-trash-btn').show();
        });
        frame.open();
    });

    /**
     * Remove option media
     * @since v.1.4.3
     */
    $(document).on('click', '.tutor-media-option-trash-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        $that.closest('.option-media-wrap').find('img').remove();
        $that.closest('.option-media-wrap').find('input').val('');
        $that.closest('.option-media-wrap').find('.tutor-media-option-trash-btn').hide();
    });


    $(document).on('change', '.tutor_addons_list_item', function(e) {
        var $that = $(this);

        var isEnable = $that.prop('checked') ? 1 : 0;
        var addonFieldName = $that.attr('name');

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {isEnable:isEnable, addonFieldName:addonFieldName, action : 'addon_enable_disable'},
            success: function (data) {
                if (data.success){
                    //Success
                }
            }
        });
    });

    /**
     * Add instructor
     * @since v.1.0.3
     */
    $(document).on('submit', '#new-instructor-form', function(e){
        e.preventDefault();

        var $that = $(this);
        var formData = $that.serializeObject();
        formData.action = 'tutor_add_instructor';

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : formData,
            success: function (data) {
                if (data.success){
                    $that.trigger("reset");
                    $('#form-response').html('<p class="tutor-status-approved-context">'+data.data.msg+'</p>');
                }else{
                    var errorMsg = '';

                    var errors = data.data.errors;
                    if (errors && Object.keys(errors).length){
                        $.each(data.data.errors, function( index, value ) {
                            if (isObject(value)){
                                $.each(value, function( key, value1 ) {
                                    errorMsg += '<p class="tutor-required-fields">'+value1[0]+'</p>';
                                });
                            } else{
                                errorMsg += '<p class="tutor-required-fields">'+value+'</p>';
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

    $(document).on('click', 'a.instructor-action', function(e){
        e.preventDefault();

        var $that = $(this);
        var action = $that.attr('data-action');
        var instructor_id = $that.attr('data-instructor-id');
        
        var prompt_message = $that.attr('data-prompt-message');
        if(prompt_message && !confirm(prompt_message)){
            // Avoid Accidental CLick
            return;
        }

        var nonce_key = _tutorobject.nonce_key;
        var json_data = { instructor_id : instructor_id, action_name : action, action: 'instructor_approval_action'};
        json_data[nonce_key] = _tutorobject[nonce_key];

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : json_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                location.reload(true);
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    function isObject (value) {
        return value && typeof value === 'object' && value.constructor === Object;
    }

    /**
     * Tutor Assignments JS
     * @since v.1.3.3
     */
    $(document).on('click', '.tutor-create-assignments-btn', function(e){
        e.preventDefault();

        var $that = $(this);
        var topic_id = $(this).attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {topic_id : topic_id, course_id : course_id, action: 'tutor_load_assignments_builder_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr('data-topic-id', topic_id).addClass('show');

                $(document).trigger('assignment_modal_loaded', {topic_id : topic_id, course_id : course_id});

                tinymce.init(tinyMCEPreInit.mceInit.tutor_editor_config);
                tinymce.execCommand( 'mceRemoveEditor', false, 'tutor_assignments_modal_editor' );
                tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
            },
            complete: function () {
                quicktags({id : "tutor_assignments_modal_editor"});
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    $(document).on('click', '.open-tutor-assignment-modal', function(e){
        e.preventDefault();

        var $that = $(this);
        var assignment_id = $that.attr('data-assignment-id');
        var topic_id = $that.attr('data-topic-id');
        var course_id = $('#post_ID').val();

        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : {assignment_id : assignment_id, topic_id : topic_id, course_id : course_id, action: 'tutor_load_assignments_builder_modal'},
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                $('.tutor-lesson-modal-wrap').attr({'data-assignment-id' : assignment_id, 'data-topic-id':topic_id}).addClass('show');

                $(document).trigger('assignment_modal_loaded', {assignment_id : assignment_id, topic_id : topic_id, course_id : course_id});

                tinymce.init(tinyMCEPreInit.mceInit.tutor_editor_config);
                tinymce.execCommand( 'mceRemoveEditor', false, 'tutor_assignments_modal_editor' );
                tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
            },
            complete: function () {
                quicktags({id : "tutor_assignments_modal_editor"});
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Update Assignment Data
     */
    $(document).on( 'click', '.update_assignment_modal_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var content;
        var inputid = 'tutor_assignments_modal_editor';
        var editor = tinyMCE.get(inputid);
        if (editor) {
            content = editor.getContent();
        } else {
            content = $('#'+inputid).val();
        }
        
        var form_data = $(this).closest('form').serializeObject();
        form_data.assignment_content = content;
        
        $.ajax({
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            data : form_data,
            beforeSend: function () {
                $that.addClass('tutor-updating-message');
            },
            success: function (data) {
                if (data.success){
                    $('#tutor-course-content-wrap').html(data.data.course_contents);
                    enable_sorting_topic_lesson();

                    //Close the modal
                    $('.tutor-lesson-modal-wrap').removeClass('show');
                    
                    tutor_toast(__('Assignment Updated', 'tutor'), $that.data('toast_success_message'), 'success');
                }
            },
            complete: function () {
                $that.removeClass('tutor-updating-message');
            }
        });
    });

    /**
     * Add Assignment
     */
    $(document).on( 'click', '.add-assignment-attachments',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: __( 'Select or Upload Media Of Your Choice', 'tutor' ),
            button: {
                text: __( 'Upload media', 'tutor' )
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            var  field_markup = '<div class="tutor-individual-attachment-file"><p class="attachment-file-name">'+attachment.filename+'</p><input type="hidden" name="tutor_assignment_attachments[]" value="'+attachment.id+'"><a href="javascript:;" class="remove-assignment-attachment-a text-muted"> &times; Remove</a></div>';

            $('#assignment-attached-file').append(field_markup);
            $that.closest('.video_source_wrap_html5').find('input').val(attachment.id);
        });
        // Finally, open the modal on click
        frame.open();
    });

    $(document).on( 'click', '.remove-assignment-attachment-a',  function( event ){
        event.preventDefault();
        $(this).closest('.tutor-individual-attachment-file').remove();
    });

    /**
     * Used for backend profile photo upload.
     */

    //tutor_video_poster_upload_btn
    $(document).on( 'click', '.tutor_video_poster_upload_btn',  function( event ){
        event.preventDefault();

        var $that = $(this);
        var frame;
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: __( 'Select or Upload Media Of Your Choice', 'tutor' ),
            button: {
                text: __( 'Upload media', 'tutor')
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            $that.closest('.tutor-video-poster-wrap').find('.video-poster-img').html('<img src="'+attachment.sizes.thumbnail.url+'" alt="" />');
            $that.closest('.tutor-video-poster-wrap').find('input').val(attachment.id);
        });
        // Finally, open the modal on click
        frame.open();
    });


    /**
     * Tutor Memberships toggle in Paid Membership Pro panel
     * @since v.1.3.6
     */

    $(document).on( 'change', '#tutor_pmpro_membership_model_select',  function( e ){
        e.preventDefault();

        var $that = $(this);

        if ($that.val() === 'category_wise_membership'){
            $('.membership_course_categories').show();
        } else{
            $('.membership_course_categories').hide();
        }
    });

    $(document).on( 'change', '#tutor_pmpro_membership_model_select',  function( e ){
        e.preventDefault();

        var $that = $(this);

        if ($that.val() === 'category_wise_membership'){
            $('.membership_course_categories').show();
        } else{
            $('.membership_course_categories').hide();
        }
    });

    // Require category selection
    $(document).on('submit', '.pmpro_admin form', function(e) {
        var form = $(this);

        if(!form.find('input[name="tutor_action"]').length) {
            // Level editor or tutor action not necessary
            return;
        }

        if(
            form.find('[name="tutor_pmpro_membership_model"]').val()=='category_wise_membership' && 
            !form.find('.membership_course_categories input:checked').length) {

            if(!confirm(__('Do you want to save without any category?', 'tutor'))) {
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
            inputTooShort: function() {
                return __( 'Please add 1 or more character', 'tutor' );
            },
        },
        escapeMarkup: function( m ) {
            return m;
        },
        ajax: {
            url : window._tutorobject.ajaxurl,
            type : 'POST',
            dataType: 'json',
            delay:       1000,
            data: function( params ) {
                return {
                    term:     params.term,
                    action:   'tutor_json_search_students'
                };
            },
            processResults: function( data ) {
                var terms = [];
                if ( data ) {
                    $.each( data, function( id, text ) {
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
    $(document).on( 'click', 'table.enrolments .delete a',  function( e ){
        e.preventDefault();

        var url = $(this).attr('href');
        var popup;

        var data = {
            title: __('Delete this enrolment', 'tutor'),
            description : __('All of the course data like quiz attempts, assignment, lesson <br/>progress will be deleted if you delete this student\'s enrollment.', 'tutor'),
            buttons : {
                reset: {
                    title: __('Cancel', 'tutor'),
                    class: 'secondary',

                    callback: function() {
                        popup.remove();
                    }
                },
                keep: {
                    title: __('Yes, Delete This', 'tutor'),
                    class: 'primary',
                    callback: function() {
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
    if(price_type.length==0){
        $('#_tutor_is_course_public_meta_checkbox').show();
    }
    else{
        price_type.change(function(){
            if($(this).prop('checked')){
                var method = $(this).val()=='paid' ? 'hide' : 'show';
                $('#_tutor_is_course_public_meta_checkbox')[method]();
            }
        }).trigger('change');
    }    
    
    
    /**
     * Focus selected instructor layout in setting page
     * 
     * @since  v.1.7.5
    */
    $(document).on('click', '.instructor-layout-template', function(){
        $('.instructor-layout-template').removeClass('selected-template');
        $(this).addClass('selected-template');
    });


    
    /**
     * Programmatically open preview link. For some reason it's not working normally.
     * 
     * @since  v.1.7.9
    */
   $('#preview-action a.preview').click(function(e) {
        var href = $(this).attr('href');

        if(href) {
            e.preventDefault();
            window.open(href, '_blank');
        }
   });

    /** Disable typing on datePicker field */
    $('.hasDatepicker, .tutor_date_picker').keydown( function( e ) {
        if ( e.keyCode !== 8 && e.keyCode !== 46 ) {
            e.preventDefault();
        }
    });
   
});

>>>>>>> jk
