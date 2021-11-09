document.addEventListener('DOMContentLoaded', (event) => {
    const topBar = document.querySelector('.tutor-single-page-top-bar');
    const sideBar = document.querySelector('.tutor-lesson-sidebar');
    sideBar.style.top = topBar.clientHeight + 'px';
});

document.addEventListener('DOMContentLoaded', (event) => {
    const sideBarTabs = document.querySelectorAll('.tutor-sidebar-tab-item');
    sideBarTabs.forEach((tab) => {
        tab.addEventListener('click', (event) => {
            console.log('tab activate');
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
});