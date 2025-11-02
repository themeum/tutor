import { css } from '@emotion/react';
import type { UseMutationResult } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import ImageInput from '@TutorShared/atoms/ImageInput';
import { LoadingOverlay } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Popover from '@TutorShared/molecules/Popover';

import config, { tutorConfig } from '@TutorShared/config/config';
import { VideoRegex } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { withVisibilityControl } from '@TutorShared/hoc/withVisibilityControl';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { POPOVER_PLACEMENTS } from '@TutorShared/hooks/usePortalPopover';
import useWPMedia, { type WPMedia } from '@TutorShared/hooks/useWpMedia';
import { type IconCollection } from '@TutorShared/icons/types';
import { useGetYouTubeVideoDuration } from '@TutorShared/services/video';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import type { TutorMutationResponse } from '@TutorShared/utils/types';
import { covertSecondsToHMS } from '@TutorShared/utils/util';
import { requiredRule } from '@TutorShared/utils/validation';
import {
  convertYouTubeDurationToSeconds,
  generateVideoThumbnail,
  getExternalVideoDuration,
  getVimeoVideoDuration,
} from '@TutorShared/utils/video';

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

interface GetVideoDuration {
  source: string;
  url: string;
  getYouTubeVideoDurationMutation: UseMutationResult<
    TutorMutationResponse<{
      duration: string;
    }>,
    Error,
    string,
    unknown
  >;
}

const videoSourceOptions = tutorConfig.supported_video_sources || [];
const videoSourcesSelectOptions = videoSourceOptions.filter((option) => option.value !== 'html5');
const videoSources = videoSourceOptions.map((item) => item.value);

const thumbnailGeneratorSources = ['vimeo', 'youtube', 'html5'];

const placeholderMap = {
  youtube: __('Paste YouTube Video URL', __TUTOR_TEXT_DOMAIN__),
  vimeo: __('Paste Vimeo Video URL', __TUTOR_TEXT_DOMAIN__),
  external_url: __('Paste External Video URL', __TUTOR_TEXT_DOMAIN__),
  shortcode: __('Paste Video Shortcode', __TUTOR_TEXT_DOMAIN__),
  embedded: __('Paste Embedded Video Code', __TUTOR_TEXT_DOMAIN__),
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

const getVideoDuration = async ({ source, url, getYouTubeVideoDurationMutation }: GetVideoDuration) => {
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
    // eslint-disable-next-line no-console
    console.error('Error getting video duration:', error);
    return null;
  }
};

const FormVideoInput = ({
  field,
  fieldState,
  label,
  helpText,
  buttonText = __('Upload Media', __TUTOR_TEXT_DOMAIN__),
  infoText,
  onChange,
  supportedFormats,
  loading,
  onGetDuration,
}: FormVideoInputProps) => {
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
  const [localPoster, setLocalPoster] = useState<string>('');
  const [isOpen, setIsOpen] = useState(false);
  const triggerRef = useRef<HTMLDivElement>(null);

  const handleVideoFileUpdate = async (files: WPMedia | WPMedia[] | null) => {
    if (!files) {
      return;
    }

    const file = Array.isArray(files) ? files[0] : files;
    const updateData = {
      source: 'html5',
      source_video_id: file.id.toString(),
      source_html5: file.url,
    };
    field.onChange(updateFieldValue(field.value, updateData));
    onChange?.(updateFieldValue(field.value, updateData));

    try {
      setIsThumbnailLoading(true);
      resetImageSelection();
      const posterUrl = await generateVideoThumbnail('external_url', file.url);
      const duration = await getExternalVideoDuration(file.url);

      if (!duration) {
        return;
      }
      setDuration(covertSecondsToHMS(Math.floor(duration)));
      if (onGetDuration) {
        onGetDuration(covertSecondsToHMS(Math.floor(duration)));
      }

      if (posterUrl) {
        setLocalPoster(posterUrl);
      }
    } finally {
      setIsThumbnailLoading(false);
    }
  };

  const { openMediaLibrary: openVideoLibrary, resetFiles: resetVideoSelection } = useWPMedia({
    options: {
      type: supportedFormats?.length ? supportedFormats.map((format) => `video/${format}`).join(',') : 'video',
    },
    onChange: handleVideoFileUpdate,
  });

  const { openMediaLibrary: openImageLibrary, resetFiles: resetImageSelection } = useWPMedia({
    options: {
      type: 'image',
    },
    onChange: (files) => {
      if (!files) {
        return;
      }

      const file = Array.isArray(files) ? files[0] : files;
      const updateData = {
        poster: file.id.toString(),
        poster_url: file.url,
      };
      field.onChange(updateFieldValue(field.value, updateData));
      onChange?.(updateFieldValue(field.value, updateData));
    },
    initialFiles: field.value?.poster
      ? { id: Number(field.value.poster), url: field.value.poster_url, title: '' }
      : null,
  });

  const videoSource = form.watch('videoSource') || '';
  const fieldValue = field.value;

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
      const source = fieldValue.source as 'vimeo' | 'youtube' | 'html5';
      setIsThumbnailLoading(true);
      generateVideoThumbnail(source, fieldValue[`source_${source}` as keyof CourseVideo] || '')
        .then((url) => {
          setIsThumbnailLoading(false);
          setLocalPoster(url);
        })
        .finally(() => {
          setIsThumbnailLoading(false);
        });
    }

    if (Object.values(duration).some((value) => value > 0)) {
      return;
    }

    if (fieldValue.source === 'vimeo') {
      getVimeoVideoDuration(fieldValue['source_vimeo' as keyof CourseVideo] || '').then((duration) => {
        if (!duration) {
          return;
        }

        setDuration(covertSecondsToHMS(Math.floor(duration)));

        if (onGetDuration) {
          onGetDuration(covertSecondsToHMS(Math.floor(duration)));
        }
      });
    }

    if (['external_url', 'html5'].includes(fieldValue.source)) {
      getExternalVideoDuration(fieldValue[`source_${fieldValue.source}` as keyof CourseVideo] || '').then(
        (duration) => {
          if (!duration) {
            return;
          }

          setDuration(covertSecondsToHMS(Math.floor(duration)));

          if (onGetDuration) {
            onGetDuration(covertSecondsToHMS(Math.floor(duration)));
          }
        },
      );
    }

    if (fieldValue.source === 'youtube' && tutorConfig.settings?.youtube_api_key_exist) {
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
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [fieldValue]);

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
            {__('No video source selected', __TUTOR_TEXT_DOMAIN__)}
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
            {__('Select from settings', __TUTOR_TEXT_DOMAIN__)}
          </Button>
        </div>
      </div>
    );
  }

  const handleUpload = (type: 'video' | 'poster') => {
    if (type === 'video') {
      openVideoLibrary();
      return;
    }
    openImageLibrary();
  };

  const handleClear = (type: 'video' | 'poster') => {
    const updateData =
      type === 'video'
        ? { source: '', source_video_id: '', poster: '', poster_url: '' }
        : { poster: '', poster_url: '' };
    const updatedValue = updateFieldValue(fieldValue, updateData);
    if (type === 'video') {
      resetVideoSelection();
    } else {
      resetImageSelection();
    }

    field.onChange(updatedValue);
    setLocalPoster('');
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
        getVideoDuration({ source, url, getYouTubeVideoDurationMutation }),
        thumbnailGeneratorSources.includes(source)
          ? generateVideoThumbnail(source as 'youtube' | 'vimeo' | 'external_url' | 'html5', url)
          : null,
      ]);

      if (duration) {
        setDuration(duration);
        onGetDuration?.(duration);
      }

      if (thumbnail) {
        setLocalPoster(thumbnail);
      }
    } finally {
      setIsThumbnailLoading(false);
    }
  };

  const validateVideoUrl = (url: string) => {
    const videoUrl = url.trim();
    if (videoSource === 'embedded') return true;

    if (videoSource === 'shortcode') {
      return videoValidation.shortcode(videoUrl) ? true : __('Invalid Shortcode', __TUTOR_TEXT_DOMAIN__);
    }

    if (!videoValidation.url(videoUrl)) {
      return __('Invalid URL', __TUTOR_TEXT_DOMAIN__);
    }

    if (videoSource === 'youtube' && !videoValidation.youtube(videoUrl)) {
      return __('Invalid YouTube URL', __TUTOR_TEXT_DOMAIN__);
    }

    if (videoSource === 'vimeo' && !videoValidation.vimeo(videoUrl)) {
      return __('Invalid Vimeo URL', __TUTOR_TEXT_DOMAIN__);
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
                          data-cy="upload-media"
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
                              data-cy="add-from-url"
                              type="button"
                              css={styles.urlButton}
                              onClick={() => {
                                setIsOpen((previousState) => !previousState);
                              }}
                            >
                              {__('Add from URL', __TUTOR_TEXT_DOMAIN__)}
                            </button>
                          }
                        >
                          <Button
                            data-cy="add-from-url"
                            size="small"
                            variant="secondary"
                            icon={<SVGIcon name="plusSquareBrand" height={24} width={24} />}
                            onClick={() => {
                              setIsOpen((previousState) => !previousState);
                            }}
                          >
                            {__('Add from URL', __TUTOR_TEXT_DOMAIN__)}
                          </Button>
                        </Show>
                      </Show>

                      <Show when={videoSources.includes('html5')}>
                        <p css={styles.infoTexts}>{infoText}</p>
                      </Show>
                    </div>
                  }
                >
                  {() => {
                    return (
                      <div css={styles.previewWrapper} data-cy="media-preview">
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
                              data-cy="remove-video"
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
                                      id: Number(fieldValue.poster) || 0,
                                      url: fieldValue.poster_url || localPoster,
                                      title: '',
                                    }
                                  : null
                              }
                              loading={isThumbnailLoading}
                              isClearAble={!!fieldValue?.poster}
                              disabled={['vimeo', 'youtube'].includes(fieldValue?.source || '')}
                              uploadHandler={() => handleUpload('poster')}
                              clearHandler={() => handleClear('poster')}
                              buttonText={__('Upload Thumbnail', __TUTOR_TEXT_DOMAIN__)}
                              infoText={__('Upload a thumbnail image for your video', __TUTOR_TEXT_DOMAIN__)}
                              emptyImageCss={styles.thumbImage}
                              previewImageCss={styles.thumbImage}
                              overlayCss={styles.thumbImage}
                              replaceButtonText={__('Replace Thumbnail', __TUTOR_TEXT_DOMAIN__)}
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
      <Popover
        triggerRef={triggerRef}
        isOpen={isOpen}
        placement={POPOVER_PLACEMENTS.MIDDLE}
        animationType={AnimationType.fadeIn}
        closePopover={() => setIsOpen(false)}
        positionModifier={{
          top: 17,
          left: 0,
        }}
        maxWidth={`${triggerRef.current?.offsetWidth || 300}px`}
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
                  placeholder={__('Select source', __TUTOR_TEXT_DOMAIN__)}
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
                    placeholderMap[videoSource as keyof typeof placeholderMap] ||
                    __('Paste Here', __TUTOR_TEXT_DOMAIN__)
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
              {__('Cancel', __TUTOR_TEXT_DOMAIN__)}
            </Button>
            <Button
              data-cy="submit-url"
              variant="secondary"
              size="small"
              onClick={form.handleSubmit(handleDataFromUrl)}
            >
              {__('Ok', __TUTOR_TEXT_DOMAIN__)}
            </Button>
          </div>
        </div>
      </Popover>
    </>
  );
};

export default withVisibilityControl(FormVideoInput);

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

    ${hasVideoSource &&
    css`
      background-color: ${colorTokens.bg.white};
    `}
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
    ${!hasImageInput &&
    css`
      ${styleUtils.overflowYAuto};
    `};
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

    &:focus,
    &:active,
    &:hover {
      background: none;
      color: ${colorTokens.text.brand};
    }

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
