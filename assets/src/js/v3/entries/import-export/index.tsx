import React from 'react';
import { createRoot } from 'react-dom/client';

import ErrorBoundary from '@TutorShared/components/ErrorBoundary';
import App from './components/App';

const root = createRoot(document.getElementById('import-export-root') as HTMLElement);

root.render(
  <React.StrictMode>
    <ErrorBoundary>
      <App />
    </ErrorBoundary>
  </React.StrictMode>,
);
