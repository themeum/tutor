import React from 'react';
import ReactDom from 'react-dom';

window.addEventListener('DOMContentLoaded', function() {
    function DatePicker() {
        const element = `<h3>Hello</h3>`;
        const wrapper =  document.getElementById('tutor-v2-date-picker');
        if (wrapper) {
            ReactDom.render(element, document.getElementById('tutor-v2-date-picker'));
        }
    }
    DatePicker();
});
