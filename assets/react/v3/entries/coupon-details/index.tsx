import React from 'react';
import ReactDOM from 'react-dom/client';

import ErrorBoundary from '@Components/ErrorBoundary';
import App from '@CouponComponents/App';
import { HashRouter } from 'react-router-dom';

const root = ReactDOM.createRoot(document.getElementById('tutor-coupon-root') as HTMLElement);

root.render(
  <React.StrictMode>
    <HashRouter>
      <ErrorBoundary>
        <App />
      </ErrorBoundary>
    </HashRouter>
  </React.StrictMode>,
);
