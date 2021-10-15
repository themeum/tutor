import React from 'react';
import ReactDOM from 'react-dom';

import App from './components/App';

window.addEventListener( 'DOMContentLoaded', () => {
    function tutorAddonsList() {
        const element = (
            <App></App>
        );
        ReactDOM.render( element, document.getElementById( 'tutor-addons-list-wrapper' ) ); 
    } 
    tutorAddonsList();
} );