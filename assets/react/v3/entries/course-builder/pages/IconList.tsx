import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Container from '@TutorShared/components/Container';
import FormInput from '@TutorShared/components/fields/FormInput';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import { useDebounce } from '@TutorShared/hooks/useDebounce';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { icons } from '@TutorShared/icons/types';
import { css } from '@emotion/react';
import { useMemo } from 'react';
import { Controller } from 'react-hook-form';

const IconList = () => {
  const form = useFormWithGlobalError<{ search: string }>({ defaultValues: { search: '' } });
  const search = useDebounce(form.watch('search'));
  const filteredIcons = useMemo(() => {
    if (!search) {
      return icons;
    }
    return icons.filter((icon) => new RegExp(search, 'i').test(icon));
  }, [search]);
  return (
    <Container>
      <div css={styles.container}>
        <Controller
          control={form.control}
          name="search"
          render={(props) => <FormInput {...props} placeholder="Search icons..." />}
        />

        <div css={styles.wrapper}>
          <For each={filteredIcons}>
            {(icon, index) => {
              return (
                <div>
                  <SVGIcon key={index} name={icon} width={60} height={60} />
                  <span>{icon}</span>
                </div>
              );
            }}
          </For>
        </div>
      </div>
    </Container>
  );
};

export default IconList;

const styles = {
  container: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[32]};
    margin-block: 60px;
  `,
  wrapper: css`
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: ${spacing[32]};

    & > div {
      display: flex;
      flex-direction: column;
      gap: ${spacing[8]};
      ${typography.caption()};
      color: ${colorTokens.text.subdued};
      align-items: center;

      svg {
        color: ${colorTokens.icon.default};
      }
    }
  `,
};
