import React from 'react';
import { createRoot } from 'react-dom/client';
import TutorDateTimePicker from '../../../v2-library/src/components/datapicker/TutorDateTimePicker';

function DatePicker() {
    const wrappers = document.querySelectorAll('.tutor-v2-date-time-picker');
    for (let wrapper of wrappers) {
        if (wrapper) {
            let disablePastDate = false;
            const { dataset = {} } = wrapper;
            /**
             * If has tutor-disable-past-date then disable past
             * date selection
             * 
             * @since v2.1.0
             */
            if (wrapper.hasAttribute('tutor-disable-past-date')) {
                disablePastDate = true;
            }
            const root = createRoot(wrapper);
            root.render(<TutorDateTimePicker {...dataset} disablePastDate={disablePastDate} />);
        }
    }
}

window.addEventListener('DOMContentLoaded', DatePicker);
window.addEventListener(_tutorobject.content_change_event, DatePicker);