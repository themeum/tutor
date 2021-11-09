import React from 'react';
import ReactDom from 'react-dom';

window.addEventListener('DOMContentLoaded', function() {
    function DatePicker() {
        const element = <h3>Hello</h3>;
        const wrappers =  document.querySelectorAll('.tutor-v2-date-picker');
        for(let wrapper of wrappers) {
            if (wrapper) {
                ReactDom.render(element, wrapper);
            }
        }
    }
    DatePicker();
});
