import Alert from '@TutorShared/atoms/Alert';
import { Component, type ErrorInfo, type ReactNode } from 'react';

interface ComponentErrorBoundaryProps {
  children: ReactNode;
  componentName?: string;
  fallback?: ReactNode;
  showError?: boolean;
  onError?: (error: Error, errorInfo: ErrorInfo) => void;
}

interface ComponentErrorBoundaryState {
  hasError: boolean;
  error: Error | null;
}

class ComponentErrorBoundary extends Component<ComponentErrorBoundaryProps, ComponentErrorBoundaryState> {
  state: ComponentErrorBoundaryState = { hasError: false, error: null };

  static defaultProps = {
    showError: true,
    componentName: 'Component',
  };

  static getDerivedStateFromError(error: Error) {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    // eslint-disable-next-line no-console
    console.error(`Error rendering ${this.props.componentName}:`, error, errorInfo);
    this.props.onError?.(error, errorInfo);
  }

  render() {
    const { children, fallback, showError } = this.props;
    const { hasError, error } = this.state;

    if (hasError) {
      if (fallback) {
        return fallback;
      }

      return showError ? (
        <Alert type="danger">
          Error rendering {this.props.componentName}: {error?.message || error?.toString()}
        </Alert>
      ) : null;
    }

    return children;
  }
}

export default ComponentErrorBoundary;
