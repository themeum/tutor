import { createRoot } from 'react-dom/client';
import TutorDatepicker from '../../../../v2-library/src/components/datapicker/TutorDatepicker';

function DatePicker() {
    const wrappers = document.querySelectorAll('.tutor-v2-date-picker');
    for (let wrapper of wrappers) {
        const { dataset = {} } = wrapper;
        const root = createRoot(wrapper);
        root.render(
            <TutorDatepicker {...dataset} />
        );
    }
}

window.addEventListener('DOMContentLoaded', DatePicker);
window.addEventListener(_tutorobject.content_change_event, DatePicker);
