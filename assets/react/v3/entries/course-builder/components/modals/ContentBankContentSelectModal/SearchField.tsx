import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import { css } from '@emotion/react';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import { colorTokens } from '@TutorShared/config/styles';
import { useDebounce } from '@TutorShared/hooks/useDebounce';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import type { Filter } from '@TutorShared/hooks/usePaginatedTable';

interface FilterFormValues {
  search: string;
}

interface SearchFieldProps {
  onFilterItems: (filter: Filter) => void;
}

const SearchField = ({ onFilterItems }: SearchFieldProps) => {
  const actionsForm = useFormWithGlobalError<FilterFormValues>({ defaultValues: { search: '' } });
  const searchValue = useDebounce(actionsForm.watch('search'));

  useEffect(() => {
    onFilterItems({
      ...(searchValue.length > 0 && { search: searchValue }),
    });
  }, [onFilterItems, searchValue]);

  return (
    <Controller
      control={actionsForm.control}
      name="search"
      render={(controllerProps) => (
        <FormInputWithContent
          {...controllerProps}
          content={<SVGIcon name="search" width={24} height={24} />}
          contentCss={styles.contentCss}
          placeholder={__('Search...', 'tutor-pro')}
          showVerticalBar={false}
        />
      )}
    />
  );
};

export default SearchField;

const styles = {
  contentCss: css`
    svg {
      color: ${colorTokens.icon.default};
    }
  `,
};
