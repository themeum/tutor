import React from 'react';
import ReactDOM from 'react-dom/client';

import App from '@CouponComponents/App';
import ErrorBoundary from '@TutorShared/components/ErrorBoundary';

const root = ReactDOM.createRoot(document.getElementById('tutor-coupon-root') as HTMLElement);

root.render(
  <React.StrictMode>
    <ErrorBoundary>
      <App />
    </ErrorBoundary>
  </React.StrictMode>,
);
