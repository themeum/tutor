window.selectSearchField = (selectElement) => {
    const tutorFormSelect = document.querySelectorAll(selectElement);

    const dd_hide_onclick = () => {
        setTimeout(() => {
            let dd_wrap_main = document.querySelectorAll('.tutor-dropdown-select.select-dropdown');
            if (dd_wrap_main) {
                dd_wrap_main.forEach((item_main) => {
                    item_main.onclick = (e) => { e.stopPropagation(); }
                    let dd_wrap = item_main.querySelectorAll('.tutor-dropdown-select-options-container');
                    dd_wrap.forEach((item) => {
                        item.onclick = (e) => { e.stopPropagation(); }
                        item.classList.remove('is-active');
                    })
                })
            }
        }, 100)
    }

    document.onclick = () => {
        dd_hide_onclick();
    }

    setTimeout(() => {
        tutorFormSelect.forEach(element => {

            let initialSelectedItem = element.options[element.selectedIndex];
            // console.log(element.options[element.selectedIndex].text);

            element.style.display = 'none';
            let searchInputWrap, searchInput, resultFilter, resultWrap, resultList, textToSearch, dropDown;
            element.insertAdjacentHTML('afterend', ddMarkup(element.options));
            searchInputWrap = element.nextElementSibling.querySelector('.tutor-input-search');
            searchInput = searchInputWrap && searchInputWrap.querySelector('input');
            if (element.options.length < 5) {
                searchInputWrap.style.display = 'none';
            }

            dropDown = element.nextElementSibling.querySelector('.tutor-dropdown-select-options-container');
            const selectLabel = element.nextElementSibling.querySelector('.tutor-dropdown-select-selected');
            const selectedLabel = selectLabel && selectLabel.querySelector('.text-medium-body');
            selectedLabel.innerText = initialSelectedItem.text;

            selectLabel.onclick = (e) => {
                // dd_hide_onclick();
                e.stopPropagation();
                dropDown.classList.toggle('is-active');
                searchInput.focus();
            }

            resultWrap = searchInputWrap.nextElementSibling;
            resultList = resultWrap && resultWrap.querySelectorAll('.tutor-dropdown-select-option');

            if (resultList) {
                resultList.forEach((item) => {
                    item.onclick = (e) => {
                        let selectFieldOptions = Array.from(element.options);
                        selectFieldOptions.forEach((option, i) => {
                            if (option.value === e.target.dataset.key) {
                                dropDown.classList.toggle('is-active');
                                selectedLabel.innerText = e.target.innerText;
                                selectedLabel.dataset.value = option.value;
                                element.value = option.value;
                            }
                        });
                        var onChangeEvent = new Event('change');
                        element.dispatchEvent(onChangeEvent);
                    }
                })
            }




            searchInput.onkeyup = (e) => {
                resultFilter = e.target.value.toUpperCase();
                resultList.forEach((item) => {
                    textToSearch = item.querySelector(".text-regular-caption");
                    txtValue = textToSearch.textContent || textToSearch.innerText;
                    if (txtValue.toUpperCase().indexOf(resultFilter) > -1) {
                        item.style.display = ''
                    } else {
                        // console.log(item.style.display);
                        item.style.display = 'none';
                        /* resultWrap.innerHTML = `
                        <div class="tutor-dropdown-select-option">
                            <label for="select-item-1">
                                <div class="text-regular-caption color-text-title tutor-admin-report-frequency" data-key="">No item found.</div>
                            </label>
                        </div>
                        `; */
                    }
                })
            }
        });
    }, 20);



    const ddMarkup = (options) => {

        let optionsList = '';
        Array.from(options).forEach((item) => {
            optionsList += `
            <div class="tutor-dropdown-select-option">
                <label for="select-item-1">
                    <div class="text-regular-caption color-text-title tutor-admin-report-frequency" data-key="${item.value}">${item.text}</div>
                </label>
            </div>
            `;
        });

        let markupDD = `
        <div class="tutor-dropdown-select select-dropdown">
            <div class="tutor-dropdown-select-options-container">
                <div class="tutor-input-search">
                    <div class="tutor-input-group tutor-form-control-has-icon tutor-form-control-lg">
                        <span class="ttr-search-filled tutor-input-group-icon color-black-50"></span>
                        <input
                        type="search"
                        class="tutor-form-control"
                        placeholder="Search ..."
                        />
                    </div>
                </div>
                <div class="tutor-frequencies">
                    ${optionsList}
                </div>
            </div>
            <div class="tutor-dropdown-select-selected">
                <div class="text-medium-body color-text-primary"> ${window.wp.i18n.__('Select One', 'tutor')}	</div>
            </div>
        </div>
        `;
        return markupDD;
    };
}

selectSearchField('.tutor-form-select');