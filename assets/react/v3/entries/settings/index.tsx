import React from 'react';
import ReactDOM from 'react-dom/client';

import App from '@Settings/components/App';
import ErrorBoundary from '@TutorShared/components/ErrorBoundary';
import { HashRouter } from 'react-router-dom';

const root = ReactDOM.createRoot(document.getElementById('tutor-settings-app') as HTMLElement);

root.render(
  <React.StrictMode>
    <HashRouter>
      <ErrorBoundary>
        <App />
      </ErrorBoundary>
    </HashRouter>
  </React.StrictMode>,
);
