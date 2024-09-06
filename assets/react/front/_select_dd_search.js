window.selectSearchField = (selectElement) => {
	const tutorFormSelect = document.querySelectorAll(selectElement);
	  
	(() => {
		tutorFormSelect.forEach((element) => {
			if (element && !element.classList.contains('tutor-js-form-select') && !element.hasAttribute('noDropdown') && !element.classList.contains('no-tutor-dropdown')) {
				let initialSelectedItem = element.options[element.selectedIndex];
				element.style.display = 'none';
				let selectElement, searchInputWrap, searchInput, resultFilter, resultWrap, resultList, textToSearch, dropDown;

				element.insertAdjacentHTML('afterend', ddMarkup(element.options));

				selectElement = element.nextElementSibling;
				searchInputWrap = selectElement.querySelector('.tutor-form-select-search');
				searchInput = searchInputWrap && searchInputWrap.querySelector('input');

				dropDown = selectElement.querySelector('.tutor-form-select-dropdown');
				const selectLabel = selectElement.querySelector('.tutor-form-select-label');
				selectLabel.innerText = initialSelectedItem && initialSelectedItem.text;

				selectElement.onclick = (e) => {
					e.stopPropagation();
					dd_hide_dom_click(document.querySelectorAll('.tutor-js-form-select'));

					selectElement.classList.toggle('is-active');

					setTimeout(() => {
						searchInput.focus();
					}, 100);

					dropDown.onclick = (e) => {
						e.stopPropagation();
					};
				};

				dd_hide_dom_click(document.querySelectorAll('.tutor-js-form-select'));

				resultWrap = searchInputWrap.nextElementSibling;
				resultList = resultWrap && resultWrap.querySelectorAll('.tutor-form-select-option');

				if (resultList) {
					resultList.forEach((item) => {
						item.onclick = (e) => {
							e.stopPropagation();
							let selectFieldOptions = Array.from(element.options);
							selectFieldOptions.forEach((option, i) => {
								if (option.value === e.target.dataset.key) {
									selectElement.classList.remove('is-active');
									selectLabel.innerText = e.target.innerText;
									selectLabel.dataset.value = option.value;
									element.value = option.value;
									const save_tutor_option = document.getElementById('save_tutor_option');
									if (save_tutor_option) {
										save_tutor_option.disabled = false;
									}
								}
							});

							const onChangeEvent = new Event('change', { bubbles: true });
							element.dispatchEvent(onChangeEvent);
						};
					});
				}

				const countHiddenItems = (list) => {
					let result = 0;
					list.forEach((item) => {
						if (item.style.display !== 'none') {
							result += 1;
						}
					});
					return result;
				};

				searchInput.oninput = (e) => {
					let txtValue,
						noItemFound = false;
					resultFilter = e.target.value.toUpperCase();
					resultList.forEach((item) => {
						textToSearch = item.querySelector('[tutor-dropdown-item]');
						txtValue = textToSearch.textContent || textToSearch.innerText;
						if (txtValue.toUpperCase().indexOf(resultFilter) > -1) {
							item.style.display = '';
							noItemFound = 'false';
						} else {
							noItemFound = 'true';
							item.style.display = 'none';
						}
					});

					let noItemText = `
                    <div class="tutor-form-select-option noItem">
                        No item found
                    </div>
                    `;

					let appendNoItemText = dropDown.querySelector('.tutor-form-select-options');
					if (0 == countHiddenItems(resultList)) {
						let hasNoItem = false;
						appendNoItemText.querySelectorAll('.tutor-form-select-option').forEach((item) => {
							if (item.classList.contains('noItem') == true) {
								hasNoItem = true;
							}
						});
						if (false == hasNoItem) {
							appendNoItemText.insertAdjacentHTML('beforeend', noItemText);
							hasNoItem = true;
						}
					} else {
						if (null !== dropDown.querySelector('.noItem')) {
							dropDown.querySelector('.noItem').remove();
						}
					}
				};
			}
		});

		const selectDdMarkup = document.querySelectorAll('.tutor-js-form-select');
		selectDdMarkup.forEach((item) => {
			if (item.nextElementSibling) {
				if (item.nextElementSibling.classList.contains('tutor-js-form-select')) {
					item.nextElementSibling.remove();
				}
			}
		});

		let otherDropDown = document.querySelectorAll('.tutor-js-form-select');
		document.onclick = (e) => {
			dd_hide_dom_click(otherDropDown);
		};
	})();

	function dd_hide_dom_click(elem) {
		if (elem) {
			elem.forEach((elemItem) => {
				elemItem.classList.remove('is-active');
			});
		}
	}

	function ddMarkup(options) {
		let optionsList = '';
		Array.from(options).forEach((item) => {
			optionsList += `
            <div class="tutor-form-select-option">
				<span tutor-dropdown-item data-key="${tutor_esc_attr(item.value)}" class="tutor-nowrap-ellipsis" title="${tutor_esc_attr(item.text)}">${tutor_esc_html(item.text)}</span>
            </div>
            `;
		});

		let markupDD = `
      <div class="tutor-form-control tutor-form-select tutor-js-form-select">
			<span class="tutor-form-select-label" tutor-dropdown-label>${window.wp.i18n.__('Select', 'tutor')}</span>
            <div class="tutor-form-select-dropdown">
				<div class="tutor-form-select-search tutor-pt-8 tutor-px-8">
					<div class="tutor-form-wrap">
						<span class="tutor-form-icon"><i class="tutor-icon-search" area-hidden="true"></i></span>
						<input type="search" class="tutor-form-control" placeholder="Search ..." />
					</div>
				</div>
                <div class="tutor-form-select-options">
                    ${optionsList}
                </div>
            </div>
        </div>
        `;
		return markupDD;
	}
};

selectSearchField('.tutor-form-select');
