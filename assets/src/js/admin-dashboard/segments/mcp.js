/**
 * Tools → MCP — enable toggle, dirty state, abilities UI.
 */
document.addEventListener('DOMContentLoaded', () => {
	const root = document.getElementById('tutor-mcp-root');
	if (!root) {
		return;
	}

	const hasAdapter = root.getAttribute('data-has-adapter') === '1';
	const form = document.getElementById('tutor-mcp-form');
	const saveBtn = root.querySelector('[data-mcp-save]');
	const dirtyEl = root.querySelector('[data-mcp-dirty]');

	const serializeForm = (f) => {
		if (!f) {
			return '';
		}
		const fd = new FormData(f);
		const parts = [];
		fd.forEach((v, k) => {
			parts.push(`${k}=${v}`);
		});
		parts.sort();
		return parts.join('&');
	};

	const initial = serializeForm(form);

	const markDirty = () => {
		if (!form) {
			return;
		}
		const dirty = serializeForm(form) !== initial;
		if (dirtyEl) {
			dirtyEl.classList.toggle('tutor-d-none', !dirty);
		}
		if (saveBtn) {
			saveBtn.disabled = !dirty;
		}
	};

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

	root.addEventListener('change', (e) => {
		if (!e.target || !e.target.matches('input, select, textarea')) {
			return;
		}

		if (e.target.matches('[data-mcp-enable]') && e.target.checked && !hasAdapter) {
			e.target.checked = false;
			openAdapterModal();
			toggleEnabledRows();
			markDirty();
			return;
		}

		if (e.target.matches('[data-mcp-ability-toggle]')) {
			setAbilityTools(e.target.closest('.tutor-mcp-ability-row'), e.target.checked);
		}

		markDirty();
		toggleEnabledRows();
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
		markDirty();
	});

	toggleEnabledRows();
	markDirty();
});
