/**
 * Consent logs modal functionality for students and instructors.
 *
 * @since 4.0.0
 */
const initConsentLogs = () => {
	const overlay = document.getElementById('tutor-consent-logs-modal');
	const triggers = document.querySelectorAll('[data-consent-logs-trigger]');

	if (!overlay || !triggers.length) {
		return;
	}

	const modalBody = overlay.querySelector('.tutor-consent-logs-modal-body');
	const downloadBtn = overlay.querySelector('[data-consent-logs-download]');

	if (!modalBody) {
		return;
	}

	const AJAX_URL = window.ajaxurl || window._tutorobject?.ajaxurl || '';
	const AJAX_ACTION = 'tutor_user_consents';
	const NONCE_KEY = window._tutorobject?.nonce_key || '_tutor_nonce';
	const NONCE_VALUE = window._tutorobject?.[NONCE_KEY] || '';
	const { __ } = wp.i18n;

	// Current open state for CSV export.
	let currentUserName = '';
	let currentUserJoined = '';
	let currentUserEmail = '';
	let currentUserLogin = '';
	let currentLogs = [];

	const showLoading = () => {
		modalBody.innerHTML = `<div class="tutor-d-flex tutor-align-center tutor-justify-center tutor-py-48 tutor-color-muted tutor-fs-6">${__('Loading…', 'tutor')}</div>`;
		if (downloadBtn) downloadBtn.style.display = 'none';
	};

	const showEmpty = () => {
		modalBody.innerHTML = `<div class="tutor-d-flex tutor-align-center tutor-justify-center tutor-py-48 tutor-color-muted tutor-fs-6">${__('No consent logs found.', 'tutor')}</div>`;
		if (downloadBtn) downloadBtn.style.display = 'none';
	};

	const fetchAndRender = (userId) => {
		if (!userId) {
			showEmpty();
			return;
		}

		currentLogs = [];
		showLoading();

		const body = new FormData();
		body.append('action', AJAX_ACTION);
		body.append('user_action', 'all_consents_given_by_user');
		body.append('user_id', userId);
		body.append(NONCE_KEY, NONCE_VALUE);

		fetch(AJAX_URL, { method: 'POST', body })
			.then((r) => r.json())
			.then((res) => {
				const data = res?.data;
				if (!data || (res.status_code && res.status_code !== 200)) {
					showEmpty();
					return;
				}

				// Render server-generated HTML directly.
				modalBody.innerHTML = data.html || '';

				if (data.has_logs && Array.isArray(data.logs) && data.logs.length) {
					currentLogs = data.logs;
					currentUserName = data.user?.name || '';
					currentUserLogin = data.user?.login || '';
					currentUserEmail = data.user?.email || '';
					currentUserJoined = data.user?.joined || '';

					if (downloadBtn) {
						downloadBtn.style.display = '';
					}
				} else {
					if (downloadBtn) {
						downloadBtn.style.display = 'none';
					}
				}
			})
			.catch(() => showEmpty());
	};

	const downloadCSV = () => {
		if (!currentLogs.length) return;

		const escapeCsvCell = (v) => {
			v = String(v ?? '').trim();

			const formulaChars = ['=', '+', '-', '@', '\t', '\r'];
			if (formulaChars.some((ch) => v.startsWith(ch))) {
				v = "'" + v;
			}

			return `"${v.replace(/"/g, '""')}"`;
		};

		const studentInfo = [
			['Name:', currentUserName],
			['User Name:', currentUserLogin],
			['Email:', currentUserEmail],
			['Joined At:', currentUserJoined],
			[],
		];

		const headers = ['Title', 'Date (UTC)', 'IP Address', 'Source', 'User Agent'];

		const rows = currentLogs.map((log) => [
			log.consent_title || __('Consent', 'tutor'),
			log.created_at_gmt || '',
			log.ip_address || '',
			log.source || '',
			log.user_agent || '',
		]);

		const csv = [...studentInfo, headers, ...rows]
			.map((row) => row.map(escapeCsvCell).join(','))
			.join('\n');

		const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		// Preserve Unicode letters (\p{L}), marks/vowel signs (\p{M}), and numbers (\p{N})
		// across all languages while stripping dots, slashes, and illegal filesystem characters.
		const safeName = (currentUserName || currentUserLogin || '')
			.replace(/[^\p{L}\p{M}\p{N}_\- ]/gu, '')
			.trim()
			.replace(/\s+/g, '-') || 'user';

		a.download = `consent-logs-${safeName}.csv`;
		a.click();
		URL.revokeObjectURL(url);
	};

	triggers.forEach((btn) => {
		btn.addEventListener('click', () => {
			const userId = btn.dataset.userId;
			if (!userId) {
				showEmpty();
				return;
			}
			fetchAndRender(userId);
		});
	});

	if (downloadBtn) {
		downloadBtn.addEventListener('click', downloadCSV);
	}
};

document.addEventListener('DOMContentLoaded', initConsentLogs);
