document.addEventListener('DOMContentLoaded', (event) => {
    /* sidetab tab position */
    const topBar = document.querySelector('.tutor-single-page-top-bar');
    const sideBar = document.querySelector('.tutor-lesson-sidebar');
    sideBar ? sideBar.style.top = topBar.clientHeight + 'px' : 0;
    /* sidetab tab position */

    /* sidetab tab */
    const sideBarTabs = document.querySelectorAll('.tutor-sidebar-tab-item');
    sideBarTabs.forEach((tab) => {
        tab.addEventListener('click', (event) => {
            clearActiveClass();
            event.currentTarget.classList.add('active');
            let id = event.currentTarget.getAttribute('data-sidebar-tab');
            document.getElementById(id).classList.add('active');
        });
    });

    const clearActiveClass = function() {
        for (let i = 0; i < sideBarTabs.length; i++) {
            sideBarTabs[i].classList.remove('active');
        }
        let sidebarTabItems = document.querySelectorAll(
            '.tutor-lesson-sidebar-tab-item'
        );
        for (let i = 0; i < sidebarTabItems.length; i++) {
            sidebarTabItems[i].classList.remove('active');
        }
    };
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

    const replyComment = document.querySelector(
        '.tutor-comment-box.tutor-reply-box'
    );

    if (parentComments) {
        [...parentComments].forEach((parentComment) => {

            const childComments = parentComment.querySelectorAll(
                '.tutor-comments-list.tutor-child-comment'
            );
            const commentLine = parentComment.querySelector('.tutor-comment-line');
            const childCommentCount = childComments.length;
            const lastCommentHeight = childComments[childCommentCount - 1].clientHeight;
            let heightOfLine =
                lastCommentHeight + replyComment.clientHeight + 20 - 25 + 50;
            commentLine.style.setProperty('height', `calc(100% - ${heightOfLine}px)`);
        });
    }
    /* commenting */

    /* Show More Text */
    const showMoreBtn = document.querySelector('.tutor-show-more-btn button');
    showMoreBtn ? showMoreBtn.addEventListener('click', showMore) : 0;

    function showMore() {
        let lessText = document.getElementById("short-text");
        let dots = document.getElementById("dots");
        let moreText = document.getElementById("full-text");
        let btnText = document.getElementById("showBtn");
        let contSect = document.getElementById("content-section");
        console.log(lessText, dots, moreText, btnText);
        if (dots.style.display === "none") {
            lessText.style.display = "block";
            dots.style.display = "inline";
            btnText.innerHTML = "<span class='btn-icon ttr-plus-filled color-design-brand'></span><span class='color-text-primary'>Show More</span>";
            moreText.style.display = "none";
            contSect.classList.remove('no-before');
        } else {
            lessText.style.display = "none";
            dots.style.display = "none";
            btnText.innerHTML = "<span class='btn-icon ttr-minus-filled color-design-brand'></span><span class='color-text-primary'>Show Less</span>";
            moreText.style.display = "block";
            contSect.classList.add('no-before');
        }
    }
    /* Show More Text */

});