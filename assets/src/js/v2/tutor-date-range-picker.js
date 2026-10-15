import { createRoot } from 'react-dom/client';
import TutorDateRangePicker from '../../../../v2-library/src/components/datapicker/TutorDateRangePicker';

function DateRangePicker() {
    const wrappers = document.querySelectorAll('.tutor-v2-date-range-picker');
    for (let wrapper of wrappers) {
        const root = createRoot(wrapper);
        root.render(
            <TutorDateRangePicker />
        );
    }
}

window.addEventListener('DOMContentLoaded', DateRangePicker);
window.addEventListener(_tutorobject.content_change_event, DateRangePicker);
