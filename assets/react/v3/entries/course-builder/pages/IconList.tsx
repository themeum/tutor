import SVGIcon from '@Atoms/SVGIcon';
import Container from '@Components/Container';
import FormInput from '@Components/fields/FormInput';
import collection from '@Config/icon-list';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import { useDebounce } from '@Hooks/useDebounce';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { getObjectEntries } from '@Utils/util';
import { css } from '@emotion/react';
import { useMemo } from 'react';
import { Controller } from 'react-hook-form';
const icons = getObjectEntries(collection).map(([name]) => name);

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
