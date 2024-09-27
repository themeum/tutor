import ajaxHandler from '../../admin-dashboard/segments/filter';
jQuery(document).ready(function ($) {
	$('.tutor-sortable-list').sortable();
});

document.addEventListener('DOMContentLoaded', (event) => {
	const { __, _x, _n, _nx } = wp.i18n;
	const sidebar = document.querySelector('.tutor-lesson-sidebar.tutor-desktop-sidebar');
	const sidebarToggle = document.querySelector('.tutor-sidebar-toggle-anchor');
	if (sidebar && sidebarToggle) {
		sidebarToggle.addEventListener('click', () => {
			if (getComputedStyle(sidebar).flex === '0 0 400px') {
				sidebar.style.flex = '0 0 0px';
				sidebar.style.display = 'none';
			} else {
				sidebar.style.display = 'block';
				sidebar.style.flex = '0 0 400px';
			}
		});
	}

	/**
	 * dynamically calcutate sidebarContent height from top
	 * and decrease sidebar height
	 */
	const sidebarTabContent = document.querySelector('.tutor-sidebar-tabs-content');
	if (sidebarTabContent) {
		let sidebarTabContentBoundingTop = sidebarTabContent.getBoundingClientRect().top;
		sidebarTabContent.style.height = `calc(100vh - ${sidebarTabContentBoundingTop}px)`;
	}

	const sidebarTabeHandler = function (sideBarTabs) {
		const tabWrapper = document.querySelector('.tutor-desktop-sidebar-area');

		if (null !== tabWrapper && tabWrapper.children.length < 2) {
			return;
		}

		sideBarTabs.forEach((tab) => {
			tab.addEventListener('click', (event) => {
				const tabConent = event.currentTarget.parentNode.nextElementSibling;
				clearActiveClass(tabConent);
				event.currentTarget.classList.add('active');
				let id = event.currentTarget.getAttribute('data-sidebar-tab');
				const activeQnaTabContent = tabConent.querySelector('#' + id);
				activeQnaTabContent.classList.add('active');

				/**
				 * dynamically calcutate qnatabcontentrarea height from top
				 * and decrease it's height from 100vh
				 */
				const sidebarTabArea = document.querySelector('.tutor-lessons-tab-area');
				let sidebarTabAreaHeight = sidebarTabArea.offsetHeight;
				if (id == 'sidebar-qna-tab-content') {
					activeQnaTabContent.style.height = `calc(100% - ${sidebarTabAreaHeight}px)`;
				}
			});
		});
		const clearActiveClass = function (tabConent) {
			for (let i = 0; i < sideBarTabs.length; i++) {
				sideBarTabs[i].classList.remove('active');
			}
			let sidebarTabItems = tabConent.querySelectorAll('.tutor-lesson-sidebar-tab-item');
			for (let i = 0; i < sidebarTabItems.length; i++) {
				sidebarTabItems[i].classList.remove('active');
			}
		};
	};
	const desktopSidebar = document.querySelectorAll('.tutor-desktop-sidebar-area .tutor-sidebar-tab-item');
	const mobileSidebar = document.querySelectorAll('.tutor-mobile-sidebar-area .tutor-sidebar-tab-item');
	if (desktopSidebar) {
		sidebarTabeHandler(desktopSidebar);
	}
	if (mobileSidebar) {
		sidebarTabeHandler(mobileSidebar);
	}
	/* end of sidetab tab */

	/* comment text-area focus arrow style */
	const commentTextarea = document.querySelectorAll('.tutor-comment-textarea textarea');
	if (commentTextarea) {
		commentTextarea.forEach((item) => {
			item.addEventListener('focus', () => {
				item.parentElement.classList.add('is-focused');
			});
			item.addEventListener('blur', () => {
				item.parentElement.classList.remove('is-focused');
			});
		});
	}

	/* comment text-area focus arrow style */
	function commentSideLine() {
		const parentComments = document.querySelectorAll('.tutor-comments-list.tutor-parent-comment');
		const replyComment = document.querySelector('.tutor-comment-box.tutor-reply-box');
		if (parentComments) {
			[...parentComments].forEach((parentComment) => {
				const childComments = parentComment.querySelectorAll('.tutor-comments-list.tutor-child-comment');
				const commentLine = parentComment.querySelector('.tutor-comment-line');
				const childCommentCount = childComments.length;
				if (childComments[childCommentCount - 1]) {
					const lastCommentHeight = childComments[childCommentCount - 1].clientHeight;

					let heightOfLine = lastCommentHeight + replyComment.clientHeight + 20 - 25 + 50;
					commentLine.style.setProperty('height', `calc(100% - ${heightOfLine}px)`);
				}
			});
		}
	}
	commentSideLine();
	window.addEventListener(_tutorobject.content_change_event, commentSideLine);
	/* commenting */

	// quiz drag n drop functionality
	const tutorDraggables = document.querySelectorAll('.tutor-draggable > div');
	const tutorDropzone = document.querySelectorAll('.tutor-dropzone');
	tutorDraggables.forEach((quizBox) => {
		quizBox.addEventListener('dragstart', dragStart);
		quizBox.addEventListener('dragend', dragEnd);
	});
	tutorDraggables.forEach((quizBox) => {
		['touchstart', 'touchmove', 'touchend'].forEach(function (e) {
			quizBox.addEventListener(e, touchHandler);
		});
	});
	tutorDropzone.forEach((quizImageBox) => {
		quizImageBox.addEventListener('dragover', dragOver);
		quizImageBox.addEventListener('dragenter', dragEnter);
		quizImageBox.addEventListener('dragleave', dragLeave);
		quizImageBox.addEventListener('drop', dragDrop);
	});

	let isScrolling = false;
	let scrollDirection = 0;

	function touchHandler(e) {
		e.preventDefault();
		const { type } = e;

		if (type === 'touchstart') {
			this.classList.add('tutor-dragging');
			startScrollLoop();
		} else if (type === 'touchmove') {
			const element = e.target.closest('.tutor-dragging');
			let copiedDragElement = document.querySelector('.tutor-drag-copy');
			if (element) {
				const mainElementBoundingRect = element.getBoundingClientRect();
				const clientY = e.touches[0].clientY;
				const clientX = e.touches[0].clientX;

				const scrollThreshold = 50;
				const maxScrollSpeed = 30;

				const viewportHeight = window.innerHeight;
				const distanceFromBottom = viewportHeight - clientY;
				const distanceFromTop = clientY;

				scrollDirection = 0;
				
				if (distanceFromBottom < scrollThreshold) {
					scrollDirection = calculateScrollSpeed(scrollThreshold, distanceFromBottom, maxScrollSpeed);
				} else if (distanceFromTop < scrollThreshold) {
					scrollDirection = - calculateScrollSpeed(scrollThreshold, distanceFromTop, maxScrollSpeed)
				}

				if (!copiedDragElement) {
					copiedDragElement = element.cloneNode(true);
					copiedDragElement.classList.add('tutor-drag-copy');
					element.parentNode.appendChild(copiedDragElement);
				}
				copiedDragElement.style.position = 'fixed';
				copiedDragElement.style.left = clientX - copiedDragElement.clientWidth / 2 + 'px';
				copiedDragElement.style.top = clientY - copiedDragElement.clientHeight / 2 + 'px';
				copiedDragElement.style.zIndex = '9999';
				copiedDragElement.style.opacity = '0.5';
				copiedDragElement.style.width = mainElementBoundingRect.width + 'px';
				copiedDragElement.style.height = mainElementBoundingRect.height + 'px';
			}
		} else if (type === 'touchend') {
			const copiedDragElement = document.querySelector('.tutor-drag-copy');
			if (copiedDragElement) {
				copiedDragElement.remove();
				const evt = typeof e.originalEvent === 'undefined' ? e : e.originalEvent;
				const touch = evt.touches[0] || evt.changedTouches[0];
				let [x, y] = [touch.clientX, touch.clientY];
				let dropZone = document.elementFromPoint(x, y);
				if (dropZone.classList.contains('tutor-dropzone') || dropZone.closest('.tutor-dropzone')) {
					if (!dropZone.classList.contains('tutor-dropzone')) {
						dropZone = dropZone.closest('.tutor-dropzone');
					}
					const input = copiedDragElement.querySelector('input');
					const inputName = input.dataset.name;
					const newInput = document.createElement('input');
					newInput.type = 'text';
					newInput.setAttribute('value', input.value);
					newInput.setAttribute('name', inputName);
					dropZone.appendChild(newInput);
					const copyContent = copiedDragElement.querySelector('.tutor-dragging-text-conent').textContent;
					dropZone.querySelector('.tutor-dragging-text-conent').textContent = copyContent;
					this.classList.remove('tutor-dragging');
				}
			}
			stopScrollLoop();
		}
	}
	function startScrollLoop() {
		if (!isScrolling) {
			isScrolling = true;
			scrollPage();
		}
	}
	function stopScrollLoop() {
		isScrolling = false;
	}
	function scrollPage() {
		if (isScrolling) {
			if (scrollDirection !== 0) {
				window.scrollBy(0, scrollDirection);
			}
			requestAnimationFrame(scrollPage);
		}
	}
	function calculateScrollSpeed(threshold, distance, maxSpeed) {
		return (threshold - distance) / threshold * maxSpeed;
	}
	function dragStart() {
		this.classList.add('tutor-dragging');
	}
	function dragEnd() {
		this.classList.remove('tutor-dragging');
	}
	function dragOver(event) {
		this.classList.add('tutor-drop-over');
		event.preventDefault();
	}
	function dragEnter() { }
	function dragLeave() {
		this.classList.remove('tutor-drop-over');
	}
	function dragDrop() {
		const copyElement = document.querySelector('.tutor-quiz-border-box.tutor-dragging');
		if (this.querySelector('input')) {
			this.querySelector('input').remove();
		}
		const input = copyElement.querySelector('input');
		const inputName = input.dataset.name;
		const newInput = document.createElement('input');
		newInput.type = 'text';
		newInput.setAttribute('value', input.value);
		newInput.setAttribute('name', inputName);
		this.appendChild(newInput);
		const copyContent = copyElement.querySelector('.tutor-dragging-text-conent').textContent;
		this.querySelector('.tutor-dragging-text-conent').textContent = copyContent;
		this.classList.remove('tutor-drop-over');
	}

	// tutor assignment file upload
	const fileUploadField = document.getElementById('tutor-assignment-file-upload');

	if (fileUploadField) {
		fileUploadField.addEventListener('change', tutorAssignmentFileHandler);
	}
	function tutorAssignmentFileHandler() {

		const uploadedFileSize = [...fileUploadField.files].reduce((sum, file) => sum + file.size, 0); // byte
		const uploadSizeLimit =
			parseInt(document.querySelector('input[name="tutor_assignment_upload_limit"]')?.value) || 0;
		let message = '';
		const maxAllowedFiles = window._tutorobject.assignment_max_file_allowed;
		let alreadyUploaded = document.querySelectorAll(
			'#tutor-student-assignment-edit-file-preview .tutor-instructor-card'
		).length;
		const allowedToUpload = maxAllowedFiles - alreadyUploaded;
		if (fileUploadField.files.length > allowedToUpload) {
			fileUploadField.value = null;
			tutor_toast(__('Warning', 'tutor'), __(`Max ${maxAllowedFiles} file allowed to upload`, 'tutor'), 'error');
			return;

		}
		if (uploadedFileSize > uploadSizeLimit) {
			fileUploadField.value = null;
			tutor_toast(
				__('Warning', 'tutor'),
				__(`File size exceeds maximum limit ${Math.floor(uploadSizeLimit / 1000000)} MB.`, 'tutor'),
				'error'
			);
			return;
		}

		if ('files' in fileUploadField) {
			if (fileUploadField && fileUploadField.files.length == 0) {
				message = 'Select one or more files.';
			} else {
				if (fileUploadField.files.length > allowedToUpload) {
					tutor_toast(
						__('Warning', 'tutor'),
						__(`Max ${maxAllowedFiles} file allowed to upload`, 'tutor'),
						'error'
					);
				}
				let fileCard = '';
				const assignmentFilePreview = document.querySelector('.tutor-asisgnment-upload-file-preview');
				const assignmentEditFilePreview = document.getElementById('tutor-student-assignment-edit-file-preview');

				for (let i = 0; i < allowedToUpload; i++) {
					let file = fileUploadField.files[i];
					if (!file) {
						continue;
					}
					let editWrapClass = assignmentEditFilePreview ? 'tutor-col-sm-5 tutor-py-16 tutor-mr-16' : '';
					fileCard += `<div class="tutor-instructor-card ${editWrapClass}">
                                    <div class="tutor-icard-content">
                                        <div class="tutor-fs-6 tutor-color-secondary">
                                            ${file.name}
                                        </div>
                                        <div class="tutor-fs-7">Size: ${file.size}</div>
                                    </div>
                                    <div onclick="(() => {
										this.closest('.tutor-instructor-card').remove();
									})()" class="tutor-attachment-file-close tutor-iconic-btn tutor-iconic-btn-outline flex-center">
                                        <span class="tutor-icon-times"></span>
                                    </div>
                                </div>`;
				}
				if (assignmentFilePreview) {
					assignmentFilePreview.innerHTML = fileCard;
				}
				if (assignmentEditFilePreview) {
					assignmentEditFilePreview.insertAdjacentHTML('beforeend', fileCard);
				}
			}
		}
	}

	//remove file
	const removeButton = document.querySelectorAll('.tutor-attachment-file-close a');
	removeButton.forEach((item) => {
		item.onclick = async (event) => {
			event.preventDefault();
			const currentTarget = event.currentTarget;
			let fileName = currentTarget.dataset.name;
			let id = currentTarget.dataset.id;
			const formData = new FormData();
			formData.set('action', 'tutor_remove_assignment_attachment');
			formData.set('assignment_comment_id', id);
			formData.set('file_name', fileName);
			formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
			const span = currentTarget.querySelector('span');
			event.target.classList.add('is-loading');
			const post = await ajaxHandler(formData);
			if (post.ok) {
				const response = await post.json();
				if (!response) {
					tutor_toast(__('Warning', 'tutor'), __(`Attachment remove failed`, 'tutor'), 'error');
				} else {
					currentTarget.closest('.tutor-instructor-card').remove();
				}
			} else {
				alert(post.statusText);
				event.target.classList.remove('is-loading');
			}
		};
	});
});
