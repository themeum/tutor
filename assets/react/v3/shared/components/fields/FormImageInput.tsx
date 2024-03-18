import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { rgba } from 'polished';
import FormFieldWrapper from './FormFieldWrapper';

export type Media = {
	id: number | null;
	url: string;
	title?: string;
};

type FormImageInputProps = {
	label?: string;
	onChange?: () => void;
	helpText?: string;
	buttonText?: string;
	infoText?: string;
} & FormControllerProps<Media | null>;

const FormImageInput = ({
	field,
	fieldState,
	label,
	helpText,
	buttonText = __('Upload Media', 'tutor'),
	infoText,
}: FormImageInputProps) => {
	const wpMedia = window.wp.media({
		library: { type: 'image' },
	});

	const fieldValue = field.value;

	const uploadHandler = () => {
		wpMedia.open();
	};

	wpMedia.on('select', () => {
		const attachment = wpMedia.state().get('selection').first().toJSON();
		const { id, url, title } = attachment;

		field.onChange({ id, url, title });
	});

	const clearHandler = () => {
		field.onChange({ id: null, url: '', title: '' });
	};

	return (
		<FormFieldWrapper label={label} field={field} fieldState={fieldState} helpText={helpText}>
			{() => {
				return (
					<div>
						<Show
							when={fieldValue?.url}
							fallback={
								<div css={styles.emptyMedia}>
									<SVGIcon name="addImage" width={32} height={32} />
									<Button variant="text" onClick={uploadHandler}>
										{buttonText}
									</Button>
									<p css={styles.infoTexts}>{infoText}</p>
								</div>
							}
						>
							{(url) => {
								return (
									<div css={styles.previewWrapper}>
										<img src={url} alt={fieldValue?.title} css={styles.imagePreview} />
										<div css={styles.hoverPreview} data-hover-buttons-wrapper>
											<Button variant="outlined" onClick={uploadHandler}>
												{__('Replace Image', 'tutor')}
											</Button>
											<Button variant="text" onClick={clearHandler}>
												{__('Remove', 'tutor')}
											</Button>
										</div>
									</div>
								);
							}}
						</Show>
					</div>
				);
			}}
		</FormFieldWrapper>
	);
};

export default FormImageInput;

const styles = {
	emptyMedia: css`
    width: 100%;
    height: 168px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: ${spacing[8]};
    border: 1px dashed ${colorTokens.stroke.border};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.bg.white};

    svg {
      color: ${colorTokens.icon.default};
    }

    &:hover svg {
      color: ${colorTokens.brand.blue};
    }
  `,
	infoTexts: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
	previewWrapper: css`
    width: 100%;
    height: 168px;
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    overflow: hidden;
    position: relative;
    background-color: ${colorTokens.bg.white};

    &:hover {
      [data-hover-buttons-wrapper] {
        opacity: 1;
      }
    }
  `,
	imagePreview: css`
    height: 100%;
    width: 100%;
    object-fit: cover;
  `,
	hoverPreview: css`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: ${spacing[8]};
    opacity: 0;
    position: absolute;
    inset: 0;
    background-color: ${rgba(colorTokens.color.black.main, 0.6)};
    border-radius: ${borderRadius[8]};

    button:first-of-type {
      box-shadow: ${shadow.button};
    }

    button:last-of-type {
      color: ${colorTokens.text.white};
      box-shadow: none;
    }
  `,
};
