import { useOrderContext } from '@OrderDetails/contexts/order-context';
import { useAdminCommentMutation } from '@OrderDetails/services/order';
import Alert from '@TutorShared/atoms/Alert';
import { Box, BoxTitle } from '@TutorShared/atoms/Box';
import Button from '@TutorShared/atoms/Button';
import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { requiredRule } from '@TutorShared/utils/validation';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

function Activities() {
  const { order } = useOrderContext();
  const adminCommentMutation = useAdminCommentMutation();
  const form = useFormWithGlobalError<{ comment: string }>({
    defaultValues: {
      comment: '',
    },
  });
  return (
    <Box bordered css={styleUtils.boxReset}>
      <BoxTitle separator tooltip={__('You can see all the activities against this order chronologically.', 'tutor')}>
        {__('Order activities', 'tutor')}
      </BoxTitle>
      <div css={styles.content}>
        <div css={styles.activities}>
          <div css={styles.activityItem}>
            <span css={styles.dot} />
            <form
              onSubmit={form.handleSubmit((values) => {
                adminCommentMutation.mutate({ order_id: order.id, comment: values.comment });
                form.reset();
              })}
              css={styles.form}
            >
              <Controller
                control={form.control}
                name="comment"
                rules={{ ...requiredRule() }}
                render={(props) => (
                  <FormTextareaInput
                    {...props}
                    label={__('Add a comment (Only admin can see comments.)', 'tutor')}
                    placeholder={__('Write a comment for this order...', 'tutor')}
                    rows={3}
                  />
                )}
              />
              <Button type="submit" variant="primary" size="small" isOutlined loading={adminCommentMutation.isPending}>
                {__('Post', 'tutor')}
              </Button>
            </form>
          </div>
          <Show when={order.activities}>
            {(activities) => (
              <For each={activities}>
                {(activity) => (
                  <div css={styles.activityItem} key={activity.id}>
                    <span css={styles.dot} />
                    <div css={styles.innerContent}>
                      <span>{activity.created_at_readable}</span>
                      <span>{activity.message}</span>
                      <Show when={activity.cancel_reason}>
                        <Alert type="info">{activity.cancel_reason}</Alert>
                      </Show>
                    </div>
                  </div>
                )}
              </For>
            )}
          </Show>
        </div>
      </div>
    </Box>
  );
}

export default Activities;

const styles = {
  content: css`
    padding: ${spacing[16]} ${spacing[24]};
  `,
  activities: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    padding-left: ${spacing[20]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
  form: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[20]};

    button {
      align-self: end;
    }
  `,
  dot: css`
    position: absolute;
    width: 16px;
    height: 16px;
    left: -28px;
    top: 2px;
    border-radius: ${borderRadius.circle};
    background-color: ${colorTokens.color.black[8]};

    &::before {
      content: '';
      position: absolute;
      width: 8px;
      height: 8px;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      border-radius: ${borderRadius.circle};
      background-color: ${colorTokens.icon.hover};
    }
  `,
  activityItem: css`
    position: relative;
    &:last-of-type::before {
      content: '';
      position: absolute;
      height: 100%;
      width: 16px;
      left: -28px;
      background: ${colorTokens.background.white};
    }

    &:first-of-type::before {
      content: '';
      position: absolute;
      height: 4px;
      width: 16px;
      left: -28px;
      top: 0;
      background: ${colorTokens.background.white};
    }
  `,
  innerContent: css`
    display: flex;
    flex-direction: column;

    ${typography.caption('medium')};
    color: ${colorTokens.text.primary};

    & > span:first-of-type {
      ${typography.small()};
      color: ${colorTokens.text.subdued};
    }
  `,
};
