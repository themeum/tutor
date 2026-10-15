import type { ReactNode } from 'react';

interface ErrorBoundaryProps {
  children: ReactNode;
}

let ErrorBoundaryComponent: React.ComponentType<ErrorBoundaryProps>;

if (process.env.NODE_ENV === 'development') {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  ErrorBoundaryComponent = require('./ErrorBoundaryDev').default;
} else {
  // eslint-disable-next-line @typescript-eslint/no-require-imports
  ErrorBoundaryComponent = require('./ErrorBoundaryProd').default;
}

const ErrorBoundary = ({ children }: ErrorBoundaryProps) => {
  return <ErrorBoundaryComponent>{children}</ErrorBoundaryComponent>;
};

export default ErrorBoundary;
