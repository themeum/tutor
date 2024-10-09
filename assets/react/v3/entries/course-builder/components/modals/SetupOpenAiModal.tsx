import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Alert from '@Atoms/Alert';
import Button from '@Atoms/Button';

import FormInput from '@Components/fields/FormInput';
import FormSwitch from '@Components/fields/FormSwitch';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';

import config, { tutorConfig } from '@Config/config';
import { TutorRoles } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useSaveOpenAiSettingsMutation } from '@CourseBuilderServices/course';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { requiredRule } from '@Utils/validation';

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
const isCurrentUserAdmin = tutorConfig.current_user.roles.includes(TutorRoles.ADMINISTRATOR);

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

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    form.setFocus('openAIApiKey');
  }, []);

  return (
    <BasicModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      title={isCurrentUserAdmin ? __('Set Open AI API key', 'tutor') : undefined}
      entireHeader={isCurrentUserAdmin ? undefined : <>&nbsp;</>}
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
                alt={__('Connect API KEY', 'tutor')}
              />

              <div>
                <div css={styles.message}>{__('API is not connected', 'tutor')}</div>
                <div css={styles.title}>
                  {__('Please, ask your Admin to connect the API with Tutor LMS Pro.', 'tutor')}
                </div>
              </div>
            </>
          }
        >
          <form css={styles.formWrapper} onSubmit={form.handleSubmit(handleSubmit)}>
            <div css={styles.infoText}>
              <div>
                {__('Find your Secret API key in your ', 'tutor')}
                {/* @TODO: need to confirm the URL */}
                <a href={config.CHATGPT_PLATFORM_URL}>{__('Open AI User settings', 'tutor')}</a>
                {__(' and paste it here to connect Open AI with your Tutor LMS website.', 'tutor')}
              </div>

              <Alert type="info" icon="warning">
                {__('This page will reload after submit. Please save course information.', 'tutor')}
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
                  label={__('Open AI API key', 'tutor')}
                  placeholder={__('Enter your Open AI API key', 'tutor')}
                />
              )}
            />

            <Controller
              name="enable_open_ai"
              control={form.control}
              render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Enable Open AI', 'tutor')} />}
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
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              size="small"
              onClick={form.handleSubmit(handleSubmit)}
              loading={saveOpenAiSettingsMutation.isPending}
            >
              {__('Save', 'tutor')}
            </Button>
          </div>
        </Show>
      </div>
    </BasicModalWrapper>
  );
};

export default SetupOpenAiModal;

const styles = {
  wrapper: ({
    isCurrentUserAdmin,
  }: {
    isCurrentUserAdmin: boolean;
  }) => css`
    width: 560px;
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};

    ${
      !isCurrentUserAdmin &&
      css`
        padding: ${spacing[24]};
        padding-top: ${spacing[6]};
      `
    }
    
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
    height:310px;
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
