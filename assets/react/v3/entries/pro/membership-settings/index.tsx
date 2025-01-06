import React from 'react';
import { createRoot } from 'react-dom/client';
import { HashRouter } from 'react-router-dom';

import ErrorBoundary from '@TutorShared/components/ErrorBoundary';
import App from './components/App';

const element = document.getElementById('tutor-membership-settings');
if (element) {
  const root = createRoot(element as HTMLElement);
  root.render(
    <React.StrictMode>
      <HashRouter>
        <ErrorBoundary>
          <App />
        </ErrorBoundary>
      </HashRouter>
    </React.StrictMode>,
  );
} else {
  console.error('Target element not found.');
}
