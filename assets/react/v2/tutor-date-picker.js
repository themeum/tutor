import React, { lazy, Suspense } from 'react';
import { createRoot } from 'react-dom/client';

const TutorDatepicker = lazy(() => import('../../../v2-library/src/components/datapicker/TutorDatepicker'));

function DatePicker() {
    const wrappers = document.querySelectorAll('.tutor-v2-date-picker');
    for (let wrapper of wrappers) {
        const { dataset = {} } = wrapper;
        const root = createRoot(wrapper);
        root.render(
            <Suspense fallback={<div>Loading...</div>}>
                <TutorDatepicker {...dataset} />
            </Suspense>
        );
    }
}

window.addEventListener('DOMContentLoaded', DatePicker);
window.addEventListener(_tutorobject.content_change_event, DatePicker);
