import React, { lazy, Suspense } from 'react';
import { createRoot } from 'react-dom/client';

const TutorDatepicker = lazy(() => import('../../../v2-library/src/components/datapicker/TutorDatepicker'));

function DatePicker() {
    const { __ } = wp.i18n;

    const fallbackElement = (
        <div class="tutor-form-wrap">
            <span class="tutor-form-icon tutor-form-icon-reverse">
                <span class="tutor-icon-calender-line" aria-hidden="true"></span>
            </span>
            <input class="tutor-form-control" placeholder={__('Loading...', 'tutor')} />
        </div>
    );

    const wrappers = document.querySelectorAll('.tutor-v2-date-picker');
    for (let wrapper of wrappers) {
        const { dataset = {} } = wrapper;
        const root = createRoot(wrapper);
        root.render(
            <Suspense fallback={fallbackElement}>
                <TutorDatepicker {...dataset} />
            </Suspense>
        );
    }
}

window.addEventListener('DOMContentLoaded', DatePicker);
window.addEventListener(_tutorobject.content_change_event, DatePicker);
