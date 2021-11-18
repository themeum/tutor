document.addEventListener('DOMContentLoaded', (event) => {
    /* sidetab tab position */
    const topBar = document.querySelector('.tutor-single-page-top-bar');
    const sideBar = document.querySelector('.tutor-lesson-sidebar');
    sideBar.style.top = topBar.clientHeight + 'px';
    /* sidetab tab position */

    const sidebarTabeHandler = function(sideBarTabs) {
        sideBarTabs.forEach((tab) => {
            tab.addEventListener('click', (event) => {
                const tabConent =
                    event.currentTarget.parentNode.nextElementSibling;
                clearActiveClass(tabConent);
                event.currentTarget.classList.add('active');
                let id = event.currentTarget.getAttribute('data-sidebar-tab');
                console.log(tabConent.querySelector('#' + id));
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

    /* commenting */
    const parentComments = document.querySelectorAll(
        '.tutor-comments-list.tutor-parent-comment'
    );
    console.log('sdfa  s   ', parentComments);
    const replyComment = document.querySelector(
        '.tutor-comment-box.tutor-reply-box'
    );
    console.log('sdfa  s   ', replyComment);
    if (parentComments) {
        [...parentComments].forEach((parentComment) => {
            const childComments = parentComment.querySelectorAll(
                '.tutor-comments-list.tutor-child-comment'
            );
            const commentLine = parentComment.querySelector(
                '.tutor-comment-line'
            );
            const childCommentCount = childComments.length;
            const lastCommentHeight =
                childComments[childCommentCount - 1].clientHeight;
            let heightOfLine =
                lastCommentHeight + replyComment.clientHeight + 20 - 25 + 50;
            commentLine.style.setProperty(
                'height',
                `calc(100% - ${heightOfLine}px)`
            );
        });
    }
    /* commenting */

    // quize drag n drop functionality
    const quizBoxs = document.querySelectorAll('.tutor-quiz-border-box');
    const quizImageBoxs = document.querySelectorAll('.tutor-quiz-dotted-box');
    // const quizImageBoxs = document.querySelectorAll('.quiz-image-box');
    quizBoxs.forEach((quizBox) => {
        quizBox.addEventListener('dragstart', dragStart);
        quizBox.addEventListener('dragend', dragEnd);
        // console.log(quizBox);
    });
    quizImageBoxs.forEach((quizImageBox) => {
        quizImageBox.addEventListener('dragover', dragOver);
        quizImageBox.addEventListener('dragenter', dragEnter);
        quizImageBox.addEventListener('dragleave', dragLeave);
        quizImageBox.addEventListener('drop', dragDrop);
    });

    function dragStart() {
        this.classList.add('tutor-dragging');
        console.log('start ', this);
    }

    function dragEnd() {
        this.classList.remove('tutor-dragging');
        console.log('end ', this);
    }

    function dragOver(event) {
        this.classList.add('tutor-drop-over');
        console.log('dragOver ', this);
        event.preventDefault();
    }

    function dragEnter() {
        console.log('dragEnter', this);
    }

    function dragLeave() {
        this.classList.remove('tutor-drop-over');
        console.log('dragLeave', this);
    }

    function dragDrop() {
        const copyElement = document.querySelector(
            '.tutor-quiz-border-box.tutor-dragging'
        );
        // this.textContent = copyElement.textContent;
        this.innerHTML = copyElement.innerHTML;
        console.log('drop ', copyElement.textContent, this.textContent);
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
                console.log(message);
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