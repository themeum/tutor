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
			const row = e.target.closest('.tutor-mcp-ability-row');
			const on = e.target.checked;
			if (row) {
				row.querySelectorAll('input[name="tutor_mcp_enabled_tools[]"]').forEach((input) => {
					input.checked = on;
				});
				const tools = row.querySelector('.tutor-mcp-tools');
				if (tools) {
					tools.classList.toggle('tutor-mcp-dimmed', !on);
				}
			}
		}

		if (e.target.matches('input[name="tutor_mcp_enabled_tools[]"]')) {
			const abRow = e.target.closest('.tutor-mcp-ability-row');
			if (abRow) {
				const toolsInputs = abRow.querySelectorAll('input[name="tutor_mcp_enabled_tools[]"]');
				let any = false;
				let all = true;
				toolsInputs.forEach((input) => {
					if (input.checked) {
						any = true;
					} else {
						all = false;
					}
				});
				const master = abRow.querySelector('[data-mcp-ability-toggle]');
				if (master) {
					master.checked = any;
					master.indeterminate = any && !all;
				}
				const toolsWrap = abRow.querySelector('.tutor-mcp-tools');
				if (toolsWrap) {
					toolsWrap.classList.toggle('tutor-mcp-dimmed', !any);
				}
			}
		}

		markDirty();
		toggleEnabledRows();
	});

	root.addEventListener('click', (e) => {
		const chev = e.target.closest('[data-mcp-ab-toggle]');
		if (!chev) {
			return;
		}
		e.preventDefault();
		const row = chev.closest('.tutor-mcp-ability-row');
		if (row) {
			row.classList.toggle('is-open');
			chev.setAttribute('aria-expanded', row.classList.contains('is-open') ? 'true' : 'false');
		}
	});

	toggleEnabledRows();
	markDirty();
});
