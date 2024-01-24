import React from "react";
import ReactDOM from "react-dom/client";

import ErrorBoundary from "@Components/ErrorBoundary";
import App from "@CBComponents/App";

const root = ReactDOM.createRoot(
  document.getElementById("tutor-course-builder") as HTMLElement
);

root.render(
  <React.StrictMode>
    <ErrorBoundary>
      <App />
    </ErrorBoundary>
  </React.StrictMode>
);
