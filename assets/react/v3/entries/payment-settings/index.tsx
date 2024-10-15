import { createRoot } from 'react-dom/client';
import App from './components/App';

const root = createRoot(document.getElementById('ecommerce_payment') as HTMLElement);
root.render(<App />);
