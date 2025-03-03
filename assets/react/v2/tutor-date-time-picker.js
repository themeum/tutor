import React, { lazy, Suspense } from 'react';
import { createRoot } from 'react-dom/client';

const TutorDateTimePicker = lazy(() => import('../../../v2-library/src/components/datapicker/TutorDateTimePicker'));

function DateTimePicker() {
    const { __ } = wp.i18n;
    const wrappers = document.querySelectorAll('.tutor-v2-date-time-picker');
    for (let wrapper of wrappers) {
        const { dataset = {} } = wrapper;
        const root = createRoot(wrapper);
        root.render(
            <Suspense fallback={<div>{__('Loading...', 'tutor')}</div>}>
                <TutorDateTimePicker {...dataset} />
            </Suspense>
        );
    }
}

window.addEventListener('DOMContentLoaded', DateTimePicker);
window.addEventListener(_tutorobject.content_change_event, DateTimePicker);
