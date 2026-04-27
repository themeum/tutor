/**
 * Student actions consent logs modal.
 *
 * @since 4.0.0
 */

const AJAX_URL = window.ajaxurl || window._tutorobject?.ajaxurl || '';
const AJAX_ACTION = 'tutor_user_consents';
const NONCE_KEY = window._tutorobject?.nonce_key || '_tutor_nonce';
const NONCE_VALUE = window._tutorobject?.[NONCE_KEY] || '';


const overlay = document.getElementById('tutor-consent-logs-modal');
const modalBody = overlay?.querySelector('.tutor-consent-logs-modal-body');
const downloadBtn = overlay?.querySelector('[data-consent-logs-download]');
const userNameEl = overlay?.querySelector('.tutor-consent-user-card-name');
const userJoinedEl = overlay?.querySelector('.tutor-consent-user-card-joined');
const userAvatarEl = overlay?.querySelector('.tutor-consent-user-card img');

// Current open state.
let currentUserId = 0;
let currentUserName = '';
let currentUserJoined = '';
let currentUserEmail = '';
let currentUserLogin = '';
let currentLogs = [];

// ── Helpers ─────────────────────────────────────────────────────────────

const { __ } = wp.i18n;



/**
 * Build a human-readable title from the consent_title or accepted flag.
 *
 * @param {Object} log
 * @returns {string}
 */
const getLogTitle = (log) => {
	if (log.consent_title) {
		return `${log.accepted == 1 ? __('Accepted', 'tutor') : __('Declined', 'tutor')} ${log.consent_title}`;
	}

	return log.accepted == 1 ? __('Accepted Consent', 'tutor') : __('Declined Consent', 'tutor');
};

const renderTimeline = (logs) => {
	const items = logs.map((log, index) => {
		const title = getLogTitle(log);
		const date = log.created_at_utc || '';
		const ago = log.timeAgo || log.time_ago || '';
		const ip = log.ip_address ? `IP: ${log.ip_address}` : '';
		const source = log.source ? `Source: ${log.source}` : '';
		const agent = log.user_agent ? `Agent: ${log.user_agent}` : '';
		const metaLines = [ip, source, agent].filter(Boolean);

		return `
				<div class="tutor-consent-timeline-item">
					<div class="tutor-consent-timeline-track">
						<span class="tutor-consent-timeline-dot"></span>
					</div>
					<div class="tutor-consent-timeline-content">
						<p class="tutor-consent-timeline-title">${title}</p>
						<p class="tutor-consent-timeline-date">${date}</p>
						${metaLines.length ? `<div class="tutor-consent-timeline-meta">${metaLines.join('<br>')}</div>` : ''}
					</div>
					<div class="tutor-consent-timeline-ago">${ago}</div>
				</div>
			`;
	});

	return items.join('');
};

const showLoading = () => {
	if (!modalBody) return;
	modalBody.innerHTML = `<div class="tutor-d-flex tutor-align-center tutor-justify-center tutor-py-48 tutor-color-muted tutor-fs-6">${__('Loading…', 'tutor')}</div>`;
};

const showEmpty = () => {
	if (!modalBody) return;
	modalBody.innerHTML = `<div class="tutor-d-flex tutor-align-center tutor-justify-center tutor-py-48 tutor-color-muted tutor-fs-6">${__('No consent logs found.', 'tutor')}</div>`;
};

const fetchAndRender = (userId, userName, userJoined, avatarSrc, userEmail, userLogin) => {
	if (!overlay || !modalBody) return;

	currentUserId = userId;
	currentUserName = userName;
	currentUserJoined = userJoined;
	currentUserEmail = userEmail;
	currentUserLogin = userLogin;

	// Fill user card.
	if (userNameEl) userNameEl.textContent = userName;
	if (userJoinedEl) userJoinedEl.textContent = userJoined ? `${__('Joined', 'tutor')} ${userJoined}` : '';
	if (userAvatarEl && avatarSrc) userAvatarEl.src = avatarSrc;

	// showLoading();

	const body = new FormData();
	body.append('action', AJAX_ACTION);
	body.append('user_action', 'all_consents_given_by_user');
	body.append('user_id', userId);
	body.append(NONCE_KEY, NONCE_VALUE);

	fetch(AJAX_URL, { method: 'POST', body })
		.then((r) => r.json())
		.then((data) => {
			const logs = data.data;
			currentLogs = logs;

			if (!logs.length) {
				showEmpty();
				return;
			}

			const userCard = `
					<div class="tutor-consent-user-card">
						${avatarSrc ? `<img src="${avatarSrc}" alt="${userName}" />` : ''}
						<div class="tutor-consent-user-card-info">
							<span class="tutor-consent-user-card-name">${userName}</span>
							<span class="tutor-consent-user-card-joined">${userJoined ? `${__('Joined', 'tutor')} ${userJoined}` : ''}</span>
						</div>
					</div>
				`;

			modalBody.innerHTML = `
					<div class="tutor-consent-timeline">${renderTimeline(logs)}</div>
					${userCard}
				`;
		})
		.catch(() => showEmpty());
};

const downloadCSV = () => {
	if (!currentLogs.length) return;

	const studentInfo = [
		['Name:', currentUserName],
		['User Name:', currentUserLogin],
		['Email:', currentUserEmail],
		['Joined At:', currentUserJoined],
		[],
	];

	const headers = ['Title', 'Date (UTC)', 'IP Address', 'Source', 'User Agent', 'Accepted'];

	const rows = currentLogs.map((log) => [
		getLogTitle(log),
		log.created_at_utc || '',
		log.ip_address || '',
		log.source || '',
		log.user_agent || '',
		log.accepted == 1 ? 'Yes' : 'No',
	]);

	const escape = (v) => `"${String(v).replace(/"/g, '""')}"`;

	const csv = [...studentInfo, headers, ...rows]
		.map((row) => row.map(escape).join(','))
		.join('\n');

	const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
	const url = URL.createObjectURL(blob);
	const a = document.createElement('a');
	a.href = url;
	a.download = `consent-logs-${currentUserId}.csv`;
	a.click();
	URL.revokeObjectURL(url);
};


const initConsentLogTriggers = () => {
	document.querySelectorAll('[data-consent-logs-trigger]').forEach((btn) => {
		btn.addEventListener('click', () => {
			const userId = btn.dataset.userId || '';
			const userName = btn.dataset.userName || '';
			const userJoined = btn.dataset.userJoined || '';
			const avatarSrc = btn.dataset.avatarSrc || '';
			const userEmail = btn.dataset.userEmail || '';
			const userLogin = btn.dataset.userLogin || '';

			fetchAndRender(userId, userName, userJoined, avatarSrc, userEmail, userLogin);
		});
	});
};


if (downloadBtn) downloadBtn.addEventListener('click', downloadCSV);


document.addEventListener('DOMContentLoaded', initConsentLogTriggers);

