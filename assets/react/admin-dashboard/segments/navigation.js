
/**
 * Navigation tab
 */
const navTabLists = document.querySelectorAll('ul.tutor-option-nav');
const navTabItems = document.querySelectorAll('li.tutor-option-nav-item a');
const navPages = document.querySelectorAll('.tutor-option-nav-page');

navTabLists.forEach((list) => {
    list.addEventListener('click', (e) => {
        const dataTab = e.target.parentElement.dataset.tab || e.target.dataset.tab;
        const pageSlug = e.target.parentElement.dataset.page || e.target.dataset.page;

        if (dataTab) {
            // remove active from other buttons
            navTabItems.forEach((item) => {
                item.classList.remove('active');
                if (e.target.dataset.tab) {
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
            const loadingSpinner = document.getElementById(dataTab).querySelector('.loading-spinner');
            if (loadingSpinner) {
                document.getElementById(dataTab).querySelector('.loading-spinner').remove();
            }

            //enable if tinymce content changed
            if (null !== tinymce && typeof tinymce !== 'undefined') {
                tinymce.activeEditor.on("change", function (e) {
                    document.getElementById('save_tutor_option').disabled = false;
                });
            }
        }

    });
});