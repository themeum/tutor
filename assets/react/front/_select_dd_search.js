window.selectSearchField = (selectElement) => {
    const tutorFormSelect = document.querySelectorAll(selectElement);

    const dd_hide_onclick = () => {
        let dd_wrap = document.querySelectorAll('.tutor-dropdown-select-options-container');
        dd_wrap.forEach((item) => {
            item.onclick = (e) => e.stopPropagation();
            item.classList.contains('is-active') ? item.classList.remove('is-active') : ''
        })
    }

    document.onclick = () => {
        dd_hide_onclick();
    }

    setTimeout(() => {
        tutorFormSelect.forEach(element => {
            element.style.display = 'none';
            let searchInputWrap, searchInput, resultFilter, resultWrap, resultList, textToSearch, dropDownAll, dropDown;
            element.insertAdjacentHTML('afterend', ddMarkup(element.options));
            searchInputWrap = element.nextElementSibling.querySelector('.tutor-input-search');
            searchInput = searchInputWrap && searchInputWrap.querySelector('input');
            if (element.options.length < 5) {
                searchInputWrap.style.display = 'none';
            }

            dropDownWrapper = document.querySelector('.tutor-dropdown-select');
            dropDownAll = document.querySelector('.tutor-dropdown-select-options-container');
            dropDown = element.nextElementSibling.querySelector('.tutor-dropdown-select-options-container');
            const selectLabel = element.nextElementSibling.querySelector('.tutor-dropdown-select-selected');
            const selectedLabel = selectLabel && selectLabel.querySelector('.text-medium-body');

            selectLabel.onclick = (e) => {
                e.stopPropagation();
                dd_hide_onclick();
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
                                element.value = e.target.dataset.key;
                                dropDown.classList.toggle('is-active');
                                selectedLabel.innerText = e.target.innerText;
                                selectedLabel.dataset.value = option.value;
                            }
                        });
                        console.log(element.value);
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
                <div class="text-medium-body color-text-primary"> Today	</div>
            </div>
        </div>
        `;
        return markupDD;
    };
}

selectSearchField('.tutor-form-select');