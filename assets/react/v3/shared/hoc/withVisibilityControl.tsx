import { type ComponentType } from 'react';

import useVisibilityControl from '@TutorShared/hooks/useVisibilityControl';
import { type WithVisibilityProps } from '@TutorShared/utils/types';

export const withVisibilityControl = <P extends object>(WrappedComponent: ComponentType<WithVisibilityProps<P>>) => {
  return (props: WithVisibilityProps<P>) => {
    const { visibilityKey, ...restProps } = props;
    const isVisible = useVisibilityControl(visibilityKey);

    if (!isVisible) {
      return null;
    }

    // @ts-ignore
    return <WrappedComponent {...(restProps as WithVisibilityProps<P>)} />;
  };
};
