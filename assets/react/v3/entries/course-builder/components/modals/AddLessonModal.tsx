import React from 'react';
import ModalWrapper from '@Components/modals/ModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { css } from '@emotion/react';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { Controller } from 'react-hook-form';
import FormInput from '@Components/fields/FormInput';
import { __ } from '@wordpress/i18n';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import FormImageInput, { type Media } from '@Components/fields/FormImageInput';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { typography } from '@Config/typography';
import FormSwitch from '@Components/fields/FormSwitch';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import FormInputWithContent from '@Components/fields/FormInputWithContent';

interface AddLessonModalProps extends ModalProps {
	closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

interface AddLessonForm {
	lesson_name: string;
	description: string;
	featured_image: Media | null;
	duration_hour: number;
	duration_min: number;
	duration_sec: number;
	available_after_days: number;
	lesson_preview: boolean;
}

const AddLessonModal = ({ closeModal, icon, title, subtitle }: AddLessonModalProps) => {
	const form = useFormWithGlobalError<AddLessonForm>();

	const onSubmit = (data: AddLessonForm) => {
		console.log(data);
		closeModal({ action: 'CONFIRM' });
	};

	return (
		<ModalWrapper
			onClose={() => closeModal({ action: 'CLOSE' })}
			icon={icon}
			title={title}
			subtitle={subtitle}
			actions={
				<>
					<Button variant="text" size="small" onClick={() => closeModal({ action: 'CLOSE' })}>
						Cancel
					</Button>
					<Button variant="primary" size="small" onClick={form.handleSubmit(onSubmit)}>
						Save
					</Button>
				</>
			}
		>
			<div css={styles.wrapper}>
				<div>
					<div css={styles.lessonInfo}>
						<Controller
							name="lesson_name"
							control={form.control}
							render={(controllerProps) => (
								<FormInput
									{...controllerProps}
									label={__('Lesson Name', 'tutor')}
									placeholder={__('Enter Lesson Name', 'tutor')}
									maxLimit={245}
									isClearable
								/>
							)}
						/>
						<Controller
							name="description"
							control={form.control}
							render={(controllerProps) => (
								<FormTextareaInput
									{...controllerProps}
									label={__('Description', 'tutor')}
									placeholder={__('Enter Lesson Description', 'tutor')}
								/>
							)}
						/>
					</div>
				</div>

				<div css={styles.rightPanel}>
					<Controller
						name="featured_image"
						control={form.control}
						render={(controllerProps) => (
							<FormImageInput
								{...controllerProps}
								label={__('Featured Image', 'tutor')}
								buttonText={__('Upload Course Thumbnail', 'tutor')}
								infoText={__('Size: 700x430 pixels', 'tutor')}
							/>
						)}
					/>
					{/* // @TODO: Need to add FormVideo component when its implemented*/}
					{/* <Controller
              name='thumbnail'
              control={form.control}
              render={(controllerProps) => (
                <FormImageInput
                  {...controllerProps}
                  label={__('Featured Image', 'tutor')}
                  buttonText={__('Upload Course Thumbnail', 'tutor')}
                  infoText={__('Size: 700x430 pixels', 'tutor')}
                />
              )}
            /> */}
					<div css={styles.durationWrapper}>
						<span css={styles.additoinLabel}>{__('Video playback time', 'tutor')}</span>
						<div css={styles.duration}>
							<Controller
								name="duration_hour"
								control={form.control}
								render={(controllerProps) => (
									<FormInputWithContent
										{...controllerProps}
										type="number"
										content={<span css={styles.durationContent}>{__('hour', 'tutor')}</span>}
										contentPosition="right"
										placeholder="0"
										showVerticalBar={false}
									/>
								)}
							/>
							<Controller
								name="duration_min"
								control={form.control}
								render={(controllerProps) => (
									<FormInputWithContent
										{...controllerProps}
										type="number"
										content={<span css={styles.durationContent}>{__('min', 'tutor')}</span>}
										contentPosition="right"
										placeholder="0"
										showVerticalBar={false}
									/>
								)}
							/>
							<Controller
								name="duration_sec"
								control={form.control}
								render={(controllerProps) => (
									<FormInputWithContent
										{...controllerProps}
										type="number"
										content={<span css={styles.durationContent}>{__('sec', 'tutor')}</span>}
										contentPosition="right"
										placeholder="0"
										showVerticalBar={false}
									/>
								)}
							/>
						</div>
					</div>

					<Controller
						name="available_after_days"
						control={form.control}
						render={(controllerProps) => (
							<FormInput
								{...controllerProps}
								type="number"
								label={__('Available after days', 'tutor')}
								helpText={__('Set the number of days after which the lesson will be available', 'tutor')}
								placeholder="0"
							/>
						)}
					/>

					<div css={styles.uploadAttachment}>
						<span css={styles.additoinLabel}>{__('Exercise Files', 'tutor')}</span>
						<Button icon={<SVGIcon name="attach" height={24} width={24} />} variant="secondary">
							Upload Attachment
						</Button>
					</div>

					<div css={styles.lessonPreview}>
						<Controller
							name="lesson_preview"
							control={form.control}
							render={(controllerProps) => (
								<FormSwitch
									{...controllerProps}
									label={__('Lesson preview', 'tutor')}
									helpText={__('Show preview', 'tutor')}
								/>
							)}
						/>
					</div>
				</div>
			</div>
		</ModalWrapper>
	);
};

export default AddLessonModal;

const styles = {
	wrapper: css`
    width: 1217px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 395px;
    height: 100%;
    padding-inline: ${spacing[32]};
  `,
	lessonInfo: css`
    padding-block: ${spacing[20]};
    padding-right: ${spacing[32]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
    position: sticky;
    top: 0;
  `,
	rightPanel: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    padding-block: ${spacing[20]};
    padding-left: ${spacing[32]};
  `,
	durationWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
	duration: css`
    display: flex;
    align-items: flex-end;
    gap: ${spacing[8]};
  `,
	durationContent: css`
    ${typography.small()};
    color: ${colorTokens.text.hints};
  `,

	uploadAttachment: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
	additoinLabel: css`
    ${typography.body()}
    color: ${colorTokens.text.title};
  `,
	lessonPreview: css`
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
  `,
};
