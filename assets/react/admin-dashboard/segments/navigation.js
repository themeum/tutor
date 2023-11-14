
/**
 * Navigation tab
 */
const navTabLists = document.querySelectorAll('[tutor-option-tabs]');
const navTabItems = document.querySelectorAll('[tutor-option-tabs] li > a');
const navPages = document.querySelectorAll('.tutor-option-nav-page');

function enableSaveButton() {
    if (document.getElementById('save_tutor_option')) {
        document.getElementById('save_tutor_option').disabled = false;
    }
}

readyState_complete(() => {
    const loadNavItem = document.querySelector('[tutor-option-tabs] li > a.is-active');
    if (null !== loadNavItem) {
        document.title = loadNavItem.querySelector('[tutor-option-label]').innerText + ' < ' + _tutorobject.site_title;
    }

    navTabLists.forEach((list) => {
        list.addEventListener('click', (e) => {
            const dataTab = e.target.parentElement.dataset.tab || e.target.dataset.tab;
            const pageSlug = e.target.parentElement.dataset.page || e.target.dataset.page;

            if (dataTab) {
                // Set page title on changing nav tabs
                document.title = e.target.innerText + ' < ' + _tutorobject.site_title;
                // remove active from other buttons
                navTabItems.forEach((item) => {
                    item.classList.remove('is-active');
                    document.body.classList.remove(item.dataset.tab);
                    if (e.target.dataset.tab) {
                        document.body.classList.add(e.target.dataset.tab);
                        e.target.classList.add('is-active');
                    } else {
                        e.target.parentElement.classList.add('is-active');
                    }
                });
                // hide other tab contents
                navPages.forEach((content) => {
                    content.classList.remove('is-active');
                });
                // add active to the current content
                const currentContent = document.querySelector(`#${dataTab}`);
                currentContent.classList.add('is-active');

                // History push
                const url = new URL(window.location);

                const params = new URLSearchParams({ page: pageSlug, tab_page: dataTab });
                const pushUrl = `${url.origin + url.pathname}?${params.toString()}`;

                window.history.pushState({}, '', pushUrl);
                addBodyClass(window.location);

                const loadingSpinner = document.getElementById(dataTab).querySelector('.loading-spinner');
                if (loadingSpinner) {
                    document.getElementById(dataTab).querySelector('.loading-spinner').remove();
                }
            
            }

        });
    });
});

addBodyClass(window.location);
