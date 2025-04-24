import { useMemo } from 'react';

import { tutorConfig } from '@TutorShared/config/config';
import { isDefined } from '@TutorShared/utils/types';

/**
 * Custom hook to control the visibility of fields based on the provided visibility key and context.
 *
 * @param {string} visibilityKey - The key used to determine the visibility of the field.
 * @returns {boolean} - Returns true if the field should be visible, false otherwise.
 */
const useVisibilityControl = (visibilityKey: string = ''): boolean => {
  return useMemo(() => {
    // If no visibility key provided, always show the field
    if (!isDefined(visibilityKey)) {
      return true;
    }

    const [context, key] = visibilityKey?.split('.') || [];

    if (!isDefined(context) || !isDefined(key)) {
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

    if (!Object.keys(visibilitySettings).includes(keyWithRole)) {
      return true;
    }

    return visibilitySettings[keyWithRole] === 'on';
  }, [visibilityKey]);
};

export default useVisibilityControl;
