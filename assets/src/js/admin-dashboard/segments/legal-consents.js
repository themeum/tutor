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
			const headerSaveButton = getHeaderSaveButton();
			if (!headerSaveButton) {
				return;
			}

			headerSaveButton.removeAttribute('disabled');
			headerSaveButton.click();
			syncFooterSaveButtons();
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

	container.querySelectorAll('[data-page-select-toggle]').forEach((button) => {
		button.addEventListener('click', () => {
			const select = button.closest('.tutor-option-field-input').querySelector('[data-page-select]');
			if (select) {
				select.focus();
			}
		});

		const parent = button.closest('.tutor-option-field-input');
		const selectEl = parent.querySelector('[data-page-select]');
		const textarea = parent.querySelector('textarea');

		if (selectEl && textarea) {
			let previousValues = new Set(Array.from(selectEl.selectedOptions).filter(opt => opt.value).map(opt => opt.value));
			selectEl.addEventListener('change', () => {
				const selectedOptions = Array.from(selectEl.selectedOptions).filter(opt => opt.value);
				const currentValues = new Set(selectedOptions.map(opt => opt.value));
				const newSelections = [...currentValues].filter(val => !previousValues.has(val) && val);

				if (newSelections.length > 0) {
					const newLinks = newSelections.map(val => {
						const opt = selectEl.querySelector(`option[value="${val}"]`);
						const pageTitle = opt ? opt.textContent : '';
						const slug = pageTitle.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
						return `{{${slug}|${val}}`;
					});
					textarea.value = textarea.value ? `${textarea.value} ${newLinks.join(' ')}` : newLinks.join(' ');
					previousValues = currentValues;
					markSettingsAsChanged();
				} else {
					previousValues = currentValues;
				}
			});
		}
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
