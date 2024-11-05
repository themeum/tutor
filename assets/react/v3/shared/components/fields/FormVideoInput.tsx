import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';
import ImageInput from '@Atoms/ImageInput';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';

import config, { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useGetYouTubeVideoDuration } from '@CourseBuilderServices/course';
import {
  convertYouTubeDurationToSeconds,
  covertSecondsToHMS,
  getExternalVideoDuration,
  getVimeoVideoDuration,
} from '@CourseBuilderUtils/utils';
import { AnimationType } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { requiredRule } from '@Utils/validation';

import FormFieldWrapper from './FormFieldWrapper';
import FormSelectInput from './FormSelectInput';
import FormTextareaInput from './FormTextareaInput';

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
  [key: string]: string | undefined;
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
  loading?: boolean;
  onGetDuration?: (duration: { hours: number; minutes: number; seconds: number }) => void;
} & FormControllerProps<CourseVideo | null>;

const videoSourceOptions = tutorConfig.supported_video_sources || [];
const videoSourcesSelectOptions = videoSourceOptions.filter((option) => option.value !== 'html5');
const videoSources = videoSourceOptions.map((item) => item.value);

const placeholderMap = {
  youtube: __('Paste YouTube Video URL', 'tutor'),
  vimeo: __('Paste Vimeo Video URL', 'tutor'),
  external_url: __('Paste External Video URL', 'tutor'),
  shortcode: __('Paste Video Shortcode', 'tutor'),
  embedded: __('Paste Embedded Video Code', 'tutor'),
};

const updateFieldValue = (fieldValue: CourseVideo | null, update: Partial<CourseVideo>) => {
  const defaultValue = {
    source: '',
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
  loading,
  onGetDuration,
}: FormVideoInputProps) => {
  if (!videoSources.length) {
    return (
      <div
        css={styles.emptyMedia({
          hasVideoSource: false,
        })}
      >
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
            window.open(config.VIDEO_SOURCES_SETTINGS_URL, '_blank', 'noopener');
          }}
        >
          {__('Select from settings', 'tutor')}
        </Button>
      </div>
    );
  }

  const fieldValue = field.value;
  const form = useFormWithGlobalError<URLFormData>({
    defaultValues: {
      videoSource: videoSourcesSelectOptions[0]?.value || '',
      videoUrl: '',
    },
  });
  const getYouTubeVideoDurationMutation = useGetYouTubeVideoDuration();

  const videoSource = form.watch('videoSource') || '';

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (!fieldValue) {
      return;
    }

    if (!fieldValue.source) {
      form.setValue('videoSource', videoSourcesSelectOptions[0]?.value);
      form.setValue('videoUrl', fieldValue[`source_${videoSourcesSelectOptions[0]?.value}` as keyof CourseVideo] || '');
      return;
    }

    const isVideoSourceAvailable = videoSources.includes(fieldValue.source);

    if (!isVideoSourceAvailable) {
      field.onChange(updateFieldValue(fieldValue, { source: '' }));
      return;
    }

    form.setValue('videoSource', fieldValue.source);
    form.setValue('videoUrl', fieldValue[`source_${fieldValue.source}` as keyof CourseVideo] || '');
  }, [fieldValue]);

  const [isOpen, setIsOpen] = useState(false);
  const triggerRef = useRef<HTMLDivElement>(null);
  const { popoverRef, position } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    triggerRef,
    positionModifier: {
      top: triggerRef.current?.getBoundingClientRect().top || 0,
      left: 0,
    },
  });

  const handleUpload = (type: 'video' | 'poster') => {
    const uploader = window.wp.media({
      library: {
        type: type === 'video' ? (supportedFormats || []).map((format) => `video/${format}`).join(',') : 'image',
      },
    });

    uploader.open();
    uploader.on('select', () => {
      const attachment = uploader.state().get('selection').first().toJSON();
      const updateData =
        type === 'video'
          ? { source: 'html5', source_video_id: attachment.id }
          : { poster: attachment.id, poster_url: attachment.url };

      if (type === 'video' && onGetDuration) {
        getExternalVideoDuration(attachment.url).then((duration) => {
          if (duration) {
            onGetDuration(covertSecondsToHMS(Math.floor(duration)));
          }
        });
      }

      field.onChange(updateFieldValue(fieldValue, updateData));
      onChange?.(updateFieldValue(fieldValue, updateData));
    });
  };

  const handleClear = (type: 'video' | 'poster') => {
    const updateData = type === 'video' ? { source: '' } : { poster: '', poster_url: '' };
    field.onChange(updateFieldValue(fieldValue, updateData));
    if (onChange) {
      onChange(updateFieldValue(fieldValue, updateData));
    }
  };

  const isVideoAvailable = () => {
    if (!fieldValue?.source || !videoSources.includes(fieldValue.source)) {
      return false;
    }
    const sourceType = fieldValue?.source;
    const videoIdKey = `source_${sourceType}` as keyof CourseVideo;
    return fieldValue && fieldValue[videoIdKey] !== '';
  };

  const handleDataFromUrl = async (data: URLFormData) => {
    const source = data.videoSource;
    const updatedValue = {
      source,
      [`source_${source}`]: data.videoUrl,
    };

    field.onChange(updateFieldValue(fieldValue, updatedValue));
    onChange?.(updateFieldValue(fieldValue, updatedValue));
    setIsOpen(false);

    try {
      if (source === 'vimeo') {
        const duration = await getVimeoVideoDuration(data.videoUrl);
        if (onGetDuration && duration) {
          onGetDuration(covertSecondsToHMS(Math.floor(duration)));
        }
      }

      if (source === 'external_url') {
        const duration = await getExternalVideoDuration(data.videoUrl);

        if (onGetDuration && duration) {
          onGetDuration(covertSecondsToHMS(Math.floor(duration)));
        }
      }

      if (source === 'youtube') {
        const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
        const match = data.videoUrl.match(regExp);
        const videoId = match && match[7].length === 11 ? match[7] : '';
        const response = await getYouTubeVideoDurationMutation.mutateAsync(videoId);

        const duration = response.data.duration;
        const seconds = convertYouTubeDurationToSeconds(duration);

        if (onGetDuration && duration) {
          onGetDuration(covertSecondsToHMS(Math.floor(seconds)));
        }
      }
    } catch (error) {
      console.error(error);
    }
  };

  const validateVideoUrl = (url: string) => {
    const value = url.trim();
    const regex = /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;

    if (form.watch('videoSource') === 'shortcode') {
      const regExp = /^\[.*\]$/;
      const match = value.match(regExp);

      if (!match) {
        return __('Invalid Shortcode', 'tutor');
      }

      return true;
    }

    if (!regex.test(value)) {
      return __('Invalid URL', 'tutor');
    }

    if (form.watch('videoSource') === 'youtube') {
      const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
      const match = value.match(regExp);
      if (!match || match[7].length !== 11) {
        return __('Invalid YouTube URL', 'tutor');
      }

      return true;
    }

    if (form.watch('videoSource') === 'vimeo') {
      const regExp = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
      const match = value.match(regExp);

      if (!match || !match[5]) {
        return __('Invalid Vimeo URL', 'tutor');
      }
    }

    if (form.watch('videoSource') === 'embedded') {
      const regExp = /<iframe.*src="(.*)".*><\/iframe>/;
      const match = value.match(regExp);

      if (!match || !match[1]) {
        return __('Invalid Embedded URL', 'tutor');
      }
    }

    return true;
  };

  return (
    <>
      <FormFieldWrapper label={label} field={field} fieldState={fieldState} helpText={helpText}>
        {() => {
          return (
            <div ref={triggerRef}>
              <Show
                when={!loading}
                fallback={
                  <div css={styles.emptyMedia({ hasVideoSource: true })}>
                    <LoadingOverlay />
                  </div>
                }
              >
                <Show
                  when={isVideoAvailable()}
                  fallback={
                    <div
                      css={styles.emptyMedia({
                        hasVideoSource: true,
                      })}
                    >
                      <Show when={videoSources.includes('html5')}>
                        <Button
                          variant="secondary"
                          icon={<SVGIcon name="monitorPlay" height={24} width={24} />}
                          onClick={() => {
                            handleUpload('video');
                          }}
                        >
                          {buttonText}
                        </Button>
                      </Show>
                      <Show when={videoSources.filter((source) => source !== 'html5').length > 0}>
                        <Show
                          when={!videoSources.includes('html5')}
                          fallback={
                            <button
                              type="button"
                              css={styles.urlButton}
                              onClick={() => {
                                setIsOpen((previousState) => !previousState);
                              }}
                            >
                              {__('Add from URL', 'tutor')}
                            </button>
                          }
                        >
                          <Button
                            variant="secondary"
                            icon={<SVGIcon name="plusSquareBrand" height={24} width={24} />}
                            onClick={() => {
                              setIsOpen((previousState) => !previousState);
                            }}
                          >
                            {__('Add from URL', 'tutor')}
                          </Button>
                        </Show>
                      </Show>

                      <Show when={videoSources.includes('html5')}>
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
                                <div css={styleUtils.text.ellipsis(1)}>
                                  {videoSourceOptions.find((option) => option.value === fieldValue?.source)?.label}
                                </div>
                              </div>
                            </div>
                          </div>

                          <div css={styles.actionButtons}>
                            <Show when={videoSource !== 'html5'}>
                              <button
                                type="button"
                                css={styleUtils.actionButton}
                                onClick={() => {
                                  setIsOpen(true);
                                }}
                              >
                                <SVGIcon name="edit" height={24} width={24} />
                              </button>
                            </Show>
                            <button
                              type="button"
                              css={styleUtils.actionButton}
                              onClick={() => {
                                handleClear('video');
                              }}
                            >
                              <SVGIcon name="cross" height={24} width={24} />
                            </button>
                          </div>
                        </div>
                        <div
                          css={styles.imagePreview({
                            isHTMLVideo: fieldValue?.source === 'html5',
                          })}
                        >
                          <Show
                            when={fieldValue?.source === 'html5'}
                            fallback={<div css={styles.urlData}>{form.watch('videoUrl')}</div>}
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
                              uploadHandler={() => handleUpload('poster')}
                              clearHandler={() => handleClear('poster')}
                              buttonText={__('Upload Thumbnail', 'tutor')}
                              infoText={__('Upload a thumbnail image for your video', 'tutor')}
                              emptyImageCss={styles.thumbImage}
                              previewImageCss={styles.thumbImage}
                              overlayCss={styles.thumbImage}
                              replaceButtonText={__('Replace Thumbnail', 'tutor')}
                            />
                          </Show>
                        </div>
                      </div>
                    );
                  }}
                </Show>
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
              top: triggerRef.current?.getBoundingClientRect().top,
              maxWidth: triggerRef.current?.offsetWidth,
            },
          ]}
        >
          <div css={styles.popoverContent}>
            <Controller
              control={form.control}
              name="videoSource"
              rules={{ ...requiredRule() }}
              render={(controllerProps) => {
                return (
                  <FormSelectInput
                    {...controllerProps}
                    options={videoSourcesSelectOptions}
                    disabled={videoSourceOptions.length <= 1}
                    placeholder={__('Select source', 'tutor')}
                    hideCaret={videoSourceOptions.length <= 1}
                  />
                );
              }}
            />
            <Controller
              control={form.control}
              name="videoUrl"
              rules={{
                ...requiredRule(),
                validate: validateVideoUrl,
              }}
              render={(controllerProps) => {
                return (
                  <FormTextareaInput
                    {...controllerProps}
                    inputCss={css`
                      border-style: dashed;
                    `}
                    rows={2}
                    placeholder={
                      placeholderMap[form.watch('videoSource') as keyof typeof placeholderMap] ||
                      __('Paste URL', 'tutor')
                    }
                  />
                );
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
  emptyMedia: ({ hasVideoSource = false }: { hasVideoSource: boolean }) => css`
    width: 100%;
    height: 164px;
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
    ${typography.tiny()};
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
    ${typography.caption()};
    ${styleUtils.display.flex('column')};
    padding: ${spacing[8]} ${spacing[12]};
    gap: ${spacing[8]};
    word-break: break-all;
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
  imagePreview: ({ isHTMLVideo }: { isHTMLVideo: boolean }) => css`
    width: 100%;
    max-height: 168px;
    position: relative;
    overflow: hidden;
    background-color: ${colorTokens.bg.white};
    ${!isHTMLVideo && styleUtils.overflowYAuto};

    &:hover {
      [data-hover-buttons-wrapper] {
        opacity: 1;
      }
    }
  `,
  thumbImage: css`
    border-radius: 0;
    border: none;
  `,
  urlButton: css`
    ${styleUtils.resetButton};
    ${typography.small('medium')};
    color: ${colorTokens.text.brand};
    margin-bottom: ${spacing[8]};
  `,
  actionButtons: css`
    ${styleUtils.display.flex()};
    gap: ${spacing[4]};
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
