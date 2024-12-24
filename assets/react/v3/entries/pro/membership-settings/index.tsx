import React from 'react';
import { createRoot } from 'react-dom/client';

import ErrorBoundary from '@Components/ErrorBoundary';
import App from './components/App';

const element = document.getElementById('tutor-membership-settings');
if (element) {
  const root = createRoot(element as HTMLElement);
  root.render(
    <React.StrictMode>
      <ErrorBoundary>
        <App />
      </ErrorBoundary>
    </React.StrictMode>,
  );
} else {
  console.error('Target element not found.');
}
