import React, { lazy, Suspense } from 'react';
import { createRoot } from 'react-dom/client';

const TutorDateRangePicker = lazy(() => import('../../../v2-library/src/components/datapicker/TutorDateRangePicker'));

function DateRangePicker() {
    const { __ } = wp.i18n;
    const wrappers = document.querySelectorAll('.tutor-v2-date-range-picker');
    for (let wrapper of wrappers) {
        const root = createRoot(wrapper);
        root.render(
            <Suspense fallback={<div>{__('Loading...', 'tutor')}</div>}>
                <TutorDateRangePicker />
            </Suspense>
        );
    }
}

window.addEventListener('DOMContentLoaded', DateRangePicker);
window.addEventListener(_tutorobject.content_change_event, DateRangePicker);
