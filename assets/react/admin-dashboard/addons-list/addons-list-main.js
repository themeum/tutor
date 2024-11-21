import React from 'react';
import { createRoot } from 'react-dom/client';

import App from './components/App';

window.addEventListener('DOMContentLoaded', () => {
    function tutorAddonsList() {
        const element = (
            <App></App>
        );
        const addonWrapper = document.getElementById('tutor-addons-list-wrapper');
        if (null !== addonWrapper) {
            const root = createRoot(addonWrapper);
            root.render(element);
        }
    }
    tutorAddonsList();
});