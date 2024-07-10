/**
 * On click add filter value on the url
 * and refresh page
 *
 * Handle bulk action
 *
 * @package Filter / sorting
 * @since v2.0.0
 */
const { __, _x, _n, _nx } = wp.i18n;

document.addEventListener('DOMContentLoaded', function () {
	const commonConfirmModal = document.getElementById('tutor-common-confirmation-modal');
	const commonConfirmForm = document.getElementById('tutor-common-confirmation-form');

	const filterCourse = document.getElementById('tutor-backend-filter-course');
	if (filterCourse) {
		filterCourse.addEventListener(
			'change',
			(e) => {
				window.location = urlPrams('course-id', e.target.value);
			},
			{ once: true },
		);
	}
	const filterCategory = document.getElementById('tutor-backend-filter-category');
	if (filterCategory) {
		filterCategory.addEventListener(
			'change',
			(e) => {
				window.location = urlPrams('category', e.target.value);
			},
			{ once: true },
		);
	}
	const filterOrder = document.getElementById('tutor-backend-filter-order');
	if (filterOrder) {
		filterOrder.addEventListener(
			'change',
			(e) => {
				window.location = urlPrams('order', e.target.value);
			},
			{ once: true },
		);
	}
	const filterPaymentStatus = document.getElementById('tutor-backend-filter-payment-status');
	filterPaymentStatus?.addEventListener(
		'change',
		(e) => {
			window.location = urlPrams('payment-status', e.target.value);
		},
		{ once: true },
	);
	
	const filterCouponStatus = document.getElementById('tutor-backend-filter-coupon-status');

	filterCouponStatus?.addEventListener(
		'change', 
		(e) => {
			window.location = urlPrams('coupon-status', e.target.value);
		},
		{ once: true },
	);

	const filterSearch = document.getElementById('tutor-admin-search-filter-form');
	const search_field = document.getElementById('tutor-backend-filter-search');

	if (filterSearch) {
		// Resubmit filter on clear
		// So we can avoid wrong tab link retaining search value
		search_field.addEventListener('search', e => {
			let { value } = e.currentTarget || {};
			if (/\S+/.test(value) == false) {
				window.location = urlPrams('search', '');
			}
		});

		// Assign search value to normal form submission
		filterSearch.onsubmit = (e) => {
			e.preventDefault();
			const search = search_field.value;
			window.location = urlPrams('search', search);
		};
	}

	/**
	 * onclick apply button show checkbox select message
	 * if not selected
	 */
	const applyButton = document.getElementById('tutor-admin-bulk-action-btn');
	const modal = document.querySelector('.tutor-bulk-modal-disabled');
	if (applyButton) {
		applyButton.onclick = () => {
			const bulkIds = [];
			const bulkFields = document.querySelectorAll('.tutor-bulk-checkbox');
			for (let field of bulkFields) {
				if (field.checked) {
					bulkIds.push(field.value);
				}
			}
			if (bulkIds.length) {
				modal.setAttribute('id', 'tutor-bulk-confirm-popup');
			} else {
				tutor_toast(__('Warning', 'tutor'), __('Nothing was selected for bulk action.', 'tutor'), 'error');
				if (modal.hasAttribute('id')) {
					modal.removeAttribute('id');
				}
			}
		};
	}

	/**
	 * Onsubmit bulk form handle ajax request then reload page
	 */
	const bulkForm = document.getElementById('tutor-admin-bulk-action-form');
	if (bulkForm) {
		bulkForm.onsubmit = async (e) => {
			e.preventDefault();
			e.stopPropagation();
			const formData = new FormData(bulkForm);
			const bulkIds = [];
			const bulkFields = document.querySelectorAll('.tutor-bulk-checkbox');
			for (let field of bulkFields) {
				if (field.checked) {
					bulkIds.push(field.value);
				}
			}
			if (!bulkIds.length) {
				alert(__('Select checkbox for action', 'tutor'));
				return;
			}
			formData.set('bulk-ids', bulkIds);
			formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
			try {
				const submitButton = document.querySelector('#tutor-confirm-bulk-action[data-tutor-modal-submit]');
				submitButton.classList.add('is-loading')
				const post = await fetch(window._tutorobject.ajaxurl, {
					method: 'POST',
					body: formData,
				});
				submitButton.classList.remove('is-loading')
				if (post.ok) {
					const response = await post.json();
					if (response.success) {
						location.reload();
					} else {
						let { message = __('Something went wrong, please try again ', 'tutor') } = response.data || {};
						tutor_toast(__('Failed', 'tutor'), message, 'error');
					}
				}
			} catch (error) {
				console.log(error);
			}
		};
	}

	/**
	 * onclick bulk action button show confirm popup
	 * on click confirm button submit bulk form
	 */
	const bulkActionButton = document.getElementById('tutor-confirm-bulk-action');
	if (bulkActionButton) {
		bulkActionButton.onclick = () => {
			const input = document.createElement('input');
			input.type = 'submit';
			bulkForm.appendChild(input);
			input.click();
			input.remove();
		};
	}

	function urlPrams(type, val) {
		const url = new URL(window.location.href);
		const params = url.searchParams;
		params.set(type, val);
		params.set('paged', 1);
		return url;
	}

	/**
	 * Select all bulk checkboxes
	 *
	 * @since v2.0.0
	 */
	const selectAll = document.querySelector('#tutor-bulk-checkbox-all');
	if (selectAll) {
		selectAll.addEventListener('click', () => {
			const checkboxes = document.querySelectorAll('.tutor-bulk-checkbox');
			checkboxes.forEach((item) => {
				if (selectAll.checked) {
					item.checked = true;
				} else {
					item.checked = false;
				}
			});
		});
	}

	/**
	 * Delete course delete
	 */
	const deleteCourse = document.querySelectorAll('.tutor-admin-course-delete');
	for (let course of deleteCourse) {
		course.onclick = (e) => {
			const id = e.currentTarget.dataset.id;

			if (commonConfirmForm) {
				commonConfirmForm.elements.action.value = 'tutor_course_delete';
				commonConfirmForm.elements.id.value = id;
			}
		};
	}

	/**
	 * Handle permanent delete action
	 *
	 * @since 3.0.0
	 */
	const permanentDeleteElem = document.querySelectorAll('.tutor-delete-permanently');
	for (let deleteElem of permanentDeleteElem) {
		deleteElem.onclick = (e) => {
			const id = e.currentTarget.dataset.id;
			const action = e.currentTarget.dataset.action;

			if (commonConfirmForm) {
				commonConfirmForm.elements.action.value = action
				commonConfirmForm.elements.id.value = id;
			}
		};
	}
	/**
	 * Handle common confirmation form
	 *
	 * @since v.2.0.0
	 */
	if (commonConfirmForm) {
		commonConfirmForm.onsubmit = async (e) => {
			e.preventDefault();
			const formData = new FormData(commonConfirmForm);
			//show loading
			const submitButton = commonConfirmForm.querySelector('[data-tutor-modal-submit]');
			submitButton.classList.add('is-loading');

			const post = await ajaxHandler(formData);
			//hide modal
			if (commonConfirmModal.classList.contains('tutor-is-active')) {
				commonConfirmModal.classList.remove('tutor-is-active');
			}
			if (post.ok) {
				const response = await post.json();
				submitButton.classList.remove('is-loading');
				if (response) {
					if (typeof response === 'object' && response.success) {
						tutor_toast(__('Delete', 'tutor'), response.data, 'success');
						location.reload();
					} else if (typeof response === 'object' && response.success === false) {
						tutor_toast(__('Failed', 'tutor'), response.data, 'error');
					} else {
						tutor_toast(__('Delete', 'tutor'), __('Succefully deleted ', 'tutor'), 'success');
						location.reload();
					}
				} else {
					tutor_toast(__('Failed', 'tutor'), __('Delete failed ', 'tutor'), 'error');
				}
			}
		};
	}
	/**
	 * Handle ajax request show toast message on success | failure
	 *
	 * @param {*} formData including action and all form fields
	 */
	async function ajaxHandler(formData) {
		try {
			const post = await fetch(window._tutorobject.ajaxurl, {
				method: 'POST',
				body: formData,
			});
			return post;
		} catch (error) {
			tutor_toast(__('Operation failed', 'tutor'), error, 'error');
		}
	}
});

export default async function ajaxHandler(formData) {
	try {
		const post = await fetch(window._tutorobject.ajaxurl, {
			method: 'POST',
			body: formData,
		});
		return post;
	} catch (error) {
		tutor_toast(__('Operation failed', 'tutor'), error, 'error');
	}
}
