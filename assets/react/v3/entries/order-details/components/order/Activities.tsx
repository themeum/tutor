import { Box, BoxTitle } from '@Atoms/Box';
import Button from '@Atoms/Button';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { requiredRule } from '@Utils/validation';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

function Activities() {
  const form = useFormWithGlobalError<{ comment: string }>({
    defaultValues: {
      comment: '',
    },
  });
  return (
    <Box>
      <BoxTitle separator tooltip="Order activities">
        {__('Order activities')}
      </BoxTitle>
      <div css={styles.content}>
        <div css={styles.activities}>
          <div css={styles.activityItem}>
            <span css={styles.dot} />
            <form
              onSubmit={form.handleSubmit((values) => {
                console.log(values);
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
                    label={__('Add a comment (Only you and other staff can see comments)', 'tutor')}
                    rows={5}
                  />
                )}
              />
              <Button type="submit" variant="primary" size="small" isOutlined>
                Post
              </Button>
            </form>
          </div>
          <div css={styles.activityItem}>
            <span css={styles.dot} />
            <div css={styles.innerContent}>
              <span>Nov 16, 2024 10:17 AM</span>
              <span>You sent an order invoice to</span>
              <span>example@gmail.com</span>
            </div>
          </div>
          <div css={styles.activityItem}>
            <span css={styles.dot} />
            <div css={styles.innerContent}>
              <span>Nov 16, 2024 10:17 AM</span>
              <span>Nikola Tesla placed an order</span>
            </div>
          </div>
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
