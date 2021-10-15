/**
 * Addon Filter and Search
 */
 const selectFilterOption = document.querySelector('.tutor-addons-list-select-filter select.tutor-form-select');
 const searchField = document.querySelector('.tutor-addons-list-select-filter input[type="search"]');
 const searchBtn = document.querySelector('.tutor-addons-list-select-filter .search-btn');
 const tabFilterBtns = document.querySelectorAll('.tutor-addons-list-header .filter-btns .filter-btn');
 const addonsListWrapper = document.querySelector('.tutor-addons-list-items');
 const addonsListCards = document.querySelectorAll('.tutor-addons-card');
 import {addonData} from './addonlist-data'

 const addonsData = _tutorobject.addons_data;
 
 window.addEventListener('DOMContentLoaded', () => {
    addonsData.forEach((item) => {
         const { name, thumb_url, description, is_enabled, version } = item;
         const isChecked = is_enabled && 'checked';
         const author = 'Themeum';
         const url = 'https://www.themeum.com';
         let isSubscribed = false;
 
         addonsListWrapper.innerHTML += `
         <div class="tutor-addons-card ${isSubscribed ? 'not-subscribed' : ''}" ${is_enabled ? 'data-addon-active' : ''}>
             <div class="card-body tutor-px-30 tutor-py-40">
                 <div class="addon-logo">
                 <img src="${thumb_url}" alt="${name}" />
                     
                 </div>
                 <div class="addon-title tutor-mt-20">
                     <h5 class="text-medium-h5 color-text-primary">${name}</h5>
                     <p class="text-medium-small color-text-hints tutor-mt-5">
                         By <a href="${url}" class="color-brand-wordpress">${author}</a>
                     </p>
                 </div>
                 <div class="addon-des text-regular-body color-text-subsued tutor-mt-20">
                     <p>${description}</p>
                 </div>
             </div>
             <div
                 class="
                     card-footer
                     tutor-px-30 tutor-py-25
                     d-flex
                     justify-content-between
                     align-items-center
                 "
             >
                 <div class="addon-toggle">
                     ${
                         isSubscribed
                             ? `
                             <p class="color-text-hints text-medium-small">Required Plugin(s)</p>
                             <p class="color-text-primary text-medium-caption tutor-mt-2">
                                 Woocommerce Subscription
                             </p>`
                             : `
                             <label class="tutor-form-toggle">
                                 <input type="checkbox" class="tutor-form-toggle-input" ${isChecked} />
                                 <span class="tutor-form-toggle-control"></span>
                                 <span class="tutor-form-toggle-label color-text-primary tutor-ml-5">Active</span>
                             </label>`
                     }
                     
                 </div>
                 <div class="addon-version text-medium-small color-text-hints">
                     Version : <span class="text-bold-small color-text-primary">${version}</span>
                 </div>
             </div>
         </div>`;
     });
 });
 
 // Header Tab Filter
 tabFilterBtns.forEach((btn) => {
     btn.addEventListener('click', (e) => {
         tabFilterBtns.forEach((otherBtn) => {
             otherBtn.classList.remove('is-active');
         });
         btn.classList.add('is-active');
 
         const dataAttr = btn.getAttribute('data-tab-filter-target');
         toggleAddonCards(dataAttr);
     });
 });
 
 // Filter Selected types
 selectFilterOption.addEventListener('change', function (e) {
     const selectValue = e.target.value;
     toggleAddonCards(selectValue);
 });
 
 const toggleAddonCards = (value) => {
     switch (value) {
         case 'active':
             document.querySelectorAll('.tutor-addons-list-items .tutor-addons-card').forEach((item) => {
                 item.style.display = 'none';
             });
             document.querySelectorAll('.tutor-addons-list-items [data-addon-active]').forEach((item) => {
                 item.style.display = 'block';
             });
             break;
         case 'deactive':
             document.querySelectorAll('.tutor-addons-list-items .tutor-addons-card').forEach((item) => {
                 if (!item.hasAttribute('data-addon-active')) {
                     item.style.display = 'block';
                 } else {
                     item.style.display = 'none';
                 }
             });
             break;
         case 'all':
             document.querySelectorAll('.tutor-addons-list-items .tutor-addons-card').forEach((item) => {
                 item.style.display = 'block';
             });
             break;
     }
 };
 
 window.addEventListener('load', () => {
     const addonsListCheckboxs = document.querySelectorAll('.tutor-addons-card input[type=checkbox]');
 
     addonsListCheckboxs.forEach((inputEl) => {
         inputEl.addEventListener('change', function (e) {
             if (e.target.checked) {
                 e.target.closest('.tutor-addons-card').setAttribute('data-addon-active', '');
             } else {
                 e.target.closest('.tutor-addons-card').removeAttribute('data-addon-active');
             }
         });
     });
 
     searchBtn.addEventListener('click', () => {
         if (searchField.value) {
             const cardTitles = Array.from(document.querySelectorAll('.tutor-addons-card .addon-title h5')).map(
                 (x) => x.innerText,
             );
 
             // console.log(searchField.value, cardTitles, cardTitles.includes(searchField.value));
 
             /**
              * Filter array items based on search criteria (query)
              */
             const filterItems = (arr, query) => {
                 return arr.filter((el) => el.toLowerCase().indexOf(query.toLowerCase()) !== -1);
             };
 
             // console.log(filterItems(cardTitles, searchField.value));
             console.log(
                 filterItems(
                     Array.from(addonData).map((x) => x.title),
                     searchField.value,
                 ),
             );
         }
     });
 });