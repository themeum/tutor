import { createRoot } from 'react-dom/client';
import TutorDateTimePicker from '../../../../v2-library/src/components/datapicker/TutorDateTimePicker';

function DateTimePicker() {
    const wrappers = document.querySelectorAll('.tutor-v2-date-time-picker');
    for (let wrapper of wrappers) {
        const { dataset = {} } = wrapper;
        const root = createRoot(wrapper);
        root.render(
            <TutorDateTimePicker {...dataset} />
        );
    }
}

window.addEventListener('DOMContentLoaded', DateTimePicker);
window.addEventListener(_tutorobject.content_change_event, DateTimePicker);
