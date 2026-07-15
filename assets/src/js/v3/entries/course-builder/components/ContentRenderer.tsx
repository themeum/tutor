import React from 'react';

import ComponentErrorBoundary from '@TutorShared/components/ComponentErrorBoundary';

interface ContentRendererProps {
  component: React.ReactNode;
}

const ContentRenderer = ({ component }: ContentRendererProps) => {
  return <ComponentErrorBoundary componentName={'content'}>{component}</ComponentErrorBoundary>;
};

export default ContentRenderer;
