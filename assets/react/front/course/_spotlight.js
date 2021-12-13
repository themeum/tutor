jQuery(document).ready(function($) {
	$('.tutor-sortable-list').sortable();
});

document.addEventListener('DOMContentLoaded', (event) => {
	const sidebar = document.querySelector(
		'.tutor-lesson-sidebar.tutor-desktop-sidebar'
	);
	const sidebarToggle = document.querySelector(
		'.tutor-sidebar-toggle-anchor'
	);
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

	const sidebarTabeHandler = function(sideBarTabs) {
		sideBarTabs.forEach((tab) => {
			tab.addEventListener('click', (event) => {
				const tabConent =
					event.currentTarget.parentNode.nextElementSibling;
				clearActiveClass(tabConent);
				event.currentTarget.classList.add('active');
				let id = event.currentTarget.getAttribute('data-sidebar-tab');
				tabConent.querySelector('#' + id).classList.add('active');
			});
		});
		const clearActiveClass = function(tabConent) {
			for (let i = 0; i < sideBarTabs.length; i++) {
				sideBarTabs[i].classList.remove('active');
			}
			let sidebarTabItems = tabConent.querySelectorAll(
				'.tutor-lesson-sidebar-tab-item'
			);
			for (let i = 0; i < sidebarTabItems.length; i++) {
				sidebarTabItems[i].classList.remove('active');
			}
		};
	};
	const desktopSidebar = document.querySelectorAll(
		'.tutor-desktop-sidebar-area .tutor-sidebar-tab-item'
	);
	const mobileSidebar = document.querySelectorAll(
		'.tutor-mobile-sidebar-area .tutor-sidebar-tab-item'
	);
	if (desktopSidebar) {
		sidebarTabeHandler(desktopSidebar);
	}
	if (mobileSidebar) {
		sidebarTabeHandler(mobileSidebar);
	}
	/* end of sidetab tab */

	/* comment text-area focus arrow style */
	const commentTextarea = document.querySelectorAll(
		'.tutor-comment-textarea textarea'
	);
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

    const parentComments = document.querySelectorAll(
        '.tutor-comments-list.tutor-parent-comment'
    );
    const replyComment = document.querySelector(
        '.tutor-comment-box.tutor-reply-box'
    );
    function commentSideLine() {
        if (parentComments) {
            [...parentComments].forEach((parentComment) => {
                
                const childComments = parentComment.querySelectorAll(
                    '.tutor-comments-list.tutor-child-comment'
                );
                const commentLine = parentComment.querySelector(
                    '.tutor-comment-line'
                );
                const childCommentCount = childComments.length;
                if(childComments[childCommentCount - 1]) {
                    const lastCommentHeight = childComments[childCommentCount - 1].clientHeight;
                    let heightOfLine = lastCommentHeight + replyComment.clientHeight + 20 - 25 + 50;
                    commentLine.style.setProperty(
                        'height',
                        `calc(100% - ${heightOfLine}px)`
                    );
                }
            });
        }
    }
    commentSideLine();
    // if(parentComments && replyComment) {
    //     console.log('yo yo pops from the baler list ', parentComments);
    //     console.log('yo yo pops from the baler list ', replyComment);
    //     commentSideLine();
    // }
	const spotlightTabs = document.querySelectorAll(
		'.tutor-spotlight-tab.tutor-default-tab .tab-header-item'
	);
	const spotlightTabContent = document.querySelectorAll(
		'.tutor-spotlight-tab .tab-body-item'
	);
	if (spotlightTabs && spotlightTabContent) {
		spotlightTabs.forEach((tab) => {
			tab.addEventListener('click', (event) => { 
                clearSpotlightTabActiveClass();
				event.currentTarget.classList.add('is-active');
				let id = event.currentTarget.getAttribute(
					'data-tutor-spotlight-tab-target'
				);
				const tabConent =
					event.currentTarget.parentNode.nextElementSibling;
				tabConent.querySelector('#' + id).classList.add('is-active');
                if (id === 'tutor-course-spotlight-tab-3') {
                    console.log('milse ', id);
					commentSideLine();
				}
			});
		});
		const clearSpotlightTabActiveClass = () => {
			spotlightTabs.forEach((item) => {
				item.classList.remove('is-active');
			});
			spotlightTabContent.forEach((item) => {
				item.classList.remove('is-active');
			});
		};
	}
	/* commenting */

	// quize drag n drop functionality
	const tutorDraggables = document.querySelectorAll('.tutor-draggable > div');
	const tutorDropzone = document.querySelectorAll('.tutor-dropzone');
	tutorDraggables.forEach((quizBox) => {
		quizBox.addEventListener('dragstart', dragStart);
		quizBox.addEventListener('dragend', dragEnd);
	});
	tutorDropzone.forEach((quizImageBox) => {
		quizImageBox.addEventListener('dragover', dragOver);
		quizImageBox.addEventListener('dragenter', dragEnter);
		quizImageBox.addEventListener('dragleave', dragLeave);
		quizImageBox.addEventListener('drop', dragDrop);
	});
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
	function dragEnter() {}
	function dragLeave() {
		this.classList.remove('tutor-drop-over');
	}
	function dragDrop() {
		const copyElement = document.querySelector(
			'.tutor-quiz-border-box.tutor-dragging'
		);
		if (this.querySelector('input')) {
			this.querySelector('input').remove();
		}
		const input = copyElement.querySelector('input');
		const inputValue = input.value;
		const inputName = input.dataset.name;
		const newInput = document.createElement('input');
		newInput.type = 'text';
		newInput.setAttribute('value', input.value);
		newInput.setAttribute('name', inputName);
		this.appendChild(newInput);
		const copyContent = copyElement.querySelector(
			'.tutor-dragging-text-conent'
		).textContent;
		this.querySelector(
			'.tutor-dragging-text-conent'
		).textContent = copyContent;
		this.classList.remove('tutor-drop-over');
	}

	// tutor assignment file upload
	const fileUploadField = document.getElementById(
		'tutor-assignment-file-upload'
	);
	if (fileUploadField) {
		fileUploadField.addEventListener('change', tutorAssignmentFileHandler);
	}
	function tutorAssignmentFileHandler() {
		let message = '';
		if ('files' in fileUploadField) {
			if (fileUploadField.files.length == 0) {
				message = 'Select one or more files.';
			} else {
				let fileCard = '';
				for (let i = 0; i < fileUploadField.files.length; i++) {
					let file = fileUploadField.files[i];
					fileCard += `<div class="tutor-instructor-card">
                                    <div class="tutor-icard-content">
                                        <div class="text-regular-body color-text-title">
                                            ${file.name}
                                        </div>
                                        <div class="text-regular-small">Size: ${file.size}</div>
                                    </div>
                                    <div onclick="(() => {
										this.closest('.tutor-instructor-card').remove();
									})()" class="tutor-attachment-file-close tutor-avatar tutor-is-xs flex-center">
                                        <span class="ttr-cross-filled color-design-brand"></span>
                                    </div>
                                </div>`;
				}
				document.querySelector(
					'.tutor-asisgnment-upload-file-preview'
				).innerHTML = fileCard;
			}
		}
	}

	/* Show More Text */
	const showMoreBtn = document.querySelector('.tutor-show-more-btn button');
	if (showMoreBtn) {
		showMoreBtn.addEventListener('click', showMore);
	}

	function showMore() {
		let lessText = document.getElementById('short-text');
		let dots = document.getElementById('dots');
		let moreText = document.getElementById('full-text');
		let btnText = document.getElementById('showBtn');
		let contSect = document.getElementById('content-section');
		if (dots.style.display === 'none') {
			lessText.style.display = 'block';
			dots.style.display = 'inline';
			btnText.innerHTML =
				"<span class='btn-icon ttr-plus-filled color-design-brand'></span><span class='color-text-primary'>Show More</span>";
			moreText.style.display = 'none';
		} else {
			lessText.style.display = 'none';
			dots.style.display = 'none';
			btnText.innerHTML =
				"<span class='btn-icon ttr-minus-filled color-design-brand'></span><span class='color-text-primary'>Show Less</span>";
			moreText.style.display = 'block';
			contSect.classList.add('no-before');
		}
	}
});