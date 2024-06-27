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
import type { Media } from '@Components/fields/FormImageInput';
import { styleUtils } from '@Utils/style-utils';
import { formatBytes, formatSeconds } from '@Utils/util';
import { format } from 'date-fns';
import { DateFormats } from '@Config/constants';

type FormVideoInputProps = {
  label?: string;
  onChange?: (media: Media | null) => void;
  helpText?: string;
  buttonText?: string;
  infoText?: string;
  supportedFormats?: string[];
  fromUrl?: boolean;
} & FormControllerProps<Media | null>;

const FormVideoInput = ({
  field,
  fieldState,
  label,
  helpText,
  buttonText = __('Upload Media', 'tutor'),
  infoText,
  onChange,
  supportedFormats,
  fromUrl = false,
}: FormVideoInputProps) => {
  const wpMedia = window.wp.media({
    library: { type: supportedFormats ? supportedFormats.map((type) => `video/${type}`).join(',') : 'video' },
  });

  const fieldValue = field.value;

  const uploadHandler = () => {
    wpMedia.open();
  };

  wpMedia.on('select', () => {
    const attachment = wpMedia.state().get('selection').first().toJSON();
    console.log(attachment);
    const { id, url, title, subtype, date, filesizeInBytes } = attachment;

    const video = document.createElement('video');
    video.src = url;
    video.style.display = 'none';

    // Function to clean up video and canvas elements
    const cleanUp = () => {
      video.removeEventListener('loadedmetadata', onLoadedMetadata);
      video.removeEventListener('seeked', onSeeked);
      if (document.body.contains(video)) {
        document.body.removeChild(video);
      }
    };

    const onSeeked = () => {
      // Create a canvas to capture the thumbnail
      const canvas = document.createElement('canvas');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      const context = canvas.getContext('2d');

      if (!context) {
        return;
      }

      context.drawImage(video, 0, 0, canvas.width, canvas.height);

      // Get the thumbnail data URL
      const thumbnail = canvas.toDataURL('image/png');

      // Handle the video details, including duration and thumbnail
      const videoDetails = {
        id,
        url,
        title,
        duration: video.duration,
        subtype,
        date,
        filesizeInBytes,
        sizes: {
          thumbnail: {
            url: thumbnail,
            height: canvas.height,
            width: canvas.width,
          },
          full: {
            url: thumbnail,
            height: canvas.height,
            width: canvas.width,
          },
          medium: {
            url: thumbnail,
            height: canvas.height,
            width: canvas.width,
          },
          large: {
            url: thumbnail,
            height: canvas.height,
            width: canvas.width,
          },
        },
      };

      field.onChange(videoDetails);

      if (onChange) {
        onChange(videoDetails);
      }

      // Clean up the video and canvas elements
      cleanUp();
    };

    const onLoadedMetadata = () => {
      // Set the current time to capture the thumbnail (e.g., at 1 second)
      video.currentTime = 1;
    };

    // Listen for loadedmetadata event
    video.addEventListener('loadedmetadata', onLoadedMetadata);
    video.addEventListener('seeked', onSeeked);

    // Append the video element to the body to load it
    document.body.appendChild(video);
  });

  const clearHandler = () => {
    field.onChange(null);

    if (onChange) {
      onChange(null);
    }
  };

  return (
    <FormFieldWrapper label={label} field={field} fieldState={fieldState} helpText={helpText}>
      {() => {
        return (
          <div>
            <Show
              when={fieldValue}
              fallback={
                <div css={styles.emptyMedia}>
                  <Button
                    variant="secondary"
                    icon={<SVGIcon name="monitorPlay" height={24} width={24} />}
                    onClick={uploadHandler}
                  >
                    {buttonText}
                  </Button>
                  <button type="button" css={styles.urlButton}>
                    {__('Add from URL', 'tutor')}
                  </button>

                  <p css={styles.infoTexts}>{infoText}</p>
                </div>
              }
            >
              {(media) => {
                console.log(media);
                return (
                  <div css={styles.previewWrapper}>
                    <div css={styles.videoInfoWrapper}>
                      <div css={styles.videoInfoCard}>
                        <SVGIcon name="preview" height={40} width={40} />

                        <div css={styles.videoInfo}>
                          <div css={styles.videoInfoTitle}>
                            <div css={styleUtils.text.ellipsis(1)}>{media.title}</div>

                            <div css={styles.fileExtension}>{`.${media.subtype}`}</div>
                          </div>

                          <div css={styles.videoInfoSubtitle}>
                            <span>{`${__('Size', 'tutor')}: ${formatBytes(media?.filesizeInBytes || 0)}`}</span>

                            <SVGIcon name="dot" height={2} width={2} />

                            {media.date && (
                              <span>{format(new Date(media.date), DateFormats.monthDayYearHoursMinutes)}</span>
                            )}
                          </div>
                        </div>
                      </div>

                      <button type="button" css={styles.removeButton} onClick={clearHandler}>
                        <SVGIcon name="cross" height={24} width={24} />
                      </button>
                    </div>
                    <div css={styles.imagePreview}>
                      <img src={media.sizes?.thumbnail.url} alt={fieldValue?.title} css={styles.thumbImage} />

                      <div css={styles.duration}>{formatSeconds(media.duration || 0)}</div>

                      <div css={styles.hoverPreview} data-hover-buttons-wrapper>
                        <Button variant="secondary" onClick={uploadHandler}>
                          {__('Replace Image', 'tutor')}
                        </Button>
                      </div>
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

export default FormVideoInput;

const styles = {
  emptyMedia: css`
    width: 100%;
    height: 100%;
    min-height: 168px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: ${spacing[8]};
    border: 1px dashed ${colorTokens.stroke.border};
    border-radius: ${borderRadius[8]};
    background-color: ${colorTokens.bg.white};
  `,
  infoTexts: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
  previewWrapper: css`
    width: 100%;
    height: 100%;
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    overflow: hidden;
  `,
  videoInfoWrapper: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;
    gap: ${spacing[20]};
    padding: ${spacing[8]} ${spacing[12]};
  `,
  videoInfoCard: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
  `,
  videoInfo: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  videoInfoTitle: css`
    ${styleUtils.display.flex()};
    ${typography.caption('medium')}
    word-break: break-all;
  `,
  fileExtension: css`
    flex-shrink: 0;
  `,
  videoInfoSubtitle: css`
    ${typography.tiny('regular')}
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
    color: ${colorTokens.text.hints};

    svg {
      color: ${colorTokens.icon.default};
    }
  `,
  imagePreview: css`
    width: 100%;
    max-height: 168px;
    position: relative;
    overflow: hidden;
    background-color: ${colorTokens.bg.white};

    &:hover {
      [data-hover-buttons-wrapper] {
        opacity: 1;
      }
    }
  `,
  thumbImage: css`
    width: 100%;
    object-fit: cover;
    object-position: center;
  `,
  duration: css`
    position: absolute;
    bottom: ${spacing[12]};
    right: ${spacing[12]};
    padding: ${spacing[2]} ${spacing[4]};
    ${typography.tiny('regular')};
    color: ${colorTokens.text.white};
    border-radius: ${borderRadius[2]};
    background-color: ${rgba(colorTokens.color.black.main, 0.5)};
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

    button {
      box-shadow: ${shadow.button};
    }
  `,
  urlButton: css`
    ${styleUtils.resetButton};
    ${typography.small('medium')};
    color: ${colorTokens.text.brand};
  `,
  removeButton: css`
    ${styleUtils.resetButton};
    align-self: flex-start;
    color: ${colorTokens.icon.default};
  `,
};
