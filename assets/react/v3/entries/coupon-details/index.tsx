import React from 'react';
import ReactDOM from 'react-dom/client';

import ErrorBoundary from '@Components/ErrorBoundary';
import App from '@CouponComponents/App';

const root = ReactDOM.createRoot(document.getElementById('tutor-coupon-root') as HTMLElement);

root.render(
  <React.StrictMode>
    <ErrorBoundary>
      <App />
    </ErrorBoundary>
  </React.StrictMode>
);
