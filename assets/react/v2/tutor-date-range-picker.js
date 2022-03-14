import React from 'react';
import ReactDom from 'react-dom';
import TutorDateRangePicker from '../../../v2-library/src/components/datapicker/TutorDateRangePicker';

window.addEventListener('DOMContentLoaded', function() {
    function dateRangePicker() {
        const element = <TutorDateRangePicker></TutorDateRangePicker>;
        const wrappers = document.querySelectorAll('.tutor-v2-date-range-picker');
        for (let wrapper of wrappers) {
            ReactDom.render(element, wrapper)
        } 
    }
    dateRangePicker();
});