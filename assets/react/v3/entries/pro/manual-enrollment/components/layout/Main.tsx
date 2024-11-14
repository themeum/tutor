import FormSelectInput from '@Components/fields/FormSelectInput';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import FormSelectCourse from '@EnrollmentComponents/FormSelectCourse';
import FormSelectStudents from '@EnrollmentComponents/FormSelectStudents';
import type { Enrollment } from '@EnrollmentServices/enrollment';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { requiredRule } from '@Utils/validation';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, FormProvider } from 'react-hook-form';
import Topbar, { TOPBAR_HEIGHT } from './Topbar';
import { tutorConfig } from '@Config/config';
import Show from '@Controls/Show';

function Main() {
  const form = useFormWithGlobalError<Enrollment>({
    defaultValues: {
      course: null,
      students: [],
      payment_status: 'paid',
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
              <Controller
                name="course"
                control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => <FormSelectCourse {...controllerProps} />}
              />
              <Show when={tutorConfig.settings?.monetize_by === 'tutor' && course?.is_purchasable}>
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
              </Show>

              <Show when={isSubscriptionCourse}>
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
              </Show>
            </div>
            <div css={styles.right}>
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
    grid-template-columns: 255px 1fr;
    gap: ${spacing[24]};
  `,
  left: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
    padding-top: ${spacing[32]};
  `,
  right: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    padding-left: ${spacing[24]};
    padding-top: ${spacing[32]};
  `,
  studentsWrapper: css`
    background-color: ${colorTokens.background.white};
    border-radius: ${borderRadius[8]};
    padding: ${spacing[8]} ${spacing[16]} ${spacing[12]};
  `,
};
