window.selectSearchField = (selectElement) => {
    const tutorFormSelect = document.querySelectorAll(selectElement);

    setTimeout(() => {
        tutorFormSelect.forEach(element => {
            let searchInput, resultFilter, resultWrap, resultList, textToSearch, dropDownAll, dropDown;
            element.insertAdjacentHTML('afterend', ddMarkup(element.options));
            searchInput = element.nextElementSibling.querySelector('input');

            dropDownWrapper = document.querySelector('.tutor-dropdown-select');
            dropDownAll = document.querySelector('.tutor-dropdown-select-options-container');
            dropDown = element.nextElementSibling.querySelector('.tutor-dropdown-select-options-container');
            const selectLabel = element.nextElementSibling.querySelector('.tutor-dropdown-select-selected');

            selectLabel.onclick = (e) => {
                dropDownAll.classList.remove('is-active');
                dropDown.classList.toggle('is-active');
                searchInput.focus();
            }
            resultWrap = searchInput.nextElementSibling;
            resultList = resultWrap.querySelectorAll('.tutor-dropdown-select-option');
            resultList.forEach((item) => {
                item.onclick = (e) => {
                    let selectFieldOptions = Array.from(element.options);
                    selectFieldOptions.forEach((option, i) => {
                        if (option.value === e.target.dataset.key) element.selectedIndex = i;
                    });
                    dropDown.classList.toggle('is-active');
                    selectLabel.querySelector('.text-medium-body').innerText = e.target.innerText;
                    console.log(element.value);
                }
            })

            searchInput.onkeyup = (e) => {
                resultFilter = e.target.value.toUpperCase();
                resultList.forEach((item) => {
                    textToSearch = item.querySelector(".text-regular-caption");
                    txtValue = textToSearch.textContent || textToSearch.innerText;
                    if (txtValue.toUpperCase().indexOf(resultFilter) > -1) {
                        item.style.display = ''
                    } else {
                        item.style.display = 'none';
                        resultWrap.innerHTML = `
                        <div class="tutor-dropdown-select-option">
                            <label for="select-item-1">
                                <div class="text-regular-caption color-text-title tutor-admin-report-frequency" data-key="">No item found.</div>
                            </label>
                        </div>
                        `;
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
                <input type="text" placeholder="Search here...">
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