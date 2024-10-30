import React from 'react';
import { createRoot } from 'react-dom/client';

import ErrorBoundary from '@Components/ErrorBoundary';
import App from './components/App';

const root = createRoot(document.getElementById('ecommerce_tax') as HTMLElement);

root.render(
  <React.StrictMode>
    <ErrorBoundary>
      <App />
    </ErrorBoundary>
  </React.StrictMode>
);
