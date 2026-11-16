/**
 * Tools → MCP — section-specific interactions.
 * Modals / clipboard use Tutor core (`data-tutor-modal-*`, `data-tutor-clipboard-copy-target`).
 * Site-specific Connect values come from `#tutor-mcp-root[data-mcp-client]` (admin page only).
 */
document.addEventListener('DOMContentLoaded', () => {
	const { __, sprintf } = wp.i18n;
	const root = document.getElementById('tutor-mcp-root');
	if (!root) {
		return;
	}

	const parseClientConfig = () => {
		try {
			const raw = root.getAttribute('data-mcp-client');
			if (!raw) {
				return {};
			}
			const parsed = JSON.parse(raw);
			return parsed && typeof parsed === 'object' ? parsed : {};
		} catch (e) {
			return {};
		}
	};

	const site = parseClientConfig();
	const endpoint = typeof site.endpoint === 'string' ? site.endpoint : '';
	const abspath = typeof site.abspath === 'string' ? site.abspath : '';
	const agent = typeof site.agentUser === 'string' && site.agentUser ? site.agentUser : 'tutor-mcp-agent';
	const serverId = typeof site.serverId === 'string' && site.serverId ? site.serverId : 'tutor-mcp';
	const hasAdapter = Boolean(site.hasAdapter);

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
		root.querySelectorAll('[data-mcp-conn-live]').forEach((el) => {
			el.classList.toggle('tutor-d-none', !on);
		});
		root.querySelectorAll('[data-mcp-conn-locked]').forEach((el) => {
			el.classList.toggle('tutor-d-none', on);
		});
	};

	const pretty = (obj) => JSON.stringify(obj, null, 2);

	const esc = (s) =>
		String(s == null ? '' : s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');

	const buildStdioConfig = () => ({
		mcpServers: {
			'tutor-mcp': {
				command: 'wp',
				args: [
					`--path=${abspath}`,
					'mcp-adapter',
					'serve',
					`--server=${serverId}`,
					`--user=${agent}`,
				],
			},
		},
	});

	const buildHttpConfig = () => ({
		mcpServers: {
			'tutor-mcp-http': {
				command: 'npx',
				args: ['-y', '@automattic/mcp-wordpress-remote@latest'],
				env: {
					WP_API_URL: endpoint,
					WP_API_USERNAME: agent,
					WP_API_PASSWORD: '${TUTOR_MCP_APP_PASSWORD}',
				},
			},
		},
	});

	const buildConfigs = (clientId) => {
		const stdio = buildStdioConfig();
		const http = buildHttpConfig();
		const cursorStdio = pretty(stdio);
		const httpJson = pretty(http);
		const vscodeServers = pretty({
			servers: {
				'tutor-mcp': {
					type: 'stdio',
					command: 'wp',
					args: [
						`--path=${abspath}`,
						'mcp-adapter',
						'serve',
						`--server=${serverId}`,
						`--user=${agent}`,
					],
				},
			},
		});
		const vscodeHttp = pretty({
			servers: {
				'tutor-mcp-http': {
					type: 'stdio',
					command: 'npx',
					args: ['-y', '@automattic/mcp-wordpress-remote@latest'],
					env: http.mcpServers['tutor-mcp-http'].env,
				},
			},
		});
		const geminiCmd =
			'gemini mcp add --transport http \\\n  --header "Authorization: Basic <base64(username:application-password)>" \\\n  tutor-lms ' +
			endpoint;
		const raw =
			'Server URL: ' +
			endpoint +
			'\nTransport: Streamable HTTP / STDIO\nUsername: ' +
			agent +
			'\nApplication password: (create in Users → Profile)';

		const clients = {
			cursor: {
				name: 'Cursor',
				tabs: [
					{ id: 'stdio', label: 'Local (STDIO)', file: '.cursor/mcp.json', code: cursorStdio },
					{ id: 'http', label: 'Remote (HTTP)', file: '.cursor/mcp.json', code: httpJson },
				],
				steps: [
					'Open Cursor Settings → MCP, or edit <code>.cursor/mcp.json</code>.',
					'Paste the JSON for local STDIO or remote HTTP.',
					'For HTTP, create an Application Password for <code>' +
						esc(agent) +
						'</code> under Users → Profile.',
				],
			},
			'claude-desktop': {
				name: 'Claude Desktop',
				tabs: [
					{ id: 'stdio', label: 'Local (STDIO)', file: 'claude_desktop_config.json', code: cursorStdio },
					{ id: 'http', label: 'Remote (HTTP)', file: 'claude_desktop_config.json', code: httpJson },
				],
				steps: [
					'Open Claude Desktop → Settings → Developer → Edit Config.',
					'Paste the JSON below under <code>mcpServers</code>.',
					'Restart Claude Desktop.',
				],
			},
			vscode: {
				name: 'VS Code',
				tabs: [
					{ id: 'stdio', label: 'Local (STDIO)', file: '.vscode/mcp.json', code: vscodeServers },
					{ id: 'http', label: 'Remote (HTTP)', file: '.vscode/mcp.json', code: vscodeHttp },
				],
				steps: [
					'Add a workspace <code>.vscode/mcp.json</code> file.',
					'Paste the JSON below (VS Code uses a <code>servers</code> key).',
					'Reload the window and open the MCP view.',
				],
			},
			'gemini-cli': {
				name: 'Gemini CLI',
				tabs: [{ id: 'cli', label: 'CLI', file: 'Terminal', code: geminiCmd }],
				steps: [
					'Open a terminal.',
					'Run the command below (replace the Application Password header).',
					'Start <code>gemini</code> and type <code>/mcp</code>.',
				],
			},
			other: {
				name: 'MCP client',
				tabs: [
					{ id: 'stdio', label: 'Local (STDIO)', file: 'mcp.json', code: cursorStdio },
					{ id: 'http', label: 'Remote (HTTP)', file: 'mcp.json', code: httpJson },
					{ id: 'raw', label: 'Endpoint', file: 'Values', code: raw },
				],
				steps: [
					'Prefer STDIO for local sites and HTTP + Application Password for remote sites.',
					'Use Streamable HTTP against the server URL when your client supports it.',
					'Never commit Application Passwords to git.',
				],
			},
		};

		return clients[clientId] || clients.other;
	};

	const showClientPicker = () => {
		const picker = document.getElementById('tutor-mcp-picker');
		const setup = document.getElementById('tutor-mcp-setup');
		const foot = document.getElementById('tutor-mcp-dlg-foot');
		const title = document.getElementById('tutor-mcp-connect-title');
		const sub = document.getElementById('tutor-mcp-dlg-sub');
		if (picker) {
			picker.classList.remove('tutor-d-none');
		}
		if (setup) {
			setup.classList.add('tutor-d-none');
			setup.innerHTML = '';
		}
		if (foot) {
			foot.classList.add('tutor-d-none');
		}
		if (title) {
			title.textContent = __('Connect an AI app', 'tutor');
		}
		if (sub) {
			sub.textContent = '';
			sub.classList.add('tutor-d-none');
		}
	};

	const showClientSetup = (clientId) => {
		const client = buildConfigs(clientId);
		const picker = document.getElementById('tutor-mcp-picker');
		const setup = document.getElementById('tutor-mcp-setup');
		const foot = document.getElementById('tutor-mcp-dlg-foot');
		const title = document.getElementById('tutor-mcp-connect-title');
		const sub = document.getElementById('tutor-mcp-dlg-sub');

		if (picker) {
			picker.classList.add('tutor-d-none');
		}
		if (setup) {
			setup.classList.remove('tutor-d-none');
		}
		if (foot) {
			foot.classList.remove('tutor-d-none');
		}
		if (title) {
			title.textContent = sprintf(
				/* translators: %s: client app name */
				__('Install in %s', 'tutor'),
				client.name
			);
		}
		if (sub) {
			sub.textContent = __(
				'Use STDIO locally or HTTP with an Application Password remotely.',
				'tutor'
			);
			sub.classList.remove('tutor-d-none');
		}

		const tabButtons = client.tabs
			.map((t, i) => {
				return (
					'<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-sm' +
					(i === 0 ? ' is-active' : '') +
					'" data-mcp-tab="' +
					esc(t.id) +
					'">' +
					esc(t.label) +
					'</button>'
				);
			})
			.join('');

		const panels = client.tabs
			.map((t, i) => {
				const tid = 'mcp-payload-' + t.id;
				return (
					'<div class="tutor-mcp-tab-panel' +
					(i === 0 ? '' : ' tutor-d-none') +
					'" data-mcp-tab-panel="' +
					esc(t.id) +
					'">' +
					'<div class="tutor-fs-7 tutor-color-muted tutor-mb-8">' +
					esc(t.file) +
					'</div>' +
					'<div class="tutor-mcp-code-box tutor-mb-12">' +
					'<textarea id="' +
					esc(tid) +
					'" class="tutor-form-control" readonly rows="10">' +
					esc(t.code) +
					'</textarea>' +
					'</div>' +
					'<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" data-tutor-clipboard-copy-target="' +
					esc(tid) +
					'">' +
					'<span class="tutor-icon-copy tutor-mr-8" aria-hidden="true"></span>' +
					esc(__('Copy', 'tutor')) +
					'</button></div>'
				);
			})
			.join('');

		const steps = client.steps
			.map((s) => {
				return '<li>' + s + '</li>';
			})
			.join('');

		setup.innerHTML =
			'<p class="tutor-fs-7 tutor-color-muted tutor-mb-12">' +
			esc(__('Add this to your app’s MCP server settings.', 'tutor')) +
			'</p>' +
			(client.tabs.length > 1 ? '<div class="tutor-mcp-tabs">' + tabButtons + '</div>' : '') +
			panels +
			'<p class="tutor-fs-7 tutor-color-warning tutor-mt-16">' +
			esc(
				__(
					'For HTTP, create an Application Password for the agent user in Users → Profile. Never commit it.',
					'tutor'
				)
			) +
			'</p>' +
			'<details class="tutor-mt-16"><summary class="tutor-fs-6 tutor-fw-medium tutor-color-primary" style="cursor:pointer">' +
			esc(__('Need help? Step-by-step guide', 'tutor')) +
			'</summary><ol class="tutor-fs-7 tutor-color-muted tutor-mt-12" style="padding-left:20px">' +
			steps +
			'</ol></details>';

		setup.querySelectorAll('[data-mcp-tab]').forEach((btn) => {
			btn.addEventListener('click', () => {
				const id = btn.getAttribute('data-mcp-tab');
				setup.querySelectorAll('[data-mcp-tab]').forEach((b) => {
					b.classList.toggle('is-active', b === btn);
				});
				setup.querySelectorAll('[data-mcp-tab-panel]').forEach((p) => {
					p.classList.toggle('tutor-d-none', p.getAttribute('data-mcp-tab-panel') !== id);
				});
			});
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
		if (chev) {
			e.preventDefault();
			const row = chev.closest('.tutor-mcp-ability-row');
			if (row) {
				row.classList.toggle('is-open');
				chev.setAttribute('aria-expanded', row.classList.contains('is-open') ? 'true' : 'false');
			}
			return;
		}

		const pick = e.target.closest('[data-mcp-pick]');
		if (pick) {
			e.preventDefault();
			showClientSetup(pick.getAttribute('data-mcp-pick'));
			return;
		}

		if (e.target.closest('[data-mcp-w-back]')) {
			e.preventDefault();
			showClientPicker();
		}
	});

	root.querySelectorAll('[data-tutor-modal-target="tutor-mcp-connect-modal"]').forEach((btn) => {
		btn.addEventListener('click', () => {
			showClientPicker();
		});
	});

	toggleEnabledRows();
	markDirty();
});
