/**
 * Checks if any course item within the provided data has 'children' data.
 *
 * @param data The root data object containing course information.
 * @returns true if any course topic has children, false otherwise.
 */
export const hasAnyCourseWithChildren = (data: {
  data: {
    content_type: string;
    data?: {
      courses?: {
        contents?: {
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          children?: any[];
        }[];
      }[];
      contents?: {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        children?: any[];
      }[];
    }[];
  }[];
}): boolean => {
  return data.data.some((item) => {
    if (item.content_type === 'courses') {
      if (!item.data || !Array.isArray(item.data)) {
        return false;
      }
      return item.data.some((course) => {
        if (!course.contents) {
          return false;
        }
        return course.contents.some((contentItem) => {
          return contentItem.children && contentItem.children.length > 0;
        });
      });
    } else if (item.content_type === 'course-bundle') {
      if (!item.data || !Array.isArray(item.data)) {
        return false;
      }
      return item.data.some((bundle) => {
        if (!bundle.courses || !Array.isArray(bundle.courses)) {
          return false;
        }
        return bundle.courses.some((bundleCourse) => {
          if (!bundleCourse.contents || !Array.isArray(bundleCourse.contents)) {
            return false;
          }
          return bundleCourse.contents.some((contentItem) => {
            return contentItem.children && contentItem.children.length > 0;
          });
        });
      });
    }
    return false;
  });
};
