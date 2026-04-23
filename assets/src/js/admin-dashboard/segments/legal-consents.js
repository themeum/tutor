const legalConsentPageId = 'legal_consents';

const getLegalConsentPage = () => document.getElementById(legalConsentPageId);

const getHeaderSaveButton = () => document.getElementById('save_tutor_option');

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

const captureCardState = (card) => ({
	title: card.querySelector('[data-consent-title-input]')?.value || '',
	enabled: Boolean(card.querySelector('[data-consent-enabled]')?.checked),
	message: card.querySelector('textarea[name*="message"]')?.value || '',
	method: card.querySelector('select[name*="method"]')?.value || '',
	collapsed: card.classList.contains('is-collapsed'),
	displayOn: Array.from(card.querySelectorAll('[name*="display_on"]'))
		.filter((checkbox) => checkbox.checked)
		.map((checkbox) => checkbox.value),
});

const isSameCardState = (left, right) => {
	if (!left || !right) {
		return false;
	}

	return left.title === right.title
		&& left.enabled === right.enabled
		&& left.message === right.message
		&& left.method === right.method
		&& left.collapsed === right.collapsed
		&& left.displayOn.length === right.displayOn.length
		&& left.displayOn.every((value, index) => value === right.displayOn[index]);
};

const applyCardState = (card, state) => {
	const titleInput = card.querySelector('[data-consent-title-input]');
	const enabledInput = card.querySelector('[data-consent-enabled]');
	const enabledHiddenInput = card.querySelector('[data-consent-enabled-hidden]');
	const messageInput = card.querySelector('textarea[name*="message"]');
	const methodSelect = card.querySelector('select[name*="method"]');

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

	card.querySelectorAll('[name*="display_on"]').forEach((checkbox) => {
		checkbox.checked = state.displayOn.includes(checkbox.value);
	});
};

const markSettingsAsChanged = () => {
	const headerSaveButton = getHeaderSaveButton();

	if (!headerSaveButton) {
		return;
	}

	headerSaveButton.removeAttribute('disabled');
	syncFooterSaveButtons();
};

const syncCardSaveButton = (card, savedState) => {
	const saveButton = card.querySelector('[data-consent-save]');
	if (!saveButton || saveButton.classList.contains('is-loading')) {
		return;
	}

	saveButton.disabled = isSameCardState(captureCardState(card), savedState);
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

const closePageDropdown = (dropdown, toggleButton) => {
	if (!dropdown) {
		return;
	}

	dropdown.hidden = true;

	if (toggleButton) {
		toggleButton.setAttribute('aria-expanded', 'false');
	}
};

const closeAllPageDropdowns = (scope = document, exceptDropdown = null) => {
	scope.querySelectorAll('[data-page-dropdown]').forEach((dropdown) => {
		if (dropdown === exceptDropdown) {
			return;
		}

		closePageDropdown(dropdown, dropdown.parentElement?.querySelector('[data-page-dropdown-toggle]'));
	});
};

const bindPageLinkControl = (card) => {
	const fieldInput = card.querySelector('[data-page-dropdown-toggle]')?.closest('.tutor-option-field-input');
	const toggleButton = fieldInput?.querySelector('[data-page-dropdown-toggle]');
	const dropdown = fieldInput?.querySelector('[data-page-dropdown]');
	const textarea = fieldInput?.querySelector('textarea');
	const pageButtons = fieldInput?.querySelectorAll('[data-page-btn]') || [];

	if (!fieldInput || !toggleButton || !dropdown || !textarea) {
		return;
	}

	const syncPageButtons = () => {
		pageButtons.forEach((button) => {
			const pageKey = button.dataset.pageKey;
			const placeholder = pageKey ? `{${pageKey}}` : '';
			const isSelected = placeholder && textarea.value.includes(placeholder);

			button.disabled = Boolean(isSelected);
			button.classList.toggle('is-selected', Boolean(isSelected));
		});
	};

	toggleButton.addEventListener('click', (event) => {
		event.stopPropagation();

		const isOpening = dropdown.hidden;
		closeAllPageDropdowns(card.closest('[data-legal-consents]') || document, isOpening ? dropdown : null);
		dropdown.hidden = !isOpening;
		toggleButton.setAttribute('aria-expanded', isOpening ? 'true' : 'false');
	});

	pageButtons.forEach((button) => {
		button.addEventListener('click', () => {
			const pageKey = button.dataset.pageKey;

			if (!pageKey) {
				return;
			}

			const placeholder = `{${pageKey}}`;
			const currentValue = textarea.value.trim();

			if (!currentValue.includes(placeholder)) {
				textarea.value = currentValue ? `${currentValue} ${placeholder}` : placeholder;
			}

			syncPageButtons();
			closePageDropdown(dropdown, toggleButton);
			textarea.dispatchEvent(new Event('input', { bubbles: true }));
		});
	});

	textarea.addEventListener('input', () => {
		syncPageButtons();
	});

	syncPageButtons();
};

const bindCard = (card) => {
	const titleInput = card.querySelector('[data-consent-title-input]');
	const enabledInput = card.querySelector('[data-consent-enabled]');
	const enabledHiddenInput = card.querySelector('[data-consent-enabled-hidden]');
	const toggleButton = card.querySelector('[data-consent-toggle]');
	const deleteButton = card.querySelector('[data-consent-delete]');
	const cancelButton = card.querySelector('[data-consent-cancel]');
	const saveButton = card.querySelector('[data-consent-save]');
	let consentId = Number(card.dataset.consentId || 0);
	let savedState = captureCardState(card);
	syncCardSaveButton(card, savedState);

	if (titleInput) {
		titleInput.addEventListener('input', (event) => {
			updateConsentTitle(card, event.target.value);
			syncCardSaveButton(card, savedState);
		});
	}

	if (enabledInput && enabledHiddenInput) {
		enabledInput.addEventListener('change', () => {
			enabledHiddenInput.value = enabledInput.checked ? 'on' : 'off';
			markSettingsAsChanged();
			syncCardSaveButton(card, savedState);
		});
	}

	card.querySelectorAll('[name*="display_on"]').forEach((checkbox) => {
		checkbox.addEventListener('change', () => {
			markSettingsAsChanged();
			syncCardSaveButton(card, savedState);
		});
	});

	if (toggleButton) {
		toggleButton.addEventListener('click', () => {
			toggleConsentCard(card, !card.classList.contains('is-collapsed'));
			syncCardSaveButton(card, savedState);
		});
	}

	if (deleteButton) {
		deleteButton.addEventListener('click', () => {
			if (!consentId) {
				card.remove();
				showToast('Success', 'Legal consent removed.', 'success');
				markSettingsAsChanged();
				return;
			}

			const formData = new FormData();
			formData.append('action', 'tutor_gdpr_legal_consents');
			formData.append('crud_action', 'delete');
			formData.append('_tutor_nonce', document.querySelector('input[name="_tutor_nonce"]').value);
			formData.append('id', String(consentId));

			deleteButton.classList.add('is-loading');
			deleteButton.disabled = true;

			fetch(ajaxurl, {
				method: 'POST',
				body: formData,
			})
				.then((response) => response.json())
				.then((data) => {
					if (data.success) {
						card.remove();
						showToast('Success', getResponseMessage(data, 'Legal consent deleted successfully.'), 'success');
						return;
					}

					showToast('Failed', getResponseMessage(data, 'Failed to delete legal consent.'), 'error');
				})
				.catch(() => {
					showToast('Failed', 'Failed to delete legal consent.', 'error');
				})
				.finally(() => {
					deleteButton.classList.remove('is-loading');
					deleteButton.disabled = false;
				});
		});
	}

	if (cancelButton) {
		cancelButton.addEventListener('click', () => {
			applyCardState(card, savedState);
			syncCardSaveButton(card, savedState);
		});
	}

	card.querySelector('textarea[name*="message"]')?.addEventListener('input', () => {
		markSettingsAsChanged();
		syncCardSaveButton(card, savedState);
	});

	card.querySelector('select[name*="method"]')?.addEventListener('change', () => {
		markSettingsAsChanged();
		syncCardSaveButton(card, savedState);
	});

	if (saveButton) {
		saveButton.addEventListener('click', () => {
			const formData = new FormData();
			formData.append('action', 'tutor_gdpr_legal_consents');
			formData.append('crud_action', consentId ? 'update' : 'create');
			formData.append('_tutor_nonce', document.querySelector('input[name="_tutor_nonce"]').value);
			if (consentId) {
				formData.append('id', String(consentId));
			}
			formData.append('consent_title', card.querySelector('[data-consent-title-input]')?.value || '');
			formData.append('consent_message', card.querySelector('textarea[name*="message"]')?.value || '');

			const displayOnCheckboxes = card.querySelectorAll('[name*="display_on"]');
			const displayOnValues = Array.from(displayOnCheckboxes).filter((cb) => cb.checked).map((cb) => cb.value);
			formData.append('display_on', displayOnValues.join(','));

			const methodSelect = card.querySelector('select[name*="method"]');
			if (methodSelect) {
				formData.append('consent_method', methodSelect.value);
			}

			const messageValue = card.querySelector('textarea[name*="message"]')?.value || '';
			const usedPlaceholders = new Set(Array.from(messageValue.matchAll(/\{([a-zA-Z0-9_-]+)\}/g), (match) => match[1]));
			const selectedPages = {};

			card.querySelectorAll('[data-page-btn]').forEach((button) => {
				const pageKey = button.dataset.pageKey;
				if (!pageKey || !usedPlaceholders.has(pageKey)) {
					return;
				}

				selectedPages[pageKey] = button.value;
			});

			formData.append('consent_map', Object.keys(selectedPages).length > 0 ? JSON.stringify(selectedPages) : '{}');

			formData.append('is_active', enabledInput?.checked ? '1' : '0');

			saveButton.classList.add('is-loading');
			saveButton.disabled = true;

			fetch(ajaxurl, {
				method: 'POST',
				body: formData,
			})
				.then((res) => res.json())
				.then((data) => {
					if (isSuccessfulResponse(data)) {
						const returnedId = Number(data?.data?.id || 0);

						if (!consentId && returnedId) {
							consentId = returnedId;
							card.dataset.consentId = String(returnedId);
						}

						toggleConsentCard(card, true);
						savedState = captureCardState(card);
						syncCardSaveButton(card, savedState);
						showToast('Success', getResponseMessage(data, 'Legal consent saved successfully.'), 'success');
						return;
					}

					showToast('Failed', getResponseMessage(data, 'Failed to save legal consent.'), 'error');
				})
				.catch(() => {
					showToast('Failed', 'Failed to save legal consent.', 'error');
				})
				.finally(() => {
					saveButton.classList.remove('is-loading');
					saveButton.disabled = false;
				});
		});
	}

	bindPageLinkControl(card);
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

	document.addEventListener('click', (event) => {
		const target = event.target;
		if (!(target instanceof Element)) {
			return;
		}

		if (!target.closest('[data-page-dropdown]') && !target.closest('[data-page-dropdown-toggle]')) {
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
