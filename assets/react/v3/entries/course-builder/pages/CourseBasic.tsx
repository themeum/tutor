import FormInput from "@Components/fields/FormInput";
import FormInputWithContent from "@Components/fields/FormInputWithContent";
import FormRadioGroup from "@Components/fields/FormRadioGroup";
import FormSelectInput from "@Components/fields/FormSelectInput";
import FormSwitch from "@Components/fields/FormSwitch";
import FormTextareaInput from "@Components/fields/FormTextareaInput";
import {
  colorPalateTutor,
  footerHeight,
  headerHeight,
  spacing,
} from "@Config/styles";
import { typography } from "@Config/typography";
import { useFormWithGlobalError } from "@Hooks/useFormWithGlobalError";
import { css } from "@emotion/react";
import { Controller } from "react-hook-form";

const CourseBasic = () => {
  const form = useFormWithGlobalError();

  return (
    <div css={styles.wrapper}>
      <div css={styles.mainForm}>
        <h6 css={styles.title}>Course Basic</h6>

        <form css={styles.form}>
          <Controller
            name="title"
            control={form.control}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                label="Title"
                placeholder="Course title"
                maxLimit={245}
                isClearable
              />
            )}
          />

          <Controller
            name="price"
            control={form.control}
            render={(controllerProps) => (
              <FormInputWithContent
                {...controllerProps}
                label="Regular Price"
                placeholder="0.00"
                content="$"
              />
            )}
          />

          <Controller
            name="public"
            control={form.control}
            render={(controllerProps) => (
              <FormSwitch
                {...controllerProps}
                label="Public Course"
                helpText="Public course help text"
              />
            )}
          />

          <Controller
            name="description"
            control={form.control}
            render={(controllerProps) => (
              <FormTextareaInput
                {...controllerProps}
                label="Course Description"
                maxLimit={400}
              />
            )}
          />

          <Controller
            name="has_price"
            control={form.control}
            render={(controllerProps) => (
              <FormRadioGroup
                {...controllerProps}
                label="Price"
                options={[{label: 'Free', value: 0}, {label: 'Paid', value: 1}]}
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
                  label: "One",
                  value: 1,
                },
                {
                  label: "Two",
                  value: 2,
                },
                {
                  label: "Three",
                  value: 3,
                },
              ]}
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
    ${typography.heading6("medium")};
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
    border-left: 1px solid ${colorPalateTutor.stroke.default};
    min-height: calc(100vh - (${headerHeight}px + ${footerHeight}px));
  `,
};
