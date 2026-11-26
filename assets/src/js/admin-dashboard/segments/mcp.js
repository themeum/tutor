/**
 * Tools → MCP — enable toggle, AJAX auto-save, abilities UI.
 */
import ajaxHandler from '../../helper/ajax-handler';

document.addEventListener('DOMContentLoaded', () => {
	const { __ } = wp.i18n;
	const root = document.getElementById('tutor-mcp-root');
	if (!root) {
		return;
	}

	const hasAdapter = root.getAttribute('data-has-adapter') === '1';
	const form = document.getElementById('tutor-mcp-form');
	let saving = false;
	let pendingSave = false;

	const openAdapterModal = () => {
		const modal = document.getElementById('tutor-mcp-adapter-modal');
		if (!modal) {
			return;
		}
		modal.classList.add('tutor-is-active');
		modal.setAttribute('aria-hidden', 'false');
	};

	const toggleEnabledRows = () => {
		const enabledInput = root.querySelector('[data-mcp-enable]');
		const on = enabledInput ? enabledInput.checked : false;
		root.querySelectorAll('[data-mcp-when-enabled]').forEach((el) => {
			el.classList.toggle('tutor-d-none', !on);
		});
	};

	const setAbilityTools = (row, on) => {
		if (!row) {
			return;
		}
		row.querySelectorAll('input[name="tutor_mcp_enabled_tools[]"]').forEach((input) => {
			input.checked = on;
		});
	};

	const updateAbilityCounts = () => {
		let totalOn = 0;
		let total = 0;

		root.querySelectorAll('.tutor-mcp-abilities').forEach((group) => {
			const rows = group.querySelectorAll('.tutor-mcp-ability-row');
			let groupOn = 0;
			rows.forEach((row) => {
				total += 1;
				const toggle = row.querySelector('[data-mcp-ability-toggle]');
				if (toggle?.checked) {
					groupOn += 1;
					totalOn += 1;
				}
			});

			const countEl = group.querySelector('[data-mcp-group-count]');
			if (countEl) {
				countEl.textContent = `(${groupOn}/${rows.length})`;
			}
		});

		const summaryEl = root.querySelector('[data-mcp-abilities-summary]');
		if (summaryEl && total > 0) {
			summaryEl.textContent = __('Abilities (%1$d/%2$d)', 'tutor')
				.replace('%1$d', String(totalOn))
				.replace('%2$d', String(total));
		}
	};

	const saveSettings = async () => {
		if (!form) {
			return;
		}

		if (saving) {
			pendingSave = true;
			return;
		}

		saving = true;
		pendingSave = false;

		const formData = new FormData(form);
		formData.set('action', 'tutor_mcp_save_settings');
		formData.set(window._tutorobject.nonce_key, window._tutorobject[window._tutorobject.nonce_key]);

		try {
			const post = await ajaxHandler(formData);
			const res = await post.json();

			if (Number(res?.status_code) === 200) {
				updateAbilityCounts();
				tutor_toast(__('Success', 'tutor'), res.message || __('MCP settings saved.', 'tutor'), 'success');
			} else {
				tutor_toast(__('Failed', 'tutor'), res?.message || __('Something went wrong!', 'tutor'), 'error');
			}
		} catch (error) {
			tutor_toast(__('Failed', 'tutor'), __('Something went wrong!', 'tutor'), 'error');
		} finally {
			saving = false;
			if (pendingSave) {
				saveSettings();
			}
		}
	};

	form?.addEventListener('submit', (e) => {
		e.preventDefault();
	});

	root.addEventListener('change', (e) => {
		if (!e.target || !e.target.matches('input, select, textarea')) {
			return;
		}

		if (e.target.matches('[data-mcp-enable]') && e.target.checked && !hasAdapter) {
			e.target.checked = false;
			openAdapterModal();
			toggleEnabledRows();
			return;
		}

		if (e.target.matches('[data-mcp-ability-toggle]')) {
			setAbilityTools(e.target.closest('.tutor-mcp-ability-row'), e.target.checked);
			updateAbilityCounts();
		}

		toggleEnabledRows();
		saveSettings();
	});

	root.addEventListener('click', (e) => {
		const resetBtn = e.target.closest('[data-mcp-reset-default]');
		if (!resetBtn) {
			return;
		}
		e.preventDefault();
		root.querySelectorAll('[data-mcp-ability-toggle]').forEach((toggle) => {
			toggle.checked = true;
			setAbilityTools(toggle.closest('.tutor-mcp-ability-row'), true);
		});
		updateAbilityCounts();
		saveSettings();
	});

	toggleEnabledRows();
});
