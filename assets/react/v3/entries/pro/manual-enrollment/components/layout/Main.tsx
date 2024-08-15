import Container from '@Components/Container';
import { colorTokens, spacing } from '@Config/styles';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { Controller, FormProvider } from 'react-hook-form';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';
import { __ } from '@wordpress/i18n';
import { Enrollment } from '@EnrollmentServices/enrollment';
import Courses from '@EnrollmentComponents/Courses';
import FormSelectInput from '@Components/fields/FormSelectInput';
import { requiredRule } from '@Utils/validation';

function Main() {
  const params = new URLSearchParams(window.location.search);
  const form = useFormWithGlobalError<Enrollment>({
    defaultValues: {
      courses: [],
      students: [],
      status: '',
      subscription: '',
    },
  });

  return (
    <div css={styles.wrapper}>
      <FormProvider {...form}>
        <Topbar />
        <Container>
          <div css={styles.content}>
            <div css={styles.left}>
              <Courses />
            </div>
            <div css={styles.right}>
              <Controller
                name="coupon_title"
                //   control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => (
                  <FormSelectInput {...controllerProps} label={__('Status', 'tutor')} options={[]} />
                )}
              />
            </div>
          </div>
        </Container>
      </FormProvider>
    </div>
  );
}

export default Main;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.default};
  `,

  content: css`
    min-height: calc(100vh - ${TOPBAR_HEIGHT}px);
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 342px;
    gap: ${spacing[36]};
    margin-top: ${spacing[32]};
  `,
  left: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  right: css``,
};
