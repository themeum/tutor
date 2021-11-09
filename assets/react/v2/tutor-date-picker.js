import React from 'react';
import ReactDom from 'react-dom';
import TutorDatepicker from '../../../v2-library/src/components/datapicker/TutorDatepicker';

window.addEventListener('DOMContentLoaded', function() {
    function DatePicker() {
        const element = <TutorDatepicker></TutorDatepicker>;
        const wrappers =  document.querySelectorAll('.tutor-v2-date-picker');
        for(let wrapper of wrappers) {
            if (wrapper) {
                ReactDom.render(element, wrapper);
            }
        }
    }
    DatePicker();
});
