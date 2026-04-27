const { __ } = wp.i18n;

const SELECTORS = {
	legalConsentPage: 'legal_consents',
	headerSaveButton: 'save_tutor_option',
	tutorOptionForm: 'tutor-option-form',
	tabLinks: '[tutor-option-tabs] a',
	tabPages: '.tutor-option-nav-page',
	nonceInput: 'input[name="_tutor_nonce"]',
	legalConsentsContainer: '[data-legal-consents]',
	consentList: '[data-consent-list]',
	consentTemplate: '[data-consent-template]',
	consentCard: '[data-consent-card]',
	consentEmptyState: '[data-consent-empty-state]',
	consentFooter: '[data-consent-footer]',
	addConsent: '[data-add-consent]',
	consentTitleDisplay: '[data-consent-title]',
	consentTitleInput: '[data-consent-title-input]',
	consentEnabled: '[data-consent-enabled]',
	consentEnabledHidden: '[data-consent-enabled-hidden]',
	consentToggleButton: '[data-consent-toggle]',
	consentDeleteButton: '[data-consent-delete]',
	consentCancelButton: '[data-consent-cancel]',
	consentSaveButton: '[data-consent-save]',
	consentMessageTextarea: 'textarea[name="consent_message"]',
	consentMethodSelect: 'select[name="consent_method"]',
	displayOnCheckboxes: '[name="display_on[]"]',
	pageDropdownToggle: '[data-page-dropdown-toggle]',
	pageDropdown: '[data-page-dropdown]',
	pageButton: '[data-page-btn]',
};

const CSS_CLASSES = {
	active: 'is-active',
	collapsed: 'is-collapsed',
	loading: 'is-loading',
};

const ARIA = {
	expanded: 'aria-expanded',
};

const DATA_ATTRIBUTES = {
	consentId: 'consentId',
	nextIndex: 'nextIndex',
	pageKey: 'pageKey',
};

const AJAX_ACTIONS = {
	legalConsents: 'tutor_gdpr_legal_consents',
};

const CRUD_ACTIONS = {
	create: 'create',
	update: 'update',
	delete: 'delete',
};

const FORM_FIELDS = {
	action: 'action',
	crudAction: 'crud_action',
	nonce: '_tutor_nonce',
	id: 'id',
	title: 'consent_title',
	message: 'consent_message',
	displayOn: 'display_on',
	method: 'consent_method',
	consentMap: 'consent_map',
	isActive: 'is_active',
};

const DEFAULT_CONSENT_TITLE = __('Demo Consent', 'tutor');
const PLACEHOLDER_PATTERN = /\{([a-zA-Z0-9_-]+)\}/g;
const TEMPLATE_INDEX_PLACEHOLDER = '__INDEX__';
const TUTOR_OPTION_SAVED_EVENT = 'tutor_option_saved';

const getNonceValue = () => document.querySelector(SELECTORS.nonceInput)?.value || '';

const getLegalConsentPage = () => document.getElementById(SELECTORS.legalConsentPage);

const getHeaderSaveButton = () => document.getElementById(SELECTORS.headerSaveButton);

const showToast = (title, message, type = 'success') => {
	if (typeof window.tutor_toast === 'function') {
		window.tutor_toast(title, message, type);
	}
};

const getResponseMessage = (data, fallbackMessage) => {
	if (typeof data?.message === 'string' && data.message.length) {
		return data.message;
	}

	if (typeof data?.data === 'string' && data.data.length) {
		return data.data;
	}

	return fallbackMessage;
};

const isSuccessfulResponse = (data) => Boolean(
	data?.success === true
	|| (typeof data?.status_code === 'number' && data.status_code >= 200 && data.status_code < 300)
);

const isLegalConsentsPageActive = () => {
	const page = getLegalConsentPage();
	return !!page && page.classList.contains(CSS_CLASSES.active);
};

const syncFooterSaveButtons = () => {
	const page = getLegalConsentPage();
	const headerSaveButton = getHeaderSaveButton();

	if (!page || !headerSaveButton) {
		return;
	}

	const isLoading = headerSaveButton.classList.contains(CSS_CLASSES.loading);
	page.querySelectorAll(SELECTORS.consentSaveButton).forEach((button) => {
		button.classList.toggle(CSS_CLASSES.loading, isLoading);
	});
};

const toggleHeaderSaveVisibility = () => {
	const headerSaveButton = getHeaderSaveButton();

	if (!headerSaveButton) {
		return;
	}

	headerSaveButton.style.display = isLegalConsentsPageActive() ? 'none' : '';
};

const markSettingsAsChanged = () => {
	const headerSaveButton = getHeaderSaveButton();

	if (!headerSaveButton) {
		return;
	}

	headerSaveButton.removeAttribute('disabled');
	syncFooterSaveButtons();
};

const updateConsentTitle = (card, value) => {
	const title = card.querySelector(SELECTORS.consentTitleDisplay);
	if (title) {
		title.textContent = value.trim() || DEFAULT_CONSENT_TITLE;
	}
};

const syncEmptyState = (container) => {
	const list = container?.querySelector(SELECTORS.consentList);
	const emptyState = container?.querySelector(SELECTORS.consentEmptyState);
	const footer = container?.querySelector(SELECTORS.consentFooter);

	if (!list || !emptyState) {
		return;
	}

	const hasCards = list.querySelectorAll(SELECTORS.consentCard).length > 0;

	emptyState.hidden = hasCards;

	if (footer) {
		footer.hidden = !hasCards;
	}
};

const getCardFormState = (card) => ({
	title: card.querySelector(SELECTORS.consentTitleInput)?.value || '',
	enabled: Boolean(card.querySelector(SELECTORS.consentEnabled)?.checked),
	message: card.querySelector(SELECTORS.consentMessageTextarea)?.value || '',
	method: card.querySelector(SELECTORS.consentMethodSelect)?.value || '',
	displayOn: Array.from(card.querySelectorAll(SELECTORS.displayOnCheckboxes))
		.filter((checkbox) => checkbox.checked)
		.map((checkbox) => checkbox.value)
		.sort(),
	consentMap: getSelectedPagesMap(card),
});

const getSelectedPagesMap = (card) => {
	const messageValue = card.querySelector(SELECTORS.consentMessageTextarea)?.value || '';
	const usedPlaceholders = new Set(Array.from(messageValue.matchAll(PLACEHOLDER_PATTERN), (match) => match[1]));
	const selectedPages = {};

	card.querySelectorAll(SELECTORS.pageButton).forEach((button) => {
		const pageKey = button.dataset[DATA_ATTRIBUTES.pageKey];
		if (pageKey && usedPlaceholders.has(pageKey)) {
			selectedPages[pageKey] = button.value;
		}
	});

	return selectedPages;
};

const isSameCardState = (left, right) => {
	if (!left || !right) {
		return false;
	}

	return left.title === right.title
		&& left.enabled === right.enabled
		&& left.message === right.message
		&& left.method === right.method
		&& JSON.stringify(left.displayOn) === JSON.stringify(right.displayOn)
		&& JSON.stringify(left.consentMap) === JSON.stringify(right.consentMap);
};

const applyCardState = (card, state) => {
	const titleInput = card.querySelector(SELECTORS.consentTitleInput);
	const enabledInput = card.querySelector(SELECTORS.consentEnabled);
	const enabledHiddenInput = card.querySelector(SELECTORS.consentEnabledHidden);
	const messageInput = card.querySelector(SELECTORS.consentMessageTextarea);
	const methodSelect = card.querySelector(SELECTORS.consentMethodSelect);

	if (titleInput) {
		titleInput.value = state.title || '';
		updateConsentTitle(card, titleInput.value);
	}

	if (enabledInput) {
		enabledInput.checked = Boolean(state.enabled);
	}

	if (enabledHiddenInput) {
		enabledHiddenInput.value = state.enabled ? 'on' : 'off';
	}

	if (messageInput) {
		messageInput.value = state.message || '';
		messageInput.dispatchEvent(new Event('input', { bubbles: true }));
	}

	if (methodSelect) {
		methodSelect.value = state.method || methodSelect.value;
	}

	card.querySelectorAll(SELECTORS.displayOnCheckboxes).forEach((checkbox) => {
		checkbox.checked = state.displayOn.includes(checkbox.value);
	});
};

const syncCardSaveButton = (card, savedState) => {
	const saveButton = card.querySelector(SELECTORS.consentSaveButton);
	if (!saveButton || saveButton.classList.contains(CSS_CLASSES.loading)) {
		return;
	}

	saveButton.disabled = isSameCardState(getCardFormState(card), savedState);
};

const toggleConsentCard = (card, collapsed) => {
	if (0 === Number(card.dataset[DATA_ATTRIBUTES.consentId] || 0)) {
		return;
	}

	card.classList.toggle(CSS_CLASSES.collapsed, collapsed);

	const toggleButton = card.querySelector(SELECTORS.consentToggleButton);
	if (toggleButton) {
		toggleButton.setAttribute(ARIA.expanded, String(!collapsed));
	}
};

const closePageDropdown = (dropdown, toggleButton) => {
	if (!dropdown) {
		return;
	}

	dropdown.hidden = true;

	if (toggleButton) {
		toggleButton.setAttribute(ARIA.expanded, 'false');
	}
};

const closeAllPageDropdowns = (scope = document, exceptDropdown = null) => {
	scope.querySelectorAll(SELECTORS.pageDropdown).forEach((dropdown) => {
		if (dropdown === exceptDropdown) {
			return;
		}

		closePageDropdown(dropdown, dropdown.parentElement?.querySelector(SELECTORS.pageDropdownToggle));
	});
};

const bindPageLinkControl = (card, onCardChange) => {
	const fieldInput = card.querySelector(SELECTORS.pageDropdownToggle)?.closest('.tutor-option-field-input');
	const toggleButton = fieldInput?.querySelector(SELECTORS.pageDropdownToggle);
	const dropdown = fieldInput?.querySelector(SELECTORS.pageDropdown);
	const textarea = fieldInput?.querySelector(SELECTORS.consentMessageTextarea);
	const pageButtons = fieldInput?.querySelectorAll(SELECTORS.pageButton) || [];

	if (!fieldInput || !toggleButton || !dropdown || !textarea) {
		return;
	}

	const syncPageButtons = () => {
		pageButtons.forEach((button) => {
			const pageKey = button.dataset[DATA_ATTRIBUTES.pageKey] || '';
			const placeholder = pageKey ? `{${pageKey}}` : '';
			const isSelected = Boolean(placeholder && textarea.value.includes(placeholder));

			button.disabled = isSelected;
			button.classList.toggle('is-selected', isSelected);
		});
	};

	toggleButton.addEventListener('click', (event) => {
		event.stopPropagation();

		const isOpening = dropdown.hidden;
		closeAllPageDropdowns(card.closest(SELECTORS.legalConsentsContainer) || document, isOpening ? dropdown : null);
		dropdown.hidden = !isOpening;
		toggleButton.setAttribute(ARIA.expanded, String(isOpening));
	});

	pageButtons.forEach((button) => {
		button.addEventListener('click', () => {
			const pageKey = button.dataset[DATA_ATTRIBUTES.pageKey];

			if (!pageKey) {
				return;
			}

			const placeholder = `{${pageKey}}`;
			const currentValue = textarea.value.trim();

			if (!currentValue.includes(placeholder)) {
				textarea.value = currentValue ? `${currentValue} ${placeholder}` : placeholder;
				textarea.dispatchEvent(new Event('input', { bubbles: true }));
			}

			syncPageButtons();
			closePageDropdown(dropdown, toggleButton);
		});
	});

	textarea.addEventListener('input', () => {
		syncPageButtons();
		onCardChange();
	});

	syncPageButtons();
};

const buildSavePayload = ({ card, consentId, enabledInput, savedState }) => {
	const currentState = getCardFormState(card);
	const payload = new FormData();

	payload.append(FORM_FIELDS.action, AJAX_ACTIONS.legalConsents);
	payload.append(FORM_FIELDS.crudAction, consentId ? CRUD_ACTIONS.update : CRUD_ACTIONS.create);
	payload.append(FORM_FIELDS.nonce, getNonceValue());

	if (consentId) {
		payload.append(FORM_FIELDS.id, String(consentId));
	}

	const fields = {
		[FORM_FIELDS.title]: currentState.title,
		[FORM_FIELDS.message]: currentState.message,
		[FORM_FIELDS.displayOn]: currentState.displayOn.join(','),
		[FORM_FIELDS.method]: currentState.method,
		[FORM_FIELDS.consentMap]: JSON.stringify(currentState.consentMap),
		[FORM_FIELDS.isActive]: enabledInput?.checked ? '1' : '0',
	};

	if (!consentId) {
		Object.entries(fields).forEach(([key, value]) => {
			payload.append(key, value);
		});

		return payload;
	}

	const previousDisplayOn = savedState.displayOn.join(',');
	const previousConsentMap = JSON.stringify(savedState.consentMap);

	if (fields[FORM_FIELDS.title] !== savedState.title) {
		payload.append(FORM_FIELDS.title, fields[FORM_FIELDS.title]);
	}

	if (fields[FORM_FIELDS.message] !== savedState.message) {
		payload.append(FORM_FIELDS.message, fields[FORM_FIELDS.message]);
	}

	if (fields[FORM_FIELDS.displayOn] !== previousDisplayOn) {
		payload.append(FORM_FIELDS.displayOn, fields[FORM_FIELDS.displayOn]);
	}

	if (fields[FORM_FIELDS.method] !== savedState.method) {
		payload.append(FORM_FIELDS.method, fields[FORM_FIELDS.method]);
	}

	if (fields[FORM_FIELDS.consentMap] !== previousConsentMap) {
		payload.append(FORM_FIELDS.consentMap, fields[FORM_FIELDS.consentMap]);
	}

	if (fields[FORM_FIELDS.isActive] !== (savedState.enabled ? '1' : '0')) {
		payload.append(FORM_FIELDS.isActive, fields[FORM_FIELDS.isActive]);
	}

	return payload;
};

const deleteConsent = ({ card, consentId, deleteButton, onSuccess = () => { } }) => {
	const formData = new FormData();
	formData.append(FORM_FIELDS.action, AJAX_ACTIONS.legalConsents);
	formData.append(FORM_FIELDS.crudAction, CRUD_ACTIONS.delete);
	formData.append(FORM_FIELDS.nonce, getNonceValue());
	formData.append(FORM_FIELDS.id, String(consentId));

	deleteButton.classList.add(CSS_CLASSES.loading);
	deleteButton.disabled = true;

	fetch(ajaxurl, { method: 'POST', body: formData })
		.then((response) => response.json())
		.then((data) => {
			if (isSuccessfulResponse(data)) {
				card.remove();
				onSuccess();
				showToast(__('Success', 'tutor'), getResponseMessage(data, __('Legal consent deleted successfully.', 'tutor')), 'success');
				return;
			}

			showToast(__('Failed', 'tutor'), getResponseMessage(data, __('Failed to delete legal consent.', 'tutor')), 'error');
		})
		.catch(() => {
			showToast(__('Failed', 'tutor'), __('Failed to delete legal consent.', 'tutor'), 'error');
		})
		.finally(() => {
			deleteButton.classList.remove(CSS_CLASSES.loading);
			deleteButton.disabled = false;
		});
};

const updateConsentEnabledState = ({ consentId, enabledInput, enabledHiddenInput, onSuccess }) => {
	const formData = new FormData();
	formData.append(FORM_FIELDS.action, AJAX_ACTIONS.legalConsents);
	formData.append(FORM_FIELDS.crudAction, CRUD_ACTIONS.update);
	formData.append(FORM_FIELDS.nonce, getNonceValue());
	formData.append(FORM_FIELDS.id, String(consentId));
	formData.append(FORM_FIELDS.isActive, enabledInput.checked ? '1' : '0');

	enabledInput.disabled = true;

	fetch(ajaxurl, { method: 'POST', body: formData })
		.then((response) => response.json())
		.then((data) => {
			if (isSuccessfulResponse(data)) {
				onSuccess();
				showToast(__('Success', 'tutor'), getResponseMessage(data, __('Legal consent updated successfully.', 'tutor')), 'success');
				return;
			}

			enabledInput.checked = !enabledInput.checked;
			enabledHiddenInput.value = enabledInput.checked ? 'on' : 'off';
			showToast(__('Failed', 'tutor'), getResponseMessage(data, __('Failed to update legal consent.', 'tutor')), 'error');
		})
		.catch(() => {
			enabledInput.checked = !enabledInput.checked;
			enabledHiddenInput.value = enabledInput.checked ? 'on' : 'off';
			showToast(__('Failed', 'tutor'), __('Failed to update legal consent.', 'tutor'), 'error');
		})
		.finally(() => {
			enabledInput.disabled = false;
		});
};

const saveConsent = ({ card, consentId, saveButton, enabledInput, savedState, onSuccess }) => {
	const formData = buildSavePayload({ card, consentId, enabledInput, savedState });

	saveButton.classList.add(CSS_CLASSES.loading);
	saveButton.disabled = true;

	fetch(ajaxurl, { method: 'POST', body: formData })
		.then((response) => response.json())
		.then((data) => {
			if (isSuccessfulResponse(data)) {
				saveButton.classList.remove(CSS_CLASSES.loading);
				onSuccess(Number(data?.data?.id || 0));
				showToast(__('Success', 'tutor'), getResponseMessage(data, __('Legal consent saved successfully.', 'tutor')), 'success');
				return;
			}

			showToast(__('Failed', 'tutor'), getResponseMessage(data, __('Failed to save legal consent.', 'tutor')), 'error');
			saveButton.classList.remove(CSS_CLASSES.loading);
			saveButton.disabled = false;
		})
		.catch(() => {
			showToast(__('Failed', 'tutor'), __('Failed to save legal consent.', 'tutor'), 'error');
			saveButton.classList.remove(CSS_CLASSES.loading);
			saveButton.disabled = false;
		});
};

const bindCard = (card) => {
	const titleInput = card.querySelector(SELECTORS.consentTitleInput);
	const enabledInput = card.querySelector(SELECTORS.consentEnabled);
	const enabledHiddenInput = card.querySelector(SELECTORS.consentEnabledHidden);
	const toggleButton = card.querySelector(SELECTORS.consentToggleButton);
	const deleteButton = card.querySelector(SELECTORS.consentDeleteButton);
	const cancelButton = card.querySelector(SELECTORS.consentCancelButton);
	const saveButton = card.querySelector(SELECTORS.consentSaveButton);
	const container = card.closest(SELECTORS.legalConsentsContainer);

	let consentId = Number(card.dataset[DATA_ATTRIBUTES.consentId] || 0);
	let savedState = getCardFormState(card);
	syncCardSaveButton(card, savedState);

	const onCardChange = () => {
		markSettingsAsChanged();
		syncCardSaveButton(card, savedState);
	};

	titleInput?.addEventListener('input', (event) => {
		updateConsentTitle(card, event.target.value);
		onCardChange();
	});

	if (enabledInput && enabledHiddenInput) {
		enabledInput.addEventListener('change', () => {
			enabledHiddenInput.value = enabledInput.checked ? 'on' : 'off';

			if (!consentId) {
				onCardChange();
				return;
			}

			updateConsentEnabledState({
				consentId,
				enabledInput,
				enabledHiddenInput,
				onSuccess: () => {
					savedState = getCardFormState(card);
					syncCardSaveButton(card, savedState);
				},
			});
		});
	}

	card.querySelectorAll(SELECTORS.displayOnCheckboxes).forEach((checkbox) => {
		checkbox.addEventListener('change', onCardChange);
	});

	card.querySelector(SELECTORS.consentMethodSelect)?.addEventListener('change', onCardChange);

	toggleButton?.addEventListener('click', () => {
		if (toggleButton.disabled) {
			return;
		}

		toggleConsentCard(card, !card.classList.contains(CSS_CLASSES.collapsed));
	});

	deleteButton?.addEventListener('click', () => {
		const modal = document.getElementById('tutor-legal-consent-delete-modal');
		const confirmBtn = document.getElementById('tutor-legal-consent-confirm-delete');

		if (!modal || !confirmBtn) {
			if (!consentId) {
				card.remove();
				showToast(__('Success', 'tutor'), __('Legal consent removed.', 'tutor'), 'success');
				syncEmptyState(container);
				markSettingsAsChanged();
				return;
			}

			deleteConsent({
				card,
				consentId,
				deleteButton,
				onSuccess: () => syncEmptyState(container),
			});
			return;
		}

		modal.classList.add('tutor-is-active');

		const newConfirmBtn = confirmBtn.cloneNode(true);
		confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

		newConfirmBtn.addEventListener('click', () => {
			const closeBtn = modal.querySelector('[data-tutor-modal-close]');

			if (!consentId) {
				closeBtn?.click();
				card.remove();
				showToast(__('Success', 'tutor'), __('Legal consent removed.', 'tutor'), 'success');
				syncEmptyState(container);
				markSettingsAsChanged();
				return;
			}

			deleteConsent({
				card,
				consentId,
				deleteButton: newConfirmBtn,
				onSuccess: () => {
					closeBtn?.click();
					syncEmptyState(container);
				},
			});
		});
	});

	cancelButton?.addEventListener('click', () => {
		applyCardState(card, savedState);
		syncCardSaveButton(card, savedState);
	});

	saveButton?.addEventListener('click', () => {
		saveConsent({
			card,
			consentId,
			saveButton,
			enabledInput,
			savedState,
			onSuccess: (returnedId) => {
				if (!consentId && returnedId) {
					consentId = returnedId;
					card.dataset[DATA_ATTRIBUTES.consentId] = String(returnedId);
					if (toggleButton) {
						toggleButton.disabled = false;
					}
					if (enabledInput) {
						enabledInput.disabled = false;
					}
				}

				toggleConsentCard(card, true);
				savedState = getCardFormState(card);
				syncCardSaveButton(card, savedState);
			},
		});
	});

	bindPageLinkControl(card, onCardChange);
};

const appendConsentCard = (container) => {
	const template = container.querySelector(SELECTORS.consentTemplate);
	const list = container.querySelector(SELECTORS.consentList);

	if (!template || !list) {
		return;
	}

	const nextIndex = Number(container.dataset[DATA_ATTRIBUTES.nextIndex] || list.children.length || 0);
	const markup = template.innerHTML.replaceAll(TEMPLATE_INDEX_PLACEHOLDER, String(nextIndex));
	const wrapper = document.createElement('div');
	wrapper.innerHTML = markup.trim();

	const card = wrapper.firstElementChild;
	if (!card) {
		return;
	}

	list.appendChild(card);
	container.dataset[DATA_ATTRIBUTES.nextIndex] = String(nextIndex + 1);
	bindCard(card);
	updateConsentTitle(card, card.querySelector(SELECTORS.consentTitleInput)?.value || '');
	syncEmptyState(container);
	markSettingsAsChanged();
};

const initLegalConsents = () => {
	const page = getLegalConsentPage();
	if (!page) {
		return;
	}

	const container = page.querySelector(SELECTORS.legalConsentsContainer);
	if (!container) {
		return;
	}

	const cards = container.querySelectorAll(SELECTORS.consentCard);
	container.dataset[DATA_ATTRIBUTES.nextIndex] = String(cards.length);

	cards.forEach((card) => {
		bindCard(card);
		updateConsentTitle(card, card.querySelector(SELECTORS.consentTitleInput)?.value || '');
	});

	syncEmptyState(container);

	container.querySelectorAll(SELECTORS.addConsent).forEach((button) => {
		button.addEventListener('click', () => {
			appendConsentCard(container);
		});
	});

	document.addEventListener('click', (event) => {
		const target = event.target;
		if (!(target instanceof Element)) {
			return;
		}

		if (!target.closest(SELECTORS.pageDropdown) && !target.closest(SELECTORS.pageDropdownToggle)) {
			closeAllPageDropdowns(container);
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

	document.querySelectorAll(SELECTORS.tabLinks).forEach((tab) => {
		tab.addEventListener('click', () => {
			window.setTimeout(() => {
				toggleHeaderSaveVisibility();
				syncFooterSaveButtons();
			}, 0);
		});
	});

	const optionForm = document.getElementById(SELECTORS.tutorOptionForm);
	optionForm?.addEventListener('input', syncFooterSaveButtons);
	optionForm?.addEventListener('change', syncFooterSaveButtons);
	window.addEventListener(TUTOR_OPTION_SAVED_EVENT, syncFooterSaveButtons);

	const tabPagesObserver = new MutationObserver(() => {
		toggleHeaderSaveVisibility();
		syncFooterSaveButtons();
	});

	document.querySelectorAll(SELECTORS.tabPages).forEach((tabPage) => {
		tabPagesObserver.observe(tabPage, {
			attributes: true,
			attributeFilter: ['class'],
		});
	});
};

document.addEventListener('DOMContentLoaded', initLegalConsents);
