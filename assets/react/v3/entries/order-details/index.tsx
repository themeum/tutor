import React from 'react';
import ReactDOM from 'react-dom/client';

import ErrorBoundary from '@Components/ErrorBoundary';
import App from '@OrderComponents/App';

const root = ReactDOM.createRoot(document.getElementById('tutor-order-details-root') as HTMLElement);

root.render(
  <React.StrictMode>
    <ErrorBoundary>
      <App />
    </ErrorBoundary>
  </React.StrictMode>
);
