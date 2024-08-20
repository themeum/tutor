import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { Controller, FormProvider } from 'react-hook-form';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';
import { Enrollment } from '@EnrollmentServices/enrollment';
import FormSelectInput from '@Components/fields/FormSelectInput';
import { requiredRule } from '@Utils/validation';
import FormSelectCourse from '@EnrollmentComponents/FormSelectCourse';
import FormSelectStudents from '@EnrollmentComponents/FormSelectStudents';
const { __ } = wp.i18n;

function Main() {
  const form = useFormWithGlobalError<Enrollment>({
    defaultValues: {
      course: null,
      students: [],
      payment_status: '',
      subscription: '',
    },
  });

  const course = form.watch('course');

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

  const isSubscriptionCourse = !!course?.plans?.length;

  const subscriptionOptions =
    course?.plans?.map((item) => {
      return {
        label: item.plan_name,
        value: item.id,
      };
    }) ?? [];

  return (
    <div css={styles.wrapper}>
      <FormProvider {...form}>
        <Topbar />
        <div css={styles.container}>
          <div css={styles.content}>
            <div css={styles.left}>
              <div css={styles.studentsWrapper}>
                <Controller
                  name="students"
                  control={form.control}
                  rules={requiredRule()}
                  render={(controllerProps) => (
                    <FormSelectStudents {...controllerProps} label={__('Students', 'tutor')} disabled={!course} />
                  )}
                />
              </div>
            </div>
            <div css={styles.right}>
              <Controller
                name="course"
                control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => <FormSelectCourse {...controllerProps} />}
              />
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

              {isSubscriptionCourse && (
                <Controller
                  name="subscription"
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
              )}
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
  studentsWrapper: css`
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius[8]};
    padding: ${spacing[8]} ${spacing[16]} ${spacing[12]};
  `,
};
