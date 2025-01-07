import React from 'react';
import ReactDOM from 'react-dom/client';
import { HashRouter } from 'react-router-dom';

import ErrorBoundary from '@TutorShared/components/ErrorBoundary';

import App from '@BundleBuilderComponents/App';

const root = ReactDOM.createRoot(document.getElementById('tutor-course-bundle-builder-root') as HTMLElement);

root.render(
  <React.StrictMode>
    <HashRouter>
      <ErrorBoundary>
        <App />
      </ErrorBoundary>
    </HashRouter>
  </React.StrictMode>,
);
