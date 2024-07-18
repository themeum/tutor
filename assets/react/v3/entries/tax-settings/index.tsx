import React from 'react';
import { createRoot } from 'react-dom/client';

const App = () => {
  return (
    <>
      <h3>Hello</h3>
    </>
  );
};

const root = createRoot(document.getElementById('ecommerce_tax') as HTMLElement);
root.render(<App />);
