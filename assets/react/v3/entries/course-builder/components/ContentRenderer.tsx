import ComponentErrorBoundary from '@TutorShared/components/ComponentErrorBoundary';
import React from 'react';

const ContentRenderer: React.FC<{
  component: React.ReactNode;
}> = ({ component }) => {
  return <ComponentErrorBoundary componentName={'content'}>{component}</ComponentErrorBoundary>;
};

export default ContentRenderer;
