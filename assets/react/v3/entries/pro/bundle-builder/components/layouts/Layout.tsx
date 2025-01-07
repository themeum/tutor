import { css } from '@emotion/react';
import { FormProvider } from 'react-hook-form';

import BundleContainer from '@/v3/entries/pro/bundle-builder/components/course-bundle/BundleContainer';
import Header from '@BundleBuilderComponents/layouts/header/Header';
import {
  convertBundleToFormData,
  defaultCourseBundleData,
  useGetBundleDetails,
  type BundleFormData,
} from '@BundleBuilderServices/bundle';
import { Breakpoint, colorTokens, containerMaxWidth, headerHeight, spacing } from '@TutorShared/config/styles';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { useEffect } from 'react';
import { getBundleId } from '../../utils/utils';

const bundleId = getBundleId();

const Layout = () => {
  const form = useFormWithGlobalError<BundleFormData>({
    defaultValues: defaultCourseBundleData,
    shouldFocusError: true,
  });
  const getCourseBundleDetailsQuery = useGetBundleDetails(bundleId);

  useEffect(() => {
    if (getCourseBundleDetailsQuery.data) {
      const convertedCourseBundleData = convertBundleToFormData(getCourseBundleDetailsQuery.data);
      form.reset(convertedCourseBundleData, {
        keepDirtyValues: true,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [getCourseBundleDetailsQuery.data]);

  return (
    <FormProvider {...form}>
      <div css={styles.wrapper}>
        <Header />
        <div css={styles.contentWrapper}>
          <BundleContainer />
        </div>
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
