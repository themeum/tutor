const legalConsentPageId = 'legal_consents';

const getLegalConsentPage = () => document.getElementById(legalConsentPageId);

const getHeaderSaveButton = () => document.getElementById('save_tutor_option');

const isLegalConsentsPageActive = () => {
	const page = getLegalConsentPage();
	return !!page && page.classList.contains('is-active');
};

const toggleHeaderSaveVisibility = () => {
	const headerSaveButton = getHeaderSaveButton();

	if (!headerSaveButton) {
		return;
	}

	headerSaveButton.style.display = isLegalConsentsPageActive() ? 'none' : '';
};

const syncFooterSaveButtons = () => {
	const page = getLegalConsentPage();
	const headerSaveButton = getHeaderSaveButton();

	if (!page || !headerSaveButton) {
		return;
	}

	page.querySelectorAll('[data-consent-save]').forEach((button) => {
		button.disabled = headerSaveButton.disabled;
		button.classList.toggle('is-loading', headerSaveButton.classList.contains('is-loading'));
	});
};

const updateConsentTitle = (card, value) => {
	const title = card.querySelector('[data-consent-title]');
	if (!title) {
		return;
	}

	title.textContent = value.trim() || 'Registration Consent';
};

const markSettingsAsChanged = () => {
	const headerSaveButton = getHeaderSaveButton();

	if (!headerSaveButton) {
		return;
	}

	headerSaveButton.removeAttribute('disabled');
	syncFooterSaveButtons();
};

const toggleConsentCard = (card, collapsed) => {
	card.classList.toggle('is-collapsed', collapsed);

	const collapsedInput = card.querySelector('[data-consent-collapsed]');
	if (collapsedInput) {
		collapsedInput.value = collapsed ? 'on' : 'off';
	}

	const toggleButton = card.querySelector('[data-consent-toggle]');
	if (toggleButton) {
		toggleButton.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
	}

	markSettingsAsChanged();
};

const bindCard = (card) => {
	const titleInput = card.querySelector('[data-consent-title-input]');
	const enabledInput = card.querySelector('[data-consent-enabled]');
	const enabledHiddenInput = card.querySelector('[data-consent-enabled-hidden]');
	const toggleButton = card.querySelector('[data-consent-toggle]');
	const deleteButton = card.querySelector('[data-consent-delete]');
	const cancelButton = card.querySelector('[data-consent-cancel]');
	const saveButton = card.querySelector('[data-consent-save]');

	if (titleInput) {
		titleInput.addEventListener('input', (event) => {
			updateConsentTitle(card, event.target.value);
		});
	}

	if (enabledInput && enabledHiddenInput) {
		enabledInput.addEventListener('change', () => {
			enabledHiddenInput.value = enabledInput.checked ? 'on' : 'off';
			markSettingsAsChanged();
		});
	}

	if (toggleButton) {
		toggleButton.addEventListener('click', () => {
			toggleConsentCard(card, !card.classList.contains('is-collapsed'));
		});
	}

	if (deleteButton) {
		deleteButton.addEventListener('click', () => {
			card.remove();
			markSettingsAsChanged();
		});
	}

	if (cancelButton) {
		cancelButton.addEventListener('click', () => {
			window.location.reload();
		});
	}

	if (saveButton) {
		saveButton.addEventListener('click', () => {
			const formData = new FormData();
			formData.append('action', 'create');
			formData.append('consent_title', card.querySelector('[data-consent-title-input]')?.value || '');
			formData.append('consent_message', card.querySelector('textarea[name*="message"]')?.value || '');

			const displayOnCheckboxes = card.querySelectorAll('[name*="display_on"]');
			const displayOnValues = Array.from(displayOnCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
			formData.append('display_on', displayOnValues.join(','));

			const methodSelect = card.querySelector('select[name*="method"]');
			if (methodSelect) {
				formData.append('consent_method', methodSelect.value);
			}

			const contentMapCheckboxes = card.querySelectorAll('[data-page-checkbox]');
			if (contentMapCheckboxes.length) {
				const selectedPages = Array.from(contentMapCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
				formData.append('consent_map', JSON.stringify(selectedPages.reduce((acc, val, idx) => {
					acc[`page_${idx + 1}`] = val;
					return acc;
				}, {})));
			}

			saveButton.classList.add('is-loading');
			saveButton.disabled = true;

			fetch(ajaxurl, {
				method: 'POST',
				body: formData,
			})
				.then(res => res.json())
				.then(data => {
					if (data.success) {
						window.location.reload();
					}
				})
				.finally(() => {
					saveButton.classList.remove('is-loading');
					saveButton.disabled = false;
				});
		});
	}
};

const appendConsentCard = (container) => {
	const template = container.querySelector('[data-consent-template]');
	const list = container.querySelector('[data-consent-list]');

	if (!template || !list) {
		return;
	}

	const nextIndex = Number(container.dataset.nextIndex || list.children.length || 0);
	const markup = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
	const wrapper = document.createElement('div');

	wrapper.innerHTML = markup.trim();

	const card = wrapper.firstElementChild;
	if (!card) {
		return;
	}

	list.appendChild(card);
	container.dataset.nextIndex = String(nextIndex + 1);
	bindCard(card);
	updateConsentTitle(card, card.querySelector('[data-consent-title-input]')?.value || '');
	markSettingsAsChanged();
};

const initLegalConsents = () => {
	const page = getLegalConsentPage();
	if (!page) {
		return;
	}

	const container = page.querySelector('[data-legal-consents]');
	if (!container) {
		return;
	}

	const cards = container.querySelectorAll('[data-consent-card]');
	container.dataset.nextIndex = String(cards.length);

	cards.forEach((card) => {
		bindCard(card);
		updateConsentTitle(card, card.querySelector('[data-consent-title-input]')?.value || '');
	});

	container.querySelector('[data-add-consent]')?.addEventListener('click', () => {
		appendConsentCard(container);
	});

	container.querySelectorAll('[data-page-dropdown-toggle]').forEach((button) => {
		button.addEventListener('click', (e) => {
			e.stopPropagation();
			const dropdown = button.closest('.tutor-option-field-input').querySelector('[data-page-dropdown]');
			if (dropdown) {
				dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
			}
		});

		const parent = button.closest('.tutor-option-field-input');
		const checkboxes = parent.querySelectorAll('[data-page-checkbox]');
		const textarea = parent.querySelector('textarea');
		const toggleBtn = parent.querySelector('[data-page-dropdown-toggle]');

		if (checkboxes.length && textarea) {
			checkboxes.forEach(checkbox => {
				checkbox.addEventListener('change', () => {
					const checkedOptions = Array.from(checkboxes).filter(cb => cb.checked);
					const currentValues = new Set(checkedOptions.map(cb => cb.value));
					const previousValues = new Set([...checkedOptions.map(cb => cb.value), ...Array.from(checkboxes).filter(cb => !cb.checked).map(cb => cb.value)]);

					const newlySelected = [...currentValues].filter(val => !previousValues.has(val) || (previousValues.has(val) && checkbox.checked && checkbox.dataset.previousChecked === 'true'));

					if (newlySelected.length > 0) {
						const newLinks = newlySelected.map(val => {
							const cb = Array.from(checkboxes).find(c => c.value === val);
							const pageTitle = cb ? cb.nextElementSibling?.textContent || '' : '';
							const slug = pageTitle.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
							return `{{${slug}|${val}}`;
						});
						textarea.value = textarea.value ? `${textarea.value} ${newLinks.join(' ')}` : newLinks.join(' ');
						markSettingsAsChanged();
					}

					checkedOptions.forEach(cb => cb.dataset.previousChecked = 'true');
				});
			});
		}

		document.addEventListener('click', (e) => {
			if (!button.contains(e.target) && !button.closest('.tutor-option-field-input').querySelector('[data-page-dropdown]')?.contains(e.target)) {
				const dropdown = parent.querySelector('[data-page-dropdown]');
				if (dropdown) {
					dropdown.style.display = 'none';
				}
			}
		});
	});

	toggleHeaderSaveVisibility();
	syncFooterSaveButtons();

	const headerSaveButton = getHeaderSaveButton();
	if (headerSaveButton) {
		const observer = new MutationObserver(() => {
			toggleHeaderSaveVisibility();
			syncFooterSaveButtons();
		});

		observer.observe(headerSaveButton, {
			attributes: true,
			attributeFilter: ['disabled', 'class', 'style'],
		});
	}

	document.querySelectorAll('[tutor-option-tabs] a').forEach((tab) => {
		tab.addEventListener('click', () => {
			window.setTimeout(() => {
				toggleHeaderSaveVisibility();
				syncFooterSaveButtons();
			}, 0);
		});
	});

	document.getElementById('tutor-option-form')?.addEventListener('input', syncFooterSaveButtons);
	document.getElementById('tutor-option-form')?.addEventListener('change', syncFooterSaveButtons);
	window.addEventListener('tutor_option_saved', syncFooterSaveButtons);
};

document.addEventListener('DOMContentLoaded', initLegalConsents);
