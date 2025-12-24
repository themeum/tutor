import React from 'react';
import ReactDOM from 'react-dom/client';

import App from '@AddonList/components/App';
import ErrorBoundary from '@TutorShared/components/ErrorBoundary';

const root = ReactDOM.createRoot(document.getElementById('tutor-addon-list-wrapper') as HTMLElement);

root.render(
  <React.StrictMode>
    <ErrorBoundary>
      <App />
    </ErrorBoundary>
  </React.StrictMode>,
);
