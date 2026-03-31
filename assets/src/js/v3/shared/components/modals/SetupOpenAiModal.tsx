import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Alert from '@TutorShared/atoms/Alert';
import Button from '@TutorShared/atoms/Button';

import FormInput from '@TutorShared/components/fields/FormInput';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';

import config, { tutorConfig } from '@TutorShared/config/config';
import { TutorRoles } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { useSaveOpenAiSettingsMutation } from '@TutorShared/services/magic-ai';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { requiredRule } from '@TutorShared/utils/validation';

interface SetupOpenAiModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  image?: string;
  image2x?: string;
}

interface OpenAiApiForm {
  openAIApiKey: string;
  enable_open_ai: boolean;
}

const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';
const isCurrentUserAdmin = tutorConfig.current_user.roles?.includes(TutorRoles.ADMINISTRATOR);

const SetupOpenAiModal = ({ closeModal, image, image2x }: SetupOpenAiModalProps) => {
  const form = useFormWithGlobalError<OpenAiApiForm>({
    defaultValues: {
      openAIApiKey: '',
      enable_open_ai: isOpenAiEnabled,
    },
    shouldFocusError: true,
  });

  const saveOpenAiSettingsMutation = useSaveOpenAiSettingsMutation();

  const handleSubmit = async (data: OpenAiApiForm) => {
    const response = await saveOpenAiSettingsMutation.mutateAsync({
      chatgpt_api_key: data.openAIApiKey,
      chatgpt_enable: data.enable_open_ai ? 1 : 0,
    });

    if (response.status_code === 200) {
      closeModal({ action: 'CONFIRM' });
      window.location.reload();
    }
  };

  useEffect(() => {
    form.setFocus('openAIApiKey');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <BasicModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      title={isCurrentUserAdmin ? __('Set OpenAI API key', __TUTOR_TEXT_DOMAIN__) : undefined}
      entireHeader={isCurrentUserAdmin ? undefined : <>&nbsp;</>}
      maxWidth={560}
    >
      <div
        css={styles.wrapper({
          isCurrentUserAdmin,
        })}
      >
        <Show
          when={isCurrentUserAdmin}
          fallback={
            <>
              <img
                css={styles.image}
                src={image}
                srcSet={image2x ? `${image} 1x, ${image2x} 2x` : `${image} 1x`}
                alt={__('Connect API KEY', __TUTOR_TEXT_DOMAIN__)}
              />

              <div>
                <div css={styles.message}>{__('API is not connected', __TUTOR_TEXT_DOMAIN__)}</div>
                <div css={styles.title}>
                  {__('Please, ask your Admin to connect the API with Tutor LMS Pro.', __TUTOR_TEXT_DOMAIN__)}
                </div>
              </div>
            </>
          }
        >
          <>
            <form css={styles.formWrapper} onSubmit={form.handleSubmit(handleSubmit)}>
              <div css={styles.infoText}>
                <div
                  dangerouslySetInnerHTML={{
                    /* translators: %1$s and %2$s are opening and closing anchor tags for the "OpenAI User settings" link */
                    __html: sprintf(
                      __(
                        'Find your Secret API key in your %1$sOpenAI User settings%2$s and paste it here to connect OpenAI with your Tutor LMS website.',
                        __TUTOR_TEXT_DOMAIN__,
                      ),
                      `<a href="${config.CHATGPT_PLATFORM_URL}" target="_blank" rel="noopener noreferrer">`,
                      '</a>',
                    ),
                  }}
                ></div>

                <Alert type="info" icon="warning">
                  {__(
                    'The page will reload after submission. Make sure to save the course information.',
                    __TUTOR_TEXT_DOMAIN__,
                  )}
                </Alert>
              </div>

              <Controller
                name="openAIApiKey"
                control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    type="password"
                    isPassword
                    label={__('OpenAI API key', __TUTOR_TEXT_DOMAIN__)}
                    placeholder={__('Enter your OpenAI API key', __TUTOR_TEXT_DOMAIN__)}
                  />
                )}
              />

              <Controller
                name="enable_open_ai"
                control={form.control}
                render={(controllerProps) => (
                  <FormSwitch {...controllerProps} label={__('Enable OpenAI', __TUTOR_TEXT_DOMAIN__)} />
                )}
              />
            </form>
            <div css={styles.formFooter}>
              <Button
                onClick={() =>
                  closeModal({
                    action: 'CLOSE',
                  })
                }
                variant="text"
                size="small"
              >
                {__('Cancel', __TUTOR_TEXT_DOMAIN__)}
              </Button>
              <Button
                size="small"
                onClick={form.handleSubmit(handleSubmit)}
                loading={saveOpenAiSettingsMutation.isPending}
              >
                {__('Save', __TUTOR_TEXT_DOMAIN__)}
              </Button>
            </div>
          </>
        </Show>
      </div>
    </BasicModalWrapper>
  );
};

export default SetupOpenAiModal;

const styles = {
  wrapper: ({ isCurrentUserAdmin }: { isCurrentUserAdmin: boolean }) => css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};

    ${!isCurrentUserAdmin &&
    css`
      padding: ${spacing[24]};
      padding-top: ${spacing[6]};
    `}
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};
    padding: ${spacing[16]} ${spacing[16]} 0 ${spacing[16]};
  `,
  infoText: css`
    ${typography.small()};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    color: ${colorTokens.text.subdued};

    a {
      ${styleUtils.resetButton}
      color: ${colorTokens.text.brand};
    }
  `,
  formFooter: css`
    ${styleUtils.display.flex()};
    justify-content: flex-end;
    gap: ${spacing[16]};
    border-top: 1px solid ${colorTokens.stroke.divider};
    padding: ${spacing[16]};
  `,
  image: css`
    height: 310px;
    width: 100%;
    object-fit: cover;
    object-position: center;
    border-radius: ${borderRadius[8]};
  `,
  message: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
  title: css`
    ${typography.heading4('medium')};
    color: ${colorTokens.text.primary};
    margin-top: ${spacing[4]};
    text-wrap: pretty;
  `,
};
