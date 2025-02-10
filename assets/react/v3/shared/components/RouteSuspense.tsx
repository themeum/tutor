import { LoadingOverlay } from '@TutorShared/atoms/LoadingSpinner';
import { type ReactNode, Suspense } from 'react';
import { useLocation } from 'react-router-dom';
import ErrorBoundary from './ErrorBoundary';

type RouteSuspenseProps = {
  component: ReactNode;
};

const RouteSuspense = ({ component }: RouteSuspenseProps) => {
  const { pathname } = useLocation();
  return (
    <ErrorBoundary key={pathname}>
      <Suspense fallback={<LoadingOverlay />}>{component}</Suspense>
    </ErrorBoundary>
  );
};

export default RouteSuspense;
