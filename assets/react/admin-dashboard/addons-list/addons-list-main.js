import React from 'react';
import ReactDOM from 'react-dom';

import App from './components/App';

window.addEventListener( 'DOMContentLoaded', () => {
    function tutorAddonsList() {
        const element = (
            <App></App>
        );
        const addonWrapper = document.getElementById( 'tutor-addons-list-wrapper' );
        if (null !== addonWrapper) {
            ReactDOM.render( element, addonWrapper ); 
        }
    } 
    tutorAddonsList();
} );