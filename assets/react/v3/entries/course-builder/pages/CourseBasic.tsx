import Button, { ButtonSize, ButtonVariant } from '@Atoms/Button';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { useModal } from '@Components/modals/Modal';
import ReferenceModal from '@Components/modals/ReferenceModal';
import ConfirmationModal from '@Components/modals/ConfirmationModal';
import { borderRadius, colorTokens, footerHeight, headerHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { Controller } from 'react-hook-form';
import Tabs from '@Molecules/Tabs';
import { useState } from 'react';
import SVGIcon from '@Atoms/SVGIcon';
import FormImageMedia from '@Components/fields/FormImageMedia';
import FormDateInput from '@Components/fields/FormDateInput';
import FormTimeInput from '@Components/fields/FormTimeInput';
import { __ } from '@wordpress/i18n';

const CourseBasic = () => {
  const form = useFormWithGlobalError();
  const { showModal } = useModal();

  const [activeTab, setActiveTab] = useState('general');

  const tabList = [
    {
      label: 'General',
      value: 'general',
      icon: <SVGIcon name="settings" width={24} height={24} />,
    },
    {
      label: 'Content Drip',
      value: 'content_drip',
      icon: <SVGIcon name="contentDrip" width={24} height={24} />,
      activeBadge: true,
    },
  ];

  return (
    <div css={styles.wrapper}>
      <div css={styles.mainForm}>
        <h6 css={styles.title}>Course Basic</h6>

        <Button
          onClick={async () => {
            const { action } = await showModal({
              component: ConfirmationModal,
              props: {
                title: 'Modal',
              },
              closeOnOutsideClick: true,
            });
            console.log(action);
          }}
        >
          Open Modal
        </Button>
        <Tabs tabList={tabList} activeTab={activeTab} onChange={setActiveTab} />

        <div css={styles.courseSettings}>
          <Tabs tabList={tabList} activeTab={activeTab} onChange={setActiveTab} orientation="vertical" />

          <div css={styles.courseSettingsRight}>
            <Controller
              name="title"
              control={form.control}
              render={(controllerProps) => (
                <FormInput {...controllerProps} label="Title" placeholder="Course title" maxLimit={245} isClearable />
              )}
            />

            <Controller
              name="description"
              control={form.control}
              render={(controllerProps) => (
                <FormTextareaInput {...controllerProps} label="Course Description" maxLimit={400} />
              )}
            />
          </div>
        </div>

        <form css={styles.form}>
          <Controller
            name="title"
            control={form.control}
            render={(controllerProps) => (
              <FormInput {...controllerProps} label="Title" placeholder="Course title" maxLimit={245} isClearable />
            )}
          />

          <Controller
            name="price"
            control={form.control}
            render={(controllerProps) => (
              <FormInputWithContent {...controllerProps} label="Regular Price" placeholder="0.00" content="$" />
            )}
          />

          <Controller
            name="public"
            control={form.control}
            render={(controllerProps) => (
              <FormSwitch {...controllerProps} label="Public Course" helpText="Public course help text" />
            )}
          />

          <Controller
            name="description"
            control={form.control}
            render={(controllerProps) => (
              <FormTextareaInput {...controllerProps} label="Course Description" maxLimit={400} />
            )}
          />

          <Controller
            name="has_price"
            control={form.control}
            render={(controllerProps) => (
              <FormRadioGroup
                {...controllerProps}
                label="Price"
                options={[
                  { label: 'Free', value: 0 },
                  { label: 'Paid', value: 1 },
                ]}
              />
            )}
          />
        </form>
      </div>
      <div css={styles.sidebar}>
        <Controller
          name="level"
          control={form.control}
          defaultValue={2}
          render={(controllerProps) => (
            <FormSelectInput
              {...controllerProps}
              label="Visibility Status"
              helpText="Hello there"
              options={[
                {
                  label: 'One',
                  value: 1,
                },
                {
                  label: 'Two',
                  value: 2,
                },
                {
                  label: 'Three',
                  value: 3,
                },
              ]}
            />
          )}
        />

        <div css={styles.scheduleOptions}>
          <Controller
            name="schedule_options"
            control={form.control}
            defaultValue={true}
            render={(controllerProps) => <FormSwitch {...controllerProps} label={__('Schedule Options', 'tutor')} />}
          />

          <div css={styles.dateAndTimeWrapper}>
            <Controller
              name="schedule_date"
              control={form.control}
              render={(controllerProps) => <FormDateInput {...controllerProps} isClearable={false} />}
            />

            <Controller
              name="schedule_time"
              control={form.control}
              render={(controllerProps) => <FormTimeInput {...controllerProps} interval={60} isClearable={false} />}
            />
          </div>

          <div css={styles.scheduleButtonsWrapper}>
            <Button variant="tertiary" size="small">
              {__('Cancel')}
            </Button>
            <Button variant="secondary" size="small">
              {__('Save')}
            </Button>
          </div>
        </div>

        <Controller
          name="featured_image"
          control={form.control}
          render={(controllerProps) => (
            <FormImageMedia
              {...controllerProps}
              label={__('Featured Image', 'tutor')}
              buttonText={__('Upload Course Thumbnail', 'tutor')}
              infoText={__('Size: 700x430 pixels', 'tutor')}
            />
          )}
        />
      </div>
    </div>
  );
};

export default CourseBasic;

const styles = {
  wrapper: css`
    display: grid;
    grid-template-columns: 1fr 370px;
  `,
  mainForm: css`
    padding: ${spacing[24]} ${spacing[64]};
  `,
  title: css`
    ${typography.heading6('medium')};
    margin-bottom: ${spacing[40]};
  `,
  form: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
  sidebar: css`
    padding-top: ${spacing[24]};
    padding-left: ${spacing[64]};
    border-left: 1px solid ${colorTokens.stroke.default};
    min-height: calc(100vh - (${headerHeight}px + ${footerHeight}px));

    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
  scheduleOptions: css`
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  dateAndTimeWrapper: css`
    display: grid;
    grid-template-columns: 1fr 124px;
    gap: 1px;
    background-image: linear-gradient(to right, transparent, ${colorTokens.stroke.default}, transparent);
    margin-top: ${spacing[12]};
    border-radius: ${borderRadius[6]};

    &:focus-within {
      box-shadow: ${shadow.focus};
    }

    > div {
      &:first-of-type {
        input {
          border-top-right-radius: 0;
          border-bottom-right-radius: 0;
          border-right: none;
          box-shadow: none;
        }
      }
      &:last-of-type {
        input {
          border-top-left-radius: 0;
          border-bottom-left-radius: 0;
          border-left: none;
          box-shadow: none;
        }
      }
    }
  `,
  scheduleButtonsWrapper: css`
    display: flex;
    gap: ${spacing[12]};

    button {
      width: 100%;

      span {
        justify-content: center;
      }
    }
  `,
  courseSettings: css`
    display: grid;
    grid-template-columns: 200px 1fr;
    margin-block: ${spacing[48]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    background-color: ${colorTokens.background.default};
    overflow: hidden;
  `,
  courseSettingsRight: css`
    padding: ${spacing[16]} ${spacing[32]} ${spacing[32]} ${spacing[32]};
    background-color: ${colorTokens.background.white};
  `,
};
