import { createRoot } from 'react-dom/client';
import App from './components/App';

const root = createRoot(document.getElementById('ecommerce_tax') as HTMLElement);
root.render(<App />);
