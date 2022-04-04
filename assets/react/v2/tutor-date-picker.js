import React from 'react';
import ReactDom from 'react-dom';
import TutorDatepicker from '../../../v2-library/src/components/datapicker/TutorDatepicker';

function DatePicker() {
    const wrappers =  document.querySelectorAll('.tutor-v2-date-picker');
    for(let wrapper of wrappers) {
        if (wrapper) {
            const {dataset={}} = wrapper;
            ReactDom.render(<TutorDatepicker {...dataset}/>, wrapper);
        }
    }
}

window.addEventListener('DOMContentLoaded', DatePicker);
window.addEventListener(_tutorobject.content_change_event, DatePicker);