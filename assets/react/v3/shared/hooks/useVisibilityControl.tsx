import { tutorConfig } from '@TutorShared/config/config';
import { isDefined, type VisibilityContext } from '@TutorShared/utils/types';
import { useMemo } from 'react';

interface VisibilityControlProps {
  key?: string;
  context?: VisibilityContext;
}
/**
 * Custom hook to control the visibility of fields based on the provided visibility key and context.
 *
 * @param {VisibilityControlProps} props - The properties for controlling visibility.
 * @param {string} [props.key] - The key used to determine visibility.
 * @param {string} [props.context] - The context in which the visibility key is used.
 * @returns {boolean} - Returns true if the field should be visible, false otherwise.
 */
const useVisibilityControl = ({ key, context }: VisibilityControlProps = {}): boolean => {
  return useMemo(() => {
    // If no visibility key provided, always show the field
    if (!isDefined(key) || !isDefined(context)) {
      return true;
    }

    const visibilitySettings =
      tutorConfig?.visibility_control?.[context as keyof (typeof tutorConfig)['visibility_control']];

    if (!visibilitySettings) {
      return true;
    }

    const userRoles = tutorConfig.current_user.roles;
    const primaryRole = userRoles.includes('administrator') ? 'admin' : 'instructor';
    const keyWithRole = `${key}_${primaryRole}`;

    return visibilitySettings[keyWithRole] === 'on';
  }, [key, context]);
};

export default useVisibilityControl;
