import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import { __ } from '@wordpress/i18n';
import { rgba } from 'polished';
import FormFieldWrapper from './FormFieldWrapper';
import { styleUtils } from '@Utils/style-utils';
import { useEffect, useRef, useState } from 'react';
import ImageInput from '@Atoms/ImageInput';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { AnimationType } from '@Hooks/useAnimation';
import { css } from '@emotion/react';
import config, { tutorConfig } from '@Config/config';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { Controller } from 'react-hook-form';
import FormSelectInput from './FormSelectInput';
import FormTextareaInput from './FormTextareaInput';
import type { Option } from '@Utils/types';

export interface CourseVideo {
  source: string;
  source_video_id: string;
  poster: string;
  poster_url: string;
  source_external_url: string;
  source_shortcode: string;
  source_youtube: string;
  source_vimeo: string;
  source_embedded: string;
}

interface URLFormData {
  videoSource: string;
  videoUrl: string;
}

type FormVideoInputProps = {
  label?: string;
  onChange?: (video: CourseVideo | null) => void;
  helpText?: string;
  buttonText?: string;
  infoText?: string;
  supportedFormats?: string[];
} & FormControllerProps<CourseVideo | null>;

const videoSources = Array.isArray(tutorConfig.settings.supported_video_sources)
  ? tutorConfig.settings.supported_video_sources
  : [tutorConfig.settings.supported_video_sources];

const videoSourceOptions = videoSources.reduce((options, source) => {
  let option: Option<string> | undefined;
  switch (source) {
    case 'external_url':
      option = { label: __('External URL', 'tutor'), value: 'external' };
      break;
    case 'shortcode':
      option = { label: __('Shortcode', 'tutor'), value: 'shortcode' };
      break;
    case 'youtube':
      option = { label: __('YouTube', 'tutor'), value: 'youtube' };
      break;
    case 'vimeo':
      option = { label: __('Vimeo', 'tutor'), value: 'vimeo' };
      break;
    case 'embedded':
      option = { label: __('Embedded', 'tutor'), value: 'embedded' };
      break;
    default:
      option = undefined;
  }
  if (option) options.push(option);
  return options;
}, [] as Option<string>[]);

const updateFieldValue = (fieldValue: CourseVideo | null, update: Partial<CourseVideo>) => {
  const defaultValue = {
    source: 'html5',
    source_video_id: '',
    poster: '',
    poster_url: '',
    source_external_url: '',
    source_shortcode: '',
    source_youtube: '',
    source_vimeo: '',
    source_embedded: '',
  };
  return fieldValue ? { ...fieldValue, ...update } : { ...defaultValue, ...update };
};

const FormVideoInput = ({
  field,
  fieldState,
  label,
  helpText,
  buttonText = __('Upload Media', 'tutor'),
  infoText,
  onChange,
  supportedFormats,
}: FormVideoInputProps) => {
  const form = useFormWithGlobalError<URLFormData>();

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (field.value) {
      form.setValue('videoSource', field.value.source);

      switch (field.value.source) {
        case 'external_url':
          form.setValue('videoUrl', field.value.source_external_url);
          break;
        case 'shortcode':
          form.setValue('videoUrl', field.value.source_shortcode);
          break;
        case 'youtube':
          form.setValue('videoUrl', field.value.source_youtube);
          break;
        case 'vimeo':
          form.setValue('videoUrl', field.value.source_vimeo);
          break;
        case 'embedded':
          form.setValue('videoUrl', field.value.source_embedded);
          break;
        default:
          break;
      }
    }
  }, [field.value]);

  const [isOpen, setIsOpen] = useState(false);
  const triggerRef = useRef<HTMLDivElement>(null);
  const { popoverRef, triggerWidth, position } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    triggerRef,
    positionModifier: {
      top: -(triggerRef.current?.offsetHeight || 0) - 9, // 9 is the gap between the trigger and the popover
      left: 0,
    },
  });
  const videoUploader = window.wp.media({
    library: { type: supportedFormats ? supportedFormats.map((type) => `video/${type}`).join(',') : 'video' },
  });

  const posterUploader = window.wp.media({
    library: { type: 'image' },
  });

  const fieldValue = field.value;
  const isVideoAvailable = () => {
    switch (fieldValue?.source) {
      case 'html5':
        return fieldValue.source_video_id !== '';
      case 'external_url':
        return fieldValue.source_external_url !== '';
      case 'shortcode':
        return fieldValue.source_shortcode !== '';
      case 'youtube':
        return fieldValue.source_youtube !== '';
      case 'vimeo':
        return fieldValue.source_vimeo !== '';
      case 'embedded':
        return fieldValue.source_embedded !== '';
      default:
        return false;
    }
  };

  const showPosterInput = fieldValue?.source === 'html5';

  const videoUploadHandler = () => {
    videoUploader.open();
  };

  const posterUploadHandler = () => {
    posterUploader.open();
  };

  videoUploader.on('select', () => {
    const attachment = videoUploader.state().get('selection').first().toJSON();
    const { id } = attachment;

    field.onChange(updateFieldValue(fieldValue, { source: 'html5', source_video_id: id }));

    if (onChange) {
      onChange(
        updateFieldValue(fieldValue, {
          source: 'html5',
          source_video_id: id,
        })
      );
    }
  });

  posterUploader.on('select', () => {
    const attachment = posterUploader.state().get('selection').first().toJSON();
    const { id, url } = attachment;

    field.onChange(
      updateFieldValue(fieldValue, {
        poster: id,
        poster_url: url,
      })
    );

    if (onChange) {
      onChange(
        updateFieldValue(fieldValue, {
          poster: id,
          poster_url: url,
        })
      );
    }
  });

  const videoClearHandler = () => {
    const currentVideoSource = fieldValue?.source;

    const updatedValue: CourseVideo = fieldValue
      ? { ...fieldValue }
      : {
          source: 'html5',
          source_video_id: '',
          poster: '',
          poster_url: '',
          source_external_url: '',
          source_shortcode: '',
          source_youtube: '',
          source_vimeo: '',
          source_embedded: '',
        };

    switch (currentVideoSource) {
      case 'html5':
        updatedValue.source_video_id = '';
        break;
      case 'external_url':
        updatedValue.source_external_url = '';
        break;
      case 'shortcode':
        updatedValue.source_shortcode = '';
        break;
      case 'youtube':
        updatedValue.source_youtube = '';
        break;
      case 'vimeo':
        updatedValue.source_vimeo = '';
        break;
      case 'embedded':
        updatedValue.source_embedded = '';
        break;
      default:
        break;
    }

    field.onChange(updatedValue);

    if (onChange) {
      onChange(updatedValue);
    }
  };

  const posterClearHandler = () => {
    field.onChange(
      updateFieldValue(fieldValue, {
        poster: '',
        poster_url: '',
      })
    );

    if (onChange) {
      onChange(
        updateFieldValue(fieldValue, {
          poster: '',
          poster_url: '',
        })
      );
    }
  };

  const handleDataFromUrl = (data: URLFormData) => {
    let source = '';
    let embedded = '';
    let externalUrl = '';
    let shortcode = '';
    let vimeo = '';
    let youtube = '';

    switch (data.videoSource) {
      case 'external':
        source = 'external_url';
        externalUrl = data.videoUrl;
        break;
      case 'shortcode':
        source = 'shortcode';
        shortcode = data.videoUrl;
        break;
      case 'youtube':
        source = 'youtube';
        youtube = data.videoUrl;
        break;
      case 'vimeo':
        source = 'vimeo';
        vimeo = data.videoUrl;
        break;
      case 'embedded':
        source = 'embedded';
        embedded = data.videoUrl;
        break;
      default:
        source = 'external_url';
        externalUrl = data.videoUrl;
        break;
    }

    field.onChange(
      updateFieldValue(fieldValue, {
        source,
        source_embedded: embedded,
        source_external_url: externalUrl,
        source_shortcode: shortcode,
        source_vimeo: vimeo,
        source_youtube: youtube,
      })
    );

    if (onChange) {
      onChange(
        updateFieldValue(fieldValue, {
          source,
          source_embedded: embedded,
          source_external_url: externalUrl,
          source_shortcode: shortcode,
          source_vimeo: vimeo,
          source_youtube: youtube,
        })
      );
    }

    setIsOpen(false);
  };

  return (
    <>
      <FormFieldWrapper label={label} field={field} fieldState={fieldState} helpText={helpText}>
        {() => {
          return (
            <div>
              <Show
                when={isVideoAvailable()}
                fallback={
                  <div
                    ref={triggerRef}
                    css={styles.emptyMedia({
                      hasVideoSource: videoSources.length > 0,
                    })}
                  >
                    <Show
                      when={videoSources.length > 0}
                      fallback={
                        <>
                          <p css={styles.warningText}>
                            <SVGIcon name="info" height={20} width={20} />
                            {__('No video source selected', 'tutor')}
                          </p>

                          <Button
                            buttonCss={styles.selectFromSettingsButton}
                            variant="secondary"
                            size="small"
                            icon={<SVGIcon name="linkExternal" height={24} width={24} />}
                            onClick={() => {
                              window.open(config.VIDEO_SOURCES_SETTINGS_URL, '_blank');
                            }}
                          >
                            {__('Select from settings', 'tutor')}
                          </Button>
                        </>
                      }
                    >
                      <Show when={videoSources.includes('html5')}>
                        <Button
                          variant="secondary"
                          icon={<SVGIcon name="monitorPlay" height={24} width={24} />}
                          onClick={videoUploadHandler}
                        >
                          {buttonText}
                        </Button>
                      </Show>
                      <Show when={videoSources.filter((source) => source !== 'html5').length > 0}>
                        <button
                          type="button"
                          css={styles.urlButton}
                          onClick={() => {
                            setIsOpen((prev) => !prev);
                          }}
                        >
                          {__('Add from URL', 'tutor')}
                        </button>
                      </Show>

                      <p css={styles.infoTexts}>{infoText}</p>
                    </Show>
                  </div>
                }
              >
                {(media) => {
                  return (
                    <div css={styles.previewWrapper}>
                      <div css={styles.videoInfoWrapper}>
                        <div css={styles.videoInfoCard}>
                          <SVGIcon name="video" height={40} width={40} />

                          <div css={styles.videoInfo}>
                            <div css={styles.videoInfoTitle}>
                              <div css={styleUtils.text.ellipsis(1)}>{__('Video', 'tutor')}</div>

                              <div css={styles.fileExtension}>{`.${__('mp4', 'tutor')}`}</div>
                            </div>
                          </div>
                        </div>

                        <button type="button" css={styles.removeButton} onClick={videoClearHandler}>
                          <SVGIcon name="cross" height={24} width={24} />
                        </button>
                      </div>
                      <div css={styles.imagePreview}>
                        <Show
                          when={showPosterInput}
                          fallback={
                            <div css={styles.urlData}>
                              <p>
                                {`
                                ${__('Source', 'tutor')}: 
                                ${
                                  videoSourceOptions.find((option) => option.value === form.watch('videoSource'))
                                    ?.label || ''
                                }`}
                              </p>
                              <p>{`${__('URL', 'tutor')}: ${form.watch('videoUrl')}`}</p>
                            </div>
                          }
                        >
                          <ImageInput
                            value={
                              fieldValue
                                ? {
                                    id: Number(fieldValue.poster),
                                    url: fieldValue.poster_url,
                                    title: '',
                                  }
                                : null
                            }
                            uploadHandler={posterUploadHandler}
                            clearHandler={posterClearHandler}
                            buttonText={__('Upload Poster', 'tutor')}
                            infoText={__('Upload a poster for the video', 'tutor')}
                            emptyImageCss={styles.thumbImage}
                            previewImageCss={styles.thumbImage}
                            overlayCss={styles.thumbImage}
                            replaceButtonText={__('Replace Poster', 'tutor')}
                          />
                        </Show>
                      </div>
                    </div>
                  );
                }}
              </Show>
            </div>
          );
        }}
      </FormFieldWrapper>
      <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)} animationType={AnimationType.fadeIn}>
        <div
          ref={popoverRef}
          css={[
            styles.popover,
            {
              left: position.left,
              top: position.top,
              maxWidth: triggerWidth,
            },
          ]}
        >
          <div css={styles.popoverContent}>
            <Controller
              control={form.control}
              name="videoSource"
              rules={{ required: __('This field is required', 'tutor') }}
              render={(controllerProps) => {
                return (
                  <FormSelectInput
                    {...controllerProps}
                    options={videoSourceOptions}
                    placeholder={__('Select source', 'tutor')}
                  />
                );
              }}
            />
            <Controller
              control={form.control}
              name="videoUrl"
              rules={{ required: __('This field is required', 'tutor') }}
              render={(controllerProps) => {
                return <FormTextareaInput {...controllerProps} rows={2} placeholder={__('https://')} />;
              }}
            />

            <div css={styles.popoverButtonWrapper}>
              <Button
                variant="text"
                size="small"
                onClick={() => {
                  setIsOpen(false);
                }}
              >
                {__('Cancel', 'tutor')}
              </Button>
              <Button variant="secondary" size="small" onClick={form.handleSubmit(handleDataFromUrl)}>
                {__('Ok', 'tutor')}
              </Button>
            </div>
          </div>
        </div>
      </Portal>
    </>
  );
};

export default FormVideoInput;

const styles = {
  emptyMedia: ({
    hasVideoSource,
  }: {
    hasVideoSource: boolean;
  }) => css`
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
    background-color: ${colorTokens.background.status.warning};

    ${
      hasVideoSource &&
      css`
        background-color: ${colorTokens.bg.white};
      `
    }
  `,
  infoTexts: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
  warningText: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[4]};
    ${typography.caption()};
    color: ${colorTokens.text.warning};
  `,
  selectFromSettingsButton: css`
    background: ${colorTokens.bg.white};
  `,
  urlData: css`
    ${styleUtils.display.flex('column')};
    padding: ${spacing[8]} ${spacing[12]};
    gap: ${spacing[8]};
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

    svg {
      color: ${colorTokens.icon.hover};
    }
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
    border-top-right-radius: 0;
    border-top-left-radius: 0;
    border: none;
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
  popover: css`
    position: absolute;
    width: 100%;
    z-index: ${zIndex.dropdown};
    background-color: ${colorTokens.bg.white};
    border-radius: ${borderRadius.card};
    box-shadow: ${shadow.popover};
  `,
  popoverContent: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    padding: ${spacing[16]};
  `,
  popoverButtonWrapper: css`
    ${styleUtils.display.flex()};
    gap: ${spacing[8]};
    justify-content: flex-end;
  `,
};