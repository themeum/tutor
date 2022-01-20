
/**
 * Navigation tab
 */
const navTabLists = document.querySelectorAll('ul.tutor-option-nav');
const navTabItems = document.querySelectorAll('li.tutor-option-nav-item a');
const navPages = document.querySelectorAll('.tutor-option-nav-page');

readyState_complete(() => {
    const loadNavItem = document.querySelector('li.tutor-option-nav-item a.active');
    if (null !== loadNavItem) {
        document.title = loadNavItem.querySelector('.nav-label').innerText + ' ‹ ' + _tutorobject.site_title;
    }


    navTabLists.forEach((list) => {
        list.addEventListener('click', (e) => {
            const dataTab = e.target.parentElement.dataset.tab || e.target.dataset.tab;
            const pageSlug = e.target.parentElement.dataset.page || e.target.dataset.page;

            if (dataTab) {
                // Set page title on changing nav tabs
                document.title = e.target.innerText + ' ‹ ' + _tutorobject.site_title;
                // remove active from other buttons
                navTabItems.forEach((item) => {
                    item.classList.remove('active');
                    document.body.classList.remove(item.dataset.tab);
                    if (e.target.dataset.tab) {
                        document.body.classList.add(e.target.dataset.tab);
                        e.target.classList.add('active');
                    } else {
                        e.target.parentElement.classList.add('active');
                    }
                });
                // hide other tab contents
                navPages.forEach((content) => {
                    content.classList.remove('active');
                });
                // add active to the current content
                const currentContent = document.querySelector(`#${dataTab}`);
                currentContent.classList.add('active');

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

                console.log(typeof(tinyMCE) != "undefined");
                //enable if tinymce content changed
                if (tinymce && 'undefined' !== typeof tinymce && null !== tinymce) {
                    tinymce.activeEditor.on("change", function (e) {
                        if (document.getElementById('save_tutor_option')) {
                            document.getElementById('save_tutor_option').disabled = false;
                        }
                    });
                }
            }

        });
    });

});


addBodyClass(window.location);
