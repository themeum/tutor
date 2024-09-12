import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';

import FormInput from '@Components/fields/FormInput';
import FormSwitch from '@Components/fields/FormSwitch';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';

import config, { tutorConfig } from '@Config/config';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useSaveOpenAiSettingsMutation } from '@CourseBuilderServices/course';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { requiredRule } from '@Utils/validation';

interface SetupOpenAiModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

interface OpenAiApiForm {
  openAIApiKey: string;
  enable_open_ai: boolean;
}

const isOpenAiEnabled = tutorConfig.settings.chatgpt_enable === 'on';

const SetupOpenAiModal = ({ closeModal }: SetupOpenAiModalProps) => {
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
      api_key: data.openAIApiKey,
      chatgpt_enable: data.enable_open_ai ? 'on' : 'off',
    });

    if (response.success) {
      closeModal({ action: 'CONFIRM' });
    }
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    form.setFocus('openAIApiKey');
  }, []);

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={__('Set Open AI API key', 'tutor')}>
      <div css={styles.wrapper}>
        <form css={styles.formWrapper} onSubmit={form.handleSubmit(handleSubmit)}>
          <span
            css={styles.infoText}
            dangerouslySetInnerHTML={{
              __html: sprintf(
                __(
                  'Find your Secret API key in your %sOpen AI User settings%s and paste it here to connect Open AI with your Tutor LMS website.',
                ),
                // @TODO: need to confirm the URL
                `<a href="${config.CHATGPT_PLATFORM_URL}" target="_blank" rel="noreferrer">`,
                '</a>',
              ),
            }}
          />
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
                helpText={__(
                  'Find your Secret API key in your Open AI User settings and paste it here to connect Open AI with your Tutor LMS website.',
                  'tutor',
                )}
              />
            )}
          />

          <Controller
            name="enable_open_ai"
            control={form.control}
            render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Enable Open AI', 'tutor')} />}
          />

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
        </form>
      </div>
    </BasicModalWrapper>
  );
};

export default SetupOpenAiModal;

const styles = {
  wrapper: css`
    width: 560px;
    padding: 0 ${spacing[16]} ${spacing[24]} ${spacing[16]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};
    padding-top: ${spacing[16]};
  `,
  infoText: css`
    ${typography.small()};
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
    margin-top: ${spacing[16]};
  `,
};
