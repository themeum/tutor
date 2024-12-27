import { css } from '@emotion/react';
import { FormProvider } from 'react-hook-form';

import CourseBundleContainer from '@BundleBuilderComponents/course-bundle/CourseBundleContainer';
import CourseSelection from '@BundleBuilderComponents/course-bundle/CourseSelection';
import Header from '@BundleBuilderComponents/layouts/header/Header';
import { type CourseBundle } from '@BundleBuilderServices/bundle';
import { Breakpoint, colorTokens, containerMaxWidth, headerHeight, spacing } from '@Config/styles';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

const mockData = [
  {
    id: 1,
    title: 'Tutor LMS For Beginners Part II: Progress Your Webflow Skills With This Course',
    image: 'https://placehold.co/600x400',
    is_purchasable: true,
    regular_price: '100',
    sale_price: null,
    course_duration: '1 hour',
    last_updated: '2021-09-01',
    total_enrolled: 100,
  },
  {
    id: 2,
    title: 'Tutor LMS For Beginners Part II: Progress Your Webflow Skills With This Course',
    image: 'https://placehold.co/600x400',
    is_purchasable: true,
    regular_price: '200',
    sale_price: '150',
    course_duration: '2 hours',
    last_updated: '2021-09-02',
    total_enrolled: 200,
  },
  {
    id: 3,
    title: 'Course 3',
    image: 'https://placehold.co/600x400',
    is_purchasable: true,
    regular_price: '300',
    sale_price: null,
    course_duration: '3 hours',
    last_updated: '2021-09-03',
    total_enrolled: 300,
  },
];

const Layout = () => {
  const form = useFormWithGlobalError<CourseBundle>({
    defaultValues: {
      courses: mockData,
    },
  });
  return (
    <FormProvider {...form}>
      <div css={styles.wrapper}>
        <Header />
        <CourseBundleContainer />
        <CourseSelection />
      </div>
    </FormProvider>
  );
};

export default Layout;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.surface.courseBuilder};
  `,
  contentWrapper: css`
    display: flex;
    max-width: ${containerMaxWidth}px;
    width: 100%;
    min-height: calc(100vh - ${headerHeight}px);
    margin: 0 auto;

    ${Breakpoint.smallTablet} {
      padding-inline: ${spacing[12]};
      padding-bottom: ${spacing[56]};
    }
  `,
};
