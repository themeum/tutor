import ComponentErrorBoundary from '@TutorShared/components/ComponentErrorBoundary';
import React from 'react';

interface ContentRendererProps {
  component: React.ReactNode;
}

const ContentRenderer = ({ component }: ContentRendererProps) => {
  return <ComponentErrorBoundary componentName={'content'}>{component}</ComponentErrorBoundary>;
};

export default ContentRenderer;
