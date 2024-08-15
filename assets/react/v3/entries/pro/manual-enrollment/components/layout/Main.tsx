import Container from '@Components/Container';
import { colorTokens, spacing } from '@Config/styles';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { Controller, FormProvider } from 'react-hook-form';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';
import { __ } from '@wordpress/i18n';
import { Enrollment } from '@EnrollmentServices/enrollment';
import FormSelectInput from '@Components/fields/FormSelectInput';
import { requiredRule } from '@Utils/validation';
import Students from '@EnrollmentComponents/Students';
import SelectCourse from '@EnrollmentComponents/SelectCourse';

function Main() {
  const params = new URLSearchParams(window.location.search);
  const form = useFormWithGlobalError<Enrollment>({
    defaultValues: {
      course: null,
      students: [
        {
          id: 1,
          name: 'John Doe',
          email: 'example@example.com',
          avatar: 'http://1.gravatar.com/avatar/d93e0f5b9e6206a877ee7b6f0c008273?s=96&d=mm&r=g',
        },
        {
          id: 2,
          name: 'John Doe',
          email: 'example@example.com',
          avatar: 'http://1.gravatar.com/avatar/d93e0f5b9e6206a877ee7b6f0c008273?s=96&d=mm&r=g',
        },
        {
          id: 3,
          name: 'John Doe',
          email: 'example@example.com',
          avatar: 'http://1.gravatar.com/avatar/d93e0f5b9e6206a877ee7b6f0c008273?s=96&d=mm&r=g',
        },
        {
          id: 4,
          name: 'John Doe',
          email: 'example@example.com',
          avatar: 'http://1.gravatar.com/avatar/d93e0f5b9e6206a877ee7b6f0c008273?s=96&d=mm&r=g',
        },
      ],
      payment_status: '',
      subscription: '',
    },
  });

  const paymentStatusOptions = [
    {
      label: __('Paid', 'tutor'),
      value: 'paid',
    },
    {
      label: __('Unpaid', 'tutor'),
      value: 'unpaid',
    },
  ];

  const subscriptionOptions = [
    {
      label: __('One', 'tutor'),
      value: 'one',
    },
    {
      label: __('Two', 'tutor'),
      value: 'two',
    },
  ];

  return (
    <div css={styles.wrapper}>
      <FormProvider {...form}>
        <Topbar />
        <div css={styles.container}>
          <div css={styles.content}>
            <div css={styles.left}>
              <Students />
            </div>
            <div css={styles.right}>
              <SelectCourse />
              <Controller
                name="payment_status"
                control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => (
                  <FormSelectInput
                    {...controllerProps}
                    label={__('Payment Status', 'tutor')}
                    options={paymentStatusOptions}
                    placeholder={__('Select payment status', 'tutor')}
                  />
                )}
              />
              <Controller
                name="payment_status"
                control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => (
                  <FormSelectInput
                    {...controllerProps}
                    label={__('Course', 'tutor')}
                    options={paymentStatusOptions}
                    placeholder={__('Select course', 'tutor')}
                    isSearchable
                  />
                )}
              />

              <Controller
                name="payment_status"
                control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => (
                  <FormSelectInput
                    {...controllerProps}
                    label={__('Subscription', 'tutor')}
                    options={subscriptionOptions}
                    placeholder={__('Select subscription', 'tutor')}
                  />
                )}
              />
            </div>
          </div>
        </div>
      </FormProvider>
    </div>
  );
}

export default Main;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.background.default};
  `,
  container: css`
    max-width: 1030px;
    margin: 0 auto;
    height: 100%;
  `,
  content: css`
    min-height: calc(100vh - ${TOPBAR_HEIGHT}px);
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 255px;
    gap: ${spacing[24]};
  `,
  left: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    border-right: 1px solid ${colorTokens.stroke.divider};
    padding-right: ${spacing[24]};
    padding-top: ${spacing[32]};
  `,
  right: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
    padding-top: ${spacing[32]};
  `,
};
