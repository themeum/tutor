import { css } from '@emotion/react';
import type { UseMutationResult } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';
import ImageInput from '@Atoms/ImageInput';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';

import config, { tutorConfig } from '@Config/config';
import { VideoRegex } from '@Config/constants';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { type TutorMutationResponse, useGetYouTubeVideoDuration } from '@CourseBuilderServices/course';
import {
  convertYouTubeDurationToSeconds,
  covertSecondsToHMS,
  generateVideoThumbnail,
  getExternalVideoDuration,
  getVimeoVideoDuration,
} from '@CourseBuilderUtils/utils';
import { AnimationType } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { requiredRule } from '@Utils/validation';
import type { IconCollection } from '../../utils/types';
import FormFieldWrapper from './FormFieldWrapper';
import FormSelectInput from './FormSelectInput';
import FormTextareaInput from './FormTextareaInput';

export interface CourseVideo {
  source: string;
  source_video_id: string;
  poster: string;
  poster_url: string;
  source_html5: string;
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

const thumbnailGeneratorSources = ['vimeo', 'youtube', 'external_url', 'html5'];

const placeholderMap = {
  youtube: __('Paste YouTube Video URL', 'tutor'),
  vimeo: __('Paste Vimeo Video URL', 'tutor'),
  external_url: __('Paste External Video URL', 'tutor'),
  shortcode: __('Paste Video Shortcode', 'tutor'),
  embedded: __('Paste Embedded Video Code', 'tutor'),
};

const videoIconMap = {
  youtube: 'youtube',
  vimeo: 'vimeo',
  shortcode: 'shortcode',
  embedded: 'coding',
};

const updateFieldValue = (fieldValue: CourseVideo | null, update: Partial<CourseVideo>) => {
  const defaultValue = {
    source: '',
    source_video_id: '',
    poster: '',
    poster_url: '',
    source_html5: '',
    source_external_url: '',
    source_shortcode: '',
    source_youtube: '',
    source_vimeo: '',
    source_embedded: '',
  };

  return fieldValue ? { ...fieldValue, ...update } : { ...defaultValue, ...update };
};

const videoValidation = {
  youtube: (url: string) => {
    const match = url.match(VideoRegex.YOUTUBE);
    return match && match[7].length === 11 ? match[7] : null;
  },
  vimeo: (url: string) => {
    const match = url.match(VideoRegex.VIMEO);
    return match?.[5] || null;
  },
  shortcode: (code: string) => {
    return code.match(VideoRegex.SHORTCODE) ? code : null;
  },
  url: (url: string) => {
    return url.match(VideoRegex.EXTERNAL_URL) ? url : null;
  },
};

const getVideoDuration = async (
  source: string,
  url: string,
  getYouTubeVideoDurationMutation: UseMutationResult<
    TutorMutationResponse<{
      duration: string;
    }>,
    Error,
    string,
    unknown
  >,
) => {
  try {
    let seconds = 0;

    switch (source) {
      case 'vimeo':
        seconds = (await getVimeoVideoDuration(url)) ?? 0;
        break;
      case 'html5':
      case 'external_url':
        seconds = (await getExternalVideoDuration(url)) ?? 0;
        break;
      case 'youtube': {
        const videoId = videoValidation.youtube(url);
        if (videoId) {
          const response = await getYouTubeVideoDurationMutation.mutateAsync(videoId);
          seconds = convertYouTubeDurationToSeconds(response.data.duration);
        }
        break;
      }
    }

    if (seconds) {
      const duration = covertSecondsToHMS(Math.floor(seconds));
      return duration;
    }
    return null;
  } catch (error) {
    console.error('Error getting video duration:', error);
    return null;
  }
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
      <div css={styles.emptyMediaWrapper}>
        <Show when={label}>
          <label>{label}</label>
        </Show>

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
  const [isThumbnailLoading, setIsThumbnailLoading] = useState(false);
  const [duration, setDuration] = useState({
    hours: 0,
    minutes: 0,
    seconds: 0,
  });

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

    if (!fieldValue.poster_url && thumbnailGeneratorSources.includes(fieldValue.source)) {
      const source = fieldValue.source as 'vimeo' | 'youtube' | 'external_url' | 'html5';
      setIsThumbnailLoading(true);
      generateVideoThumbnail(source, fieldValue[`source_${source}` as keyof CourseVideo] || '')
        .then((url) => {
          setIsThumbnailLoading(false);
          field.onChange(
            updateFieldValue(fieldValue, {
              poster: '',
              poster_url: url,
            }),
          );
        })
        .finally(() => {
          setIsThumbnailLoading(false);
        });
    }

    if (Object.values(duration).some((value) => value > 0)) {
      return;
    }

    if (fieldValue.source === 'vimeo') {
      getVimeoVideoDuration(fieldValue['source_vimeo' as keyof CourseVideo] || '')
        .then((duration) => {
          if (!duration) {
            return;
          }

          setDuration(covertSecondsToHMS(Math.floor(duration)));

          if (onGetDuration) {
            onGetDuration(covertSecondsToHMS(Math.floor(duration)));
          }
        })
        .catch((error) => {
          console.error(error);
        });
    }

    if (['external_url', 'html5'].includes(fieldValue.source)) {
      getExternalVideoDuration(fieldValue[`source_${fieldValue.source}` as keyof CourseVideo] || '')
        .then((duration) => {
          if (!duration) {
            return;
          }

          setDuration(covertSecondsToHMS(Math.floor(duration)));

          if (onGetDuration) {
            onGetDuration(covertSecondsToHMS(Math.floor(duration)));
          }
        })
        .catch((error) => {
          console.error(error);
        });
    }

    if (fieldValue.source === 'youtube') {
      const videoId = videoValidation.youtube(fieldValue['source_youtube' as keyof CourseVideo] || '') ?? '';
      getYouTubeVideoDurationMutation.mutateAsync(videoId).then((response) => {
        const duration = response.data.duration;
        if (!duration) {
          return;
        }

        const seconds = convertYouTubeDurationToSeconds(duration);

        setDuration(covertSecondsToHMS(Math.floor(seconds)));

        if (onGetDuration) {
          onGetDuration(covertSecondsToHMS(Math.floor(seconds)));
        }
      });
    }
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
    uploader.on('select', async () => {
      const attachment = uploader.state().get('selection').first().toJSON();

      const updateData =
        type === 'video'
          ? { source: 'html5', source_video_id: attachment.id }
          : { poster: attachment.id, poster_url: attachment.url };
      field.onChange(updateFieldValue(fieldValue, updateData));
      onChange?.(updateFieldValue(fieldValue, updateData));

      if (type === 'video') {
        try {
          setIsThumbnailLoading(true);
          const posterUrl = await generateVideoThumbnail('external_url', attachment.url);
          const duration = await getExternalVideoDuration(attachment.url);

          if (!duration) {
            return;
          }
          setDuration(covertSecondsToHMS(Math.floor(duration)));
          if (onGetDuration) {
            onGetDuration(covertSecondsToHMS(Math.floor(duration)));
          }

          if (posterUrl) {
            field.onChange(
              updateFieldValue(fieldValue, {
                ...updateData,
                poster: '',
                poster_url: posterUrl,
              }),
            );

            onChange?.(
              updateFieldValue(fieldValue, {
                ...updateData,
                poster: '',
                poster_url: posterUrl,
              }),
            );
          }
        } catch (error) {
          console.error(error);
        } finally {
          setIsThumbnailLoading(false);
        }
      }
    });
  };

  const handleClear = (type: 'video' | 'poster') => {
    const updateData = type === 'video' ? { source: '' } : { poster: '', poster_url: '' };
    const updatedValue = updateFieldValue(fieldValue, updateData);

    field.onChange(updatedValue);
    setDuration({
      hours: 0,
      minutes: 0,
      seconds: 0,
    });
    if (onChange) {
      onChange(updatedValue);
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
    setIsThumbnailLoading(true);
    try {
      const { videoSource: source, videoUrl: url } = data;
      const updatedValue = {
        source,
        [`source_${source}`]: url,
      };

      field.onChange(updateFieldValue(fieldValue, updatedValue));
      onChange?.(updateFieldValue(fieldValue, updatedValue));
      setIsOpen(false);

      const [duration, thumbnail] = await Promise.all([
        getVideoDuration(source, url, getYouTubeVideoDurationMutation),
        thumbnailGeneratorSources.includes(source)
          ? generateVideoThumbnail(source as 'youtube' | 'vimeo' | 'external_url' | 'html5', url)
          : null,
      ]);

      if (duration) {
        setDuration(duration);
        onGetDuration?.(duration);
      }

      if (thumbnail) {
        const valueWithThumbnail = updateFieldValue(fieldValue, {
          ...updatedValue,
          poster: '',
          poster_url: thumbnail,
        });
        field.onChange(valueWithThumbnail);
        onChange?.(valueWithThumbnail);
      }
    } finally {
      setIsThumbnailLoading(false);
    }
  };

  const validateVideoUrl = (url: string) => {
    const videoUrl = url.trim();
    if (videoSource === 'embedded') return true;

    if (videoSource === 'shortcode') {
      return videoValidation.shortcode(videoUrl) ? true : __('Invalid Shortcode', 'tutor');
    }

    if (!videoValidation.url(videoUrl)) {
      return __('Invalid URL', 'tutor');
    }

    if (videoSource === 'youtube' && !videoValidation.youtube(videoUrl)) {
      return __('Invalid YouTube URL', 'tutor');
    }

    if (videoSource === 'vimeo' && !videoValidation.vimeo(videoUrl)) {
      return __('Invalid Vimeo URL', 'tutor');
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
                          size="small"
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
                            size="small"
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
                            <SVGIcon
                              name={
                                (videoIconMap[fieldValue?.source as keyof typeof videoIconMap] as IconCollection) ||
                                'video'
                              }
                              height={36}
                              width={36}
                            />

                            <div css={styles.videoInfo}>
                              <div css={styles.videoInfoTitle}>
                                <div css={styleUtils.text.ellipsis(1)}>
                                  {thumbnailGeneratorSources.includes(fieldValue?.source || '')
                                    ? fieldValue?.[`source_${fieldValue.source}` as keyof CourseVideo]
                                    : videoSourceOptions.find((option) => option.value === fieldValue?.source)?.label}
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
                            hasImageInput: thumbnailGeneratorSources.includes(fieldValue?.source || ''),
                          })}
                        >
                          <Show
                            when={thumbnailGeneratorSources.includes(fieldValue?.source || '')}
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
                              loading={isThumbnailLoading}
                              isClearAble={!!fieldValue?.poster}
                              disabled={['vimeo', 'youtube', 'external_url'].includes(fieldValue?.source || '')}
                              uploadHandler={() => handleUpload('poster')}
                              clearHandler={() => handleClear('poster')}
                              buttonText={__('Upload Thumbnail', 'tutor')}
                              infoText={__('Upload a thumbnail image for your video', 'tutor')}
                              emptyImageCss={styles.thumbImage}
                              previewImageCss={styles.thumbImage}
                              overlayCss={styles.thumbImage}
                              replaceButtonText={__('Replace Thumbnail', 'tutor')}
                            />

                            <Show when={duration.hours > 0 || duration.minutes > 0 || duration.seconds > 0}>
                              <div css={styles.duration}>
                                {duration.hours > 0 && `${duration.hours}h`} {duration.minutes}m {duration.seconds}s
                              </div>
                            </Show>
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
      <Portal
        isOpen={isOpen}
        onClickOutside={() => setIsOpen(false)}
        onEscape={() => setIsOpen(false)}
        animationType={AnimationType.fadeIn}
      >
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
                      placeholderMap[videoSource as keyof typeof placeholderMap] || __('Paste Here', 'tutor')
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
  emptyMediaWrapper: css`
      ${styleUtils.display.flex('column')};
      gap: ${spacing[4]};
      
      label {
        ${typography.caption()};
        color: ${colorTokens.text.title};
      }
    `,
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
      background-color: ${colorTokens.bg.white};
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
        flex-shrink: 0;
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
  imagePreview: ({ hasImageInput }: { hasImageInput: boolean }) => css`
      width: 100%;
      max-height: 168px;
      position: relative;
      overflow: hidden;
      background-color: ${colorTokens.background.default};
      ${
        !hasImageInput &&
        css`
          ${styleUtils.overflowYAuto};
        `
      };
      scrollbar-gutter: auto;

      &:hover {
        [data-hover-buttons-wrapper] {
          opacity: 1;
        }
      }
    `,
  duration: css`
      ${typography.tiny()};
      position: absolute;
      bottom: ${spacing[12]};
      right: ${spacing[12]};
      background-color: rgba(0, 0, 0, 0.5);
      color: ${colorTokens.text.white};
      padding: ${spacing[4]} ${spacing[8]};
      border-radius: ${borderRadius[6]};
      pointer-events: none;
    `,
  thumbImage: css`
      border-radius: 0;
      border: none;
    `,
  urlButton: css`
      ${styleUtils.resetButton};
      ${typography.small('medium')};
      color: ${colorTokens.text.brand};
      border-radius: ${borderRadius[2]};
      padding: 0 ${spacing[4]};
      margin-bottom: ${spacing[8]};

      &:focus-visible {
        outline: 2px solid ${colorTokens.stroke.brand};
        outline-offset: 1px;
      }
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
